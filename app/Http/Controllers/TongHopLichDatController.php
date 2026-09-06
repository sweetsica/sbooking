<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\LichHen;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Trang "Tổng hợp lịch đặt" — gộp Booking (K + DV) + LichHen (TV) của cơ sở đang xem.
 *
 * Phase 1: xem + filter. Phase 2/3/4 (xuất Excel / template / nhập Excel) sẽ bổ sung
 * vào chính controller này.
 *
 * Middleware `admin` đã áp ở block routes → chỉ admin hệ thống vào được.
 */
class TongHopLichDatController extends Controller
{
    public function index(CoSo $co_so, Request $request)
    {
        $phanLoai = $request->query('phan_loai', 'all'); // all | K | TV | DV
        $tu       = $request->query('tu');
        $den      = $request->query('den');
        $q        = trim((string) $request->query('q', ''));

        // ----- Booking (K + DV) -----
        $bookings = collect();
        if ($phanLoai === 'all' || $phanLoai === 'K' || $phanLoai === 'DV') {
            $bq = Booking::where('co_so_id', $co_so->id)
                ->with(['khachHang', 'dichVu', 'sale', 'menus'])
                ->when($tu,  fn ($q1) => $q1->whereDate('ngay_dat', '>=', $tu))
                ->when($den, fn ($q1) => $q1->whereDate('ngay_dat', '<=', $den))
                ->when($q !== '', fn ($q1) => $q1->whereHas('khachHang', function ($k) use ($q) {
                    $k->where('ho_ten', 'like', "%{$q}%")
                      ->orWhere('so_dien_thoai', 'like', "%{$q}%");
                }));

            if ($phanLoai === 'K')  $bq->where('loai_dat_lich', 'phong_kham');
            if ($phanLoai === 'DV') $bq->where('loai_dat_lich', 'dich_vu');

            $bookings = $bq->orderByDesc('ngay_dat')->orderBy('gio_thuc_hien')->get();
        }

        // ----- LichHen (TV) -----
        $lichHens = collect();
        if ($phanLoai === 'all' || $phanLoai === 'TV') {
            $lq = LichHen::where('co_so_id', $co_so->id)
                ->with(['khachHang', 'bacSiTuVan', 'caKham', 'sale'])
                ->when($tu,  fn ($q1) => $q1->whereDate('ngay_hen', '>=', $tu))
                ->when($den, fn ($q1) => $q1->whereDate('ngay_hen', '<=', $den))
                ->when($q !== '', fn ($q1) => $q1->whereHas('khachHang', function ($k) use ($q) {
                    $k->where('ho_ten', 'like', "%{$q}%")
                      ->orWhere('so_dien_thoai', 'like', "%{$q}%");
                }));

            $lichHens = $lq->orderByDesc('ngay_hen')->orderBy('id')->get();
        }

        // Chuẩn hoá thành 1 collection thống nhất để render bảng đơn.
        $rows = collect();

        foreach ($bookings as $b) {
            $la_dv = $b->loai_dat_lich === 'dich_vu';
            $rows->push((object) [
                'phan_loai'    => $la_dv ? 'DV' : 'K',
                'phan_loai_label' => $la_dv ? 'Lịch dịch vụ' : 'Lịch khám',
                'ma_dl'        => 'BKG-'.str_pad((string) $b->id, 6, '0', STR_PAD_LEFT),
                'ten_khach'    => $b->khachHang?->ho_ten,
                'sdt'          => $b->khachHang?->so_dien_thoai,
                'sale'         => $b->sale?->name,
                'danh_muc'     => $b->dichVu?->ten ?? $b->menus->pluck('ten')->join(', '),
                'ngay'         => $b->ngay_dat,
                'gio'          => $b->gio_thuc_hien,
                'trang_thai'   => $b->trang_thai,       // cho_duyet | da_duyet | da_xong | tu_choi
                'ket_qua'      => $this->ketQuaBooking($b),
                'sort_key'     => optional($b->ngay_dat)->format('Y-m-d').' '.($b->gio_thuc_hien ?? ''),
            ]);
        }

        foreach ($lichHens as $l) {
            $rows->push((object) [
                'phan_loai'    => 'TV',
                'phan_loai_label' => 'Lịch tư vấn',
                'ma_dl'        => 'TVN-'.str_pad((string) $l->id, 6, '0', STR_PAD_LEFT),
                'ten_khach'    => $l->khachHang?->ho_ten,
                'sdt'          => $l->khachHang?->so_dien_thoai,
                'sale'         => $l->sale?->name,
                'danh_muc'     => $l->bacSiTuVan?->ten,
                'ngay'         => $l->ngay_hen,
                'gio'          => optional($l->caKham)->gio_bat_dau,
                'trang_thai'   => $l->trang_thai,
                'ket_qua'      => null,   // lich_hen không có "đã xong"
                'sort_key'     => optional($l->ngay_hen)->format('Y-m-d').' '.(optional($l->caKham)->gio_bat_dau ?? ''),
            ]);
        }

        $rows = $rows->sortByDesc('sort_key')->values();

        $counter = [
            'total' => $rows->count(),
            'K'     => $rows->where('phan_loai', 'K')->count(),
            'TV'    => $rows->where('phan_loai', 'TV')->count(),
            'DV'    => $rows->where('phan_loai', 'DV')->count(),
        ];

        return view('longevity.settings.tong-hop-lich-dat', [
            'coSo'    => $co_so,
            'rows'    => $rows,
            'counter' => $counter,
            'filters' => compact('phanLoai', 'tu', 'den', 'q'),
        ]);
    }

    /**
     * Suy ra cột "Kết quả" (hiển thị cạnh cột trạng thái).
     * - 'da_xong' → "Đã xong"
     * - trang_thai_khach = 'huy' → "Đã huỷ"
     * - còn lại → null (rỗng)
     */
    private function ketQuaBooking(Booking $b): ?string
    {
        if ($b->trang_thai === 'da_xong') return 'Đã xong';
        if (($b->trang_thai_khach ?? null) === 'huy') return 'Đã huỷ';
        return null;
    }
}
