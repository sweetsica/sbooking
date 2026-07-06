<?php

namespace Tests\Feature;

use App\Models\CaKham;
use App\Models\KhachHang;
use App\Models\LichHen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LichHenFlowTest extends TestCase
{
    use RefreshDatabase, BookingTestSetup;

    protected CaKham $caKham1;
    protected CaKham $caKham2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();

        // BS tư vấn local (bacSi) cần caKhams; gán user_id cho caKham
        $this->caKham1 = CaKham::create([
            'bac_si_id' => $this->bsCaHai->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '09:30:00', 'thu_tu' => 0,
        ]);
        $this->caKham2 = CaKham::create([
            'bac_si_id' => $this->bsCaHai->id,
            'gio_bat_dau' => '09:30:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 1,
        ]);
    }

    protected function lichHenPayload(array $overrides = []): array
    {
        return array_merge([
            'ho_ten'         => 'Nguyễn TV',
            'so_dien_thoai'  => '0922222222',
            'email'          => null,
            'ngay_hen'       => now()->addDay()->toDateString(),
            'bac_si_id' => $this->bsCaHai->id,
            'ca_kham_id'     => $this->caKham1->id,
            'sale_id'        => $this->sale->id,
            'nguon'          => 'Hotline',
            'ghi_chu'        => null,
        ], $overrides);
    }

    protected function makeLichHen(array $overrides = []): LichHen
    {
        $kh = KhachHang::firstOrCreate(
            ['co_so_id' => $this->coSo->id, 'so_dien_thoai' => '0933333333'],
            ['ho_ten' => 'KH LH']
        );
        return LichHen::create(array_merge([
            'co_so_id'       => $this->coSo->id,
            'khach_hang_id'  => $kh->id,
            'bac_si_id' => $this->bsCaHai->id,
            'ca_kham_id'     => $this->caKham1->id,
            'sale_id'        => $this->sale->id,
            'ngay_hen'       => now()->addDay()->toDateString(),
            'trang_thai'     => 'cho_duyet',
        ], $overrides));
    }

    // ===== C1 =====
    public function test_C1_1_validation_required(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-kham", [])
            ->assertSessionHasErrors(['ho_ten', 'so_dien_thoai', 'bac_si_id', 'ca_kham_id', 'sale_id']);
    }

    public function test_C1_3_ca_kham_da_co_lich_bi_chan(): void
    {
        $this->makeLichHen();

        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-kham", $this->lichHenPayload(['so_dien_thoai' => '0944444444']))
            ->assertSessionHasErrors(['ca_kham_id']);

        $this->assertSame(1, LichHen::count());
    }

    public function test_C1_5_tao_thanh_cong(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-kham", $this->lichHenPayload())
            ->assertRedirect("/{$this->coSo->slug}/ds-tu-van");

        $lh = LichHen::first();
        $this->assertSame('cho_duyet', $lh->trang_thai);
    }

    public function test_C1_khac_ngay_ok(): void
    {
        $this->makeLichHen();
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-kham", $this->lichHenPayload([
                'ngay_hen' => now()->addDays(2)->toDateString(),
                'so_dien_thoai' => '0944444444',
            ]));

        $this->assertSame(2, LichHen::count());
    }

    public function test_C1_guest_redirect_login(): void
    {
        $this->post("/{$this->coSo->slug}/dat-kham", $this->lichHenPayload())
            ->assertRedirect('/login');
    }

    public function test_C1_ngay_qua_khu_bi_chan(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-kham", $this->lichHenPayload([
                'ngay_hen' => now()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['ngay_hen']);
    }

    // ===== C2 =====
    public function test_C2_1_no_perm_sua_403(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->noPerm)
            ->get("/{$this->coSo->slug}/sua-tu-van/{$lh->id}")
            ->assertForbidden();
    }

    public function test_C2_2_sua_giu_nguyen_ca_ok(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-tu-van/{$lh->id}", $this->lichHenPayload([
                'ho_ten' => 'KH LH', 'so_dien_thoai' => '0933333333',
                'ghi_chu' => 'cập nhật',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('cập nhật', $lh->fresh()->ghi_chu);
    }

    public function test_C2_3_doi_ca_da_co_lich_loi(): void
    {
        $this->makeLichHen(['ca_kham_id' => $this->caKham1->id]);
        $lh2 = $this->makeLichHen([
            'ca_kham_id' => $this->caKham2->id,
            'ngay_hen' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->put("/{$this->coSo->slug}/sua-tu-van/{$lh2->id}", $this->lichHenPayload([
                'ho_ten' => 'KH LH', 'so_dien_thoai' => '0933333333',
                'ca_kham_id' => $this->caKham1->id,
            ]))
            ->assertSessionHasErrors(['ca_kham_id']);
    }

    // ===== C3 =====
    public function test_C3_1_no_perm_duyet_403(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->tuVanVien)
            ->patch("/{$this->coSo->slug}/duyet-tu-van/{$lh->id}")
            ->assertForbidden();
    }

    public function test_C3_2_duyet_ok(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-tu-van/{$lh->id}");

        $this->assertSame('da_duyet', $lh->fresh()->trang_thai);
    }

    public function test_C3_3_duyet_lai_toggle(): void
    {
        $lh = $this->makeLichHen(['trang_thai' => 'da_duyet']);
        $this->actingAs($this->vanHanh)
            ->patch("/{$this->coSo->slug}/duyet-tu-van/{$lh->id}");

        $this->assertSame('cho_duyet', $lh->fresh()->trang_thai);
    }

    public function test_C3_admin_xoa_ok(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->admin)
            ->delete("/{$this->coSo->slug}/xoa-tu-van/{$lh->id}")
            ->assertRedirect();
        $this->assertSame(0, LichHen::count());
    }

    public function test_C3_no_perm_xoa_403(): void
    {
        $lh = $this->makeLichHen();
        $this->actingAs($this->vanHanh)
            ->delete("/{$this->coSo->slug}/xoa-tu-van/{$lh->id}")
            ->assertForbidden();
    }
}
