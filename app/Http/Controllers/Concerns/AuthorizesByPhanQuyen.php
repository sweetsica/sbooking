<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PhanQuyen;
use App\Models\User;

trait AuthorizesByPhanQuyen
{
    /**
     * Bật 403 nếu user hiện tại không có quyền $field (theo phòng ban hoặc vai trò).
     * Admin (is_admin = true) luôn vượt qua.
     */
    protected function authorizePerm(string $field): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }
        if ($user->is_admin) {
            return;
        }

        // Guard: user không có cả vai_tro_id lẫn phong_ban_id → không thể có quyền.
        // Nếu không guard, closure where() rỗng → query trở thành unbounded → bypass.
        if (! $user->vai_tro_id && ! $user->phong_ban_id) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $ok = PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->where('truong', $field)->exists();

        abort_unless($ok, 403, 'Bạn không có quyền thực hiện thao tác này.');
    }

    /**
     * Trả về danh sách key trường mà user hiện tại được phép thao tác.
     * Admin → tất cả key trong BookingFields.
     */
    protected function allowedFieldKeys(): array
    {
        $user = auth()->user();
        if (! $user) return [];
        if ($user->is_admin) {
            return \App\Support\BookingFields::keys();
        }

        // Guard tương tự authorizePerm: tránh query unbounded.
        if (! $user->vai_tro_id && ! $user->phong_ban_id) {
            return [];
        }

        return PhanQuyen::where(function ($q) use ($user) {
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
                if ($user->vai_tro_id) $q->orWhere('vai_tro_id', $user->vai_tro_id);
            })->pluck('truong')->all();
    }

    /** Kiểm tra nhanh: user có quyền cụ thể không (không abort). */
    protected function hasPerm(string $field): bool
    {
        return in_array($field, $this->allowedFieldKeys(), true);
    }

    /**
     * Được phép sửa booking cụ thể? Quy tắc từ RỘNG → HẸP:
     *  - Admin → OK.
     *  - 'sua_booking' → OK mọi booking.
     *  - 'sua_booking_lien_quan' VÀ user là người tạo / BS / KTV / Sale → OK.
     *  - 'sua_booking_dich_vu_cua_toi' VÀ loai_dat_lich=dich_vu VÀ user liên quan → OK.
     *  - Còn lại → không.
     */
    protected function canEditBooking(\App\Models\Booking $booking): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($user->is_admin) return true;
        if ($this->hasPerm('sua_booking')) return true;

        $lienQuan = $booking->laLienQuan($user);
        if ($this->hasPerm('sua_booking_lien_quan') && $lienQuan) return true;

        return $this->hasPerm('sua_booking_dich_vu_cua_toi')
            && $booking->loai_dat_lich === 'dich_vu'
            && $lienQuan;
    }

    /** Bật 403 nếu user không được phép sửa booking này. */
    protected function authorizeEditBooking(\App\Models\Booking $booking): void
    {
        abort_unless($this->canEditBooking($booking), 403, 'Bạn không có quyền sửa lịch này.');
    }

    /**
     * Xác định scope xem booking: tat_ca > co_so_toi > phong_toi > cua_toi.
     * Trả về null nếu user không có bất kỳ quyền xem nào.
     */
    protected function bookingViewScope(): ?string
    {
        $user = auth()->user();
        if (! $user) return null;
        if ($user->is_admin) return 'tat_ca';

        $allowed = $this->allowedFieldKeys();
        if (in_array('xem_booking_tat_ca', $allowed, true)) return 'tat_ca';
        if (in_array('xem_booking_co_so_toi', $allowed, true)) return 'co_so_toi';
        if (in_array('xem_booking_phong_toi', $allowed, true)) return 'phong_toi';
        if (in_array('xem_booking_cua_toi', $allowed, true)) return 'cua_toi';
        // 2026-08-05: fallback perm 'xem_booking' legacy (Tư vấn viên) → coi như 'cua_toi'.
        if (in_array('xem_booking', $allowed, true)) return 'cua_toi';
        return null;
    }

    /**
     * Áp dụng scope xem booking lên query builder.
     * - tat_ca: không lọc gì thêm.
     * - co_so_toi: chỉ booking cùng cơ sở.
     * - phong_toi: booking liên quan đến thành viên cùng phòng ban.
     * - cua_toi: chỉ booking mình tạo / là BS / KTV / Sale.
     */
    protected function applyViewScope($query)
    {
        $scope = $this->bookingViewScope();
        if ($scope === null) abort(403, 'Bạn không có quyền xem booking.');
        if ($scope === 'tat_ca') return $query;

        $user = auth()->user();

        if ($scope === 'co_so_toi') {
            return $query->where('co_so_id', $user->co_so_id);
        }

        if ($scope === 'phong_toi') {
            $teamUserIds = User::where('phong_ban_id', $user->phong_ban_id)->pluck('id');
            return $query->where(function ($q) use ($teamUserIds) {
                $q->whereIn('nguoi_tao_id', $teamUserIds)
                  ->orWhereIn('bac_si_user_id', $teamUserIds)
                  ->orWhereIn('ktv_user_id', $teamUserIds)
                  ->orWhereIn('sale_id', $teamUserIds);
            });
        }

        // cua_toi
        $userId = $user->id;
        return $query->where(function ($q) use ($userId) {
            $q->where('nguoi_tao_id', $userId)
              ->orWhere('bac_si_user_id', $userId)
              ->orWhere('ktv_user_id', $userId)
              ->orWhere('sale_id', $userId);
        });
    }

    /** Kiểm tra user có quyền xem booking cụ thể không (không abort). */
    protected function canViewBooking(\App\Models\Booking $booking): bool
    {
        $scope = $this->bookingViewScope();
        if ($scope === null) return false;
        if ($scope === 'tat_ca') return true;

        $user = auth()->user();
        if ($scope === 'co_so_toi') return $booking->co_so_id === $user->co_so_id;

        if ($scope === 'phong_toi') {
            $teamUserIds = User::where('phong_ban_id', $user->phong_ban_id)->pluck('id')->all();
            return $booking->laLienQuan($user)
                || in_array($booking->nguoi_tao_id, $teamUserIds, true)
                || in_array($booking->bac_si_user_id, $teamUserIds, true)
                || in_array($booking->ktv_user_id, $teamUserIds, true)
                || in_array($booking->sale_id, $teamUserIds, true);
        }

        return $booking->laLienQuan($user);
    }
}
