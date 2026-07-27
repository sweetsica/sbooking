<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mirror 7 test user HCM sang booking. Password 207@nvt, api_token sync sau bằng script tay
 * (đọc từ CRM DB → paste vào /tmp file → import).
 *
 * Role mapping (booking):
 *   - trucpage         → nhan_vien (thêm + xem booking)
 *   - cmbooking        → quan_tri_van_hanh (duyệt + cập nhật trạng thái khách)
 *   - booking1/2       → nhan_vien
 *   - cmsale           → quan_tri_van_hanh
 *   - sale1/2          → dn_full_flow (xem + thêm + sửa booking + comment)
 */
class HcmTestFlowSeeder extends Seeder
{
    public function run(): void
    {
        $cs = CoSo::where('slug', '207nvt')->firstOrFail();
        $pbSales = PhongBan::where(['co_so_id' => $cs->id, 'ma' => 'sales'])->firstOrFail();
        $pbTuVan = PhongBan::where(['co_so_id' => $cs->id, 'ma' => 'tu_van'])->firstOrFail();

        $vrNhanVien = VaiTro::where('ma', 'nhan_vien')->firstOrFail();
        $vrVanHanh  = VaiTro::where('ma', 'quan_tri_van_hanh')->firstOrFail();
        $vrDnFull   = VaiTro::where('ma', 'dn_full_flow')->firstOrFail();

        $matKhau = Hash::make('207@nvt');

        $users = [
            ['test.hcm.trucpage',  'Test HCM Trực Page',  'Team trực page', $vrNhanVien->id, $pbSales->id],
            ['test.hcm.cmbooking', 'Test HCM CM Booking', 'CM Booking',     $vrVanHanh->id,  $pbSales->id],
            ['test.hcm.booking1',  'Test HCM Booking 1',  'Team Booking',   $vrNhanVien->id, $pbSales->id],
            ['test.hcm.booking2',  'Test HCM Booking 2',  'Team Booking',   $vrNhanVien->id, $pbSales->id],
            ['test.hcm.cmsale',    'Test HCM CM Sale',    'CM Sale',        $vrVanHanh->id,  $pbSales->id],
            ['test.hcm.sale1',     'Test HCM Sale 1',     'Sale',           $vrDnFull->id,   $pbTuVan->id],
            ['test.hcm.sale2',     'Test HCM Sale 2',     'Sale',           $vrDnFull->id,   $pbTuVan->id],
        ];

        foreach ($users as [$username, $name, $chucDanh, $vrId, $pbId]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'name'         => $name,
                    'email'        => $username . '@longevity.com.vn',
                    'chuc_danh'    => $chucDanh,
                    'password'     => $matKhau,
                    'co_so_id'     => $cs->id,
                    'phong_ban_id' => $pbId,
                    'vai_tro_id'   => $vrId,
                    'is_admin'     => false,
                ]
            );
        }

        $this->command?->info('HcmTestFlowSeeder: 7 test users HCM đã đồng bộ (password 207@nvt).');
    }
}
