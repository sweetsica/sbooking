<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dev tool 2026-08-15 — Admin impersonate user khác để QA nhanh nhiều role.
 * Gate: is_admin. quickLogin gate thêm APP_ENV=local.
 */
class ImpersonateController extends Controller
{
    public function start(Request $request, User $user)
    {
        abort_unless(Auth::check(), 403);
        $current = Auth::user();
        abort_unless($current->is_admin, 403, 'Chỉ Admin mới giả lập được.');
        abort_if($user->id === $current->id, 400, 'Không thể giả lập chính mình.');

        $originalId = $request->session()->get('impersonate_original_id', $current->id);
        $request->session()->put('impersonate_original_id', $originalId);
        $request->session()->put('impersonate_original_name', User::find($originalId)?->name ?? '?');

        Auth::login($user);
        return redirect('/')->with('status', "Đang giả lập: {$user->name}");
    }

    public function leave(Request $request)
    {
        $origId = $request->session()->pull('impersonate_original_id');
        $request->session()->forget('impersonate_original_name');
        if (! $origId) return redirect('/');
        $orig = User::find($origId);
        if (! $orig) { Auth::logout(); return redirect()->route('login'); }
        Auth::login($orig);
        return redirect('/dev/quick-login')->with('status', "Đã về {$orig->name}.");
    }

    public function quickLogin()
    {
        abort_unless(app()->environment('local'), 404);
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $users = User::with(['coSo', 'vaiTro', 'phongBan'])
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
                'chuc_danh' => $u->chuc_danh ?? '',
                'vai_tro' => $u->vaiTro?->ten ?? '',
                'phong_ban' => $u->phongBan?->ten ?? '',
                'co_so' => $u->coSo?->ten ?? '(chưa gán)',
                'is_admin' => $u->is_admin,
            ])
            ->groupBy('co_so');

        return view('dev.quick-login', ['groups' => $users]);
    }
}
