<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Booking (lịch đặt) CRUD nâng cao + export theo period.
 * Song song /api/bookings cũ (BookingApiController) — không đụng cái đó.
 */
class BookingController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'phong_id', 'bac_si_id', 'sale_id', 'khach_hang_id',
        'trang_thai', 'trang_thai_khach', 'loai_dat_lich', 'nguon'];
    private const ALLOWED_SORT    = ['id', 'ngay_dat', 'gio_thuc_hien', 'created_at'];
    private const SEARCHABLE      = ['ma_booking', 'ghi_chu'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = Booking::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        if ($from = $req->input('from')) $q->whereDate('ngay_dat', '>=', $from);
        if ($to   = $req->input('to'))   $q->whereDate('ngay_dat', '<=', $to);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT, '-ngay_dat');
        return $this->paginate($q, $req);
    }

    public function show(Booking $booking): JsonResponse
    {
        return $this->ok($booking);
    }

    public function store(Request $req): JsonResponse
    {
        return $this->ok(Booking::create($this->validated($req)), 201);
    }

    public function update(Request $req, Booking $booking): JsonResponse
    {
        $booking->update($this->validated($req, $booking->id));
        return $this->ok($booking->fresh());
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $booking->id]]);
    }

    /**
     * GET /bookings/export?from=&to=&group=day|week|month|year&filter[co_so_id]=...
     * Trả JSON aggregate {period_key: count} + summary total.
     */
    public function export(Request $req): JsonResponse
    {
        $req->validate([
            'from'  => ['nullable', 'date'],
            'to'    => ['nullable', 'date', 'after_or_equal:from'],
            'group' => ['nullable', Rule::in(['day', 'week', 'month', 'year'])],
        ]);
        $group = $req->input('group', 'day');
        $fmt = match ($group) {
            'week'  => "DATE_FORMAT(ngay_dat, '%x-W%v')",
            'month' => "DATE_FORMAT(ngay_dat, '%Y-%m')",
            'year'  => "DATE_FORMAT(ngay_dat, '%Y')",
            default => "DATE_FORMAT(ngay_dat, '%Y-%m-%d')",
        };
        $q = Booking::query()->selectRaw("$fmt as period, count(*) as total");
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        if ($from = $req->input('from')) $q->whereDate('ngay_dat', '>=', $from);
        if ($to   = $req->input('to'))   $q->whereDate('ngay_dat', '<=', $to);
        $rows = $q->groupBy(DB::raw($fmt))->orderBy('period')->get();
        return response()->json([
            'data' => $rows,
            'meta' => [
                'group' => $group,
                'from'  => $req->input('from'),
                'to'    => $req->input('to'),
                'total' => $rows->sum('total'),
            ],
        ]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'co_so_id'       => [$ignoreId ? 'sometimes' : 'required', 'integer', Rule::exists('co_so', 'id')],
            'khach_hang_id'  => ['nullable', 'integer'],
            'loai_dat_lich'  => ['nullable', 'string', 'max:50'],
            'phong_id'       => ['nullable', 'integer', Rule::exists('phong', 'id')],
            'khung_gio_id'   => ['nullable', 'integer'],
            'dich_vu_id'     => ['nullable', 'integer', Rule::exists('dich_vu', 'id')],
            'bac_si_id'      => ['nullable', 'integer', Rule::exists('bac_si', 'id')],
            'sale_id'        => ['nullable', 'integer', Rule::exists('users', 'id')],
            'ngay_dat'       => [$ignoreId ? 'sometimes' : 'required', 'date'],
            'gio_thuc_hien'  => ['nullable', 'date_format:H:i:s'],
            'gio_ket_thuc'   => ['nullable', 'date_format:H:i:s'],
            'trang_thai'     => ['sometimes', 'string', 'max:50'],
            'trang_thai_khach' => ['nullable', 'string', 'max:50'],
            'ghi_chu'        => ['nullable', 'string'],
            'nguon'          => ['nullable', 'string', 'max:50'],
        ]);
    }
}
