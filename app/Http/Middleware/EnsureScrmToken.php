<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureScrmToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.scrm.api_token');
        $provided = $request->bearerToken() ?: $request->header('X-Scrm-Token');

        if (! $expected || ! $provided || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
