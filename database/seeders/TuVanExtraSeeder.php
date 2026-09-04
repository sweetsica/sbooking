<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed 4 DV tư vấn bổ sung 2026-09-04 (mã tham chiếu spreadsheet PKD 215/216/217/218):
 *   HCM: "Tư vấn - đọc kết quả" 30' + "Tư vấn" 30'
 *   DN : "Tư vấn - đọc kết quả" 30' + "Tư vấn" 30'
 *
 * Không gắn phòng (khong_can_phong=1) và không map BS mặc định — team sẽ tự chọn.
 * Idempotent theo (co_so_id, ten).
 */
class TuVanExtraSeeder extends Seeder
{
    public function run(): void
    {
        $csHcm = CoSo::where('slug', '207nvt')->firstOrFail();
        $csDn  = CoSo::where('slug', 'lo23tdn')->firstOrFail();
        $now = now();

        // [co_so_id, ten]
        $rows = [
            [$csHcm->id, 'Tư vấn - đọc kết quả'],
            [$csHcm->id, 'Tư vấn'],
            [$csDn->id,  'Tư vấn - đọc kết quả'],
            [$csDn->id,  'Tư vấn'],
        ];

        $created = 0; $skipped = 0;
        foreach ($rows as [$csId, $ten]) {
            $exists = DB::table('dich_vu')->where('co_so_id', $csId)->where('ten', $ten)->exists();
            if ($exists) { $skipped++; continue; }
            DB::table('dich_vu')->insert([
                'co_so_id'        => $csId,
                'ten'             => $ten,
                'thoi_gian_phut'  => 30,
                'thuoc_nhom'      => 'tu_van',
                'la_dich_vu'      => 0,
                'active'          => 1,
                'khong_can_phong' => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $created++;
        }

        $this->command?->info("TuVanExtraSeeder: tạo {$created}, bỏ qua (đã có) {$skipped}, tổng " . count($rows) . '.');
    }
}
