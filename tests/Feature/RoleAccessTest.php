<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\KhachHang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase, BookingTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    protected function makeBooking(): Booking
    {
        $kh = KhachHang::create([
            'co_so_id' => $this->coSo->id,
            'ho_ten' => 'KH', 'so_dien_thoai' => '0911111111',
        ]);
        return Booking::create([
            'co_so_id' => $this->coSo->id, 'khach_hang_id' => $kh->id,
            'phong_id' => $this->phongSlot2->id, 'khung_gio_id' => $this->khung9->id,
            'dich_vu_id' => $this->dichVu->id, 'sale_id' => $this->sale->id,
            'ngay_dat' => now()->addDay()->toDateString(),
            'trang_thai' => 'cho_duyet', 'da_duyet' => false,
        ]);
    }

    // ===== ROLE 1: ADMIN =====
    public function test_D1_admin_thiet_lap_ok(): void
    {
        $this->actingAs($this->admin)
            ->get("/{$this->coSo->slug}/thiet-lap")
            ->assertOk();
    }

    public function test_D1_admin_bao_cao_ok(): void
    {
        $this->actingAs($this->admin)
            ->get("/{$this->coSo->slug}/thiet-lap/bao-cao")
            ->assertOk();
    }

    public function test_D1_admin_bao_cao_xuat_excel(): void
    {
        $this->actingAs($this->admin)
            ->get("/{$this->coSo->slug}/thiet-lap/bao-cao/xuat")
            ->assertOk()
            ->assertDownload();
    }

    public function test_D2_van_hanh_bao_cao_403(): void
    {
        $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/thiet-lap/bao-cao")
            ->assertForbidden();
    }

    public function test_D1_admin_tao_booking_ok(): void
    {
        $this->actingAs($this->admin)
            ->get("/{$this->coSo->slug}/tao-moi")
            ->assertOk();
    }

    public function test_D1_admin_duyet_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->admin)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertRedirect();
        $this->assertSame('da_duyet', $bk->fresh()->trang_thai);
    }

    public function test_D1_admin_xoa_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->admin)
            ->delete("/{$this->coSo->slug}/xoa-dat-phong/{$bk->id}")
            ->assertRedirect();
    }

    // ===== ROLE 2: QUẢN TRỊ VẬN HÀNH =====
    public function test_D2_van_hanh_thiet_lap_403(): void
    {
        $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/thiet-lap")
            ->assertForbidden();
    }

    public function test_D2_van_hanh_tao_booking_ok(): void
    {
        $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/tao-moi")
            ->assertOk();
    }

    public function test_D2_van_hanh_duyet_booking_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertRedirect();
        $this->assertSame('da_duyet', $bk->fresh()->trang_thai);
    }

    public function test_D2_van_hanh_duyet_tu_van_ok(): void
    {
        $lh = \App\Models\LichHen::create([
            'co_so_id' => $this->coSo->id,
            'khach_hang_id' => KhachHang::create([
                'co_so_id' => $this->coSo->id, 'ho_ten' => 'X', 'so_dien_thoai' => '0911',
            ])->id,
            'bac_si_id' => $this->bacSi->id,
            'sale_id' => $this->sale->id,
            'ngay_hen' => now()->addDay()->toDateString(),
            'trang_thai' => 'cho_duyet',
        ]);

        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-tu-van/{$lh->id}")
            ->assertRedirect();
        $this->assertSame('da_duyet', $lh->fresh()->trang_thai);
    }

    public function test_D2_van_hanh_xoa_booking_403(): void
    {
        // vanHanh không có xoa_booking
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->delete("/{$this->coSo->slug}/xoa-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    // ===== ROLE 3: TƯ VẤN VIÊN =====
    public function test_D3_tu_van_vien_thiet_lap_403(): void
    {
        $this->actingAs($this->tuVanVien)
            ->get("/{$this->coSo->slug}/thiet-lap")
            ->assertForbidden();
    }

    public function test_D3_tu_van_vien_tao_booking_ok(): void
    {
        // có them_booking
        $this->actingAs($this->tuVanVien)
            ->get("/{$this->coSo->slug}/tao-moi")
            ->assertOk();
    }

    public function test_D3_tu_van_vien_duyet_booking_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->tuVanVien)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_D3_tu_van_vien_duyet_tu_van_403(): void
    {
        $lh = \App\Models\LichHen::create([
            'co_so_id' => $this->coSo->id,
            'khach_hang_id' => KhachHang::create([
                'co_so_id' => $this->coSo->id, 'ho_ten' => 'X', 'so_dien_thoai' => '0911',
            ])->id,
            'bac_si_id' => $this->bacSi->id,
            'sale_id' => $this->sale->id,
            'ngay_hen' => now()->addDay()->toDateString(),
            'trang_thai' => 'cho_duyet',
        ]);

        $this->actingAs($this->tuVanVien)
            ->patch("/{$this->coSo->slug}/duyet-tu-van/{$lh->id}")
            ->assertForbidden();
    }

    // ===== ROLE 4: BÁC SĨ =====
    public function test_D4_bac_si_tao_booking_403(): void
    {
        // bac_si không có them_booking (chỉ là noPerm wrapper)
        $this->actingAs($this->bacSiUser)
            ->get("/{$this->coSo->slug}/tao-moi")
            ->assertForbidden();
    }

    public function test_D4_bac_si_thiet_lap_403(): void
    {
        $this->actingAs($this->bacSiUser)
            ->get("/{$this->coSo->slug}/thiet-lap")
            ->assertForbidden();
    }

    public function test_D4_bac_si_duyet_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->bacSiUser)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_D4_bac_si_xem_timeline_ok(): void
    {
        // /lich-hen chỉ cần auth
        $this->actingAs($this->bacSiUser)
            ->get("/{$this->coSo->slug}/lich-hen")
            ->assertOk();
    }

    // ===== ROLE 5: KTV =====
    public function test_D5_ktv_tao_booking_403(): void
    {
        $this->actingAs($this->ktv)
            ->get("/{$this->coSo->slug}/tao-moi")
            ->assertForbidden();
    }

    public function test_D5_ktv_thiet_lap_403(): void
    {
        $this->actingAs($this->ktv)
            ->get("/{$this->coSo->slug}/thiet-lap")
            ->assertForbidden();
    }

    public function test_D5_ktv_duyet_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->ktv)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_D5_ktv_xem_timeline_ok(): void
    {
        $this->actingAs($this->ktv)
            ->get("/{$this->coSo->slug}/lich-hen")
            ->assertOk();
    }

    // ===== Guest / Auth =====
    public function test_D6_guest_lich_hen_redirect_login(): void
    {
        $this->get("/{$this->coSo->slug}/lich-hen")
            ->assertRedirect('/login');
    }

    public function test_D6_guest_tao_moi_redirect_login(): void
    {
        // Sau fix #5: /tao-moi nay ở trong nhóm auth middleware → guest 302 → /login
        $this->get("/{$this->coSo->slug}/tao-moi")
            ->assertRedirect('/login');
    }

    public function test_D6_root_redirect_to_coso(): void
    {
        $this->get('/')->assertRedirect("/{$this->coSo->slug}/lich-hen");
    }

    public function test_D6_co_so_khong_ton_tai_404(): void
    {
        $this->actingAs($this->admin)
            ->get('/khong-ton-tai/lich-hen')
            ->assertNotFound();
    }
}
