<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Đợt B2 (fix mapping HCM):
// Tiêm khớp/dịch nhờn/PRP/Recells thực tế làm ở Phòng Nội HCM ("Thủ thuật nội"),
// không phải Phòng Thủ thuật riêng như HN. Đợt B1 deactivate nhầm — reactivate.
return new class extends Migration {
    public function up(): void
    {
        // Reactivate 5 DV tiêm khớp HCM + set thoi_gian_phut theo sheet HN.
        $reactivate = [
            79 => 10,  // BJR (1 khớp) — Tiêm gối
            80 => 10,  // HA 1%/khớp — Tiêm dịch nhờn
            81 => 10,  // HA 2%/khớp — Tiêm khớp gối
            82 => 10,  // PRP/khớp
            88 => 15,  // Recells — Tiêm
        ];
        foreach ($reactivate as $id => $phut) {
            DB::table('dich_vu')
                ->where('co_so_id', 2)
                ->where('id', $id)
                ->update([
                    'active'         => 1,
                    'thoi_gian_phut' => $phut,
                    'updated_at'     => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Không rollback.
    }
};
