<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2026-08-19 — Reset password toàn bộ user HN + HCM sang convention SCRM.
 *   HN (co_so_id=1) → '59@ntn' (59 Ngô Thì Nhậm)
 *   HCM (co_so_id=2) → '207@nvt' (207 Nguyễn Văn Thụ)
 * Đồng bộ với DefaultPassword bên SCRM (branch-hn / branch-hcm).
 *
 * Idempotent. Down: no-op (không lưu password cũ).
 */
return new class extends Migration
{
    private const PASSWORD_MAP = [
        1 => '59@ntn',   // HN — 59 Ngô Thì Nhậm
        2 => '207@nvt',  // HCM — 207 Nguyễn Văn Thụ
    ];

    public function up(): void
    {
        $total = 0;
        foreach (self::PASSWORD_MAP as $coSoId => $newPassword) {
            $hash = Hash::make($newPassword);
            $n = DB::table('users')
                ->where('co_so_id', $coSoId)
                ->update([
                    'password' => $hash,
                    'updated_at' => now(),
                ]);
            $total += $n;
            if (app()->runningInConsole()) {
                echo "  → co_so_id={$coSoId}: reset {$n} user → pass '{$newPassword}'\n";
            }
        }
        if (app()->runningInConsole()) {
            echo "  → Tổng: {$total} user\n";
        }
    }

    public function down(): void
    {
        // No-op — không lưu snapshot cũ.
    }
};
