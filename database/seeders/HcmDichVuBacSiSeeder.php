<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed pivot dich_vu_bac_si HCM theo ảnh 3 (mapping dv ↔ nhân sự thực hiện).
 *
 * Phụ thuộc:
 *   - HcmDichVuResetSeeder đã chạy (cần dv id).
 *   - BacSiKtvDdSeeder đã chạy (cần bs/ktv/dd id — bao gồm Bsi Quỳnh + Y sĩ Thuận).
 *
 * Idempotent qua unique(dich_vu_id, bac_si_id) — check trước khi insert.
 */
class HcmDichVuBacSiSeeder extends Seeder
{
    public function run(): void
    {
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();
        $now = now();

        // Map tên → id, lọc theo cơ sở.
        $bsIdByTen = DB::table('bac_si')->where('co_so_id', $csHcm->id)
            ->pluck('id', 'ten')->all();
        $dvIdByTen = DB::table('dich_vu')->where('co_so_id', $csHcm->id)
            ->pluck('id', 'ten')->all();

        // Nhóm nhân sự thường tái sử dụng
        $ddNhom = ['DD Thu Loan', 'DD Tam Tuấn', 'DD Hồng Gấm'];
        $ktvNhom = ['KTV Thúy Kiều', 'KTV Thùy', 'KTV Huyền', 'KTV Chính'];

        // [dv_ten => [bs_ten...]]
        $mapping = [
            'Khám Da liễu (bác sĩ)'          => ['Bác sĩ Lê Huy Thư'],
            'Khám Da (Visia)'                => ['Bác sĩ Lê Huy Thư'],
            'Siêu âm một vùng'               => ['Bác sĩ Hoàng Văn Đông', 'Bác sĩ Đức'],
            'Siêu âm toàn diện'              => ['Bác sĩ Hoàng Văn Đông', 'Bác sĩ Đức'],
            'Khám Nội (sắp triển khai)'      => ['Bác sĩ Hoàng Văn Đông', 'Bác sĩ Quỳnh'],
            'EAQ Thủy châm (1 vùng)'         => array_merge(['Bác sĩ Danh', 'Bác sĩ Duy Anh'], $ddNhom),
            'EAQ Thủy châm (toàn bộ)'        => array_merge(['Bác sĩ Danh', 'Bác sĩ Duy Anh'], $ddNhom),
            'Tiêm BJR (1 khớp)'              => array_merge(['Bác sĩ Hoàng Văn Đông'], $ddNhom),
            'Tiêm HA 1% (1 khớp)'            => array_merge(['Bác sĩ Hoàng Văn Đông'], $ddNhom),
            'Tiêm HA 2% (1 khớp)'            => array_merge(['Bác sĩ Hoàng Văn Đông'], $ddNhom),
            'Tiêm PRP (1 khớp)'              => array_merge(['Bác sĩ Hoàng Văn Đông'], $ddNhom),
            'Tiêm PRF (1 khớp)'              => array_merge(['Bác sĩ Hoàng Văn Đông'], $ddNhom),
            'Y học Phương Đông'              => ['Bác sĩ Danh', 'Y sĩ Thuận', 'Bác sĩ Duy Anh'],
            'Recell'                         => $ktvNhom,
        ];

        $created = 0; $missing = [];
        foreach ($mapping as $dvTen => $bsTens) {
            $dvId = $dvIdByTen[$dvTen] ?? null;
            if (! $dvId) { $missing[] = "dv: {$dvTen}"; continue; }

            foreach ($bsTens as $bsTen) {
                $bsId = $bsIdByTen[$bsTen] ?? null;
                if (! $bsId) { $missing[] = "bs: {$bsTen}"; continue; }

                $exists = DB::table('dich_vu_bac_si')
                    ->where('dich_vu_id', $dvId)->where('bac_si_id', $bsId)->exists();
                if ($exists) continue;

                DB::table('dich_vu_bac_si')->insert([
                    'dich_vu_id' => $dvId,
                    'bac_si_id'  => $bsId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $created++;
            }
        }

        $this->command?->info("HcmDichVuBacSiSeeder: tạo {$created} pivot dv↔nhân sự HCM.");
        if ($missing) {
            $this->command?->warn('Không tìm thấy (kiểm tra tên): ' . implode(' | ', array_unique($missing)));
        }
    }
}
