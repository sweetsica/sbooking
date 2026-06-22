<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CaKham;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\LichHen;
use App\Models\Phong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder mẫu: đặt lịch trong 1 ngày cho cơ sở 59 NTN.
 *  - 5 ca / phòng × 5 phòng = 25 Booking
 *  - 6 ca / bác sĩ × N bác sĩ = N*6 LichHen
 *
 * Giả định:
 *  - Cơ sở: 59 NTN (slug = '59ntn') vì chỉ CS này có đủ 5 phòng + 6 bác sĩ.
 *  - Ngày hẹn: hôm nay. Đổi $ngay bên dưới nếu muốn ngày khác.
 *  - Khách hàng giả được tạo mới với tiền tố "KH Mẫu".
 *
 * Chạy:  php artisan db:seed --class=LichDatMauSeeder
 */
class LichDatMauSeeder extends Seeder
{
    public function run(): void
    {
        $coSo = CoSo::where('slug', '59ntn')->firstOrFail();
        $ngay = Carbon::today()->toDateString();

        // ----------------------------------------------------------------
        // 1) Booking: 5 ca / phòng × 5 phòng
        // ----------------------------------------------------------------
        $phongs = Phong::where('co_so_id', $coSo->id)
            ->where('loai', 'kham')
            ->orderBy('id')
            ->take(5)
            ->get();

        if ($phongs->count() < 5) {
            $this->command?->warn("CS {$coSo->ten} chỉ có {$phongs->count()} phòng — bỏ qua phần Booking.");
        }

        $stt = 0;
        foreach ($phongs as $phong) {
            $khungGios = KhungGio::where('phong_id', $phong->id)
                ->orderBy('thu_tu')
                ->take(5)
                ->get();

            foreach ($khungGios as $kg) {
                $stt++;
                $kh = KhachHang::create([
                    'co_so_id'      => $coSo->id,
                    'ho_ten'        => 'KH Mẫu ' . str_pad($stt, 3, '0', STR_PAD_LEFT),
                    'so_dien_thoai' => '0900' . str_pad((string) $stt, 6, '0', STR_PAD_LEFT),
                    'email'         => "kh_mau_{$stt}@example.local",
                ]);

                Booking::create([
                    'co_so_id'       => $coSo->id,
                    'khach_hang_id'  => $kh->id,
                    'phong_id'       => $phong->id,
                    'khung_gio_id'   => $kg->id,
                    'ngay_dat'       => $ngay,
                    'gio_thuc_hien'  => $kg->gio_bat_dau,
                    'gio_ket_thuc'   => $kg->gio_ket_thuc,
                    'nguon'          => 'seed',
                    'trang_thai'     => 'cho_duyet',
                    'da_duyet'       => false,
                ]);
            }
        }

        // ----------------------------------------------------------------
        // 2) LichHen: 6 ca / bác sĩ
        // ----------------------------------------------------------------
        $bacSis = User::where('co_so_id', $coSo->id)
            ->whereHas('vaiTro', fn ($q) => $q->whereIn('ma', ['bac_si', 'bac_si_tu_van']))
            ->get();

        foreach ($bacSis as $bs) {
            $caKhams = CaKham::where('user_id', $bs->id)
                ->orderBy('thu_tu')
                ->take(6)
                ->get();

            foreach ($caKhams as $ca) {
                $stt++;
                $kh = KhachHang::create([
                    'co_so_id'      => $coSo->id,
                    'ho_ten'        => 'KH Mẫu ' . str_pad($stt, 3, '0', STR_PAD_LEFT),
                    'so_dien_thoai' => '0900' . str_pad((string) $stt, 6, '0', STR_PAD_LEFT),
                    'email'         => "kh_mau_{$stt}@example.local",
                ]);

                LichHen::create([
                    'co_so_id'        => $coSo->id,
                    'khach_hang_id'   => $kh->id,
                    'bac_si_user_id'  => $bs->id,
                    'ca_kham_id'      => $ca->id,
                    'ngay_hen'        => $ngay,
                    'nguon'           => 'seed',
                    'trang_thai'      => 'cho_duyet',
                ]);
            }
        }

        $this->command?->info("Đã seed lịch mẫu cho ngày {$ngay} tại {$coSo->ten}.");
    }
}
