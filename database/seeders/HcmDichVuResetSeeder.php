<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed 23 dịch vụ HCM sau reset + pivot dich_vu_phong.
 *
 * Nguồn: ảnh 1 (22 dv) + split "EAQ (1 vùng)" → 2 dv "(1 vùng)" + "(toàn bộ)" theo Q4 chốt.
 * Đổi tên: BJR, PRP, HA 1 phần trăm, HA 2 phần trăm, PRF → "Tiêm ... (1 khớp)" cho đồng bộ.
 *
 * Phụ thuộc: HcmPhongResetSeeder đã chạy (cần phòng để pivot).
 */
class HcmDichVuResetSeeder extends Seeder
{
    public function run(): void
    {
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();
        $now = now();

        // [ten, thoi_gian_phut, thuoc_nhom, la_dich_vu, [tên phòng thực hiện]]
        $rows = [
            ['Thăm khám lâm sàng (trừ tim mạch)', 5,  'kham_ls', 0, ['Phòng Xét nghiệm - T1']],
            ['Siêu âm một vùng',                  10, 'kham_ls', 0, ['Phòng Siêu âm - T2']],
            ['Siêu âm toàn diện',                 5,  'kham_ls', 0, ['Phòng Siêu âm - T2']],
            ['Lấy máu',                           5,  'kham_ls', 0, ['Phòng Xét nghiệm - T1']],
            ['Khám Nội (sắp triển khai)',         30, 'tu_van',  0, ['Phòng Bác sĩ Nội - T2']],
            ['Khám Da liễu (bác sĩ)',             30, 'tu_van',  0, ['Phòng Da liễu - T1']],
            ['Gene2 me Plus',                     15, 'khac',    1, ['Phòng Khách - T2']],
            ['Gene2 me',                          15, 'khac',    1, ['Phòng Khách - T2']],
            ['TruAge',                            30, 'khac',    1, ['Phòng Xét nghiệm - T1']],
            ['Gene2 + Gene2 Plus + TruAge',       45, 'khac',    1, ['Phòng Xét nghiệm - T1', 'Phòng Khách - T2']],
            ['EAQ Thủy châm (1 vùng)',            10, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['EAQ Thủy châm (toàn bộ)',           10, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Tiêm BJR (1 khớp)',                 15, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Tiêm HA 1% (1 khớp)',               15, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Tiêm HA 2% (1 khớp)',               15, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Tiêm PRP (1 khớp)',                 15, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Tiêm PRF (1 khớp)',                 15, 'khac',    1, ['Phòng Thủ thuật - T2']],
            ['Y học Phương Đông',                 60, 'khac',    1, ['Phòng YHCT - T6']],
            ['STC Japan',                         30, 'tu_van',  0, ['Phòng Tiếp đón - T4']],
            ['Recell',                            20, 'khac',    1, ['Phòng Da liễu 1 - T6', 'Phòng Da liễu 2 - T6']],
            ['Khám Da (Visia)',                   15, 'kham_ls', 0, ['Phòng Da liễu - T1', 'Phòng Da liễu 1 - T6', 'Phòng Da liễu 2 - T6']],
            ["Thực hiện lâm sàng 5'",             5,  'kham_ls', 0, ['Phòng Xét nghiệm - T1']],
            ['Tư vấn YHCT',                       15, 'tu_van',  0, ['Phòng YHCT - T6']],
        ];

        // Nạp map tên phòng → id (nhanh hơn lookup mỗi vòng).
        $phongIdByTen = DB::table('phong')->where('co_so_id', $csHcm->id)
            ->pluck('id', 'ten')->all();

        $dvCreated = 0; $pivotCreated = 0; $missingPhong = [];
        foreach ($rows as [$ten, $phut, $nhom, $laDv, $phongTens]) {
            $dvId = DB::table('dich_vu')->where('co_so_id', $csHcm->id)->where('ten', $ten)->value('id');
            if (! $dvId) {
                $dvId = DB::table('dich_vu')->insertGetId([
                    'co_so_id'       => $csHcm->id,
                    'ten'            => $ten,
                    'thoi_gian_phut' => $phut,
                    'thuoc_nhom'     => $nhom,
                    'la_dich_vu'     => $laDv,
                    'active'         => 1,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $dvCreated++;
            }

            foreach ($phongTens as $phongTen) {
                $phongId = $phongIdByTen[$phongTen] ?? null;
                if (! $phongId) { $missingPhong[] = $phongTen; continue; }

                $exists = DB::table('dich_vu_phong')
                    ->where('dich_vu_id', $dvId)->where('phong_id', $phongId)->exists();
                if ($exists) continue;

                DB::table('dich_vu_phong')->insert([
                    'dich_vu_id' => $dvId,
                    'phong_id'   => $phongId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $pivotCreated++;
            }
        }

        $this->command?->info("HcmDichVuResetSeeder: tạo {$dvCreated} dv, {$pivotCreated} pivot dv↔phòng.");
        if ($missingPhong) {
            $this->command?->warn('Thiếu phòng (chưa seed trước?): ' . implode(', ', array_unique($missingPhong)));
        }
    }
}
