<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BacSi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BacSiController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'active', 'nhan_tu_van', 'nhan_kham_ls'];
    private const ALLOWED_SORT    = ['id', 'ten', 'created_at'];
    private const SEARCHABLE      = ['ten', 'chuc_danh'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = BacSi::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(BacSi $bac_si): JsonResponse
    {
        return $this->ok($bac_si);
    }

    public function store(Request $req): JsonResponse
    {
        return $this->ok(BacSi::create($this->validated($req)), 201);
    }

    public function update(Request $req, BacSi $bac_si): JsonResponse
    {
        $bac_si->update($this->validated($req, $bac_si->id));
        return $this->ok($bac_si->fresh());
    }

    public function destroy(BacSi $bac_si): JsonResponse
    {
        $bac_si->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $bac_si->id]]);
    }

    /** Attach/detach phòng (pivot phong_bac_si). Body: {phong_ids: [10, 12]} */
    public function attachPhong(Request $req, BacSi $bac_si): JsonResponse
    {
        $data = $req->validate([
            'phong_ids'   => ['required', 'array'],
            'phong_ids.*' => ['integer', Rule::exists('phong', 'id')],
        ]);
        $rows = array_map(fn ($pid) => ['bac_si_id' => $bac_si->id, 'phong_id' => (int) $pid], $data['phong_ids']);
        \DB::table('phong_bac_si')->upsert($rows, ['bac_si_id', 'phong_id']);
        $phongIds = \DB::table('phong_bac_si')->where('bac_si_id', $bac_si->id)->pluck('phong_id');
        return $this->ok(['bac_si_id' => $bac_si->id, 'phong_ids' => $phongIds]);
    }

    public function detachPhong(Request $req, BacSi $bac_si): JsonResponse
    {
        $data = $req->validate([
            'phong_ids'   => ['required', 'array'],
            'phong_ids.*' => ['integer'],
        ]);
        \DB::table('phong_bac_si')->where('bac_si_id', $bac_si->id)->whereIn('phong_id', $data['phong_ids'])->delete();
        $phongIds = \DB::table('phong_bac_si')->where('bac_si_id', $bac_si->id)->pluck('phong_id');
        return $this->ok(['bac_si_id' => $bac_si->id, 'phong_ids' => $phongIds]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'co_so_id'      => [$ignoreId ? 'sometimes' : 'required', 'integer', Rule::exists('co_so', 'id')],
            'ten'           => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'chuc_danh'     => ['nullable', 'string', 'max:100'],
            'nhan_tu_van'   => ['sometimes', 'boolean'],
            'phut_tu_van'   => ['nullable', 'integer', 'min:1'],
            'nhan_kham_ls'  => ['sometimes', 'boolean'],
            'phut_kham_ls'  => ['nullable', 'integer', 'min:1'],
            'gio_bat_dau'   => ['nullable', 'date_format:H:i'],
            'gio_ket_thuc'  => ['nullable', 'date_format:H:i'],
            'active'        => ['sometimes', 'boolean'],
            'xuat_hien_moi_co_so' => ['sometimes', 'boolean'],
        ]);
    }
}
