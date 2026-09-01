<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * /thiet-lap/nhat-ky-hanh-dong — hiển thị public/logs.md dạng list.
 * Middleware admin (gated ở route).
 */
class PublicLogController extends Controller
{
    public function index(Request $req)
    {
        $file = public_path('logs.md');
        if (! file_exists($file)) {
            return view('longevity.settings.nhat-ky-hanh-dong', [
                'lines' => collect(), 'total' => 0, 'q' => '', 'tail' => 500,
            ]);
        }
        $q = trim((string) $req->input('q', ''));
        $tail = (int) $req->input('tail', 500);
        $tail = max(50, min(5000, $tail));

        $all = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $entries = array_values(array_filter($all, fn ($l) => str_starts_with($l, '- `')));
        $total = count($entries);
        $lines = array_slice($entries, -$tail);
        if ($q !== '') {
            $lines = array_values(array_filter($lines, fn ($l) => stripos($l, $q) !== false));
        }
        $lines = array_reverse($lines);

        return view('longevity.settings.nhat-ky-hanh-dong', compact('lines', 'total', 'q', 'tail'));
    }
}
