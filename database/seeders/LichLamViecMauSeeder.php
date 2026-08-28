<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\LichLamViec;
use App\Models\NgayNghi;
use App\Models\Phong;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seed dữ liệu MẪU cho Lịch làm việc (đã duyệt) + Ngày nghỉ, 3 cơ sở × 3 tháng
 * (tháng hiện tại + 2 tháng kế tiếp). Bác sĩ = danh mục bac_si (pivot phong_bac_si);
 * KTV = ktv mặc định của phòng dịch vụ (user).
 */
class LichLamViecMauSeeder extends Seeder
{
    /** Số tháng seed kể từ tháng hiện tại (>=1). */
    private const MONTHS_AHEAD = 3;

    /** Cơ sở cần seed. */
    private const CO_SO_SLUGS = ['59ntn', '207nvt', '23tdn'];

    public function run(): void
    {
        foreach (self::CO_SO_SLUGS as $slug) {
            $coSo = CoSo::where('slug', $slug)->first();
            if (! $coSo) {
                $this->command?->warn("Bỏ qua {$slug}: không tìm thấy cơ sở.");
                continue;
            }

            $phongBacSi = Phong::where('co_so_id', $coSo->id)
                ->where('kieu_phong', 'phong_kham')
                ->with('bacSis')->get();
            $phongDichVu = Phong::where('co_so_id', $coSo->id)
                ->where('kieu_phong', 'phong_dich_vu')
                ->whereNotNull('ktv_mac_dinh_id')->get();
            $bsNghi = BacSi::where('co_so_id', $coSo->id)->orderBy('id')->first();

            // Dọn ngày nghỉ mẫu cũ 1 lần cho cơ sở (không phụ thuộc tháng).
            NgayNghi::where('co_so_id', $coSo->id)->where('ly_do', 'like', 'Seed mẫu%')->delete();

            for ($m = 0; $m < self::MONTHS_AHEAD; $m++) {
                $thang = Carbon::now()->startOfMonth()->addMonths($m);
                $this->seedThang($coSo, $thang, $phongBacSi, $phongDichVu, $bsNghi);
            }
        }
    }

    private function seedThang(CoSo $coSo, Carbon $thang, $phongBacSi, $phongDichVu, ?BacSi $bsNghi): void
    {
        // Dọn lịch cũ của tháng để chạy lại sạch.
        LichLamViec::where('co_so_id', $coSo->id)->whereDate('thang', $thang->toDateString())->delete();

        $lich = LichLamViec::create([
            'co_so_id'    => $coSo->id,
            'thang'       => $thang->toDateString(),
            'trang_thai'  => 'da_duyet',
            'ghi_chu'     => 'Lịch mẫu tự sinh',
            'applied_at'  => now(),
        ]);

        $rows = [];
        $daysInMonth = (int) $thang->copy()->endOfMonth()->format('d');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ngay = $thang->copy()->day($d);
            if ($ngay->isSunday()) {
                continue; // Chủ nhật nghỉ
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

        // Ngày nghỉ mẫu cho tháng này (nghỉ lễ toàn cơ sở + BS nghỉ phép).
        NgayNghi::create([
            'co_so_id'     => $coSo->id,
            'loai'         => 'co_so',
            'doi_tuong_id' => null,
            'tu_ngay'      => $thang->copy()->day(15)->toDateString(),
            'den_ngay'     => $thang->copy()->day(15)->toDateString(),
            'ca'           => 'ca_ngay',
            'ly_do'        => 'Seed mẫu: nghỉ lễ toàn cơ sở',
        ]);
        if ($bsNghi) {
            NgayNghi::create([
                'co_so_id'     => $coSo->id,
                'loai'         => 'bac_si',
                'doi_tuong_id' => $bsNghi->id,
                'tu_ngay'      => $thang->copy()->day(20)->toDateString(),
                'den_ngay'     => $thang->copy()->day(21)->toDateString(),
                'ca'           => 'ca_ngay',
                'ly_do'        => 'Seed mẫu: bác sĩ nghỉ phép',
            ]);
        }

        $this->command?->info("✓ {$coSo->slug} — tháng " . $thang->format('m/Y'));
    }
}
