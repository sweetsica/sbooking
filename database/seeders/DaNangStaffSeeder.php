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
 * Seed cơ sở Đà Nẵng + 9 nhân sự (đồng bộ với CRM lara-scrm).
 *
 * Mapping với CRM:
 *   - Slug cơ sở: `dn` (khớp cột `facilities.booking_co_so_slug` bên CRM).
 *   - Email trùng CRM (`ltkp@longevity.com.vn` …) → sau này link SSO không phải map thêm.
 *   - Username = phần trước @ email (cùng convention với các user Bảo IT/Tú MOD hiện có).
 *   - Mật khẩu mặc định: `123456` (đồng bộ CRM demo). Đổi tay sau khi phát cho nhân sự.
 *
 * Role bên booking:
 *   - CM Kim Phấn        → quan_tri_van_hanh (duyệt + cập nhật trạng thái khách + bình luận)
 *   - TL Bông            → tu_van_vien (xem + sửa booking + sửa lịch tư vấn)
 *   - HC (3 bạn)         → tu_van_vien
 *   - Tele (4 bạn)       → nhan_vien (thêm + xem booking)
 *
 * Chạy: `php artisan db:seed --class=DaNangStaffSeeder`
 */
class DaNangStaffSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Cơ sở Đà Nẵng đã tồn tại sẵn với slug '23tdn' (Lô 2+3 Trần Đăng Ninh) — reuse, không tạo mới.
        $csDn = CoSo::where('slug', '23tdn')->firstOrFail();

        // 2) Phòng ban chuẩn cho cơ sở DN (giống 4 cơ sở cũ).
        $phongBanChuan = [
            'sales'            => 'Kinh doanh (Sales)',
            'tu_van'           => 'Phòng tư vấn',
            'admin'            => 'Admin Vận hành',
            'phong_kham_ngoai' => 'Phòng khám Ngoại',
            'phong_chuyen_gia' => 'Phòng chuyên gia',
            'phong_kham_noi_1' => 'Phòng khám Nội 1',
            'phong_kham_noi_2' => 'Phòng khám Nội 2',
            'phong_sieu_am'    => 'Phòng siêu âm',
        ];
        $pb = [];
        foreach ($phongBanChuan as $ma => $ten) {
            $pb[$ma] = PhongBan::updateOrCreate(
                ['co_so_id' => $csDn->id, 'ma' => $ma],
                ['ten' => $ten]
            );
        }

        // 3) Vai trò.
        $vrVanHanh = VaiTro::where('ma', 'quan_tri_van_hanh')->firstOrFail();

        // Role riêng cho Đà Nẵng — team làm cả tele + booking + sale nên cần union perms
        // (xem + thêm + sửa booking + sửa lịch tư vấn + bình luận).
        $vrDnFull = VaiTro::firstOrCreate(
            ['ma' => 'dn_full_flow'],
            ['ten' => 'Nhân viên Đà Nẵng (full flow)']
        );
        foreach (['xem_booking_cua_toi', 'them_booking', 'sua_booking', 'sua_lich_tu_van', 'binh_luan_booking', 'cap_nhat_trang_thai_khach'] as $q) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vrDnFull->id, 'truong' => $q]);
        }

        // 4) 9 user Đà Nẵng.
        $matKhau = Hash::make('123456');

        $users = [
            // CM
            [
                'username' => 'ltkp', 'name' => 'Lương Thị Kim Phấn', 'email' => 'ltkp@longevity.com.vn',
                'chuc_danh' => 'CM Marketing Đà Nẵng',
                'vai_tro_id' => $vrVanHanh->id, 'phong_ban_id' => $pb['sales']->id,
            ],
            // TL
            [
                'username' => 'ntb', 'name' => 'Nguyễn Thị Bông', 'email' => 'ntb@longevity.com.vn',
                'chuc_danh' => 'Team Leader',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['sales']->id,
            ],
            // 3 HC → tư vấn viên, phòng tư vấn
            [
                'username' => 'ntan', 'name' => 'Nguyễn Thị Ánh Nhung', 'email' => 'ntan@longevity.com.vn',
                'chuc_danh' => 'HC',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['tu_van']->id,
            ],
            [
                'username' => 'lthu', 'name' => 'Lê Thị Hoàng Uyên', 'email' => 'lthu@longevity.com.vn',
                'chuc_danh' => 'HC',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['tu_van']->id,
            ],
            [
                'username' => 'ltkhi', 'name' => 'Lương Thị Kim Hiếu', 'email' => 'ltkhi@longevity.com.vn',
                'chuc_danh' => 'HC',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['tu_van']->id,
            ],
            // 4 Tele → nhân viên (thêm booking), phòng sales
            [
                'username' => 'stk', 'name' => 'Sử Trung Kiên', 'email' => 'stk@longevity.com.vn',
                'chuc_danh' => 'Tele',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['sales']->id,
            ],
            [
                'username' => 'lttv', 'name' => 'Lương Thị Tường Vy', 'email' => 'lttv@longevity.com.vn',
                'chuc_danh' => 'Tele',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['sales']->id,
            ],
            [
                'username' => 'tnah', 'name' => 'Trần Ngọc An Hoà', 'email' => 'tnah@longevity.com.vn',
                'chuc_danh' => 'Tele',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['sales']->id,
            ],
            [
                'username' => 'ntmh', 'name' => 'Nguyễn Thị Mỹ Hạnh', 'email' => 'ntmh@longevity.com.vn',
                'chuc_danh' => 'Tele',
                'vai_tro_id' => $vrDnFull->id, 'phong_ban_id' => $pb['sales']->id,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'name'         => $u['name'],
                    'email'        => $u['email'],
                    'chuc_danh'    => $u['chuc_danh'],
                    'password'     => $matKhau,
                    'co_so_id'     => $csDn->id,
                    'phong_ban_id' => $u['phong_ban_id'],
                    'vai_tro_id'   => $u['vai_tro_id'],
                    'is_admin'     => false,
                ]
            );
        }

        $this->command?->info('DaNangStaffSeeder: cơ sở "dn" + 9 nhân sự Đà Nẵng đã đồng bộ.');
    }
}
