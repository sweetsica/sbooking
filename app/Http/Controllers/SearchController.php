<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesByPhanQuyen;
use App\Models\Booking;
use App\Models\CoSo;
use App\Models\KhachHang;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use AuthorizesByPhanQuyen;

    /**
     * Tìm lịch đặt phòng theo tên / SĐT khách hàng.
     */
    public function index(CoSo $co_so, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $bookings = collect();

        if ($q !== '') {
            $khIds = KhachHang::where('co_so_id', $co_so->id)
                ->where(function ($w) use ($q) {
                    $w->where('ho_ten', 'like', "%{$q}%")
                        ->orWhere('so_dien_thoai', 'like', "%{$q}%");
                })
                ->pluck('id');

            $bkQuery = Booking::where('co_so_id', $co_so->id)
                ->whereIn('khach_hang_id', $khIds);
            $this->applyViewScope($bkQuery, $co_so);
            $bookings = $bkQuery
                ->with(['khachHang', 'phong', 'khungGio', 'dichVu'])
                ->latest('ngay_dat')->latest('id')->limit(50)->get();
        }

        return view('longevity.search', [
            'coSo' => $co_so,
            'q' => $q,
            'bookings' => $bookings,
            'lichHens' => collect(),
        ]);
    }

    /**
     * Xem chi tiết đặt phòng (chỉ đọc).
     */
    public function showBooking(CoSo $co_so, Booking $booking)
    {
        abort_unless($booking->co_so_id === $co_so->id, 404);

        $user = auth()->user();
        if ($user && ! $user->is_admin) {
            $checkQuery = Booking::where('id', $booking->id);
            if (! $this->applyViewScope($checkQuery)) {
                abort(403, 'Bạn không có quyền xem lịch này.');
            }
            abort_unless($checkQuery->exists(), 403, 'Bạn không có quyền xem lịch này.');
        }

        $booking->load(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'ktv', 'sale', 'menus',
            'phanHois.nguoiDung.vaiTro', 'phanHois.nguoiDung.phongBan']);

        return view('longevity.show', [
            'coSo' => $co_so,
            'booking' => $booking,
            'canDuyet' => $this->hasPerm('duyet_booking'),
            'canPhanHoi' => $this->hasPerm('ghi_chu_phan_hoi'),
        ]);
    }
}
