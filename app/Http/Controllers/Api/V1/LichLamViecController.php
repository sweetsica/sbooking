<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LichLamViec;
use App\Models\LichLamViecChiTiet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LichLamViecController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'trang_thai'];
    private const ALLOWED_SORT    = ['id', 'thang', 'trang_thai', 'created_at'];

    public function index(Request $req): JsonResponse
    {
        $q = LichLamViec::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        if ($month = $req->input('month')) {
            $q->whereDate('thang', $month);
        }
        $q = $this->applySort($q, $req, self::ALLOWED_SORT, '-thang');
        return $this->paginate($q, $req);
    }

    public function show(Request $req, LichLamViec $lich_lam_viec): JsonResponse
    {
        $withCt = $req->boolean('with_chi_tiet');
        if ($withCt) $lich_lam_viec->load('chiTiets');
        return $this->ok($lich_lam_viec);
    }

    public function store(Request $req): JsonResponse
    {
        $data = $req->validate([
            'co_so_id'   => ['required', 'integer', Rule::exists('co_so', 'id')],
            'thang'      => ['required', 'date'],
            'trang_thai' => ['sometimes', Rule::in(array_keys(LichLamViec::TRANG_THAI))],
            'ghi_chu'    => ['nullable', 'string'],
        ]);
        $data['trang_thai'] = $data['trang_thai'] ?? 'nhap';
        return $this->ok(LichLamViec::create($data), 201);
    }

    public function update(Request $req, LichLamViec $lich_lam_viec): JsonResponse
    {
        $data = $req->validate([
            'trang_thai'    => ['sometimes', Rule::in(array_keys(LichLamViec::TRANG_THAI))],
            'ghi_chu'       => ['nullable', 'string'],
            'ly_do_tu_choi' => ['nullable', 'string'],
            'applied_at'    => ['nullable', 'date'],
        ]);
        $lich_lam_viec->update($data);
        return $this->ok($lich_lam_viec->fresh());
    }

    public function destroy(LichLamViec $lich_lam_viec): JsonResponse
    {
        // Cascade xoá chi tiết.
        LichLamViecChiTiet::where('lich_lam_viec_id', $lich_lam_viec->id)->delete();
        $lich_lam_viec->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $lich_lam_viec->id]]);
    }

    /** GET /lich-lam-viec/{llv}/chi-tiet — list chi tiết. */
    public function chiTiets(Request $req, LichLamViec $lich_lam_viec): JsonResponse
    {
        $q = LichLamViecChiTiet::where('lich_lam_viec_id', $lich_lam_viec->id);
        foreach (['loai', 'phong_id', 'doi_tuong_id', 'ca'] as $f) {
            if ($v = $req->input("filter.$f")) $q->where($f, $v);
        }
        if ($from = $req->input('from')) $q->whereDate('ngay', '>=', $from);
        if ($to = $req->input('to'))     $q->whereDate('ngay', '<=', $to);
        $q->orderBy('ngay')->orderBy('ca');
        return $this->paginate($q, $req);
    }

    /** POST /lich-lam-viec/{llv}/chi-tiet — bulk add. body: {items:[{loai,doi_tuong_id,phong_id,ngay,ca,ten?}, ...]} */
    public function storeChiTiet(Request $req, LichLamViec $lich_lam_viec): JsonResponse
    {
        $data = $req->validate([
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.loai'            => ['required', Rule::in(['bac_si', 'ktv'])],
            'items.*.doi_tuong_id'    => ['required', 'integer'],
            'items.*.phong_id'        => ['required', 'integer', Rule::exists('phong', 'id')],
            'items.*.ngay'            => ['required', 'date'],
            'items.*.ca'              => ['required', Rule::in(['sang', 'chieu'])],
            'items.*.ten'             => ['nullable', 'string', 'max:255'],
        ]);
        $rows = array_map(fn ($it) => $it + [
            'lich_lam_viec_id' => $lich_lam_viec->id,
            'created_at' => now(), 'updated_at' => now(),
        ], $data['items']);
        foreach (array_chunk($rows, 500) as $chunk) {
            \DB::table('lich_lam_viec_chi_tiet')->insert($chunk);
        }
        return $this->ok(['inserted' => count($rows)], 201);
    }

    /** DELETE /lich-lam-viec/{llv}/chi-tiet/{ct} */
    public function destroyChiTiet(LichLamViec $lich_lam_viec, LichLamViecChiTiet $chi_tiet): JsonResponse
    {
        if ($chi_tiet->lich_lam_viec_id !== $lich_lam_viec->id) {
            return response()->json(['message' => 'Chi tiết không thuộc lịch này.'], 404);
        }
        $chi_tiet->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $chi_tiet->id]]);
    }
}
