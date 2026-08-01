<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class EnsureScrmToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Phase B 2026-08-01: đọc token DB trước (encrypted), fallback env cũ.
        $expected = $this->resolveExpectedToken();
        $provided = $request->bearerToken() ?: $request->header('X-Scrm-Token');

        if (! $expected || ! $provided || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }

    private function resolveExpectedToken(): ?string
    {
        $enc = AppSetting::get('scrm_api_token');
        if ($enc) {
            try {
                return Crypt::decryptString($enc);
            } catch (\Throwable $e) {
                // Fall through to env if decrypt fails.
            }
        }
        return config('services.scrm.api_token') ?: null;
    }
}
