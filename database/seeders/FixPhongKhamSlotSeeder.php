<?php

namespace Database\Seeders;

use App\Models\Phong;
use Illuminate\Database\Seeder;

/**
 * Bản vá: phòng KHÁM (phong_kham) chỉ phục vụ TUẦN TỰ (1 ghế/bác sĩ), nên
 * so_slot_toi_da phải = 1. "Số khách/giờ" đã do độ dài khung giờ quyết định
 * (vd khung 5 phút = 12 khách/giờ), KHÔNG phải số giường song song.
 *
 * Bản seed cũ của HN (59 NTN) đặt nhầm so_slot_toi_da = 12 cho các phòng khám,
 * khiến lịch biểu hiện 12 cột "Giường" và sức chứa bị phồng 12 lần.
 *
 * Vá này CHỈ cập nhật so_slot_toi_da; KHÔNG xóa/tạo lại khung giờ,
 * nên khung_gio_id giữ nguyên và booking hiện có không bị lệch tham chiếu.
 * Idempotent: chạy lại nhiều lần vẫn an toàn.
 */
class FixPhongKhamSlotSeeder extends Seeder
{
    public function run(): void
    {
        $updated = Phong::where('kieu_phong', 'phong_kham')
            ->where('so_slot_toi_da', '!=', 1)
            ->update(['so_slot_toi_da' => 1]);

        $this->command?->info("Đã vá {$updated} phòng khám về so_slot_toi_da = 1.");
    }
}
