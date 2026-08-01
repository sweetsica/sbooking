<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\KhachHang;
use App\Notifications\LichNotification;
use App\Support\LichEvent;
use App\Services\NotificationRecipientResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

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

    /**
     * POST /api/bookings — nhận push từ Lara-SCRM tạo booking mới.
     * Phase C1.b (2026-08-01).
     *
     * Upsert khach_hang theo so_dien_thoai (tạo mới nếu chưa có). Tạo booking status=cho_duyet.
     * Không yêu cầu phong_id/khung_gio_id — lễ tân sbooking gán khi duyệt.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'so_dien_thoai'  => ['required', 'string', 'max:20'],
            'ho_ten'         => ['required', 'string', 'max:120'],
            'co_so_id'       => ['required', 'integer', 'exists:co_so,id'],
            'ngay_dat'       => ['required', 'date_format:Y-m-d'],
            'gio_thuc_hien'  => ['nullable', 'string'],
            'dich_vu_id'     => ['nullable', 'integer', 'exists:dich_vu,id'],
            'bac_si_id'      => ['nullable', 'integer', 'exists:bac_si,id'],
            'loai_dat_lich'  => ['nullable', 'in:phong_kham,dich_vu'],
            'nguon'          => ['nullable', 'string', 'max:60'],
            'crm_khach_ma'   => ['nullable', 'string', 'max:60'],
            'ghi_chu'        => ['nullable', 'string', 'max:2000'],
            'so_lieu_trinh'   => ['nullable', 'string', 'max:40'],
            'so_luong_lo'     => ['nullable', 'string', 'max:40'],
            'dung_tich_lo'    => ['nullable', 'string', 'max:40'],
            'ket_hop_medical' => ['nullable', 'boolean'],
        ]);

        try {
            $result = DB::transaction(function () use ($data) {
                $kh = KhachHang::firstOrCreate(
                    ['so_dien_thoai' => $data['so_dien_thoai']],
                    ['ho_ten' => $data['ho_ten'], 'co_so_id' => $data['co_so_id']]
                );

                $booking = Booking::create([
                    'co_so_id'      => $data['co_so_id'],
                    'khach_hang_id' => $kh->id,
                    'loai_dat_lich' => $data['loai_dat_lich'] ?? 'phong_kham',
                    'dich_vu_id'    => $data['dich_vu_id'] ?? null,
                    'bac_si_id'     => $data['bac_si_id'] ?? null,
                    'ngay_dat'      => $data['ngay_dat'],
                    'gio_thuc_hien' => $data['gio_thuc_hien'] ?? null,
                    'nguon'         => $data['nguon'] ?? 'SCRM',
                    'crm_khach_ma'  => $data['crm_khach_ma'] ?? null,
                    'ghi_chu'       => $data['ghi_chu'] ?? null,
                    'so_lieu_trinh'   => $data['so_lieu_trinh'] ?? null,
                    'so_luong_lo'     => $data['so_luong_lo'] ?? null,
                    'dung_tich_lo'    => $data['dung_tich_lo'] ?? null,
                    'ket_hop_medical' => $data['ket_hop_medical'] ?? false,
                    'trang_thai'    => 'cho_duyet',
                    'da_duyet'      => false,
                ]);

                return [$booking, $kh];
            });

            [$booking, $kh] = $result;
            $booking->refresh(); // pick up ma_booking sinh trong booted()

            // Phase C1.b rev10 2026-08-02: notify operators (Admin duyệt) khi có booking mới từ scrm push.
            try {
                $resolver = app(NotificationRecipientResolver::class);
                $recipients = $resolver->forBooking($booking->fresh(['khachHang', 'coSo']), LichEvent::TAO_MOI);
                if ($recipients->isNotEmpty()) {
                    Notification::send($recipients, new LichNotification($booking, LichEvent::TAO_MOI, 'Data Source (SCRM)'));
                }
            } catch (Throwable $ne) {
                Log::warning('Notify TAO_MOI api store failed: ' . $ne->getMessage(), ['booking_id' => $booking->id]);
            }

            return response()->json([
                'id'            => $booking->id,
                'ma_booking'    => $booking->ma_booking,
                'khach_hang_id' => $kh->id,
                'trang_thai'    => $booking->trang_thai,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Không tạo được booking',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
