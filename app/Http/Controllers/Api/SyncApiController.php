<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BacSi;
use App\Models\Booking;
use App\Models\DichVu;
use App\Models\KhungGio;
use App\Models\Phong;
use App\Models\User;

// Note: Booking::giuCho() scope trả các đơn "giữ chỗ" (cho_duyet + da_duyet, loại tu_choi).
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase C1 (2026-08-01) — endpoint sync data booking sang scrm.
 * Sync 1 chiều sbooking → scrm. Middleware `scrm.token` verify bearer.
 *
 * Phase C1.d (2026-08-02) — thêm bac_si, phong, khung-gio per phong, để scrm
 * chọn phòng + BS + kiểm tra capacity trực tiếp trong form.
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
            'data'  => $rows,
        ]);
    }

    /**
     * GET /api/sync/users
     * Trả danh sách user sbooking để scrm map (Phase C1.e — sync note/CV 2 chiều).
     */
    public function users(): JsonResponse
    {
        $rows = User::query()
            ->with('vaiTro:id,ma,ten')
            ->select(['id', 'name', 'chuc_danh', 'username', 'email', 'co_so_id', 'phong_ban_id', 'vai_tro_id', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id, 'name' => $u->name, 'chuc_danh' => $u->chuc_danh,
                'username' => $u->username, 'email' => $u->email,
                'co_so_id' => $u->co_so_id, 'phong_ban_id' => $u->phong_ban_id,
                'vai_tro_id' => $u->vai_tro_id,
                'vai_tro_ma' => $u->vaiTro?->ma,
                'vai_tro_ten' => $u->vaiTro?->ten,
                'updated_at' => $u->updated_at,
            ]);

        return response()->json([
            'count' => $rows->count(),
            'data'  => $rows,
        ]);
    }

    /**
     * GET /api/sync/bac-si
     * Trả danh sách toàn bộ bác sĩ + config nhận tư vấn / khám lâm sàng.
     */
    public function bacSi(): JsonResponse
    {
        $rows = BacSi::query()
            ->select([
                'id', 'co_so_id', 'ten', 'chuc_danh', 'active',
                'xuat_hien_moi_co_so', 'nhan_tu_van', 'phut_tu_van',
                'nhan_kham_ls', 'phut_kham_ls',
                'gio_bat_dau', 'gio_ket_thuc', 'updated_at',
            ])
            ->orderBy('id')
            ->get();

        return response()->json([
            'count' => $rows->count(),
            'data'  => $rows,
        ]);
    }

    /**
     * GET /api/sync/phong?co_so_id=X[&ngay=YYYY-MM-DD&gio=HH:MM]
     *
     * Trả danh sách phòng của cơ sở. Nếu có ngay+gio → tính luôn trạng thái
     * (số slot đã đặt / tối đa) để scrm hiển thị "còn N / M" hoặc "đã full".
     */
    public function phong(Request $request): JsonResponse
    {
        $data = $request->validate([
            'co_so_id' => ['required', 'integer', 'exists:co_so,id'],
            'ngay'     => ['nullable', 'date_format:Y-m-d'],
            'gio'      => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $rooms = Phong::query()
            ->where('co_so_id', $data['co_so_id'])
            ->where('trang_thai', 'hoat_dong')
            ->orderBy('ten')
            ->get(['id', 'co_so_id', 'ten', 'loai', 'kieu_phong', 'so_slot_toi_da', 'phut_moi_khach', 'trang_thai', 'updated_at']);

        $withStatus = ! empty($data['ngay']) && ! empty($data['gio']);
        $result = $rooms->map(function (Phong $p) use ($data, $withStatus) {
            $row = [
                'id'              => $p->id,
                'co_so_id'        => $p->co_so_id,
                'ten'             => $p->ten,
                'loai'            => $p->loai,
                'kieu_phong'      => $p->kieu_phong,
                'so_slot_toi_da'  => (int) $p->so_slot_toi_da,
                'phut_moi_khach'  => (int) $p->phut_moi_khach,
                'trang_thai'      => $p->trang_thai,
                'updated_at'      => $p->updated_at,
            ];

            if ($withStatus) {
                $used = Booking::where('phong_id', $p->id)
                    ->whereDate('ngay_dat', $data['ngay'])
                    ->where(function ($q) use ($data) {
                        // Booking đè giờ chọn: gio_thuc_hien = gio (đơn giản hoá — check start-time match).
                        // Đơn chưa gán phong_id (từ SCRM) không tính vì phong_id null.
                        $q->where('gio_thuc_hien', 'like', $data['gio'] . '%');
                    })
                    ->giuCho()
                    ->count();

                $capacity = max(1, (int) $p->so_slot_toi_da);
                $row['booked']   = $used;
                $row['capacity'] = $capacity;
                $row['full']     = $used >= $capacity;
            }

            return $row;
        });

        return response()->json([
            'co_so_id' => (int) $data['co_so_id'],
            'count'    => $rooms->count(),
            'data'     => $result,
        ]);
    }

    /**
     * GET /api/sync/khung-gio?phong_id=X&dich_vu_id=Y[&ngay=YYYY-MM-DD]
     *
     * Trả slot bookable của phòng, subdivided theo thời lượng dịch vụ:
     *  - Phòng dịch vụ: dùng phut_moi_khach của phòng.
     *  - Phòng khám: dùng thoi_gian_phut của dich_vu (5' khám lâm sàng, 30' tư vấn, …).
     * Logic gộp/chia giống BookingController::khungGio. Bỏ qua giờ nghỉ trưa 12:00–13:30.
     * Đánh dấu slot đã đầy (full=true) khi phòng dịch vụ hết capacity.
     *
     * Fallback:
     *  - Không có dich_vu_id → dùng khung_gio gốc của phòng (mỗi khung 1 slot).
     *  - Có co_so_id (compat cũ) → distinct start-time union tất cả phòng active.
     */
    public function khungGio(Request $request): JsonResponse
    {
        $data = $request->validate([
            'co_so_id'    => ['nullable', 'integer', 'exists:co_so,id'],
            'phong_id'    => ['nullable', 'integer', 'exists:phong,id'],
            'dich_vu_id'  => ['nullable', 'integer', 'exists:dich_vu,id'],
            'ngay'        => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (empty($data['co_so_id']) && empty($data['phong_id'])) {
            return response()->json(['message' => 'Thiếu co_so_id hoặc phong_id'], 422);
        }

        if (! empty($data['phong_id'])) {
            $phong = Phong::with('khungGios')->find($data['phong_id']);
            if (! $phong || $phong->khungGios->isEmpty()) {
                return response()->json(['phong_id' => (int) $data['phong_id'], 'phut_moi' => null, 'count' => 0, 'slots' => []]);
            }

            $ngayStr = $data['ngay'] ?? now()->format('Y-m-d');
            $capacity = max(1, (int) $phong->so_slot_toi_da);
            $dv = ! empty($data['dich_vu_id']) ? DichVu::find($data['dich_vu_id']) : null;

            $phutMoi = null;
            if ($phong->kieu_phong === 'phong_dich_vu' && $phong->phut_moi_khach) {
                $phutMoi = (int) $phong->phut_moi_khach;
            } elseif ($dv && $dv->thoi_gian_phut) {
                $phutMoi = (int) $dv->thoi_gian_phut;
            }

            $toMin = fn (string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
            $khungs = $phong->khungGios->sortBy('thu_tu')->values();

            // Chưa có phutMoi → trả khung gốc.
            if (! $phutMoi) {
                $slots = $khungs->map(fn ($k) => [
                    'id'           => $k->id,
                    'gio_bat_dau'  => substr($k->gio_bat_dau, 0, 5),
                    'gio_ket_thuc' => substr($k->gio_ket_thuc, 0, 5),
                    'label'        => substr($k->gio_bat_dau, 0, 5) . '–' . substr($k->gio_ket_thuc, 0, 5),
                    'full'         => false,
                ])->values();
                return response()->json([
                    'phong_id' => (int) $data['phong_id'],
                    'phut_moi' => null,
                    'count'    => $slots->count(),
                    'slots'    => $slots,
                ]);
            }

            // Subdivide: mỗi slot dài $phutMoi phút, bước từ open đến close của phòng, gán khung_gio_id chứa mốc bắt đầu.
            $khungChua = function (int $t) use ($khungs, $toMin) {
                $found = null;
                foreach ($khungs as $k) {
                    if ($toMin(substr($k->gio_bat_dau, 0, 5)) <= $t) $found = $k;
                    else break;
                }
                return $found ?? $khungs->first();
            };

            $open  = $toMin(substr($khungs->first()->gio_bat_dau, 0, 5));
            $close = $toMin(substr($khungs->last()->gio_ket_thuc, 0, 5));
            $chamTrua = fn (int $s, int $e) => $s < 13 * 60 + 30 && $e > 12 * 60;

            $slots = [];
            for ($t = $open; $t + $phutMoi <= $close; $t += $phutMoi) {
                $s = $t; $e = $t + $phutMoi;
                if ($chamTrua($s, $e)) continue;

                $kg = $khungChua($t);
                $full = false;
                if ($phong->kieu_phong === 'phong_dich_vu') {
                    // Đếm booking trùng khoảng giờ trong ngày, phòng này. Chỉ giữ chỗ.
                    $count = Booking::where('phong_id', $phong->id)
                        ->whereDate('ngay_dat', $ngayStr)
                        ->giuCho()
                        ->where(function ($q) use ($s, $e) {
                            $sStr = sprintf('%02d:%02d:00', intdiv($s, 60), $s % 60);
                            $eStr = sprintf('%02d:%02d:00', intdiv($e, 60), $e % 60);
                            $q->where('gio_thuc_hien', '<', $eStr)
                              ->where('gio_ket_thuc', '>', $sStr);
                        })
                        ->count();
                    $full = $count >= $capacity;
                }

                $slots[] = [
                    'id'           => $kg->id,
                    'gio_bat_dau'  => sprintf('%02d:%02d', intdiv($s, 60), $s % 60),
                    'gio_ket_thuc' => sprintf('%02d:%02d', intdiv($e, 60), $e % 60),
                    'label'        => sprintf('%02d:%02d', intdiv($s, 60), $s % 60) . '–' . sprintf('%02d:%02d', intdiv($e, 60), $e % 60),
                    'full'         => $full,
                ];
            }

            return response()->json([
                'phong_id' => (int) $data['phong_id'],
                'phut_moi' => $phutMoi,
                'count'    => count($slots),
                'slots'    => $slots,
            ]);
        }

        // Fallback compat cũ: distinct start-time cấp cơ sở.
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
                'label'       => substr($r->gio_bat_dau, 0, 5),
            ])
            ->values();

        return response()->json([
            'co_so_id' => (int) $data['co_so_id'],
            'count'    => $slots->count(),
            'slots'    => $slots,
        ]);
    }
}
