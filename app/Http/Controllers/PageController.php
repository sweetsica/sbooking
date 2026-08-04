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
        // Không nạp toàn bộ khung giờ (mỗi phòng khám có ~120 khung 5') — chỉ cần giờ
        // mở/đóng. Trang phòng hiển thị lịch trình TỔNG HỢP THEO GIỜ, không theo khung.
        $phongs = $co_so->phongs()->orderBy('id')->get();

        // Giờ mở/đóng mỗi phòng — 1 query gộp thay vì nạp 600 dòng khung giờ.
        $bounds = \App\Models\KhungGio::whereIn('phong_id', $phongs->pluck('id'))
            ->selectRaw('phong_id, MIN(gio_bat_dau) as o, MAX(gio_ket_thuc) as c')
            ->groupBy('phong_id')
            ->get()->keyBy('phong_id');

        $bookingsByPhong = Booking::where('co_so_id', $co_so->id)
            ->where('trang_thai', '!=', 'tu_choi') // đơn bị từ chối không chiếm chỗ
            ->whereDate('ngay_dat', $date)
            ->get(['id', 'phong_id', 'khung_gio_id', 'gio_thuc_hien', 'gio_ket_thuc'])
            ->groupBy('phong_id');

        // Giờ bắt đầu/kết thúc của các khung được booking tham chiếu (fallback khi
        // booking thiếu gio_thuc_hien) — 1 query, chỉ các khung cần thiết.
        $kgIds = $bookingsByPhong->flatten()->pluck('khung_gio_id')->filter()->unique();
        $kgTimes = \App\Models\KhungGio::whereIn('id', $kgIds)
            ->get(['id', 'gio_bat_dau', 'gio_ket_thuc'])->keyBy('id');

        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;

        $roomData = $phongs->map(function ($phong) use ($bookingsByPhong, $bounds, $kgTimes, $toMin, $date) {
            $beds = max(1, (int) $phong->so_slot_toi_da);
            $b = $bounds->get($phong->id);
            $startHour = $b ? (int) substr($b->o, 0, 2) : 8;
            $endHour = 18;
            if ($b) {
                $eh = (int) substr($b->c, 0, 2);
                $em = (int) substr($b->c, 3, 2);
                $endHour = $em > 0 ? $eh + 1 : $eh; // làm tròn lên nếu lẻ phút
            }
            if ($endHour <= $startHour) $endHour = $startHour + 1;

            $bookings = $bookingsByPhong->get($phong->id, collect());

            // Khoảng giờ thực [s, e] (phút) của mỗi booking.
            $intervals = $bookings->map(function ($bk) use ($kgTimes, $toMin) {
                $kg = $bk->khung_gio_id ? $kgTimes->get($bk->khung_gio_id) : null;
                $bd = $bk->gio_thuc_hien ?: $kg?->gio_bat_dau;
                $kt = $bk->gio_ket_thuc ?: $kg?->gio_ket_thuc;
                $s = $toMin($bd ? substr($bd, 0, 5) : null);
                if ($s === null) return null;
                $e = $toMin($kt ? substr($kt, 0, 5) : null) ?? ($s + 60);
                if ($e <= $s) $e = $s + 60;
                return [$s, $e];
            })->filter()->values();

            $hours = range($startHour, $endHour - 1);

            // Mỗi giờ: số giường bị chiếm = số booking overlap [h:00, h+1:00], cap ở số giường.
            $hourData = [];
            foreach ($hours as $h) {
                $hs = $h * 60;
                $he = $hs + 60;
                $occ = min($beds, $intervals->filter(fn ($iv) => $iv[0] < $he && $hs < $iv[1])->count());
                $hourData[$h] = [
                    'occupied' => $occ,
                    'status' => $occ >= $beds ? 'full' : ($occ > 0 ? 'partial' : 'empty'),
                ];
            }

            // Giờ chọn mặc định: giờ hiện tại nếu là hôm nay & trong khoảng, else giờ mở.
            $defaultHour = $startHour;
            if ($date->isToday()) {
                $nowH = (int) now()->format('H');
                if ($nowH >= $startHour && $nowH < $endHour) $defaultHour = $nowH;
            }

            $occupied = $bookings->count(); // tổng lượt đặt trong ngày
            $capacity = count($hours) * $beds; // sức chứa = số giờ × số giường
            $fill = $capacity > 0 ? (int) round($occupied / $capacity * 100) : 0;

            return [
                'phong' => $phong,
                'beds' => $beds,
                'occupied' => $occupied,
                'fill' => $fill,
                'hours' => $hours,
                'hourData' => $hourData,
                'defaultHour' => $defaultHour,
            ];
        });

        return view('longevity.rooms', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'roomData' => $roomData,
            'date' => $date,
        ]);
    }

    /**
     * 2026-08-05 (SCRM T10 merge): dashboard mặc định của /lich-hen.
     * 4 widget + list booking cơ bản + JSON endpoint bán real-time 15s.
     */
    public function dashboard(CoSo $co_so, Request $request)
    {
        $today = now()->toDateString();
        $now = now();
        $in1h = $now->copy()->addHour();

        $base = fn () => Booking::where('co_so_id', $co_so->id)->visibleTo(auth()->user());

        $todayCount = (clone $base())->whereDate('ngay_dat', $today)->count();

        $processingCount = (clone $base())
            ->whereDate('ngay_dat', $today)
            ->where(function ($q) {
                $q->where('trang_thai_khach', 'da_toi')
                  ->orWhere('trang_thai_khach', 'toi_tre')
                  ->orWhere('trang_thai_tiep_don', 'dang_tiep_don');
            })
            ->where('trang_thai', '!=', 'da_xong')
            ->count();

        $upcomingCount = (clone $base())
            ->whereDate('ngay_dat', $today)
            ->where('trang_thai', 'da_duyet')
            ->whereNull('trang_thai_khach')
            ->where(function ($q) use ($now, $in1h) {
                $q->whereBetween('gio_thuc_hien', [$now->format('H:i:s'), $in1h->format('H:i:s')]);
            })
            ->count();

        $doneCount = (clone $base())
            ->whereDate('ngay_dat', $today)
            ->where('trang_thai', 'da_xong')
            ->count();

        $tab = in_array($request->query('tab'), ['today', 'processing', 'upcoming', 'done'], true)
            ? $request->query('tab') : 'today';

        $listQ = (clone $base())->whereDate('ngay_dat', $today);
        if ($tab === 'processing') {
            $listQ->where(function ($q) {
                $q->where('trang_thai_khach', 'da_toi')
                  ->orWhere('trang_thai_khach', 'toi_tre')
                  ->orWhere('trang_thai_tiep_don', 'dang_tiep_don');
            })->where('trang_thai', '!=', 'da_xong');
        } elseif ($tab === 'upcoming') {
            $listQ->where('trang_thai', 'da_duyet')
                ->whereNull('trang_thai_khach')
                ->whereBetween('gio_thuc_hien', [$now->format('H:i:s'), $in1h->format('H:i:s')]);
        } elseif ($tab === 'done') {
            $listQ->where('trang_thai', 'da_xong');
        }

        $bookings = $listQ->with(['khachHang', 'dichVu', 'sale'])
            ->orderBy('gio_thuc_hien')
            ->limit(100)
            ->get();

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json([
                'counts' => compact('todayCount', 'processingCount', 'upcomingCount', 'doneCount') + [
                    'today' => $todayCount, 'processing' => $processingCount,
                    'upcoming' => $upcomingCount, 'done' => $doneCount,
                ],
                'tab' => $tab,
                'bookings' => $bookings->map(fn ($b) => [
                    'id' => $b->id, 'ma_booking' => $b->ma_booking,
                    'ten_khach' => $b->khachHang?->ho_ten,
                    'sdt' => $b->khachHang?->so_dien_thoai,
                    'sale' => $b->sale?->name,
                    'loai' => $b->loai_dat_lich, 'dich_vu' => $b->dichVu?->ten,
                    'gio' => $b->gio_thuc_hien ? substr($b->gio_thuc_hien, 0, 5) : null,
                    'trang_thai' => $b->trang_thai, 'trang_thai_khach' => $b->trang_thai_khach,
                    'url' => "/{$co_so->slug}/xem-dat-phong/{$b->id}",
                ])->values(),
                'server_time' => now()->format('H:i:s'),
            ]);
        }

        return view('longevity.dashboard', [
            'coSo' => $co_so, 'todayCount' => $todayCount,
            'processingCount' => $processingCount,
            'upcomingCount' => $upcomingCount, 'doneCount' => $doneCount,
            'tab' => $tab, 'bookings' => $bookings, 'active' => 'lich-hen',
        ]);
    }

    public function timeline(CoSo $co_so, Request $request)
    {
        // Lọc phòng theo kiểu: 'phong_kham' (mặc định) hoặc 'phong_dich_vu'
        $kieu = $request->query('kieu') === 'dich_vu' ? 'phong_dich_vu' : 'phong_kham';

        $rooms = $co_so->phongs()
            ->where('kieu_phong', $kieu)
            ->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();
        $view = $request->query('view') === 'thang' ? 'thang' : 'ngay';

        $room = $rooms->firstWhere('id', (int) $request->query('phong_id'))
            ?? $rooms->firstWhere('trang_thai', 'hoat_dong')
            ?? $rooms->first();

        // ----- Lọc theo nhân sự: KTV (phòng dịch vụ) hoặc Bác sĩ (phòng khám) -----
        // Mặc định lọc = chính mình nếu người đăng nhập đúng vai trò đó; 0 = tất cả.
        $isDichVu   = $kieu === 'phong_dich_vu';
        $staffParam = $isDichVu ? 'ktv_id' : 'bac_si_id';
        $staffCol   = $isDichVu ? 'ktv_user_id' : 'bac_si_user_id';
        $staffLabel = $isDichVu ? 'KTV' : 'Bác sĩ';
        $authUser   = auth()->user();

        if ($isDichVu) {
            $vrIds = VaiTro::where('ma', 'ktv')->pluck('id');
            $staffList = User::whereIn('vai_tro_id', $vrIds)
                ->where('co_so_id', $co_so->id)
                ->orderBy('name')->get();
        } else {
            // Bác sĩ của cơ sở + bác sĩ tư vấn global.
            $vrIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
            $staffList = User::whereIn('vai_tro_id', $vrIds)
                ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
                ->orderBy('name')->get();
        }
        $selfIsStaff = $authUser && $vrIds->contains($authUser->vai_tro_id);
        $staffId = $request->has($staffParam)
            ? (int) $request->query($staffParam)
            : ($selfIsStaff ? (int) $authUser->id : 0);

        // ----- VIEW THÁNG: lưới lịch, mỗi ô đếm số booking của phòng trong ngày -----
        if ($view === 'thang') {
            $month = $this->buildMonthCells($date, function ($from, $to) use ($co_so, $room, $staffId, $staffCol) {
                $q = Booking::where('co_so_id', $co_so->id)
                    ->where('trang_thai', '!=', 'tu_choi') // đơn bị từ chối không chiếm chỗ
                    ->whereBetween('ngay_dat', [$from, $to]);
                if ($room) $q->where('phong_id', $room->id);
                if ($staffId) $q->where($staffCol, $staffId);

                return $q->selectRaw('DATE(ngay_dat) d, COUNT(*) c')->groupBy('d')->pluck('c', 'd')->all();
            });

            return view('longevity.timeline', [
                'coSo' => $co_so,
                'rooms' => $rooms,
                'room' => $room,
                'date' => $date,
                'view' => $view,
                'kieu' => $kieu,
                'staffList' => $staffList,
                'staffId' => $staffId,
                'staffParam' => $staffParam,
                'staffLabel' => $staffLabel,
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
                ->when($staffId, fn ($q) => $q->where($staffCol, $staffId))
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
            'kieu' => $kieu,
            'staffList' => $staffList,
            'staffId' => $staffId,
            'staffParam' => $staffParam,
            'staffLabel' => $staffLabel,
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
        if ($approvalMode) {
            $this->authorizePerm('duyet_booking');
        } else {
            $scope = $this->bookingViewScope();
            if ($scope === null) abort(403, 'Bạn không có quyền xem booking.');
        }

        $query = Booking::where('co_so_id', $co_so->id)
            ->with(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'ktv', 'sale']);

        if (! $approvalMode) {
            $this->applyViewScope($query);
        }

        if ($request->filled('ngay_tu')) {
            $query->whereDate('ngay_dat', '>=', $request->query('ngay_tu'));
        }
        if ($request->filled('ngay_den')) {
            $query->whereDate('ngay_dat', '<=', $request->query('ngay_den'));
        }
        if ($request->filled('phong_id')) {
            $query->where('phong_id', $request->query('phong_id'));
        }
        if ($request->filled('bac_si_id')) {
            $query->where('bac_si_user_id', $request->query('bac_si_id'));
        }
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->query('sale_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }
        if ($approvalMode) {
            $query->where('trang_thai', 'cho_duyet'); // khóa cứng chỉ đơn chờ duyệt
        } elseif ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->query('trang_thai'));
        }

        // Sort: mặc định latest('id'); cho phép sort theo ngay_dat hoặc khung_gio (gio_thuc_hien).
        $sort = in_array($request->query('sort'), ['ngay_dat', 'khung_gio'], true) ? $request->query('sort') : null;
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        if ($sort === 'ngay_dat') {
            // Cùng ngày → xếp theo id (thứ tự tạo) cùng chiều để không nhảy lộn xộn.
            $query->orderBy('ngay_dat', $dir)->orderBy('id', $dir);
        } elseif ($sort === 'khung_gio') {
            // Cùng giờ thực hiện → xếp theo id cùng chiều để tránh nhảy lộn xộn.
            $query->orderByRaw("gio_thuc_hien IS NULL, gio_thuc_hien {$dir}")->orderBy('id', $dir);
        } else {
            $query->latest('id');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Lịch trong khung giờ hiện tại (H:00 → H+1:00) của HÔM NAY — độc lập với bộ lọc phía trên.
        // Mục đích: giúp NV theo dõi nhanh khách đang / sắp đến trong 60' hiện tại.
        $now = now();
        $hStart = sprintf('%02d:00', $now->hour);
        $hEnd = sprintf('%02d:00', ($now->hour + 1) % 24);
        $currentSlotQuery = Booking::where('co_so_id', $co_so->id)
            ->whereDate('ngay_dat', $now->toDateString())
            ->where('trang_thai', '!=', 'tu_choi')
            ->whereNotNull('gio_thuc_hien')
            ->where('gio_thuc_hien', '<', $hEnd)
            ->where(function ($q) use ($hStart) {
                $q->whereNull('gio_ket_thuc')->orWhere('gio_ket_thuc', '>', $hStart);
            })
            ->with(['khachHang', 'phong', 'bacSi', 'ktv', 'dichVu'])
            ->orderBy('gio_thuc_hien');

        if (! $approvalMode) {
            $this->applyViewScope($currentSlotQuery);
        }
        $currentSlotBookings = $currentSlotQuery->get();

        // BS để filter: thuộc cơ sở hoặc global (is_tu_van=true)
        $vrBacSiIds = \App\Models\VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $bacSis = \App\Models\User::whereIn('vai_tro_id', $vrBacSiIds)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')->get(['id', 'name', 'chuc_danh']);

        // Sale để filter: nhân viên phụ trách đơn (tư vấn viên / lễ tân / nhân viên)
        $vrSaleIds = \App\Models\VaiTro::whereIn('ma', ['tu_van_vien', 'sales_lead', 'sales_manager', 'le_tan', 'nhan_vien'])->pluck('id');
        $sales = \App\Models\User::whereIn('vai_tro_id', $vrSaleIds)
            ->where('co_so_id', $co_so->id)
            ->where('is_admin', false)
            ->orderBy('name')->get(['id', 'name', 'chuc_danh']);

        return view('longevity.bookings', [
            'coSo' => $co_so,
            'bookings' => $bookings,
            'phongs' => $co_so->phongs()->get(),
            'bacSis' => $bacSis,
            'sales' => $sales,
            'nguons' => Booking::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'phong_id', 'bac_si_id', 'sale_id', 'nguon', 'trang_thai']),
            'sort' => $sort,
            'dir' => $dir,
            'currentSlotBookings' => $currentSlotBookings,
            'currentSlotLabel' => $hStart . ' - ' . $hEnd,
            'approvalMode' => $approvalMode,
        ]);
    }
}
