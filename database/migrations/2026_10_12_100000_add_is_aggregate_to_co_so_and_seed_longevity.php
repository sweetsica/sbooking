<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-10-12 — "Toàn công ty" view cho admin hệ thống.
 *   - Thêm cột is_aggregate vào co_so (nullable, default false).
 *   - Seed CoSo virtual 'longevity-vn' với is_aggregate=true, active=true.
 *     Mọi màn khi $coSo->is_aggregate=true → aggregate mode (list cross-cơ sở,
 *     settings CRUD group-by cơ sở với co_so_id cụ thể).
 *   - KHÔNG xoá/sửa data cơ sở hiện có; chỉ thêm 1 row virtual.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('co_so', 'is_aggregate')) {
            Schema::table('co_so', function (Blueprint $t) {
                $t->boolean('is_aggregate')->default(false)->after('active')
                    ->comment('true = cơ sở virtual "Toàn công ty" (longevity-vn)');
            });
        }

        $exists = DB::table('co_so')->where('slug', 'longevity-vn')->first();
        if (! $exists) {
            DB::table('co_so')->insert([
                'ten'          => 'Longevity Medical — Toàn công ty',
                'slug'         => 'longevity-vn',
                'dia_chi'      => null,
                'active'       => true,
                'is_aggregate' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } else {
            DB::table('co_so')->where('slug', 'longevity-vn')->update([
                'is_aggregate' => true,
                'active'       => true,
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('co_so')->where('slug', 'longevity-vn')->delete();
        if (Schema::hasColumn('co_so', 'is_aggregate')) {
            Schema::table('co_so', function (Blueprint $t) {
                $t->dropColumn('is_aggregate');
            });
        }
    }
};
