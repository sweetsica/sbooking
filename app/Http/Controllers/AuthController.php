<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập tài khoản.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Tài khoản hoặc mật khẩu không đúng.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor(Auth::user()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Trang chủ theo người dùng: cơ sở của họ (admin -> cơ sở đầu tiên)
    private function homeFor($user): string
    {
        $coSo = $user->coSo ?? CoSo::where('active', true)->first();

        return $coSo ? "/{$coSo->slug}/lich-hen" : '/login';
    }
}
