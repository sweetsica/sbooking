<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function rooms(CoSo $co_so)
    {
        return view('yhct.rooms', ['coSo' => $co_so]);
    }

    public function timeline(CoSo $co_so, Request $request)
    {
        $rooms = $co_so->phongs()->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();

        $room = $rooms->firstWhere('id', (int) $request->query('phong_id'))
            ?? $rooms->firstWhere('trang_thai', 'hoat_dong')
            ?? $rooms->first();

        $slots = $room ? $room->khungGios()->orderBy('thu_tu')->get() : collect();
        $beds = $room ? max(1, (int) $room->so_slot_toi_da) : 0;

        $bookings = collect();
        if ($room) {
            $bookings = Booking::where('co_so_id', $co_so->id)
                ->where('phong_id', $room->id)
                ->whereDate('ngay_dat', $date)
                ->with(['khachHang', 'dichVu', 'bacSi', 'khungGio'])
                ->orderBy('id')->get();
        }

        // Xếp booking vào lưới: mỗi khung giờ -> các giường (lấp tuần tự)
        $bySlot = $bookings->groupBy('khung_gio_id');
        $grid = $slots->map(function ($k) use ($bySlot, $beds) {
            $items = ($bySlot[$k->id] ?? collect())->values();
            $row = [];
            for ($i = 0; $i < $beds; $i++) {
                $row[] = $items[$i] ?? null;
            }
            return ['slot' => $k, 'beds' => $row];
        });

        $total = $bookings->count();
        $approved = $bookings->where('trang_thai', 'da_duyet')->count();
        $capacity = max(1, $slots->count() * $beds);

        return view('yhct.timeline', [
            'coSo' => $co_so,
            'rooms' => $rooms,
            'room' => $room,
            'date' => $date,
            'beds' => $beds,
            'grid' => $grid,
            'stats' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $total - $approved,
                'fill' => (int) round($total / $capacity * 100),
            ],
        ]);
    }

    public function bookings(CoSo $co_so, Request $request)
    {
        $query = Booking::where('co_so_id', $co_so->id)
            ->with(['khachHang', 'phong', 'khungGio', 'dichVu', 'bacSi', 'sale'])
            ->latest('id');

        if ($request->filled('ngay_tu')) {
            $query->whereDate('ngay_dat', '>=', $request->query('ngay_tu'));
        }
        if ($request->filled('ngay_den')) {
            $query->whereDate('ngay_dat', '<=', $request->query('ngay_den'));
        }
        if ($request->filled('phong_id')) {
            $query->where('phong_id', $request->query('phong_id'));
        }
        if ($request->filled('nguon')) {
            $query->where('nguon', $request->query('nguon'));
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('yhct.bookings', [
            'coSo' => $co_so,
            'bookings' => $bookings,
            'phongs' => $co_so->phongs()->get(),
            'nguons' => Booking::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'phong_id', 'nguon']),
        ]);
    }
}
