<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\DichVu;
use App\Models\KhungGio;

/**
 * 2026-08-10: Tách checkBacSiCapacity + helpers ra trait để cả web BookingController và
 * API BookingApiController cùng dùng. Trước đây SCRM push booking qua API mà API chỉ check phòng,
 * đến khi admin bấm duyệt bên sbooking mới báo lỗi BS+DV cần thời lượng > khung → UX kém.
 */
trait BookingCapacityChecks
{
    protected function bccKhoangGioCuaKhung(int $khungGioId): ?array
    {
        $kg = KhungGio::find($khungGioId);
        if (! $kg) return null;
        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        return [
            $toMin(substr($kg->gio_bat_dau, 0, 5)),
            $toMin(substr($kg->gio_ket_thuc, 0, 5)),
        ];
    }

    protected function bccPhutCanCuaBookingBS(?BacSi $bs, ?DichVu $dv): int
    {
        if (! $dv) return 0;
        return match ($dv->thuoc_nhom) {
            'tu_van'  => (int) ($bs?->phut_tu_van ?? 30),
            'kham_ls' => (int) ($bs?->phut_kham_ls ?? 5),
            default   => (int) ($dv->thoi_gian_phut ?? 30),
        };
    }

    protected function bccKhoangGioBooking(Booking $b): ?array
    {
        $toMin = fn (?string $t) => $t ? ((int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2)) : null;
        $bd = $b->gio_thuc_hien ?: $b->khungGio?->gio_bat_dau;
        $kt = $b->gio_ket_thuc ?: $b->khungGio?->gio_ket_thuc;
        $s = $toMin($bd ? substr($bd, 0, 5) : null);
        if ($s === null) return null;
        $e = $toMin($kt ? substr($kt, 0, 5) : null) ?? ($s + 60);
        return [$s, $e];
    }

    protected function bccBacSiPhutDaDung(int $bacSiId, string $ngay, int $s, int $e, ?int $exceptId = null, ?BacSi $bs = null): int
    {
        $bookings = Booking::with(['dichVu', 'khungGio'])
            ->where('bac_si_id', $bacSiId)
            ->whereDate('ngay_dat', $ngay)
            ->giuCho()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get();

        $bs ??= BacSi::find($bacSiId);
        $total = 0;
        foreach ($bookings as $b) {
            $r = $this->bccKhoangGioBooking($b);
            if (! $r) continue;
            [$os, $oe] = $r;
            if ($s < $oe && $os < $e) {
                $total += $this->bccPhutCanCuaBookingBS($bs, $b->dichVu);
            }
        }
        return $total;
    }

    protected function bccFormatKhoang(int $s, int $e): string
    {
        return sprintf('%02d:%02d-%02d:%02d', intdiv($s, 60), $s % 60, intdiv($e, 60), $e % 60);
    }

    /**
     * Lỗi nếu BS không nhận loại dịch vụ HOẶC đã có lịch trùng khoảng giờ booking. Tính cross-cơ sở.
     * Trả về câu lỗi (dùng cho flash/response) hoặc null nếu OK.
     */
    protected function bccCheckBacSiCapacity(int $bacSiId, int $khungGioId, int $dichVuId, string $ngay, ?int $exceptId = null, ?string $gioBatDau = null, ?string $gioKetThuc = null): ?string
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

        $khoang = $this->bccKhoangGioCuaKhung($khungGioId);
        if (! $khoang) return null;
        $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $s = $gioBatDau ? $toMin($gioBatDau) : $khoang[0];
        $e = $gioKetThuc ? $toMin($gioKetThuc) : $khoang[1];
        if ($e <= $s) { [$s, $e] = $khoang; }

        $can = $this->bccPhutCanCuaBookingBS($bs, $dv);
        if (($e - $s) < $can) {
            return "Bác sĩ này cần {$can} phút cho loại dịch vụ này, nhưng khung đã chọn chỉ " . ($e - $s) . " phút. Vui lòng chọn khung dài hơn hoặc bác sĩ khác.";
        }

        if ($this->bccBacSiPhutDaDung($bacSiId, $ngay, $s, $e, $exceptId, $bs) > 0) {
            return "Bác sĩ này đã có lịch trùng giờ trong khoảng {$this->bccFormatKhoang($s, $e)} ngày này. Vui lòng chọn giờ khác hoặc bác sĩ khác.";
        }

        return null;
    }
}
