<?php

namespace App\Http\Controllers;

use App\Models\CoSo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required'         => 'Vui lòng nhập mật khẩu mới.',
            'password.min'              => 'Mật khẩu mới tối thiểu 6 ký tự.',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = $data['password']; // cast 'hashed' tự băm
        $user->save();

        return back()->with('ok', 'Đã đổi mật khẩu thành công.');
    }

    // Trang chủ theo người dùng: bác sĩ → lịch tư vấn, còn lại → đặt phòng
    private function homeFor($user): string
    {
        $coSo = $user->coSo ?? CoSo::where('active', true)->first();
        if (! $coSo) return '/login';

        $vaiTroMa = $user->vaiTro?->ma;

        // Bác sĩ → lịch tư vấn
        if (in_array($vaiTroMa, ['bac_si', 'bac_si_tu_van'], true)) {
            return "/{$coSo->slug}/lich-tu-van";
        }

        // Nhân viên → trang tạo booking
        if ($vaiTroMa === 'nhan_vien') {
            return "/{$coSo->slug}/tao-moi";
        }

        return "/{$coSo->slug}/lich-hen";
    }
}
