<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationLogController extends Controller
{
    /**
     * GET /{coSo}/thiet-lap/nhat-ky-thong-bao
     * Admin xem toàn bộ notifications (kể cả đã bị user ẩn).
     */
    public function index(CoSo $co_so, Request $request)
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $q       = trim((string) $request->query('q', ''));
        $userId  = $request->query('user_id');
        $event   = $request->query('event');
        $status  = $request->query('status'); // '', 'unread', 'read', 'hidden', 'visible'
        $tu      = $request->query('tu');
        $den     = $request->query('den');

        $query = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->orderByDesc('created_at');

        if ($userId) {
            $query->where('notifiable_id', $userId);
        }

        if ($event !== null && $event !== '') {
            // data lưu JSON; MySQL JSON_EXTRACT (Laravel hỗ trợ ->)
            $query->where('data->event', $event);
        }

        match ($status) {
            'unread'  => $query->whereNull('read_at'),
            'read'    => $query->whereNotNull('read_at'),
            'hidden'  => $query->whereNotNull('hidden_at'),
            'visible' => $query->whereNull('hidden_at'),
            default   => null,
        };

        if ($tu) $query->whereDate('created_at', '>=', $tu);
        if ($den) $query->whereDate('created_at', '<=', $den);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('data', 'like', $like);
            });
        }

        $items = $query->paginate(30)->withQueryString();

        // Preload user map cho hiển thị tên (tránh N+1)
        $userIds = $items->getCollection()->pluck('notifiable_id')->unique()->all();
        $users   = User::whereIn('id', $userIds)->get()->keyBy('id');

        // Danh sách event để filter dropdown (distinct từ data).
        // 2026-09-04: pluck('data->event') qua query builder trả stdClass với property
        // literal 'data->event' — vài row data null/thiếu event → "Undefined property".
        // Fix: extract JSON path an toàn ở tầng PHP, đồng thời tương thích cả 2 kiểu
        // (data lưu array/JSON string).
        $eventOptions = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->whereNotNull('data')
            ->pluck('data')
            ->map(function ($d) {
                if (is_array($d))  return $d['event'] ?? null;
                if (is_string($d)) return json_decode($d, true)['event'] ?? null;
                if (is_object($d)) return $d->event ?? null;
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('longevity.settings.notification-log', [
            'coSo'         => $co_so,
            'items'        => $items,
            'users'        => $users,
            'allUsers'     => User::orderBy('name')->get(['id', 'name']),
            'eventOptions' => $eventOptions,
            'filters'      => compact('q', 'userId', 'event', 'status', 'tu', 'den'),
        ]);
    }
}
