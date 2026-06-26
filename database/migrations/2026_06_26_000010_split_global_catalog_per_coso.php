<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tách danh mục dùng chung (dich_vu, menu có co_so_id = NULL) thành bản RIÊNG cho từng cơ sở,
 * để sửa ở 1 cơ sở không ảnh hưởng cơ sở khác.
 *
 * - Bản gốc (NULL) được gán cho cơ sở đầu tiên (id nhỏ nhất) — giữ nguyên id nên đơn cũ không hỏng.
 * - Các cơ sở còn lại được NHÂN BẢN mỗi mục.
 * - Booking / booking_menu của cơ sở khác (nếu lỡ trỏ vào bản gốc) được trỏ lại bản của chính cơ sở đó.
 * - Idempotent: chạy lại khi không còn bản NULL thì không làm gì.
 */
return new class extends Migration
{
    public function up(): void
    {
        $coSos = DB::table('co_so')->orderBy('id')->pluck('id')->all();
        if (count($coSos) === 0) {
            return;
        }
        $primary = $coSos[0];
        $others = array_slice($coSos, 1);
        $now = now();

        foreach (['dich_vu', 'menu'] as $table) {
            $globals = DB::table($table)->whereNull('co_so_id')->get();

            foreach ($globals as $g) {
                // Bản gốc -> cơ sở đầu tiên (giữ id, đơn cũ vẫn hợp lệ).
                DB::table($table)->where('id', $g->id)->update([
                    'co_so_id'   => $primary,
                    'updated_at' => $now,
                ]);

                // map: co_so_id => id bản ghi tương ứng
                $map = [$primary => $g->id];

                $base = (array) $g;
                unset($base['id'], $base['created_at'], $base['updated_at']);

                foreach ($others as $cs) {
                    $row = $base;
                    $row['co_so_id'] = $cs;
                    $row['created_at'] = $now;
                    $row['updated_at'] = $now;
                    $map[$cs] = DB::table($table)->insertGetId($row);
                }

                // Trỏ lại tham chiếu của các cơ sở khác (cơ sở primary đã trỏ đúng id gốc).
                if ($table === 'dich_vu') {
                    foreach ($others as $cs) {
                        DB::table('booking')
                            ->where('co_so_id', $cs)
                            ->where('dich_vu_id', $g->id)
                            ->update(['dich_vu_id' => $map[$cs]]);
                    }
                } else { // menu qua pivot booking_menu
                    foreach ($others as $cs) {
                        DB::table('booking_menu')
                            ->where('menu_id', $g->id)
                            ->whereIn('booking_id', function ($q) use ($cs) {
                                $q->select('id')->from('booking')->where('co_so_id', $cs);
                            })
                            ->update(['menu_id' => $map[$cs]]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Không thể đảo ngược an toàn (đã nhân bản dữ liệu). Để no-op.
    }
};
