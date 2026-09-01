<?php

namespace App\Support;

/**
 * Ghi hoạt động vào public/logs.md — append-only, 1 dòng markdown per event.
 * Mirror pattern lara-scrm cho consistency.
 */
class PublicLog
{
    public static function write(string $action, ?string $detail = null): void
    {
        $file = public_path('logs.md');
        $u = auth()->user();
        $who = $u ? "user#{$u->id} ({$u->name})" : 'guest';
        $ip = request()?->ip() ?? '-';
        $ts = now()->format('Y-m-d H:i:s');
        $line = "- `{$ts}` **{$who}** — {$action}" . ($detail ? " · {$detail}" : "") . " · _IP {$ip}_" . PHP_EOL;

        if (! file_exists($file)) {
            @file_put_contents($file, "# Nhật ký hoạt động (SBooking)\n\nAppend-only. Rotate tay khi to quá.\n\n");
        }
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
