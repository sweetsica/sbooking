<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('phong', function (Blueprint $t) {
            // 2026-08-09: cho phép 1 phòng khám vừa nhận booking khám vừa nhận booking tư vấn.
            //   Bên scrm khi user chọn bucket "Tư vấn" → filter phòng theo cột này.
            //   Default true — mặc định mọi phòng đều được đặt tư vấn (theo yêu cầu PKD).
            $t->boolean('duoc_dat_tu_van')->default(true)->after('kieu_phong');
        });
    }

    public function down(): void
    {
        Schema::table('phong', function (Blueprint $t) {
            $t->dropColumn('duoc_dat_tu_van');
        });
    }
};
