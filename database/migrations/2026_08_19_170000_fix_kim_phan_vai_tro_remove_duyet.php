<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 2026-08-19 — Fix Kim Phấn (CM Đà Nẵng) đang bị gán vai_tro=2 "Quản trị vận hành"
 *   → có luôn quyền `duyet_booking`. CM KHÔNG được duyệt lịch, đó là quyền của
 *   Admin cơ sở / Admin vận hành. Đổi sang vai_tro=13 "Nhân viên Đà Nẵng (full flow)"
 *   giống Bông (Team Leader).
 *
 * Idempotent: chỉ update nếu vai_tro hiện tại là 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        $affected = DB::table('users')
            ->where('name', 'Lương Thị Kim Phấn')
            ->where('co_so_id', 3) // chỉ ĐN, tránh nhầm user cùng tên khác cơ sở
            ->where('vai_tro_id', 2)
            ->update([
                'vai_tro_id' => 13,
                'updated_at' => now(),
            ]);

        if (app()->runningInConsole()) {
            echo "  → reassign Kim Phấn: {$affected} user (vai_tro 2 → 13)\n";
        }
    }

    public function down(): void
    {
        // Rollback về vai_tro cũ nếu cần.
        DB::table('users')
            ->where('name', 'Lương Thị Kim Phấn')
            ->where('co_so_id', 3)
            ->where('vai_tro_id', 13)
            ->update(['vai_tro_id' => 2, 'updated_at' => now()]);
    }
};
