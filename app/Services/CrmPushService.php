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

    public static function pushDeleteAsync(Booking $booking, int $userId): void
    {
        if (! $booking->crm_khach_ma) return;
        // Snapshot booking data trước khi delete (Job chạy sau response, booking có thể đã gone).
        $snapshot = ['id' => $booking->id, 'ma_booking' => $booking->ma_booking, 'crm_khach_ma' => $booking->crm_khach_ma];
        \Illuminate\Support\Facades\App::terminating(function () use ($snapshot, $userId) {
            $user = \App\Models\User::find($userId);
            if (! $user?->api_token) return;
            try {
                \Illuminate\Support\Facades\Http::withToken($user->api_token)->acceptJson()->timeout(6)
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
            'comment' => $comment,
        ]);
    }

    public static function pushEdit(Booking $booking, int $userId, string $summary): array
    {
        return self::push($booking, $userId, [
            'type' => 'edit',
            'booking_ma' => $booking->ma_booking,
            'summary' => $summary,
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

        $user = \App\Models\User::find($userId);
        $token = $user?->api_token;
        if (! $token) {
            Log::warning("CrmPush skipped: user $userId thiếu api_token.");
            return ['ok' => false, 'msg' => 'User chưa có api_token, chưa đẩy CRM.'];
        }

        try {
            $r = Http::withToken($token)->acceptJson()->timeout(6)
                ->post(self::crmUrl() . '/api/leads/' . $booking->crm_khach_ma . '/booking-event', $payload);
            if ($r->successful()) {
                return ['ok' => true, 'msg' => 'Đã đẩy sang CRM ' . $booking->crm_khach_ma . '.'];
            }
            Log::warning('CrmPush non-2xx', ['status' => $r->status(), 'body' => $r->body(), 'payload' => $payload]);
            return ['ok' => false, 'msg' => 'CRM trả HTTP ' . $r->status() . '.'];
        } catch (\Throwable $e) {
            Log::warning('CrmPush failed: ' . $e->getMessage(), ['payload' => $payload]);
            return ['ok' => false, 'msg' => 'Lỗi mạng CRM: ' . $e->getMessage()];
        }
    }
}
