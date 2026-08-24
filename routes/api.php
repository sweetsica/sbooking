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
    Route::post('/bookings/{booking}/comments', [BookingApiController::class, 'comment']);
    Route::get('/sync/dich-vu', [SyncApiController::class, 'dichVu']);
    Route::get('/sync/dich-vu-phong', [SyncApiController::class, 'dichVuPhong']);
    Route::get('/sync/users',   [SyncApiController::class, 'users']);
    Route::get('/sync/bac-si',  [SyncApiController::class, 'bacSi']);
    Route::get('/sync/phong',   [SyncApiController::class, 'phong']);
    Route::get('/sync/khung-gio', [SyncApiController::class, 'khungGio']);
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
