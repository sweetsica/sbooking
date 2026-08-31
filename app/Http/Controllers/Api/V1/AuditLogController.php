<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['method', 'response_status', 'actor_id'];
    private const ALLOWED_SORT    = ['id', 'created_at', 'response_status'];

    public function index(Request $req): JsonResponse
    {
        $q = ApiAuditLog::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        if ($path = $req->input('filter.path')) {
            $q->where('path', 'like', '%' . $path . '%');
        }
        if ($from = $req->input('from')) $q->where('created_at', '>=', $from);
        if ($to   = $req->input('to'))   $q->where('created_at', '<=', $to);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT, '-id');
        return $this->paginate($q, $req);
    }
}
