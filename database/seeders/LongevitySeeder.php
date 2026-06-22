<?php

namespace Database\Seeders;

use App\Models\CoSo;
use App\Models\KhungGio;
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
        VaiTro::firstOrCreate(['ma' => 'nhan_vien'],     ['ten' => 'Nhân viên']);
        $vrKtv       = VaiTro::firstOrCreate(['ma' => 'ktv'],           ['ten' => 'Kỹ thuật viên']);
        $vrBacSi     = VaiTro::firstOrCreate(['ma' => 'bac_si'],        ['ten' => 'Bác sĩ']);
        $vrBsTuVan   = VaiTro::firstOrCreate(['ma' => 'bac_si_tu_van'], ['ten' => 'Bác sĩ tư vấn']);
        $vrTuVanVien = VaiTro::firstOrCreate(['ma' => 'tu_van_vien'],   ['ten' => 'Tư vấn viên']);
        VaiTro::firstOrCreate(['ma' => 'le_tan'],        ['ten' => 'Lễ tân']);
        $vrAdmin     = VaiTro::firstOrCreate(['ma' => 'admin'],         ['ten' => 'Quản trị viên']);

        // ---- Phòng ban ----
        $pbSales     = PhongBan::firstOrCreate(['ma' => 'sales'],           ['ten' => 'Kinh doanh (Sales)']);
        $pbTuVan     = PhongBan::firstOrCreate(['ma' => 'tu_van'],          ['ten' => 'Phòng tư vấn']);
        $pbAdmin     = PhongBan::updateOrCreate(['ma' => 'admin'],          ['ten' => 'Admin Vận hành']);
        $pbKhamNgoai = PhongBan::firstOrCreate(['ma' => 'phong_kham_ngoai'],['ten' => 'Phòng khám Ngoại']);
        $pbChuyenGia = PhongBan::firstOrCreate(['ma' => 'phong_chuyen_gia'],['ten' => 'Phòng chuyên gia']);
        $pbKhamNoi1  = PhongBan::firstOrCreate(['ma' => 'phong_kham_noi_1'],['ten' => 'Phòng khám Nội 1']);
        $pbKhamNoi2  = PhongBan::firstOrCreate(['ma' => 'phong_kham_noi_2'],['ten' => 'Phòng khám Nội 2']);
        $pbSieuAm    = PhongBan::firstOrCreate(['ma' => 'phong_sieu_am'],   ['ten' => 'Phòng siêu âm']);

        // ---- Phân quyền mặc định cho Tư vấn viên (gắn theo vai trò) ----
        foreach (['xem_booking', 'sua_booking', 'sua_lich_tu_van'] as $truong) {
            PhanQuyen::firstOrCreate([
                'vai_tro_id' => $vrTuVanVien->id,
                'truong'     => $truong,
            ]);
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

        // =============================================
        // CƠ SỞ 1 — 59 Ngô Thì Nhậm (8h - 18h)
        // =============================================

        // --- Bác sĩ + KTV theo phòng chức năng ---

        // Phòng khám Ngoại — BS tư vấn 1: Nguyễn Tiến Dũng (30 phút/khách)
        $bsTuVan1 = User::updateOrCreate(['username' => 'ntd'], [
            'name'           => 'Nguyễn Tiến Dũng',
            'email'          => 'ntd@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbKhamNgoai->id,
            'vai_tro_id'     => $vrBsTuVan->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 30,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
        ]);
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

        // Phòng chuyên gia — BS tư vấn 2: Lê Tuyên Hồng Dương (30 phút/khách)
        $bsTuVan2 = User::updateOrCreate(['username' => 'lthd'], [
            'name'           => 'Lê Tuyên Hồng Dương',
            'email'          => 'lthd@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbChuyenGia->id,
            'vai_tro_id'     => $vrBsTuVan->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 30,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
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

        // Phòng khám Nội 1 — BS: Trương Thị Biên (12 khách/giờ → 5 phút/khách)
        $bsTTB = User::updateOrCreate(['username' => 'ttb'], [
            'name'           => 'Trương Thị Biên',
            'email'          => 'ttb@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbKhamNoi1->id,
            'vai_tro_id'     => $vrBacSi->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 5,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
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

        // Phòng khám Nội 2 — BS: Ngô Thị Ngà (12 khách/giờ → 5 phút) + BS Bác Biên (Tim mạch, 30 phút/khách)
        $bsNTN = User::updateOrCreate(['username' => 'ntn_bs'], [
            'name'           => 'Ngô Thị Ngà',
            'email'          => 'ntn_bs@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbKhamNoi2->id,
            'vai_tro_id'     => $vrBacSi->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 5,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
        ]);
        $bsBBTM = User::updateOrCreate(['username' => 'bb_tm'], [
            'name'           => 'Bác Biên (Tim mạch)',
            'email'          => 'bb_tm@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbKhamNoi2->id,
            'vai_tro_id'     => $vrBacSi->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 30,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
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

        // Phòng siêu âm — BS: Bác Hồng (25 phút/khách)
        $bsBH = User::updateOrCreate(['username' => 'bh_sa'], [
            'name'           => 'Bác Hồng',
            'email'          => 'bh_sa@59ntn.local',
            'chuc_danh'      => 'BS.',
            'password'       => $matKhau,
            'co_so_id'       => $cs59ntn->id,
            'phong_ban_id'   => $pbSieuAm->id,
            'vai_tro_id'     => $vrBacSi->id,
            'is_admin'       => false,
            'thoi_gian_kham' => 25,
            'gio_bat_dau'    => '08:00',
            'gio_ket_thuc'   => '18:00',
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

        // --- Phòng dịch vụ 59 NTN: 5 phòng, 12 slot mỗi phòng ---
        $this->seedPhong($cs59ntn, [
            'Phòng khám Ngoại' => 12,
            'Phòng chuyên gia' => 12,
            'Phòng khám Nội 1' => 12,
            'Phòng khám Nội 2' => 12,
            'Phòng siêu âm'   => 12,
        ]);

        // Tạo ca khám cho tất cả bác sĩ (cả tư vấn lẫn thăm khám)
        foreach ([$bsTuVan1, $bsTuVan2, $bsTTB, $bsNTN, $bsBBTM, $bsBH] as $bs) {
            if ($bs->caKhams()->count() === 0) {
                $bs->taoCaKham();
            }
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
            'Phòng siêu âm'  => 1,
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
                'phong_ban_id' => $pbTuVan->id,
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
    }

    private function seedPhong(CoSo $coSo, array $phongs): void
    {
        foreach ($phongs as $ten => $soSlot) {
            $phong = Phong::updateOrCreate(
                ['co_so_id' => $coSo->id, 'ten' => $ten],
                ['loai' => 'kham', 'so_slot_toi_da' => $soSlot, 'trang_thai' => 'hoat_dong']
            );

            if ($phong->khungGios()->count() === 0) {
                for ($i = 0; $i < 12; $i++) {
                    $startMin = 8 * 60 + $i * 50;
                    KhungGio::create([
                        'phong_id'     => $phong->id,
                        'gio_bat_dau'  => sprintf('%02d:%02d:00', intdiv($startMin, 60), $startMin % 60),
                        'gio_ket_thuc' => sprintf('%02d:%02d:00', intdiv($startMin + 50, 60), ($startMin + 50) % 60),
                        'thu_tu'       => $i,
                    ]);
                }
            }
        }
    }
}
