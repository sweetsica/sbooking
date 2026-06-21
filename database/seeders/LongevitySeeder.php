<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\LichHen;
use App\Models\Menu;
use App\Models\Phong;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LongevitySeeder extends Seeder
{
    public function run(): void
    {
        // ---- Phòng ban ----
        $pbSales = PhongBan::firstOrCreate(['ma' => 'sales'], ['ten' => 'Kinh doanh (Sales)']);
        $pbKtv   = PhongBan::firstOrCreate(['ma' => 'ktv'],   ['ten' => 'Kỹ thuật viên / Điều dưỡng']);
        $pbAdmin = PhongBan::firstOrCreate(['ma' => 'admin'], ['ten' => 'Quản trị']);

        // ---- Vai trò ----
        $vrNhanVien  = VaiTro::firstOrCreate(['ma' => 'nhan_vien'],     ['ten' => 'Nhân viên']);
        $vrKtv       = VaiTro::firstOrCreate(['ma' => 'ktv'],           ['ten' => 'Kỹ thuật viên']);
        $vrBacSi     = VaiTro::firstOrCreate(['ma' => 'bac_si'],        ['ten' => 'Bác sĩ']);
        $vrBsTuVan   = VaiTro::firstOrCreate(['ma' => 'bac_si_tu_van'], ['ten' => 'Bác sĩ tư vấn']);
        $vrLeTan     = VaiTro::firstOrCreate(['ma' => 'le_tan'],        ['ten' => 'Lễ tân']);
        $vrAdmin     = VaiTro::firstOrCreate(['ma' => 'admin'],         ['ten' => 'Quản trị viên']);

        // ---- Cơ sở ----
        $cs1 = CoSo::firstOrCreate(['slug' => '59ntn'], [
            'ten' => 'Cơ sở 1 - 59 Ngô Thì Nhậm',
            'dia_chi' => '59 Ngô Thì Nhậm, Hai Bà Trưng, Hà Nội',
        ]);
        $cs2 = CoSo::firstOrCreate(['slug' => '12lhp'], [
            'ten' => 'Cơ sở 2 - 12 Lê Hồng Phong',
            'dia_chi' => '12 Lê Hồng Phong, Ba Đình, Hà Nội',
        ]);

        // ---- Admin toàn hệ thống ----
        User::updateOrCreate(['email' => 'admin@sweetsica.com'], [
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('59ntn'),
            'co_so_id' => null,
            'phong_ban_id' => $pbAdmin->id,
            'vai_tro_id' => $vrAdmin->id,
            'is_admin' => true,
        ]);

        // ---- Nhân viên theo từng cơ sở ----
        $seedStaff = function (CoSo $cs, string $suffix, array $staff) use ($pbKtv, $pbSales) {
            $created = [];
            foreach ($staff as [$vaiTro, $chucDanh, $ten, $email, $extra]) {
                $pb = in_array($vaiTro->ma, ['ktv', 'bac_si', 'bac_si_tu_van']) ? $pbKtv : $pbSales;
                $user = User::firstOrCreate(
                    ['email' => $email],
                    array_merge([
                        'name' => $ten,
                        'chuc_danh' => $chucDanh,
                        'password' => Hash::make('password'),
                        'co_so_id' => $cs->id,
                        'phong_ban_id' => $pb->id,
                        'vai_tro_id' => $vaiTro->id,
                        'is_admin' => false,
                    ], $extra)
                );
                $created[$user->email] = $user;
            }
            return $created;
        };

        $staff1 = $seedStaff($cs1, '59ntn', [
            [$vrKtv, 'KTV.', 'Lê Văn C', 'ktv1@longevity.test', []],
            [$vrKtv, 'KTV.', 'Phạm Thị D', 'ktv2@longevity.test', []],
            [$vrBacSi, 'BS.', 'Nguyễn Văn A', 'bs1@longevity.test', []],
            [$vrBacSi, 'BS.', 'Trần Thị B', 'bs2@longevity.test', []],
            [$vrLeTan, null, 'Nguyễn Thị Lễ Tân', 'letan1@longevity.test', []],
            [$vrLeTan, null, 'Trần Văn Lễ Tân', 'letan2@longevity.test', []],
            // Bác sĩ tư vấn (có lịch ca khám)
            [$vrBsTuVan, 'PGS.TS.BS.', 'Nguyễn Minh Tuấn', 'bstv1@longevity.test', [
                'thoi_gian_kham' => 30, 'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '15:00',
            ]],
            [$vrBsTuVan, 'ThS.BS.', 'Lê Thị Hương', 'bstv2@longevity.test', [
                'thoi_gian_kham' => 20, 'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00',
            ]],
            [$vrBsTuVan, 'BS.', 'Phạm Đức Long', 'bstv3@longevity.test', [
                'thoi_gian_kham' => 15, 'gio_bat_dau' => '09:00', 'gio_ket_thuc' => '16:00',
                'is_tu_van' => true, // global — xuất hiện ở mọi cơ sở
            ]],
        ]);

        $staff2 = $seedStaff($cs2, '12lhp', [
            [$vrKtv, 'KTV.', 'Vũ Thị F', 'ktv3@longevity.test', []],
            [$vrKtv, 'KTV.', 'Hoàng Văn G', 'ktv4@longevity.test', []],
            [$vrBacSi, 'BS.', 'Hoàng Văn E', 'bs3@longevity.test', []],
            [$vrBacSi, 'BS.', 'Đặng Thị H', 'bs4@longevity.test', []],
            [$vrLeTan, null, 'Đỗ Thị Lễ Tân', 'letan3@longevity.test', []],
            [$vrLeTan, null, 'Lý Văn Lễ Tân', 'letan4@longevity.test', []],
            [$vrBsTuVan, 'TS.BS.', 'Trần Văn Khoa', 'bstv4@longevity.test', [
                'thoi_gian_kham' => 20, 'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '12:00',
            ]],
            [$vrBsTuVan, 'BS.', 'Đặng Thị Mai', 'bstv5@longevity.test', [
                'thoi_gian_kham' => 30, 'gio_bat_dau' => '13:00', 'gio_ket_thuc' => '17:00',
            ]],
        ]);

        // Tạo ca khám cho bác sĩ tư vấn
        foreach (User::whereNotNull('thoi_gian_kham')->get() as $bs) {
            if ($bs->caKhams()->count() === 0) {
                $bs->taoCaKham();
            }
        }

        // ---- Dịch vụ + Menu + Phòng ----
        $seedCoSo = function (CoSo $cs, array $opts) {
            foreach ($opts['dich_vu'] as $ten) {
                DichVu::firstOrCreate(['co_so_id' => $cs->id, 'ten' => $ten]);
            }
            foreach ($opts['menu'] as $ten) {
                Menu::firstOrCreate(['co_so_id' => $cs->id, 'ten' => $ten]);
            }
            // Phòng khám + khung giờ (50 phút mỗi slot, 12 slot, 8h-18h)
            foreach ($opts['phong'] as [$ten, $slot, $trangThai]) {
                $phong = Phong::updateOrCreate(
                    ['co_so_id' => $cs->id, 'ten' => $ten],
                    ['loai' => 'kham', 'so_slot_toi_da' => $slot, 'trang_thai' => $trangThai]
                );
                if ($phong->khungGios()->count() !== 12 && $trangThai === 'hoat_dong') {
                    $phong->khungGios()->delete();
                    for ($i = 0; $i < 12; $i++) {
                        $startMin = 8 * 60 + $i * 50;
                        KhungGio::create([
                            'phong_id' => $phong->id,
                            'gio_bat_dau' => sprintf('%02d:%02d:00', intdiv($startMin, 60), $startMin % 60),
                            'gio_ket_thuc' => sprintf('%02d:%02d:00', intdiv($startMin + 50, 60), ($startMin + 50) % 60),
                            'thu_tu' => $i,
                        ]);
                    }
                }
            }
        };

        $seedCoSo($cs1, [
            'dich_vu' => ['Vật lý trị liệu toàn thân', 'Châm cứu - Thủy châm', 'Xoa bóp bấm huyệt', 'Đông trùng hạ thảo trị liệu'],
            'menu' => ['Trà thảo mộc', 'Xông hơi thảo dược', 'Đắp parafin', 'Bấm huyệt cổ vai gáy', 'Ngâm chân thuốc bắc'],
            'phong' => [
                ['Phòng khám Ngoại', 1, 'hoat_dong'],
                ['Phòng chuyên gia', 1, 'hoat_dong'],
                ['Phòng khám Nội 1', 1, 'hoat_dong'],
                ['Phòng khám Nội 2', 1, 'hoat_dong'],
            ],
        ]);

        $seedCoSo($cs2, [
            'dich_vu' => ['Vật lý trị liệu toàn thân', 'Châm cứu - Thủy châm', 'Giác hơi'],
            'menu' => ['Trà thảo mộc', 'Xông hơi thảo dược', 'Ngâm chân thuốc bắc'],
            'phong' => [
                ['Phòng khám Ngoại', 1, 'hoat_dong'],
                ['Phòng chuyên gia', 1, 'hoat_dong'],
                ['Phòng khám Nội 1', 1, 'hoat_dong'],
                ['Phòng khám Nội 2', 1, 'hoat_dong'],
            ],
        ]);

        // ---- Booking mẫu cho cơ sở 1 ----
        $kh = KhachHang::firstOrCreate(
            ['so_dien_thoai' => '0901234567'],
            ['co_so_id' => $cs1->id, 'ho_ten' => 'Nguyễn Anh Quân', 'email' => 'anhquan@email.com']
        );
        $phong = $cs1->phongs()->where('trang_thai', 'hoat_dong')->first();
        $letan = $staff1['letan1@longevity.test'];
        $bs = $staff1['bs1@longevity.test'];
        $ktv = $staff1['ktv1@longevity.test'];
        if ($phong && Booking::where('co_so_id', $cs1->id)->count() === 0) {
            $bk = Booking::create([
                'co_so_id' => $cs1->id,
                'khach_hang_id' => $kh->id,
                'phong_id' => $phong->id,
                'khung_gio_id' => $phong->khungGios()->first()?->id,
                'dich_vu_id' => $cs1->dichVus()->first()?->id,
                'bac_si_user_id' => $bs->id,
                'ktv_user_id' => $ktv->id,
                'sale_id' => $letan->id,
                'ngay_dat' => now()->toDateString(),
                'gio_thuc_hien' => '08:00:00',
                'gio_ket_thuc' => '09:00:00',
                'so_lieu_trinh' => '1/10',
                'nguon' => 'Fanpage Facebook',
                'trang_thai' => 'da_duyet',
                'da_duyet' => true,
            ]);
            $bk->menus()->sync($cs1->menus()->take(2)->pluck('id'));
        }

        // ---- Lịch hẹn tư vấn mẫu cho cơ sở 1 ----
        $bstv = $staff1['bstv1@longevity.test'];
        if ($bstv && LichHen::where('co_so_id', $cs1->id)->count() === 0) {
            $kh1 = KhachHang::firstOrCreate(
                ['co_so_id' => $cs1->id, 'so_dien_thoai' => '0912345678'],
                ['ho_ten' => 'Trần Thị Lan', 'email' => 'lan.tran@email.com']
            );
            $kh2 = KhachHang::firstOrCreate(
                ['co_so_id' => $cs1->id, 'so_dien_thoai' => '0987654321'],
                ['ho_ten' => 'Lý Văn Hải']
            );
            $slots = $bstv->caKhams()->orderBy('thu_tu')->get();
            if ($slots->count() >= 2) {
                LichHen::create([
                    'co_so_id' => $cs1->id, 'khach_hang_id' => $kh1->id,
                    'bac_si_user_id' => $bstv->id, 'ca_kham_id' => $slots[0]->id,
                    'sale_id' => $letan->id, 'ngay_hen' => now()->toDateString(),
                    'nguon' => 'Fanpage Facebook', 'trang_thai' => 'da_duyet',
                ]);
                LichHen::create([
                    'co_so_id' => $cs1->id, 'khach_hang_id' => $kh2->id,
                    'bac_si_user_id' => $bstv->id, 'ca_kham_id' => $slots[1]->id,
                    'sale_id' => $letan->id, 'ngay_hen' => now()->toDateString(),
                    'nguon' => 'Hotline', 'ghi_chu' => 'Khách hỏi về liệu trình châm cứu',
                    'trang_thai' => 'cho_duyet',
                ]);
            }
        }
    }
}
