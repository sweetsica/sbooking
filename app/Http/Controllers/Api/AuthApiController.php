<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * POST /api/auth/login
     * Body: { username, password, device_name }
     * Trả về token Sanctum để client lưu lại.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username'    => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('username', $data['username'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Sai tài khoản hoặc mật khẩu.'],
            ]);
        }

        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'        => $user->id,
                'name'      => $user->name,
                'username'  => $user->username,
                'email'     => $user->email,
                'is_admin'  => (bool) $user->is_admin,
                'co_so_id'  => $user->co_so_id,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout — revoke token hiện tại.
     */
    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/auth/me — info user hiện tại.
     */
    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json([
            'id'        => $u->id,
            'name'      => $u->name,
            'username'  => $u->username,
            'email'     => $u->email,
            'is_admin'  => (bool) $u->is_admin,
            'co_so_id'  => $u->co_so_id,
            'vai_tro_id' => $u->vai_tro_id,
        ]);
    }
}
