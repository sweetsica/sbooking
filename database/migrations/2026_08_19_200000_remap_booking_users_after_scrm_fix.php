<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-19 (rev) — Re-run remap booking user refs sau khi SCRM fix mapping.
 *
 * Migration 2026_08_19_190000 đã chạy trước SCRM mapping fix (prod pull sớm),
 * nên booking cũ vẫn chưa được remap. Migration này copy logic + rerun.
 *
 * Idempotent: nếu tất cả booking đã đúng thì không update gì.
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

                $target = DB::table('users')
                    ->where('co_so_id', $b->co_so_id)
                    ->where('name', $sbUser->name)
                    ->where('username', 'not like', '%_legacy')
                    ->first(['id']);

                if ($target) {
                    $update[$f] = (int) $target->id;
                } else {
                    $skipped[] = "B#{$b->id} co_so={$b->co_so_id} {$f}=user#{$uid} ({$sbUser->name}, co_so={$sbUser->co_so_id}) → không tìm được user cùng tên";
                }
            }
            if ($update) {
                $update['updated_at'] = now();
                DB::table('booking')->where('id', $b->id)->update($update);
                $remapped++;
            }
        }

        if (app()->runningInConsole()) {
            echo "  → Remap booking user refs (rev): {$remapped} booking updated\n";
            foreach ($skipped as $s) echo "    [SKIP] {$s}\n";
        }
    }

    public function down(): void
    {
        // No-op.
    }
};
