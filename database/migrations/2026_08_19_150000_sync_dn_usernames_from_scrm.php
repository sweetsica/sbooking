<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2026-08-19 — Đồng bộ username + email cho user Đà Nẵng (co_so_id=3) sang
 *   format vị trí, khớp với SCRM (RenameUsersToPositionFormatSeeder).
 *
 * Ánh xạ NAME → USERNAME theo SCRM. Chỉ áp cho user DN. Password cũng reset
 *   thành 'l23@tdn' (đồng bộ default password DN convention SCRM).
 *
 * Idempotent: user đã có username khớp → chỉ đảm bảo email + password đúng.
 * Skip nếu target username đã bị user KHÁC chiếm (log ra để admin xử tay).
 */
return new class extends Migration
{
    // Copy 1-1 từ SCRM RenameUsersToPositionFormatSeeder::NAME_TO_USERNAME (nhánh DN).
    private const DN_NAME_TO_USERNAME = [
        'Lương Thị Kim Phấn'        => 'dn.cms01',
        'Nguyễn Thị Bông'           => 'dn.tl01',
        'Nguyễn Thị Ánh Nhung'      => 'dn.sale01',
        'Lê Thị Hoàng Uyên'         => 'dn.sale02',
        'Lương Thị Kim Hiếu'        => 'dn.sale03',
        'Sử Trung Kiên'             => 'dn.sale04',
        'Lương Thị Tường Vy'        => 'dn.sale05',
        'Trần Ngọc An Hoà'          => 'dn.sale06',
        'Nguyễn Thị Mỹ Hạnh'        => 'dn.sale07',
        'Tài khoản Trực Page cơ sở ĐN' => 'dn.page01',
    ];

    public function up(): void
    {
        $newPasswordHash = Hash::make('l23@tdn');
        $updated = 0;
        $skipped = [];

        // 2026-08-19: cleanup dupe — user cùng tên "Lương Thị Kim Phấn" tồn tại ở co_so_id=1
        //   với username `dn.cms01` (misconfig cũ). Free username để user DN thật (co_so_id=3) chiếm.
        //   Không xoá vì có thể có ref lịch sử — chỉ rename thành *_legacy.
        DB::table('users')
            ->where('id', '!=', 0)
            ->where('name', 'Lương Thị Kim Phấn')
            ->where('co_so_id', '!=', 3)
            ->where('username', 'dn.cms01')
            ->update([
                'username' => 'dn.cms01_legacy',
                'email' => 'dn.cms01_legacy@longevity.com.vn',
                'updated_at' => now(),
            ]);

        DB::transaction(function () use ($newPasswordHash, &$updated, &$skipped) {
            foreach (self::DN_NAME_TO_USERNAME as $name => $targetUsername) {
                $targetEmail = $targetUsername . '@longevity.com.vn';

                // Lấy user DN theo tên (co_so_id=3). Nếu không có → skip.
                $u = DB::table('users')
                    ->where('co_so_id', 3)
                    ->where('name', $name)
                    ->first();
                if (! $u) {
                    $skipped[] = "[SKIP] Không tìm thấy user DN: {$name}";
                    continue;
                }

                // Kiểm tra collision: username/email đã bị user KHÁC chiếm?
                $conflict = DB::table('users')
                    ->where(function ($q) use ($targetUsername, $targetEmail) {
                        $q->where('username', $targetUsername)->orWhere('email', $targetEmail);
                    })
                    ->where('id', '!=', $u->id)
                    ->first();
                if ($conflict) {
                    $skipped[] = "[SKIP] Collision cho {$name} ({$targetUsername}) — user #{$conflict->id} đã chiếm ({$conflict->username} / {$conflict->email}). Xử tay: xoá hoặc rename user #{$conflict->id} trước.";
                    continue;
                }

                DB::table('users')->where('id', $u->id)->update([
                    'username'   => $targetUsername,
                    'email'      => $targetEmail,
                    'password'   => $newPasswordHash,
                    'updated_at' => now(),
                ]);
                $updated++;
            }
        });

        if (app()->runningInConsole()) {
            echo "  → sync DN username: updated={$updated}\n";
            foreach ($skipped as $s) echo "    {$s}\n";
        }
    }

    public function down(): void
    {
        // No-op — không lưu snapshot cũ để rollback.
    }
};
