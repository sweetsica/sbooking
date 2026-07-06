<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LichHen;
use App\Models\PhanQuyen;
use App\Models\User;
use App\Support\LichEvent;
use Illuminate\Support\Collection;

/**
 * Quyết định danh sách user nhận thông báo cho một event lịch.
 *
 * Quy tắc nguồn người nhận (per event):
 *  - TAO_MOI  : OPERATORS (lễ tân + admin vận hành + admin) — biết để vào duyệt.
 *  - DUYET    : TAGGED (BS/KTV/sale của chính lịch) — chỉ thông báo cho người liên quan.
 *  - CAP_NHAT : TAGGED + OPERATORS — ai dính đến cần biết.
 *  - TU_CHOI  : OPERATORS (sale + lễ tân) — không phiền chuyên môn.
 *  - HUY      : OPERATORS (sale + lễ tân) — không phiền chuyên môn.
 *  - NHAC_HEN : TAGGED (BS/KTV của lịch) — chỉ người làm việc trực tiếp.
 *
 * Sau khi xác định pool, lọc tiếp theo permission tương ứng (trừ trường hợp tagged
 * cho TAO_MOI/DUYET/CAP_NHAT/NHAC_HEN — người được tag mặc nhiên có liên quan).
 */
class NotificationRecipientResolver
{
    public function forBooking(Booking $booking, string $event): Collection
    {
        // Lưu ý: booking->bac_si_id trỏ DANH MỤC bac_si (không phải tài khoản user)
        // nên bác sĩ không nằm trong pool nhận thông báo — chỉ KTV + Sale (là user thật).
        return $this->collect($booking, $event, [
            'ktv_user_id'    => $booking->ktv_user_id,
            'sale_id'        => $booking->sale_id,
        ]);
    }

    public function forLichHen(LichHen $lichHen, string $event): Collection
    {
        // Bác sĩ tư vấn = danh mục bac_si (không phải user) → không nằm trong pool nhận TB.
        return $this->collect($lichHen, $event, [
            'sale_id'        => $lichHen->sale_id,
        ]);
    }

    /**
     * @param array<string,?int> $tagged
     */
    protected function collect(object $lich, string $event, array $tagged): Collection
    {
        $coSoId = (int) $lich->co_so_id;
        $taggedIds = collect($tagged)->filter()->values()->all();

        [$includeTagged, $includeOperators] = $this->poolFor($event);
        $perm = $this->permissionForEvent($event);

        $users = collect();

        if ($includeTagged && ! empty($taggedIds)) {
            // Tagged: lấy luôn, không cần check permission (vì họ tham gia trực tiếp)
            $users = $users->merge(User::whereIn('id', $taggedIds)->get());
        }

        if ($includeOperators) {
            // Operators: trong cùng cơ sở + có permission tương ứng
            $users = $users->merge($this->operatorsInCoSo($coSoId, $perm, $taggedIds));
        }

        return $users->unique('id')->values();
    }

    /**
     * Trả về [includeTagged, includeOperators] cho mỗi event.
     */
    protected function poolFor(string $event): array
    {
        return match ($event) {
            LichEvent::TAO_MOI   => [false, true],   // chỉ operators biết để duyệt
            LichEvent::DUYET     => [true,  false],  // chỉ người được tag
            LichEvent::CAP_NHAT  => [true,  true],   // cả 2
            LichEvent::TU_CHOI   => [false, true],   // chỉ operators (sale/lễ tân)
            LichEvent::HUY       => [false, true],   // chỉ operators
            LichEvent::NHAC_HEN  => [true,  false],  // chỉ người được tag
            default              => [false, true],
        };
    }

    protected function permissionForEvent(string $event): string
    {
        return match ($event) {
            // TAO_MOI: thông báo cho người có thể duyệt — dùng luôn 'duyet_booking'
            LichEvent::TAO_MOI                   => 'duyet_booking',
            LichEvent::DUYET                     => 'nhan_tb_tag_lich',
            LichEvent::CAP_NHAT                  => 'nhan_tb_cap_nhat_lich',
            LichEvent::TU_CHOI, LichEvent::HUY   => 'nhan_tb_huy_lich',
            LichEvent::NHAC_HEN                  => 'nhan_tb_nhac_hen',
            default                              => 'nhan_tb_cap_nhat_lich',
        };
    }

    /**
     * Users trong cơ sở có quyền nhận (qua vai trò hoặc phòng ban), trừ các id đã có.
     *
     * @param array<int> $exclude
     */
    protected function operatorsInCoSo(int $coSoId, string $truong, array $exclude = []): Collection
    {
        $vaiTroIds  = PhanQuyen::where('truong', $truong)->whereNotNull('vai_tro_id')->pluck('vai_tro_id')->all();
        $phongBanIds = PhanQuyen::where('truong', $truong)->whereNotNull('phong_ban_id')->pluck('phong_ban_id')->all();

        // Admin (is_admin) nhận mọi cơ sở, không cần co_so_id match.
        // Operators khác phải thuộc cùng co_so_id.
        $q = User::query()->where(function ($outer) use ($coSoId, $vaiTroIds, $phongBanIds) {
            $outer->where('is_admin', true);
            if (! empty($vaiTroIds) || ! empty($phongBanIds)) {
                $outer->orWhere(function ($inner) use ($coSoId, $vaiTroIds, $phongBanIds) {
                    $inner->where('co_so_id', $coSoId)
                        ->where(function ($cond) use ($vaiTroIds, $phongBanIds) {
                            if (! empty($vaiTroIds))   $cond->orWhereIn('vai_tro_id', $vaiTroIds);
                            if (! empty($phongBanIds)) $cond->orWhereIn('phong_ban_id', $phongBanIds);
                        });
                });
            }
        });

        if (! empty($exclude)) $q->whereNotIn('id', $exclude);

        return $q->get();
    }

    public function userHasPermission(User $user, string $truong): bool
    {
        if ($user->is_admin) return true;

        return PhanQuyen::where('truong', $truong)
            ->where(function ($q) use ($user) {
                if ($user->vai_tro_id)  $q->orWhere('vai_tro_id', $user->vai_tro_id);
                if ($user->phong_ban_id) $q->orWhere('phong_ban_id', $user->phong_ban_id);
            })
            ->exists();
    }
}
