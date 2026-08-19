<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2026-08-19 — Reset password toàn bộ user Đà Nẵng (co_so_id=3) sau report
 *   "toàn bộ Đà Nẵng không vào được". Mật khẩu mới đồng bộ với convention SCRM:
 *   Cơ sở ĐN → `l23@tdn` (Lô 23 Trần Đăng Ninh).
 *
 * Danh sách 10 user affected: xem query dưới. Idempotent — chạy nhiều lần OK.
 * Down: KHÔNG rollback được (không lưu password cũ). Nếu cần undo → user tự
 *   đổi lại qua UI Nhân sự.
 */
return new class extends Migration
{
    public function up(): void
    {
        $newPassword = 'l23@tdn';
        $hash = Hash::make($newPassword);

        $affected = DB::table('users')
            ->where('co_so_id', 3)
            ->update([
                'password' => $hash,
                'updated_at' => now(),
            ]);

        // Ghi ra log để trace.
        if (app()->runningInConsole()) {
            echo "  → reset password cho {$affected} user Đà Nẵng (co_so_id=3) — new pass: '{$newPassword}'\n";
        }
    }

    public function down(): void
    {
        // No-op: không lưu password cũ để rollback.
    }
};
