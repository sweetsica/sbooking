<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Push sự kiện booking sang CRM lara-scrm.
 * Match theo mã KH (booking.crm_khach_ma). Nếu booking không có → bỏ qua.
 * Auth: Bearer = user.api_token của người thực hiện action.
 */
class CrmPushService
{
    public static function crmUrl(): string
    {
        // 2026-08-05: đọc AppSetting scrm_url trước (UI /thiet-lap/ket-noi/scrm), fallback config/env.
        $dbUrl = \App\Models\AppSetting::get('scrm_url');
        if ($dbUrl) return rtrim($dbUrl, '/');
        return rtrim(config('services.crm.url') ?? env('CRM_URL', 'http://127.0.0.1:1999'), '/');
    }

    public static function pushStatus(Booking $booking, int $userId): array
    {
        return self::push($booking, $userId, [
            'type' => 'status',
            'booking_ma' => $booking->ma_booking,
            'sbooking_booking_id' => $booking->id,
            'trang_thai_khach' => $booking->trang_thai_khach,
            'trang_thai' => $booking->trang_thai,
            'ly_do_tu_choi' => $booking->ly_do_tu_choi,
        ]);
    }

    /**
     * Phase C1.b rev12 2026-08-02 — async push chạy sau response, không lag user.
     * Dùng cho action lag-sensitive: capNhatTrangThaiKhach / duyet / tuChoi / destroy.
     */
    public static function pushStatusAsync(Booking $booking, int $userId): void
    {
        if (! $booking->crm_khach_ma) return;
        $bookingId = $booking->id;
        \Illuminate\Support\Facades\App::terminating(function () use ($bookingId, $userId) {
            $b = Booking::find($bookingId);
            if ($b) self::pushStatus($b, $userId);
        });
    }

    /**
     * 2026-08-03: token dùng cho callback = shared secret (env SCRM_API_TOKEN — cùng chuỗi scrm dùng
     * để gọi sbooking, đơn giản hoá cấu hình). Fallback user.api_token nếu env chưa set.
     */
    public static function callbackToken(int $userId = 0): ?string
    {
        // 2026-08-05: đọc token từ AppSetting scrm_api_token (encrypted, set qua UI /thiet-lap/ket-noi/scrm).
        //   Fallback env SCRM_API_TOKEN, cuối cùng user.api_token.
        $enc = \App\Models\AppSetting::get('scrm_api_token');
        if ($enc) {
            try {
                $tok = \Illuminate\Support\Facades\Crypt::decryptString($enc);
                if ($tok) return $tok;
            } catch (\Throwable $e) { /* fallthrough */ }
        }
        $shared = env('SCRM_API_TOKEN');
        if ($shared) return $shared;
        $user = $userId ? \App\Models\User::find($userId) : null;
        return $user?->api_token;
    }

    public static function pushDeleteAsync(Booking $booking, int $userId): void
    {
        if (! $booking->crm_khach_ma) return;
        // Snapshot booking data trước khi delete (Job chạy sau response, booking có thể đã gone).
        $snapshot = ['id' => $booking->id, 'ma_booking' => $booking->ma_booking, 'crm_khach_ma' => $booking->crm_khach_ma];
        \Illuminate\Support\Facades\App::terminating(function () use ($snapshot, $userId) {
            $token = self::callbackToken($userId);
            if (! $token) return;
            try {
                \Illuminate\Support\Facades\Http::withToken($token)->acceptJson()->timeout(6)
                    ->post(self::crmUrl() . '/api/leads/' . $snapshot['crm_khach_ma'] . '/booking-event', [
                        'type' => 'delete',
                        'booking_ma' => $snapshot['ma_booking'],
                        'sbooking_booking_id' => $snapshot['id'],
                    ]);
            } catch (\Throwable $e) {
                Log::warning('CrmPushDeleteAsync failed: ' . $e->getMessage());
            }
        });
    }

    public static function pushComment(Booking $booking, int $userId, string $comment): array
    {
        return self::push($booking, $userId, [
            'type' => 'comment',
            'booking_ma' => $booking->ma_booking,
            'sbooking_booking_id' => $booking->id,
            'sbooking_user_id' => $userId,
            'comment' => $comment,
        ]);
    }

    /**
     * Phase C1.e.2 (2026-08-02) — pushEdit giờ gửi cả snapshot booking hiện tại để scrm sync
     * ghi_chu / sale_id / ngay_dat / gio_thuc_hien / phong_id / bac_si_id / dich_vu_id vào booking_log.
     */
    public static function pushEdit(Booking $booking, int $userId, string $summary): array
    {
        return self::push($booking, $userId, [
            'type' => 'edit',
            'booking_ma' => $booking->ma_booking,
            'sbooking_booking_id' => $booking->id,
            'summary' => $summary,
            'changes' => [
                'ghi_chu'         => $booking->ghi_chu,
                'sale_id'         => $booking->sale_id,
                'ngay_dat'        => optional($booking->ngay_dat)->toDateString(),
                'gio_thuc_hien'   => $booking->gio_thuc_hien,
                'phong_id'        => $booking->phong_id,
                'bac_si_id'       => $booking->bac_si_id,
                'dich_vu_id'      => $booking->dich_vu_id,
                'so_lieu_trinh'   => $booking->so_lieu_trinh,
                'so_luong_lo'     => $booking->so_luong_lo,
                'dung_tich_lo'    => $booking->dung_tich_lo,
                'ket_hop_medical' => (bool) $booking->ket_hop_medical,
                'co_tu_van'       => (bool) $booking->co_tu_van,
                'co_kham_cls'     => (bool) $booking->co_kham_cls,
            ],
        ]);
    }

    /** Phase C1.b rev4 (2026-08-01) — báo về CRM khi booking bị Admin xóa. Phải gọi TRƯỚC $booking->delete(). */
    public static function pushDelete(Booking $booking, int $userId): array
    {
        return self::push($booking, $userId, [
            'type' => 'delete',
            'booking_ma' => $booking->ma_booking,
            'sbooking_booking_id' => $booking->id,
        ]);
    }

    /** Trả về mảng ['ok'=>bool, 'msg'=>string] cho caller flash message. */
    private static function push(Booking $booking, int $userId, array $payload): array
    {
        if (! $booking->crm_khach_ma) {
            return ['ok' => false, 'msg' => 'Booking chưa link CRM khách_ma → không đẩy.'];
        }

        $token = self::callbackToken($userId);
        if (! $token) {
            Log::warning("CrmPush skipped: không có SCRM_API_TOKEN env + user $userId thiếu api_token.");
            return ['ok' => false, 'msg' => 'Chưa cấu hình SCRM_API_TOKEN, chưa đẩy CRM.'];
        }

        try {
            $r = Http::withToken($token)->acceptJson()->timeout(6)
                ->post(self::crmUrl() . '/api/leads/' . $booking->crm_khach_ma . '/booking-event', $payload);
            if ($r->successful()) {
                // Phase 6.25.C — scrm trả ups_assignment khi checkin auto-chia sale từ Sale Tiếp Đón.
                $body = $r->json();
                if (! empty($body['ups_assignment']['sale_user_id'])) {
                    $assign = $body['ups_assignment'];
                    $localUserId = null;
                    if (! empty($assign['sbooking_user_id'])) {
                        $localUserId = (int) $assign['sbooking_user_id'];
                    } elseif (! empty($assign['sale_email'])) {
                        $localUserId = \App\Models\User::where('email', $assign['sale_email'])->value('id');
                    }
                    if ($localUserId) {
                        $booking->update(['tiep_don_user_id' => $localUserId]);
                    }
                }
                return ['ok' => true, 'msg' => 'Đã đẩy sang CRM ' . $booking->crm_khach_ma . '.'];
            }
            Log::warning('CrmPush non-2xx', ['status' => $r->status(), 'body' => $r->body(), 'payload' => $payload]);
            return ['ok' => false, 'msg' => 'CRM trả HTTP ' . $r->status() . '.'];
        } catch (\Throwable $e) {
            Log::warning('CrmPush failed: ' . $e->getMessage(), ['payload' => $payload]);
            return ['ok' => false, 'msg' => 'Lỗi mạng CRM: ' . $e->getMessage()];
        }
    }

    /** Phase 6.25.C — Push nút "Đang tiếp đón" / "Hoàn tất" sang scrm. */
    public static function pushTiepDon(Booking $booking, int $userId, bool $isBusy): array
    {
        $token = self::callbackToken($userId);
        if (! $token) {
            return ['ok' => false, 'msg' => 'Chưa cấu hình SCRM_API_TOKEN.'];
        }
        $u = \App\Models\User::find($userId);
        $endpoint = $isBusy ? '/api/ups/busy' : '/api/ups/complete';
        try {
            $r = Http::withToken($token)->acceptJson()->timeout(6)
                ->post(self::crmUrl() . $endpoint, [
                    'sale_email' => $u?->email,
                    'work_date'  => now()->toDateString(),
                ]);
            if ($r->successful()) {
                return ['ok' => true, 'msg' => $isBusy ? 'Đã báo đang tiếp đón.' : 'Đã báo hoàn tất.'];
            }
            return ['ok' => false, 'msg' => 'CRM trả HTTP ' . $r->status() . '.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Lỗi mạng CRM: ' . $e->getMessage()];
        }
    }
}
