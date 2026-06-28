<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NotificationApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth — public
Route::post('/auth/login', [AuthApiController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me',      [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/notifications',                 [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count',    [NotificationApiController::class, 'unreadCount']);
    Route::post('/notifications/read-all',       [NotificationApiController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read',      [NotificationApiController::class, 'markRead']);
});
