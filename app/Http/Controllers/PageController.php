<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CoSo;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function rooms(CoSo $co_so, Request $request)
    {
        $danhSachCoSo = CoSo::where('active', true)->orderBy('id')->get();
        $date = $request->date('ngay') ?? now();
        $phongs = $co_so->phongs()->with('khungGios')->orderBy('id')->get();

        $bookingsByPhong = Booking::where('co_so_id', $co_so->id)
            ->whereDate('ngay_dat', $date)
            ->get()
            ->groupBy('phong_id');

        $roomData = $phongs->map(function ($phong) use ($bookingsByPhong, $date) {
            $bookings = $bookingsByPhong->get($phong->id, collect());
            $slots = $phong->khungGios;
            $beds = max(1, (int) $phong->so_slot_toi_da);
            $capacity = $slots->count() * $beds;
            $occupied = $bookings->count();
            $fill = $capacity > 0 ? (int) round($occupied / $capacity * 100) : 0;

            $bySlot = $bookings->groupBy('khung_gio_id');
            $slotStatus = $slots->map(function ($kg) use ($bySlot, $beds) {
                $count = ($bySlot[$kg->id] ?? collect())->count();
                if ($count >= $beds) return 'full';
                if ($count > 0) return 'partial';
                return 'empty';
            });

            $bedStatus = [];
            $currentSlot = $slots->first();
            if ($currentSlot) {
                $currentBookings = ($bySlot[$currentSlot->id] ?? collect())->count();
                for ($i = 0; $i < $beds; $i++) {
                    $bedStatus[] = $i < $currentBookings ? 'occupied' : 'available';
                }
            }

            return [
                'phong' => $phong,
                'beds' => $beds,
                'occupied' => $occupied,
                'fill' => $fill,
                'slotStatus' => $slotStatus,
                'bedStatus' => $bedStatus,
            ];
        });

        return view('longevity.rooms', [
            'coSo' => $co_so,
            'danhSachCoSo' => $danhSachCoSo,
            'roomData' => $roomData,
            'date' => $date,
        ]);
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

        return view('longevity.timeline', [
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

        return view('longevity.bookings', [
            'coSo' => $co_so,
            'bookings' => $bookings,
            'phongs' => $co_so->phongs()->get(),
            'nguons' => Booking::where('co_so_id', $co_so->id)
                ->whereNotNull('nguon')->distinct()->pluck('nguon'),
            'filters' => $request->only(['ngay_tu', 'ngay_den', 'phong_id', 'nguon']),
        ]);
    }
}
