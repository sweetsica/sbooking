<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\Phong;
use App\Models\User;
use App\Models\VaiTro;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed lịch đặt phòng từ đầu tháng 6 tới hôm nay, đủ các trạng thái:
 *   - cho_duyet (chờ duyệt)
 *   - da_duyet  (đã duyệt)
 *   - da_xong   (đã hoàn thành — chỉ cho ngày đã qua)
 *   - tu_choi   (từ chối, kèm "Lý do từ chối")
 *
 * Có thể chạy lại nhiều lần: dữ liệu cũ (ghi_chu chứa marker '[seed-t6]') sẽ bị xóa trước.
 *
 * Chạy:  php artisan db:seed --class=LichThang6Seeder
 */
class LichThang6Seeder extends Seeder
{
    /** Lý do từ chối mẫu cho các đơn tu_choi. */
    private array $lyDoTuChoi = [
        'Khách báo bận, xin dời sang lịch khác.',
        'Trùng khung giờ với một lịch đã duyệt trước đó.',
        'Sai/thiếu thông tin liên hệ, không xác nhận được với khách.',
        'Phòng và khung giờ đã kín chỗ.',
        'Khách chưa đủ điều kiện sức khỏe để thực hiện dịch vụ.',
        'Khách chủ động hủy yêu cầu đặt lịch.',
        'Đặt nhầm cơ sở / sai dịch vụ.',
    ];

    private array $nguons = [
        'MKT — Marketing', 'MKT BR — Marketing BR', 'BDM',
        'BOD — Ban lãnh đạo giới thiệu', 'SA — Sale Appointment',
        'BA — Booking Appointment', 'WI — Walk-in',
    ];

    public function run(): void
    {
        $start = Carbon::create(2026, 6, 1);
        $today = Carbon::today();

        // Dọn dữ liệu seed cũ để chạy lại sạch sẽ.
        $oldIds = Booking::where('ghi_chu', 'like', '[seed-t6]%')
            ->orWhere('nguon', 'seed-t6') // tương thích data seed cũ trước khi đổi marker.
            ->pluck('id');
        if ($oldIds->isNotEmpty()) {
            DB::table('booking_menu')->whereIn('booking_id', $oldIds)->delete();
            Booking::whereIn('id', $oldIds)->delete();
        }
        KhachHang::where('email', 'like', 'kh_t6_%@example.local')->delete();

        $vaiTroIds = VaiTro::whereIn('ma', ['bac_si', 'bac_si_tu_van'])->pluck('id');
        $stt = 0;
        $tongTheoTT = ['cho_duyet' => 0, 'da_duyet' => 0, 'da_xong' => 0, 'tu_choi' => 0];

        foreach (CoSo::where('active', true)->orderBy('id')->get() as $coSo) {
            $phongs = Phong::where('co_so_id', $coSo->id)
                ->where('loai', 'kham')
                ->orderBy('id')->get()
                ->filter(fn ($p) => KhungGio::where('phong_id', $p->id)->exists())
                ->values();

            if ($phongs->isEmpty()) {
                continue;
            }

            $bacSis = BacSi::where('active', true)
                ->where(fn ($q) => $q->where('co_so_id', $coSo->id)->orWhere('xuat_hien_moi_co_so', true))
                ->pluck('id')->all();

            for ($day = $start->copy(); $day->lte($today); $day->addDay()) {
                $isPast = $day->lt($today);

                // 2–3 phòng hoạt động mỗi ngày
                $phongNgay = $phongs->shuffle()->take(rand(2, min(3, $phongs->count())));

                foreach ($phongNgay as $phong) {
                    $slots = KhungGio::where('phong_id', $phong->id)
                        ->orderBy('thu_tu')->get()
                        ->shuffle()->take(rand(2, 4));

                    foreach ($slots as $kg) {
                        $stt++;
                        $kh = KhachHang::create([
                            'co_so_id'      => $coSo->id,
                            'ho_ten'        => 'Khách T6-' . str_pad((string) $stt, 4, '0', STR_PAD_LEFT),
                            'so_dien_thoai' => '03' . str_pad((string) (10000000 + $stt), 8, '0', STR_PAD_LEFT),
                            'email'         => "kh_t6_{$stt}@example.local",
                        ]);

                        $trangThai = $this->bocTrangThai($isPast);
                        $coTuVan = (bool) rand(0, 1);
                        $coKhamCls = ! $coTuVan && rand(0, 1) === 1;

                        Booking::create([
                            'co_so_id'       => $coSo->id,
                            'khach_hang_id'  => $kh->id,
                            'phong_id'       => $phong->id,
                            'khung_gio_id'   => $kg->id,
                            // ~85% có phân bác sĩ (nếu cơ sở có bác sĩ), còn lại để trống.
                            'bac_si_id'      => ($bacSis && rand(1, 100) <= 85) ? $bacSis[array_rand($bacSis)] : null,
                            'ngay_dat'       => $day->toDateString(),
                            'gio_thuc_hien'  => $kg->gio_bat_dau,
                            'gio_ket_thuc'   => $kg->gio_ket_thuc,
                            'nguon'          => $this->nguons[array_rand($this->nguons)],
                            'so_lieu_trinh'  => rand(1, 8) . '/10',
                            'co_tu_van'      => $coTuVan,
                            'co_kham_cls'    => $coKhamCls,
                            // Marker "[seed-t6]" trong ghi_chu để cleanup idempotent.
                            'ghi_chu'        => '[seed-t6] ' . (rand(0, 2) === 0 ? 'Khách ưu tiên, gọi trước 30 phút.' : ''),
                            'trang_thai'     => $trangThai,
                            'da_duyet'       => in_array($trangThai, ['da_duyet', 'da_xong'], true),
                            'ly_do_tu_choi'  => $trangThai === 'tu_choi'
                                ? $this->lyDoTuChoi[array_rand($this->lyDoTuChoi)]
                                : null,
                        ]);

                        $tongTheoTT[$trangThai]++;
                    }
                }
            }
        }

        $tong = array_sum($tongTheoTT);
        $this->command?->info("Đã seed {$tong} lịch đặt phòng (01/06 → {$today->format('d/m/Y')}).");
        $this->command?->info(sprintf(
            'Chờ duyệt: %d · Đã duyệt: %d · Đã xong: %d · Từ chối: %d',
            $tongTheoTT['cho_duyet'], $tongTheoTT['da_duyet'], $tongTheoTT['da_xong'], $tongTheoTT['tu_choi']
        ));
    }

    /** Bốc trạng thái theo phân phối; ngày đã qua mới có "đã xong". */
    private function bocTrangThai(bool $isPast): string
    {
        $r = rand(1, 100);
        if ($isPast) {
            // 35% đã xong, 30% đã duyệt, 20% từ chối, 15% chờ duyệt
            return match (true) {
                $r <= 35 => 'da_xong',
                $r <= 65 => 'da_duyet',
                $r <= 85 => 'tu_choi',
                default  => 'cho_duyet',
            };
        }

        // Hôm nay: 45% đã duyệt, 35% chờ duyệt, 20% từ chối
        return match (true) {
            $r <= 45 => 'da_duyet',
            $r <= 80 => 'cho_duyet',
            default  => 'tu_choi',
        };
    }
}
