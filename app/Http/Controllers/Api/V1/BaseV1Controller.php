<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base cho mọi controller /api/v1/*.
 * Cung cấp helper: response chuẩn, filter, pagination, sort.
 */
abstract class BaseV1Controller extends Controller
{
    /**
     * Áp filter đơn giản: ?filter[co_so_id]=1&filter[active]=true
     * Chỉ những field trong $allowed mới được filter (whitelist).
     */
    protected function applyFilters(Builder $q, Request $req, array $allowed): Builder
    {
        $filters = (array) $req->input('filter', []);
        foreach ($filters as $key => $val) {
            if (! in_array($key, $allowed, true)) continue;
            if ($val === '' || $val === null) continue;
            if (is_array($val)) {
                $q->whereIn($key, $val);
            } else {
                $q->where($key, $val);
            }
        }
        if ($search = trim((string) $req->input('q', ''))) {
            // Concrete controller override nếu muốn search phức tạp hơn.
            $q->where(function ($qq) use ($search, $req) {
                $searchable = $req->attributes->get('_searchable', []);
                foreach ($searchable as $col) {
                    $qq->orWhere($col, 'like', '%' . $search . '%');
                }
            });
        }
        return $q;
    }

    protected function applySort(Builder $q, Request $req, array $allowed, string $default = '-id'): Builder
    {
        $sort = (string) $req->input('sort', $default);
        foreach (explode(',', $sort) as $s) {
            $s = trim($s);
            if ($s === '') continue;
            $dir = 'asc';
            if (str_starts_with($s, '-')) { $dir = 'desc'; $s = ltrim($s, '-'); }
            if (in_array($s, $allowed, true)) $q->orderBy($s, $dir);
        }
        return $q;
    }

    protected function paginate(Builder $q, Request $req): JsonResponse
    {
        $per = min(200, max(1, (int) $req->input('per_page', 25)));
        $p = $q->paginate($per);
        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
            ],
        ]);
    }

    protected function ok($data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }
}
