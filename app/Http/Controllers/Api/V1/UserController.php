<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseV1Controller
{
    private const ALLOWED_FILTERS = ['co_so_id', 'phong_ban_id', 'vai_tro_id', 'is_admin', 'username', 'email'];
    private const ALLOWED_SORT    = ['id', 'name', 'username', 'created_at', 'updated_at'];
    private const SEARCHABLE      = ['name', 'username', 'email', 'chuc_danh'];

    public function index(Request $req): JsonResponse
    {
        $req->attributes->set('_searchable', self::SEARCHABLE);
        $q = User::query();
        $q = $this->applyFilters($q, $req, self::ALLOWED_FILTERS);
        $q = $this->applySort($q, $req, self::ALLOWED_SORT);
        return $this->paginate($q, $req);
    }

    public function show(User $user): JsonResponse
    {
        return $this->ok($user);
    }

    public function store(Request $req): JsonResponse
    {
        $data = $this->validated($req);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return $this->ok($user, 201);
    }

    public function update(Request $req, User $user): JsonResponse
    {
        $data = $this->validated($req, $user->id);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return $this->ok($user->fresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['data' => ['deleted' => true, 'id' => $user->id]]);
    }

    /**
     * Chuyển user sang cơ sở khác (dùng nhanh khi sync lỗi, kiểu case Quỳnh HCM→HN).
     * PATCH /users/{user}/move  body: {co_so_id: N, phong_ban_id?: N}
     */
    public function move(Request $req, User $user): JsonResponse
    {
        $data = $req->validate([
            'co_so_id'     => ['required', 'integer', Rule::exists('co_so', 'id')],
            'phong_ban_id' => ['nullable', 'integer', Rule::exists('phong_ban', 'id')],
        ]);
        $user->update($data);
        return $this->ok($user->fresh());
    }

    /**
     * POST /api/v1/users/{id}/toggle-busy — Phase 6.26.c (2026-09-04).
     * SCRM push flip users.dung_nhan_lead khi sale toggle busy bên data source.
     * Body: { dung_nhan_lead: bool }. Idempotent.
     */
    public function toggleBusy(Request $req, User $user): JsonResponse
    {
        $data = $req->validate([
            'dung_nhan_lead' => ['required', 'boolean'],
        ]);
        $user->update(['dung_nhan_lead' => (bool) $data['dung_nhan_lead']]);
        return response()->json([
            'id' => $user->id,
            'dung_nhan_lead' => (bool) $user->dung_nhan_lead,
        ]);
    }

    private function validated(Request $req, ?int $ignoreId = null): array
    {
        return $req->validate([
            'name'         => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'chuc_danh'    => ['nullable', 'string', 'max:100'],
            'username'     => [
                $ignoreId ? 'sometimes' : 'required', 'string', 'max:100',
                Rule::unique('users', 'username')->ignore($ignoreId),
            ],
            'email'        => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],
            'password'     => [$ignoreId ? 'nullable' : 'required', 'string', 'min:6'],
            'co_so_id'     => ['nullable', 'integer', Rule::exists('co_so', 'id')],
            'phong_ban_id' => ['nullable', 'integer', Rule::exists('phong_ban', 'id')],
            'vai_tro_id'   => ['nullable', 'integer', Rule::exists('vai_tro', 'id')],
            'is_admin'     => ['sometimes', 'boolean'],
        ]);
    }
}
