<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BookingCapacityChecks;
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
    use BookingCapacityChecks;

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
    /**
     * 2026-08-19 — pre-flight check (dry-run) cho SCRM lead-form.
     * Chạy đúng ràng buộc trong store() (room capacity + BS trùng giờ) nhưng KHÔNG insert.
     * Trả 200 {ok: true} nếu qua, 409/422 giữ nguyên message như store để hiển thị inline.
     */
    public function preflight(Request $request): JsonResponse
    {
        $data = $request->validate([
            'co_so_id'      => ['required', 'integer', 'exists:co_so,id'],
            'ngay_dat'      => ['required', 'date_format:Y-m-d'],
            'gio_thuc_hien' => ['nullable', 'string'],
            'gio_ket_thuc'  => ['nullable', 'string'],
            'dich_vu_id'    => ['nullable', 'integer', 'exists:dich_vu,id'],
            'bac_si_id'     => ['nullable', 'integer', 'exists:bac_si,id'],
            'phong_id'      => ['nullable', 'integer', 'exists:phong,id'],
            'khung_gio_id'  => ['nullable', 'integer', 'exists:khung_gio,id'],
        ]);

        if (! empty($data['phong_id']) && ! empty($data['gio_thuc_hien'])) {
            $phong = \App\Models\Phong::find($data['phong_id']);
            if ($phong) {
                $capacity = max(1, (int) $phong->so_slot_toi_da);
                $gio = substr($data['gio_thuc_hien'], 0, 5);
                $count = Booking::where('phong_id', $phong->id)
                    ->whereDate('ngay_dat', $data['ngay_dat'])
                    ->where('gio_thuc_hien', 'LIKE', $gio . '%')
                    ->giuCho()
                    ->count();
                if ($count >= $capacity) {
                    return response()->json([
                        'message' => "Phòng {$phong->ten} đã đầy ({$count}/{$capacity}) tại {$gio} ngày {$data['ngay_dat']} — chọn giờ khác hoặc phòng khác.",
                        'error'   => 'room_full',
                    ], 409);
                }
            }
        }

        if (! empty($data['bac_si_id']) && ! empty($data['dich_vu_id']) && ! empty($data['khung_gio_id'])) {
            $err = $this->bccCheckBacSiCapacity(
                (int) $data['bac_si_id'],
                (int) $data['khung_gio_id'],
                (int) $data['dich_vu_id'],
                $data['ngay_dat'],
                null,
                $data['gio_thuc_hien'] ?? null,
                $data['gio_ket_thuc'] ?? null,
            );
            if ($err) {
                return response()->json(['message' => $err, 'error' => 'bs_capacity'], 422);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'so_dien_thoai'  => ['required', 'string', 'max:20'],
            'ho_ten'         => ['required', 'string', 'max:120'],
            'co_so_id'       => ['required', 'integer', 'exists:co_so,id'],
            'ngay_dat'       => ['required', 'date_format:Y-m-d'],
            'gio_thuc_hien'  => ['nullable', 'string'],
            'gio_ket_thuc'   => ['nullable', 'string'], // Phase 6.25.C fix
            'dich_vu_id'     => ['nullable', 'integer', 'exists:dich_vu,id'],
            'bac_si_id'      => ['nullable', 'integer', 'exists:bac_si,id'],
            'phong_id'       => ['nullable', 'integer', 'exists:phong,id'],
            'khung_gio_id'   => ['nullable', 'integer', 'exists:khung_gio,id'],
            // 2026-08-19 Phase B: 'phong_kham' giữ hỗ trợ backward-compat, sẽ map thành 'kham_ls'.
            'loai_dat_lich'  => ['nullable', 'in:phong_kham,kham_ls,tu_van,dich_vu'],
            'nguon'          => ['nullable', 'string', 'max:60'],
            'crm_khach_ma'   => ['nullable', 'string', 'max:60'],
            'ghi_chu'        => ['nullable', 'string', 'max:2000'],
            // 2026-08-07: đổi so_lieu_trinh + bỏ so_luong_lo/dung_tich_lo.
            'so_luong'        => ['nullable', 'integer', 'min:1'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'co_tu_van'       => ['nullable', 'boolean'],
            'co_kham_cls'     => ['nullable', 'boolean'],
            'sale_id'          => ['nullable', 'integer', 'exists:users,id'],
            'tiep_don_user_id' => ['nullable', 'integer', 'exists:users,id'],
            // 2026-08-18: nguoi_tao_id = sale gốc (creator) — CRM push sang để modal Duyệt lock dropdown
            // khi source ∈ SA/BA/MKT_BR.
            'nguoi_tao_id'     => ['nullable', 'integer', 'exists:users,id'],
            // 2026-08-18: tele_owner_id/name = Tele phụ trách phase 2 SCRM (lead.owner sau CM chia).
            // Snapshot để modal Duyệt hiển thị — không FK vì user thuộc SCRM (không map cứng sang sbooking).
            'tele_owner_id'    => ['nullable', 'integer'],
            'tele_owner_name'  => ['nullable', 'string', 'max:150'],
        ]);

        // Phase C1.d 2026-08-02: capacity guard sớm ngay tại API — không cho tạo trùng slot
        // của phòng dịch vụ. Đơn cho_duyet + da_duyet đã chiếm slot; đơn tu_choi loại trừ.
        if (! empty($data['phong_id']) && ! empty($data['gio_thuc_hien'])) {
            $phong = \App\Models\Phong::find($data['phong_id']);
            if ($phong) {
                $capacity = max(1, (int) $phong->so_slot_toi_da);
                $gio = substr($data['gio_thuc_hien'], 0, 5);
                $count = Booking::where('phong_id', $phong->id)
                    ->whereDate('ngay_dat', $data['ngay_dat'])
                    ->where('gio_thuc_hien', 'LIKE', $gio . '%')
                    ->giuCho()
                    ->count();
                if ($count >= $capacity) {
                    return response()->json([
                        'message' => "Phòng {$phong->ten} đã đầy ({$count}/{$capacity}) tại {$gio} ngày {$data['ngay_dat']} — chọn giờ khác hoặc phòng khác.",
                        'error'   => 'room_full',
                    ], 409);
                }
            }
        }

        // 2026-08-10: capacity guard BS+DV+khung — trước đây chỉ check ở duyet() bên web,
        // SCRM tạo booking không biết → admin duyệt bên sbooking mới bị chặn (UX kém).
        // Check tại đây: nếu fail → 422 với message, SCRM markFailed(sync_error) và show cho user.
        if (! empty($data['bac_si_id']) && ! empty($data['dich_vu_id']) && ! empty($data['khung_gio_id'])) {
            $err = $this->bccCheckBacSiCapacity(
                (int) $data['bac_si_id'],
                (int) $data['khung_gio_id'],
                (int) $data['dich_vu_id'],
                $data['ngay_dat'],
                null,
                $data['gio_thuc_hien'] ?? null,
                $data['gio_ket_thuc'] ?? null,
            );
            if ($err) {
                return response()->json([
                    'message' => $err,
                    'error'   => 'bs_capacity',
                ], 422);
            }
        }

        try {
            $result = DB::transaction(function () use ($data) {
                $kh = KhachHang::firstOrCreate(
                    ['so_dien_thoai' => $data['so_dien_thoai']],
                    ['ho_ten' => $data['ho_ten'], 'co_so_id' => $data['co_so_id']]
                );

                // 2026-08-16: Mọi booking mới đều chờ Admin vận hành sbooking duyệt (dù nguồn nào, loại nào).
                // 2026-08-19 Phase B: 3 loại chuẩn — 'kham_ls' | 'tu_van' | 'dich_vu'.
                //   Backward-compat: 'phong_kham' cũ → 'kham_ls' (default), 'tu_van' giữ nguyên.
                $loaiRaw = $data['loai_dat_lich'] ?? 'kham_ls';
                $loaiDatLich = $loaiRaw === 'phong_kham' ? 'kham_ls' : $loaiRaw;
                $autoDuyet = false;

                $booking = Booking::create([
                    'co_so_id'      => $data['co_so_id'],
                    'khach_hang_id' => $kh->id,
                    'loai_dat_lich' => $loaiDatLich,
                    'dich_vu_id'    => $data['dich_vu_id'] ?? null,
                    'bac_si_id'     => $data['bac_si_id'] ?? null,
                    'phong_id'      => $data['phong_id'] ?? null,
                    'khung_gio_id'  => $data['khung_gio_id'] ?? null,
                    'ngay_dat'      => $data['ngay_dat'],
                    'gio_thuc_hien' => $data['gio_thuc_hien'] ?? null,
                    'gio_ket_thuc'  => $data['gio_ket_thuc'] ?? null,
                    'nguon'         => $data['nguon'] ?? 'SCRM',
                    'crm_khach_ma'  => $data['crm_khach_ma'] ?? null,
                    'ghi_chu'       => $data['ghi_chu'] ?? null,
                    'so_luong'        => $data['so_luong'] ?? null,
                    'ket_hop_medical' => $data['ket_hop_medical'] ?? false,
                    'co_tu_van'       => $data['co_tu_van'] ?? false,
                    'co_kham_cls'     => $data['co_kham_cls'] ?? false,
                    'sale_id'         => $data['sale_id'] ?? null,
                    'tiep_don_user_id' => $data['tiep_don_user_id'] ?? null,
                    // 2026-08-18: nguoi_tao_id = sale gốc từ CRM push, fallback auth() (booking tạo tay bên sbooking).
                    'nguoi_tao_id'    => $data['nguoi_tao_id'] ?? auth()->id(),
                    // 2026-08-18: Tele phụ trách phase 2 SCRM (owner sau CM chia) — snapshot cho modal Duyệt.
                    'tele_owner_id'   => $data['tele_owner_id'] ?? null,
                    'tele_owner_name' => $data['tele_owner_name'] ?? null,
                    'trang_thai'    => $autoDuyet ? 'da_duyet' : 'cho_duyet',
                    'da_duyet'      => $autoDuyet,
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

    /**
     * PUT /api/bookings/{booking} — Phase C1.e (2026-08-02).
     * Nhận edit từ scrm: note, sale, ngay/gio, dich_vu, bac_si, phong, so_luong/medical.
     * Nếu đổi slot (ngay+gio) → re-check capacity phòng. Nếu conflict → 409.
     */
    public function update(Booking $booking, Request $request): JsonResponse
    {
        $data = $request->validate([
            'ghi_chu'         => ['nullable', 'string', 'max:2000'],
            'sale_id'         => ['nullable', 'integer', 'exists:users,id'],
            'ngay_dat'        => ['nullable', 'date_format:Y-m-d'],
            'gio_thuc_hien'   => ['nullable', 'string'],
            'dich_vu_id'      => ['nullable', 'integer', 'exists:dich_vu,id'],
            'bac_si_id'       => ['nullable', 'integer', 'exists:bac_si,id'],
            'phong_id'        => ['nullable', 'integer', 'exists:phong,id'],
            'khung_gio_id'    => ['nullable', 'integer', 'exists:khung_gio,id'],
            'tiep_don_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'gio_ket_thuc'    => ['nullable', 'string'],
            // 2026-08-07: đổi so_lieu_trinh + bỏ so_luong_lo/dung_tich_lo.
            'so_luong'        => ['nullable', 'integer', 'min:1'],
            'ket_hop_medical' => ['nullable', 'boolean'],
            'co_tu_van'       => ['nullable', 'boolean'],
            'co_kham_cls'     => ['nullable', 'boolean'],
            // B5/2026-08-15: SCRM có thể push huỷ (auto-cancel 15' khách trễ).
            'trang_thai'      => ['nullable', 'in:huy'],
            'ly_do_huy'       => ['nullable', 'string', 'max:500'],
            // 2026-08-18: cập nhật Tele phụ trách khi CM đổi owner phase 2.
            'tele_owner_id'   => ['nullable', 'integer'],
            'tele_owner_name' => ['nullable', 'string', 'max:150'],
        ]);

        // Capacity guard nếu slot thay đổi (ngay/gio/phong).
        $newPhongId = $data['phong_id'] ?? $booking->phong_id;
        $newNgay = $data['ngay_dat'] ?? optional($booking->ngay_dat)->toDateString();
        $newGio = $data['gio_thuc_hien'] ?? $booking->gio_thuc_hien;
        $slotChanged = ($newPhongId != $booking->phong_id)
            || ($newNgay != optional($booking->ngay_dat)->toDateString())
            || ($newGio != $booking->gio_thuc_hien);

        if ($slotChanged && $newPhongId && $newGio) {
            $phong = \App\Models\Phong::find($newPhongId);
            if ($phong) {
                $capacity = max(1, (int) $phong->so_slot_toi_da);
                $gio = substr($newGio, 0, 5);
                $count = Booking::where('phong_id', $phong->id)
                    ->whereDate('ngay_dat', $newNgay)
                    ->where('gio_thuc_hien', 'LIKE', $gio . '%')
                    ->where('id', '!=', $booking->id)
                    ->giuCho()
                    ->count();
                if ($count >= $capacity) {
                    return response()->json([
                        'message' => "Phòng {$phong->ten} đã đầy ({$count}/{$capacity}) tại {$gio} ngày {$newNgay} — chọn giờ khác hoặc phòng khác.",
                        'error'   => 'room_full',
                    ], 409);
                }
            }
        }

        // B5/2026-08-15: map ly_do_huy → ly_do_tu_choi (dùng chung cột lý do) khi hủy.
        if (($data['trang_thai'] ?? null) === 'huy' && ! empty($data['ly_do_huy'])) {
            $data['ly_do_tu_choi'] = 'Auto-hủy 15\': ' . $data['ly_do_huy'];
        }
        unset($data['ly_do_huy']);

        $booking->fill(array_filter($data, fn ($v) => $v !== null));
        $booking->save();

        return response()->json([
            'id'         => $booking->id,
            'ma_booking' => $booking->ma_booking,
            'trang_thai' => $booking->trang_thai,
            'updated_at' => $booking->updated_at,
        ]);
    }

    /**
     * POST /api/bookings/{booking}/comments — Phase C1.f (2026-08-02).
     * Nhận bình luận từ scrm, tạo BookingBinhLuan. Không notify (scrm là caller, đã có UI).
     * user_id resolve từ payload sbooking_user_id (đã map ở scrm.users.sbooking_user_id).
     */
    public function comment(\App\Models\Booking $booking, Request $request): JsonResponse
    {
        $data = $request->validate([
            'noi_dung' => ['required', 'string', 'max:2000'],
            'sbooking_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'scrm_user_name' => ['nullable', 'string', 'max:120'],
        ]);

        $bl = $booking->binhLuans()->create([
            'user_id'  => $data['sbooking_user_id'] ?? null,
            'noi_dung' => ($data['scrm_user_name'] ? '[Hệ thống Data · ' . $data['scrm_user_name'] . '] ' : '[Hệ thống Data] ') . $data['noi_dung'],
        ]);

        return response()->json([
            'id' => $bl->id,
            'booking_id' => $bl->booking_id,
            'created_at' => $bl->created_at,
        ], 201);
    }
}
