<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use App\Models\User;
use App\Models\VaiTro;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seed LỊCH TƯ VẤN (bảng lich_hen) cho cả tháng 6, gắn vào ca khám của bác sĩ
 * tư vấn — để timeline "Quản lý Lịch Tư Vấn" (/{slug}/lich-tu-van) hiện đủ màu:
 *   - cho_duyet (chờ duyệt) → vàng
 *   - da_duyet  (đã duyệt)  → xanh
 *   - tu_choi   (từ chối)   → ẩn khỏi đếm, ô trở lại trống
 *
 * Lưu ý: các seeder Booking (LichDatMauSeeder / LichThang6Seeder) ghi bảng
 * `bookings`, KHÔNG tạo lich_hen, nên timeline tư vấn trước đây luôn xám.
 *
 * Khớp đúng truy vấn của LichHenController::manage():
 *   BS tư vấn = vai_tro 'bac_si_tu_van' (thuộc cơ sở hoặc is_tu_van),
 *   ghép theo ca_kham_id + ngay_hen, tối đa 1 lịch (khác tu_choi) / ca / ngày.
 *
 * Có thể chạy lại nhiều lần: dữ liệu cũ (nguon = 'seed-tv-t6') bị xóa trước.
 *
 * Chạy:  php artisan db:seed --class=LichTuVanThang6Seeder
 */
class LichTuVanThang6Seeder extends Seeder
{
    private array $lyDoTuChoi = [
        'Khách báo bận, xin dời sang lịch khác.',
        'Trùng khung giờ với một lịch đã duyệt trước đó.',
        'Sai/thiếu thông tin liên hệ, không xác nhận được với khách.',
        'Khách chủ động hủy yêu cầu đặt lịch.',
        'Đặt nhầm cơ sở / sai bác sĩ.',
    ];

    private array $nguons = ['Fanpage', 'Hotline', 'Website', 'Khách quen', 'Giới thiệu'];

    public function run(): void
    {
        $start = Carbon::create(2026, 6, 1);
        $end   = Carbon::create(2026, 6, 30);

        // Dọn dữ liệu seed cũ để chạy lại sạch.
        LichHen::where('nguon', 'seed-tv-t6')->delete();
        KhachHang::where('email', 'like', 'kh_tv_t6_%@example.local')->delete();

        $vrBsTuVan = VaiTro::where('ma', 'bac_si_tu_van')->first();
        if (! $vrBsTuVan) {
            $this->command?->warn('Chưa có vai trò bac_si_tu_van — bỏ qua seed lịch tư vấn.');
            return;
        }

        $stt = 0;
        $tongTheoTT = ['cho_duyet' => 0, 'da_duyet' => 0, 'tu_choi' => 0];

        foreach (CoSo::where('active', true)->orderBy('id')->get() as $coSo) {
            $bacSis = User::where('vai_tro_id', $vrBsTuVan->id)
                ->where(fn ($q) => $q->where('co_so_id', $coSo->id)->orWhere('is_tu_van', true))
                ->with('caKhams')
                ->orderBy('id')->get();

            if ($bacSis->isEmpty()) {
                continue;
            }

            // Sale phụ trách: ưu tiên người dùng của cơ sở, fallback bất kỳ.
            $saleIds = User::where('co_so_id', $coSo->id)->pluck('id')->all()
                ?: User::pluck('id')->all();

            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                foreach ($bacSis as $bs) {
                    $slots = $bs->caKhams->sortBy('thu_tu')->values();
                    if ($slots->isEmpty()) {
                        continue;
                    }

                    foreach ($slots as $ck) {
                        // ~45% ca khám có lịch trong ngày → vừa lấp màu, vừa chừa ô trống.
                        if (rand(1, 100) > 45) {
                            continue;
                        }

                        $stt++;
                        $kh = KhachHang::create([
                            'co_so_id'      => $coSo->id,
                            'ho_ten'        => 'KH Tư Vấn ' . str_pad((string) $stt, 4, '0', STR_PAD_LEFT),
                            'so_dien_thoai' => '07' . str_pad((string) (10000000 + $stt), 8, '0', STR_PAD_LEFT),
                            'email'         => "kh_tv_t6_{$stt}@example.local",
                        ]);

                        $trangThai = $this->bocTrangThai();

                        LichHen::create([
                            'co_so_id'       => $coSo->id,
                            'khach_hang_id'  => $kh->id,
                            'bac_si_user_id' => $bs->id,
                            'ca_kham_id'     => $ck->id,
                            'sale_id'        => $saleIds[array_rand($saleIds)],
                            'ngay_hen'       => $day->toDateString(),
                            'nguon'          => 'seed-tv-t6',
                            'ghi_chu'        => rand(0, 3) === 0 ? 'Khách hẹn qua ' . $this->nguons[array_rand($this->nguons)] . '.' : null,
                            'trang_thai'     => $trangThai,
                        ]);

                        $tongTheoTT[$trangThai]++;
                    }
                }
            }
        }

        $tong = array_sum($tongTheoTT);
        $this->command?->info("Đã seed {$tong} lịch tư vấn (01/06 → 30/06/2026).");
        $this->command?->info(sprintf(
            'Chờ duyệt: %d · Đã duyệt: %d · Từ chối: %d',
            $tongTheoTT['cho_duyet'], $tongTheoTT['da_duyet'], $tongTheoTT['tu_choi']
        ));
    }

    /** Trộn 3 trạng thái: 40% đã duyệt, 35% chờ duyệt, 25% từ chối. */
    private function bocTrangThai(): string
    {
        $r = rand(1, 100);

        return match (true) {
            $r <= 40 => 'da_duyet',
            $r <= 75 => 'cho_duyet',
            default  => 'tu_choi',
        };
    }
}
