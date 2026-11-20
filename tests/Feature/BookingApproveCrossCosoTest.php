<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 2026-11-20: luồng cross-cơ-sở (sale ĐN đặt khách HCM).
 * - Same-cơ-sở self-owned: force creator, ignore payload.
 * - Cross-cơ-sở self-owned: dùng payload (sale cơ sở đích), nguoi_tao_id không đổi.
 * - Cross-cơ-sở + payload sai cơ sở: 422.
 */
class BookingApproveCrossCosoTest extends TestCase
{
    use RefreshDatabase, BookingTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    protected function makeSelfOwnedBooking(int $bookingCoSoId, int $creatorId): Booking
    {
        $kh = KhachHang::create([
            'co_so_id' => $bookingCoSoId,
            'ho_ten' => 'KH Cross',
            'so_dien_thoai' => '0900000000',
        ]);
        return Booking::create([
            'co_so_id'      => $bookingCoSoId,
            'khach_hang_id' => $kh->id,
            'phong_id'      => $this->phongSlot2->id,
            'khung_gio_id'  => $this->khung9->id,
            'dich_vu_id'    => $this->dichVu->id,
            'sale_id'       => $creatorId,
            'nguoi_tao_id'  => $creatorId,
            'nguon'         => 'sa',
            'ngay_dat'      => now()->addDay()->toDateString(),
            'gio_thuc_hien' => '09:00:00',
            'gio_ket_thuc'  => '10:00:00',
            'trang_thai'    => 'cho_duyet',
            'da_duyet'      => false,
        ]);
    }

    public function test_self_owned_same_coso_force_creator_ignore_payload(): void
    {
        $creator = $this->mkUser('Sale Gốc', 'sg1', $this->vrNhanVien->id);
        $otherSale = $this->mkUser('Sale Khác', 'sk1', $this->vrNhanVien->id);
        $bk = $this->makeSelfOwnedBooking($this->coSo->id, $creator->id);

        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}", [
                'tiep_don_user_id' => $otherSale->id, // payload — sẽ bị override
            ])
            ->assertRedirect();

        $bk->refresh();
        $this->assertTrue((bool) $bk->da_duyet);
        $this->assertSame($creator->id, $bk->tiep_don_user_id, 'Same-cơ-sở self-owned phải force về creator');
        $this->assertSame($creator->id, $bk->nguoi_tao_id);
    }

    public function test_self_owned_cross_coso_use_payload_keep_creator(): void
    {
        // Sale ĐN (coSo2) đặt khách HCM (coSo).
        $saleDN = $this->mkUser('Sale ĐN', 'saledn', $this->vrNhanVien->id, ['co_so_id' => $this->coSo2->id]);
        $saleHCM = $this->mkUser('Sale HCM', 'salehcm', $this->vrNhanVien->id); // default co_so = coSo
        $bk = $this->makeSelfOwnedBooking($this->coSo->id, $saleDN->id);

        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}", [
                'tiep_don_user_id' => $saleHCM->id,
            ])
            ->assertRedirect();

        $bk->refresh();
        $this->assertTrue((bool) $bk->da_duyet, 'Cross-cơ-sở phải duyệt được');
        $this->assertSame($saleHCM->id, $bk->tiep_don_user_id, 'Tiếp đón = sale cơ sở đích (payload)');
        $this->assertSame($saleDN->id, $bk->nguoi_tao_id, 'Creator giữ nguyên → quyền sở hữu sale gốc');
    }

    public function test_cross_coso_payload_wrong_coso_rejected(): void
    {
        $saleDN = $this->mkUser('Sale ĐN 2', 'saledn2', $this->vrNhanVien->id, ['co_so_id' => $this->coSo2->id]);
        $saleDN_other = $this->mkUser('Sale ĐN Khác', 'saledn3', $this->vrNhanVien->id, ['co_so_id' => $this->coSo2->id]);
        $bk = $this->makeSelfOwnedBooking($this->coSo->id, $saleDN->id);

        $resp = $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-dat-phong/{$bk->id}", [
                'tiep_don_user_id' => $saleDN_other->id, // sale cơ sở sai
            ]);
        $resp->assertStatus(422);

        $bk->refresh();
        $this->assertFalse((bool) $bk->da_duyet, 'Không được duyệt khi sale sai cơ sở');
    }
}
