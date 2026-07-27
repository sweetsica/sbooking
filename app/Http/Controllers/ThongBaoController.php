<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThongBaoController extends Controller
{
    /** Notifications còn hiện (chưa bị user "xóa" — hidden_at IS NULL). */
    protected function visibleQuery($user)
    {
        return $user->notifications()->whereNull('hidden_at');
    }

    protected function visibleUnreadCount($user): int
    {
        return (int) $user->notifications()->whereNull('hidden_at')->whereNull('read_at')->count();
    }

    /** Trang /thong-bao — full list, paginated. */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $items = $this->visibleQuery($user)->paginate(20);

        $coSo = $request->attributes->get('co_so') ?? CoSo::where('active', true)->first();

        return view('longevity.thong-bao.index', [
            'items' => $items,
            'coSo'  => $coSo,
            'unreadCount' => $this->visibleUnreadCount($user),
        ]);
    }

    /** API/AJAX: trả về JSON unread count + 10 thông báo gần nhất cho chuông dropdown. */
    public function summary()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        return response()->json([
            'unread_count' => $this->visibleUnreadCount($user),
            'items' => $this->visibleQuery($user)->limit(10)->get()->map(fn ($n) => [
                'id'          => $n->id,
                'event'       => $n->data['event']    ?? null,
                'tieu_de'     => $n->data['tieu_de']  ?? 'Thông báo',
                'noi_dung'    => $n->data['noi_dung'] ?? '',
                'link'        => $n->data['link']     ?? '#',
                'thoi_gian'   => $n->data['thoi_gian'] ?? null,
                'khach_hang'  => $n->data['khach_hang'] ?? null,
                'read_at'     => $n->read_at?->toIso8601String(),
                'created_at'  => $n->created_at->toIso8601String(),
                'created_human' => $n->created_at->diffForHumans(),
            ])->values(),
        ]);
    }

    public function markRead(string $id)
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $n = $user->notifications()->where('id', $id)->first();
        abort_unless($n, 404);
        if (! $n->read_at) $n->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $user->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    /** Ẩn 1 thông báo khỏi UI của user (admin log vẫn thấy). */
    public function hide(string $id)
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $n = $user->notifications()->where('id', $id)->first();
        abort_unless($n, 404);
        if (! $n->hidden_at) {
            $n->forceFill(['hidden_at' => now()])->save();
        }
        return response()->json(['ok' => true]);
    }

    /** Ẩn tất cả thông báo của user hiện tại. */
    public function hideAll()
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $user->notifications()->whereNull('hidden_at')->update(['hidden_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
