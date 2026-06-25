<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
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

        return view('longevity.create', $this->formData($co_so, 'phong_kham') + [
            'bk' => null,
            'allowedFields' => null,
            'loaiDatLich' => 'phong_kham',
        ]);
    }

    /** Form đặt lịch dịch vụ - chỉ Phòng + KTV + Dịch vụ (không có BS). */
    public function createDichVu(CoSo $co_so)
    {
        $this->authorizePerm('them_booking');

        return view('longevity.create', $this->formData($co_so, 'phong_dich_vu') + [
            'bk' => null,
            'allowedFields' => null,
            'loaiDatLich' => 'dich_vu',
        ]);
    }

    /** Store cho đặt lịch dịch vụ - ép loai_dat_lich + bỏ BS check. */
    public function storeDichVu(CoSo $co_so, Request $request)
    {
        $request->merge(['loai_dat_lich' => 'dich_vu', 'bac_si_user_id' => null]);
        return $this->store($co_so, $request);
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

    /** Dữ liệu dùng chung cho form tạo / sửa. $kieuPhong: 'phong_kham' | 'phong_dich_vu' | null (cả 2). */
    private function formData(CoSo $co_so, ?string $kieuPhong = null): array
    {
        $co_so->load([
            'phongs' => fn ($q) => $q
                ->where('trang_thai', 'hoat_dong')
                ->when($kieuPhong, fn ($qq) => $qq->where('kieu_phong', $kieuPhong))
                ->with('khungGios'),
        ]);

        // Dịch vụ (= Liệu pháp): gồm cả của cơ sở + dùng chung.
        // Lọc theo loại: phong_kham → la_dich_vu=false; phong_dich_vu → la_dich_vu=true
        $dichVus = DichVu::where('active', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhereNull('co_so_id'))
            ->when($kieuPhong === 'phong_kham', fn ($q) => $q->where('la_dich_vu', false))
            ->when($kieuPhong === 'phong_dich_vu', fn ($q) => $q->where('la_dich_vu', true))
            ->orderBy('ten')->get();
        $co_so->setRelation('dichVus', $dichVus);

        // Menu: tương tự, gồm cả của cơ sở + dùng chung
        $menus = \App\Models\Menu::where('active', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhereNull('co_so_id'))
            ->orderBy('ten')->get();
        $co_so->setRelation('menus', $menus);

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

        // Nhân viên Sale: chỉ lấy các vai trò mang tính chất sale/lễ tân/nhân viên
        // (tránh lẫn bác sĩ / KTV / admin vào dropdown sale)
        $saleVaiTroIds = VaiTro::whereIn('ma', ['tu_van_vien', 'le_tan', 'nhan_vien'])->pluck('id');
        $sales = User::where('co_so_id', $co_so->id)
            ->whereIn('vai_tro_id', $saleVaiTroIds)
            ->where('is_admin', false)
            ->orderBy('name')->get();

        // Khung giờ + meta theo từng phòng
        $slots = $co_so->phongs->mapWithKeys(fn ($p) => [
            $p->id => [
                'kieu_phong' => $p->kieu_phong,
                'phut_moi_khach' => $p->phut_moi_khach,
                'ktv_mac_dinh_id' => $p->ktv_mac_dinh_id,
                'khung_gios' => $p->khungGios->map(fn ($k) => [
                    'id' => $k->id,
                    'nhan' => $k->nhan,
                    'bd' => substr($k->gio_bat_dau, 0, 5),
                    'kt' => substr($k->gio_ket_thuc, 0, 5),
                ])->values(),
            ],
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
    // Nếu có ?bac_si_id và ?dich_vu_id → mỗi khung kèm thêm sub_slots tách theo phút BS
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

        $except = $request->query('except');
        $counts = Booking::where('co_so_id', $co_so->id)
            ->where('phong_id', $phong->id)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->selectRaw('khung_gio_id, COUNT(*) as c')
            ->groupBy('khung_gio_id')
            ->pluck('c', 'khung_gio_id');

        // Tính phút cho sub-slot:
        // - Phòng dịch vụ: phut_moi_khach của phòng
        // - Phòng khám có BS + DV: theo phút BS
        $bsId = $request->query('bac_si_id');
        $dvId = $request->query('dich_vu_id');
        $phutMoi = null;
        $bs = $bsId ? User::find($bsId) : null;
        $dv = $dvId ? DichVu::find($dvId) : null;
        if ($phong->kieu_phong === 'phong_dich_vu' && $phong->phut_moi_khach) {
            $phutMoi = (int) $phong->phut_moi_khach;
        } elseif ($bs && $dv) {
            $phutMoi = $this->phutCanCuaBookingBS($bs, $dv);
        }

        $ngayStr = $ngay instanceof \DateTimeInterface ? $ngay->format('Y-m-d') : (string) $ngay;

        return response()->json([
            'capacity' => $capacity,
            'phut_moi' => $phutMoi,
            'slots' => $phong->khungGios->map(function ($k) use ($counts, $capacity, $phutMoi, $bs, $bsId, $phong, $ngayStr, $except) {
                $booked = (int) ($counts[$k->id] ?? 0);
                $sub = [];

                if ($phutMoi) {
                    $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
                    $s = $toMin(substr($k->gio_bat_dau, 0, 5));
                    $e = $toMin(substr($k->gio_ket_thuc, 0, 5));
                    for ($t = $s; $t + $phutMoi <= $e; $t += $phutMoi) {
                        $subBd = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
                        $subKt = sprintf('%02d:%02d', intdiv($t + $phutMoi, 60), ($t + $phutMoi) % 60);
                        if ($bs && $bsId) {
                            $busy = $this->bacSiPhutDaDung((int) $bsId, $ngayStr, $t, $t + $phutMoi, $except ? (int) $except : null) > 0;
                        } else {
                            // Phòng dịch vụ: check overlap với booking khác trong cùng phòng + khung
                            $busy = $this->phongDichVuBan($phong->id, (int) $k->id, $ngayStr, $t, $t + $phutMoi, $capacity, $except ? (int) $except : null);
                        }
                        $sub[] = [
                            'bd' => $subBd,
                            'kt' => $subKt,
                            'full' => $busy,
                        ];
                    }
                }

                return [
                    'id' => $k->id,
                    'nhan' => $k->nhan,
                    'bd' => substr($k->gio_bat_dau, 0, 5),
                    'kt' => substr($k->gio_ket_thuc, 0, 5),
                    'gio' => (int) substr($k->gio_bat_dau, 0, 2),
                    'booked' => $booked,
                    'capacity' => $capacity,
                    'full' => $booked >= $capacity,
                    'sub_slots' => $sub,
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

    /**
     * Capacity bác sĩ trong khung giờ tính theo PHÚT.
     * Mỗi khung giờ = (kết_thúc - bắt_đầu) phút (thường 60).
     * Mỗi booking chiếm `dich_vu.thoi_gian_phut` phút.
     * Trả về số phút còn lại của BS trong khung giờ + ngày đó.
     */
    /**
     * Số phút 1 booking chiếm của bác sĩ:
     *  - dich_vu.thuoc_nhom = 'tu_van' → BS.phut_tu_van
     *  - 'kham_ls' → BS.phut_kham_ls
     *  - 'khac' → dich_vu.thoi_gian_phut
     */
    private function phutCanCuaBookingBS(?User $bs, ?DichVu $dv): int
    {
        if (! $dv) return 0;
        return match ($dv->thuoc_nhom) {
            'tu_van'  => (int) ($bs?->phut_tu_van ?? 30),
            'kham_ls' => (int) ($bs?->phut_kham_ls ?? 5),
            default   => (int) ($dv->thoi_gian_phut ?? 30),
        };
    }

    /** Khoảng giờ thực [bd, kt] của 1 booking (phút trong ngày). */
    private function khoangGioBooking(Booking $b): ?array
    {
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        $bd = $b->gio_thuc_hien ?: $b->khungGio?->gio_bat_dau;
        $kt = $b->gio_ket_thuc ?: $b->khungGio?->gio_ket_thuc;
        $s = $toMin($bd ? substr($bd, 0, 5) : null);
        if ($s === null) return null;
        $e = $toMin($kt ? substr($kt, 0, 5) : null) ?? ($s + 60);
        return [$s, $e];
    }

    /**
     * Tổng phút BS đã chiếm trong khoảng [s, e] (phút trong ngày), ngày $ngay,
     * tính XUYÊN MỌI CƠ SỞ (vì BS chỉ có 1 cơ thể).
     */
    private function bacSiPhutDaDung(int $bacSiId, string $ngay, int $s, int $e, ?int $exceptId = null): int
    {
        $bookings = Booking::with(['dichVu', 'khungGio'])
            ->where('bac_si_user_id', $bacSiId)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get();

        $bs = User::find($bacSiId);
        $total = 0;
        foreach ($bookings as $b) {
            $r = $this->khoangGioBooking($b);
            if (! $r) continue;
            [$os, $oe] = $r;
            // Overlap với [s, e]?
            if ($s < $oe && $os < $e) {
                $total += $this->phutCanCuaBookingBS($bs, $b->dichVu);
            }
        }
        return $total;
    }

    private function khungGioCapacity(int $khungGioId): int
    {
        $kg = KhungGio::find($khungGioId);
        if (! $kg) return 60;
        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $c = $toMin(substr($kg->gio_ket_thuc, 0, 5)) - $toMin(substr($kg->gio_bat_dau, 0, 5));
        return $c > 0 ? $c : 60;
    }

    /** Khoảng [s, e] của booking mới đang validate (từ gio_thuc_hien/gio_ket_thuc hoặc khung_gio_id). */
    private function khoangGioBookingMoi(array $data): ?array
    {
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        if (! empty($data['gio_thuc_hien']) && ! empty($data['gio_ket_thuc'])) {
            return [$toMin($data['gio_thuc_hien']), $toMin($data['gio_ket_thuc'])];
        }
        if (! empty($data['khung_gio_id'])) {
            return $this->khoangGioCuaKhung((int) $data['khung_gio_id']);
        }
        return null;
    }

    /**
     * Phòng dịch vụ có "slot song song" = so_slot_toi_da. Trong khoảng [s, e]:
     * - Đếm booking khác có overlap với [s, e] trong cùng phòng + khung_gio
     * - Nếu >= so_slot_toi_da → bận
     */
    private function phongDichVuBan(int $phongId, int $khungGioId, string $ngay, int $s, int $e, int $capacity, ?int $exceptId = null): bool
    {
        $bookings = Booking::with('khungGio')
            ->where('phong_id', $phongId)
            ->where('khung_gio_id', $khungGioId)
            ->whereDate('ngay_dat', $ngay)
            ->where('trang_thai', '!=', 'tu_choi')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get();
        $count = 0;
        foreach ($bookings as $b) {
            $r = $this->khoangGioBooking($b);
            if (! $r) continue;
            [$os, $oe] = $r;
            if ($s < $oe && $os < $e) $count++;
        }
        return $count >= $capacity;
    }

    /**
     * Kiểm gio_thuc_hien / gio_ket_thuc có nằm trong khung_gio cha không.
     * Trả về array errors (theo field) hoặc [] nếu hợp lệ.
     */
    private function validateGioTrongKhung(array $data): array
    {
        $errors = [];
        $khoang = $this->khoangGioCuaKhung((int) ($data['khung_gio_id'] ?? 0));
        if (! $khoang) return $errors;

        [$kgStart, $kgEnd] = $khoang;
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;

        $bd = $toMin($data['gio_thuc_hien'] ?? null);
        $kt = $toMin($data['gio_ket_thuc'] ?? null);
        $fmt = fn (int $m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        if ($bd !== null && $bd < $kgStart) {
            $errors['gio_thuc_hien'] = 'Giờ thực hiện phải >= ' . $fmt($kgStart) . ' (đầu khung).';
        }
        if ($bd !== null && $bd >= $kgEnd) {
            $errors['gio_thuc_hien'] = 'Giờ thực hiện phải nhỏ hơn ' . $fmt($kgEnd) . ' (cuối khung).';
        }
        if ($kt !== null && $kt > $kgEnd) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải <= ' . $fmt($kgEnd) . ' (cuối khung).';
        }
        if ($bd !== null && $kt !== null && $kt <= $bd) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ thực hiện.';
        }
        return $errors;
    }

    /** Khoảng giờ [bd, kt] của 1 khung_gio (phút trong ngày). */
    private function khoangGioCuaKhung(int $khungGioId): ?array
    {
        $kg = KhungGio::find($khungGioId);
        if (! $kg) return null;
        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        return [
            $toMin(substr($kg->gio_bat_dau, 0, 5)),
            $toMin(substr($kg->gio_ket_thuc, 0, 5)),
        ];
    }

    /** Lỗi nếu BS không đủ capacity / không nhận loại dịch vụ. Tính cross-cơ sở. */
    private function checkBacSiCapacity(int $bacSiId, int $khungGioId, int $dichVuId, string $ngay, ?int $exceptId = null): ?string
    {
        $dv = DichVu::find($dichVuId);
        if (! $dv) return null;

        $bs = User::find($bacSiId);
        if ($bs && $dv->thuoc_nhom === 'tu_van' && ! $bs->nhan_tu_van) {
            return 'Bác sĩ này không nhận tư vấn. Vui lòng chọn bác sĩ khác.';
        }
        if ($bs && $dv->thuoc_nhom === 'kham_ls' && ! $bs->nhan_kham_ls) {
            return 'Bác sĩ này không nhận thăm khám lâm sàng. Vui lòng chọn bác sĩ khác.';
        }

        $khoang = $this->khoangGioCuaKhung($khungGioId);
        if (! $khoang) return null;
        [$s, $e] = $khoang;
        $capacity = $e - $s;
        if ($capacity <= 0) $capacity = 60;

        $used = $this->bacSiPhutDaDung($bacSiId, $ngay, $s, $e, $exceptId);
        $need = $this->phutCanCuaBookingBS($bs, $dv);
        $remain = max(0, $capacity - $used);

        if ($remain < $need) {
            return "Bác sĩ này đã hết giờ rảnh trong khoảng {$this->formatKhoang($s, $e)} ngày này (còn {$remain} phút, cần {$need} phút). BS có thể đang bận ở cơ sở khác hoặc đã kín lịch.";
        }
        return null;
    }

    private function formatKhoang(int $s, int $e): string
    {
        return sprintf('%02d:%02d-%02d:%02d', intdiv($s, 60), $s % 60, intdiv($e, 60), $e % 60);
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

    /**
     * Trả về danh sách BS với trạng thái khả dụng cho 1 khung giờ + dịch vụ + ngày.
     * Dùng để render dropdown ở form (disable BS không đủ giờ).
     */
    public function checkBacSi(CoSo $co_so, Request $request)
    {
        $khungGioId = (int) $request->query('khung_gio_id');
        $dichVuId   = (int) $request->query('dich_vu_id');
        $ngay       = $request->query('ngay');
        $exceptId   = $request->query('except');

        if (! $khungGioId || ! $dichVuId || ! $ngay) {
            return response()->json(['list' => []]);
        }

        $vrIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $bacSis = User::whereIn('vai_tro_id', $vrIds)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('is_tu_van', true))
            ->orderBy('name')->get();

        $list = $bacSis->map(function ($bs) use ($co_so, $khungGioId, $dichVuId, $ngay, $exceptId) {
            $err = $this->checkBacSiCapacity($bs->id, $khungGioId, $dichVuId, $ngay, $exceptId ? (int) $exceptId : null);
            return [
                'id'      => $bs->id,
                'name'    => $bs->ten_day_du,
                'available' => $err === null,
                'reason'  => $err,
            ];
        });

        return response()->json(['list' => $list]);
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
            'gio_thuc_hien' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'gio_ket_thuc'  => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'dich_vu_id'    => [$request->input('loai_dat_lich') === 'dich_vu' ? 'nullable' : 'required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => [$request->input('loai_dat_lich') === 'dich_vu' ? 'nullable' : 'required', Rule::exists('users', 'id')],
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
            'gio_thuc_hien.regex'    => 'Giờ thực hiện phải là HH:MM.',
        ]);

        // Giờ thực hiện / kết thúc phải nằm trong khung_gio cha
        $gioErrors = $this->validateGioTrongKhung($data);
        if (! empty($gioErrors)) {
            return back()->withInput()->withErrors($gioErrors);
        }

        // KTV conflict check - theo khoảng giờ thực, chặn cả khi ở phòng khác
        if (! empty($data['ktv_user_id'])) {
            $ktvBusy = $this->ktvBanKhoangGio($co_so, (int) $data['ktv_user_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($ktvBusy) {
                return back()->withInput()->withErrors([
                    'ktv_user_id' => 'KTV đã được đặt, vui lòng chọn KTV khác.',
                ]);
            }
        }

        // Capacity bác sĩ theo phút (nhận cờ + thời lượng dịch vụ) - chỉ check khi có cả BS và dịch vụ
        if (! empty($data['bac_si_user_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['bac_si_user_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat']);
            if ($err) {
                return back()->withInput()->withErrors(['bac_si_user_id' => $err]);
            }
        }

        // Chặn slot phòng:
        // - Phòng dịch vụ: check overlap theo khoảng giờ thực (vì chia sub-slot theo phút)
        // - Phòng khám: check tổng số booking ≤ so_slot_toi_da
        $phong = Phong::find($data['phong_id']);
        if ($phong && $phong->kieu_phong === 'phong_dich_vu') {
            $khoang = $this->khoangGioBookingMoi($data);
            if ($khoang) {
                $capacity = max(1, (int) $phong->so_slot_toi_da);
                if ($this->phongDichVuBan((int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'], $khoang[0], $khoang[1], $capacity)) {
                    return back()->withInput()->withErrors([
                        'khung_gio_id' => 'Khoảng giờ này tại phòng dịch vụ đã được đặt. Vui lòng chọn giờ khác.',
                    ]);
                }
            }
        } else {
            if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'])) {
                return back()->withInput()->withErrors([
                    'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
                ]);
            }
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
            'loai_dat_lich' => in_array($request->input('loai_dat_lich'), ['phong_kham', 'dich_vu'], true) ? $request->input('loai_dat_lich') : 'phong_kham',
            'khach_hang_id' => $kh->id,
            'phong_id'      => $data['phong_id'],
            'khung_gio_id'  => $data['khung_gio_id'],
            'dich_vu_id'    => $data['dich_vu_id'] ?? null,
            'bac_si_user_id' => $data['bac_si_user_id'] ?? null,
            'ktv_user_id'   => $data['ktv_user_id'] ?? null,
            'sale_id'       => $data['sale_id'] ?? null,
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
            'dich_vu_id'    => [$request->input('loai_dat_lich') === 'dich_vu' ? 'nullable' : 'required', Rule::exists('dich_vu', 'id')],
            'sale_id'       => [$request->input('loai_dat_lich') === 'dich_vu' ? 'nullable' : 'required', Rule::exists('users', 'id')],
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
            'gio_thuc_hien.regex'    => 'Giờ thực hiện phải là HH:MM.',
        ]);

        // Giờ thực hiện / kết thúc phải nằm trong khung_gio cha
        $gioErrors = $this->validateGioTrongKhung($data);
        if (! empty($gioErrors)) {
            return back()->withInput()->withErrors($gioErrors);
        }

        // KTV conflict check (trừ booking hiện tại) - theo khoảng giờ thực
        if (! empty($data['ktv_user_id'])) {
            $ktvBusy = $this->ktvBanKhoangGio($co_so, (int) $data['ktv_user_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null, $booking->id);
            if ($ktvBusy) {
                return back()->withInput()->withErrors([
                    'ktv_user_id' => 'KTV đã được đặt, vui lòng chọn KTV khác.',
                ]);
            }
        }

        // Capacity bác sĩ (loại trừ booking hiện tại) - chỉ check khi có cả BS và dịch vụ
        if (! empty($data['bac_si_user_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['bac_si_user_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat'], $booking->id);
            if ($err) {
                return back()->withInput()->withErrors(['bac_si_user_id' => $err]);
            }
        }

        // Slot phòng (bỏ qua booking đang sửa)
        $phong = Phong::find($data['phong_id']);
        if ($phong && $phong->kieu_phong === 'phong_dich_vu') {
            $khoang = $this->khoangGioBookingMoi($data);
            if ($khoang) {
                $capacity = max(1, (int) $phong->so_slot_toi_da);
                if ($this->phongDichVuBan((int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'], $khoang[0], $khoang[1], $capacity, $booking->id)) {
                    return back()->withInput()->withErrors([
                        'khung_gio_id' => 'Khoảng giờ này tại phòng dịch vụ đã được đặt. Vui lòng chọn giờ khác.',
                    ]);
                }
            }
        } else {
            if ($this->khungGioDayCho($co_so, (int) $data['phong_id'], (int) $data['khung_gio_id'], $data['ngay_dat'], $booking->id)) {
                return back()->withInput()->withErrors([
                    'khung_gio_id' => 'Khung giờ này đã được đặt kín cho ngày đã chọn. Vui lòng chọn khung giờ khác.',
                ]);
            }
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
                $err = $this->checkBacSiCapacity((int) $booking->bac_si_user_id, (int) $booking->khung_gio_id, (int) $booking->dich_vu_id, (string) $booking->ngay_dat->toDateString(), $booking->id);
                if ($err) {
                    return back()->with('error', 'Không duyệt được: '.$err);
                }
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
