<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhungGio;
use App\Models\Menu;
use App\Models\PhanQuyen;
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
        $matKhau = Hash::make('59@ntn');

        // ---- Vai trò ----
        $vrNhanVien  = VaiTro::firstOrCreate(['ma' => 'nhan_vien'],     ['ten' => 'Nhân viên']);
        $vrKtv       = VaiTro::firstOrCreate(['ma' => 'ktv'],           ['ten' => 'Kỹ thuật viên']);
        $vrBacSi     = VaiTro::firstOrCreate(['ma' => 'bac_si'],        ['ten' => 'Bác sĩ']);
        $vrBsTuVan   = VaiTro::firstOrCreate(['ma' => 'bac_si_tu_van'], ['ten' => 'Bác sĩ tư vấn']);
        $vrTuVanVien = VaiTro::firstOrCreate(['ma' => 'tu_van_vien'],   ['ten' => 'Tư vấn viên']);
        VaiTro::firstOrCreate(['ma' => 'le_tan'],        ['ten' => 'Lễ tân']);
        $vrVanHanh   = VaiTro::firstOrCreate(['ma' => 'quan_tri_van_hanh'], ['ten' => 'Quản trị vận hành']);
        $vrAdmin     = VaiTro::firstOrCreate(['ma' => 'admin'],         ['ten' => 'Quản trị hệ thống']);

        // ---- Phòng ban: tạo RIÊNG từng cơ sở ở bên dưới (sau khi cơ sở đã tồn tại) ----

        // ---- Phân quyền mặc định theo vai trò ----
        $quyenMacDinh = [
            // Tư vấn viên: xem + sửa booking, sửa lịch tư vấn + bình luận sau dịch vụ
            $vrTuVanVien->id => ['xem_booking', 'sua_booking', 'sua_lich_tu_van', 'binh_luan_booking'],
            // Nhân viên: thêm + xem booking (danh sách chỉ đọc)
            $vrNhanVien->id  => ['them_booking', 'xem_booking'],
            // Quản trị vận hành: xem + thêm + duyệt + cập nhật trạng thái khách + bình luận
            $vrVanHanh->id   => ['xem_booking', 'them_booking', 'duyet_booking', 'duyet_tu_van',
                                 'cap_nhat_trang_thai_khach', 'binh_luan_booking'],
            // KTV, Bác sĩ: xem booking + bình luận sau dịch vụ
            $vrKtv->id       => ['xem_booking', 'binh_luan_booking'],
            $vrBacSi->id     => ['xem_booking', 'binh_luan_booking'],
            $vrBsTuVan->id   => ['xem_booking'],
        ];
        // Lễ tân: xem + thêm booking + cập nhật trạng thái khách + bình luận
        if ($vrLeTan = VaiTro::where('ma', 'le_tan')->first()) {
            $quyenMacDinh[$vrLeTan->id] = ['xem_booking', 'them_booking', 'cap_nhat_trang_thai_khach', 'binh_luan_booking'];
        }
        foreach ($quyenMacDinh as $vaiTroId => $truongs) {
            foreach ($truongs as $truong) {
                PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $truong]);
            }
        }

        // ---- 4 cơ sở ----
        $cs59ntn = CoSo::firstOrCreate(['slug' => '59ntn'], [
            'ten'     => 'Cơ sở 1 - 59 Ngô Thì Nhậm',
            'dia_chi' => '59 Ngô Thì Nhậm, Hai Bà Trưng, Hà Nội',
        ]);
        $cs207nvt = CoSo::firstOrCreate(['slug' => '207nvt'], [
            'ten'     => 'Cơ sở 2 - 207 Nguyễn Văn Thủ',
            'dia_chi' => '207 Nguyễn Văn Thủ, HCM',
        ]);
        $cslo23tdn = CoSo::firstOrCreate(['slug' => 'lo23tdn'], [
            'ten'     => 'Cơ sở 3 - Lô 2+3 KĐT Trần Đăng Ninh',
            'dia_chi' => 'Lô 2+3 KĐT Trần Đăng Ninh',
        ]);
        $cs137nct = CoSo::firstOrCreate(['slug' => '137nct'], [
            'ten'     => 'Cơ sở 4 - 137 NCT HCM',
            'dia_chi' => '137 NCT HCM',
        ]);

        // ---- Phòng ban RIÊNG cho TỪNG cơ sở (8 bộ phận chuẩn / cơ sở) ----
        $phongBanChuan = [
            'sales'            => 'Kinh doanh (Sales)',
            'tu_van'           => 'Phòng tư vấn',
            'admin'            => 'Admin Vận hành',
            'phong_kham_ngoai' => 'Phòng khám Ngoại',
            'phong_chuyen_gia' => 'Phòng chuyên gia',
            'phong_kham_noi_1' => 'Phòng khám Nội 1',
            'phong_kham_noi_2' => 'Phòng khám Nội 2',
            'phong_sieu_am'    => 'Phòng siêu âm',
        ];
        $pb = []; // $pb[co_so_id][ma] = PhongBan
        foreach ([$cs59ntn, $cs207nvt, $cslo23tdn, $cs137nct] as $cs) {
            foreach ($phongBanChuan as $ma => $ten) {
                $pb[$cs->id][$ma] = PhongBan::updateOrCreate(
                    ['co_so_id' => $cs->id, 'ma' => $ma],
                    ['ten' => $ten]
                );
            }
        }
        // Alias cho cơ sở 1 (phần tạo user bên dưới dùng các biến này).
        $pbSales     = $pb[$cs59ntn->id]['sales'];
        $pbTuVan     = $pb[$cs59ntn->id]['tu_van'];
        $pbAdmin     = $pb[$cs59ntn->id]['admin'];
        $pbKhamNgoai = $pb[$cs59ntn->id]['phong_kham_ngoai'];
        $pbChuyenGia = $pb[$cs59ntn->id]['phong_chuyen_gia'];
        $pbKhamNoi1  = $pb[$cs59ntn->id]['phong_kham_noi_1'];
        $pbKhamNoi2  = $pb[$cs59ntn->id]['phong_kham_noi_2'];
        $pbSieuAm    = $pb[$cs59ntn->id]['phong_sieu_am'];
        // Tư vấn HCM thuộc cơ sở 2.
        $pbTuVan207  = $pb[$cs207nvt->id]['tu_van'];

        // ---- Admin Hệ thống (IT) — tài khoản đặc biệt, không thuộc phòng ban / cơ sở nào, full quyền ----
        User::updateOrCreate(['username' => 'admin'], [
            'name'         => 'Admin Hệ thống',
            'email'        => 'admin@sweetsica.com',
            'password'     => Hash::make('59ntn'),
            'co_so_id'     => null,
            'phong_ban_id' => null,
            'vai_tro_id'   => $vrAdmin->id,
            'is_admin'     => true,
        ]);

        User::updateOrCreate(['username' => 'adminvh'], [
            'name'         => 'Admin Vận hành',
            'email'        => 'adminvh@sweetsica.com',
            'password'     => Hash::make('59@ntn'),
            'co_so_id'     => null,
            'phong_ban_id' => null,
            'vai_tro_id'   => $vrVanHanh->id,
            'is_admin'     => false,
        ]);

        // ---- Admin hệ thống bổ sung (IT / MOD) — full quyền, mật khẩu = username ----
        foreach ([
            ['username' => 'baoit', 'name' => 'Bảo IT'],
            ['username' => 'tumod', 'name' => 'Tú MOD'],
        ] as $a) {
            User::updateOrCreate(['username' => $a['username']], [
                'name'         => $a['name'],
                'email'        => $a['username'] . '@sweetsica.com',
                'password'     => Hash::make($a['username']),
                'co_so_id'     => null,
                'phong_ban_id' => null,
                'vai_tro_id'   => $vrAdmin->id,
                'is_admin'     => true,
            ]);
        }

        // =============================================
        // CƠ SỞ 1 — 59 Ngô Thì Nhậm (8h - 18h)
        // =============================================

        // --- Bác sĩ + KTV theo phòng chức năng ---

        // (Bác sĩ riêng lẻ đã bỏ — module bác sĩ dùng DANH MỤC bac_si, seed bên dưới.)
        User::updateOrCreate(['username' => 'ktv1'], [
            'name'         => 'KTV Phòng Ngoại',
            'email'        => 'ktv1@59ntn.local',
            'chuc_danh'    => 'KTV.',
            'password'     => $matKhau,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => $pbKhamNgoai->id,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        User::updateOrCreate(['username' => 'ktv2'], [
            'name'         => 'KTV Phòng Chuyên gia',
            'email'        => 'ktv2@59ntn.local',
            'chuc_danh'    => 'KTV.',
            'password'     => $matKhau,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => $pbChuyenGia->id,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        User::updateOrCreate(['username' => 'ktv3'], [
            'name'         => 'KTV Phòng Nội 1',
            'email'        => 'ktv3@59ntn.local',
            'chuc_danh'    => 'KTV.',
            'password'     => $matKhau,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => $pbKhamNoi1->id,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        User::updateOrCreate(['username' => 'ktv4'], [
            'name'         => 'KTV Phòng Nội 2',
            'email'        => 'ktv4@59ntn.local',
            'chuc_danh'    => 'KTV.',
            'password'     => $matKhau,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => $pbKhamNoi2->id,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        User::updateOrCreate(['username' => 'ktv5'], [
            'name'         => 'KTV Phòng Siêu âm',
            'email'        => 'ktv5@59ntn.local',
            'chuc_danh'    => 'KTV.',
            'password'     => $matKhau,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => $pbSieuAm->id,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        // --- Phòng khám 59 NTN: 5 phòng khám, 12 slot mỗi phòng ---
        $this->seedPhong($cs59ntn, [
            'Phòng khám Ngoại' => 12,
            'Phòng chuyên gia' => 12,
            'Phòng khám Nội 1' => 12,
            'Phòng khám Nội 2' => 12,
            'Phòng siêu âm'   => 12,
        ]);

        // --- Phòng dịch vụ 59 NTN (Xông T4: 10 slot × 30p, YHCT T4: 12 slot × 60p) ---
        $this->seedPhong($cs59ntn, [
            'Phòng Xông T4'          => ['kieu' => 'phong_dich_vu', 'so_slot' => 10, 'phut' => 30, 'ktv_username' => 'ktv4'],
            'Phòng trị liệu YHCT T4' => ['kieu' => 'phong_dich_vu', 'so_slot' => 12, 'phut' => 60, 'ktv_username' => 'ktv5'],
        ]);

        // DANH MỤC bác sĩ (bảng bac_si) — nguồn bác sĩ cho form đặt lịch phòng khám.
        // Bác sĩ gán vào phòng qua pivot phong_bac_si (bac_si_id), cấu hình ở Thiết lập → Phòng.
        $mkBacSi = fn (array $attr) => BacSi::updateOrCreate(
            ['co_so_id' => $cs59ntn->id, 'ten' => $attr['ten']],
            $attr + ['gio_bat_dau' => '08:00', 'gio_ket_thuc' => '18:00', 'active' => true]
        );
        $dmND  = $mkBacSi(['ten' => 'Nguyễn Tiến Dũng',    'chuc_danh' => 'BS.', 'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5]);
        $dmLHD = $mkBacSi(['ten' => 'Lê Tuyên Hồng Dương', 'chuc_danh' => 'BS.', 'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5]);
        $dmTTB = $mkBacSi(['ten' => 'Trương Thị Biên',     'chuc_danh' => 'BS.', 'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5]);
        $dmNTN = $mkBacSi(['ten' => 'Ngô Thị Ngà',         'chuc_danh' => 'BS.', 'nhan_tu_van' => false, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5]);
        $dmBB  = $mkBacSi(['ten' => 'Bác Biên (Tim mạch)', 'chuc_danh' => 'BS.', 'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => false]);
        $dmBH  = $mkBacSi(['ten' => 'Bác Hồng',            'chuc_danh' => 'BS.', 'nhan_tu_van' => false, 'nhan_kham_ls' => true,  'phut_kham_ls' => 25]);

        // Gán bác sĩ (danh mục) vào phòng theo cấu hình phòng khám 59 NTN.
        $ganBacSiPhong = [
            'Phòng khám Ngoại' => [$dmND->id],            // Nguyễn Tiến Dũng
            'Phòng chuyên gia' => [$dmLHD->id],           // Lê Tuyên Hồng Dương
            'Phòng khám Nội 1' => [$dmTTB->id],           // Trương Thị Biên
            'Phòng khám Nội 2' => [$dmNTN->id, $dmBB->id],// Ngô Thị Ngà + Bác Biên (Tim mạch)
            'Phòng siêu âm'    => [$dmBH->id],            // Bác Hồng
        ];
        foreach ($ganBacSiPhong as $tenPhong => $bsIds) {
            $phong = Phong::where('co_so_id', $cs59ntn->id)->where('ten', $tenPhong)->first();
            $phong?->bacSis()->sync($bsIds);
        }

        // Sinh ca khám (tư vấn) cho các bác sĩ danh mục nhận tư vấn.
        foreach ([$dmND, $dmLHD, $dmTTB, $dmBB] as $dm) {
            $dm->taoCaKham();
        }

        // Mỗi cơ sở có 1 tài khoản BÁC SĨ DÙNG CHUNG (đăng nhập), độc lập với module bác sĩ–phòng.
        foreach ([$cs59ntn, $cs207nvt] as $cs) {
            User::updateOrCreate(['username' => 'bsi' . $cs->slug], [
                'name'         => 'Bác sĩ ' . $cs->slug,
                'email'        => 'bsi' . $cs->slug . '@local',
                'chuc_danh'    => 'BS.',
                'password'     => Hash::make('bacsi'),
                'co_so_id'     => $cs->id,
                'phong_ban_id' => null,
                'vai_tro_id'   => $vrBacSi->id,
                'is_admin'     => false,
            ]);
        }

        // --- Tư vấn viên Hà Nội (cơ sở 59 NTN) ---
        $tvHN = [
            ['username' => 'tttg', 'name' => 'Trần Thị Thu Giang',  'chuc_danh' => 'CM'],
            ['username' => 'thk',  'name' => 'Trần Huy Kiên',       'chuc_danh' => 'HC'],
            ['username' => 'nhg',  'name' => 'Nguyễn Hương Giang',  'chuc_danh' => 'SHC'],
            ['username' => 'nmp',  'name' => 'Nguyễn Minh Phương',  'chuc_danh' => 'HC'],
            ['username' => 'nta',  'name' => 'Nguyễn Thị Anh',      'chuc_danh' => 'HC'],
            ['username' => 'ntn',  'name' => 'Nguyễn Thị Nga',      'chuc_danh' => 'SHC'],
            ['username' => 'ctla', 'name' => 'Cao Thị Lan Anh',     'chuc_danh' => 'SHC'],
            ['username' => 'tvh',  'name' => 'Tạ Văn Hợi',          'chuc_danh' => 'CM'],
            ['username' => 'ptt',  'name' => 'Phạm Thanh Trúc',     'chuc_danh' => 'HC'],
            ['username' => 'ntt',  'name' => 'Nguyễn Thị Thúy',     'chuc_danh' => 'SHC'],
            ['username' => 'nhd',  'name' => 'Nguyễn Hoành Đức',    'chuc_danh' => 'TL'],
            ['username' => 'pta',  'name' => 'Phạm Tú Anh',         'chuc_danh' => 'HC'],
            ['username' => 'ntm',  'name' => 'Nguyễn Trà My',       'chuc_danh' => 'SHC'],
            ['username' => 'nma',  'name' => 'Nguyễn Mai Anh',      'chuc_danh' => 'HC'],
        ];
        foreach ($tvHN as $tv) {
            User::updateOrCreate(['username' => $tv['username']], [
                'name'         => $tv['name'],
                'email'        => $tv['username'] . '@59ntn.local',
                'chuc_danh'    => $tv['chuc_danh'],
                'password'     => $matKhau,
                'co_so_id'     => $cs59ntn->id,
                'phong_ban_id' => $pbTuVan->id,
                'vai_tro_id'   => $vrTuVanVien->id,
                'is_admin'     => false,
            ]);
        }

        // =============================================
        // CƠ SỞ 2 — 207 Nguyễn Văn Thủ, HCM (8h - 18h)
        // =============================================
        $this->seedPhong($cs207nvt, [
            'Phòng khám Nội' => 1,
            'Phòng siêu âm'  => ['so_slot' => 24, 'phut' => 25], // 25 phút/ca, 8h–18h = 24 ca
            'Phòng YHCT'     => 1,
        ]);

        // --- Tư vấn viên HCM (cơ sở 207 NVT) ---
        $tvHCM = [
            ['username' => 'tnkn', 'name' => 'Trần Nguyễn Kim Ngân',  'chuc_danh' => 'DM'],
            ['username' => 'ptkq', 'name' => 'Phan Trần Khánh Quỳnh', 'chuc_danh' => 'TL'],
            ['username' => 'ttyn', 'name' => 'Trương Thị Yến Nhi',    'chuc_danh' => 'SHC'],
            ['username' => 'nthn', 'name' => 'Nguyễn Thị Hoài Như',    'chuc_danh' => 'SHC'],
            ['username' => 'htmm', 'name' => 'Huỳnh Thị My My',       'chuc_danh' => 'HC'],
            ['username' => 'ntth', 'name' => 'Nguyễn Thị Thanh',      'chuc_danh' => 'HC'],
            ['username' => 'ntkc', 'name' => 'Nguyễn Thị Kim Chi',    'chuc_danh' => 'HC'],
            ['username' => 'ltpt', 'name' => 'Lê Thị Phương Tự',      'chuc_danh' => 'Trợ lý KD'],
            ['username' => 'ttbt', 'name' => 'Trần Thị Bích Trâm',    'chuc_danh' => 'CM'],
            ['username' => 'ntmt', 'name' => 'Nguyễn Thị Minh Thư',   'chuc_danh' => 'Trợ lý KD/CM HCM'],
            ['username' => 'lpd',  'name' => 'Lê Phát Đạt',           'chuc_danh' => 'SHC'],
            ['username' => 'hbtl', 'name' => 'Huỳnh Bùi Thanh Lan',   'chuc_danh' => 'CM'],
        ];
        foreach ($tvHCM as $tv) {
            User::updateOrCreate(['username' => $tv['username']], [
                'name'         => $tv['name'],
                'email'        => $tv['username'] . '@207nvt.local',
                'chuc_danh'    => $tv['chuc_danh'],
                'password'     => $matKhau,
                'co_so_id'     => $cs207nvt->id,
                'phong_ban_id' => $pbTuVan207->id,
                'vai_tro_id'   => $vrTuVanVien->id,
                'is_admin'     => false,
            ]);
        }

        // =============================================
        // CƠ SỞ 3 — Lô 2+3 KĐT Trần Đăng Ninh (8h - 18h)
        // =============================================
        $this->seedPhong($cslo23tdn, ['Phòng khám' => 1]);

        // =============================================
        // CƠ SỞ 4 — 137 NCT HCM (8h - 18h)
        // =============================================
        $this->seedPhong($cs137nct, ['Phòng khám' => 1]);

        // =============================================
        // MENU + DỊCH VỤ (= Liệu pháp) — RIÊNG cho TỪNG cơ sở
        // Sửa ở cơ sở này KHÔNG ảnh hưởng cơ sở khác.
        // =============================================
        $dsCoSo = CoSo::orderBy('id')->get();
        $primary = $dsCoSo->first();

        // Tự chữa: nếu còn bản dùng chung (co_so_id = NULL) từ phiên bản cũ → gán về
        // cơ sở đầu tiên (giữ nguyên id nên các booking đang trỏ vào không bị hỏng).
        if ($primary) {
            Menu::whereNull('co_so_id')->update(['co_so_id' => $primary->id]);
            DichVu::whereNull('co_so_id')->update(['co_so_id' => $primary->id]);
        }

        // Xóa các tên dịch vụ cũ bị trùng nghĩa (idempotent: chạy lại nhiều lần OK)
        DichVu::whereIn('ten', ['Tư vấn', 'Thăm khám lâm sàng (trừ tim mạch)', 'Massage'])->delete();

        $menus = ['Trà', 'Hoa quả', 'Bánh kẹo'];

        $services = [
            // 8 LIỆU PHÁP THĂM KHÁM (la_dich_vu=false) → hiện ở form đặt lịch phòng khám
            ['ten' => 'Thăm khám lâm sàng',    'thuoc_nhom' => 'kham_ls', 'thoi_gian_phut' => 5,  'la_dich_vu' => false],
            ['ten' => 'Thăm khám tim mạch',    'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 30, 'la_dich_vu' => false],
            ['ten' => 'Thực hiện lâm sàng',    'thuoc_nhom' => 'kham_ls', 'thoi_gian_phut' => 5,  'la_dich_vu' => false],
            ['ten' => 'Siêu âm',               'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 25, 'la_dich_vu' => false],
            ['ten' => 'Chụp XQuang',           'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 15, 'la_dich_vu' => false],
            ['ten' => 'Lấy máu',               'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 10, 'la_dich_vu' => false],
            ['ten' => 'Đọc kết quả Gene',      'thuoc_nhom' => 'tu_van',  'thoi_gian_phut' => 30, 'la_dich_vu' => false],
            ['ten' => 'Tư vấn - đọc kết quả',  'thuoc_nhom' => 'tu_van',  'thoi_gian_phut' => 30, 'la_dich_vu' => false],
            // 2 DỊCH VỤ (la_dich_vu=true) → hiện ở form đặt lịch dịch vụ
            ['ten' => 'Xông hơi',              'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 30, 'la_dich_vu' => true],
            ['ten' => 'Trị liệu YHCT',         'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 60, 'la_dich_vu' => true],
        ];

        foreach ($dsCoSo as $cs) {
            foreach ($menus as $tenMenu) {
                Menu::updateOrCreate(
                    ['co_so_id' => $cs->id, 'ten' => $tenMenu],
                    ['active' => true]
                );
            }

            foreach ($services as $s) {
                DichVu::updateOrCreate(
                    ['co_so_id' => $cs->id, 'ten' => $s['ten']],
                    [
                        'thoi_gian_phut' => $s['thoi_gian_phut'],
                        'thuoc_nhom' => $s['thuoc_nhom'],
                        'la_dich_vu' => $s['la_dich_vu'],
                        'active' => true,
                    ]
                );
            }
        }
    }

    private function seedPhong(CoSo $coSo, array $phongs): void
    {
        foreach ($phongs as $ten => $cfg) {
            // Backward compat: int = so_slot_toi_da
            if (is_int($cfg)) $cfg = ['so_slot' => $cfg];
            $attrs = [
                'loai' => 'kham',
                'kieu_phong' => $cfg['kieu'] ?? 'phong_kham',
                'so_slot_toi_da' => $cfg['so_slot'] ?? 1,
                'phut_moi_khach' => $cfg['phut'] ?? null,
                'trang_thai' => 'hoat_dong',
            ];
            if (! empty($cfg['ktv_username'])) {
                $attrs['ktv_mac_dinh_id'] = User::where('username', $cfg['ktv_username'])->value('id');
            }

            $phong = Phong::updateOrCreate(
                ['co_so_id' => $coSo->id, 'ten' => $ten],
                $attrs
            );

            // Thời lượng mỗi khung: ưu tiên cfg['phut'], mặc định theo loại phòng
            // phong_kham: 5 phút (12 khách/giờ); phong_dich_vu: 30 phút/khách
            $khungLen = $cfg['phut'] ?? ($phong->kieu_phong === 'phong_dich_vu' ? 30 : 5);

            // Số khung: nếu cfg chỉ định so_slot + phut → dùng so_slot;
            // còn lại tính theo giờ làm việc 8h–18h (600 phút)
            $soKhung = (isset($cfg['phut']) && isset($cfg['so_slot']))
                ? (int) $cfg['so_slot']
                : intdiv(600, $khungLen);

            // Xóa khung giờ cũ để seed lại đúng (tránh giữ data sai từ lần chạy trước)
            $phong->khungGios()->delete();
            for ($i = 0; $i < $soKhung; $i++) {
                $startMin = 8 * 60 + $i * $khungLen;
                $endMin   = $startMin + $khungLen;
                KhungGio::create([
                    'phong_id'     => $phong->id,
                    'gio_bat_dau'  => sprintf('%02d:%02d:00', intdiv($startMin, 60), $startMin % 60),
                    'gio_ket_thuc' => sprintf('%02d:%02d:00', intdiv($endMin, 60), $endMin % 60),
                    'thu_tu'       => $i,
                ]);
            }
        }
    }
}
