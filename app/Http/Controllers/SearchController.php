<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use App\Models\LichHen;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Tìm lịch đặt theo tên / SĐT khách hàng (cả đặt phòng & lịch tư vấn).
     */
    public function index(CoSo $co_so, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $bookings = collect();
        $lichHens = collect();

        if ($q !== '') {
            $khIds = KhachHang::where('co_so_id', $co_so->id)
                ->where(function ($w) use ($q) {
                    $w->where('ho_ten', 'like', "%{$q}%")
                        ->orWhere('so_dien_thoai', 'like', "%{$q}%");
                })
                ->pluck('id');

            $bookings = Booking::where('co_so_id', $co_so->id)
                ->whereIn('khach_hang_id', $khIds)
                ->with(['khachHang', 'phong', 'khungGio', 'dichVu'])
                ->latest('ngay_dat')->latest('id')->limit(50)->get();

            $lichHens = LichHen::where('co_so_id', $co_so->id)
                ->whereIn('khach_hang_id', $khIds)
                ->with(['khachHang', 'bacSiTuVan', 'caKham'])
                ->latest('ngay_hen')->latest('id')->limit(50)->get();
        }

        return view('longevity.search', [
            'coSo' => $co_so,
            'q' => $q,
            'bookings' => $bookings,
            'lichHens' => $lichHens,
        ]);
    }

    /**
     * Xem chi tiết lịch tư vấn (chỉ đọc).
     */
    public function showLichHen(CoSo $co_so, LichHen $lich_hen)
    {
        abort_unless($lich_hen->co_so_id === $co_so->id, 404);

        $lich_hen->load(['khachHang', 'bacSiTuVan', 'caKham', 'sale']);

        return view('longevity.lich-hen.show', [
            'coSo' => $co_so,
            'lichHen' => $lich_hen,
        ]);
    }

    /**
     * Xem chi tiết đặt phòng (chỉ đọc).
     */
    public function showBooking(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);

        $booking->load(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'sale', 'menus']);

        return view('longevity.show', [
            'coSo' => $co_so,
            'booking' => $booking,
        ]);
    }
}
