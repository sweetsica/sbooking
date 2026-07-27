<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\PhanQuyen;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed nhân sự Hà Nội (Team Giang + Team Hoi) — đồng bộ CRM lara-scrm.
 *
 * Mapping:
 *   - Cơ sở: `59ntn` (Cơ sở 1 - 59 Ngô Thì Nhậm) — reuse, không tạo mới.
 *   - Email trùng CRM, username = prefix email.
 *   - Password: `59@ntn` (đồng bộ với LongevitySeeder gốc).
 *
 * Role:
 *   - CM + TL → quan_tri_van_hanh
 *   - HC (team-hoi-booking) → nhan_vien (thêm + xem booking, đúng vai trò booking staff)
 *   - SHC (team-hoi-sale) + HC (team-giang-sale) → dn_full_flow (xem/thêm/sửa booking + sửa lịch tư vấn + bình luận)
 */
class HanoiStaffSeeder extends Seeder
{
    public function run(): void
    {
        $csHn = CoSo::where('slug', '59ntn')->firstOrFail();

        $pbSales = PhongBan::where(['co_so_id' => $csHn->id, 'ma' => 'sales'])->firstOrFail();
        $pbTuVan = PhongBan::where(['co_so_id' => $csHn->id, 'ma' => 'tu_van'])->firstOrFail();

        $vrVanHanh  = VaiTro::where('ma', 'quan_tri_van_hanh')->firstOrFail();
        $vrNhanVien = VaiTro::where('ma', 'nhan_vien')->firstOrFail();

        $vrSaleFull = VaiTro::updateOrCreate(
            ['ma' => 'dn_full_flow'],
            ['ten' => 'Sale full flow (tele + booking + sale)']
        );
        foreach (['xem_booking', 'them_booking', 'sua_booking', 'sua_lich_tu_van', 'binh_luan_booking'] as $q) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vrSaleFull->id, 'truong' => $q]);
        }

        $matKhau = Hash::make('59@ntn');

        $users = [
            // CM Team Giang + Team Hoi
            ['username' => 'ttg', 'name' => 'Trần Thị Thu Giang', 'email' => 'ttg@longevity.com.vn', 'chuc_danh' => 'Clinic Manager', 'vai_tro_id' => $vrVanHanh->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'tvh', 'name' => 'Tạ Văn Hợi',         'email' => 'tvh@longevity.com.vn', 'chuc_danh' => 'Clinic Manager', 'vai_tro_id' => $vrVanHanh->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'nhd', 'name' => 'Nguyễn Hoành Đức',   'email' => 'nhd@longevity.com.vn', 'chuc_danh' => 'Team Leader',    'vai_tro_id' => $vrVanHanh->id, 'phong_ban_id' => $pbSales->id],

            // Team Hoi booking (HC) — dedicated booking staff → nhan_vien
            ['username' => 'thk', 'name' => 'Trần Huy Kiên',      'email' => 'thk@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrNhanVien->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'nta', 'name' => 'Nguyễn Thị Anh',     'email' => 'nta@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrNhanVien->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'ptt', 'name' => 'Phạm Thanh Trúc',    'email' => 'ptt@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrNhanVien->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'pta', 'name' => 'Phạm Tú Anh',        'email' => 'pta@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrNhanVien->id, 'phong_ban_id' => $pbSales->id],
            ['username' => 'nma', 'name' => 'Nguyễn Mai Anh',     'email' => 'nma@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrNhanVien->id, 'phong_ban_id' => $pbSales->id],

            // Team Hoi sale (SHC) → sale_full_flow
            ['username' => 'nhg', 'name' => 'Nguyễn Hương Giang', 'email' => 'nhg@longevity.com.vn', 'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'ntn', 'name' => 'Nguyễn Thị Nga',     'email' => 'ntn@longevity.com.vn', 'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'cla', 'name' => 'Cao Thị Lan Anh',    'email' => 'cla@longevity.com.vn', 'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'ntt', 'name' => 'Nguyễn Thị Thúy',    'email' => 'ntt@longevity.com.vn', 'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'ntm', 'name' => 'Nguyễn Trà My',      'email' => 'ntm@longevity.com.vn', 'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],

            // Team Giang sale
            ['username' => 'nmp', 'name' => 'Nguyễn Minh Phương', 'email' => 'nmp@longevity.com.vn', 'chuc_danh' => 'HC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], [
                'name' => $u['name'], 'email' => $u['email'], 'chuc_danh' => $u['chuc_danh'],
                'password' => $matKhau, 'co_so_id' => $csHn->id,
                'phong_ban_id' => $u['phong_ban_id'], 'vai_tro_id' => $u['vai_tro_id'], 'is_admin' => false,
            ]);
        }

        $this->command?->info('HanoiStaffSeeder: 14 nhân sự HN đã đồng bộ (cơ sở 59ntn).');
    }
}
