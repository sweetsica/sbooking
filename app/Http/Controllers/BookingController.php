<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\Ktv;
use App\Models\LichHen;
use App\Models\LichLamViec;
use App\Models\NgayNghi;
use App\Models\PhanQuyen;
use App\Models\Phong;
use App\Models\User;
use App\Models\VaiTro;
use App\Notifications\LichNotification;
use App\Services\NotificationRecipientResolver;
use App\Support\LichEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    use AuthorizesByPhanQuyen;
    public function create(CoSo $co_so, Request $request)
    {
        // 2026-09-04 — Ép luồng tạo booking đi qua Datasource để lead/KPI/report đồng bộ.
        // Form store() cũng abort(410) — defense-in-depth với form cache.
        return view('longevity.booking-blocked', ['coSo' => $co_so]);
    }

    /** Form đặt lịch dịch vụ — CHẶN, chuyển sang Datasource (từ 2026-09-04). */
    public function createDichVu(CoSo $co_so, Request $request)
    {
        return view('longevity.booking-blocked', ['coSo' => $co_so]);
    }

    /** Lấy prefill khách hàng từ query (SCRM gửi sang khi mở form). */
    private function prefillFromQuery(Request $request): array
    {
        return [
            'ho_ten'        => trim((string) $request->query('ho_ten', '')),
            'so_dien_thoai' => trim((string) $request->query('so_dien_thoai', '')),
            'email'         => trim((string) $request->query('email', '')),
            'khach_ma'      => trim((string) $request->query('khach_ma', '')),
        ];
    }

    /**
     * Chỉ chấp nhận return_url có host nằm trong whitelist services.scrm.callback_hosts.
     * Trả về URL đã chuẩn hoá hoặc null.
     */
    private function safeReturnUrl(?string $url): ?string
    {
        if (! $url) return null;

        $parts = parse_url($url);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        if (! in_array($parts['scheme'], ['http', 'https'], true)) return null;

        // Ưu tiên setting DB (admin sửa qua UI Thiết lập › Kết nối SCRM), fallback env.
        $dbList = \App\Models\AppSetting::get('scrm_callback_hosts');
        $hosts = $dbList
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $dbList))))
            : (array) config('services.scrm.callback_hosts', []);
        $hostPort = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (! in_array($parts['host'], $hosts, true) && ! in_array($hostPort, $hosts, true)) {
            return null;
        }

        return $url;
    }

    /** Store cho đặt lịch dịch vụ — CHẶN từ 2026-09-04. */
    public function storeDichVu(CoSo $co_so, Request $request)
    {
        abort(410, 'Tạo booking đã chuyển sang Datasource. Vui lòng mở SCRM để tạo lịch — sbooking không nhận form tạo mới nữa.');
    }

    public function edit(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizeEditBooking($booking);

        // Phase 6.25.C (local): Sale tiếp nhận chỉ được xem + ghi trạng thái, không mở form Sửa.
        $u = auth()->user();
        $isTiepDon = ($booking->tiep_don_user_id === $u->id) || ($booking->sale_id === $u->id);
        if ($isTiepDon && ! $u->is_admin) {
            return redirect("/{$co_so->slug}/xem-dat-phong/{$booking->id}")
                ->with('warn', 'Sale tiếp nhận chỉ xem + ghi trạng thái. Không được sửa info khách.');
        }

        $booking->load(['khachHang', 'menus']);

        return view('longevity.create', $this->formData($co_so) + [
            'bk' => $booking,
            'allowedFields' => $this->allowedFieldKeys(),
            'canPhanHoi' => $this->hasPerm('ghi_chu_phan_hoi'),
            // Local (Phase C1.b rev8): gate block "Trạng thái lịch hẹn" + bình luận trong form edit.
            'canTrangThai' => $this->hasPerm('cap_nhat_trang_thai_khach'),
            'canBinhLuan' => $this->hasPerm('binh_luan_booking'),
            'isAdmin' => (bool) $u?->is_admin,
        ]);
    }

    /** Dữ liệu dùng chung cho form tạo / sửa. $kieuPhong: 'kham_ls' | 'phong_dich_vu' | null (cả 2). */
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
            ->where('co_so_id', $co_so->id)
            ->when($kieuPhong === 'kham_ls', fn ($q) => $q->where('la_dich_vu', false))
            ->when($kieuPhong === 'phong_dich_vu', fn ($q) => $q->where('la_dich_vu', true))
            ->orderBy('ten')->get();
        $co_so->setRelation('dichVus', $dichVus);

        // Menu: tương tự, gồm cả của cơ sở + dùng chung
        $menus = \App\Models\Menu::where('active', true)
            ->where('co_so_id', $co_so->id)
            ->orderBy('ten')->get();
        $co_so->setRelation('menus', $menus);

        $vrKtv = VaiTro::where('ma', 'ktv')->first();

        // Bác sĩ = DANH MỤC bac_si của cơ sở (hoặc xuất hiện mọi cơ sở).
        // Bác sĩ được gán vào phòng ở Thiết lập → Phòng; form lọc theo phòng khi chọn.
        $bacSis = BacSi::where('active', true)
            ->where(fn ($q) => $q->where('co_so_id', $co_so->id)->orWhere('xuat_hien_moi_co_so', true))
            ->orderBy('ten')->get();

        // KTV thuộc cơ sở (từ bảng danh mục ktv, không phải users)
        $ktvs = Ktv::where('co_so_id', $co_so->id)
            ->where('active', true)
            ->orderBy('ten')->get();

        // Nhân viên Sale: chỉ lấy các vai trò mang tính chất sale/lễ tân/nhân viên
        // (tránh lẫn bác sĩ / KTV / admin vào dropdown sale)
        $saleVaiTroIds = VaiTro::whereIn('ma', ['tu_van_vien', 'le_tan', 'nhan_vien', 'dn_full_flow'])->pluck('id');
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
            // 2026-08-07: list cơ sở cho dropdown "Địa điểm" — user đổi cơ sở → reload sang slug khác
            //   (chống nhầm cơ sở khi tele HN book cho ĐN).
            'allCoSos' => CoSo::orderBy('ten')->get(['id', 'slug', 'ten']),
        ];
    }

    // Trả về danh sách SLOT bookable của 1 phòng theo ngày + liệu pháp.
    // Thời lượng slot = phut_moi_khach (phòng dịch vụ) hoặc dich_vu.thoi_gian_phut (phòng khám).
    // Khi slot dài hơn khung giờ nền (vd tư vấn 30' trên khung 5') → GỘP khung: bước theo thời lượng
    // trên toàn dải giờ mở của phòng, mỗi slot map tới khung_gio_id chứa điểm bắt đầu.
    public function khungGio(CoSo $co_so, Request $request)
    {
        $phong = Phong::where('co_so_id', $co_so->id)
            ->where('id', $request->query('phong_id'))
            ->with('khungGios')->first();

        if (! $phong || $phong->khungGios->isEmpty()) {
            return response()->json(['phut_moi' => null, 'slots' => []]);
        }

        $ngay = $request->date('ngay') ?? now();
        $ngayStr = $ngay instanceof \DateTimeInterface ? $ngay->format('Y-m-d') : (string) $ngay;
        $capacity = max(1, (int) $phong->so_slot_toi_da);
        $except = $request->query('except') ? (int) $request->query('except') : null;

        // Thời lượng slot: phòng dịch vụ → phut_moi_khach; phòng khám → thoi_gian_phut của liệu pháp.
        $dv = $request->query('dich_vu_id') ? DichVu::find($request->query('dich_vu_id')) : null;
        $phutMoi = null;
        if ($phong->kieu_phong === 'phong_dich_vu' && $phong->phut_moi_khach) {
            $phutMoi = (int) $phong->phut_moi_khach;
        } elseif ($dv && $dv->thoi_gian_phut) {
            $phutMoi = (int) $dv->thoi_gian_phut;
        }

        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $khungs = $phong->khungGios->sortBy('thu_tu')->values();

        // Chưa chọn liệu pháp → trả khung giờ gốc (mỗi khung 1 slot).
        if (! $phutMoi) {
            $slots = $khungs->map(fn ($k) => [
                'id'   => $k->id,
                'bd'   => substr($k->gio_bat_dau, 0, 5),
                'kt'   => substr($k->gio_ket_thuc, 0, 5),
                'full' => false,
            ])->values();

            return response()->json(['phut_moi' => null, 'slots' => $slots]);
        }

        // Khung chứa mốc bắt đầu slot (slot có thể trải nhiều khung khi gộp).
        $khungChua = function (int $t) use ($khungs, $toMin) {
            $found = null;
            foreach ($khungs as $k) {
                if ($toMin(substr($k->gio_bat_dau, 0, 5)) <= $t) {
                    $found = $k;
                } else {
                    break;
                }
            }

            return $found ?? $khungs->first();
        };

        $open  = $toMin(substr($khungs->first()->gio_bat_dau, 0, 5));
        $close = $toMin(substr($khungs->last()->gio_ket_thuc, 0, 5));

        $slots = [];
        for ($t = $open; $t + $phutMoi <= $close; $t += $phutMoi) {
            $kg = $khungChua($t);
            $s = $t;
            $e = $t + $phutMoi;
            // Bỏ qua slot chạm giờ nghỉ trưa (12:00–13:30) — không cho đặt.
            if ($this->chamGioTrua($s, $e)) {
                continue;
            }
            // Phòng dịch vụ: đầy khi đủ số slot song song. Phòng khám: để bước chọn bác sĩ lọc.
            $full = $phong->kieu_phong === 'phong_dich_vu'
                ? $this->phongDichVuBan($phong->id, (int) $kg->id, $ngayStr, $s, $e, $capacity, $except)
                : false;

            $slots[] = [
                'id'   => $kg->id,
                'bd'   => sprintf('%02d:%02d', intdiv($s, 60), $s % 60),
                'kt'   => sprintf('%02d:%02d', intdiv($e, 60), $e % 60),
                'full' => $full,
            ];
        }

        return response()->json(['phut_moi' => $phutMoi, 'slots' => $slots]);
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
            ->giuCho()
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
    private function phutCanCuaBookingBS(?BacSi $bs, ?DichVu $dv): int
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
    private function bacSiPhutDaDung(int $bacSiId, string $ngay, int $s, int $e, ?int $exceptId = null, ?BacSi $bs = null): int
    {
        $bookings = Booking::with(['dichVu', 'khungGio'])
            ->where('bac_si_id', $bacSiId)
            ->whereDate('ngay_dat', $ngay)
            ->giuCho()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get();

        $bs ??= BacSi::find($bacSiId); // dùng lại nếu caller đã có (tránh N+1 trong vòng lặp)
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
            ->giuCho()
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
    // 2026-08-19: siết boundary — gio_thuc_hien/gio_ket_thuc PHẢI nằm trong khung_gio đã chọn
    //   (không cho phép trải nhiều khung). Trước đây chỉ bó theo giờ mở cửa của phòng →
    //   khung 09:15-09:20 mà end 09:40 vẫn qua, phi thực tế. Nếu DV dài hơn khung → user chọn khung dài hơn.
    private function validateGioTrongKhung(array $data): array
    {
        $errors = [];
        $kg = KhungGio::with('phong.khungGios')->find((int) ($data['khung_gio_id'] ?? 0));
        $khungs = $kg?->phong?->khungGios;
        if (! $kg || ! $khungs || $khungs->isEmpty()) return $errors;

        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        $kgBd  = $toMin(substr($kg->gio_bat_dau, 0, 5));
        $kgKt  = $toMin(substr($kg->gio_ket_thuc, 0, 5));

        $bd = $toMin($data['gio_thuc_hien'] ?? null);
        $kt = $toMin($data['gio_ket_thuc'] ?? null);
        $fmt = fn (int $m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        if ($bd !== null && $bd < $kgBd) {
            $errors['gio_thuc_hien'] = 'Giờ thực hiện phải >= ' . $fmt($kgBd) . ' (đầu khung giờ).';
        }
        if ($bd !== null && $bd >= $kgKt) {
            $errors['gio_thuc_hien'] = 'Giờ thực hiện phải nhỏ hơn ' . $fmt($kgKt) . ' (cuối khung giờ).';
        }
        if ($kt !== null && $kt > $kgKt) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải <= ' . $fmt($kgKt) . ' (cuối khung giờ ' . $fmt($kgBd) . '-' . $fmt($kgKt) . '). Chọn khung giờ dài hơn nếu DV cần nhiều thời gian.';
        }
        if ($bd !== null && $kt !== null && $kt <= $bd) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ thực hiện.';
        }
        // Cấm đặt vào giờ nghỉ trưa (giữa ca Sáng và ca Chiều).
        if ($bd !== null && $this->chamGioTrua($bd, $kt)) {
            $truaBd = $toMin(LichLamViec::CA['sang']['kt']);
            $truaKt = $toMin(LichLamViec::CA['chieu']['bd']);
            $errors['gio_thuc_hien'] = 'Không thể đặt vào giờ nghỉ trưa (' . $fmt($truaBd) . '–' . $fmt($truaKt) . ').';
        }
        return $errors;
    }

    /**
     * Khoảng [s, e) (phút trong ngày) có chạm giờ nghỉ trưa không?
     * Giờ trưa = giữa kết thúc ca Sáng (12:00) và bắt đầu ca Chiều (13:30) — không được đặt lịch.
     * Nếu chỉ có giờ bắt đầu ($e null) → coi là 1 mốc, chặn khi mốc rơi vào [12:00, 13:30).
     */
    private function chamGioTrua(int $s, ?int $e): bool
    {
        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $truaBd = $toMin(LichLamViec::CA['sang']['kt']);   // 12:00
        $truaKt = $toMin(LichLamViec::CA['chieu']['bd']);  // 13:30

        return $e !== null
            ? ($s < $truaKt && $e > $truaBd)
            : ($s >= $truaBd && $s < $truaKt);
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

    /**
     * Lỗi nếu BS không nhận loại dịch vụ HOẶC đã có lịch trùng khoảng giờ booking. Tính cross-cơ sở.
     * Khoảng giờ ưu tiên [gioBatDau, gioKetThuc] (slot thực, có thể trải nhiều khung); fallback khung giờ.
     */
    private function checkBacSiCapacity(int $bacSiId, int $khungGioId, int $dichVuId, string $ngay, ?int $exceptId = null, ?string $gioBatDau = null, ?string $gioKetThuc = null): ?string
    {
        $dv = DichVu::find($dichVuId);
        if (! $dv) return null;

        $bs = BacSi::find($bacSiId);
        if ($bs && $dv->thuoc_nhom === 'tu_van' && ! $bs->nhan_tu_van) {
            return 'Bác sĩ này không nhận tư vấn. Vui lòng chọn bác sĩ khác.';
        }
        if ($bs && $dv->thuoc_nhom === 'kham_ls' && ! $bs->nhan_kham_ls) {
            return 'Bác sĩ này không nhận thăm khám lâm sàng. Vui lòng chọn bác sĩ khác.';
        }

        $khoang = $this->khoangGioCuaKhung($khungGioId);
        if (! $khoang) return null;
        $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $s = $gioBatDau ? $toMin($gioBatDau) : $khoang[0];
        $e = $gioKetThuc ? $toMin($gioKetThuc) : $khoang[1];
        if ($e <= $s) { [$s, $e] = $khoang; }

        // Khung đã chọn phải đủ thời lượng bác sĩ cần cho loại dịch vụ.
        $can = $this->phutCanCuaBookingBS($bs, $dv);
        if (($e - $s) < $can) {
            return "Bác sĩ này cần {$can} phút cho loại dịch vụ này, nhưng khung đã chọn chỉ " . ($e - $s) . " phút. Vui lòng chọn khung dài hơn hoặc bác sĩ khác.";
        }

        // Bác sĩ chỉ phục vụ 1 khách/lúc → bận nếu có lịch nào đè khoảng [s, e].
        if ($this->bacSiPhutDaDung($bacSiId, $ngay, $s, $e, $exceptId) > 0) {
            return "Bác sĩ này đã có lịch trùng giờ trong khoảng {$this->formatKhoang($s, $e)} ngày này. Vui lòng chọn giờ khác hoặc bác sĩ khác.";
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
            ->where('bac_si_id', $bacSiId)
            ->whereDate('ngay_dat', $ngay)
            ->giuCho()
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
                $bs = BacSi::find($bacSiId);

                return 'Lưu ý: ' . ($bs?->ten_day_du ?? 'Bác sĩ') . ' đã có lịch lúc '
                    . substr($obd, 0, 5) . ' tại ' . ($o->phong?->ten ?? 'phòng khác')
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
            ->giuCho()
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
    // Danh sách bác sĩ cho 1 slot: bác sĩ CỦA PHÒNG (+ bác sĩ tư vấn global nếu là tư vấn),
    // lọc theo loại (nhan_tu_van / nhan_kham_ls) + đánh dấu bận theo khoảng giờ slot đã chọn.
    /**
     * Chặn cứng booking khi cơ sở hoặc phòng có "ngày nghỉ" phủ ngày/ca đã chọn.
     * Trả mảng [field => message] để gắn vào withErrors, hoặc null nếu không vướng.
     * Ca booking suy ra từ giờ thực hiện (nghỉ trưa → null, chỉ vướng nghỉ cả ngày).
     */
    private function ngayNghiChan(CoSo $co_so, array $data): ?array
    {
        $ngay = $data['ngay_dat'] ?? null;
        if (! $ngay) {
            return null;
        }
        $ca = LichLamViec::caTheoGio($data['gio_thuc_hien'] ?? null);

        if (NgayNghi::coSoDong($co_so->id, $ngay, $ca)) {
            return ['ngay_dat' => 'Cơ sở đóng cửa (ngày nghỉ) vào ngày/ca đã chọn. Vui lòng chọn ngày khác.'];
        }
        if (! empty($data['phong_id']) && NgayNghi::phongDong($co_so->id, (int) $data['phong_id'], $ngay, $ca)) {
            return ['phong_id' => 'Phòng này nghỉ (ngày nghỉ) vào ngày/ca đã chọn. Vui lòng chọn phòng hoặc ngày khác.'];
        }

        return null;
    }

    public function checkBacSi(CoSo $co_so, Request $request)
    {
        $phongId  = (int) $request->query('phong_id');
        $dichVuId = (int) $request->query('dich_vu_id');
        $ngay     = $request->query('ngay');
        $bd       = $request->query('gio_bat_dau');
        $kt       = $request->query('gio_ket_thuc');
        $exceptId = $request->query('except') ? (int) $request->query('except') : null;

        if (! $phongId || ! $dichVuId || ! $ngay || ! $bd || ! $kt) {
            return response()->json(['list' => []]);
        }

        $dv = DichVu::find($dichVuId);
        $phong = Phong::where('co_so_id', $co_so->id)->where('id', $phongId)->with('bacSis')->first();
        if (! $dv || ! $phong) {
            return response()->json(['list' => []]);
        }

        // Ứng viên = bác sĩ (danh mục) gán vào phòng; nếu tư vấn → thêm bác sĩ
        // xuất hiện mọi cơ sở (danh mục dùng chung).
        $candidates = $phong->bacSis;
        if ($dv->thuoc_nhom === 'tu_van') {
            $global = BacSi::where('active', true)->where('xuat_hien_moi_co_so', true)->get();
            $candidates = $candidates->concat($global)->unique('id');
        }

        // Lọc theo loại liệu pháp.
        $candidates = $candidates->filter(fn ($bs) => match ($dv->thuoc_nhom) {
            'tu_van'  => (bool) $bs->nhan_tu_van,
            'kham_ls' => (bool) $bs->nhan_kham_ls,
            default   => true,
        })->sortBy('ten')->values();

        // Lịch trực hiệu lực: KHÔNG lọc ai — vẫn cho chọn mọi bác sĩ. Chỉ đánh dấu
        // người không khớp lịch để UI hiện cảnh báo (cờ 'truc' mỗi người + 'co_lich').
        $ca = LichLamViec::caTheoGio($bd);
        $coLich = LichLamViec::dangHieuLuc($co_so->id, $ngay) !== null;
        $truc = $ca ? LichLamViec::bacSiTruc($co_so->id, $phongId, $ngay, $ca) : collect();
        $nghiIds = NgayNghi::nguoiNghiIds($co_so->id, $ngay, $ca);

        $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $s = $toMin($bd);
        $e = $toMin($kt);
        $slotLen = $e - $s;

        $list = $candidates->map(function ($bs) use ($ngay, $s, $e, $slotLen, $dv, $exceptId, $truc, $nghiIds) {
            // Bác sĩ cần đủ thời lượng cho loại dịch vụ; nếu cần nhiều hơn khung đã chọn → loại.
            $can = $this->phutCanCuaBookingBS($bs, $dv);
            $fit = $slotLen >= $can;
            $busy = $fit && $this->bacSiPhutDaDung($bs->id, $ngay, $s, $e, $exceptId, null, $bs) > 0;

            return [
                'id'        => $bs->id,
                'name'      => $bs->ten_day_du,
                'chuc_danh' => (string) $bs->chuc_danh,
                'available' => $fit && ! $busy,
                'truc'      => $truc->has($bs->id), // có trực phòng+ngày+ca không
                'nghi'      => $nghiIds->contains($bs->id), // có ngày nghỉ phủ ngày/ca không
                'reason'    => ! $fit
                    ? "Cần {$can} phút, khung chỉ {$slotLen} phút"
                    : ($busy ? 'Bác sĩ kín lịch' : null),
            ];
        });

        return response()->json(['list' => $list, 'co_lich' => $coLich]);
    }

    // Danh sách KTV cơ sở + cờ trực phòng dịch vụ theo ngày + ca (cảnh báo mềm, không lọc).
    public function checkKtv(CoSo $co_so, Request $request)
    {
        $phongId = (int) $request->query('phong_id');
        $ngay    = $request->query('ngay');
        $bd      = $request->query('gio_bat_dau');

        if (! $phongId || ! $ngay || ! $bd) {
            return response()->json(['list' => []]);
        }

        $ca = LichLamViec::caTheoGio($bd);
        $coLich = LichLamViec::dangHieuLuc($co_so->id, $ngay) !== null;
        $truc = $ca ? LichLamViec::bacSiTruc($co_so->id, $phongId, $ngay, $ca) : collect();
        $nghiIds = NgayNghi::nguoiNghiIds($co_so->id, $ngay, $ca);

        $ktvs = Ktv::where('co_so_id', $co_so->id)->where('active', true)->orderBy('ten')->get();

        $list = $ktvs->map(fn ($k) => [
            'id'   => $k->id,
            'name' => $k->ten,
            'truc' => $truc->has($k->id),
            'nghi' => $nghiIds->contains($k->id),
        ])->values();

        return response()->json(['list' => $list, 'co_lich' => $coLich]);
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
        // 2026-09-04 — CHẶN: mọi booking mới phải tạo qua Datasource.
        // (API `/api/bookings` cho datasource push vẫn hoạt động — route riêng, không đụng.)
        abort(410, 'Tạo booking đã chuyển sang Datasource. Vui lòng mở SCRM để tạo lịch — sbooking không nhận form tạo mới nữa.');

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
            // 2026-08-07: sale_id giờ nullable — form ẩn Sale select, fallback auth()->id() phía dưới.
            'sale_id'       => ['nullable', Rule::exists('users', 'id')],
            'bac_si_id' => ['nullable', Rule::exists('bac_si', 'id')],
            'ho_tro_id' => ['nullable', 'different:bac_si_id', Rule::exists('bac_si', 'id')],
            'ktv_user_id'   => ['nullable', Rule::exists('ktv', 'id')],
            // 2026-08-07: đổi so_lieu_trinh (text "1/10") → so_luong (int ≥ 1). Bỏ so_luong_lo + dung_tich_lo.
            'so_luong'      => ['nullable', 'integer', 'min:1'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'lan_dau'         => ['nullable', 'boolean'],
            'khach_tang'      => ['nullable', 'string', 'in:co,khong,khac'],
            'khach_tang_ghi_chu' => ['nullable', 'string', 'max:500'],
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
            'gio_thuc_hien.regex'    => 'Giờ thực hiện phải là HH:MM.',
            'so_luong.integer'       => 'Số lượng phải là số nguyên.',
            'so_luong.min'           => 'Số lượng phải ≥ 1 (không âm hoặc 0).',
        ]);

        // Giờ thực hiện / kết thúc phải nằm trong khung_gio cha
        $gioErrors = $this->validateGioTrongKhung($data);
        if (! empty($gioErrors)) {
            return back()->withInput()->withErrors($gioErrors);
        }

        // Ngày nghỉ: cơ sở / phòng đóng cửa → chặn cứng (bác sĩ/KTV nghỉ chỉ cảnh báo mềm ở form).
        $nghiErr = $this->ngayNghiChan($co_so, $data);
        if ($nghiErr) {
            return back()->withInput()->withErrors($nghiErr);
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
        if (! empty($data['bac_si_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['bac_si_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat'], null, $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($err) {
                return back()->withInput()->withErrors(['bac_si_id' => $err]);
            }
        }
        // 2026-09-04 — Phase 1d: capacity check cho hỗ trợ (dùng chung logic BS).
        if (! empty($data['ho_tro_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['ho_tro_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat'], null, $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($err) {
                return back()->withInput()->withErrors(['ho_tro_id' => 'Nhân sự hỗ trợ: ' . $err]);
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
        $canhBaoBacSi = ! empty($data['bac_si_id'])
            ? $this->bacSiTrungLich($co_so, (int) $data['bac_si_id'], $data['ngay_dat'], (int) $data['khung_gio_id'], $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null)
            : null;

        $loaiDatLich = in_array($request->input('loai_dat_lich'), ['kham_ls', 'tu_van', 'dich_vu'], true) ? $request->input('loai_dat_lich') : 'kham_ls';
        // 2026-08-16: Mọi booking mới đều chờ Admin vận hành duyệt (bỏ auto-duyet cho phong_kham).
        $autoDuyet = false;

        $booking = Booking::create([
            'co_so_id'      => $co_so->id,
            'loai_dat_lich' => $loaiDatLich,
            'khach_hang_id' => $kh->id,
            'phong_id'      => $data['phong_id'],
            'khung_gio_id'  => $data['khung_gio_id'],
            'dich_vu_id'    => $data['dich_vu_id'] ?? null,
            'bac_si_id' => $data['bac_si_id'] ?? null,
            'ho_tro_id' => $data['ho_tro_id'] ?? null,
            'ktv_user_id'   => $data['ktv_user_id'] ?? null,
            // 2026-08-07: fallback sale_id = auth user khi form ẩn Sale select.
            'sale_id'       => $data['sale_id'] ?? auth()->id(),
            'nguoi_tao_id'  => auth()->id(),
            'ngay_dat'      => $data['ngay_dat'],
            'gio_thuc_hien' => $gioBatDau,
            'gio_ket_thuc'  => $gioKetThuc,
            'so_luong'      => $data['so_luong'] ?? null,
            'nguon'         => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'lan_dau'       => $request->boolean('lan_dau'),
            'khach_tang'    => $data['khach_tang'] ?? 'khong',
            'khach_tang_ghi_chu' => ($data['khach_tang'] ?? 'khong') === 'khac' ? ($data['khach_tang_ghi_chu'] ?? null) : null,
            'co_tu_van'     => $request->boolean('co_tu_van'),
            'co_kham_cls'   => $request->boolean('co_kham_cls'),
            'ghi_chu'       => $data['ghi_chu'] ?? null,
            'trang_thai'    => $autoDuyet ? 'da_duyet' : 'cho_duyet',
            'da_duyet'      => $autoDuyet,
            'crm_khach_ma'  => trim((string) $request->input('khach_ma', '')) ?: null,
        ]);

        if (! empty($data['menu_ids'])) {
            $booking->menus()->sync($data['menu_ids']);
        }

        $this->notifyLich($booking, LichEvent::TAO_MOI);

        // Nếu SCRM (hoặc client bên ngoài) mở form kèm return_url whitelist → redirect callback với mã booking.
        $return = $this->safeReturnUrl($request->input('return_url'));
        if ($return) {
            $booking->refresh(); // đảm bảo có ma_booking (sinh ở event created)
            $sep = str_contains($return, '?') ? '&' : '?';
            return redirect()->away($return . $sep . http_build_query([
                'booking_ma' => $booking->ma_booking,
                'booking_id' => $booking->id,
            ]));
        }

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã tạo lịch hẹn cho ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBacSi);
    }

    /** Gửi thông báo cho người nhận liên quan đến booking event này. */
    protected function notifyLich(Booking $booking, string $event): void
    {
        try {
            $resolver = app(NotificationRecipientResolver::class);
            $recipients = $resolver->forBooking($booking->fresh(['khachHang', 'coSo']), $event);
            if ($recipients->isEmpty()) return;

            Notification::send(
                $recipients,
                new LichNotification($booking, $event, auth()->user()?->name)
            );
        } catch (\Throwable $e) {
            \Log::warning('Notify booking failed: '.$e->getMessage(), [
                'event' => $event, 'booking_id' => $booking->id,
            ]);
        }
    }

    public function update(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizeEditBooking($booking);

        // Phase 6.25.C fix (2026-08-03) — Sale tiếp nhận (được gán manual scrm hoặc auto UPS)
        // chỉ được ghi tình trạng khách + comment, KHÔNG sửa info khách. Admin bypass.
        $u = auth()->user();
        $isTiepDon = ($booking->tiep_don_user_id === $u->id) || ($booking->sale_id === $u->id);
        if ($isTiepDon && ! $u->is_admin) {
            abort(403, 'Sale tiếp nhận không được sửa thông tin khách — chỉ được ghi trạng thái (Đang tiếp đón / Hoàn tất) và bình luận. Liên hệ Admin nếu cần sửa.');
        }

        // Snapshot trước khi update để diff → push edit sang CRM nếu có thay đổi lịch/phòng/dịch vụ/note/sale.
        $beforeSnapshot = [
            'ngay_dat'     => (string) $booking->ngay_dat,
            'gio_thuc_hien' => (string) $booking->gio_thuc_hien,
            'gio_ket_thuc' => (string) $booking->gio_ket_thuc,
            'phong_id'     => $booking->phong_id,
            'bac_si_id'    => $booking->bac_si_id,
            'dich_vu_id'   => $booking->dich_vu_id,
            'ghi_chu'      => (string) $booking->ghi_chu,
            'sale_id'      => $booking->sale_id,
        ];

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
            // 2026-08-07: sale_id giờ nullable — form ẩn Sale select, fallback auth()->id() phía dưới.
            'sale_id'       => ['nullable', Rule::exists('users', 'id')],
            'bac_si_id' => ['nullable', Rule::exists('bac_si', 'id')],
            'ho_tro_id' => ['nullable', 'different:bac_si_id', Rule::exists('bac_si', 'id')],
            'ktv_user_id'   => ['nullable', Rule::exists('ktv', 'id')],
            // 2026-08-07: đổi so_lieu_trinh (text "1/10") → so_luong (int ≥ 1). Bỏ so_luong_lo + dung_tich_lo.
            'so_luong'      => ['nullable', 'integer', 'min:1'],
            'nguon'         => ['nullable', 'string', 'max:100'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'lan_dau'         => ['nullable', 'boolean'],
            'khach_tang'      => ['nullable', 'string', 'in:co,khong,khac'],
            'khach_tang_ghi_chu' => ['nullable', 'string', 'max:500'],
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
            'gio_thuc_hien.regex'    => 'Giờ thực hiện phải là HH:MM.',
            'so_luong.integer'       => 'Số lượng phải là số nguyên.',
            'so_luong.min'           => 'Số lượng phải ≥ 1 (không âm hoặc 0).',
        ]);

        // Giờ thực hiện / kết thúc phải nằm trong khung_gio cha
        $gioErrors = $this->validateGioTrongKhung($data);
        if (! empty($gioErrors)) {
            return back()->withInput()->withErrors($gioErrors);
        }

        // Ngày nghỉ: cơ sở / phòng đóng cửa → chặn cứng (bác sĩ/KTV nghỉ chỉ cảnh báo mềm ở form).
        $nghiErr = $this->ngayNghiChan($co_so, $data);
        if ($nghiErr) {
            return back()->withInput()->withErrors($nghiErr);
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
        if (! empty($data['bac_si_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['bac_si_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat'], $booking->id, $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($err) {
                return back()->withInput()->withErrors(['bac_si_id' => $err]);
            }
        }
        if (! empty($data['ho_tro_id']) && ! empty($data['dich_vu_id'])) {
            $err = $this->checkBacSiCapacity((int) $data['ho_tro_id'], (int) $data['khung_gio_id'], (int) $data['dich_vu_id'], $data['ngay_dat'], $booking->id, $data['gio_thuc_hien'] ?? null, $data['gio_ket_thuc'] ?? null);
            if ($err) {
                return back()->withInput()->withErrors(['ho_tro_id' => 'Nhân sự hỗ trợ: ' . $err]);
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
            'bac_si_id'  => $data['bac_si_id'] ?? null,
            'ho_tro_id'  => $data['ho_tro_id'] ?? null,
            'ktv_user_id'     => $data['ktv_user_id'] ?? null,
            // 2026-08-07: fallback auth user cho sale_id ẩn.
            'sale_id'         => $data['sale_id'] ?? auth()->id(),
            'ngay_dat'        => $data['ngay_dat'],
            'gio_thuc_hien'   => ! empty($data['gio_thuc_hien']) ? $data['gio_thuc_hien'] . ':00' : null,
            'gio_ket_thuc'    => ! empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] . ':00' : null,
            'so_luong'        => $data['so_luong'] ?? null,
            'nguon'           => $data['nguon'] ?? null,
            'ket_hop_medical' => $request->boolean('ket_hop_medical'),
            'lan_dau'         => $request->boolean('lan_dau'),
            'khach_tang'      => $data['khach_tang'] ?? 'khong',
            'khach_tang_ghi_chu' => ($data['khach_tang'] ?? 'khong') === 'khac' ? ($data['khach_tang_ghi_chu'] ?? null) : null,
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
        $canhBaoBacSi = $booking->bac_si_id
            ? $this->bacSiTrungLich($co_so, (int) $booking->bac_si_id, (string) $booking->ngay_dat, (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id)
            : null;

        $this->notifyLich($booking, LichEvent::CAP_NHAT);

        // Push edit sang CRM nếu có thay đổi liên quan tới lịch/phòng/dịch vụ.
        $fresh = $booking->fresh();
        $diffs = [];
        $labelMap = [
            'ngay_dat'      => 'ngày',
            'gio_thuc_hien' => 'giờ bắt đầu',
            'gio_ket_thuc'  => 'giờ kết thúc',
            'phong_id'      => 'phòng',
            'bac_si_id'     => 'bác sĩ',
            'dich_vu_id'    => 'dịch vụ',
            'ghi_chu'       => 'ghi chú',
            'sale_id'       => 'sale',
        ];
        foreach ($labelMap as $col => $label) {
            $b = (string) ($beforeSnapshot[$col] ?? '');
            $a = (string) ($fresh->{$col} ?? '');
            if ($b !== $a) $diffs[] = "đổi $label: " . ($b === '' ? '—' : $b) . ' → ' . ($a === '' ? '—' : $a);
        }
        if ($diffs !== []) {
            \App\Services\CrmPushService::pushEdit($fresh, auth()->id(), implode('; ', $diffs));
        }

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã cập nhật lịch hẹn của ' . $kh->ho_ten . '.')
            ->with('warning', $canhBaoBacSi);
    }

    public function destroy(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('xoa_booking');

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        // Gửi thông báo TRƯỚC khi xoá để còn ref các quan hệ
        $this->notifyLich($booking, LichEvent::HUY);

        // Phase C1.b rev12 2026-08-02: async — snapshot data + push sau response.
        \App\Services\CrmPushService::pushDeleteAsync($booking, auth()->id());

        $booking->menus()->detach();
        $booking->delete();

        $extra = '';

        return redirect("/{$co_so->slug}/danh-sach")
            ->with('ok', 'Đã xóa lịch hẹn của ' . $ten . '.' . $extra);
    }

    /** Duyệt / bỏ duyệt lịch đặt phòng (chỉ admin).
     *  B5c (2026-08-14): admin vận hành + admin hệ thống được edit sale/giờ/note lúc duyệt.
     *    Fields nhận từ request (optional): gio_thuc_hien, gio_ket_thuc, tiep_don_user_id, ghi_chu.
     *    Giờ nhập tự do (không validate capacity — dùng để thống kê ca đến trễ / quá giờ sau).
     */
    public function duyet(CoSo $co_so, Booking $booking, \Illuminate\Http\Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('duyet_booking');

        $approve = ! $booking->da_duyet;

        // B5c: apply edit fields TRƯỚC khi validate capacity + push CRM.
        // 2026-08-18: tiep_don_user_id BẮT BUỘC khi duyệt. Sale hỗ trợ (tiep_don_ho_tro_id) optional.
        //   Nguồn SA/BA/MKT_BR (lead.source_group từ CRM): tiep_don_user_id FIX CỨNG = sale gốc (creator),
        //   admin không được sửa; chỉ được thêm tiep_don_ho_tro_id để hỗ trợ đón khi sale gốc bận.
        //   Nguồn khác: admin sửa cả 2 slot bình thường.
        if ($approve) {
            $data = $request->validate([
                'gio_thuc_hien'       => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'gio_ket_thuc'        => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'tiep_don_user_id'    => ['required', 'integer', 'exists:users,id'],
                'tiep_don_ho_tro_id'  => ['nullable', 'integer', 'exists:users,id', 'different:tiep_don_user_id'],
                // 2026-09-04: Admin có thể sửa BS + Nhân sự hỗ trợ y tế khi duyệt.
                'bac_si_id'           => ['nullable', 'integer', 'exists:bac_si,id'],
                'ho_tro_id'           => ['nullable', 'integer', 'exists:bac_si,id', 'different:bac_si_id'],
                'ghi_chu'             => ['nullable', 'string', 'max:2000'],
            ], [
                'tiep_don_user_id.required'    => 'Vui lòng chọn Sale tiếp đón trước khi duyệt.',
                'tiep_don_ho_tro_id.different' => 'Sale hỗ trợ phải khác Sale tiếp đón gốc.',
                'ho_tro_id.different'          => 'Nhân sự hỗ trợ phải khác Bác sĩ chính.',
            ]);

            // 2026-09-04: cập nhật BS + hỗ trợ y tế nếu admin có sửa (giữ nguyên nếu không truyền).
            if (array_key_exists('bac_si_id', $data)) {
                $booking->bac_si_id = $data['bac_si_id'] ?: null;
            }
            if (array_key_exists('ho_tro_id', $data)) {
                $booking->ho_tro_id = $data['ho_tro_id'] ?: null;
            }

            if (! empty($data['gio_thuc_hien'])) $booking->gio_thuc_hien = $data['gio_thuc_hien'];
            if (! empty($data['gio_ket_thuc']))  $booking->gio_ket_thuc  = $data['gio_ket_thuc'];
            if (array_key_exists('ghi_chu', $data)) $booking->ghi_chu = $data['ghi_chu'];
            // Q5.2: sale dropdown chỉ hiện sale cùng co_so_id của booking.
            $saleOk = \App\Models\User::where('id', $data['tiep_don_user_id'])
                ->where('co_so_id', $booking->co_so_id)->exists();
            abort_unless($saleOk, 422, 'Sale không thuộc cơ sở của booking này.');

            // 2026-08-18: nguồn SA/BA/MKT_BR — tiep_don_user_id fix cứng = creator (nguoi_tao_id).
            //   Admin không được sửa. Chỉ được thêm sale hỗ trợ.
            $selfOwnedSources = ['sa', 'ba', 'mkt_br'];
            $isSelfOwned = in_array($booking->nguon, $selfOwnedSources, true);
            if ($isSelfOwned && $booking->nguoi_tao_id) {
                // Ignore payload — force = creator để bảo vệ quyền sở hữu sale gốc.
                $booking->tiep_don_user_id = $booking->nguoi_tao_id;
            } else {
                $booking->tiep_don_user_id = $data['tiep_don_user_id'];
            }

            // Sale hỗ trợ (optional).
            if (! empty($data['tiep_don_ho_tro_id'])) {
                $hoTroOk = \App\Models\User::where('id', $data['tiep_don_ho_tro_id'])
                    ->where('co_so_id', $booking->co_so_id)->exists();
                abort_unless($hoTroOk, 422, 'Sale hỗ trợ không thuộc cơ sở của booking này.');
                $booking->tiep_don_ho_tro_id = $data['tiep_don_ho_tro_id'];
                $booking->tiep_don_ho_tro_by = auth()->id();
                $booking->tiep_don_ho_tro_at = now();
            }
        }

        // Phase C1.d (2026-08-02): guard capacity mỗi lần duyệt (không chỉ re-approve
        // đơn từ chối). Đơn từ CRM push sang có thể trải qua thời gian chờ nên slot có
        // thể đã bị đơn khác chiếm. Bỏ qua field null (đơn thiếu phong/khung/BS thì
        // check được gì check nấy).
        $canhBao = [];
        if ($approve) {
            $ngayStr = (string) $booking->ngay_dat->toDateString();
            if ($booking->phong_id && $booking->khung_gio_id) {
                if ($this->khungGioDayCho($co_so, (int) $booking->phong_id, (int) $booking->khung_gio_id, $ngayStr, $booking->id)) {
                    return back()->with('error', 'Không duyệt được: phòng đã đầy slot cho khung giờ này. Vui lòng đổi phòng/giờ trước khi duyệt.');
                }
            }
            if ($booking->ktv_user_id && $booking->khung_gio_id) {
                $busy = $this->ktvBanKhoangGio($co_so, (int) $booking->ktv_user_id, $ngayStr, (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id);
                if ($busy) {
                    $ktvErr = 'KTV đã được đặt cho khung giờ này bởi đơn khác.';
                    \App\Services\CrmPushService::pushValidationErrorAsync($booking, auth()->id(), $ktvErr);
                    return back()->with('error', 'Không duyệt được: '.$ktvErr);
                }
            }
            if ($booking->bac_si_id && $booking->dich_vu_id && $booking->khung_gio_id) {
                // 2026-08-28 fix: truyền gio_bat_dau/gio_ket_thuc từ booking (11:00-11:30) thay vì
                //   để check fallback dùng range khung_gio (5 phút granular slot) → mất khớp DV cần 30 phút.
                $bStart = $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null;
                $bEnd   = $booking->gio_ket_thuc  ? substr($booking->gio_ket_thuc, 0, 5)  : null;
                $err = $this->checkBacSiCapacity((int) $booking->bac_si_id, (int) $booking->khung_gio_id, (int) $booking->dich_vu_id, $ngayStr, $booking->id, $bStart, $bEnd);
                if ($err) {
                    \App\Services\CrmPushService::pushValidationErrorAsync($booking, auth()->id(), $err);
                    return back()->with('error', 'Không duyệt được: '.$err);
                }
                $msg = $this->bacSiTrungLich($co_so, (int) $booking->bac_si_id, $ngayStr, (int) $booking->khung_gio_id, $booking->gio_thuc_hien ? substr($booking->gio_thuc_hien, 0, 5) : null, $booking->gio_ket_thuc ? substr($booking->gio_ket_thuc, 0, 5) : null, $booking->id);
                if ($msg) $canhBao[] = $msg;
            }
        }

        $booking->da_duyet = $approve;
        $booking->trang_thai = $approve ? 'da_duyet' : 'cho_duyet';
        $booking->ly_do_tu_choi = null; // duyệt lại thì xóa lý do từ chối cũ
        $booking->save();

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        if ($approve) {
            $this->notifyLich($booking, LichEvent::DUYET);
        }

        // Phase C1.b 2026-08-01: push trạng thái duyệt/bỏ duyệt về CRM để BookingLog.sync_status cập nhật.
        \App\Services\CrmPushService::pushStatusAsync($booking, auth()->id());
        $push = ['ok' => true, 'msg' => ''];
        $extra = $push['ok'] ? '' : ' (chưa đẩy được CRM: ' . $push['msg'] . ')';

        return back()
            ->with('ok', ($approve ? 'Đã duyệt' : 'Đã bỏ duyệt') . ' lịch hẹn của ' . $ten . '.' . $extra)
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

        $this->notifyLich($booking, LichEvent::TU_CHOI);

        // Phase C1.b 2026-08-01: push trạng thái từ chối về CRM.
        \App\Services\CrmPushService::pushStatusAsync($booking, auth()->id());
        $push = ['ok' => true, 'msg' => ''];
        $extra = $push['ok'] ? '' : ' (chưa đẩy được CRM: ' . $push['msg'] . ')';

        return back()->with('ok', 'Đã từ chối lịch hẹn của ' . $ten . '.' . $extra);
    }

    /** Ghi nhận phản hồi từ khách (chỉ khi lịch hẹn đã xong). */
    public function phanHoi(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('duyet_booking');

        if ($booking->trang_thai !== 'da_xong') {
            return back()->with('error', 'Chỉ ghi phản hồi cho lịch hẹn đã hoàn thành.');
        }

        $data = $request->validate([
            'phan_hoi_khach' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->phan_hoi_khach = $data['phan_hoi_khach'] ?: null;
        $booking->save();

        $ten = $booking->khachHang?->ho_ten ?? 'khách';

        return back()->with('ok', 'Đã lưu phản hồi từ khách cho lịch hẹn của ' . $ten . '.');
    }

    // 2026-08-05: BỎ themPhanHoi / xoaPhanHoi (dead code — thay bằng themBinhLuan / xoaBinhLuan qua migration 07_05).

    /** Đánh dấu đã xong / hoàn tác về đã duyệt. */
    public function xong(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('cap_nhat_trang_thai_khach');

        $done = $booking->trang_thai !== 'da_xong';
        if ($done) {
            $booking->trang_thai = 'da_xong';
            $booking->da_duyet = true;
        } else {
            $booking->trang_thai = 'da_duyet';
            $booking->da_duyet = true;
        }
        $booking->save();

        \App\Services\CrmPushService::pushStatusAsync($booking, auth()->id());
        $push = ['ok' => true, 'msg' => ''];

        $ten = $booking->khachHang?->ho_ten ?? 'khách';
        $msg = ($done ? 'Đã hoàn thành' : 'Đã chuyển lại "Đã duyệt"') . ' lịch hẹn của ' . $ten . '. ' . $push['msg'];

        return back()->with($push['ok'] ? 'ok' : 'warn', $msg);
    }

    /** Cập nhật trạng thái khách: đã tới / tới trễ / hủy (hoặc bỏ chọn). Khách hủy → trả slot. */
    public function capNhatTrangThaiKhach(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('cap_nhat_trang_thai_khach');

        $data = $request->validate([
            'trang_thai_khach' => ['nullable', Rule::in(['da_toi', 'toi_tre', 'huy'])],
        ]);

        // Bấm lại đúng trạng thái đang chọn → bỏ chọn (toggle về null).
        $moi = $data['trang_thai_khach'] ?? null;
        $booking->trang_thai_khach = ($booking->trang_thai_khach === $moi) ? null : $moi;
        $booking->save();

        \App\Services\CrmPushService::pushStatusAsync($booking, auth()->id());
        $push = ['ok' => true, 'msg' => ''];

        $nhan = ['da_toi' => 'Khách đã tới', 'toi_tre' => 'Khách tới trễ', 'huy' => 'Khách hủy'];
        $msg = $booking->trang_thai_khach
            ? 'Đã cập nhật: ' . ($nhan[$booking->trang_thai_khach] ?? $booking->trang_thai_khach)
                . ($booking->trang_thai_khach === 'huy' ? ' — khung giờ đã được trả về kho.' : '.')
            : 'Đã bỏ trạng thái khách.';
        $msg .= ' ' . $push['msg'];

        return back()->with($push['ok'] ? 'ok' : 'warn', $msg);
    }

    /**
     * Phase 6.25.C — Sale được auto-chia từ UPS scrm tick "Đang tiếp đón" / "Hoàn tất".
     * Toggle trạng thái + push sang scrm để mark busy/free trong daily_attendance.
     */
    public function capNhatTiepDon(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        // Chỉ sale được scrm auto-chia mới được tick.
        abort_unless(
            $booking->tiep_don_user_id === auth()->id() || $booking->sale_id === auth()->id(),
            403,
            'Chỉ sale được gán (manual scrm phase 3 hoặc auto UPS) mới có quyền tick.'
        );

        $data = $request->validate([
            'trang_thai_tiep_don' => ['required', Rule::in(['dang_tiep_don', 'hoan_tat'])],
        ]);
        $moi = $data['trang_thai_tiep_don'];

        $updates = ['trang_thai_tiep_don' => $moi];
        if ($moi === 'dang_tiep_don') {
            $updates['tiep_don_bat_dau'] = now();
            $updates['tiep_don_hoan_tat'] = null;
        } else {
            $updates['tiep_don_hoan_tat'] = now();
        }
        $booking->update($updates);

        $push = \App\Services\CrmPushService::pushTiepDon($booking, auth()->id(), $moi === 'dang_tiep_don');

        $label = $moi === 'dang_tiep_don' ? 'Bắt đầu tiếp đón khách' : 'Hoàn tất tiếp đón';

        return back()->with($push['ok'] ? 'ok' : 'warn', $label . '. ' . $push['msg']);
    }

    /**
     * 2026-08-18 — Sale tiếp đón bấm "Đã xong" khi khách checkin xong:
     *   3 field bắt buộc — tinh_trang_checkin + ket_qua_sau_checkin (nullable khi hủy lịch) + phan_loai
     *   → set checkin_hoan_tat_at/by + push callback CRM để close phase 5 + sync classification.
     */
    public function capNhatCheckinDone(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        abort_unless(
            $booking->tiep_don_user_id === auth()->id() || $booking->sale_id === auth()->id() || auth()->user()?->is_admin,
            403,
            'Chỉ Sale tiếp đón / Sale phụ trách / Admin mới được bấm Đã xong.'
        );

        $data = $request->validate([
            'tinh_trang_checkin'  => ['required', Rule::in(array_keys(Booking::TINH_TRANG_CHECKIN))],
            'ket_qua_sau_checkin' => ['nullable', Rule::in(array_keys(Booking::KET_QUA_SAU_CHECKIN))],
            'phan_loai'           => ['required', Rule::in(array_keys(Booking::PHAN_LOAI))],
        ], [], [
            'tinh_trang_checkin' => 'tình trạng checkin',
            'ket_qua_sau_checkin' => 'kết quả sau checkin',
            'phan_loai' => 'phân loại',
        ]);

        if ($data['tinh_trang_checkin'] !== 'huy_lich' && empty($data['ket_qua_sau_checkin'])) {
            return back()->withErrors(['ket_qua_sau_checkin' => 'Kết quả sau checkin bắt buộc khi không phải hủy lịch.']);
        }

        $booking->fill($data);
        $booking->checkin_hoan_tat_at = now();
        $booking->checkin_hoan_tat_by = auth()->id();
        $booking->save();

        $push = \App\Services\CrmPushService::pushCheckinDone($booking, auth()->id());

        return back()->with($push['ok'] ? 'ok' : 'warn',
            'Đã lưu kết quả checkin. ' . $push['msg']
        );
    }

    /**
     * 2026-08-10 — Toggle cờ "Booking trễ" (Admin cơ sở / Quản trị vận hành / is_admin BO).
     */
    public function toggleBookingTre(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $u = auth()->user();
        $allowed = $u?->is_admin || in_array($u?->vaiTro?->ma, ['admin_co_so', 'quan_tri_van_hanh'], true);
        abort_unless($allowed, 403, 'Chỉ Admin cơ sở / Quản trị vận hành mới được tick Booking trễ.');

        $booking->update(['booking_tre' => ! (bool) $booking->booking_tre]);
        $label = $booking->booking_tre ? 'Đã đánh dấu Booking trễ.' : 'Đã bỏ đánh dấu Booking trễ.';

        return back()->with('ok', $label);
    }

    /** Thêm bình luận "sau dịch vụ" (nhiều vai trò được phép). */
    public function themBinhLuan(CoSo $co_so, Booking $booking, Request $request)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);
        $this->authorizePerm('binh_luan_booking');

        $data = $request->validate([
            'noi_dung' => ['required', 'string', 'max:2000'],
        ], [
            'noi_dung.required' => 'Vui lòng nhập nội dung bình luận.',
        ]);

        $booking->binhLuans()->create([
            'user_id'  => auth()->id(),
            'noi_dung' => $data['noi_dung'],
        ]);

        $push = \App\Services\CrmPushService::pushComment($booking, auth()->id(), $data['noi_dung']);

        return back()->with($push['ok'] ? 'ok' : 'warn', 'Đã gửi bình luận. ' . $push['msg']);
    }

    /** Xóa bình luận — chỉ Admin hệ thống. */
    public function xoaBinhLuan(CoSo $co_so, Booking $booking, \App\Models\BookingBinhLuan $binh_luan)
    {
        abort_unless($booking->co_so_id === $co_so->id && $binh_luan->booking_id === $booking->id, 404);
        abort_unless(auth()->user()?->is_admin, 403, 'Chỉ quản trị hệ thống được xóa bình luận.');

        $binh_luan->delete();

        return back()->with('ok', 'Đã xóa bình luận.');
    }

}
