<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-19 — Dọn booking cũ có tiep_don_user_id / sale_id / nguoi_tao_id
 *   trỏ tới user KHÁC cơ sở → admin bấm Duyệt bị 422 "Sale không thuộc cơ sở".
 *
 * Root cause: SCRM push booking với sbooking_user_id sai (đã fix bên SCRM
 *   migration 2026_08_19_180000). Booking cũ đã lưu sai vẫn còn.
 *
 * Chiến lược: với mỗi booking, mỗi field user-ref, nếu user trỏ tới thuộc
 *   co_so KHÁC booking.co_so_id → tìm user CÙNG TÊN thuộc đúng booking.co_so_id
 *   (bỏ qua user có username kết thúc _legacy). Nếu tìm được → remap; nếu
 *   không → giữ nguyên (log skip).
 */
return new class extends Migration
{
    public function up(): void
    {
        $fields = ['tiep_don_user_id', 'sale_id', 'nguoi_tao_id', 'tiep_don_ho_tro_id'];
        $remapped = 0;
        $skipped = [];

        $bookings = DB::table('booking')->get(['id', 'co_so_id', ...$fields]);
        foreach ($bookings as $b) {
            $update = [];
            foreach ($fields as $f) {
                $uid = $b->$f ?? null;
                if (! $uid) continue;

                $sbUser = DB::table('users')->where('id', $uid)->first(['id', 'name', 'co_so_id', 'username']);
                if (! $sbUser || (int) $sbUser->co_so_id === (int) $b->co_so_id) continue;

                // User cross-cơ sở → tìm user cùng tên đúng co_so.
                $target = DB::table('users')
                    ->where('co_so_id', $b->co_so_id)
                    ->where('name', $sbUser->name)
                    ->where('username', 'not like', '%_legacy')
                    ->first(['id']);

                if ($target) {
                    $update[$f] = (int) $target->id;
                } else {
                    $skipped[] = "B#{$b->id} co_so={$b->co_so_id} {$f}=user#{$uid} ({$sbUser->name}, co_so={$sbUser->co_so_id}) → không tìm được user cùng tên ở co_so đúng";
                }
            }
            if ($update) {
                $update['updated_at'] = now();
                DB::table('booking')->where('id', $b->id)->update($update);
                $remapped++;
            }
        }

        if (app()->runningInConsole()) {
            echo "  → Remap booking user refs: {$remapped} booking updated\n";
            foreach ($skipped as $s) echo "    [SKIP] {$s}\n";
        }
    }

    public function down(): void
    {
        // No-op — không lưu snapshot cũ.
    }
};
