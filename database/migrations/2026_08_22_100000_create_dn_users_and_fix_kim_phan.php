<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2026-08-22 — Bên sbooking đang thiếu 8 user DN (chỉ có Admin ĐN sb#41 và
 *   Lương Thị Kim Phấn sb#19 nhưng đang bị gắn sai co_so=1/phong_ban=30).
 *   Migration này:
 *   - Fix Kim Phấn về co_so=3, phong_ban=16 (Admin Vận hành DN), vai_tro=2
 *     (quan_tri_van_hanh), username 'dn.cms01' để khớp SCRM auto-map.
 *   - Tạo 8 user còn lại theo mapping SCRM #10..#17.
 *   Password đồng bộ convention: 'l23@tdn'. Chạy sau → chạy `sb:sync-users`
 *   bên SCRM để backfill sbooking_user_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pass = Hash::make('l23@tdn');
        $now = now();

        // 1. Fix Kim Phấn sb#19
        $fixed = DB::table('users')->where('id', 19)->update([
            'co_so_id' => 3,
            'phong_ban_id' => 16,
            'vai_tro_id' => 2,
            'username' => 'dn.cms01',
            'email' => 'dn.cms01@longevity.com.vn',
            'chuc_danh' => 'CM',
            'updated_at' => $now,
        ]);

        // 2. Tạo 8 user DN mới
        $users = [
            ['name' => 'Nguyễn Thị Bông',       'username' => 'dn.tl01',   'chuc_danh' => 'TL',   'phong_ban_id' => 15, 'vai_tro_id' => 3],
            ['name' => 'Nguyễn Thị Ánh Nhung',  'username' => 'dn.sale01', 'chuc_danh' => 'HC',   'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Lê Thị Hoàng Uyên',     'username' => 'dn.sale02', 'chuc_danh' => 'HC',   'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Lương Thị Kim Hiếu',    'username' => 'dn.sale03', 'chuc_danh' => 'HC',   'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Sử Trung Kiên',         'username' => 'dn.sale04', 'chuc_danh' => 'Tele', 'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Lương Thị Tường Vy',    'username' => 'dn.sale05', 'chuc_danh' => 'Tele', 'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Trần Ngọc An Hoà',      'username' => 'dn.sale06', 'chuc_danh' => 'Tele', 'phong_ban_id' => 15, 'vai_tro_id' => 10],
            ['name' => 'Nguyễn Thị Mỹ Hạnh',    'username' => 'dn.sale07', 'chuc_danh' => 'Tele', 'phong_ban_id' => 15, 'vai_tro_id' => 10],
        ];

        $created = 0;
        $skipped = 0;
        foreach ($users as $u) {
            $exists = DB::table('users')->where('username', $u['username'])->exists();
            if ($exists) { $skipped++; continue; }
            DB::table('users')->insert([
                'co_so_id' => 3,
                'phong_ban_id' => $u['phong_ban_id'],
                'vai_tro_id' => $u['vai_tro_id'],
                'is_admin' => 0,
                'is_tu_van' => 0,
                'nhan_tu_van' => 0,
                'phut_tu_van' => 30,
                'nhan_kham_ls' => 0,
                'phut_kham_ls' => 5,
                'name' => $u['name'],
                'chuc_danh' => $u['chuc_danh'],
                'dung_nhan_lead' => 0,
                'username' => $u['username'],
                'email' => $u['username'] . '@longevity.com.vn',
                'password' => $pass,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $created++;
        }

        if (app()->runningInConsole()) {
            echo "  → Fix Kim Phấn sb#19: {$fixed} row\n";
            echo "  → Tạo user DN mới: {$created}, skip (đã có): {$skipped}\n";
            echo "  → Password mặc định: 'l23@tdn' — nhớ chạy `php artisan sb:sync-users` bên SCRM để auto-map\n";
        }
    }

    public function down(): void
    {
        // No-op: không rollback tạo user (tránh mất data nếu có booking gắn tên).
    }
};
