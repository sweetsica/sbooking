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
 * Seed nhân sự HCM (Team Ashley + DM HCM) — đồng bộ CRM lara-scrm.
 *
 * Mapping:
 *   - Cơ sở: `207nvt` (Cơ sở 2 - 207 Nguyễn Văn Thủ) — reuse, không tạo mới.
 *   - Email trùng CRM, username = prefix email.
 *   - Password: `207@nvt`.
 *
 * Role:
 *   - CM (Clinic Manager) + DM HCM → quan_tri_van_hanh (duyệt + cập nhật)
 *   - TL / SHC / HC / Assistant CM → dn_full_flow (union quyền: xem + thêm + sửa booking, sửa lịch tư vấn, bình luận)
 */
class HcmStaffSeeder extends Seeder
{
    public function run(): void
    {
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();

        $pbSales  = PhongBan::where(['co_so_id' => $csHcm->id, 'ma' => 'sales'])->firstOrFail();
        $pbTuVan  = PhongBan::where(['co_so_id' => $csHcm->id, 'ma' => 'tu_van'])->firstOrFail();

        $vrVanHanh = VaiTro::where('ma', 'quan_tri_van_hanh')->firstOrFail();

        // Role "Sale full flow" — reuse code `dn_full_flow` từ DaNangStaffSeeder (đổi tên cho clear intent).
        $vrSaleFull = VaiTro::updateOrCreate(
            ['ma' => 'dn_full_flow'],
            ['ten' => 'Sale full flow (tele + booking + sale)']
        );
        foreach (['xem_booking_cua_toi', 'them_booking', 'sua_booking', 'sua_lich_tu_van', 'binh_luan_booking', 'cap_nhat_trang_thai_khach'] as $q) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vrSaleFull->id, 'truong' => $q]);
        }

        $matKhau = Hash::make('207@nvt');

        $users = [
            // DM HCM (khu vực)
            ['username' => 'tnkn', 'name' => 'Trần Nguyễn Kim Ngân', 'email' => 'tnkn@longevity.com.vn', 'chuc_danh' => 'DM HCM', 'vai_tro_id' => $vrVanHanh->id, 'phong_ban_id' => $pbSales->id],
            // CM
            ['username' => 'tbt',  'name' => 'Trần Thị Bích Trâm',  'email' => 'tbt@longevity.com.vn',  'chuc_danh' => 'Clinic Manager',  'vai_tro_id' => $vrVanHanh->id,  'phong_ban_id' => $pbSales->id],
            ['username' => 'hbtl', 'name' => 'Huỳnh Bùi Thanh Lan', 'email' => 'hbtl@longevity.com.vn', 'chuc_danh' => 'Clinic Manager',  'vai_tro_id' => $vrVanHanh->id,  'phong_ban_id' => $pbSales->id],
            ['username' => 'nmt',  'name' => 'Nguyễn Thị Minh Thư', 'email' => 'nmt@longevity.com.vn',  'chuc_danh' => 'Assistant CM HCM', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbSales->id],
            // TL
            ['username' => 'ptkq', 'name' => 'Phan Trần Khánh Quỳn', 'email' => 'ptkq@longevity.com.vn', 'chuc_danh' => 'Team Leader', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbSales->id],
            // SHC + HC (sale)
            ['username' => 'tyn',  'name' => 'Trương Thị Yến Nhi',  'email' => 'tyn@longevity.com.vn',  'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'nhn',  'name' => 'Nguyễn Thị Hoài Như', 'email' => 'nhn@longevity.com.vn',  'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'hmm',  'name' => 'Huỳnh Thị My My',     'email' => 'hmm@longevity.com.vn',  'chuc_danh' => 'HC',  'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'ntt2', 'name' => 'Nguyễn Thị Thanh',    'email' => 'ntt2@longevity.com.vn', 'chuc_danh' => 'HC',  'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'nkc',  'name' => 'Nguyễn Thị Kim Chi',  'email' => 'nkc@longevity.com.vn',  'chuc_danh' => 'HC',  'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
            ['username' => 'lpd',  'name' => 'Lê Phát Đạt',         'email' => 'lpd@longevity.com.vn',  'chuc_danh' => 'SHC', 'vai_tro_id' => $vrSaleFull->id, 'phong_ban_id' => $pbTuVan->id],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], [
                'name' => $u['name'], 'email' => $u['email'], 'chuc_danh' => $u['chuc_danh'],
                'password' => $matKhau, 'co_so_id' => $csHcm->id,
                'phong_ban_id' => $u['phong_ban_id'], 'vai_tro_id' => $u['vai_tro_id'], 'is_admin' => false,
            ]);
        }

        $this->command?->info('HcmStaffSeeder: 11 nhân sự HCM đã đồng bộ (cơ sở 207nvt).');
    }
}
