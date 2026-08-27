<?php

namespace Database\Seeders;

use App\Models\KhungGio;
use App\Models\Phong;
use Illuminate\Database\Seeder;

/**
 * Backfill khung_gio cho phòng chưa có (migration 2026_08_24_150000 insert
 * 6 phòng HN mới nhưng quên tạo khung_gio → API /sync/khung-gio trả empty).
 *
 * Rule: mỗi phòng, tạo khung 5' (phong_kham) hoặc theo phut_moi_khach (phong_dich_vu),
 * từ 08:00 tới 18:00, chỉ cho phòng chưa có khung.
 */
class EnsurePhongKhungGioSeeder extends Seeder
{
    public function run(): void
    {
        $totalAdded = 0;
        foreach (Phong::doesntHave('khungGios')->get() as $phong) {
            $khungLen = $phong->phut_moi_khach ?: ($phong->kieu_phong === 'phong_dich_vu' ? 30 : 5);
            $soKhung = intdiv(600, $khungLen); // 10 tiếng 08:00–18:00 chia đều.
            for ($i = 0; $i < $soKhung; $i++) {
                $startMin = 8 * 60 + $i * $khungLen;
                $endMin   = $startMin + $khungLen;
                KhungGio::create([
                    'phong_id'     => $phong->id,
                    'gio_bat_dau'  => sprintf('%02d:%02d:00', intdiv($startMin, 60), $startMin % 60),
                    'gio_ket_thuc' => sprintf('%02d:%02d:00', intdiv($endMin, 60), $endMin % 60),
                    'thu_tu'       => $i,
                ]);
            }
            $totalAdded += $soKhung;
            $this->command?->info("  ↳ phong #{$phong->id} {$phong->ten}: +{$soKhung} khung {$khungLen}'");
        }
        $this->command?->info("Backfill khung_gio: {$totalAdded} khung.");
    }
}
