<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DichVu;
use App\Models\KhungGio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase C1 (2026-08-01) — endpoint sync data booking (dich_vu / co_so / bac_si) sang scrm.
 * Sync 1 chiều sbooking → scrm. Middleware `scrm.token` verify bearer.
 */
class SyncApiController extends Controller
{
    public function dichVu(): JsonResponse
    {
        $rows = DichVu::query()
            ->select(['id', 'co_so_id', 'ten', 'thoi_gian_phut', 'thuoc_nhom', 'la_dich_vu', 'active', 'updated_at'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'count' => $rows->count(),
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/sync/khung-gio?co_so_id=X
     * Phase C1.b rev3 (2026-08-01) — trả khung giờ khả dụng của cơ sở (union tất cả phòng active).
     * Scrm dùng làm dropdown chọn giờ trong form Thêm booking (thay hardcode 8:30-12/13:30-18).
     */
    public function khungGio(Request $request): JsonResponse
    {
        $data = $request->validate([
            'co_so_id' => ['required', 'integer', 'exists:co_so,id'],
        ]);

        // Chỉ lấy các giờ bắt đầu (distinct), loại khung <30' (data test có nhiều slot 5-10'). Scrm chỉ cần giờ start.
        $slots = KhungGio::query()
            ->select('gio_bat_dau')
            ->join('phong', 'phong.id', '=', 'khung_gio.phong_id')
            ->where('phong.co_so_id', $data['co_so_id'])
            ->where('phong.trang_thai', 'hoat_dong')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, gio_bat_dau, gio_ket_thuc) >= 30')
            ->distinct()
            ->orderBy('gio_bat_dau')
            ->get()
            ->map(fn ($r) => [
                'gio_bat_dau' => substr($r->gio_bat_dau, 0, 5),
                'label' => substr($r->gio_bat_dau, 0, 5),
            ])
            ->values();

        return response()->json([
            'co_so_id' => (int) $data['co_so_id'],
            'count' => $slots->count(),
            'slots' => $slots,
        ]);
    }
}
