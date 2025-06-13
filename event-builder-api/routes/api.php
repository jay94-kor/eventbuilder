<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\RfpController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventBasicController; // EventBasicController 추가

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Features API (public)
Route::get('/features', [FeatureController::class, 'index']);
Route::get('/features/{feature}/recommendations', [FeatureController::class, 'showRecommendations']); // 추천 기능 라우트 추가

// Auth API (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // 인증이 필요한 라우트
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateUser']); // 사용자 정보 업데이트 라우트 추가
        Route::post('/user/mark-onboarded', [AuthController::class, 'markOnboarded']); // 온보딩 완료 라우트 추가
    });
});

// RFPs API (인증 필요)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('rfps', RfpController::class);
    Route::post('/rfp/budget-validation', [RfpController::class, 'validateBudget']); // 예산 검증 라우트 추가
    
    // Dashboard API
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // EventBasics API
    Route::apiResource('event-basics', EventBasicController::class);
});
