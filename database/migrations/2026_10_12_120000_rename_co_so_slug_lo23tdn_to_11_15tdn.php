<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-10-12 — Đồng bộ slug cơ sở ĐN với data app.
 *   Data đã dùng slug '11-15tdn' từ 2026-09-14 (migration rename_dn_booking_co_so_slug).
 *   Sbooking vẫn ở 'lo23tdn' → push booking + sync catalog có thể lệch mapping.
 *   Fix: đổi slug sbooking 'lo23tdn' → '11-15tdn' để khớp.
 * Idempotent.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('co_so')
            ->where('slug', 'lo23tdn')
            ->update(['slug' => '11-15tdn', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('co_so')
            ->where('slug', '11-15tdn')
            ->update(['slug' => 'lo23tdn', 'updated_at' => now()]);
    }
};
