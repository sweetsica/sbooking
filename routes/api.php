<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\SyncApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth — public
Route::post('/auth/login', [AuthApiController::class, 'login']);

// Server-to-server cho Lara-SCRM (bearer token cố định qua env SCRM_API_TOKEN).
Route::middleware('scrm.token')->group(function () {
    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    // 2026-08-19: pre-flight check (dry-run) — SCRM lead-form call trước khi tạo booking
    // để hiển thị lỗi BS trùng lịch / phòng full / khung ngắn ngay tại form, không đợi sync fail.
    Route::post('/bookings/preflight', [BookingApiController::class, 'preflight']);
    Route::put('/bookings/{booking}', [BookingApiController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingApiController::class, 'destroy']);
    Route::post('/bookings/{booking}/comments', [BookingApiController::class, 'comment']);
    Route::get('/sync/dich-vu', [SyncApiController::class, 'dichVu']);
    Route::get('/sync/dich-vu-phong', [SyncApiController::class, 'dichVuPhong']);
    Route::get('/sync/users',   [SyncApiController::class, 'users']);
    Route::get('/sync/bac-si',  [SyncApiController::class, 'bacSi']);
    Route::get('/sync/phong',   [SyncApiController::class, 'phong']);
    Route::get('/sync/khung-gio', [SyncApiController::class, 'khungGio']);
});

// ═══════════════════════════════════════════════════════════════════
// API v1 — CRUD chuẩn cho các entity (dùng bearer token scrm.token,
// throttle 60 req/min/token, cover phong_ban, co_so, bac_si, user, …).
// Phase A (2026-08-30): users + phong_ban + co_so + bac_si.
// ═══════════════════════════════════════════════════════════════════
Route::prefix('v1')->middleware(['scrm.token', 'throttle:api-v1', 'api.audit'])->group(function () {
    Route::apiResource('users',     \App\Http\Controllers\Api\V1\UserController::class);
    Route::patch('users/{user}/move', [\App\Http\Controllers\Api\V1\UserController::class, 'move']);

    Route::apiResource('phong-ban', \App\Http\Controllers\Api\V1\PhongBanController::class)
        ->parameters(['phong-ban' => 'phong_ban']);

    Route::apiResource('co-so',     \App\Http\Controllers\Api\V1\CoSoController::class)
        ->parameters(['co-so' => 'co_so']);

    Route::apiResource('bac-si',    \App\Http\Controllers\Api\V1\BacSiController::class)
        ->parameters(['bac-si' => 'bac_si']);
    Route::post('bac-si/{bac_si}/attach-phong',   [\App\Http\Controllers\Api\V1\BacSiController::class, 'attachPhong']);
    Route::post('bac-si/{bac_si}/detach-phong',   [\App\Http\Controllers\Api\V1\BacSiController::class, 'detachPhong']);

    // Phase B
    Route::apiResource('phong', \App\Http\Controllers\Api\V1\PhongController::class);
    Route::post('phong/{phong}/attach-bac-si', [\App\Http\Controllers\Api\V1\PhongController::class, 'attachBacSi']);
    Route::post('phong/{phong}/detach-bac-si', [\App\Http\Controllers\Api\V1\PhongController::class, 'detachBacSi']);

    Route::apiResource('dich-vu', \App\Http\Controllers\Api\V1\DichVuController::class)
        ->parameters(['dich-vu' => 'dich_vu']);

    // Phase C: LichLamViec + Bookings (song song /api/bookings cũ).
    Route::apiResource('lich-lam-viec', \App\Http\Controllers\Api\V1\LichLamViecController::class)
        ->parameters(['lich-lam-viec' => 'lich_lam_viec']);
    Route::get   ('lich-lam-viec/{lich_lam_viec}/chi-tiet',              [\App\Http\Controllers\Api\V1\LichLamViecController::class, 'chiTiets']);
    Route::post  ('lich-lam-viec/{lich_lam_viec}/chi-tiet',              [\App\Http\Controllers\Api\V1\LichLamViecController::class, 'storeChiTiet']);
    Route::delete('lich-lam-viec/{lich_lam_viec}/chi-tiet/{chi_tiet}',   [\App\Http\Controllers\Api\V1\LichLamViecController::class, 'destroyChiTiet']);

    Route::get('bookings/export', [\App\Http\Controllers\Api\V1\BookingController::class, 'export']);
    Route::apiResource('bookings', \App\Http\Controllers\Api\V1\BookingController::class);

    // Phase D: audit + inspect
    Route::get('audit-logs', [\App\Http\Controllers\Api\V1\AuditLogController::class, 'index']);
    Route::get('inspect/booking/{id}', [\App\Http\Controllers\Api\V1\InspectController::class, 'booking']);
});

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me',      [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/notifications',                 [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count',    [NotificationApiController::class, 'unreadCount']);
    Route::post('/notifications/read-all',       [NotificationApiController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read',      [NotificationApiController::class, 'markRead']);
    Route::delete('/notifications',              [NotificationApiController::class, 'hideAll']);
    Route::delete('/notifications/{id}',         [NotificationApiController::class, 'hide']);
});
