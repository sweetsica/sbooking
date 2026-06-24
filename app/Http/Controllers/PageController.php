<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use AuthorizesByPhanQuyen;
    /** Trang Bác sĩ: mỗi bác sĩ + danh sách booking đặt phòng được phân công trong ngày. */
    public function doctors(CoSo $co_so, Request $request)
    {
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now(); // mặc định hôm nay
        $view = $request->query('view') === 'thang' ? 'thang' : 'ngay';

        // Tài khoản đang đăng nhập có phải bác sĩ không (không phải admin).
        $authUser = auth()->user();
        $isDoctorView = $authUser && ! $authUser->is_admin
            && in_array($authUser->vaiTro?->ma, ['bac_si', 'bac_si_tu_van'], true);

        $vaiTroIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $bacSis = User::whereIn('vai_tro_id', $vaiTroIds)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->with('phongBan')
            ->orderBy('name')->get();

        // Tài khoản bác sĩ: chỉ quan tâm lịch của chính mình.
        $bacSiUserId = $isDoctorView ? $authUser->id : null;

        // ----- VIEW THÁNG: lưới lịch, mỗi ô đếm số booking trong ngày -----
        if ($view === 'thang') {
            $month = $this->buildMonthCells($date, function ($from, $to) use ($co_so, $bacSiUserId) {
                $q = Booking::where('co_so_id', $co_so->id)
                    ->whereBetween('ngay_dat', [$from, $to]);
                if ($bacSiUserId) $q->where('bac_si_user_id', $bacSiUserId);

                return $q->selectRaw('DATE(ngay_dat) d, COUNT(*) c')->groupBy('d')->pluck('c', 'd')->all();
            });

            return view('longevity.doctors', [
                'coSo' => $co_so,
                'danhSachCoSo' => $danhSachCoSo,
                'date' => $date,
                'view' => $view,
                'isDoctorView' => $isDoctorView,
                'monthCells' => $month['cells'],
                'monthStart' => $month['monthStart'],
            ]);
        }

        // Tài khoản bác sĩ chỉ xem thẻ của chính mình.
        if ($bacSiUserId) {
            $bacSis = $bacSis->where('id', $bacSiUserId)->values();
        }

        // Mỗi bác sĩ: 5 lịch gần nhất + phân trang riêng (page param "bs{id}").
        $cards = $bacSis->map(function ($bs) use ($co_so, $date) {
            $q = Booking::where('co_so_id', $co_so->id)
                ->where('bac_si_user_id', $bs->id)
                ->whereDate('ngay_dat', $date)
                ->with(['khachHang', 'phong', 'khungGio', 'dichVu'])
                ->orderByDesc('gio_thuc_hien')->orderByDesc('id');

            $items = $q->paginate(5, ['*'], 'bs' . $bs->id)->withQueryString();

            return [
                'bs' => $bs,
                'items' => $items,
                'total' => $items->total(),
            ];
        });

        // Lịch chưa gán bác sĩ (chỉ admin/nhân viên cần thấy để phân; bác sĩ thì không).
        $unassigned = null;
        if (! $isDoctorView) {
            $unassigned = Booking::where('co_so_id', $co_so->id)
                ->whereNull('bac_si_user_id')
                ->whereDate('ngay_dat', $date)
                ->with(['khachHang', 'phong', 'khungGio', 'dichVu'])
                ->orderByDesc('gio_thuc_hien')->orderByDesc('id')
                ->paginate(5, ['*'], 'chuaphan')->withQueryString();
        }

        // Thống kê tổng (mọi lịch của cơ sở trong ngày đã chọn).
        $statQ = Booking::where('co_so_id', $co_so->id)->whereDate('ngay_dat', $date);
        if ($bacSiUserId) $statQ->where('bac_si_user_id', $bacSiUserId);
        $total = (clone $statQ)->count();
        $approved = (clone $statQ)->whereIn('trang_thai', ['da_duyet', 'da_xong'])->count();

        return view('longevity.doctors', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'date' => $date,
            'view' => $view,
            'isDoctorView' => $isDoctorView,
            'cards' => $cards,
            'unassigned' => $unassigned,
            'stats' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $total - $approved,
            ],
        ]);
    }

    /**
     * Dựng lưới lịch tháng (tuần bắt đầu Thứ Hai). $counter($from, $to) trả về
     * map ['Y-m-d' => số booking] để tô màu + hiển thị số lịch mỗi ngày.
     */
    private function buildMonthCells(\Illuminate\Support\Carbon $date, \Closure $counter): array
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(\Carbon\CarbonInterface::SUNDAY);

        $counts = $counter($monthStart->toDateString(), $monthEnd->toDateString());
        $today = now()->toDateString();
        $selected = $date->toDateString();

        $cells = [];
        for ($d = $gridStart->copy(); $d <= $gridEnd; $d->addDay()) {
            $key = $d->toDateString();
            $cells[] = [
                'date' => $d->copy(),
                'inMonth' => $d->month === $monthStart->month,
                'count' => (int) ($counts[$key] ?? 0),
                'isToday' => $key === $today,
                'isSelected' => $key === $selected,
            ];
        }

        return ['cells' => $cells, 'monthStart' => $monthStart];
    }

    public function rooms(CoSo $co_so, Request $request)
    {
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();
        $phongs = $co_so->phongs()->with('khungGios')->orderBy('id')->get();

        $bookingsByPhong = Booking::where('co_so_id', $co_so->id)
            ->where('trang_thai', '!=', 'tu_choi') // đơn bị từ chối không chiếm chỗ
            ->whereDate('ngay_dat', $date)
            ->get()
            ->groupBy('phong_id');

        $roomData = $phongs->map(function ($phong) use ($bookingsByPhong, $date) {
            $bookings = $bookingsByPhong->get($phong->id, collect());
            $slots = $phong->khungGios;
            $beds = max(1, (int) $phong->so_slot_toi_da);
            $capacity = $slots->count() * $beds;
            $occupied = $bookings->count();
            $fill = $capacity > 0 ? (int) round($occupied / $capacity * 100) : 0;

            $bySlot = $bookings->groupBy('khung_gio_id');
            $slotStatus = $slots->map(function ($kg) use ($bySlot, $beds) {
                $count = ($bySlot[$kg->id] ?? collect())->count();
                if ($count >= $beds) return 'full';
                if ($count > 0) return 'partial';
                return 'empty';
            });

            $bedStatus = [];
            $currentSlot = $slots->first();
            if ($currentSlot) {
                $currentBookings = ($bySlot[$currentSlot->id] ?? collect())->count();
                for ($i = 0; $i < $beds; $i++) {
                    $bedStatus[] = $i < $currentBookings ? 'occupied' : 'available';
                }
            }

            return [
                'phong' => $phong,
                'beds' => $beds,
                'occupied' => $occupied,
                'fill' => $fill,
                'slotStatus' => $slotStatus,
                'bedStatus' => $bedStatus,
            ];
        });

        return view('longevity.rooms', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'roomData' => $roomData,
            'date' => $date,
        ]);
    }

    public function timeline(CoSo $co_so, Request $request)
    {
        // Trang chủ Lịch hẹn: mọi tài khoản đã đăng nhập đều xem được (không khóa theo quyền)
        $rooms = $co_so->phongs()->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();
        $view = $request->query('view') === 'thang' ? 'thang' : 'ngay';

        $room = $rooms->firstWhere('id', (int) $request->query('phong_id'))
            ?? $rooms->firstWhere('trang_thai', 'hoat_dong')
            ?? $rooms->first();

        // ----- VIEW THÁNG: lưới lịch, mỗi ô đếm số booking của phòng trong ngày -----
        if ($view === 'thang') {
            $month = $this->buildMonthCells($date, function ($from, $to) use ($co_so, $room) {
                $q = Booking::where('co_so_id', $co_so->id)
                    ->where('trang_thai', '!=', 'tu_choi') // đơn bị từ chối không chiếm chỗ
                    ->whereBetween('ngay_dat', [$from, $to]);
                if ($room) $q->where('phong_id', $room->id);

                return $q->selectRaw('DATE(ngay_dat) d, COUNT(*) c')->groupBy('d')->pluck('c', 'd')->all();
            });

            return view('longevity.timeline', [
                'coSo' => $co_so,
                'rooms' => $rooms,
                'room' => $room,
                'date' => $date,
                'view' => $view,
                'monthCells' => $month['cells'],
                'monthStart' => $month['monthStart'],
            ]);
        }

        $slots = $room ? $room->khungGios()->orderBy('thu_tu')->get() : collect();
        $beds = $room ? max(1, (int) $room->so_slot_toi_da) : 0;

        $bookings = collect();
        if ($room) {
            $bookings = Booking::where('co_so_id', $co_so->id)
                ->where('phong_id', $room->id)
                ->where('trang_thai', '!=', 'tu_choi') // đơn bị từ chối không chiếm chỗ trong lịch biểu
                ->whereDate('ngay_dat', $date)
                ->with(['khachHang', 'dichVu', 'bacSi', 'ktv', 'khungGio'])
                ->orderBy('id')->get();
        }

        // Lưới theo KHUNG 1 GIỜ: lấy khoảng giờ từ các khung giờ của phòng.
        $startHour = $slots->isNotEmpty() ? (int) substr($slots->first()->gio_bat_dau, 0, 2) : 8;
        $endHour = $startHour;
        if ($slots->isNotEmpty()) {
            foreach ($slots as $s) {
                [$eh, $em] = array_map('intval', explode(':', substr($s->gio_ket_thuc, 0, 5)));
                $h = $em > 0 ? $eh + 1 : $eh; // làm tròn lên nếu lẻ phút
                $endHour = max($endHour, $h);
            }
        } else {
            $endHour = 18;
        }

        // ----- Bố cục dạng LỊCH: khối cao theo thời lượng, trùng giờ thì chia cột -----
        $hourPx = 64;
        $dayStart = $startHour * 60;
        $dayEnd = $endHour * 60;
        $bodyHeight = max(1, $endHour - $startHour) * $hourPx;

        $toMin = function ($t) {
            if (! $t) return null;
            $p = explode(':', $t);
            return ((int) $p[0]) * 60 + ((int) ($p[1] ?? 0));
        };

        // Dựng danh sách sự kiện (start/end phút), mặc định 1 tiếng nếu thiếu giờ kết thúc.
        $raw = [];
        foreach ($bookings as $b) {
            $s = $toMin($b->gio_thuc_hien ?: $b->khungGio?->gio_bat_dau);
            if ($s === null) continue;
            $e = $toMin($b->gio_ket_thuc ?: $b->khungGio?->gio_ket_thuc);
            if ($e === null || $e <= $s) $e = $s + 60;
            $s = max($s, $dayStart);
            $e = min($e, $dayEnd);
            if ($e <= $s) continue;
            $raw[] = ['bk' => $b, 's' => $s, 'e' => $e];
        }
        usort($raw, fn ($a, $b) => ($a['s'] <=> $b['s']) ?: ($a['e'] <=> $b['e']));

        $nbeds = max(1, $beds);

        // Phân booking vào các giường (greedy: giường trống đầu tiên; nếu quá tải -> giường rảnh sớm nhất).
        $bedEnds = array_fill(0, $nbeds, 0);
        $bedItems = array_fill(0, $nbeds, []);
        foreach ($raw as $ev) {
            $bed = null;
            for ($i = 0; $i < $nbeds; $i++) {
                if ($ev['s'] >= $bedEnds[$i]) { $bed = $i; break; }
            }
            if ($bed === null) {
                $bed = 0;
                for ($i = 1; $i < $nbeds; $i++) {
                    if ($bedEnds[$i] < $bedEnds[$bed]) $bed = $i;
                }
            }
            $bedItems[$bed][] = $ev;
            $bedEnds[$bed] = max($bedEnds[$bed], $ev['e']);
        }

        // Trong từng giường: chồng giờ thì chia cột (mỗi ca một cột con).
        $splitColumns = function (array $items) use ($dayStart, $hourPx) {
            $out = [];
            $cluster = [];
            $clusterEnd = null;
            $flush = function () use (&$cluster, &$out, $dayStart, $hourPx) {
                if (! $cluster) return;
                $colEnds = [];
                foreach ($cluster as $i => $ev) {
                    $col = null;
                    foreach ($colEnds as $ci => $cend) {
                        if ($ev['s'] >= $cend) { $col = $ci; break; }
                    }
                    if ($col === null) $col = count($colEnds);
                    $colEnds[$col] = $ev['e'];
                    $cluster[$i]['col'] = $col;
                }
                $ncols = count($colEnds);
                foreach ($cluster as $ev) {
                    $out[] = [
                        'bk' => $ev['bk'],
                        'top' => ($ev['s'] - $dayStart) / 60 * $hourPx,
                        'height' => ($ev['e'] - $ev['s']) / 60 * $hourPx,
                        'col' => $ev['col'],
                        'ncols' => $ncols,
                    ];
                }
                $cluster = [];
            };
            foreach ($items as $ev) {
                if ($cluster && $ev['s'] >= $clusterEnd) { $flush(); $clusterEnd = null; }
                $cluster[] = $ev;
                $clusterEnd = $clusterEnd === null ? $ev['e'] : max($clusterEnd, $ev['e']);
            }
            $flush();

            return $out;
        };

        $bedColumns = [];
        for ($i = 0; $i < $nbeds; $i++) {
            $bedColumns[] = [
                'index' => $i + 1,
                'events' => $splitColumns($bedItems[$i]),
            ];
        }

        $hours = range($startHour, $endHour - 1);

        $total = $bookings->count();
        $approved = $bookings->whereIn('trang_thai', ['da_duyet', 'da_xong'])->count();
        $capacity = max(1, ($endHour - $startHour) * $nbeds);

        return view('longevity.timeline', [
            'coSo' => $co_so,
            'rooms' => $rooms,
            'room' => $room,
            'date' => $date,
            'view' => $view,
            'beds' => $nbeds,
            'bedColumns' => $bedColumns,
            'hours' => $hours,
            'hourPx' => $hourPx,
            'bodyHeight' => $bodyHeight,
            'startHour' => $startHour,
            'endHour' => $endHour,
            'stats' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $total - $approved,
                'fill' => (int) round($total / $capacity * 100),
            ],
        ]);
    }

    // Trang "Duyệt lịch": cùng danh sách nhưng chỉ các đơn đang chờ duyệt.
    public function approvals(CoSo $co_so, Request $request)
    {
        return $this->bookings($co_so, $request, true);
    }

    public function bookings(CoSo $co_so, Request $request, bool $approvalMode = false)
    {
        $this->authorizePerm($approvalMode ? 'duyet_booking' : 'xem_booking');

        $query = Booking::where('co_so_id', $co_so->id)
            ->with(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'ktv', 'sale'])
            ->latest('id');

        if ($request->filled('ngay_tu')) {
            $query->whereDate('ngay_dat', '>=', $request->query('ngay_tu'));
        }
        if ($request->filled('ngay_den')) {
            $query->whereDate('ngay_dat', '<=', $request->query('ngay_den'));
        }
        if ($request->filled('phong_id')) {
            $query->where('phong_id', $request->query('phong_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }
        if ($approvalMode) {
            $query->where('trang_thai', 'cho_duyet'); // khóa cứng chỉ đơn chờ duyệt
        } elseif ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->query('trang_thai'));
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('longevity.bookings', [
            'coSo' => $co_so,
            'bookings' => $bookings,
            'phongs' => $co_so->phongs()->get(),
            'nguons' => Booking::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'phong_id', 'nguon', 'trang_thai']),
            'approvalMode' => $approvalMode,
        ]);
    }
}
