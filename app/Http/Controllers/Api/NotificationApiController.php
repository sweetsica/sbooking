<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /** Notifications còn hiện (hidden_at IS NULL). */
    protected function visibleQuery($user)
    {
        return $user->notifications()->whereNull('hidden_at');
    }

    protected function visibleUnreadCount($user): int
    {
        return (int) $user->notifications()->whereNull('hidden_at')->whereNull('read_at')->count();
    }

    /**
     * GET /api/notifications?per_page=20&page=1&only=unread
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min((int) $request->query('per_page', 20), 100);
        $query = $this->visibleQuery($user);
        if ($request->query('only') === 'unread') {
            $query->whereNull('read_at');
        }

        $items = $query->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(fn ($n) => $this->shape($n))->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'unread_count' => $this->visibleUnreadCount($user),
            ],
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->visibleUnreadCount($request->user()),
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     */
    public function markRead(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->first();
        abort_unless($n, 404);
        if (! $n->read_at) $n->markAsRead();
        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/notifications/read-all
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /api/notifications/{id} — ẩn khỏi UI (admin log vẫn thấy).
     */
    public function hide(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->first();
        abort_unless($n, 404);
        if (! $n->hidden_at) {
            $n->forceFill(['hidden_at' => now()])->save();
        }
        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /api/notifications — ẩn toàn bộ notifications của user.
     */
    public function hideAll(Request $request)
    {
        $request->user()->notifications()->whereNull('hidden_at')->update(['hidden_at' => now()]);
        return response()->json(['ok' => true]);
    }

    protected function shape($n): array
    {
        return [
            'id'           => $n->id,
            'event'        => $n->data['event']      ?? null,
            'lich_type'    => $n->data['lich_type']  ?? null,
            'lich_id'      => $n->data['lich_id']    ?? null,
            'tieu_de'      => $n->data['tieu_de']    ?? 'Thông báo',
            'noi_dung'     => $n->data['noi_dung']   ?? '',
            'link'         => $n->data['link']       ?? null,
            'khach_hang'   => $n->data['khach_hang'] ?? null,
            'thoi_gian'    => $n->data['thoi_gian']  ?? null,
            'co_so_slug'   => $n->data['co_so_slug'] ?? null,
            'actor'        => $n->data['actor']      ?? null,
            'read_at'      => $n->read_at?->toIso8601String(),
            'created_at'   => $n->created_at->toIso8601String(),
        ];
    }
}
