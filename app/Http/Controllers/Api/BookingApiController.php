<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    /**
     * GET /api/bookings
     * Server-to-server cho Lara-SCRM pull dữ liệu.
     *
     * Query:
     *   co_so_id, khach_hang_id, trang_thai, trang_thai_khach, nguon,
     *   tu_ngay, den_ngay          (theo ngay_dat, YYYY-MM-DD)
     *   updated_since              (ISO datetime, dùng đồng bộ delta theo updated_at)
     *   per_page (mặc định 100, tối đa 500)
     *   page
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'co_so_id'         => ['sometimes', 'integer'],
            'khach_hang_id'    => ['sometimes', 'integer'],
            'so_dien_thoai'    => ['sometimes', 'string', 'max:20'],
            'trang_thai'       => ['sometimes', 'string'],
            'trang_thai_khach' => ['sometimes', 'string'],
            'nguon'            => ['sometimes', 'string'],
            'tu_ngay'          => ['sometimes', 'date_format:Y-m-d'],
            'den_ngay'         => ['sometimes', 'date_format:Y-m-d'],
            'updated_since'    => ['sometimes', 'date'],
            'per_page'         => ['sometimes', 'integer', 'min:1', 'max:500'],
        ]);

        $q = Booking::query()
            ->with([
                'coSo:id,ten,slug',
                'khachHang:id,co_so_id,ho_ten,so_dien_thoai,email',
                'phong:id,ten',
                'khungGio:id,gio_bat_dau,gio_ket_thuc',
                'dichVu:id,ten',
                'bacSi:id,ten',
                'ktv:id,name',
                'sale:id,name',
                'nguoiTao:id,name',
            ]);

        foreach (['co_so_id', 'khach_hang_id', 'trang_thai', 'trang_thai_khach', 'nguon'] as $f) {
            if (isset($data[$f])) {
                $q->where($f, $data[$f]);
            }
        }

        if (isset($data['so_dien_thoai'])) {
            $q->whereHas('khachHang', fn ($kh) => $kh->where('so_dien_thoai', $data['so_dien_thoai']));
        }

        if (isset($data['tu_ngay'])) {
            $q->whereDate('ngay_dat', '>=', $data['tu_ngay']);
        }
        if (isset($data['den_ngay'])) {
            $q->whereDate('ngay_dat', '<=', $data['den_ngay']);
        }
        if (isset($data['updated_since'])) {
            $q->where('updated_at', '>=', $data['updated_since']);
        }

        $q->orderBy('updated_at', 'desc');

        $perPage = (int) ($data['per_page'] ?? 100);
        $page = $q->paginate($perPage);

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ],
        ]);
    }
}
