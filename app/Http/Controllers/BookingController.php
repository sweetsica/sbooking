<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\LichHen;
use App\Models\PhanQuyen;
use App\Models\Phong;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    use AuthorizesByPhanQuyen;
    public function create(CoSo $co_so)
    {
        $this->authorizePerm('them_booking');

        return view('longevity.create', $this->formData($co_so) + [
            'bk' => null,
            'allowedFields' => null, // null = không giới hạn (mode tạo mới)
        ]);
    }

    public function edit(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_booking');

        $booking->load(['khachHang', 'menus']);

        return view('longevity.create', $this->formData($co_so) + [
            'bk' => $booking,
            'allowedFields' => $this->allowedFieldKeys(),
        ]);
    }

    /** Dữ liệu dùng chung cho form tạo / sửa. */
    private function formData(CoSo $co_so): array
    {
        $co_so->load([
            'phongs' => fn ($q) => $q->where('trang_thai', 'hoat_dong')->with('khungGios'),
            'dichVus' => fn ($q) => $q->where('active', true),
            'menus' => fn ($q) => $q->where('active', true),
        ]);

        $vrBacSiIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $vrKtv = VaiTro::where('ma', 'ktv')->first();

        // Bác sĩ (gồm cả bác sĩ tư vấn): thuộc cơ sở hoặc có is_tu_van (global)
        $bacSis = User::whereIn('vai_tro_id', $vrBacSiIds)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')->get();

        // KTV thuộc cơ sở
        $ktvs = User::where('vai_tro_id', $vrKtv?->id)
            ->where('co_so_id', $co_so->id)
            ->orderBy('name')->get();

        // Nhân viên Sale / Lễ tân
        $sales = $co_so->nguoiDungs()->orderBy('name')->get();

        // Khung giờ theo từng phòng -> JSON cho dropdown động
        $slots = $co_so->phongs->mapWithKeys(fn ($p) => [
            $p->id => $p->khungGios->map(fn ($k) => [
                'id' => $k->id,
                'nhan' => $k->nhan,
                'bd' => substr($k->gio_bat_dau, 0, 5),
                'kt' => substr($k->gio_ket_thuc, 0, 5),
            ])->values(),
        ]);

        return [
            'coSo' => $co_so,
            'phongs' => $co_so->phongs,
            'dichVus' => $co_so->dichVus,
            'bacSis' => $bacSis,
            'ktvs' => $ktvs,
            'menus' => $co_so->menus,
            'sales' => $sales,
            'slots' => $slots,
        ];
    }

    // Trả về khung giờ (1 tiếng) của 1 phòng + tình trạng đã đầy theo ngày
    public function khungGio(CoSo $co_so, Request $request)
    {
        $phong = Phong::where('co_so_id', $co_so->id)
            ->where('id', $request->query('phong_id'))
            ->with('khungGios')->first();

        if (! $phong) {
            return response()->json(['capacity' => 0, 'slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();
        $capacity = max(1, (int) $phong->so_slot_toi_da);

        // Số booking theo từng khung giờ cho phòng + ngày đó
        $except = $request->query('except');
        $counts = Booking::where('co_so_id', $co_so->id)
            ->where('phong_id', $phong->id)
            ->whereDate('ngay_dat', $ngay)
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->selectRaw('khung_gio_id, COUNT(*) as c')
            ->groupBy('khung_gio_id')
            ->pluck('c', 'khung_gio_id');

        return response()->json([
            'capacity' => $capacity,
            'slots' => $phong->khungGios->map(function ($k) use ($counts, $capacity) {
                $booked = (int) ($counts[$k->id] ?? 0);

                return [
                    'id' => $k->id,
                    'nhan' => $k->nhan,
                    'bd' => substr($k->gio_bat_dau, 0, 5),
                    'kt' => substr($k->gio_ket_thuc, 0, 5),
                    'gio' => (int) substr($k->gio_bat_dau, 0, 2),
                    'booked' => $booked,
                    'capacity' => $capacity,
                    'full' => $booked >= $capacity,
                ];
            })->values(),
        ]);
    }

    // Khung giờ đã kín chỗ (đủ số slot của phòng) cho phòng + ngày?
    // Đơn `tu_choi` KHÔNG tính vào slot (để slot trống cho đơn mới).
    private function khungGioDayCho(CoSo $co_so, int $phongId, int $khungGioId, string $ngay, ?int $exceptId = null): bool
    {
        $capacity = max(1, (int) optional(Phong::find($phongId))->so_slot_toi_da);
        $booked = Booking::where('co_so_id', $co_so->id)
            ->where('phong_id', $phongId)
            ->where('khung_gio_id', $khungGioId)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->count();

        return $booked >= $capacity;
    }

    // Cảnh báo (KHÔNG chặn) khi bác sĩ đã có lịch trùng giờ trong cùng ngày —
    // kể cả ở phòng khác (so theo khoảng giờ thực tế, không theo khung_gio_id vì
    // mỗi phòng có khung giờ riêng). Trả về câu cảnh báo hoặc null.
    private function bacSiTrungLich(CoSo $co_so, int $bacSiId, string $ngay, int $khungGioId, ?string $gioThucHien, ?string $gioKetThuc, ?int $exceptId = null): ?string
    {
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        $kg = KhungGio::find($khungGioId);

        $bd = $gioThucHien ?: ($kg ? substr($kg->gio_bat_dau, 0, 5) : null);
        $kt = $gioKetThuc ?: ($kg ? substr($kg->gio_ket_thuc, 0, 5) : null);
        $s = $toMin($bd);
        if ($s === null) {
            return null;
        }
        $e = $toMin($kt) ?? ($s + 60); // thiếu giờ kết thúc → mặc định 1 tiếng

        $others = Booking::where('co_so_id', $co_so->id)
            ->where('bac_si_user_id', $bacSiId)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->with(['phong', 'khungGio'])
            ->get();

        foreach ($others as $o) {
            $obd = $o->gio_thuc_hien ?: $o->khungGio?->gio_bat_dau;
            $okt = $o->gio_ket_thuc ?: $o->khungGio?->gio_ket_thuc;
            $os = $toMin($obd ? substr($obd, 0, 5) : null);
            if ($os === null) {
                continue;
            }
            $oe = $toMin($okt ? substr($okt, 0, 5) : null) ?? ($os + 60);

            if ($s < $oe && $os < $e) { // hai khoảng giờ chồng nhau
                $bs = User::find($bacSiId);

                return 'Lưu ý: ' . ($bs?->ten_day_du ?? 'Bác sĩ') . ' đã có lịch lúc '
                    . substr($obd, 0, 5) . ' tại ' . ($o->phong?->ten ?? 'phòng khác')
                    . ' trong ngày này (trùng giờ) — lịch vẫn được lưu.';
            }
        }

        // Check chéo với lịch tư vấn (LichHen) của cùng bác sĩ
        $tuVans = LichHen::where('co_so_id', $co_so->id)
            ->where('bac_si_user_id', $bacSiId)
            ->whereDate('ngay_hen', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->with('caKham')
            ->get();

        foreach ($tuVans as $lh) {
            $os = $toMin($lh->caKham?->gio_bat_dau ? substr($lh->caKham->gio_bat_dau, 0, 5) : null);
            $oe = $toMin($lh->caKham?->gio_ket_thuc ? substr($lh->caKham->gio_ket_thuc, 0, 5) : null);
            if ($os === null || $oe === null) continue;

            if ($s < $oe && $os < $e) {
                $bs = User::find($bacSiId);
                return 'Lưu ý: ' . ($bs?->ten_day_du ?? 'Bác sĩ') . ' đã có lịch TƯ VẤN lúc '
                    . substr($lh->caKham->gio_bat_dau, 0, 5)
                    . ' trong ngày này (trùng giờ) — lịch vẫn được lưu.';
            }
        }

        return null;
    }

    /**
     * KTV bận theo khoảng giờ thực tế trong ngày — chặn (không cảnh báo)
     * vì KTV chỉ phục vụ 1 khách 1 lúc, không như BS có thể tư vấn ngắn.
     * So sánh theo khoảng giờ thực, không theo khung_gio_id (vì mỗi phòng có khung riêng).
     */
    private function ktvBanKhoangGio(CoSo $co_so, int $ktvId, string $ngay, int $khungGioId, ?string $gioThucHien, ?string $gioKetThuc, ?int $exceptId = null): bool
    {
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        $kg = KhungGio::find($khungGioId);

        $bd = $gioThucHien ?: ($kg ? substr($kg->gio_bat_dau, 0, 5) : null);
        $kt = $gioKetThuc ?: ($kg ? substr($kg->gio_ket_thuc, 0, 5) : null);
        $s = $toMin($bd);
        if ($s === null) {
            return false;
        }
        $e = $toMin($kt) ?? ($s + 60);

        $others = Booking::where('co_so_id', $co_so->id)
            ->where('ktv_user_id', $ktvId)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->with('khungGio')
            ->get();

        foreach ($others as $o) {
            $obd = $o->gio_thuc_hien ?: $o->khungGio?->gio_bat_dau;
            $okt = $o->gio_ket_thuc ?: $o->khungGio?->gio_ket_thuc;
            $os = $toMin($obd ? substr($obd, 0, 5) : null);
            if ($os === null) continue;
            $oe = $toMin($okt ? substr($okt, 0, 5) : null) ?? ($os + 60);

            if ($s < $oe && $os < $e) {
                return true;
            }
        }
        return false;
    }

    // Kiểm tra trùng số điện thoại trong cơ sở
    public function checkPhone(CoSo $co_so, Request $request)
    {
        $sdt = preg_replace('/\s+/', '', (string) $request->query('sdt'));
        $kh = null;
        if ($sdt !== '') {
            $kh = KhachHang::where('co_so_id', $co_so->id)
                ->where('so_dien_thoai', $sdt)->first();
        }

        return response()->json([
            'ton_tai' => (bool) $kh,
            'ho_ten' => $kh?->ho_ten,
        ]);
    }

    public function store(CoSo $co_so, Request $request)
    {
        $this->authorizePerm('them_booking');

        $data = $request->validate([
            'ho_ten'        => ['required', 'string', 'max:255'],
            'so_dien_thoai' => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'ngay_dat'      => ['required', 'date', 'after_or_equal:today'],
            'phong_id'      => ['required', Rule::exists('phong', 'id')->where('co_so_id', $co_so->id)],
            'khung_gio_id'  => ['required', Rule::exists('khung_gio', 'id')],
            'gio_thuc_hien' => ['nullable', 'regex:/^\d{2}:(00|30)$/'], // phút fix 00 / 30
            'gio_ket_thuc'  => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'dich_vu_id'    => ['required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => ['required', Rule::exists('users', 'id')],
            'bac_si_user_id' => ['nullable', Rule::exists('users', 'id')],
            'ktv_user_id'   => ['nullable', Rule::exists('users', 'id')],
            'so_lieu_trinh' => ['nullable', 'string', 'max:50'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'co_tu_van'     => ['nullable', 'boolean'],
            'co_kham_cls'   => ['nullable', 'boolean'],
            'ghi_chu'       => ['nullable', 'string'],
            'menu_ids'      => ['nullable', 'array'],
            'menu_ids.*'    => [Rule::exists('menu', 'id')],
        ], [
            'ho_ten.required'        => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'phong_id.required'      => 'Vui lòng chọn phòng.',
            'khung_gio_id.required'  => 'Vui lòng chọn khung giờ.',
            'dich_vu_id.required'    => 'Vui lòng chọn liệu pháp/dịch vụ.',
            'sale_id.required'       => 'Vui lòng chọn sale phụ trách.',
            'gio_thuc_hien.regex'    => 'Phút thực hiện chỉ được là 00 hoặc 30.',
        ]);

        // KTV conflict check - theo khoảng giờ thực, chặn cả khi ở phòng khác
        if (! empty($data['ktv_user_id'])) {
            $ktvBusy = $this->ktvBanKhoangGio($co_so, (int) $data['ktv_user_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($ktvBusy) {
                return back()->withInput()->withErrors([
                    'ktv_user_id' => 'KTV đã được đặt, vui lòng chọn KTV khác.',
                ]);
            }
        }

        // Chặn đặt trùng: khung giờ đã kín chỗ thì không cho đặt nữa
        if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'])) {
            return back()->withInput()->withErrors([
                'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
            ]);
        }

        $sdt = preg_replace('/\s+/', '', $data['so_dien_thoai']);

        // Tìm/khởi tạo khách theo SĐT trong cơ sở
        $kh = KhachHang::firstOrNew([
            'co_so_id' => $co_so->id,
            'so_dien_thoai' => $sdt,
        ]);
        $kh->ho_ten = $data['ho_ten'];
        $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $gioBatDau = ! empty($data['gio_thuc_hien']) ? $data['gio_thuc_hien'] . ':00' : null;
        $gioKetThuc = ! empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] . ':00' : null;

        // Cảnh báo trùng lịch bác sĩ (tính trước khi tạo để không tự khớp chính nó)
        $canhBaoBacSi = ! empty($data['bac_si_user_id'])
            ? $this->bacSiTrungLich($co_so, (int) $data['bac_si_user_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null)
            : null;

        $booking = Booking::create([
            'co_so_id'      => $co_so->id,
            'khach_hang_id' => $kh->id,
            'phong_id'      => $data['phong_id'],
            'khung_gio_id'  => $data['khung_gio_id'],
            'dich_vu_id'    => $data['dich_vu_id'],
            'bac_si_user_id' => $data['bac_si_user_id'] ?? null,
            'ktv_user_id'   => $data['ktv_user_id'] ?? null,
            'sale_id'       => $data['sale_id'],
            'ngay_dat'      => $data['ngay_dat'],
            'gio_thuc_hien' => $gioBatDau,
            'gio_ket_thuc'  => $gioKetThuc,
            'so_lieu_trinh' => $data['so_lieu_trinh'] ?? null,
            'nguon'         => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'co_tu_van'     => $request->boolean('co_tu_van'),
            'co_kham_cls'   => $request->boolean('co_kham_cls'),
            'ghi_chu'       => $data['ghi_chu'] ?? null,
            'trang_thai'    => 'cho_duyet',
        ]);

        if (! empty($data['menu_ids'])) {
            $booking->menus()->sync($data['menu_ids']);
        }

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã tạo lịch hẹn cho ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBacSi);
    }

    public function update(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('sua_booking');

        $data = $request->validate([
            'ho_ten'        => ['required', 'string', 'max:255'],
            'so_dien_thoai' => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'ngay_dat'      => ['required', 'date', 'after_or_equal:today'],
            'phong_id'      => ['required', Rule::exists('phong', 'id')->where('co_so_id', $co_so->id)],
            'khung_gio_id'  => ['required', Rule::exists('khung_gio', 'id')],
            'gio_thuc_hien' => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'gio_ket_thuc'  => ['nullable', 'regex:/^\d{2}:(00|30)$/'],
            'dich_vu_id'    => ['required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => ['required', Rule::exists('users', 'id')],
            'bac_si_user_id' => ['nullable', Rule::exists('users', 'id')],
            'ktv_user_id'   => ['nullable', Rule::exists('users', 'id')],
            'so_lieu_trinh' => ['nullable', 'string', 'max:50'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'co_tu_van'     => ['nullable', 'boolean'],
            'co_kham_cls'   => ['nullable', 'boolean'],
            'ghi_chu'       => ['nullable', 'string'],
            'menu_ids'      => ['nullable', 'array'],
            'menu_ids.*'    => [Rule::exists('menu', 'id')],
        ], [
            'ho_ten.required'        => 'Vui lòng nhập họ tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'phong_id.required'      => 'Vui lòng chọn phòng.',
            'khung_gio_id.required'  => 'Vui lòng chọn khung giờ.',
            'dich_vu_id.required'    => 'Vui lòng chọn liệu pháp/dịch vụ.',
            'sale_id.required'       => 'Vui lòng chọn sale phụ trách.',
            'gio_thuc_hien.regex'    => 'Phút thực hiện chỉ được là 00 hoặc 30.',
        ]);

        // KTV conflict check (trừ booking hiện tại) - theo khoảng giờ thực
        if (! empty($data['ktv_user_id'])) {
            $ktvBusy = $this->ktvBanKhoangGio($co_so, (int) $data['ktv_user_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null, $booking->id);
            if ($ktvBusy) {
                return back()->withInput()->withErrors([
                    'ktv_user_id' => 'KTV đã được đặt, vui lòng chọn KTV khác.',
                ]);
            }
        }

        // Chặn trùng khung giờ, nhưng bỏ qua chính booking đang sửa
        if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'], $booking->id)) {
            return back()->withInput()->withErrors([
                'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
            ]);
        }

        // Field-level: chỉ cho ghi các trường user được phép sửa.
        $allowed = $this->allowedFieldKeys();
        $can = fn (string $k) => in_array($k, $allowed, true);

        $khFromBk = $booking->khachHang;
        $sdt = $can('so_dien_thoai')
            ? preg_replace('/\s+/', '', $data['so_dien_thoai'])
            : ($khFromBk?->so_dien_thoai ?? '');
        $kh = KhachHang::firstOrNew(['co_so_id' => $co_so->id, 'so_dien_thoai' => $sdt]);
        if ($can('ho_ten'))  $kh->ho_ten = $data['ho_ten'];
        elseif (! $kh->ho_ten) $kh->ho_ten = $khFromBk?->ho_ten ?? '';
        if ($can('email'))   $kh->email = $data['email'] ?? $kh->email;
        $kh->save();

        $map = [
            'phong_id'        => $data['phong_id'],
            'khung_gio_id'    => $data['khung_gio_id'],
            'dich_vu_id'      => $data['dich_vu_id'],
            'bac_si_user_id'  => $data['bac_si_user_id'] ?? null,
            'ktv_user_id'     => $data['ktv_user_id'] ?? null,
            'sale_id'         => $data['sale_id'],
            'ngay_dat'        => $data['ngay_dat'],
            'gio_thuc_hien'   => ! empty($data['gio_thuc_hien']) ? $data['gio_thuc_hien'] . ':00' : null,
            'gio_ket_thuc'    => ! empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] . ':00' : null,
            'so_lieu_trinh'   => $data['so_lieu_trinh'] ?? null,
            'nguon'           => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'ghi_chu'         => $data['ghi_chu'] ?? null,
        ];
        $payload = ['khach_hang_id' => $kh->id];
        foreach ($map as $col => $val) {
            if ($can($col)) $payload[$col] = $val;
        }
        // Hai cờ phụ không phân quyền riêng — gắn vào quyền sửa nguồn.
        if ($can('nguon')) {
            $payload['co_tu_van']   = $request->boolean('co_tu_van');
            $payload['co_kham_cls'] = $request->boolean('co_kham_cls');
        }
        $booking->update($payload);

        if ($can('dich_vu_id')) {
            $booking->menus()->sync($data['menu_ids'] ?? []);
        }

        // Cảnh báo trùng lịch bác sĩ (bỏ qua chính booking đang sửa)
        $canhBaoBacSi = $booking->bac_si_user_id
            ? $this->bacSiTrungLich($co_so, (int) $booking->bac_si_user_id, (string) $booking->ngay_dat, (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id)
            : null;

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã cập nhật lịch hẹn của ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBacSi);
    }

    public function destroy(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('xoa_booking');

        $ten = $booking->khachHang?->ho_ten ?? 'khách';
        $booking->menus()->detach();
        $booking->delete();

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã xóa lịch hẹn của ' . $ten . '.');
    }

    /** Duyệt / bỏ duyệt lịch đặt phòng (chỉ admin). */
    public function duyet(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('duyet_booking');

        $approve = ! $booking->da_duyet;
        $wasRejected = $booking->trang_thai === 'tu_choi';

        // Khi duyệt lại đơn TỪ CHỐI: slot/KTV/BS có thể đã bị đơn khác chiếm trong thời gian chờ.
        // → Check lại các conflict trước khi cho phép duyệt.
        $canhBao = [];
        if ($approve && $wasRejected) {
            if ($this->khungGioDayCho($co_so, (int) $booking->phong_id, (int) $booking->khung_gio_id, (string) $booking->ngay_dat->toDateString(), $booking->id)) {
                return back()->with('error', 'Không duyệt được: khung giờ này đã được đặt kín bởi đơn khác. Vui lòng đổi khung giờ trước khi duyệt.');
            }
            if ($booking->ktv_user_id) {
                $busy = $this->ktvBanKhoangGio($co_so, (int) $booking->ktv_user_id, (string) $booking->ngay_dat->toDateString(), (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id);
                if ($busy) {
                    return back()->with('error', 'Không duyệt được: KTV đã được đặt cho khung giờ này bởi đơn khác.');
                }
            }
            if ($booking->bac_si_user_id) {
                $msg = $this->bacSiTrungLich($co_so, (int) $booking->bac_si_user_id, (string) $booking->ngay_dat->toDateString(), (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id);
                if ($msg) $canhBao[] = $msg;
            }
        }

        $booking->da_duyet = $approve;
        $booking->trang_thai = $approve ? 'da_duyet' : 'cho_duyet';
        $booking->ly_do_tu_choi = null; // duyệt lại thì xóa lý do từ chối cũ
        $booking->save();

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        return back()
            ->with('ok', ($approve ? 'Đã duyệt' : 'Đã bỏ duyệt') . ' lịch hẹn của ' . $ten . '.')
            ->with('warning', implode(' ', $canhBao) ?: null);
    }

    /** Từ chối (không duyệt) lịch đặt phòng kèm lý do (chỉ người có quyền duyệt). */
    public function tuChoi(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('duyet_booking');

        $data = $request->validate([
            'ly_do_tu_choi' => ['required', 'string', 'max:1000'],
        ], [
            'ly_do_tu_choi.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $booking->trang_thai = 'tu_choi';
        $booking->da_duyet = false;
        $booking->ly_do_tu_choi = $data['ly_do_tu_choi'];
        $booking->save();

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        return back()->with('ok', 'Đã từ chối lịch hẹn của ' . $ten . '.');
    }

    /** Đánh dấu đã xong / hoàn tác về đã duyệt. */
    public function xong(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('duyet_booking');

        $done = $booking->trang_thai !== 'da_xong';
        if ($done) {
            $booking->trang_thai = 'da_xong';
            $booking->da_duyet = true;
        } else {
            $booking->trang_thai = 'da_duyet';
            $booking->da_duyet = true;
        }
        $booking->save();

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        return back()->with('ok', ($done ? 'Đã hoàn thành' : 'Đã chuyển lại "Đã duyệt"') . ' lịch hẹn của ' . $ten . '.');
    }

}
