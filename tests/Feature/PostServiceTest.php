<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingBinhLuan;
use App\Models\KhachHang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
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
            'co_so_id' => $this->coSo->id, 'ho_ten' => 'KH PS', 'so_dien_thoai' => '0912345678',
        ]);
        return Booking::create(array_merge([
            'co_so_id'      => $this->coSo->id,
            'khach_hang_id' => $kh->id,
            'phong_id'      => $this->phongSlot1->id,
            'khung_gio_id'  => $this->khung9_p1->id,
            'dich_vu_id'    => $this->dichVu->id,
            'sale_id'       => $this->sale->id,
            'ngay_dat'      => now()->addDay()->toDateString(),
            'gio_thuc_hien' => '09:00:00',
            'gio_ket_thuc'  => '10:00:00',
            'trang_thai'    => 'da_duyet',
        ], $overrides));
    }

    public function test_cap_nhat_trang_thai_khach_ok(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/trang-thai-khach/{$bk->id}", ['trang_thai_khach' => 'da_toi'])
            ->assertRedirect();
        $this->assertSame('da_toi', $bk->fresh()->trang_thai_khach);
    }

    public function test_toggle_bo_trang_thai(): void
    {
        $bk = $this->makeBooking(['trang_thai_khach' => 'da_toi']);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/trang-thai-khach/{$bk->id}", ['trang_thai_khach' => 'da_toi']);
        $this->assertNull($bk->fresh()->trang_thai_khach); // bấm lại → bỏ chọn
    }

    public function test_khong_co_quyen_bi_chan(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->noPerm)
            ->patch("/{$this->coSo->slug}/trang-thai-khach/{$bk->id}", ['trang_thai_khach' => 'huy'])
            ->assertForbidden();
    }

    public function test_khach_huy_tra_slot_ve_kho(): void
    {
        // Phòng B (slot=1) đã có 1 booking chiếm khung 09:00.
        $bk = $this->makeBooking();
        $this->assertSame(1, Booking::giuCho()->where('phong_id', $this->phongSlot1->id)->count());

        // Khách hủy → không còn giữ chỗ.
        $bk->update(['trang_thai_khach' => 'huy']);
        $this->assertSame(0, Booking::giuCho()->where('phong_id', $this->phongSlot1->id)->count());
    }

    public function test_them_binh_luan(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/binh-luan/{$bk->id}", ['noi_dung' => 'Khách phàn nàn mùi'])
            ->assertRedirect();
        $this->assertDatabaseHas('booking_binh_luan', [
            'booking_id' => $bk->id, 'noi_dung' => 'Khách phàn nàn mùi', 'user_id' => $this->vanHanh->id,
        ]);
    }

    public function test_binh_luan_khong_quyen_bi_chan(): void
    {
        $bk = $this->makeBooking();
        $this->actingAs($this->noPerm)
            ->post("/{$this->coSo->slug}/binh-luan/{$bk->id}", ['noi_dung' => 'x'])
            ->assertForbidden();
    }

    public function test_chi_admin_xoa_binh_luan(): void
    {
        $bk = $this->makeBooking();
        $bl = BookingBinhLuan::create(['booking_id' => $bk->id, 'user_id' => $this->vanHanh->id, 'noi_dung' => 'abc']);

        // Không phải admin → cấm.
        $this->actingAs($this->vanHanh)
            ->delete("/{$this->coSo->slug}/binh-luan/{$bk->id}/{$bl->id}")
            ->assertForbidden();
        $this->assertDatabaseHas('booking_binh_luan', ['id' => $bl->id]);

        // Admin hệ thống → xóa được.
        $this->actingAs($this->admin)
            ->delete("/{$this->coSo->slug}/binh-luan/{$bk->id}/{$bl->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('booking_binh_luan', ['id' => $bl->id]);
    }
}
