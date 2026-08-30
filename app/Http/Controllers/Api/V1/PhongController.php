<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Phong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhongController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'kieu_phong', 'trang_thai', 'duoc_dat_tu_van', 'ktv_mac_dinh_id'];
    private const ALLOWED_SORT    = ['id', 'ten', 'created_at'];
    private const SEARCHABLE      = ['ten'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = Phong::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(Phong $phong): JsonResponse
    {
        return $this->ok($phong);
    }

    public function store(Request $req): JsonResponse
    {
        return $this->ok(Phong::create($this->validated($req)), 201);
    }

    public function update(Request $req, Phong $phong): JsonResponse
    {
        $phong->update($this->validated($req, $phong->id));
        return $this->ok($phong->fresh());
    }

    public function destroy(Phong $phong): JsonResponse
    {
        $phong->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $phong->id]]);
    }

    /** POST /phong/{phong}/attach-bac-si  body: {bac_si_ids: [...]} */
    public function attachBacSi(Request $req, Phong $phong): JsonResponse
    {
        $data = $req->validate([
            'bac_si_ids'   => ['required', 'array'],
            'bac_si_ids.*' => ['integer', Rule::exists('bac_si', 'id')],
        ]);
        $rows = array_map(fn ($bid) => ['phong_id' => $phong->id, 'bac_si_id' => (int) $bid], $data['bac_si_ids']);
        \DB::table('phong_bac_si')->upsert($rows, ['phong_id', 'bac_si_id']);
        $ids = \DB::table('phong_bac_si')->where('phong_id', $phong->id)->pluck('bac_si_id');
        return $this->ok(['phong_id' => $phong->id, 'bac_si_ids' => $ids]);
    }

    public function detachBacSi(Request $req, Phong $phong): JsonResponse
    {
        $data = $req->validate([
            'bac_si_ids'   => ['required', 'array'],
            'bac_si_ids.*' => ['integer'],
        ]);
        \DB::table('phong_bac_si')->where('phong_id', $phong->id)->whereIn('bac_si_id', $data['bac_si_ids'])->delete();
        $ids = \DB::table('phong_bac_si')->where('phong_id', $phong->id)->pluck('bac_si_id');
        return $this->ok(['phong_id' => $phong->id, 'bac_si_ids' => $ids]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'co_so_id'         => [$ignoreId ? 'sometimes' : 'required', 'integer', Rule::exists('co_so', 'id')],
            'ten'              => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'loai'             => ['nullable', 'string', 'max:50'],
            'kieu_phong'       => [$ignoreId ? 'sometimes' : 'required', Rule::in(['phong_kham', 'phong_dich_vu', 'phong_tu_van'])],
            'duoc_dat_tu_van'  => ['sometimes', 'boolean'],
            'so_slot_toi_da'   => ['nullable', 'integer', 'min:1'],
            'phut_moi_khach'   => ['nullable', 'integer', 'min:1'],
            'ktv_mac_dinh_id'  => ['nullable', 'integer', Rule::exists('users', 'id')],
            'trang_thai'       => ['sometimes', Rule::in(['hoat_dong', 'ngung_hoat_dong'])],
        ]);
    }
}
