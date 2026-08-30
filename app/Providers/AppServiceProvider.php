<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // API v1 rate limit: 60 req/min mỗi token (fallback IP nếu không có bearer).
        // Tắt bằng env API_V1_RATE_LIMIT=0 khi cần bulk import.
        RateLimiter::for('api-v1', function (Request $request) {
            $max = (int) env('API_V1_RATE_LIMIT', 60);
            if ($max <= 0) return Limit::none();
            $key = $request->bearerToken() ?: $request->ip();
            return Limit::perMinute($max)->by($key);
        });
    }
}
