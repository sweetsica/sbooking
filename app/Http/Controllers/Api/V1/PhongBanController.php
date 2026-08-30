<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PhongBan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhongBanController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'ma'];
    private const ALLOWED_SORT    = ['id', 'ten', 'ma', 'created_at'];
    private const SEARCHABLE      = ['ten', 'ma'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = PhongBan::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(PhongBan $phong_ban): JsonResponse
    {
        return $this->ok($phong_ban);
    }

    public function store(Request $req): JsonResponse
    {
        $data = $this->validated($req);
        return $this->ok(PhongBan::create($data), 201);
    }

    public function update(Request $req, PhongBan $phong_ban): JsonResponse
    {
        $phong_ban->update($this->validated($req, $phong_ban->id));
        return $this->ok($phong_ban->fresh());
    }

    public function destroy(PhongBan $phong_ban): JsonResponse
    {
        $phong_ban->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $phong_ban->id]]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'co_so_id' => [$ignoreId ? 'sometimes' : 'required', 'integer', Rule::exists('co_so', 'id')],
            'ten'      => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'ma'       => [
                $ignoreId ? 'sometimes' : 'required', 'string', 'max:100',
                Rule::unique('phong_ban')->where(fn ($q) => $q->where('co_so_id', $req->input('co_so_id')))->ignore($ignoreId),
            ],
        ]);
    }
}
