<?php

namespace App\Providers;

use App\Support\PublicLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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

        // Public log: đăng nhập/đăng xuất + Booking create/update/delete + LichLamViec duyệt.
        Event::listen(Login::class,  fn () => PublicLog::write('đăng nhập'));
        Event::listen(Logout::class, fn () => PublicLog::write('đăng xuất'));

        \App\Models\Booking::created(fn ($b) => PublicLog::write('tạo booking',
            "#{$b->id} " . ($b->ma_booking ?? '') . " · cs=" . ($b->co_so_id ?? '?')));
        \App\Models\Booking::updated(function ($b) {
            $changed = array_keys($b->getChanges());
            $changed = array_diff($changed, ['updated_at']);
            if ($changed) PublicLog::write('sửa booking', "#{$b->id} · " . implode(',', $changed));
        });
        \App\Models\Booking::deleted(fn ($b) => PublicLog::write('xóa booking', "#{$b->id} " . ($b->ma_booking ?? '')));

        \App\Models\LichLamViec::updated(function ($llv) {
            if ($llv->wasChanged('trang_thai')) {
                PublicLog::write('cập nhật lịch làm việc',
                    "#{$llv->id} · trang_thai=" . $llv->trang_thai . " · cs=" . $llv->co_so_id);
            }
        });
    }
}
