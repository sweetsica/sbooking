<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhachHang;
use App\Models\KhungGio;
use App\Models\Menu;
use App\Models\Phong;
use App\Models\PhongBan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class YhctSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Phòng ban ----
        $pbSales = PhongBan::firstOrCreate(['ma' => 'sales'], ['ten' => 'Kinh doanh (Sales)']);
        $pbKtv   = PhongBan::firstOrCreate(['ma' => 'ktv'],   ['ten' => 'Kỹ thuật viên / Điều dưỡng']);
        $pbAdmin = PhongBan::firstOrCreate(['ma' => 'admin'], ['ten' => 'Quản trị']);

        // ---- Cơ sở ----
        $cs1 = CoSo::firstOrCreate(['slug' => '59ntn'], [
            'ten' => 'Cơ sở 1 - 59 Ngô Thì Nhậm',
            'dia_chi' => '59 Ngô Thì Nhậm, Hai Bà Trưng, Hà Nội',
        ]);
        $cs2 = CoSo::firstOrCreate(['slug' => '12lhp'], [
            'ten' => 'Cơ sở 2 - 12 Lê Hồng Phong',
            'dia_chi' => '12 Lê Hồng Phong, Ba Đình, Hà Nội',
        ]);

        // ---- Admin toàn hệ thống (xuất hiện ở tất cả cơ sở) ----
        User::updateOrCreate(['email' => 'admin@yhct.test'], [
            'name' => 'Admin Hoa',
            'username' => 'adminhoa',
            'password' => Hash::make('admin@123'),
            'co_so_id' => null,
            'phong_ban_id' => $pbAdmin->id,
            'is_admin' => true,
        ]);

        // ---- Dữ liệu theo từng cơ sở ----
        $seedCoSo = function (CoSo $cs, array $opts) use ($pbSales, $pbKtv) {
            // Nhân viên Sales (cho mục "Sale phụ trách")
            foreach ($opts['sales'] as $i => $ten) {
                User::firstOrCreate(
                    ['email' => $cs->slug . '.sale' . ($i + 1) . '@yhct.test'],
                    ['name' => $ten, 'password' => Hash::make('password'),
                     'co_so_id' => $cs->id, 'phong_ban_id' => $pbSales->id, 'is_admin' => false]
                );
            }

            // Bác sĩ / Điều dưỡng
            foreach ($opts['bac_si'] as [$chuc, $ten]) {
                BacSi::firstOrCreate(['co_so_id' => $cs->id, 'ten' => $ten], ['chuc_danh' => $chuc]);
            }

            // Dịch vụ / Liệu pháp
            foreach ($opts['dich_vu'] as $ten) {
                DichVu::firstOrCreate(['co_so_id' => $cs->id, 'ten' => $ten]);
            }

            // Menu
            foreach ($opts['menu'] as $ten) {
                Menu::firstOrCreate(['co_so_id' => $cs->id, 'ten' => $ten]);
            }

            // Phòng + khung giờ (mỗi khung 1 tiếng, 08:00 -> 21:00)
            foreach ($opts['phong'] as [$ten, $loai, $slot, $trangThai]) {
                $phong = Phong::firstOrCreate(
                    ['co_so_id' => $cs->id, 'ten' => $ten],
                    ['loai' => $loai, 'so_slot_toi_da' => $slot, 'trang_thai' => $trangThai]
                );
                if ($phong->khungGios()->count() === 0 && $trangThai === 'hoat_dong') {
                    for ($h = 8; $h < 21; $h++) {
                        KhungGio::create([
                            'phong_id' => $phong->id,
                            'gio_bat_dau' => sprintf('%02d:00:00', $h),
                            'gio_ket_thuc' => sprintf('%02d:00:00', $h + 1),
                            'thu_tu' => $h - 8,
                        ]);
                    }
                }
            }
        };

        $seedCoSo($cs1, [
            'sales' => ['Nguyễn Thị Sale', 'Trần Văn Kinh Doanh'],
            'bac_si' => [['BS.', 'Nguyễn Văn A'], ['BS.', 'Trần Thị B'], ['KTV.', 'Lê Văn C'], ['Điều dưỡng', 'Phạm Thị D']],
            'dich_vu' => ['Vật lý trị liệu toàn thân', 'Châm cứu - Thủy châm', 'Xoa bóp bấm huyệt', 'Đông trùng hạ thảo trị liệu'],
            'menu' => ['Trà thảo mộc', 'Xông hơi thảo dược', 'Đắp parafin', 'Bấm huyệt cổ vai gáy', 'Ngâm chân thuốc bắc'],
            'phong' => [
                ['Phòng Trị liệu VIP 01', 'vip', 2, 'hoat_dong'],
                ['Phòng Trị liệu VIP 02', 'vip', 2, 'bao_tri'],
                ['Phòng Trị liệu Tổng hợp A', 'cong_dong', 12, 'hoat_dong'],
            ],
        ]);

        $seedCoSo($cs2, [
            'sales' => ['Đỗ Thị Sale 2'],
            'bac_si' => [['BS.', 'Hoàng Văn E'], ['KTV.', 'Vũ Thị F']],
            'dich_vu' => ['Vật lý trị liệu toàn thân', 'Châm cứu - Thủy châm', 'Giác hơi'],
            'menu' => ['Trà thảo mộc', 'Xông hơi thảo dược', 'Ngâm chân thuốc bắc'],
            'phong' => [
                ['Phòng Trị liệu VIP 01', 'vip', 2, 'hoat_dong'],
                ['Phòng Trị liệu Tổng hợp B', 'cong_dong', 8, 'hoat_dong'],
            ],
        ]);

        // ---- Vài khách hàng + booking mẫu cho cơ sở 1 ----
        $kh = KhachHang::firstOrCreate(
            ['so_dien_thoai' => '0901234567'],
            ['co_so_id' => $cs1->id, 'ho_ten' => 'Nguyễn Anh Quân', 'email' => 'anhquan@email.com']
        );
        $phong = $cs1->phongs()->where('trang_thai', 'hoat_dong')->first();
        $sale  = $cs1->nguoiDungs()->where('phong_ban_id', $pbSales->id)->first();
        if ($phong && Booking::where('co_so_id', $cs1->id)->count() === 0) {
            $bk = Booking::create([
                'co_so_id' => $cs1->id,
                'khach_hang_id' => $kh->id,
                'phong_id' => $phong->id,
                'khung_gio_id' => $phong->khungGios()->first()?->id,
                'dich_vu_id' => $cs1->dichVus()->first()?->id,
                'bac_si_id' => $cs1->bacSis()->first()?->id,
                'sale_id' => $sale?->id,
                'ngay_dat' => now()->toDateString(),
                'gio_thuc_hien' => '08:00:00',
                'gio_ket_thuc' => '09:00:00',
                'so_lieu_trinh' => '1/10',
                'nguon' => 'Fanpage Facebook',
                'trang_thai' => 'da_duyet',
            ]);
            $bk->menus()->sync($cs1->menus()->take(2)->pluck('id'));
        }
    }
}
