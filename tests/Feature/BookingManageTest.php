<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\KhachHang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingManageTest extends TestCase
{
    use RefreshDatabase, BookingTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    protected function makeBooking(array $overrides = []): Booking
    {
        $kh = KhachHang::create([
            'co_so_id' => $this->coSo->id,
            'ho_ten' => 'KH Test',
            'so_dien_thoai' => '0911111111',
        ]);
        return Booking::create(array_merge([
            'co_so_id'     => $this->coSo->id,
            'khach_hang_id' => $kh->id,
            'phong_id'     => $this->phongSlot2->id,
            'khung_gio_id' => $this->khung9->id,
            'dich_vu_id'   => $this->dichVu->id,
            'sale_id'      => $this->sale->id,
            'ngay_dat'     => now()->addDay()->toDateString(),
            'gio_thuc_hien' => '09:00:00',
            'gio_ket_thuc'  => '10:00:00',
            'trang_thai'   => 'cho_duyet',
            'da_duyet'     => false,
        ], $overrides));
    }

    // ===== B1. Sửa booking =====
    public function test_B1_2_sua_sang_khung_gio_khac_ok(): void
    {
        $bk = $this->makeBooking();
        $payload = $this->bookingPayload([
            'ho_ten' => 'KH Test', 'so_dien_thoai' => '0911111111',
            'khung_gio_id' => $this->khung10->id,
            'gio_thuc_hien' => '10:00', 'gio_ket_thuc' => '11:00',
        ]);

        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-dat-phong/{$bk->id}", $payload)
            ->assertRedirect("/{$this->coSo->slug}/danh-sach");

        $this->assertSame($this->khung10->id, $bk->fresh()->khung_gio_id);
    }

    public function test_B1_3_sua_sang_khung_da_kin_bi_chan(): void
    {
        // Phòng B slot=1: tạo 1 booking khung 09:00
        $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
        ]);
        $bk2 = $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'ngay_dat' => now()->addDays(2)->toDateString(),
        ]);

        // Sửa bk2 về cùng ngày → trùng
        $payload = $this->bookingPayload([
            'ho_ten' => 'KH Test', 'so_dien_thoai' => '0911111111',
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'ngay_dat' => now()->addDay()->toDateString(),
        ]);
        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-dat-phong/{$bk2->id}", $payload)
            ->assertSessionHasErrors(['khung_gio_id']);
    }

    public function test_B1_4_sua_giu_nguyen_khung_gio_ok(): void
    {
        $bk = $this->makeBooking();
        $payload = $this->bookingPayload([
            'ho_ten' => 'KH Test', 'so_dien_thoai' => '0911111111',
            'ghi_chu' => 'Cập nhật ghi chú',
        ]);

        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-dat-phong/{$bk->id}", $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Cập nhật ghi chú', $bk->fresh()->ghi_chu);
    }

    public function test_B1_5_sua_ktv_bi_bus(): void
    {
        // bk1: Phòng A khung9 với KTV X
        $this->makeBooking(['ktv_user_id' => $this->ktv->id]);
        // bk2: Phòng A khung10 (chưa có KTV)
        $bk2 = $this->makeBooking(['khung_gio_id' => $this->khung10->id]);

        // Sửa bk2 → cùng khung9 + KTV X → trùng
        $payload = $this->bookingPayload([
            'ho_ten' => 'KH Test', 'so_dien_thoai' => '0911111111',
            'khung_gio_id' => $this->khung9->id,
            'ktv_user_id' => $this->ktv->id,
        ]);
        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-dat-phong/{$bk2->id}", $payload)
            ->assertSessionHasErrors(['ktv_user_id']);
    }

    public function test_B1_no_perm_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->noPerm)
            ->get("/{$this->coSo->slug}/sua-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_B1_co_so_khac_404(): void
    {
        $bk = $this->makeBooking(['co_so_id' => $this->coSo2->id]);
        $this->actingAs($this->admin)
            ->get("/{$this->coSo->slug}/sua-dat-phong/{$bk->id}")
            ->assertNotFound();
    }

    // ===== B3. Duyệt / Từ chối / Xong =====
    public function test_B3_1_no_perm_duyet_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->tuVanVien)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_B3_2_duyet_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}")
            ->assertRedirect();

        $bk->refresh();
        $this->assertTrue((bool) $bk->da_duyet);
        $this->assertSame('da_duyet', $bk->trang_thai);
    }

    public function test_B3_3_duyet_lai_toggle(): void
    {
        $bk = $this->makeBooking(['trang_thai' => 'da_duyet', 'da_duyet' => true]);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}");

        $bk->refresh();
        $this->assertSame('cho_duyet', $bk->trang_thai);
        $this->assertFalse((bool) $bk->da_duyet);
    }

    public function test_B3_4_tu_choi_thieu_ly_do(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/tu-choi-dat-phong/{$bk->id}", [])
            ->assertSessionHasErrors(['ly_do_tu_choi']);
    }

    public function test_B3_5_tu_choi_co_ly_do(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/tu-choi-dat-phong/{$bk->id}", ['ly_do_tu_choi' => 'Khách hủy']);

        $bk->refresh();
        $this->assertSame('tu_choi', $bk->trang_thai);
        $this->assertSame('Khách hủy', $bk->ly_do_tu_choi);
        $this->assertFalse((bool) $bk->da_duyet);
    }

    public function test_B3_6_duyet_lai_sau_tu_choi_xoa_ly_do(): void
    {
        $bk = $this->makeBooking(['trang_thai' => 'tu_choi', 'ly_do_tu_choi' => 'Khách hủy']);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}");

        $bk->refresh();
        $this->assertSame('da_duyet', $bk->trang_thai);
        $this->assertNull($bk->ly_do_tu_choi);
    }

    public function test_B3_7_xong_ok(): void
    {
        $bk = $this->makeBooking(['trang_thai' => 'da_duyet', 'da_duyet' => true]);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/xong-dat-phong/{$bk->id}");

        $bk->refresh();
        $this->assertSame('da_xong', $bk->trang_thai);
    }

    public function test_B3_8_xong_toggle_ve_da_duyet(): void
    {
        $bk = $this->makeBooking(['trang_thai' => 'da_xong', 'da_duyet' => true]);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/xong-dat-phong/{$bk->id}");

        $bk->refresh();
        $this->assertSame('da_duyet', $bk->trang_thai);
    }

    // ===== B5. Re-check conflict khi duyệt lại đơn từ chối (bug #6) =====
    public function test_B5_1_don_tu_choi_khong_chiem_slot(): void
    {
        // BK1: phòng B (slot=1), khung 09:00 → bị từ chối
        $bk1 = $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'trang_thai' => 'tu_choi', 'ly_do_tu_choi' => 'Trùng lịch',
        ]);

        // BK2 mới tạo cùng phòng B + khung 09:00 → vẫn đặt được (BK1 tu_choi không chiếm slot)
        $payload = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'so_dien_thoai' => '0922222222',
        ]);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $payload)
            ->assertSessionHasNoErrors();
    }

    public function test_B5_2_duyet_lai_don_tu_choi_khi_slot_bi_chiem_bao_loi(): void
    {
        // BK1: tu_choi (slot phòng B, khung 09:00)
        $bk1 = $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'trang_thai' => 'tu_choi', 'ly_do_tu_choi' => 'Khách hủy',
            'da_duyet' => false,
        ]);

        // BK2: đặt mới vào đúng slot đó (slot=1 nên BK2 chiếm hết slot)
        $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'trang_thai' => 'da_duyet', 'da_duyet' => true,
        ]);

        // Admin duyệt lại BK1 → phải báo lỗi "đã được đặt kín bởi đơn khác"
        $resp = $this->actingAs($this->admin)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk1->id}");

        $resp->assertRedirect();
        $this->assertSame('tu_choi', $bk1->fresh()->trang_thai);  // KHÔNG được duyệt
        $this->assertNotNull(session('error'));
    }

    public function test_B5_3_duyet_lai_ok_khi_slot_con_trong(): void
    {
        // BK1: tu_choi (slot phòng B, khung 09:00)
        $bk1 = $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'trang_thai' => 'tu_choi', 'ly_do_tu_choi' => 'Khách hủy',
            'da_duyet' => false,
        ]);

        // Không có booking nào khác chiếm slot
        $this->actingAs($this->admin)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk1->id}");

        $bk1->refresh();
        $this->assertSame('da_duyet', $bk1->trang_thai);
        $this->assertNull($bk1->ly_do_tu_choi);
    }

    public function test_B5_4_duyet_lai_ktv_bi_chiem_bao_loi(): void
    {
        // BK1: tu_choi, có KTV X
        $bk1 = $this->makeBooking([
            'trang_thai' => 'tu_choi', 'ly_do_tu_choi' => 'X',
            'ktv_user_id' => $this->ktv->id,
        ]);

        // BK2: chiếm KTV X cùng khung giờ
        $this->makeBooking([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'ktv_user_id' => $this->ktv->id,
            'trang_thai' => 'da_duyet', 'da_duyet' => true,
        ]);

        // Duyệt lại BK1 → báo lỗi KTV bị chiếm
        $this->actingAs($this->admin)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk1->id}");

        $this->assertSame('tu_choi', $bk1->fresh()->trang_thai);
        $this->assertNotNull(session('error'));
    }

    // ===== B4. Xóa =====
    public function test_B4_1_no_perm_xoa_403(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->delete("/{$this->coSo->slug}/xoa-dat-phong/{$bk->id}")
            ->assertForbidden();
    }

    public function test_B4_2_admin_xoa_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->admin)
            ->delete("/{$this->coSo->slug}/xoa-dat-phong/{$bk->id}")
            ->assertRedirect();
        $this->assertSame(0, Booking::count());
    }
}
