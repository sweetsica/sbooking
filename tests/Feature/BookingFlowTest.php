<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\KhachHang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase, BookingTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    // ===== A1. Happy path =====
    public function test_A1_1_khach_dat_thanh_cong(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload())
            ->assertRedirect("/{$this->coSo->slug}/danh-sach")
            ->assertSessionHas('ok');

        $bk = Booking::first();
        $this->assertNotNull($bk);
        $this->assertSame('cho_duyet', $bk->trang_thai);
        $this->assertFalse((bool) $bk->da_duyet);
    }

    public function test_A1_6_khach_cu_reuse_va_doi_ten(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['ho_ten' => 'Nguyễn A', 'so_dien_thoai' => '0900000001']));

        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload([
                'ho_ten' => 'Nguyễn A Đổi', 'so_dien_thoai' => '0900000001',
                'khung_gio_id' => $this->khung10->id, 'gio_thuc_hien' => '10:00', 'gio_ket_thuc' => '11:00',
            ]));

        $this->assertSame(1, KhachHang::count());
        $this->assertSame('Nguyễn A Đổi', KhachHang::first()->ho_ten);
    }

    // ===== A3. Slot phòng =====
    public function test_A3_2_phong_slot2_full_thi_block(): void
    {
        $payload = $this->bookingPayload();
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $payload + ['so_dien_thoai' => '0900000010']);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $payload + ['so_dien_thoai' => '0900000011']);

        // booking #3 cùng khung phải bị chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $payload + ['so_dien_thoai' => '0900000012'])
            ->assertSessionHasErrors(['khung_gio_id']);

        $this->assertSame(2, Booking::count());
    }

    public function test_A3_3_phong_slot1_full_thi_block(): void
    {
        $p = $this->bookingPayload(['phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p + ['so_dien_thoai' => '0900000020']);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $p + ['so_dien_thoai' => '0900000021'])
            ->assertSessionHasErrors(['khung_gio_id']);

        $this->assertSame(1, Booking::count());
    }

    public function test_A3_4_khac_ngay_khong_block(): void
    {
        $p = $this->bookingPayload(['phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", array_merge($p, ['so_dien_thoai' => '0900000020']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", array_merge($p, [
            'so_dien_thoai' => '0900000021',
            'ngay_dat' => now()->addDays(2)->toDateString(),
        ]));

        $this->assertSame(2, Booking::count());
    }

    // ===== A4. Trùng BÁC SĨ → cảnh báo, KHÔNG chặn =====
    public function test_A4_1_trung_bs_co_warning_van_luu(): void
    {
        $p1 = $this->bookingPayload(['bac_si_user_id' => $this->bacSi->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p1 + ['so_dien_thoai' => '0900000030']);

        // booking 2 ở phòng B cùng giờ với BS Z
        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id,
            'khung_gio_id' => $this->khung9_p1->id,
            'bac_si_user_id' => $this->bacSi->id,
            'so_dien_thoai' => '0900000031',
        ]);
        $resp = $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p2);
        $resp->assertRedirect("/{$this->coSo->slug}/danh-sach")->assertSessionHas('warning');

        $this->assertSame(2, Booking::count());
    }

    public function test_A4_3_sat_gio_khong_canh_bao(): void
    {
        $p1 = $this->bookingPayload([
            'bac_si_user_id' => $this->bacSi->id,
            'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '10:00',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p1 + ['so_dien_thoai' => '0900000040']);

        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'bac_si_user_id' => $this->bacSi->id,
            'gio_thuc_hien' => '10:00', 'gio_ket_thuc' => '11:00',
            'so_dien_thoai' => '0900000041',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p2);

        // session('warning') có thể null/undefined - kiểm tra không có hoặc null
        $warning = session('warning');
        $this->assertTrue($warning === null || $warning === '');
    }

    // ===== A5. Trùng KTV → CHẶN =====
    public function test_A5_1_trung_ktv_bi_chan(): void
    {
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p1 + ['so_dien_thoai' => '0900000050']);

        // KTV X có lịch khung_gio=9 ngày X → đặt cùng khung_gio_id (vẫn check theo khung_gio_id)
        $p2 = $this->bookingPayload([
            'ktv_user_id' => $this->ktv->id,
            'so_dien_thoai' => '0900000051',
        ]);
        $resp = $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p2);
        $resp->assertSessionHasErrors(['ktv_user_id']);

        $this->assertSame(1, Booking::count());
    }

    public function test_A5_2_ktv_khac_ngay_ok(): void
    {
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p1 + ['so_dien_thoai' => '0900000060']);

        $p2 = $this->bookingPayload([
            'ktv_user_id' => $this->ktv->id,
            'ngay_dat' => now()->addDays(2)->toDateString(),
            'so_dien_thoai' => '0900000061',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p2);

        $this->assertSame(2, Booking::count());
    }

    // ===== A7. Validation =====
    public function test_A7_1_required_fields(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", [])
            ->assertSessionHasErrors(['ho_ten', 'so_dien_thoai', 'phong_id', 'khung_gio_id', 'dich_vu_id', 'sale_id']);
    }

    public function test_A7_3_gio_thuc_hien_regex(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['gio_thuc_hien' => '09:15']))
            ->assertSessionHasErrors(['gio_thuc_hien']);
    }

    public function test_A7_4_sdt_co_space_duoc_trim(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['so_dien_thoai' => '0912 345 678']));

        $kh = KhachHang::first();
        $this->assertSame('0912345678', $kh->so_dien_thoai);
    }

    public function test_A7_5_phong_co_so_khac_bi_loi(): void
    {
        $phongCs2 = \App\Models\Phong::create([
            'co_so_id' => $this->coSo2->id, 'ten' => 'Phòng CS2',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 1, 'trang_thai' => 'hoat_dong',
        ]);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['phong_id' => $phongCs2->id]))
            ->assertSessionHasErrors(['phong_id']);
    }

    // ===== A2. Ngày quá khứ =====
    public function test_A2_5_ngay_qua_khu_bi_chan(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['ngay_dat' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors(['ngay_dat']);
    }

    // ===== A5. KTV trùng giờ phòng KHÁC (fix bug #4) =====
    public function test_A5_3_ktv_trung_gio_phong_khac_chan(): void
    {
        // BK1: phòng A, khung 09:00 (09:00-10:00), KTV X
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/tao-moi", $p1);

        // BK2: phòng B (slot=1), khung 09:00 phòng B (09:00-10:00), CÙNG KTV X
        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id,
            'khung_gio_id' => $this->khung9_p1->id,
            'ktv_user_id' => $this->ktv->id,
            'so_dien_thoai' => '0900000099',
        ]);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $p2)
            ->assertSessionHasErrors(['ktv_user_id']);
    }

    // ===== A6. Chéo Booking ↔ LichHen (fix bug #2) =====
    public function test_A6_1_bs_co_lich_tu_van_canh_bao_khi_dat_phong(): void
    {
        // Tạo lịch tư vấn cho BS Z, ca khám 09:00-09:30
        $ck = \App\Models\CaKham::create([
            'user_id' => $this->bacSi->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '09:30:00', 'thu_tu' => 0,
        ]);
        $kh = \App\Models\KhachHang::create([
            'co_so_id' => $this->coSo->id, 'ho_ten' => 'X', 'so_dien_thoai' => '0911',
        ]);
        \App\Models\LichHen::create([
            'co_so_id' => $this->coSo->id, 'khach_hang_id' => $kh->id,
            'bac_si_user_id' => $this->bacSi->id, 'ca_kham_id' => $ck->id,
            'sale_id' => $this->sale->id,
            'ngay_hen' => now()->addDay()->toDateString(),
            'trang_thai' => 'cho_duyet',
        ]);

        // Đặt phòng cho BS Z cùng giờ 09:00-10:00 → có warning
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['bac_si_user_id' => $this->bacSi->id]))
            ->assertSessionHas('warning');
    }

    // ===== A1.2/3 Tùy chọn BS/KTV để trống =====
    public function test_A1_2_bs_null_ok(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/tao-moi", $this->bookingPayload(['bac_si_user_id' => null, 'ktv_user_id' => null]))
            ->assertRedirect();

        $this->assertNull(Booking::first()->bac_si_user_id);
        $this->assertNull(Booking::first()->ktv_user_id);
    }
}
