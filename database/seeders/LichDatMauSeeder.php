<?php

namespace Database\Seeders;

use App\Models\BacSi;
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

        // Dọn khách mẫu cũ không còn booking nào (tránh tích lũy rác qua mỗi lần seed)
        KhachHang::where('co_so_id', $coSo->id)
            ->where('ho_ten', 'like', 'KH Mẫu %')
            ->whereDoesntHave('bookings')
            ->delete();

        $sale = User::where('co_so_id', $coSo->id)->whereHas('vaiTro', fn ($q) => $q->where('ma', 'tu_van_vien'))->first()
            ?? User::where('co_so_id', $coSo->id)->first();

        // Tra cứu dịch vụ CỦA CƠ SỞ (tên đã chuẩn hoá trong LongevitySeeder; DV riêng từng cơ sở)
        $dv = fn (string $ten) => DichVu::where('co_so_id', $coSo->id)->where('ten', $ten)->first();
        $dvTuVan   = $dv('Tư vấn - đọc kết quả');
        $dvKhamLs  = $dv('Thăm khám lâm sàng');
        $dvTimMach = $dv('Thăm khám tim mạch');
        $dvSieuAm  = $dv('Siêu âm');
        $dvGene    = $dv('Đọc kết quả Gene');
        $dvXQuang  = $dv('Chụp XQuang');
        $dvLayMau  = $dv('Lấy máu');
        $dvThucHienLS = $dv('Thực hiện lâm sàng');

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

        $mkBooking = function (Phong $phong, KhungGio $kg, string $bd, string $kt, ?BacSi $bs, ?DichVu $dv, ?int $ktvId, string $trangThai) use ($coSo, $ngay, $sale, $mkKhach, $lyDoTuChoi, &$stt) {
            $kh = $mkKhach();
            // Cờ màu cho timeline đặt lịch: tư vấn → xanh lá, khám LS → xanh dương.
            // (chờ duyệt vẫn hiển thị vàng theo trạng thái, không phụ thuộc cờ này)
            $coTuVan  = $dv?->thuoc_nhom === 'tu_van';
            $coKhamLs = $dv?->thuoc_nhom === 'kham_ls';
            Booking::create([
                'co_so_id'       => $coSo->id,
                'loai_dat_lich'  => $phong->kieu_phong === 'phong_dich_vu' ? 'dich_vu' : 'phong_kham',
                'khach_hang_id'  => $kh->id,
                'phong_id'       => $phong->id,
                'khung_gio_id'   => $kg->id,
                'dich_vu_id'     => $dv?->id,
                'bac_si_id'      => $bs?->id,
                'ktv_user_id'    => $ktvId,
                'sale_id'        => $sale?->id,
                'ngay_dat'       => $ngay,
                'gio_thuc_hien'  => $bd . ':00',
                'gio_ket_thuc'   => $kt . ':00',
                'nguon'          => 'seed',
                'co_tu_van'      => $coTuVan,
                'co_kham_cls'    => $coKhamLs,
                'trang_thai'     => $trangThai,
                'da_duyet'       => $trangThai === 'da_duyet' || $trangThai === 'da_xong',
                'ly_do_tu_choi'  => $trangThai === 'tu_choi' ? $lyDoTuChoi[$stt % count($lyDoTuChoi)] : null,
            ]);
        };

        // ----------------------------------------------------------------
        // PHÒNG KHÁM — xếp theo "KHỐI CA" cho từng bác sĩ trong một cửa sổ giờ.
        // Mỗi khối: N ca cùng dịch vụ; xếp KÍN 1 giường (tuần tự) rồi TRÀN sang giường kế.
        //   - Ca ngắn (khám LS 5') → 1 giường chứa được rất nhiều ca ⇒ trông thưa giường.
        //   - Ca dài + số lượng lớn (tư vấn 30') → 1 giường chỉ 4 ca/2h ⇒ tràn nhiều giường.
        // Số giường tràn = ceil(N / số_ca_mỗi_giường), không vượt so_slot_toi_da.
        // ----------------------------------------------------------------
        $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);

        $trangThais = ['cho_duyet', 'da_duyet', 'cho_duyet', 'da_duyet', 'tu_choi', 'da_duyet'];

        $lichKhoi = [
            // [phòng, username BS, dịch vụ, từ, đến, số ca, phút/ca]
            // Ví dụ Bác sĩ A (ntd) ngày hôm nay:
            ['Phòng khám Ngoại', 'ntd',    $dvKhamLs, '08:00', '10:00', 4,  5],  // 4 ca thăm khám LS 5' → 1 giường (rất thưa)
            ['Phòng khám Ngoại', 'ntd',    $dvTuVan,  '13:00', '15:00', 24, 30], // 24 ca tư vấn 30' → ~6 giường ("la liệt")
            ['Phòng chuyên gia', 'lthd',   $dvTuVan,  '08:30', '11:30', 14, 30], // 14 ca tư vấn → ~4 giường
            ['Phòng khám Nội 1', 'ttb',    $dvKhamLs, '08:00', '11:00', 28, 5],  // khám LS dày → 1 giường
            ['Phòng khám Nội 2', 'ntn_bs', $dvKhamLs, '09:00', '11:30', 20, 5],  // khám LS → 1 giường
            ['Phòng siêu âm',    'bh_sa',  $dvSieuAm, '08:00', '12:00', 9,  25], // siêu âm 25' → 1 giường
        ];

        foreach ($lichKhoi as [$tenPhong, $bsUsername, $dv, $tu, $den, $soCa, $phut]) {
            $phong = Phong::where('co_so_id', $coSo->id)->where('ten', $tenPhong)->first();
            if (! $phong || ! $dv) continue;
            // Bác sĩ = danh mục bac_si (map mã cũ → tên danh mục).
            $tenBacSi = [
                'ntd' => 'Nguyễn Tiến Dũng', 'lthd' => 'Lê Tuyên Hồng Dương',
                'ttb' => 'Trương Thị Biên', 'ntn_bs' => 'Ngô Thị Ngà',
                'bb_tm' => 'Bác Biên (Tim mạch)', 'bh_sa' => 'Bác Hồng',
            ][$bsUsername] ?? null;
            $bs = $tenBacSi ? BacSi::where('co_so_id', $coSo->id)->where('ten', $tenBacSi)->first() : null;
            if (! $bs) continue;

            $khungs = KhungGio::where('phong_id', $phong->id)->orderBy('thu_tu')->get();
            if ($khungs->isEmpty()) continue;
            $khungByStart = [];
            foreach ($khungs as $kg) {
                $khungByStart[$toMin(substr($kg->gio_bat_dau, 0, 5))] = $kg;
            }

            $tuMin    = $toMin($tu);
            $denMin   = $toMin($den);
            $capacity = max(1, (int) $phong->so_slot_toi_da);
            $perBed   = max(1, intdiv($denMin - $tuMin, $phut)); // số ca 1 giường chứa trong cửa sổ

            for ($k = 0; $k < $soCa; $k++) {
                $bed = intdiv($k, $perBed);
                if ($bed >= $capacity) break; // không vượt số giường của phòng

                $start = $tuMin + ($k % $perBed) * $phut;
                if ($start + $phut > $denMin) continue;

                $kg = $khungByStart[$start]
                    ?? $khungs->last(fn ($x) => $toMin(substr($x->gio_bat_dau, 0, 5)) <= $start);
                if (! $kg) continue;

                $bd = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
                $kt = sprintf('%02d:%02d', intdiv($start + $phut, 60), ($start + $phut) % 60);
                $tt = $trangThais[$stt % count($trangThais)];
                $mkBooking($phong, $kg, $bd, $kt, $bs, $dv, null, $tt);
            }
        }

        // BS Tim mạch (Bác Biên) → 1 booking tư vấn tim mạch ở Phòng Nội 2, khung bắt đầu 9h
        $bsTM = BacSi::where('co_so_id', $coSo->id)->where('ten', 'Bác Biên (Tim mạch)')->first();
        $phongNoi2 = Phong::where('co_so_id', $coSo->id)->where('ten', 'Phòng khám Nội 2')->first();
        if ($bsTM && $phongNoi2 && $dvTimMach) {
            $kg9 = KhungGio::where('phong_id', $phongNoi2->id)
                ->where('gio_bat_dau', '>=', '09:00:00')
                ->orderBy('thu_tu')->first();
            if ($kg9) {
                $bd      = substr($kg9->gio_bat_dau, 0, 5);
                $phutTM  = (int) ($bsTM->phut_tu_van ?: 30);
                $startMin = (int) substr($bd, 0, 2) * 60 + (int) substr($bd, 3, 2);
                $endMin   = $startMin + $phutTM;
                $kt = sprintf('%02d:%02d', intdiv($endMin, 60), $endMin % 60);
                $mkBooking($phongNoi2, $kg9, $bd, $kt, $bsTM, $dvTimMach, null, 'da_duyet');
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

        // Mỗi phòng dịch vụ: rải ca khắp ngày (8h–18h). Mỗi mốc giờ random có/không
        // có khách; nếu có thì điền số giường ngẫu nhiên (1..capacity) ⇒ vừa lấp nhiều
        // giường, vừa chừa khoảng trống tự nhiên giữa các ca.
        foreach ($phongDvs as $pdv) {
            $phut = (int) ($pdv->phut_moi_khach ?: 30);
            $khungs = KhungGio::where('phong_id', $pdv->id)->orderBy('thu_tu')->get();
            if ($khungs->isEmpty()) continue;

            $dv = match (true) {
                str_contains(mb_strtolower($pdv->ten), 'xông') => $dvXong,
                str_contains(mb_strtolower($pdv->ten), 'yhct') => $dvYHCT,
                default => null,
            };

            $capacity = max(1, (int) $pdv->so_slot_toi_da);

            $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
            $khungByStart = [];
            foreach ($khungs as $kg) {
                $khungByStart[$toMin(substr($kg->gio_bat_dau, 0, 5))] = $kg;
            }
            $gioMo   = $toMin(substr($khungs->first()->gio_bat_dau, 0, 5));
            $gioDong = $toMin(substr($khungs->last()->gio_ket_thuc, 0, 5));

            for ($t = $gioMo; $t + $phut <= $gioDong; $t += $phut) {
                if (rand(1, 100) <= 40) continue; // ~40% mốc bỏ trống → tạo khoảng trống

                $kg = $khungByStart[$t]
                    ?? $khungs->last(fn ($k) => $toMin(substr($k->gio_bat_dau, 0, 5)) <= $t);
                if (! $kg) continue;

                $bd = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
                $kt = sprintf('%02d:%02d', intdiv($t + $phut, 60), ($t + $phut) % 60);

                $soGiuong = rand(1, $capacity); // số giường dùng tại mốc này khác nhau
                for ($g = 0; $g < $soGiuong; $g++) {
                    $tt = $trangThais[$stt % count($trangThais)];
                    $mkBooking($pdv, $kg, $bd, $kt, null, $dv, $pdv->ktv_mac_dinh_id, $tt);
                }
            }
        }

        $this->command?->info("Đã seed {$stt} booking mẫu cho ngày {$ngay} tại {$coSo->ten}.");
    }
}
