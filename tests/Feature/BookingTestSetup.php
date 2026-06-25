<?php

namespace Tests\Feature;

use App\Models\CoSo;
use App\Models\DichVu;
use App\Models\KhungGio;
use App\Models\PhanQuyen;
use App\Models\Phong;
use App\Models\PhongBan;
use App\Models\User;
use App\Models\VaiTro;
use Illuminate\Support\Facades\DB;

trait BookingTestSetup
{
    protected CoSo $coSo;
    protected CoSo $coSo2;
    protected Phong $phongSlot2;
    protected Phong $phongSlot1;
    protected Phong $phongBig;
    protected Phong $phongDichVu;
    protected KhungGio $khung9Big;
    protected KhungGio $khung9DV;
    protected KhungGio $khung9;
    protected KhungGio $khung10;
    protected KhungGio $khung9_p1;
    protected DichVu $dichVu;
    protected DichVu $dichVuTuVan;
    protected DichVu $dichVuKhamLs;
    protected User $bsCaHai;
    protected User $bsChiTuVan;
    protected User $bsChiKhamLs;

    protected User $admin;
    protected User $vanHanh;
    protected User $tuVanVien;
    protected User $bacSi;
    protected User $bacSiGlobal;
    protected User $ktv;
    protected User $sale;
    protected User $noPerm;

    protected VaiTro $vrAdmin;
    protected VaiTro $vrVanHanh;
    protected VaiTro $vrTuVanVien;
    protected VaiTro $vrBacSi;
    protected VaiTro $vrBacSiTuVan;
    protected VaiTro $vrKtv;
    protected VaiTro $vrNhanVien;

    protected function seedBase(): void
    {
        $this->coSo  = CoSo::create(['ten' => 'Cơ sở 1', 'slug' => 'cs1', 'active' => true]);
        $this->coSo2 = CoSo::create(['ten' => 'Cơ sở 2', 'slug' => 'cs2', 'active' => true]);

        $this->phongSlot2 = Phong::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Phòng A',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 2, 'trang_thai' => 'hoat_dong',
        ]);
        $this->phongSlot1 = Phong::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Phòng B',
            'loai' => 'vip', 'so_slot_toi_da' => 1, 'trang_thai' => 'hoat_dong',
        ]);
        $this->phongBig = Phong::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Phòng Big',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 20, 'trang_thai' => 'hoat_dong',
        ]);
        $this->khung9Big = KhungGio::create([
            'phong_id' => $this->phongBig->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);

        // Phòng dịch vụ: slot=1, 30p/khách, KTV mặc định = $this->ktv
        $this->phongDichVu = Phong::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Phòng Xông T4',
            'kieu_phong' => 'phong_dich_vu',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 1, 'trang_thai' => 'hoat_dong',
            'phut_moi_khach' => 30, 'ktv_mac_dinh_id' => null, // set sau khi tạo ktv
        ]);
        $this->khung9DV = KhungGio::create([
            'phong_id' => $this->phongDichVu->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);

        $this->khung9 = KhungGio::create([
            'phong_id' => $this->phongSlot2->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);
        $this->khung10 = KhungGio::create([
            'phong_id' => $this->phongSlot2->id,
            'gio_bat_dau' => '10:00:00', 'gio_ket_thuc' => '11:00:00', 'thu_tu' => 1,
        ]);
        $this->khung9_p1 = KhungGio::create([
            'phong_id' => $this->phongSlot1->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);

        $this->dichVu = DichVu::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Massage', 'active' => true,
            'thoi_gian_phut' => 30, 'thuoc_nhom' => 'khac', 'la_dich_vu' => false,
        ]);
        $this->dichVuTuVan = DichVu::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Tư vấn', 'active' => true,
            'thoi_gian_phut' => 30, 'thuoc_nhom' => 'tu_van', 'la_dich_vu' => false,
        ]);
        $this->dichVuKhamLs = DichVu::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Khám LS', 'active' => true,
            'thoi_gian_phut' => 5, 'thuoc_nhom' => 'kham_ls', 'la_dich_vu' => false,
        ]);

        // Vai trò
        $this->vrAdmin       = VaiTro::firstOrCreate(['ma' => 'admin'], ['ten' => 'Quản trị hệ thống']);
        $this->vrVanHanh     = VaiTro::firstOrCreate(['ma' => 'quan_tri_van_hanh'], ['ten' => 'Quản trị vận hành']);
        $this->vrTuVanVien   = VaiTro::firstOrCreate(['ma' => 'tu_van_vien'], ['ten' => 'Tư vấn viên']);
        $this->vrBacSi       = VaiTro::firstOrCreate(['ma' => 'bac_si'], ['ten' => 'Bác sĩ']);
        $this->vrBacSiTuVan  = VaiTro::firstOrCreate(['ma' => 'bac_si_tu_van'], ['ten' => 'Bác sĩ tư vấn']);
        $this->vrKtv         = VaiTro::firstOrCreate(['ma' => 'ktv'], ['ten' => 'Kỹ thuật viên']);
        $this->vrNhanVien    = VaiTro::firstOrCreate(['ma' => 'nhan_vien'], ['ten' => 'Nhân viên']);

        // Permissions cho từng vai trò
        $this->grantPerms($this->vrVanHanh->id, [
            'xem_booking', 'them_booking', 'sua_booking', 'duyet_booking',
            'ho_ten', 'so_dien_thoai', 'phong_id', 'khung_gio_id', 'ngay_dat',
            'gio_thuc_hien', 'gio_ket_thuc', 'bac_si_user_id', 'ktv_user_id',
            'dich_vu_id', 'sale_id', 'ghi_chu', 'nguon',
            'sua_lich_tu_van', 'duyet_tu_van',
            'xuat_lich_dat_phong', 'xuat_lich_tu_van',
        ]);
        $this->grantPerms($this->vrTuVanVien->id, ['them_booking', 'xem_booking', 'sua_lich_tu_van']);
        $this->grantPerms($this->vrNhanVien->id, ['them_booking', 'xem_booking']);

        // Users
        $this->admin = User::create([
            'name' => 'Admin', 'username' => 'admin', 'email' => 'admin@a.vn',
            'password' => bcrypt('password'), 'is_admin' => true,
            'co_so_id' => $this->coSo->id, 'vai_tro_id' => $this->vrAdmin->id,
        ]);
        $this->vanHanh = $this->mkUser('Vận hành', 'vanhanh', $this->vrVanHanh->id);
        $this->tuVanVien = $this->mkUser('Tư vấn viên', 'tvvien', $this->vrTuVanVien->id);
        $this->bacSi = $this->mkUser('BS Z', 'bsz', $this->vrBacSi->id);
        $this->bsCaHai = $this->mkUser('BS Cả Hai', 'bscahai', $this->vrBacSi->id, ['nhan_tu_van' => true, 'phut_tu_van' => 30, 'nhan_kham_ls' => true, 'phut_kham_ls' => 5]);
        $this->bsChiTuVan = $this->mkUser('BS Chỉ Tư Vấn', 'bstv', $this->vrBacSi->id, ['nhan_tu_van' => true, 'phut_tu_van' => 30, 'nhan_kham_ls' => false]);
        $this->bsChiKhamLs = $this->mkUser('BS Chỉ Khám LS', 'bskls', $this->vrBacSi->id, ['nhan_tu_van' => false, 'nhan_kham_ls' => true, 'phut_kham_ls' => 5]);
        $this->bacSiGlobal = $this->mkUser('BS Global', 'bsglobal', $this->vrBacSiTuVan->id, ['is_tu_van' => true, 'co_so_id' => $this->coSo2->id]);
        $this->ktv = $this->mkUser('KTV X', 'ktvx', $this->vrKtv->id);
        $this->sale = $this->mkUser('Sale S', 'sales', $this->vrNhanVien->id);
        // noPerm có vai trò "Bác sĩ" nhưng KHÔNG được gán PhanQuyen sua_booking
        $this->noPerm = $this->mkUser('No Perm', 'noperm', $this->vrBacSi->id);
    }

    protected function mkUser(string $name, string $username, ?int $vaiTroId, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $name,
            'username' => $username,
            'email' => $username . '@a.vn',
            'password' => bcrypt('password'),
            'co_so_id' => $this->coSo->id,
            'vai_tro_id' => $vaiTroId,
            'is_admin' => false,
        ], $extra));
    }

    protected function grantPerms(int $vaiTroId, array $fields): void
    {
        foreach ($fields as $f) {
            PhanQuyen::firstOrCreate(['vai_tro_id' => $vaiTroId, 'truong' => $f]);
        }
    }

    /** Payload đầy đủ để store booking thành công. */
    protected function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'ho_ten'        => 'Nguyễn A',
            'so_dien_thoai' => '0900000001',
            'email'         => null,
            'ngay_dat'      => now()->addDay()->toDateString(),
            'phong_id'      => $this->phongSlot2->id,
            'khung_gio_id'  => $this->khung9->id,
            'gio_thuc_hien' => '09:00',
            'gio_ket_thuc'  => '10:00',
            'dich_vu_id'    => $this->dichVu->id,
            'sale_id'       => $this->sale->id,
            'bac_si_user_id' => null,
            'ktv_user_id'   => null,
            'so_lieu_trinh' => null,
            'nguon'         => 'Hotline',
            'ghi_chu'       => null,
        ], $overrides);
    }
}
