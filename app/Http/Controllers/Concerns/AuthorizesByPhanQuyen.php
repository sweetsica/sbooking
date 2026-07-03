<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PhanQuyen;

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
     * Xác định phạm vi xem booking của user hiện tại.
     * Trả về: 'tat_ca' | 'co_so_toi' | 'phong_toi' | 'cua_toi' | null (không có quyền).
     * Ưu tiên từ rộng → hẹp: nếu có quyền rộng hơn thì dùng quyền đó.
     */
    protected function bookingViewScope(): ?string
    {
        $perms = $this->allowedFieldKeys();
        if (in_array('xem_booking_tat_ca', $perms, true)) return 'tat_ca';
        if (in_array('xem_booking_co_so_toi', $perms, true)) return 'co_so_toi';
        if (in_array('xem_booking_phong_toi', $perms, true)) return 'phong_toi';
        if (in_array('xem_booking_cua_toi', $perms, true)) return 'cua_toi';
        return null;
    }

    /**
     * Áp dụng bộ lọc phạm vi xem booking lên query.
     * Admin luôn thấy tất cả. Trả về false nếu user không có quyền xem.
     */
    protected function applyViewScope($query, ?\App\Models\CoSo $coSo = null): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($user->is_admin) return true;

        $scope = $this->bookingViewScope();
        if (! $scope) return false;

        switch ($scope) {
            case 'tat_ca':
                break;
            case 'co_so_toi':
                if ($user->co_so_id) {
                    $query->where('co_so_id', $user->co_so_id);
                }
                break;
            case 'phong_toi':
                $sameDepUsers = \App\Models\User::where('phong_ban_id', $user->phong_ban_id)
                    ->pluck('id');
                $query->where(function ($q) use ($user, $sameDepUsers) {
                    $q->whereIn('nguoi_tao_id', $sameDepUsers)
                      ->orWhereIn('bac_si_user_id', $sameDepUsers)
                      ->orWhereIn('ktv_user_id', $sameDepUsers)
                      ->orWhereIn('sale_id', $sameDepUsers);
                });
                break;
            case 'cua_toi':
                $query->where(function ($q) use ($user) {
                    $q->where('nguoi_tao_id', $user->id)
                      ->orWhere('bac_si_user_id', $user->id)
                      ->orWhere('ktv_user_id', $user->id)
                      ->orWhere('sale_id', $user->id);
                });
                break;
        }

        return true;
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
}
