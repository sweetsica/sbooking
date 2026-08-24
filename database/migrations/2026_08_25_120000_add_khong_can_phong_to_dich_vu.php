<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Đợt C.3.b (2026-08-25): flag DV không cần chọn phòng.
// Ứng dụng cho STC Japan (id 42 HN, 86 HCM) — làm ở nước ngoài.
// UI SCRM sẽ hide dropdown phòng + skip validation khi DV này được chọn.
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('dich_vu', 'khong_can_phong')) {
            Schema::table('dich_vu', function (Blueprint $t) {
                $t->boolean('khong_can_phong')->default(false)->after('active');
            });
        }
        // Set flag cho STC Japan
        DB::table('dich_vu')
            ->whereIn('id', [42, 86])
            ->update(['khong_can_phong' => 1, 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('dich_vu', 'khong_can_phong')) {
            Schema::table('dich_vu', function (Blueprint $t) {
                $t->dropColumn('khong_can_phong');
            });
        }
    }
};
