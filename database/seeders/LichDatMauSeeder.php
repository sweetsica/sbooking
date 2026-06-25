<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\Phong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder mẫu: tạo bookings 1 ngày tại cơ sở 59 NTN, khớp với cấu hình hiện tại:
 *  - Mỗi BS có phut_tu_van / phut_kham_ls riêng
 *  - Mỗi dịch vụ có thuoc_nhom (tu_van / kham_ls / khac)
 *  - Phòng khám: gio_thuc_hien chia theo phút BS
 *  - Phòng dịch vụ: gio_thuc_hien chia theo phut_moi_khach
 *
 * Chạy:  php artisan db:seed --class=LichDatMauSeeder
 */
class LichDatMauSeeder extends Seeder
{
    public function run(): void
    {
        $coSo = CoSo::where('slug', '59ntn')->firstOrFail();
        $ngay = Carbon::today()->toDateString();

        // Reset booking cũ của ngày này để chạy lại sạch
        Booking::where('co_so_id', $coSo->id)
            ->whereDate('ngay_dat', $ngay)
            ->where('nguon', 'seed')
            ->delete();

        $sale = User::where('co_so_id', $coSo->id)->whereHas('vaiTro', fn ($q) => $q->where('ma', 'tu_van_vien'))->first()
            ?? User::where('co_so_id', $coSo->id)->first();

        // Tra cứu dịch vụ (tên đã chuẩn hoá trong LongevitySeeder)
        $dvTuVan   = DichVu::where('ten', 'Tư vấn - đọc kết quả')->first();
        $dvKhamLs  = DichVu::where('ten', 'Thăm khám lâm sàng')->first();
        $dvTimMach = DichVu::where('ten', 'Thăm khám tim mạch')->first();
        $dvSieuAm  = DichVu::where('ten', 'Siêu âm')->first();
        $dvGene    = DichVu::where('ten', 'Đọc kết quả Gene')->first();
        $dvXQuang  = DichVu::where('ten', 'Chụp XQuang')->first();
        $dvLayMau  = DichVu::where('ten', 'Lấy máu')->first();
        $dvThucHienLS = DichVu::where('ten', 'Thực hiện lâm sàng')->first();

        $stt = 0;
        $mkKhach = function () use (&$stt, $coSo) {
            $stt++;
            return KhachHang::create([
                'co_so_id'      => $coSo->id,
                'ho_ten'        => 'KH Mẫu ' . str_pad($stt, 3, '0', STR_PAD_LEFT),
                'so_dien_thoai' => '0900' . str_pad((string) $stt, 6, '0', STR_PAD_LEFT),
            ]);
        };

        $lyDoTuChoi = [
            'Khách hủy do bận đột xuất.',
            'Khách yêu cầu đổi sang ngày khác.',
            'Trùng lịch khám trước đó.',
            'Sai thông tin liên hệ, không xác nhận được.',
            'Khách không đủ điều kiện sức khỏe cho dịch vụ.',
        ];

        $mkBooking = function (Phong $phong, KhungGio $kg, string $bd, string $kt, ?User $bs, ?DichVu $dv, ?int $ktvId, string $trangThai) use ($coSo, $ngay, $sale, $mkKhach, $lyDoTuChoi, &$stt) {
            $kh = $mkKhach();
            Booking::create([
                'co_so_id'       => $coSo->id,
                'loai_dat_lich'  => $phong->kieu_phong === 'phong_dich_vu' ? 'dich_vu' : 'phong_kham',
                'khach_hang_id'  => $kh->id,
                'phong_id'       => $phong->id,
                'khung_gio_id'   => $kg->id,
                'dich_vu_id'     => $dv?->id,
                'bac_si_user_id' => $bs?->id,
                'ktv_user_id'    => $ktvId,
                'sale_id'        => $sale?->id,
                'ngay_dat'       => $ngay,
                'gio_thuc_hien'  => $bd . ':00',
                'gio_ket_thuc'   => $kt . ':00',
                'nguon'          => 'seed',
                'trang_thai'     => $trangThai,
                'da_duyet'       => $trangThai === 'da_duyet' || $trangThai === 'da_xong',
                'ly_do_tu_choi'  => $trangThai === 'tu_choi' ? $lyDoTuChoi[$stt % count($lyDoTuChoi)] : null,
            ]);
        };

        // ----------------------------------------------------------------
        // PHÒNG KHÁM — mỗi phòng có 1 BS chính. Booking chia theo phút BS.
        // ----------------------------------------------------------------
        $config = [
            // [phòng, username BS, dịch vụ, số booking trong khung 8h-9h]
            ['Phòng khám Ngoại', 'ntd',   $dvTuVan,  2, 'tu_van'],   // 2 tư vấn × 30p
            ['Phòng chuyên gia', 'lthd',  $dvTuVan,  2, 'tu_van'],   // 2 tư vấn × 30p
            ['Phòng khám Nội 1', 'ttb',   $dvKhamLs, 6, 'kham_ls'],  // 6 khám LS × 5p (chỉ chiếm 30p, để minh họa)
            ['Phòng khám Nội 2', 'ntn_bs', $dvKhamLs, 4, 'kham_ls'], // 4 khám LS × 5p
            ['Phòng siêu âm',    'bh_sa', $dvSieuAm, 2, 'khac'],    // 2 siêu âm × 25p (image)
        ];

        $trangThais = ['cho_duyet', 'da_duyet', 'cho_duyet', 'da_duyet', 'tu_choi', 'da_duyet'];

        foreach ($config as [$tenPhong, $bsUsername, $dv, $soCa, $nhom]) {
            $phong = Phong::where('co_so_id', $coSo->id)->where('ten', $tenPhong)->first();
            if (! $phong || ! $dv) continue;
            $bs = User::where('username', $bsUsername)->first();
            if (! $bs) continue;

            // Lấy phút theo nhóm
            $phut = match ($nhom) {
                'tu_van'  => (int) ($bs->phut_tu_van ?: 30),
                'kham_ls' => (int) ($bs->phut_kham_ls ?: 5),
                default   => (int) ($dv->thoi_gian_phut ?: 30),
            };

            // Loop các khung giờ cho đến khi tạo đủ $soCa booking (mỗi khung nhận tối đa N ca lọt vào khung)
            $khungs = KhungGio::where('phong_id', $phong->id)->orderBy('thu_tu')->get();
            $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
            $count = 0;

            foreach ($khungs as $kg) {
                if ($count >= $soCa) break;
                $s = $toMin(substr($kg->gio_bat_dau, 0, 5));
                $e = $toMin(substr($kg->gio_ket_thuc, 0, 5));

                for ($i = 0; $count < $soCa; $i++) {
                    $start = $s + $i * $phut;
                    if ($start + $phut > $e) break; // vượt khung → qua khung sau
                    $bd = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
                    $kt = sprintf('%02d:%02d', intdiv($start + $phut, 60), ($start + $phut) % 60);
                    $tt = $trangThais[$stt % count($trangThais)];
                    $mkBooking($phong, $kg, $bd, $kt, $bs, $dv, null, $tt);
                    $count++;
                }
            }
        }

        // BS Tim mạch (Bác Biên) → 1 booking tư vấn tim mạch ở Phòng Nội 2 khung 9h
        $bsTM = User::where('username', 'bb_tm')->first();
        $phongNoi2 = Phong::where('co_so_id', $coSo->id)->where('ten', 'Phòng khám Nội 2')->first();
        if ($bsTM && $phongNoi2 && $dvTimMach) {
            $kg9 = KhungGio::where('phong_id', $phongNoi2->id)->orderBy('thu_tu')->skip(1)->first();
            if ($kg9) {
                $mkBooking($phongNoi2, $kg9, substr($kg9->gio_bat_dau, 0, 5), '09:30', $bsTM, $dvTimMach, null, 'da_duyet');
            }
        }

        // ----------------------------------------------------------------
        // PHÒNG DỊCH VỤ — mỗi phòng có KTV mặc định, không có BS.
        // ----------------------------------------------------------------
        $phongDvs = Phong::where('co_so_id', $coSo->id)
            ->where('kieu_phong', 'phong_dich_vu')
            ->get();

        // Map phòng dịch vụ → dịch vụ riêng (không phải tư vấn/khám)
        $dvXong = DichVu::where('ten', 'Xông hơi')->first();
        $dvYHCT = DichVu::where('ten', 'Trị liệu YHCT')->first();

        // Mỗi phòng dịch vụ: book 4 khung giờ đầu, mỗi khung điền ~60% số slot song song
        foreach ($phongDvs as $pdv) {
            $phut = (int) ($pdv->phut_moi_khach ?: 30);
            $khungs = KhungGio::where('phong_id', $pdv->id)->orderBy('thu_tu')->take(4)->get();
            if ($khungs->isEmpty()) continue;

            $dv = match (true) {
                str_contains(mb_strtolower($pdv->ten), 'xông') => $dvXong,
                str_contains(mb_strtolower($pdv->ten), 'yhct') => $dvYHCT,
                default => null,
            };

            $capacity = max(1, (int) $pdv->so_slot_toi_da);
            $fillSlots = max(1, (int) ceil($capacity * 0.6)); // điền ~60% slot

            $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);

            foreach ($khungs as $kg) {
                $s = $toMin(substr($kg->gio_bat_dau, 0, 5));
                $e = $toMin(substr($kg->gio_ket_thuc, 0, 5));
                $soCa = (int) floor(($e - $s) / $phut);
                if ($soCa <= 0) continue;

                // Mỗi ca trong khung × số slot song song
                for ($i = 0; $i < $soCa; $i++) {
                    $start = $s + $i * $phut;
                    $bd = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
                    $kt = sprintf('%02d:%02d', intdiv($start + $phut, 60), ($start + $phut) % 60);
                    for ($slot = 0; $slot < $fillSlots; $slot++) {
                        $tt = $trangThais[$stt % count($trangThais)];
                        $mkBooking($pdv, $kg, $bd, $kt, null, $dv, $pdv->ktv_mac_dinh_id, $tt);
                    }
                }
            }
        }

        $this->command?->info("Đã seed {$stt} booking mẫu cho ngày {$ngay} tại {$coSo->ten}.");
    }
}
