<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-07 — Đơn giản hóa các cột quantity của booking theo yêu cầu PKD:
 *   - Bỏ hẳn `so_luong_lo` + `dung_tich_lo` (không dùng trong nghiệp vụ hiện tại).
 *   - Đổi `so_lieu_trinh` (string, VD "1/10") → `so_luong` (unsigned int, ≥ 1) — user điền số nguyên tự do.
 *   Data hiện tại rỗng (verified 0 records) nên không cần backfill phức tạp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $t) {
            if (Schema::hasColumn('booking', 'so_luong_lo'))  $t->dropColumn('so_luong_lo');
            if (Schema::hasColumn('booking', 'dung_tich_lo')) $t->dropColumn('dung_tich_lo');
        });

        // Tách renameColumn ra migration riêng để tránh conflict Doctrine với dropColumn cùng blueprint.
        if (Schema::hasColumn('booking', 'so_lieu_trinh') && ! Schema::hasColumn('booking', 'so_luong')) {
            Schema::table('booking', function (Blueprint $t) {
                $t->renameColumn('so_lieu_trinh', 'so_luong');
            });
            // Đổi kiểu sang unsigned int nullable. Data cũ rỗng → không cần cast.
            // Dùng raw để tránh doctrine/dbal.
            DB::statement("ALTER TABLE booking MODIFY so_luong INT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking', 'so_luong') && ! Schema::hasColumn('booking', 'so_lieu_trinh')) {
            DB::statement("ALTER TABLE booking MODIFY so_luong VARCHAR(50) NULL");
            Schema::table('booking', function (Blueprint $t) {
                $t->renameColumn('so_luong', 'so_lieu_trinh');
            });
        }
        Schema::table('booking', function (Blueprint $t) {
            if (! Schema::hasColumn('booking', 'so_luong_lo'))  $t->string('so_luong_lo', 50)->nullable();
            if (! Schema::hasColumn('booking', 'dung_tich_lo')) $t->string('dung_tich_lo', 50)->nullable();
        });
    }
};
