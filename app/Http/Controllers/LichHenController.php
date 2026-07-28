<?php

namespace App\Http\Controllers;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CaKham;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use App\Models\PhanQuyen;
use App\Models\User;
use App\Models\VaiTro;
use App\Notifications\LichNotification;
use App\Services\NotificationRecipientResolver;
use App\Support\LichEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class LichHenController extends Controller
{
    /** Gửi thông báo cho người nhận liên quan đến lịch hẹn event này. */
    protected function notifyLich(LichHen $lh, string $event): void
    {
        try {
            $resolver = app(NotificationRecipientResolver::class);
            $recipients = $resolver->forLichHen($lh->fresh(['khachHang', 'coSo', 'caKham']), $event);
            if ($recipients->isEmpty()) return;

            Notification::send(
                $recipients,
                new LichNotification($lh, $event, auth()->user()?->name)
            );
        } catch (\Throwable $e) {
            \Log::warning('Notify lich_hen failed: '.$e->getMessage(), [
                'event' => $event, 'lich_hen_id' => $lh->id,
            ]);
        }
    }

    public function create(CoSo $co_so)
    {
        return view('longevity.lich-hen.create', $this->formData($co_so) + ['lh' => null]);
    }

    public function edit(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_lich_tu_van');

        $lich_hen->load('khachHang');

        return view('longevity.lich-hen.create', $this->formData($co_so) + ['lh' => $lich_hen]);
    }

    /** Dữ liệu dùng chung cho form tạo / sửa. */
    private function formData(CoSo $co_so): array
    {
        // Bác sĩ tư vấn = DANH MỤC bac_si có nhan_tu_van (thuộc cơ sở hoặc xuất hiện mọi cơ sở)
        $bacSis = BacSi::where('active', true)->where('nhan_tu_van', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->with('caKhams')
            ->orderBy('ten')->get();

        $sales = $co_so->nguoiDungs()->orderBy('name')->get();

        $caKhamMap = $bacSis->mapWithKeys(fn ($bs) => [
            $bs->id => $bs->caKhams->map(fn ($ck) => [
                'id' => $ck->id,
                'nhan' => $ck->nhan,
                'bd' => substr($ck->gio_bat_dau, 0, 5),
                'kt' => substr($ck->gio_ket_thuc, 0, 5),
            ])->values(),
        ]);

        return [
            'coSo' => $co_so,
            'bacSis' => $bacSis,
            'sales' => $sales,
            'caKhamMap' => $caKhamMap,
        ];
    }

    public function caKham(CoSo $co_so, Request $request)
    {
        $bs = BacSi::where('id', $request->query('bac_si_id'))
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->with('caKhams')->first();

        if (! $bs) {
            return response()->json(['slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();

        $except = $request->query('except');
        $counts = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_id', $bs->id)
            ->whereDate('ngay_hen', $ngay)
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->selectRaw('ca_kham_id, COUNT(*) as c')
            ->groupBy('ca_kham_id')
            ->pluck('c', 'ca_kham_id');

        return response()->json([
            'slots' => $bs->caKhams->map(function ($ck) use ($counts) {
                $booked = (int) ($counts[$ck->id] ?? 0);

                return [
                    'id' => $ck->id,
                    'nhan' => $ck->nhan,
                    'bd' => substr($ck->gio_bat_dau, 0, 5),
                    'kt' => substr($ck->gio_ket_thuc, 0, 5),
                    'full' => $booked >= 1,
                ];
            })->values(),
        ]);
    }

    public function checkPhone(CoSo $co_so, Request $request)
    {
        $sdt = preg_replace('/\s+/', '', (string) $request->query('sdt'));
        $kh = $sdt !== '' ? KhachHang::where('co_so_id', $co_so->id)->where('so_dien_thoai', $sdt)->first() : null;

        return response()->json([
            'ton_tai' => (bool) $kh,
            'ho_ten' => $kh?->ho_ten,
        ]);
    }

    public function show(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $lich_hen->load(['khachHang', 'bacSiTuVan', 'caKham', 'sale']);

        return view('longevity.lich-hen.show', [
            'coSo' => $co_so,
            'lichHen' => $lich_hen,
        ]);
    }

    public function store(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'ho_ten'            => ['required', 'string', 'max:255'],
            'so_dien_thoai'     => ['required', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:255'],
            'ngay_hen'          => ['required', 'date', 'after_or_equal:today'],
            'bac_si_id'    => ['required', Rule::exists('bac_si', 'id')],
            'ca_kham_id'        => ['required', Rule::exists('ca_kham', 'id')],
            'sale_id'           => ['required', Rule::exists('users', 'id')],
            'nguon'             => ['nullable', 'string', 'max:100'],
            'ghi_chu'           => ['nullable', 'string'],
        ], [
            'ho_ten.required'           => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'bac_si_id.required'   => 'Vui lòng chọn bác sĩ tư vấn.',
            'ca_kham_id.required'       => 'Vui lòng chọn ca khám.',
            'sale_id.required'          => 'Vui lòng chọn sale phụ trách.',
            'ngay_hen.after_or_equal'   => 'Ngày hẹn không được nhỏ hơn ngày hôm nay.',
        ]);

        // Check slot availability (đơn tu_choi không tính)
        $booked = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_id', $data['bac_si_id'])
            ->where('ca_kham_id', $data['ca_kham_id'])
            ->whereDate('ngay_hen', $data['ngay_hen'])
            ->where('trang_thai', '!=', 'tu_choi')
            ->exists();

        if ($booked) {
            return back()->withInput()->withErrors([
                'ca_kham_id' => 'Ca khám này đã có người đặt cho ngày đã chọn.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $canhBaoBooking = $this->bacSiTrungBooking($co_so, (int) $data['bac_si_id'], $data['ngay_hen'], (int) $data['ca_kham_id']);

        $lichHen = LichHen::create([
            'co_so_id'         => $co_so->id,
            'khach_hang_id'    => $kh->id,
            'bac_si_id'   => $data['bac_si_id'],
            'ca_kham_id'       => $data['ca_kham_id'],
            'sale_id'          => $data['sale_id'],
            'ngay_hen'         => $data['ngay_hen'],
            'nguon'            => $data['nguon'] ?? null,
            'ghi_chu'          => $data['ghi_chu'] ?? null,
            'trang_thai'       => 'cho_duyet',
        ]);

        $this->notifyLich($lichHen, LichEvent::TAO_MOI);

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã tạo lịch tư vấn cho ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBooking);
    }

    /**
     * Cảnh báo khi BS đã có lịch ĐẶT PHÒNG trùng giờ với ca khám này.
     */
    private function bacSiTrungBooking(CoSo $co_so, int $bacSiId, string $ngay, int $caKhamId, ?int $exceptId = null): ?string
    {
        // Không còn đối chiếu chéo: bác sĩ của LỊCH HẸN là tài khoản user, còn bác sĩ
        // của BOOKING phòng khám là danh mục bac_si — hai hệ id khác nhau, không so được.
        return null;
    }

    public function update(CoSo $co_so, LichHen $lich_hen, Request $request)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_lich_tu_van');

        $data = $request->validate([
            'ho_ten'            => ['required', 'string', 'max:255'],
            'so_dien_thoai'     => ['required', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:255'],
            'ngay_hen'          => ['required', 'date'],
            'bac_si_id'    => ['required', Rule::exists('bac_si', 'id')],
            'ca_kham_id'        => ['required', Rule::exists('ca_kham', 'id')],
            'sale_id'           => ['required', Rule::exists('users', 'id')],
            'nguon'             => ['nullable', 'string', 'max:100'],
            'ghi_chu'           => ['nullable', 'string'],
        ], [
            'ho_ten.required'           => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'bac_si_id.required'   => 'Vui lòng chọn bác sĩ tư vấn.',
            'ca_kham_id.required'       => 'Vui lòng chọn ca khám.',
            'sale_id.required'          => 'Vui lòng chọn sale phụ trách.',
        ]);

        // Ca khám đã có người đặt (trừ chính lịch đang sửa, không tính tu_choi)?
        $booked = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_id', $data['bac_si_id'])
            ->where('ca_kham_id', $data['ca_kham_id'])
            ->whereDate('ngay_hen', $data['ngay_hen'])
            ->where('trang_thai', '!=', 'tu_choi')
            ->where('id', '!=', $lich_hen->id)
            ->exists();

        if ($booked) {
            return back()->withInput()->withErrors([
                'ca_kham_id' => 'Ca khám này đã có người đặt cho ngày đã chọn.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $lich_hen->update([
            'khach_hang_id'    => $kh->id,
            'bac_si_id'   => $data['bac_si_id'],
            'ca_kham_id'       => $data['ca_kham_id'],
            'sale_id'          => $data['sale_id'],
            'ngay_hen'         => $data['ngay_hen'],
            'nguon'            => $data['nguon'] ?? null,
            'ghi_chu'          => $data['ghi_chu'] ?? null,
        ]);

        $canhBaoBooking = $this->bacSiTrungBooking($co_so, (int) $data['bac_si_id'], $data['ngay_hen'], (int) $data['ca_kham_id'], $lich_hen->id);

        $this->notifyLich($lich_hen, LichEvent::CAP_NHAT);

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã cập nhật lịch tư vấn của ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBooking);
    }

    public function destroy(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $user = auth()->user();
        $ok = $user->is_admin || (
            ($user->vai_tro_id || $user->phong_ban_id)
            && PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', 'xoa_lich_tu_van')->exists()
        );
        abort_unless($ok, 403, 'Bạn không có quyền xóa.');

        $ten = $lich_hen->khachHang?->ho_ten ?? 'khách';
        $this->notifyLich($lich_hen, LichEvent::HUY);
        $lich_hen->delete();

        return redirect("/{$co_so->slug}/ds-tu-van")
            ->with('ok', 'Đã xóa lịch tư vấn của ' . $ten . '.');
    }

    /** Duyệt / bỏ duyệt lịch tư vấn (chỉ admin). */
    public function duyet(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $user = auth()->user();
        $ok = $user->is_admin || (
            ($user->vai_tro_id || $user->phong_ban_id)
            && PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', 'duyet_tu_van')->exists()
        );
        abort_unless($ok, 403, 'Bạn không có quyền duyệt.');

        $approve = $lich_hen->trang_thai !== 'da_duyet';
        $wasRejected = $lich_hen->trang_thai === 'tu_choi';

        // Khi duyệt lại đơn từ chối: ca khám có thể đã bị đơn khác chiếm.
        if ($approve && $wasRejected) {
            $taken = LichHen::where('co_so_id', $co_so->id)
                ->where('bac_si_id', $lich_hen->bac_si_id)
                ->where('ca_kham_id', $lich_hen->ca_kham_id)
                ->whereDate('ngay_hen', $lich_hen->ngay_hen)
                ->where('trang_thai', '!=', 'tu_choi')
                ->where('id', '!=', $lich_hen->id)
                ->exists();
            if ($taken) {
                return back()->with('error', 'Không duyệt được: ca khám này đã có đơn khác chiếm chỗ. Vui lòng đổi ca khám trước khi duyệt.');
            }
        }

        $lich_hen->trang_thai = $approve ? 'da_duyet' : 'cho_duyet';
        $lich_hen->save();

        $ten = $lich_hen->khachHang?->ho_ten ?? 'khách';

        if ($approve) {
            $this->notifyLich($lich_hen, LichEvent::DUYET);
        }

        return back()->with('ok', ($approve ? 'Đã duyệt' : 'Đã bỏ duyệt') . ' lịch tư vấn của ' . $ten . '.');
    }

    private function authorizePerm(string $field): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }
        if ($user->is_admin) {
            return;
        }

        if (! $user->vai_tro_id && ! $user->phong_ban_id) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $ok = PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', $field)->exists();

        abort_unless($ok, 403, 'Bạn không có quyền thực hiện thao tác này.');
    }

    public function manage(CoSo $co_so, Request $request)
    {
        $loai = $request->query('loai') === 'tham_kham' ? 'tham_kham' : 'tu_van';
        $cot = $loai === 'tham_kham' ? 'nhan_kham_ls' : 'nhan_tu_van';
        // Bác sĩ = DANH MỤC bac_si theo năng lực (tư vấn / khám LS)
        $bacSis = BacSi::where('active', true)->where($cot, true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->with('caKhams')
            ->orderBy('id')->get();
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();

        $lichHens = LichHen::where('co_so_id', $co_so->id)
            ->whereDate('ngay_hen', $date)
            ->with(['khachHang', 'sale'])
            ->orderBy('id')->get()
            ->groupBy('bac_si_id');

        $toMin = fn ($t) => $t ? ((int) substr($t, 0, 2)) * 60 + ((int) substr($t, 3, 2)) : null;

        $startHour = 8;
        $endHour = 18;
        if ($bacSis->isNotEmpty()) {
            $startHour = $bacSis->min(fn ($bs) => (int) substr($bs->gio_bat_dau ?? '08:00', 0, 2));
            $endHour = $bacSis->max(function ($bs) {
                [$h, $m] = array_map('intval', explode(':', substr($bs->gio_ket_thuc ?? '18:00', 0, 5)));
                return $m > 0 ? $h + 1 : $h;
            });
        }

        $hourPx = 64;
        $dayStart = $startHour * 60;
        $bodyHeight = max(1, $endHour - $startHour) * $hourPx;
        $hours = range($startHour, $endHour - 1);

        $doctorColumns = $bacSis->map(function ($bs) use ($lichHens, $dayStart, $hourPx, $toMin) {
            $byCa = ($lichHens[$bs->id] ?? collect())->keyBy('ca_kham_id');
            $slots = $bs->caKhams->sortBy('thu_tu')->values();

            $events = [];
            $booked = 0;
            foreach ($slots as $ck) {
                $lh = $byCa->get($ck->id);
                $s = $toMin($ck->gio_bat_dau);
                $e = $toMin($ck->gio_ket_thuc);
                if ($s === null || $e === null || $e <= $s) {
                    continue;
                }

                $events[] = [
                    'ck' => $ck,
                    'lh' => $lh,
                    'top' => ($s - $dayStart) / 60 * $hourPx,
                    'height' => ($e - $s) / 60 * $hourPx,
                ];

                if ($lh && $lh->trang_thai !== 'tu_choi') {
                    $booked++;
                }
            }

            return [
                'bs' => $bs,
                'events' => $events,
                'total' => $slots->count(),
                'booked' => $booked,
            ];
        });

        $allLich = $lichHens->flatten();

        return view('longevity.lich-hen.manage', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'doctorColumns' => $doctorColumns,
            'loai' => $loai,
            'date' => $date,
            'hours' => $hours,
            'hourPx' => $hourPx,
            'bodyHeight' => $bodyHeight,
            'startHour' => $startHour,
            'endHour' => $endHour,
            'stats' => [
                'total' => $allLich->count(),
                'approved' => $allLich->where('trang_thai', 'da_duyet')->count(),
                'pending' => $allLich->where('trang_thai', 'cho_duyet')->count(),
            ],
        ]);
    }

    public function list(CoSo $co_so, Request $request)
    {
        $query = LichHen::where('co_so_id', $co_so->id)
            ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
            ->latest('id');

        if ($request->filled('ngay_tu')) {
            $query->whereDate('ngay_hen', '>=', $request->query('ngay_tu'));
        }
        if ($request->filled('ngay_den')) {
            $query->whereDate('ngay_hen', '<=', $request->query('ngay_den'));
        }
        if ($request->filled('bac_si_id')) {
            $query->where('bac_si_id', $request->query('bac_si_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->query('trang_thai'));
        }

        $lichHens = $query->paginate(20)->withQueryString();

        $bacSis = BacSi::where('active', true)->where('nhan_tu_van', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->orderBy('ten')->get();

        return view('longevity.lich-hen.list', [
            'coSo' => $co_so,
            'lichHens' => $lichHens,
            'bacSis' => $bacSis,
            'nguons' => collect([
                    'MKT — Marketing', 'MKT BR — Marketing BR', 'BDM',
                    'BOD — Ban lãnh đạo giới thiệu', 'SA — Sale Appointment',
                    'BA — Booking Appointment', 'WI — Walk-in',
                ])->merge(LichHen::where('co_so_id', $co_so->id)
                    ->whereNotNull('nguon')->distinct()->pluck('nguon'))
                ->unique()->values(),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'bac_si_id', 'nguon', 'trang_thai']),
        ]);
    }
}
