<?php

namespace Database\Seeders;

use App\Models\CoSo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed dịch vụ + phòng + pivot cho cơ sở Đà Nẵng (lo23tdn).
 *
 * Nguồn: yêu cầu 2026-09-04 — bổ sung DV #214 (mã tham chiếu spreadsheet PKD):
 *   "Thăm khám lâm sàng (trừ tim mạch)" 5' — Phòng Xét nghiệm - T2
 *   BS Mai Tấn Mẫn + KTV Nguyễn Thị Phượng.
 *
 * Phụ thuộc: LongevitySeeder (cơ sở DN + slot phòng cơ bản),
 *            BacSiKtvDdSeeder (Mai Tấn Mẫn + Nguyễn Thị Phượng).
 * Idempotent: check trước insert (phong / dich_vu / pivot).
 */
class DnDichVuSeeder extends Seeder
{
    public function run(): void
    {
        $csDn = CoSo::where('slug', 'lo23tdn')->firstOrFail();
        $now = now();

        // 1) Phòng khám lâm sàng cho DN.
        $phongTen = 'Phòng Xét nghiệm - T2';
        $phongId = DB::table('phong')->where('co_so_id', $csDn->id)->where('ten', $phongTen)->value('id');
        if (! $phongId) {
            $phongId = DB::table('phong')->insertGetId([
                'co_so_id'   => $csDn->id,
                'ten'        => $phongTen,
                'kieu_phong'     => 'phong_kham',
                'so_slot_toi_da' => 1,
                'active'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2) Dịch vụ — DV kham_ls, 5', không phải "la_dich_vu" (giữ giống HCM row #1).
        $dvTen = 'Thăm khám lâm sàng (trừ tim mạch)';
        $dvId = DB::table('dich_vu')->where('co_so_id', $csDn->id)->where('ten', $dvTen)->value('id');
        if (! $dvId) {
            $dvId = DB::table('dich_vu')->insertGetId([
                'co_so_id'       => $csDn->id,
                'ten'            => $dvTen,
                'thoi_gian_phut' => 5,
                'thuoc_nhom'     => 'kham_ls',
                'la_dich_vu'     => 0,
                'active'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // 3) Pivot dv ↔ phòng.
        $exists = DB::table('dich_vu_phong')->where('dich_vu_id', $dvId)->where('phong_id', $phongId)->exists();
        if (! $exists) {
            DB::table('dich_vu_phong')->insert([
                'dich_vu_id' => $dvId, 'phong_id' => $phongId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // 4) Pivot dv ↔ nhân sự.
        $bsIdByTen = DB::table('bac_si')->where('co_so_id', $csDn->id)
            ->whereIn('ten', ['Mai Tấn Mẫn', 'Nguyễn Thị Phượng'])
            ->pluck('id', 'ten')->all();

        $missing = [];
        foreach (['Mai Tấn Mẫn', 'Nguyễn Thị Phượng'] as $ten) {
            $bsId = $bsIdByTen[$ten] ?? null;
            if (! $bsId) { $missing[] = $ten; continue; }
            $pivotExists = DB::table('dich_vu_bac_si')
                ->where('dich_vu_id', $dvId)->where('bac_si_id', $bsId)->exists();
            if ($pivotExists) continue;
            DB::table('dich_vu_bac_si')->insert([
                'dich_vu_id' => $dvId, 'bac_si_id' => $bsId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $this->command?->info("DnDichVuSeeder: phong {$phongTen} + dv \"{$dvTen}\" (id {$dvId}) + pivot xong.");
        if ($missing) {
            $this->command?->warn('Chưa có trong bac_si (chạy BacSiKtvDdSeeder trước?): ' . implode(', ', $missing));
        }
    }
}
