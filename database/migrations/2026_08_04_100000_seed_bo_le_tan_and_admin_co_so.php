<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2026-08-04 (SCRM Task 2): thêm phòng "Phòng BO (Lễ Tân)" cho tất cả cơ sở đang có
 * + 3 tài khoản Admin cơ sở (admin_59ntn / admin_23tdn / admin_207nvt), vai trò `le_tan`
 * (đã có sẵn perm `duyet_booking` từ LongevitySeeder), phòng `bo_le_tan` của cơ sở tương ứng.
 *
 * Idempotent — updateOrInsert. Chạy được cả khi DB đã có sẵn user cùng username (giữ nguyên).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Thêm phòng bo_le_tan cho MỌI cơ sở đang có (dù seeder chạy hay chưa).
        $now = now();
        foreach (DB::table('co_so')->get() as $cs) {
            DB::table('phong_ban')->updateOrInsert(
                ['co_so_id' => $cs->id, 'ma' => 'bo_le_tan'],
                ['ten' => 'Phòng BO (Lễ Tân)', 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // 2) Tra role le_tan (LongevitySeeder tạo). Bỏ qua nếu môi trường chưa có role này.
        $vrLeTan = DB::table('vai_tro')->where('ma', 'le_tan')->first();
        if (! $vrLeTan) {
            return;
        }

        // 3) 3 tài khoản Admin cơ sở matching bên scrm.
        $accounts = [
            ['username' => 'admin_59ntn',  'name' => 'Admin Cơ sở 59NTN',  'cs_slug' => '59ntn',  'chuc_danh' => 'Admin cơ sở (Hà Nội)'],
            ['username' => 'admin_23tdn',  'name' => 'Admin Cơ sở 23TDN',  'cs_slug' => '23tdn',  'chuc_danh' => 'Admin cơ sở (Đà Nẵng)'],
            ['username' => 'admin_207nvt', 'name' => 'Admin Cơ sở 207NVT', 'cs_slug' => '207nvt', 'chuc_danh' => 'Admin cơ sở (HCM)'],
        ];

        foreach ($accounts as $acc) {
            $cs = DB::table('co_so')->where('slug', $acc['cs_slug'])->first();
            if (! $cs) continue;

            $pb = DB::table('phong_ban')->where('co_so_id', $cs->id)->where('ma', 'bo_le_tan')->first();
            if (! $pb) continue;

            $email = $acc['username'] . '@longevity.com.vn';
            $existing = DB::table('users')->where('username', $acc['username'])->first();

            if ($existing) {
                // Giữ nguyên password nếu đã tồn tại — chỉ update assignment.
                DB::table('users')->where('id', $existing->id)->update([
                    'name'         => $acc['name'],
                    'chuc_danh'    => $acc['chuc_danh'],
                    'co_so_id'     => $cs->id,
                    'phong_ban_id' => $pb->id,
                    'vai_tro_id'   => $vrLeTan->id,
                    'is_admin'     => false,
                    'updated_at'   => $now,
                ]);
            } else {
                DB::table('users')->insert([
                    'username'     => $acc['username'],
                    'email'        => $email,
                    'name'         => $acc['name'],
                    'chuc_danh'    => $acc['chuc_danh'],
                    'password'     => Hash::make('59@ntn'),
                    'co_so_id'     => $cs->id,
                    'phong_ban_id' => $pb->id,
                    'vai_tro_id'   => $vrLeTan->id,
                    'is_admin'     => false,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Không xóa user (có thể đã có booking gắn). Xóa phòng bo_le_tan nếu không còn user nào ref.
        DB::table('users')->whereIn('username', ['admin_59ntn', 'admin_23tdn', 'admin_207nvt'])
            ->update(['phong_ban_id' => null]);
        DB::table('phong_ban')->where('ma', 'bo_le_tan')->delete();
    }
};
