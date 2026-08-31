<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiAuditLog;
use App\Models\Booking;
use App\Models\BookingBinhLuan;
use Illuminate\Http\JsonResponse;

/**
 * Inspect endpoints — trả state đầy đủ của 1 entity trong 1 call.
 * Mục tiêu: gửi id + link, có API check ngay không phải db:fresh online.
 */
class InspectController extends BaseV1Controller
{
    public function booking(int $id): JsonResponse
    {
        $b = Booking::with(['khachHang', 'coSo', 'phong', 'bacSi', 'dichVu'])->find($id);
        if (! $b) return response()->json(['message' => "Booking#{$id} không tồn tại"], 404);

        $comments = BookingBinhLuan::where('booking_id', $b->id)
            ->orderBy('id')->get(['id', 'noi_dung', 'user_id', 'created_at']);

        $recentAudit = ApiAuditLog::where('path', 'like', "%bookings/{$b->id}%")
            ->orderByDesc('id')->limit(10)->get(['id', 'method', 'path', 'response_status', 'created_at']);

        return $this->ok([
            'booking'      => $b,
            'khach_hang'   => $b->khachHang,
            'co_so'        => $b->coSo?->only(['id', 'slug', 'ten']),
            'phong'        => $b->phong?->only(['id', 'ten', 'kieu_phong', 'so_slot_toi_da']),
            'bac_si'       => $b->bacSi?->only(['id', 'ten', 'chuc_danh']),
            'dich_vu'      => $b->dichVu?->only(['id', 'ten', 'thuoc_nhom', 'thoi_gian_phut']),
            'tiep_don'     => [
                'user_id'      => $b->tiep_don_user_id,
                'trang_thai'   => $b->trang_thai_tiep_don,
                'bat_dau'      => $b->tiep_don_bat_dau,
                'hoan_tat'     => $b->tiep_don_hoan_tat,
            ],
            'checkin'      => [
                'tinh_trang'   => $b->tinh_trang_checkin,
                'ket_qua'      => $b->ket_qua_sau_checkin,
                'phan_loai'    => $b->phan_loai,
                'hoan_tat_at'  => $b->checkin_hoan_tat_at,
            ],
            'comments'      => $comments,
            'recent_audit'  => $recentAudit,
        ]);
    }
}
