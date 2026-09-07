<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\LichHen;
use App\Models\User;
use App\Services\TongHopLichDatImporter;
use App\Services\TongHopLichDatSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $slug = $co_so->slug;
        foreach ($bookings as $b) {
            $la_dv = $b->loai_dat_lich === 'dich_vu';
            $rows->push((object) [
                'id'           => $b->id,
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
                'bulk_key'     => "booking:{$b->id}",
                'url_show'     => "/{$slug}/xem-dat-phong/{$b->id}",
                'url_edit'     => "/{$slug}/sua-dat-phong/{$b->id}",
                'url_destroy'  => "/{$slug}/xoa-dat-phong/{$b->id}",
            ]);
        }

        foreach ($lichHens as $l) {
            $rows->push((object) [
                'id'           => $l->id,
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
                'bulk_key'     => "lichhen:{$l->id}",
                'url_show'     => "/{$slug}/xem-tu-van/{$l->id}",
                'url_edit'     => "/{$slug}/sua-tu-van/{$l->id}",
                'url_destroy'  => "/{$slug}/xoa-tu-van/{$l->id}",
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

    /**
     * POST /thiet-lap/tong-hop-lich-dat/xoa-hang-loat — xoá bulk theo checkbox.
     * Input: items[] mỗi phần tử "booking:123" hoặc "lichhen:45".
     */
    public function xoaHangLoat(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'items'   => ['required', 'array', 'min:1', 'max:500'],
            'items.*' => ['string', 'regex:/^(booking|lichhen):\d+$/'],
        ]);

        $bookingIds = [];
        $lichhenIds = [];
        foreach ($data['items'] as $it) {
            [$type, $id] = explode(':', $it, 2);
            if ($type === 'booking') $bookingIds[] = (int) $id;
            else $lichhenIds[] = (int) $id;
        }

        $delB = $bookingIds
            ? Booking::where('co_so_id', $co_so->id)->whereIn('id', $bookingIds)->delete()
            : 0;
        $delL = $lichhenIds
            ? LichHen::where('co_so_id', $co_so->id)->whereIn('id', $lichhenIds)->delete()
            : 0;

        return back()->with('import_ok', "Đã xoá {$delB} booking + {$delL} tư vấn.");
    }

    /**
     * GET /thiet-lap/tong-hop-lich-dat/mau — tải template xlsx.
     */
    public function mau(CoSo $co_so, TongHopLichDatSheet $sheet): BinaryFileResponse
    {
        $path = $sheet->buildTemplate($co_so);
        $name = 'mau-tong-hop-lich-dat-'.$co_so->slug.'-'.now()->format('Ymd-His').'.xlsx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    /**
     * POST /thiet-lap/tong-hop-lich-dat/nhap — upload + validate + (nếu OK) insert.
     * Fail-fast: 1 dòng lỗi → không insert dòng nào, trả file lỗi để user tải + sửa.
     */
    public function nhap(CoSo $co_so, Request $request, TongHopLichDatSheet $sheet, TongHopLichDatImporter $importer)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $upload = $request->file('file');
        // Lưu bản gốc vào tạm để buildErrorFile giữ nguyên định dạng header/format.
        $srcPath = $upload->getRealPath();
        [$rows, $headerMap] = $sheet->parseUpload($srcPath);

        if (empty($rows)) {
            return back()->with('import_error', 'File rỗng hoặc không có dòng dữ liệu nào.');
        }

        $result = $importer->validate($co_so, $rows);

        if (! empty($result['errors'])) {
            // Sinh file lỗi + đưa vào storage tạm, redirect kèm URL tải.
            $errPath = $sheet->buildErrorFile($srcPath, $result['errors']);
            $token = bin2hex(random_bytes(8));
            $stored = 'tonghop-loi/'.$token.'.xlsx';
            Storage::disk('local')->put($stored, file_get_contents($errPath));
            @unlink($errPath);

            return back()->with('import_error', 'Import bị chặn — ' . count($result['errors']) . ' dòng lỗi. Không dòng nào được ghi. Tải file lỗi về sửa rồi thử lại.')
                ->with('import_error_token', $token)
                ->with('import_total_rows', count($rows));
        }

        $inserted = $importer->commit($co_so, $result['valid'], (int) auth()->id());
        return back()->with('import_ok', "Đã nhập {$inserted} lịch vào cơ sở {$co_so->ten}.");
    }

    /**
     * GET /thiet-lap/tong-hop-lich-dat/nhap/loi/{token} — tải file lỗi đã sinh.
     */
    public function taiFileLoi(CoSo $co_so, string $token): BinaryFileResponse
    {
        abort_unless(preg_match('/^[a-f0-9]{16}$/', $token), 404);
        $stored = 'tonghop-loi/'.$token.'.xlsx';
        abort_unless(Storage::disk('local')->exists($stored), 404);
        $abs = Storage::disk('local')->path($stored);
        $name = 'loi-tong-hop-lich-dat-'.$co_so->slug.'-'.now()->format('Ymd-His').'.xlsx';
        return response()->download($abs, $name);
    }
}
