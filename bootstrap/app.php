<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'         => \App\Http\Middleware\EnsureAdmin::class,
            'scrm.token'    => \App\Http\Middleware\EnsureScrmToken::class,
            'api.audit'     => \App\Http\Middleware\LogApiAudit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 403 (không đủ quyền) cho người đã đăng nhập -> hiện trang "Không có quyền truy cập" thân thiện.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 403 || ! $request->user() || $request->expectsJson()) {
                return null;
            }
            $co = $request->route('co_so');
            $slug = is_object($co) ? $co->slug : (is_string($co) ? $co : null);

            return response()->view('longevity.khong-co-quyen', ['slug' => $slug], 403);
        });
    })->create();
