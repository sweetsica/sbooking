<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log mọi write op (POST/PATCH/PUT/DELETE) qua /api/v1/* vào api_audit_logs.
 * Redact field nhạy cảm (password, api_token) trong request_body.
 */
class LogApiAudit
{
    private const REDACT_KEYS = ['password', 'password_confirmation', 'api_token', 'token', 'secret'];
    private const MAX_BODY_LEN = 8192;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Chỉ log write op — GET không log để tránh nổ table.
        if (! in_array(strtoupper($request->method()), ['POST', 'PATCH', 'PUT', 'DELETE'], true)) {
            return $response;
        }

        try {
            DB::table('api_audit_logs')->insert([
                'method'          => strtoupper($request->method()),
                'path'            => substr($request->path(), 0, 255),
                'response_status' => $response->getStatusCode(),
                'request_body'    => $this->truncate(json_encode($this->redact($request->all()), JSON_UNESCAPED_UNICODE)),
                'response_body'   => $this->truncate($this->extractResponseBody($response)),
                'ip'              => $request->ip(),
                'actor_id'        => auth()->id(),
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // Không throw — log fail không được block response API.
        }

        return $response;
    }

    private function redact(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->redact($v);
            } elseif (in_array(strtolower((string) $k), self::REDACT_KEYS, true)) {
                $data[$k] = '[REDACTED]';
            }
        }
        return $data;
    }

    private function truncate(?string $s): ?string
    {
        if ($s === null) return null;
        return strlen($s) > self::MAX_BODY_LEN ? substr($s, 0, self::MAX_BODY_LEN) . '…[truncated]' : $s;
    }

    private function extractResponseBody(Response $response): ?string
    {
        $ct = $response->headers->get('Content-Type', '');
        if (! str_contains($ct, 'json')) return null;
        return $response->getContent() ?: null;
    }
}
