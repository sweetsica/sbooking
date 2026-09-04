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
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload())
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
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['ho_ten' => 'Nguyễn A', 'so_dien_thoai' => '0900000001']));

        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
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
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $payload + ['so_dien_thoai' => '0900000010']);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $payload + ['so_dien_thoai' => '0900000011']);

        // booking #3 cùng khung phải bị chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $payload + ['so_dien_thoai' => '0900000012'])
            ->assertSessionHasErrors(['khung_gio_id']);

        $this->assertSame(2, Booking::count());
    }

    public function test_A3_3_phong_slot1_full_thi_block(): void
    {
        $p = $this->bookingPayload(['phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p + ['so_dien_thoai' => '0900000020']);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p + ['so_dien_thoai' => '0900000021'])
            ->assertSessionHasErrors(['khung_gio_id']);

        $this->assertSame(1, Booking::count());
    }

    public function test_A3_4_khac_ngay_khong_block(): void
    {
        $p = $this->bookingPayload(['phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0900000020']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, [
            'so_dien_thoai' => '0900000021',
            'ngay_dat' => now()->addDays(2)->toDateString(),
        ]));

        $this->assertSame(2, Booking::count());
    }

    // ===== A4. Trùng BÁC SĨ → cảnh báo, KHÔNG chặn =====
    public function test_A4_1_trung_bs_co_warning_van_luu(): void
    {
        $p1 = $this->bookingPayload(['bac_si_id' => $this->bacSi->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p1 + ['so_dien_thoai' => '0900000030']);

        // booking 2 ở phòng B cùng giờ với BS Z
        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id,
            'khung_gio_id' => $this->khung9_p1->id,
            'bac_si_id' => $this->bacSi->id,
            'so_dien_thoai' => '0900000031',
        ]);
        $resp = $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p2);
        $resp->assertRedirect("/{$this->coSo->slug}/danh-sach")->assertSessionHas('warning');

        $this->assertSame(2, Booking::count());
    }

    public function test_A4_3_sat_gio_khong_canh_bao(): void
    {
        $p1 = $this->bookingPayload([
            'bac_si_id' => $this->bacSi->id,
            'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '10:00',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p1 + ['so_dien_thoai' => '0900000040']);

        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id, 'khung_gio_id' => $this->khung9_p1->id,
            'bac_si_id' => $this->bacSi->id,
            'gio_thuc_hien' => '10:00', 'gio_ket_thuc' => '11:00',
            'so_dien_thoai' => '0900000041',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p2);

        // session('warning') có thể null/undefined - kiểm tra không có hoặc null
        $warning = session('warning');
        $this->assertTrue($warning === null || $warning === '');
    }

    // ===== A5. Trùng KTV → CHẶN =====
    public function test_A5_1_trung_ktv_bi_chan(): void
    {
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p1 + ['so_dien_thoai' => '0900000050']);

        // KTV X có lịch khung_gio=9 ngày X → đặt cùng khung_gio_id (vẫn check theo khung_gio_id)
        $p2 = $this->bookingPayload([
            'ktv_user_id' => $this->ktv->id,
            'so_dien_thoai' => '0900000051',
        ]);
        $resp = $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p2);
        $resp->assertSessionHasErrors(['ktv_user_id']);

        $this->assertSame(1, Booking::count());
    }

    public function test_A5_2_ktv_khac_ngay_ok(): void
    {
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p1 + ['so_dien_thoai' => '0900000060']);

        $p2 = $this->bookingPayload([
            'ktv_user_id' => $this->ktv->id,
            'ngay_dat' => now()->addDays(2)->toDateString(),
            'so_dien_thoai' => '0900000061',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p2);

        $this->assertSame(2, Booking::count());
    }

    // ===== A7. Validation =====
    public function test_A7_1_required_fields(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", [])
            ->assertSessionHasErrors(['ho_ten', 'so_dien_thoai', 'phong_id', 'khung_gio_id', 'dich_vu_id', 'sale_id']);
    }

    public function test_A7_3_gio_thuc_hien_regex(): void
    {
        // Sai format HH:MM bị reject
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['gio_thuc_hien' => '9-15']))
            ->assertSessionHasErrors(['gio_thuc_hien']);
    }

    public function test_A7_3c_gio_truoc_khung_bi_chan(): void
    {
        // Khung 09:00-10:00, đặt giờ thực hiện 08:30 → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'gio_thuc_hien' => '08:30', 'gio_ket_thuc' => '09:30',
            ]))
            ->assertSessionHasErrors(['gio_thuc_hien']);
    }

    public function test_A7_3d_gio_ket_thuc_vuot_khung_bi_chan(): void
    {
        // Khung 09:00-10:00, kết thúc 10:30 → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '10:30',
            ]))
            ->assertSessionHasErrors(['gio_ket_thuc']);
    }

    public function test_A7_3e_ket_thuc_truoc_bat_dau_bi_chan(): void
    {
        // 09:30 → 09:00 (ngược) → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'gio_thuc_hien' => '09:30', 'gio_ket_thuc' => '09:00',
            ]))
            ->assertSessionHasErrors(['gio_ket_thuc']);
    }

    public function test_A7_3b_gio_25_phut_chap_nhan(): void
    {
        // Phút bất kỳ HH:MM (vd 25) phải chấp nhận để hỗ trợ BS có phút riêng (Bác Hồng 25p)
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'gio_thuc_hien' => '09:00',
                'gio_ket_thuc' => '09:25',
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_A7_4_sdt_co_space_duoc_trim(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['so_dien_thoai' => '0912 345 678']));

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
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['phong_id' => $phongCs2->id]))
            ->assertSessionHasErrors(['phong_id']);
    }

    // ===== A2. Ngày quá khứ =====
    public function test_A2_5_ngay_qua_khu_bi_chan(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['ngay_dat' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors(['ngay_dat']);
    }

    // ===== A5. KTV trùng giờ phòng KHÁC (fix bug #4) =====
    public function test_A5_3_ktv_trung_gio_phong_khac_chan(): void
    {
        // BK1: phòng A, khung 09:00 (09:00-10:00), KTV X
        $p1 = $this->bookingPayload(['ktv_user_id' => $this->ktv->id]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p1);

        // BK2: phòng B (slot=1), khung 09:00 phòng B (09:00-10:00), CÙNG KTV X
        $p2 = $this->bookingPayload([
            'phong_id' => $this->phongSlot1->id,
            'khung_gio_id' => $this->khung9_p1->id,
            'ktv_user_id' => $this->ktv->id,
            'so_dien_thoai' => '0900000099',
        ]);
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $p2)
            ->assertSessionHasErrors(['ktv_user_id']);
    }

    // ===== A6. Chéo Booking ↔ LichHen =====
    public function test_A6_1_bs_co_lich_tu_van_canh_bao_khi_dat_phong(): void
    {
        // Không còn áp dụng: bác sĩ của BOOKING phòng khám giờ là DANH MỤC bac_si,
        // còn bác sĩ của LỊCH HẸN tư vấn là tài khoản user (bac_si_user_id) — hai thực
        // thể khác nhau, nên không thể đối chiếu chéo trùng giờ giữa hai luồng.
        $this->markTestSkipped('Booking dùng danh mục bac_si, lịch tư vấn dùng user — không còn cảnh báo chéo.');
    }

    // ===== A8. Capacity bác sĩ theo phút (tư vấn 30p / khám LS 5p) =====
    public function test_A8_1_tu_van_2_khach_full_block_thu_3(): void
    {
        $p = $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuTuVan->id,
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0930000001']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0930000002']));

        // booking #3 cùng khung + BS → vượt 60p, bị chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0930000003']))
            ->assertSessionHasErrors(['bac_si_id']);

        $this->assertSame(2, Booking::count());
    }

    public function test_A8_2_kham_ls_12_khach_full_block_thu_13(): void
    {
        $p = $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuKhamLs->id,
        ]);
        for ($i = 1; $i <= 12; $i++) {
            $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '09300000'.sprintf('%02d', $i)]));
        }
        // booking #13 → 12×5=60 đã full
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0930000099']))
            ->assertSessionHasErrors(['bac_si_id']);

        $this->assertSame(12, Booking::count());
    }

    public function test_A8_3_mix_1_tu_van_6_kham_ls_full(): void
    {
        // 1 tư vấn (30) + 6 khám LS (5×6=30) = 60 phút
        $base = [
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
        ];
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload($base + [
            'dich_vu_id' => $this->dichVuTuVan->id,
            'so_dien_thoai' => '0930000010',
        ]));
        for ($i = 1; $i <= 6; $i++) {
            $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload($base + [
                'dich_vu_id' => $this->dichVuKhamLs->id,
                'so_dien_thoai' => '09300001'.sprintf('%02d', $i),
            ]));
        }
        $this->assertSame(7, Booking::count());

        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload($base + [
                'dich_vu_id' => $this->dichVuKhamLs->id,
                'so_dien_thoai' => '0930000098',
            ]))
            ->assertSessionHasErrors(['bac_si_id']);
    }

    public function test_A8_4_bs_khong_nhan_tu_van_bi_chan(): void
    {
        // BS chỉ nhận khám LS, nhưng đặt dịch vụ tư vấn → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'bac_si_id' => $this->bsChiKhamLs->id,
                'dich_vu_id' => $this->dichVuTuVan->id,
            ]))
            ->assertSessionHasErrors(['bac_si_id']);
    }

    public function test_A8_5_bs_khong_nhan_kham_ls_bi_chan(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'bac_si_id' => $this->bsChiTuVan->id,
                'dich_vu_id' => $this->dichVuKhamLs->id,
            ]))
            ->assertSessionHasErrors(['bac_si_id']);
    }

    public function test_A8_6_bs_full_van_dat_duoc_bs_khac(): void
    {
        // BS Cả Hai đầy với 2 tư vấn trong phòng Big
        $p = $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuTuVan->id,
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0931000001']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0931000002']));

        // Chuyển sang BS khác (Chỉ Tư Vấn) → OK (capacity tính riêng từng BS)
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'phong_id' => $this->phongBig->id,
                'khung_gio_id' => $this->khung9Big->id,
                'bac_si_id' => $this->bsChiTuVan->id,
                'dich_vu_id' => $this->dichVuTuVan->id,
                'so_dien_thoai' => '0931000003',
            ]))
            ->assertSessionHasNoErrors();
    }

    // ===== A8.7 Dịch vụ nhóm "khac" → phút lấy từ DichVu (vd siêu âm 25p) =====
    public function test_A8_7_dich_vu_khac_dung_phut_dich_vu(): void
    {
        // BS Hồng: không nhận tu_van, không nhận kham_ls (default phút BS không matter cho nhóm khác)
        $bsHong = $this->mkUser('BS Hồng', 'bshong', $this->vrBacSi->id, ['nhan_tu_van' => false, 'nhan_kham_ls' => false]);
        $sieuAm = \App\Models\DichVu::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Siêu âm',
            'thoi_gian_phut' => 25, 'thuoc_nhom' => 'khac', 'active' => true,
        ]);

        // 2 siêu âm × 25 = 50p → OK
        $p = $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $bsHong->id,
            'dich_vu_id' => $sieuAm->id,
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0932000001']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0932000002']));

        // booking #3 (25p nữa) → 75p > 60 → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", array_merge($p, ['so_dien_thoai' => '0932000003']))
            ->assertSessionHasErrors(['bac_si_id']);
    }

    // ===== A9. Capacity BS cross-cơ sở (BS global) =====
    public function test_A9_1_bs_global_da_co_lich_co_so_khac_bi_chan(): void
    {
        // bsGlobal đã có lịch tư vấn 30p ở cơ sở 1 khung 09:00-10:00
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuTuVan->id,
            'so_dien_thoai' => '0940000001',
        ]));

        // Tạo phòng + khung 09:00-10:00 ở CƠ SỞ 2
        $phongCs2 = \App\Models\Phong::create([
            'co_so_id' => $this->coSo2->id, 'ten' => 'Phòng CS2 Big',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 5, 'trang_thai' => 'hoat_dong',
        ]);
        $khungCs2 = \App\Models\KhungGio::create([
            'phong_id' => $phongCs2->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);
        $dvCs2 = \App\Models\DichVu::create([
            'co_so_id' => $this->coSo2->id, 'ten' => 'Tư vấn CS2',
            'thoi_gian_phut' => 30, 'thuoc_nhom' => 'tu_van', 'active' => true,
        ]);
        // Cập nhật vanHanh có quyền ở cs2 (dùng admin cho gọn)

        // Đặt BS Cả Hai (global) tại cơ sở 2 cùng giờ → vượt 60p vì đã dùng 30p cs1
        // 2 booking 30p mỗi cái = 60p, OK. Thêm cái thứ 2 ở cs2 nữa thì 90p > 60 → chặn
        $this->actingAs($this->admin)->post("/{$this->coSo2->slug}/dat-lich-tham-kham", $this->bookingPayload([
            'phong_id' => $phongCs2->id,
            'khung_gio_id' => $khungCs2->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $dvCs2->id,
            'sale_id' => $this->admin->id,
            'so_dien_thoai' => '0940000002',
        ]));
        $this->assertSame(2, Booking::count()); // 30+30=60 vừa đầy

        // Thêm 1 cái nữa cùng giờ → vượt
        $this->actingAs($this->admin)
            ->post("/{$this->coSo2->slug}/dat-lich-tham-kham", $this->bookingPayload([
                'phong_id' => $phongCs2->id,
                'khung_gio_id' => $khungCs2->id,
                'bac_si_id' => $this->bsCaHai->id,
                'dich_vu_id' => $dvCs2->id,
                'sale_id' => $this->admin->id,
                'so_dien_thoai' => '0940000003',
            ]))
            ->assertSessionHasErrors(['bac_si_id']);

        $this->assertSame(2, Booking::count());
    }

    // ===== A9.2 API check-bac-si trả về list với availability =====
    public function test_A9_2_check_bac_si_api(): void
    {
        // bsCaHai có 1 booking 30p ở khung9Big
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuTuVan->id,
            'so_dien_thoai' => '0941000001',
        ]));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload([
            'phong_id' => $this->phongBig->id,
            'khung_gio_id' => $this->khung9Big->id,
            'bac_si_id' => $this->bsCaHai->id,
            'dich_vu_id' => $this->dichVuTuVan->id,
            'so_dien_thoai' => '0941000002',
        ]));

        // API: chọn tu_van, khung 09:00 → bsCaHai đầy, bsChiTuVan còn rảnh
        $resp = $this->actingAs($this->vanHanh)
            ->getJson("/{$this->coSo->slug}/dat-lich-tham-kham/check-bac-si?phong_id={$this->phongBig->id}&dich_vu_id={$this->dichVuTuVan->id}&ngay=".now()->addDay()->toDateString()."&gio_bat_dau=09:00&gio_ket_thuc=10:00");

        $resp->assertOk();
        $list = collect($resp->json('list'));
        $caHai = $list->firstWhere('id', $this->bsCaHai->id);
        $chiTuVan = $list->firstWhere('id', $this->bsChiTuVan->id);
        $chiKhamLs = $list->firstWhere('id', $this->bsChiKhamLs->id);

        $this->assertFalse($caHai['available']);
        $this->assertTrue($chiTuVan['available']);
        $this->assertFalse($chiKhamLs['available']); // không nhận tư vấn
    }

    // ===== A10. Đặt lịch dịch vụ (không có BS) =====
    public function test_A10_1_dat_lich_dich_vu_ok(): void
    {
        $resp = $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-dich-vu", $this->bookingPayload([
                'ktv_user_id' => $this->ktv->id,
                'bac_si_id' => null,
            ]));

        $resp->assertRedirect();
        $bk = Booking::first();
        $this->assertSame('dich_vu', $bk->loai_dat_lich);
        $this->assertNull($bk->bac_si_id);
        $this->assertSame($this->ktv->id, $bk->ktv_user_id);
    }

    public function test_A10_2_dat_lich_phong_kham_van_ok(): void
    {
        $resp = $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload());

        $resp->assertRedirect();
        $bk = Booking::first();
        $this->assertSame('phong_kham', $bk->loai_dat_lich);
    }

    public function test_A10_4_form_kham_chi_show_phong_kham(): void
    {
        $resp = $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/dat-lich-tham-kham");

        $resp->assertOk();
        $resp->assertSee('Phòng A');
        $resp->assertDontSee('Phòng Xông T4');
    }

    public function test_A10_5_form_dich_vu_chi_show_phong_dich_vu(): void
    {
        $resp = $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/dat-lich-dich-vu");

        $resp->assertOk();
        $resp->assertSee('Phòng Xông T4');
        $resp->assertDontSee('Phòng A (2 slot)');
    }

    public function test_A10_6_phong_dich_vu_slot1_30p_chan_thu_3(): void
    {
        // Phòng dịch vụ slot=1, 30p → khung 09-10 có 2 sub-slot. 2 booking đầy.
        $p = $this->bookingPayload([
            'phong_id' => $this->phongDichVu->id,
            'khung_gio_id' => $this->khung9DV->id,
            'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '09:30',
            'bac_si_id' => null, 'ktv_user_id' => $this->ktv->id,
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-dich-vu", array_merge($p, ['so_dien_thoai' => '0950000001']));

        $p2 = array_merge($p, [
            'gio_thuc_hien' => '09:30', 'gio_ket_thuc' => '10:00',
            'so_dien_thoai' => '0950000002',
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-dich-vu", $p2);

        $this->assertSame(2, Booking::count());

        // 3rd booking overlap với 1 trong 2 → bị chặn (slot=1 full ở 09:00-09:30)
        $p3 = array_merge($p, [
            'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '09:30',
            'so_dien_thoai' => '0950000003',
        ]);
        $resp = $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-dich-vu", $p3);
        // Bị chặn (có thể do KTV trùng giờ hoặc slot phòng đầy - một trong hai key)
        $resp->assertSessionHasErrors();
        $this->assertSame(2, Booking::count());
    }

    public function test_A10_7_phong_dich_vu_3_booking_khong_overlap_ok(): void
    {
        // Tạo phòng dịch vụ slot=2 (cho 2 slot song song)
        $phong2 = \App\Models\Phong::create([
            'co_so_id' => $this->coSo->id, 'ten' => 'Phòng Dịch vụ Big',
            'kieu_phong' => 'phong_dich_vu',
            'loai' => 'cong_dong', 'so_slot_toi_da' => 2,
            'phut_moi_khach' => 30, 'trang_thai' => 'hoat_dong',
        ]);
        $kg = \App\Models\KhungGio::create([
            'phong_id' => $phong2->id,
            'gio_bat_dau' => '09:00:00', 'gio_ket_thuc' => '10:00:00', 'thu_tu' => 0,
        ]);

        // 2 booking 09:00-09:30 song song (slot=2) → OK
        $p = $this->bookingPayload([
            'phong_id' => $phong2->id, 'khung_gio_id' => $kg->id,
            'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '09:30',
            'bac_si_id' => null, 'ktv_user_id' => null,
        ]);
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-dich-vu", array_merge($p, ['so_dien_thoai' => '0960000001']));
        $this->actingAs($this->vanHanh)->post("/{$this->coSo->slug}/dat-lich-dich-vu", array_merge($p, ['so_dien_thoai' => '0960000002']));
        $this->assertSame(2, Booking::count());

        // 3rd cùng giờ → vượt slot=2 → chặn
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-dich-vu", array_merge($p, ['so_dien_thoai' => '0960000003']))
            ->assertSessionHasErrors(['khung_gio_id']);
    }

    public function test_A10_8_dich_vu_khong_can_sale_va_dich_vu_id(): void
    {
        // Đặt lịch dịch vụ chỉ với phòng + KTV → không cần sale_id và dich_vu_id
        $resp = $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-dich-vu", [
                'ho_ten' => 'Khách DV', 'so_dien_thoai' => '0970000001',
                'ngay_dat' => now()->addDay()->toDateString(),
                'phong_id' => $this->phongDichVu->id,
                'khung_gio_id' => $this->khung9DV->id,
                'gio_thuc_hien' => '09:00', 'gio_ket_thuc' => '09:30',
                'ktv_user_id' => $this->ktv->id,
            ]);

        $resp->assertRedirect();
        $resp->assertSessionHasNoErrors();
        $bk = Booking::first();
        $this->assertNull($bk->sale_id);
        $this->assertNull($bk->dich_vu_id);
        $this->assertSame('dich_vu', $bk->loai_dat_lich);
    }

    public function test_A10_9_phong_kham_van_can_sale_va_dich_vu(): void
    {
        // Đặt phòng khám thiếu sale_id → vẫn báo lỗi
        $resp = $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['sale_id' => null]));

        $resp->assertSessionHasErrors(['sale_id']);
    }

    public function test_A10_3_form_dich_vu_load_ok(): void
    {
        $this->actingAs($this->vanHanh)
            ->get("/{$this->coSo->slug}/dat-lich-dich-vu")
            ->assertOk();
    }

    // ===== A1.2/3 Tùy chọn BS/KTV để trống =====
    public function test_A1_2_bs_null_ok(): void
    {
        $this->actingAs($this->vanHanh)
            ->post("/{$this->coSo->slug}/dat-lich-tham-kham", $this->bookingPayload(['bac_si_id' => null, 'ktv_user_id' => null]))
            ->assertRedirect();

        $this->assertNull(Booking::first()->bac_si_id);
        $this->assertNull(Booking::first()->ktv_user_id);
    }
}
