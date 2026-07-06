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
 * Seed dữ liệu MẪU cho Lịch làm việc (đã duyệt) + Ngày nghỉ của tháng hiện tại,
 * cơ sở 59 NTN. Bác sĩ = DANH MỤC bac_si (gán vào phòng qua phong_bac_si);
 * KTV = ktv mặc định của phòng dịch vụ (tài khoản user).
 */
class LichLamViecMauSeeder extends Seeder
{
    public function run(): void
    {
        $coSo = CoSo::where('slug', '59ntn')->first();
        if (! $coSo) {
            return;
        }

        $thang = Carbon::now()->startOfMonth();

        // Dọn lịch cũ của tháng để chạy lại sạch.
        LichLamViec::where('co_so_id', $coSo->id)->whereDate('thang', $thang->toDateString())->delete();
        NgayNghi::where('co_so_id', $coSo->id)->where('ly_do', 'like', 'Seed mẫu%')->delete();

        $lich = LichLamViec::create([
            'co_so_id'    => $coSo->id,
            'thang'       => $thang->toDateString(),
            'trang_thai'  => 'da_duyet',
            'ghi_chu'     => 'Lịch mẫu tự sinh',
            'applied_at'  => now(),
        ]);

        // Bác sĩ trực theo phòng (từ pivot phong_bac_si) — cả tháng, ca sáng + chiều.
        $phongBacSi = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_kham')
            ->with('bacSis')->get();

        // KTV trực theo phòng dịch vụ (dùng ktv mặc định của phòng nếu có).
        $phongDichVu = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_dich_vu')
            ->whereNotNull('ktv_mac_dinh_id')->get();

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

        // --- Ngày nghỉ mẫu ---
        $bsNghi = BacSi::where('co_so_id', $coSo->id)->orderBy('id')->first();
        $ngayNghi = [
            // Nghỉ toàn cơ sở 1 ngày (giữa tháng)
            ['loai' => 'co_so', 'doi_tuong_id' => null,
             'tu_ngay' => $thang->copy()->day(15)->toDateString(), 'den_ngay' => $thang->copy()->day(15)->toDateString(),
             'ca' => 'ca_ngay', 'ly_do' => 'Seed mẫu: nghỉ lễ toàn cơ sở'],
        ];
        if ($bsNghi) {
            $ngayNghi[] = ['loai' => 'bac_si', 'doi_tuong_id' => $bsNghi->id,
                'tu_ngay' => $thang->copy()->day(20)->toDateString(), 'den_ngay' => $thang->copy()->day(21)->toDateString(),
                'ca' => 'ca_ngay', 'ly_do' => 'Seed mẫu: bác sĩ nghỉ phép'];
        }
        foreach ($ngayNghi as $nn) {
            NgayNghi::create($nn + ['co_so_id' => $coSo->id]);
        }

        $this->command?->info('Đã seed lịch làm việc + ngày nghỉ mẫu tháng ' . $thang->format('m/Y') . ' cho 59 NTN.');
    }
}
