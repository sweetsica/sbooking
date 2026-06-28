<?php

namespace App\Support;

/**
 * Loại sự kiện lịch để chọn template thông báo + danh sách người nhận.
 */
final class LichEvent
{
    public const TAO_MOI    = 'tao_moi';     // vừa tạo (chờ duyệt)
    public const DUYET      = 'duyet';       // được duyệt
    public const TU_CHOI    = 'tu_choi';     // bị từ chối
    public const CAP_NHAT   = 'cap_nhat';    // sửa giờ/người/phòng (sau khi đã duyệt)
    public const HUY        = 'huy';         // xoá
    public const NHAC_HEN   = 'nhac_hen';    // sắp đến giờ

    public static function label(string $event): string
    {
        return match ($event) {
            self::TAO_MOI  => 'Lịch mới',
            self::DUYET    => 'Lịch đã duyệt',
            self::TU_CHOI  => 'Lịch bị từ chối',
            self::CAP_NHAT => 'Lịch được cập nhật',
            self::HUY      => 'Lịch đã hủy',
            self::NHAC_HEN => 'Nhắc hẹn',
            default        => 'Thông báo lịch',
        };
    }
}
