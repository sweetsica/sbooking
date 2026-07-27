<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mirror 8 test user HN + DN sang booking side.
 */
class HnDnTestFlowSeeder extends Seeder
{
    public function run(): void
    {
        $csHn = CoSo::where('slug', '59ntn')->firstOrFail();
        $csDn = CoSo::where('slug', '23tdn')->firstOrFail();

        $vrNhanVien = VaiTro::where('ma', 'nhan_vien')->firstOrFail();
        $vrVanHanh  = VaiTro::where('ma', 'quan_tri_van_hanh')->firstOrFail();
        $vrDnFull   = VaiTro::where('ma', 'dn_full_flow')->firstOrFail();

        $hnPass = Hash::make('59@ntn');
        $dnPass = Hash::make('123456');

        $hnPbSales = PhongBan::where(['co_so_id' => $csHn->id, 'ma' => 'sales'])->firstOrFail();
        $hnPbTuVan = PhongBan::where(['co_so_id' => $csHn->id, 'ma' => 'tu_van'])->firstOrFail();
        $dnPbSales = PhongBan::where(['co_so_id' => $csDn->id, 'ma' => 'sales'])->firstOrFail();
        $dnPbTuVan = PhongBan::where(['co_so_id' => $csDn->id, 'ma' => 'tu_van'])->firstOrFail();

        $users = [
            ['test.hn.trucpage',  'Test HN Trực Page',  'Team trực page', $csHn->id, $vrNhanVien->id, $hnPbSales->id, $hnPass],
            ['test.hn.cmbooking', 'Test HN CM Booking', 'CM Booking',     $csHn->id, $vrVanHanh->id,  $hnPbSales->id, $hnPass],
            ['test.hn.booking1',  'Test HN Booking 1',  'Team Booking',   $csHn->id, $vrNhanVien->id, $hnPbSales->id, $hnPass],
            ['test.hn.cmsale',    'Test HN CM Sale',    'CM Sale',        $csHn->id, $vrVanHanh->id,  $hnPbSales->id, $hnPass],
            ['test.hn.sale1',     'Test HN Sale 1',     'Sale',           $csHn->id, $vrDnFull->id,   $hnPbTuVan->id, $hnPass],

            ['test.dn.cmsale',    'Test DN CM Sale',    'CM Sale',        $csDn->id, $vrVanHanh->id,  $dnPbSales->id, $dnPass],
            ['test.dn.sale1',     'Test DN Sale 1',     'Team sale ĐN',   $csDn->id, $vrDnFull->id,   $dnPbTuVan->id, $dnPass],
            ['test.dn.sale2',     'Test DN Sale 2',     'Team sale ĐN',   $csDn->id, $vrDnFull->id,   $dnPbTuVan->id, $dnPass],
        ];

        foreach ($users as [$un, $name, $cd, $csId, $vrId, $pbId, $pw]) {
            User::updateOrCreate(
                ['username' => $un],
                [
                    'name' => $name, 'email' => $un . '@longevity.com.vn', 'chuc_danh' => $cd,
                    'password' => $pw, 'co_so_id' => $csId, 'phong_ban_id' => $pbId,
                    'vai_tro_id' => $vrId, 'is_admin' => false,
                ]
            );
        }

        $this->command?->info('HnDnTestFlowSeeder: 5 HN + 3 DN đã đồng bộ.');
    }
}
