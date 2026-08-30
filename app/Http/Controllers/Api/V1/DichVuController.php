<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DichVu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DichVuController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'thuoc_nhom', 'la_dich_vu', 'active'];
    private const ALLOWED_SORT    = ['id', 'ten', 'created_at'];
    private const SEARCHABLE      = ['ten'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = DichVu::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(DichVu $dich_vu): JsonResponse
    {
        return $this->ok($dich_vu);
    }

    public function store(Request $req): JsonResponse
    {
        return $this->ok(DichVu::create($this->validated($req)), 201);
    }

    public function update(Request $req, DichVu $dich_vu): JsonResponse
    {
        $dich_vu->update($this->validated($req, $dich_vu->id));
        return $this->ok($dich_vu->fresh());
    }

    public function destroy(DichVu $dich_vu): JsonResponse
    {
        $dich_vu->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $dich_vu->id]]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'co_so_id'        => [$ignoreId ? 'sometimes' : 'required', 'integer', Rule::exists('co_so', 'id')],
            'ten'             => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'thoi_gian_phut'  => ['nullable', 'integer', 'min:1'],
            'thuoc_nhom'      => ['nullable', Rule::in(['tu_van', 'kham_ls', 'khac'])],
            'la_dich_vu'      => ['sometimes', 'boolean'],
            'active'          => ['sometimes', 'boolean'],
        ]);
    }
}
