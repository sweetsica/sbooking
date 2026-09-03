<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed 14 phòng HCM sau khi migration reset_hcm_phong_dich_vu đã clear.
 *
 * Format:
 *   ten, kieu_phong, duoc_dat_tu_van, loai, so_slot_toi_da, phut_moi_khach
 *
 * phut_moi_khach lấy theo dv chính của phòng (đã chốt với PKD 2026-09-03).
 */
class HcmPhongResetSeeder extends Seeder
{
    public function run(): void
    {
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();
        $now = now();

        $rows = [
            ['Phòng Xét nghiệm - T1',        'phong_kham',     0, 1, 5],
            ['Phòng Da liễu - T1',           'phong_kham',     1, 1, 15],
            ['Phòng Siêu âm - T2',           'phong_kham',     0, 1, 10],
            ['Phòng Bác sĩ Nội - T2',        'phong_kham',     1, 1, 30],
            ['Phòng Khách - T2',             'phong_dich_vu',  0, 1, 15],
            ['Phòng Thủ thuật - T2',         'phong_dich_vu',  0, 1, 15],
            ['Phòng Dịch vụ 1 (Trái) - T3',  'phong_dich_vu',  0, 3, 45],
            ['Phòng Dịch vụ 2 (Giữa) - T3',  'phong_dich_vu',  0, 3, 45],
            ['Phòng Dịch vụ 3 (Phải) - T3',  'phong_dich_vu',  0, 2, 45],
            ['Phòng Tiếp đón - T4',          'phong_kham',     0, 1, 30],
            ['Phòng YHCT - T6',              'phong_dich_vu',  1, 1, 60],
            ['Phòng Da liễu 1 - T6',         'phong_dich_vu',  0, 1, 15],
            ['Phòng Da liễu 2 - T6',         'phong_dich_vu',  0, 1, 15],
            ['Phòng truyền - T6',            'phong_dich_vu',  0, 1, 45],
        ];

        $inserted = 0;
        foreach ($rows as [$ten, $kieu, $tv, $slot, $phut]) {
            $exists = DB::table('phong')
                ->where('co_so_id', $csHcm->id)->where('ten', $ten)->exists();
            if ($exists) continue;

            DB::table('phong')->insert([
                'co_so_id'        => $csHcm->id,
                'ten'             => $ten,
                'kieu_phong'      => $kieu,
                'duoc_dat_tu_van' => $tv,
                'loai'            => 'kham',
                'so_slot_toi_da'  => $slot,
                'phut_moi_khach'  => $phut,
                'trang_thai'      => 'hoat_dong',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $inserted++;
        }

        $this->command?->info("HcmPhongResetSeeder: seed {$inserted}/" . count($rows) . " phòng HCM.");
    }
}
