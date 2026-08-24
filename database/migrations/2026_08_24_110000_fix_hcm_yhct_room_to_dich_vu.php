<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-24 — Đổi Phòng YHCT tại HCM (207 NVT) từ phong_kham → phong_dich_vu.
 * Bug: SCRM bucket "Dịch vụ" lọc phòng theo kieu_phong=phong_dich_vu → HCM
 * không có phòng nào match → dropdown rỗng khi tạo booking Y học Phương Đông.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cs = DB::table('co_so')->where('slug', '207nvt')->first();
        if (! $cs) return;

        $affected = DB::table('phong')
            ->where('co_so_id', $cs->id)
            ->where('ten', 'Phòng YHCT')
            ->update([
                'kieu_phong'   => 'phong_dich_vu',
                'so_slot_toi_da' => 2,
                'phut_moi_slot'  => 30,
                'updated_at'   => now(),
            ]);

        if (app()->runningInConsole()) {
            echo "  → HCM Phòng YHCT → phong_dich_vu: {$affected} row.\n";
        }
    }

    public function down(): void
    {
        // No-op: không revert (bookings có thể đã tạo dựa trên phong_dich_vu).
    }
};
