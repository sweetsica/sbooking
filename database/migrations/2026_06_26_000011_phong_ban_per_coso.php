<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tách PHÒNG BAN theo từng cơ sở.
 *  - Thêm cột co_so_id.
 *  - Đổi unique(ma) -> unique(co_so_id, ma) để mỗi cơ sở có bộ mã riêng.
 *  - Bản dùng chung (NULL) cũ -> gán cơ sở đầu tiên (giữ id), nhân bản cho cơ sở còn lại.
 *  - Trỏ lại users.phong_ban_id theo đúng cơ sở của user.
 * Idempotent ở phần dữ liệu (không còn bản NULL thì bỏ qua).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phong_ban', function (Blueprint $table) {
            $table->foreignId('co_so_id')->nullable()->after('id')->constrained('co_so')->nullOnDelete();
        });

        Schema::table('phong_ban', function (Blueprint $table) {
            $table->dropUnique('phong_ban_ma_unique');
            $table->unique(['co_so_id', 'ma']);
        });

        $coSos = DB::table('co_so')->orderBy('id')->pluck('id')->all();
        if (count($coSos) === 0) {
            return;
        }
        $primary = $coSos[0];
        $others = array_slice($coSos, 1);
        $now = now();

        $globals = DB::table('phong_ban')->whereNull('co_so_id')->get();

        foreach ($globals as $g) {
            // Bản gốc -> cơ sở đầu tiên (giữ id, user/quyền đang trỏ vào không hỏng).
            DB::table('phong_ban')->where('id', $g->id)->update([
                'co_so_id'   => $primary,
                'updated_at' => $now,
            ]);

            $map = [$primary => $g->id];
            $base = (array) $g;
            unset($base['id'], $base['created_at'], $base['updated_at']);

            foreach ($others as $cs) {
                $row = $base;
                $row['co_so_id'] = $cs;
                $row['created_at'] = $now;
                $row['updated_at'] = $now;
                $map[$cs] = DB::table('phong_ban')->insertGetId($row);
            }

            // User của cơ sở khác đang trỏ vào bản gốc -> trỏ sang bản của chính cơ sở đó.
            foreach ($others as $cs) {
                DB::table('users')
                    ->where('co_so_id', $cs)
                    ->where('phong_ban_id', $g->id)
                    ->update(['phong_ban_id' => $map[$cs]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('phong_ban', function (Blueprint $table) {
            $table->dropUnique(['co_so_id', 'ma']);
            $table->dropConstrainedForeignId('co_so_id');
            $table->unique('ma', 'phong_ban_ma_unique');
        });
    }
};
