<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Member\Http\Controllers\AuthController;

Route::prefix('api/member')->group(function () {
    // 身份验证接口
    Route::post('auth/nonce', [AuthController::class, 'nonce']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // 允许游客访问 user-info (返回默认空数据)
    Route::get('auth/user-info', [AuthController::class, 'userInfo']);

    // 需要登录认证的接口
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });
});

