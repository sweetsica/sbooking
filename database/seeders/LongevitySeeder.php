<?php

namespace Database\Seeders;

use App\Models\BacSi;
use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhungGio;
use App\Models\Ktv;
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
        // ---- Vai trò ----
        $vrNhanVien     = VaiTro::firstOrCreate(['ma' => 'nhan_vien'],          ['ten' => 'Nhân viên']);
        $vrKtv          = VaiTro::firstOrCreate(['ma' => 'ktv'],                ['ten' => 'Kỹ thuật viên']);
        $vrBacSi        = VaiTro::firstOrCreate(['ma' => 'bac_si'],             ['ten' => 'Bác sĩ']);
        $vrBsTuVan      = VaiTro::firstOrCreate(['ma' => 'bac_si_tu_van'],      ['ten' => 'Bác sĩ tư vấn']);
        $vrTuVanVien    = VaiTro::updateOrCreate(['ma' => 'tu_van_vien'],       ['ten' => 'Sales']);
        $vrLeTan        = VaiTro::firstOrCreate(['ma' => 'le_tan'],             ['ten' => 'Lễ tân']);
        $vrVanHanh      = VaiTro::firstOrCreate(['ma' => 'quan_tri_van_hanh'],  ['ten' => 'Quản trị vận hành']);
        $vrAdmin        = VaiTro::firstOrCreate(['ma' => 'admin'],              ['ten' => 'Quản trị hệ thống']);
        $vrSalesLead    = VaiTro::firstOrCreate(['ma' => 'sales_lead'],         ['ten' => 'Quản lý Sales']);
        $vrSalesManager = VaiTro::firstOrCreate(['ma' => 'sales_manager'],      ['ten' => 'Quản lý kinh doanh']);
        $vrAdminCoSo    = VaiTro::firstOrCreate(['ma' => 'admin_co_so'],        ['ten' => 'Admin cơ sở']);
        $vrQuyenXem     = VaiTro::firstOrCreate(['ma' => 'quyen_xem'],          ['ten' => 'Quyền xem']);

        // ---- Phân quyền mặc định theo vai trò ----
        $quyenMacDinh = [
            $vrTuVanVien->id    => ['them_booking', 'xem_booking_cua_toi', 'ghi_chu_phan_hoi', 'binh_luan_booking', 'cap_nhat_trang_thai_khach', 'sua_booking', 'sua_lich_tu_van'],
            $vrSalesLead->id    => ['them_booking', 'xem_booking_phong_toi', 'ghi_chu_phan_hoi'],
            $vrSalesManager->id => ['them_booking', 'xem_booking_co_so_toi', 'ghi_chu_phan_hoi'],
            $vrKtv->id          => ['xem_booking_cua_toi', 'ghi_chu_phan_hoi'],
            $vrBacSi->id        => ['xem_booking_cua_toi', 'ghi_chu_phan_hoi'],
            $vrBsTuVan->id      => ['xem_booking_cua_toi', 'ghi_chu_phan_hoi'],
            $vrLeTan->id        => ['xem_booking_co_so_toi', 'ghi_chu_phan_hoi'],
            $vrAdminCoSo->id    => ['duyet_booking', 'xem_booking_co_so_toi', 'duyet_tu_van'],
            $vrQuyenXem->id     => ['xem_booking_tat_ca', 'xem_bao_cao'],
            $vrVanHanh->id      => ['xem_booking_tat_ca', 'them_booking', 'duyet_booking', 'duyet_tu_van'],
        ];
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

        // ---- Phòng ban chuẩn (8 bộ phận / cơ sở) ----
        $phongBanChuan = [
            'sales'            => 'Kinh doanh (Sales)',
            'admin'            => 'Admin Vận hành',
            'phong_kham_ngoai' => 'Phòng khám Ngoại',
            'phong_chuyen_gia' => 'Phòng chuyên gia',
            'phong_kham_noi_1' => 'Phòng khám Nội 1',
            'phong_kham_noi_2' => 'Phòng khám Nội 2',
            'phong_sieu_am'    => 'Phòng siêu âm',
        ];
        $pb = [];
        foreach ([$cs59ntn, $cs207nvt, $cslo23tdn, $cs137nct] as $cs) {
            foreach ($phongBanChuan as $ma => $ten) {
                $pb[$cs->id][$ma] = PhongBan::updateOrCreate(
                    ['co_so_id' => $cs->id, 'ma' => $ma],
                    ['ten' => $ten]
                );
            }
        }

        // ---- Sales team phòng ban ----
        $pbTeamGiang = PhongBan::updateOrCreate(
            ['co_so_id' => $cs59ntn->id, 'ma' => 'team_giang'],
            ['ten' => 'Team Giang (HN)']
        );
        $pbTeamHoi = PhongBan::updateOrCreate(
            ['co_so_id' => $cs59ntn->id, 'ma' => 'team_hoi'],
            ['ten' => 'Team Hợi (HN)']
        );
        $pbTeamHcm = PhongBan::updateOrCreate(
            ['co_so_id' => $cs207nvt->id, 'ma' => 'team_hcm'],
            ['ten' => 'Team HCM']
        );

        // =============================================
        // TÀI KHOẢN ADMIN HỆ THỐNG (giữ nguyên)
        // =============================================
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

        // ---- Admin hệ thống mới (is_admin=true) ----
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

        $matKhauHn  = Hash::make('59@ntn');
        $matKhauHcm = Hash::make('207nvt');

        // =============================================
        // SALES HÀ NỘI — Team Giang (STT 1–7)
        // =============================================
        $salesGiang = [
            ['username' => 'tttg1',  'name' => 'Trần Thị Thu Giang',  'chuc_danh' => 'CM',  'vai_tro_id' => $vrSalesLead->id],
            ['username' => 'thk2',   'name' => 'Trần Huy Kiên',       'chuc_danh' => 'HC',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'nhg3',   'name' => 'Nguyễn Hương Giang',  'chuc_danh' => 'SHC', 'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'nta5',   'name' => 'Nguyễn Thị Anh',      'chuc_danh' => 'HC',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'ntn6',   'name' => 'Nguyễn Thị Nga',      'chuc_danh' => 'SHC', 'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'ctla7',  'name' => 'Cao Thị Lan Anh',     'chuc_danh' => 'SHC', 'vai_tro_id' => $vrTuVanVien->id],
        ];
        foreach ($salesGiang as $s) {
            User::updateOrCreate(['username' => $s['username']], [
                'name' => $s['name'], 'email' => $s['username'] . '@59ntn.local',
                'chuc_danh' => $s['chuc_danh'], 'password' => $matKhauHn,
                'co_so_id' => $cs59ntn->id, 'phong_ban_id' => $pbTeamGiang->id,
                'vai_tro_id' => $s['vai_tro_id'], 'is_admin' => false,
            ]);
        }

        // NMP4 — KTV Da liễu (không thuộc sales team)
        User::updateOrCreate(['username' => 'nmp4'], [
            'name'         => 'Nguyễn Minh Phương',
            'email'        => 'nmp4@59ntn.local',
            'chuc_danh'    => 'KTV',
            'password'     => $matKhauHn,
            'co_so_id'     => $cs59ntn->id,
            'phong_ban_id' => null,
            'vai_tro_id'   => $vrKtv->id,
            'is_admin'     => false,
        ]);

        // =============================================
        // SALES HÀ NỘI — Team Hợi (STT 8–15)
        // =============================================
        $salesHoi = [
            ['username' => 'tvh8',   'name' => 'Tạ Văn Hợi',          'chuc_danh' => 'CM',  'vai_tro_id' => $vrSalesLead->id],
            ['username' => 'ptt9',   'name' => 'Phạm Thanh Trúc',     'chuc_danh' => 'HC',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'ntt10',  'name' => 'Nguyễn Thị Thúy',     'chuc_danh' => 'SHC', 'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'nhd11',  'name' => 'Nguyễn Hoành Đức',    'chuc_danh' => 'TL',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'pta12',  'name' => 'Phạm Tú Anh',         'chuc_danh' => 'HC',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'ntm13',  'name' => 'Nguyễn Trà My',       'chuc_danh' => 'SHC', 'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'nma14',  'name' => 'Nguyễn Mai Anh',      'chuc_danh' => 'HC',  'vai_tro_id' => $vrTuVanVien->id],
            ['username' => 'ltkp15', 'name' => 'Lương Thị Kim Phấn',  'chuc_danh' => 'CM',  'vai_tro_id' => $vrTuVanVien->id],
        ];
        foreach ($salesHoi as $s) {
            User::updateOrCreate(['username' => $s['username']], [
                'name' => $s['name'], 'email' => $s['username'] . '@59ntn.local',
                'chuc_danh' => $s['chuc_danh'], 'password' => $matKhauHn,
                'co_so_id' => $cs59ntn->id, 'phong_ban_id' => $pbTeamHoi->id,
                'vai_tro_id' => $s['vai_tro_id'], 'is_admin' => false,
            ]);
        }

        // =============================================
        // SALES HCM — Team HCM (STT 19–30 + Ashley)
        // =============================================

        // Kim Ngân — DM (Quản lý kinh doanh, quản lý toàn bộ HCM)
        User::updateOrCreate(['username' => 'tnkn19'], [
            'name'         => 'Trần Nguyễn Kim Ngân',
            'email'        => 'tnkn19@207nvt.local',
            'chuc_danh'    => 'DM',
            'password'     => $matKhauHcm,
            'co_so_id'     => $cs207nvt->id,
            'phong_ban_id' => $pbTeamHcm->id,
            'vai_tro_id'   => $vrSalesManager->id,
            'is_admin'     => false,
        ]);

        // Ashley — CM (Team lead HCM)
        User::updateOrCreate(['username' => 'ashley34'], [
            'name'         => 'Ashley',
            'email'        => 'ashley34@207nvt.local',
            'chuc_danh'    => 'CM',
            'password'     => $matKhauHcm,
            'co_so_id'     => $cs207nvt->id,
            'phong_ban_id' => $pbTeamHcm->id,
            'vai_tro_id'   => $vrSalesLead->id,
            'is_admin'     => false,
        ]);

        $salesHcm = [
            ['username' => 'ptkq20', 'name' => 'Phan Trần Khánh Quỳnh', 'chuc_danh' => 'TL'],
            ['username' => 'ttyn21', 'name' => 'Trương Thị Yến Nhi',    'chuc_danh' => 'SHC'],
            ['username' => 'nthn22', 'name' => 'Nguyễn Thị Hoài Như',    'chuc_danh' => 'SHC'],
            ['username' => 'htmm23', 'name' => 'Huỳnh Thị My My',       'chuc_danh' => 'HC'],
            ['username' => 'ntth24', 'name' => 'Nguyễn Thị Thanh',      'chuc_danh' => 'HC'],
            ['username' => 'ntkc25', 'name' => 'Nguyễn Thị Kim Chi',    'chuc_danh' => 'HC'],
            ['username' => 'ltpt26', 'name' => 'Lê Thị Phương Tự',      'chuc_danh' => 'Trợ lý KD'],
            ['username' => 'ttbt27', 'name' => 'Trần Thị Bích Trâm',    'chuc_danh' => 'CM'],
            ['username' => 'ntmt28', 'name' => 'Nguyễn Thị Minh Thư',   'chuc_danh' => 'Trợ lý KD/CM HCM'],
            ['username' => 'lpd29',  'name' => 'Lê Phát Đạt',           'chuc_danh' => 'SHC'],
            ['username' => 'hbtl30', 'name' => 'Huỳnh Bùi Thanh Lan',   'chuc_danh' => 'CM'],
        ];
        foreach ($salesHcm as $s) {
            User::updateOrCreate(['username' => $s['username']], [
                'name' => $s['name'], 'email' => $s['username'] . '@207nvt.local',
                'chuc_danh' => $s['chuc_danh'], 'password' => $matKhauHcm,
                'co_so_id' => $cs207nvt->id, 'phong_ban_id' => $pbTeamHcm->id,
                'vai_tro_id' => $vrTuVanVien->id, 'is_admin' => false,
            ]);
        }

        // =============================================
        // TÀI KHOẢN CHUNG THEO CƠ SỞ (BS / KTV / Lễ tân)
        // =============================================
        $sharedAccounts = [
            ['username' => 'bsi59ntn',  'name' => 'BS chung 59 NTN',   'co_so' => $cs59ntn,  'vai_tro' => $vrBacSi,  'pw' => '59@ntn'],
            ['username' => 'bsi207nvt', 'name' => 'BS chung 207 NVT',  'co_so' => $cs207nvt, 'vai_tro' => $vrBacSi,  'pw' => '207nvt'],
            ['username' => 'ktv59ntn',  'name' => 'KTV chung 59 NTN',  'co_so' => $cs59ntn,  'vai_tro' => $vrKtv,    'pw' => '59@ntn'],
            ['username' => 'ktv207nvt', 'name' => 'KTV chung 207 NVT', 'co_so' => $cs207nvt, 'vai_tro' => $vrKtv,    'pw' => '207nvt'],
            ['username' => 'lt59ntn',   'name' => 'Lễ tân 59 NTN',     'co_so' => $cs59ntn,  'vai_tro' => $vrLeTan,  'pw' => '59@ntn'],
            ['username' => 'lt207nvt',  'name' => 'Lễ tân 207 NVT',    'co_so' => $cs207nvt, 'vai_tro' => $vrLeTan,  'pw' => '207nvt'],
        ];
        foreach ($sharedAccounts as $a) {
            User::updateOrCreate(['username' => $a['username']], [
                'name'         => $a['name'],
                'email'        => $a['username'] . '@local',
                'password'     => Hash::make($a['pw']),
                'co_so_id'     => $a['co_so']->id,
                'phong_ban_id' => null,
                'vai_tro_id'   => $a['vai_tro']->id,
                'is_admin'     => false,
            ]);
        }

        // ---- Admin cơ sở ----
        $adminCoSos = [
            ['username' => 'admin59ntn',  'name' => 'Admin 59 NTN',       'co_so' => $cs59ntn,   'pw' => '59ntn'],
            ['username' => 'admin207nvt', 'name' => 'Admin 207 NVT',      'co_so' => $cs207nvt,  'pw' => '207nvt'],
            ['username' => 'adminl23tdn', 'name' => 'Admin Lô 2+3 TĐN',  'co_so' => $cslo23tdn, 'pw' => 'l23tdn'],
        ];
        foreach ($adminCoSos as $a) {
            User::updateOrCreate(['username' => $a['username']], [
                'name'         => $a['name'],
                'email'        => $a['username'] . '@local',
                'password'     => Hash::make($a['pw']),
                'co_so_id'     => $a['co_so']->id,
                'phong_ban_id' => null,
                'vai_tro_id'   => $vrAdminCoSo->id,
                'is_admin'     => false,
            ]);
        }

        // ---- Quyền xem (viewer) ----
        $viewers = [
            ['username' => 'huyently', 'name' => 'Ms Huyền',     'chuc_danh' => 'Trợ lý kinh doanh'],
            ['username' => 'hangktt',  'name' => 'Ms Hằng KTT',  'chuc_danh' => 'Kế toán trưởng'],
            ['username' => 'lyktdt',   'name' => 'Ms Ly',        'chuc_danh' => 'Kế toán doanh thu'],
            ['username' => 'msan',     'name' => 'Ms An',        'chuc_danh' => 'COO'],
            ['username' => 'mstuyet',  'name' => 'Ms Tuyết',     'chuc_danh' => 'CEO'],
        ];
        foreach ($viewers as $v) {
            User::updateOrCreate(['username' => $v['username']], [
                'name'         => $v['name'],
                'chuc_danh'    => $v['chuc_danh'],
                'email'        => $v['username'] . '@sweetsica.com',
                'password'     => Hash::make('123'),
                'co_so_id'     => null,
                'phong_ban_id' => null,
                'vai_tro_id'   => $vrQuyenXem->id,
                'is_admin'     => false,
            ]);
        }

        // =============================================
        // PHÒNG KHÁM + PHÒNG DỊCH VỤ
        // =============================================

        // --- CƠ SỞ 1: 59 NTN — 5 phòng khám ---
        $this->seedPhong($cs59ntn, [
            'Phòng khám Ngoại' => 12,
            'Phòng chuyên gia' => 12,
            'Phòng khám Nội 1' => 12,
            'Phòng khám Nội 2' => 12,
            'Phòng siêu âm'   => 12,
        ]);

        // --- CƠ SỞ 1: 59 NTN — phòng dịch vụ (Tầng 3 + Tầng 4) ---
        Phong::where('co_so_id', $cs59ntn->id)->whereIn('ten', ['Phòng Xông T4', 'Phòng trị liệu YHCT T4'])->delete();
        $this->seedPhong($cs59ntn, [
            'Phòng Thủ thuật T3'  => ['kieu' => 'phong_dich_vu', 'so_slot' => 1,  'phut' => 30],
            'Phòng Metaboost 1 T4' => ['kieu' => 'phong_dich_vu', 'so_slot' => 3, 'phut' => 120],
            'Phòng Metaboost 2 T4' => ['kieu' => 'phong_dich_vu', 'so_slot' => 3, 'phut' => 120],
            'Phòng Metaboost 3 T4' => ['kieu' => 'phong_dich_vu', 'so_slot' => 3, 'phut' => 120],
            'Phòng YHCT 1 T4'      => ['kieu' => 'phong_dich_vu', 'so_slot' => 2, 'phut' => 60],
            'Phòng YHCT 2 T4'      => ['kieu' => 'phong_dich_vu', 'so_slot' => 2, 'phut' => 60],
            'Phòng YHCT 3 T4'      => ['kieu' => 'phong_dich_vu', 'so_slot' => 2, 'phut' => 60],
        ]);

        // --- CƠ SỞ 2: 207 NVT ---
        Phong::where('co_so_id', $cs207nvt->id)->where('ten', 'Phòng khám Nội')->update(['ten' => 'Phòng Tư vấn']);
        $this->seedPhong($cs207nvt, [
            'Phòng Tư vấn'   => 1,
            'Phòng siêu âm'  => ['so_slot' => 24, 'phut' => 25],
            'Phòng YHCT'     => 1,
        ]);

        // --- CƠ SỞ 3 + 4 ---
        $this->seedPhong($cslo23tdn, ['Phòng khám' => 1]);
        $this->seedPhong($cs137nct, ['Phòng khám' => 1]);

        // =============================================
        // DANH MỤC BÁC SĨ + KTV
        // =============================================

        // --- 59 NTN ---
        $bacSi59 = [
            ['ten' => 'Nguyễn Tiến Dũng',    'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5],
            ['ten' => 'Lê Tuyên Hồng Dương', 'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5],
            ['ten' => 'Trương Thị Biên',     'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5],
            ['ten' => 'Ngô Thị Ngà',         'chuc_danh' => 'BS.',  'nhan_tu_van' => false, 'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5],
            ['ten' => 'Bác Biên Tim mạch',   'chuc_danh' => 'BS.',  'nhan_tu_van' => false, 'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 30],
            ['ten' => 'Bác Hồng',            'chuc_danh' => 'BS.',  'nhan_tu_van' => false, 'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 15],
            ['ten' => 'Bác Bình',            'chuc_danh' => 'BS.',  'nhan_tu_van' => false, 'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 25],
        ];
        foreach ($bacSi59 as $bs) {
            BacSi::updateOrCreate(
                ['co_so_id' => $cs59ntn->id, 'ten' => $bs['ten']],
                $bs + ['gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00', 'active' => true]
            );
        }

        // --- 207 NVT ---
        BacSi::where('co_so_id', $cs207nvt->id)->whereIn('ten', ['Bác sĩ Đồng', 'Bác sĩ Da liễu', 'Bác sĩ YHCT'])->delete();
        $bacSi207 = [
            ['ten' => 'Hoàng Văn Đông',   'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5, 'xuat_hien_moi_co_so' => true],
            ['ten' => 'Lê Huy Thư',       'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => true,  'phut_kham_ls' => 5],
            ['ten' => 'Đặng Công Danh',   'chuc_danh' => 'BS.',  'nhan_tu_van' => true,  'phut_tu_van' => 30, 'nhan_kham_ls' => false, 'phut_kham_ls' => 5],
        ];
        foreach ($bacSi207 as $bs) {
            BacSi::updateOrCreate(
                ['co_so_id' => $cs207nvt->id, 'ten' => $bs['ten']],
                $bs + ['gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00', 'active' => true]
            );
        }

        // --- KTV 59 NTN ---
        $ktv59 = [
            ['ten' => 'Nguyễn Mạnh Tráng',       'nhom' => 'Medical'],
            ['ten' => 'Trần Trà Mi',              'nhom' => 'Medical'],
            ['ten' => 'Phạm Thị Thanh Nhàn',      'nhom' => 'Medical'],
            ['ten' => 'Nguyễn Thị Diễm Quỳnh',    'nhom' => 'Medical'],
            ['ten' => 'Nguyễn Chí Bách',           'nhom' => 'YHCT'],
            ['ten' => 'Nguyễn Thị Lan Vi',         'nhom' => 'YHCT'],
            ['ten' => 'Trần Văn Quang',            'nhom' => 'YHCT'],
            ['ten' => 'Trịnh Thị Thảo',            'nhom' => 'Da liễu'],
            ['ten' => 'Nguyễn Thị Minh Phương',    'nhom' => 'Da liễu'],
            ['ten' => 'Đỗ Thu Hương',              'nhom' => 'Da liễu'],
        ];
        foreach ($ktv59 as $ktv) {
            Ktv::updateOrCreate(
                ['co_so_id' => $cs59ntn->id, 'ten' => $ktv['ten']],
                ['nhom' => $ktv['nhom'], 'gio_bat_dau' => '08:00', 'gio_ket_thuc' => '17:00', 'active' => true]
            );
        }

        // =============================================
        // MENU + DỊCH VỤ (= Liệu pháp) — RIÊNG cho TỪNG cơ sở
        // =============================================
        $dsCoSo = CoSo::orderBy('id')->get();
        $primary = $dsCoSo->first();

        if ($primary) {
            Menu::whereNull('co_so_id')->update(['co_so_id' => $primary->id]);
            DichVu::whereNull('co_so_id')->update(['co_so_id' => $primary->id]);
        }

        DichVu::whereIn('ten', ['Thăm khám lâm sàng', 'Massage', 'Xông hơi', 'Trị liệu YHCT'])->delete();

        $menus = ['Trà', 'Hoa quả', 'Bánh kẹo'];

        // --- 1. Danh mục thăm khám ---
        $thamKham = [
            ['ten' => 'Thăm khám lâm sàng (trừ tim mạch)', 'thuoc_nhom' => 'kham_ls', 'thoi_gian_phut' => 5],
            ['ten' => 'Thăm khám tim mạch',                'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 30],
            ['ten' => 'Thực hiện lâm sàng',                'thuoc_nhom' => 'kham_ls', 'thoi_gian_phut' => 5],
            ['ten' => 'Siêu âm',                           'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 25],
            ['ten' => 'Chụp XQuang',                       'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 15],
            ['ten' => 'Lấy máu',                           'thuoc_nhom' => 'khac',    'thoi_gian_phut' => 10],
            ['ten' => 'Đọc kết quả Gene',                  'thuoc_nhom' => 'tu_van',  'thoi_gian_phut' => 30],
            ['ten' => 'Tư vấn - đọc kết quả',              'thuoc_nhom' => 'tu_van',  'thoi_gian_phut' => 30],
            ['ten' => 'Tư vấn',                            'thuoc_nhom' => 'tu_van',  'thoi_gian_phut' => 30],
        ];

        // --- 2. Danh mục sử dụng dịch vụ ---
        $dichVu = [
            'Gói khám sức khỏe chuyên sâu Signature nam',
            'Gói khám sức khỏe chuyên sâu Signature nữ',
            'Gói khám sức khỏe định kỳ Diamond Nam',
            'Gói khám sức khỏe định kỳ Diamond Nữ',
            'Gói khám sức khỏe Excutive Health Check Nam (Doanh nghiệp)',
            'Gói khám sức khỏe Excutive Health Check Nữ (Doanh nghiệp)',
            'Gói khám sức khỏe tổng quát',
            'Gói khám sức khỏe chuyên sâu về Cơ xương khớp',
            'Gói khám sức khỏe chuyên sâu về Tim mạch & đột quỵ',
            'Gói khám sức khỏe chuyên sâu về Gan',
            'Gói khám sức khỏe chuyên sâu về Tiểu đường',
            'Gói khám sức khỏe chuyên sâu về Tuyến giáp',
            'Gói khám sức khỏe chuyên sâu về Rối loạn chuyển hóa',
            'Gói khám VVIP Nữ',
            'Gói khám VVIP Nam',
            'Gói khám xét nghiệm và siêu âm tổng quát',
            'Gene2 me Plus',
            'Gene2 me',
            'TruAge',
            'Gene2 + Gene2 Plus + TruAge',
            'Return TruAge',
            'EAQ (1 vùng)',
            'BJR (1 khớp)',
            'HA 1%/khớp',
            'HA 2%/khớp',
            'PRP/khớp',
            'Y học Phương Đông',
            'DeepOxy & DetoxCell (xông)',
            'DeepOxy & DetoxCell (tổng hợp)',
            'STC Japan',
            'NK',
            'Recells',
        ];

        $services = [];
        foreach ($thamKham as $tk) {
            $services[] = array_merge($tk, ['la_dich_vu' => false]);
        }
        foreach ($dichVu as $ten) {
            $services[] = ['ten' => $ten, 'thuoc_nhom' => 'khac', 'thoi_gian_phut' => 30, 'la_dich_vu' => true];
        }

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
            if (is_int($cfg)) $cfg = ['so_slot' => $cfg];
            $attrs = [
                'loai' => 'kham',
                'kieu_phong' => $cfg['kieu'] ?? 'phong_kham',
                'so_slot_toi_da' => $cfg['so_slot'] ?? 1,
                'phut_moi_khach' => $cfg['phut'] ?? null,
                'trang_thai' => 'hoat_dong',
            ];

            $phong = Phong::updateOrCreate(
                ['co_so_id' => $coSo->id, 'ten' => $ten],
                $attrs
            );

            $khungLen = $cfg['phut'] ?? ($phong->kieu_phong === 'phong_dich_vu' ? 30 : 5);
            $soKhung = intdiv(600, $khungLen);

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
