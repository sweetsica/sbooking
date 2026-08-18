<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-18 — Grant full booking perms cho vai trò "Admin cơ sở" (ma=admin_co_so).
 *
 * Trước: admin_co_so chỉ có duyet_booking / xem_booking_co_so_toi / duyet_tu_van
 * → duyệt được lịch nhưng KHÔNG update được trạng thái khách (Khách đã tới/Tới trễ/Hủy),
 *   không bình luận, không thêm/sửa booking. Không hợp lý với vai trò quản lý cơ sở.
 *
 * Thêm perms:
 *   - cap_nhat_trang_thai_khach: 3 nút trạng thái khách
 *   - binh_luan_booking: thêm bình luận vào booking
 *   - them_booking, sua_booking: quản lý booking (giống admin hệ thống nhưng scope cơ sở)
 *   - ghi_chu_phan_hoi: ghi phản hồi khách sau khi da_xong
 *   - sua_lich_tu_van: sửa lịch tư vấn
 *
 * Idempotent: chỉ insert nếu chưa có.
 */
return new class extends Migration
{
    private const VAI_TRO_MA = 'admin_co_so';

    private const PERMS = [
        'cap_nhat_trang_thai_khach',
        'binh_luan_booking',
        'them_booking',
        'sua_booking',
        'ghi_chu_phan_hoi',
        'sua_lich_tu_van',
    ];

    public function up(): void
    {
        $vaiTroId = DB::table('vai_tro')->where('ma', self::VAI_TRO_MA)->value('id');
        if (! $vaiTroId) return;

        foreach (self::PERMS as $truong) {
            $exists = DB::table('phan_quyen')
                ->where('vai_tro_id', $vaiTroId)
                ->where('truong', $truong)
                ->exists();
            if (! $exists) {
                DB::table('phan_quyen')->insert([
                    'vai_tro_id' => $vaiTroId,
                    'truong'     => $truong,
                ]);
            }
        }
    }

    public function down(): void
    {
        $vaiTroId = DB::table('vai_tro')->where('ma', self::VAI_TRO_MA)->value('id');
        if (! $vaiTroId) return;
        DB::table('phan_quyen')
            ->where('vai_tro_id', $vaiTroId)
            ->whereIn('truong', self::PERMS)
            ->delete();
    }
};
