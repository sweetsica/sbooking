<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Đợt C.3.c (2026-08-25): fix slot HN theo rule "60/thời gian DV chính".
// User chốt: slot = tối đa khách/giờ, tránh dồn khách vào 1 giờ.
// Semantics: so_slot_toi_da=1 (mặc định 1 giường) + phut_moi_khach = 60/N.
return new class extends Migration {
    public function up(): void
    {
        // HN — Fix slot=12 sai (đợt cũ seed nhầm) theo rule user "60/thời gian DV".
        $hnPhongById = [
            // id => [slot, phut_moi_khach, ghi_chu]
            1  => [1, 30, 'Ngoại — khám 30\'/khách'],
            2  => [1, 30, 'Chuyên gia'],
            3  => [1, 30, 'Nội 1 — Khám Nội 30\''],
            4  => [1, 30, 'Nội 2'],
            5  => [1, 25, 'Siêu âm 25\''],
        ];
        foreach ($hnPhongById as $id => [$slot, $phut, $note]) {
            DB::table('phong')
                ->where('id', $id)->where('co_so_id', 1)
                ->update(['so_slot_toi_da' => $slot, 'phut_moi_khach' => $phut, 'updated_at' => now()]);
        }

        // Fix Phòng lấy mẫu HN (đợt A set slot=2 phut=10 → 12 khách/giờ). User chốt "6 khách/giờ".
        // → 1 giường (slot=1) × phut=10 = 6 khách/giờ.
        DB::table('phong')->where('co_so_id', 1)->where('ten', 'Phòng lấy mẫu')
            ->update(['so_slot_toi_da' => 1, 'updated_at' => now()]);

        // Fix HCM: id 14 Siêu âm đang slot=24 (sai) → slot=1, phut=25.
        DB::table('phong')->where('id', 14)->where('co_so_id', 2)
            ->update(['so_slot_toi_da' => 1, 'phut_moi_khach' => 25, 'updated_at' => now()]);

        // HCM Xét nghiệm: đợt A set slot=2 phut=10 → 12 khách/giờ. Áp cùng rule HN → slot=1.
        DB::table('phong')->where('co_so_id', 2)->where('ten', 'Phòng Xét nghiệm')
            ->update(['so_slot_toi_da' => 1, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Không rollback.
    }
};
