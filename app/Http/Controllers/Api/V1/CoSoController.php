<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CoSo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoSoController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['active', 'slug'];
    private const ALLOWED_SORT    = ['id', 'ten', 'slug', 'created_at'];
    private const SEARCHABLE      = ['ten', 'slug', 'dia_chi'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = CoSo::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(CoSo $co_so): JsonResponse
    {
        return $this->ok($co_so);
    }

    public function store(Request $req): JsonResponse
    {
        return $this->ok(CoSo::create($this->validated($req)), 201);
    }

    public function update(Request $req, CoSo $co_so): JsonResponse
    {
        $co_so->update($this->validated($req, $co_so->id));
        return $this->ok($co_so->fresh());
    }

    public function destroy(CoSo $co_so): JsonResponse
    {
        $co_so->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $co_so->id]]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'ten'                => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug'               => [
                $ignoreId ? 'sometimes' : 'required', 'string', 'max:100',
                Rule::unique('co_so', 'slug')->ignore($ignoreId),
            ],
            'dia_chi'            => ['nullable', 'string', 'max:500'],
            'active'             => ['sometimes', 'boolean'],
            'gio_mo_cua'         => ['nullable', 'date_format:H:i'],
            'gio_dong_cua'       => ['nullable', 'date_format:H:i'],
            'thoi_gian_ca_phut'  => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
