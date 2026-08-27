<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\LichLamViec;
use App\Models\NgayNghi;
use App\Models\Phong;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seed lịch làm việc (đã duyệt) cho 3 cơ sở (HN/HCM/DN),
 * tháng hiện tại + tháng sau, mọi BS/KTV đều trực Sáng + Chiều mỗi ngày (trừ CN).
 * Không seed ngày nghỉ mẫu — test env cần lịch sạch.
 */
class LichLamViecMauSeeder extends Seeder
{
    private const CO_SO_SLUGS = ['59ntn', '207nvt', 'lo23tdn'];

    public function run(): void
    {
        $thangDau = Carbon::now()->startOfMonth();
        $months   = [$thangDau->copy(), $thangDau->copy()->addMonth()];

        foreach (self::CO_SO_SLUGS as $slug) {
            $coSo = CoSo::where('slug', $slug)->first();
            if (! $coSo) {
                $this->command?->warn("Bỏ qua cơ sở '{$slug}' — không tồn tại.");
                continue;
            }

            foreach ($months as $thang) {
                $this->seedThang($coSo, $thang);
            }
        }
    }

    private function seedThang(CoSo $coSo, Carbon $thang): void
    {
        // Dọn lịch cũ của tháng này (chạy lại sạch).
        LichLamViec::where('co_so_id', $coSo->id)
            ->whereDate('thang', $thang->toDateString())
            ->delete();
        NgayNghi::where('co_so_id', $coSo->id)
            ->where('ly_do', 'like', 'Seed mẫu%')
            ->delete();

        $lich = LichLamViec::create([
            'co_so_id'   => $coSo->id,
            'thang'      => $thang->toDateString(),
            'trang_thai' => 'da_duyet',
            'ghi_chu'    => 'Lịch mẫu tự sinh',
            'applied_at' => now(),
        ]);

        $phongBacSi = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_kham')
            ->with('bacSis')->get();

        $phongDichVu = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_dich_vu')
            ->whereNotNull('ktv_mac_dinh_id')->get();

        $rows        = [];
        $daysInMonth = (int) $thang->copy()->endOfMonth()->format('d');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ngay = $thang->copy()->day($d);
            if ($ngay->isSunday()) {
                continue;
            }
            $ngayStr = $ngay->toDateString();

            foreach ($phongBacSi as $phong) {
                foreach ($phong->bacSis as $bs) {
                    foreach (['sang', 'chieu'] as $ca) {
                        $rows[] = [
                            'lich_lam_viec_id' => $lich->id,
                            'loai'         => 'bac_si',
                            'doi_tuong_id' => $bs->id,
                            'phong_id'     => $phong->id,
                            'ngay'         => $ngayStr,
                            'ca'           => $ca,
                            'ten'          => $bs->ten_day_du,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                }
            }

            foreach ($phongDichVu as $phong) {
                foreach (['sang', 'chieu'] as $ca) {
                    $rows[] = [
                        'lich_lam_viec_id' => $lich->id,
                        'loai'         => 'ktv',
                        'doi_tuong_id' => $phong->ktv_mac_dinh_id,
                        'phong_id'     => $phong->id,
                        'ngay'         => $ngayStr,
                        'ca'           => $ca,
                        'ten'          => optional($phong->ktvMacDinh)->name,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            \DB::table('lich_lam_viec_chi_tiet')->insert($chunk);
        }

        $this->command?->info(sprintf(
            'Seeded lịch %s (%s): %d rows',
            $coSo->slug,
            $thang->format('m/Y'),
            count($rows)
        ));
    }
}
