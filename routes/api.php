<?php

use App\Http\Controllers\Api\SimtalApiController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Public — tidak perlu token
// ─────────────────────────────────────────────
Route::post('/auth/login', [SimtalApiController::class, 'login']);

// ─────────────────────────────────────────────
// Protected — perlu Bearer token
// ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employee/profile', [SimtalApiController::class, 'profile']);
    Route::get('/employees',        [SimtalApiController::class, 'employees']);
});