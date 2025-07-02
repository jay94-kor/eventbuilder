<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RfpController;
use App\Http\Controllers\ElementDefinitionController;
use App\Http\Controllers\RfpApprovalController;
use App\Http\Controllers\AnnouncementController; // AnnouncementController 추가
use App\Http\Controllers\ProposalController; // ProposalController 추가
use App\Http\Controllers\EvaluationController; // EvaluationController 추가
use App\Http\Controllers\ContractController; // ContractController 추가
use App\Http\Controllers\ScheduleController; // ScheduleController 추가
use App\Http\Controllers\ScheduleAttachmentController; // ScheduleAttachmentController 추가
<<<<<<< Updated upstream
=======
use App\Http\Controllers\AgencyController; // AgencyController 추가
use App\Http\Controllers\CategoryController; // CategoryController 추가
use App\Http\Controllers\AdminController; // AdminController 추가
>>>>>>> Stashed changes

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 인증 관련 공개 라우트
Route::post('/login', [AuthController::class, 'login']);

// 인증이 필요한 라우트 (Sanctum 미들웨어를 통해 토큰 검증)
Route::middleware('auth:sanctum')->group(function () {
    // 사용자 인증 관련 라우트
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // RFP 관련 라우트
    Route::post('/rfps', [RfpController::class, 'store']);
    Route::get('/rfps', [RfpController::class, 'index']);
<<<<<<< Updated upstream
=======

    // RFP 임시저장 관련 라우트 (구체적인 라우트를 먼저 정의)
    Route::post('/rfps/draft', [RfpController::class, 'saveDraft']);  // 임시저장 생성
    Route::get('/rfps/drafts', [RfpController::class, 'getDrafts']); // 임시저장 목록
    Route::get('/rfps/{rfp}/draft', [RfpController::class, 'getDraft']); // 임시저장 조회
    Route::put('/rfps/{rfp}/draft', [RfpController::class, 'updateDraft']); // 임시저장 수정
    Route::post('/rfps/{rfp}/draft/publish', [RfpController::class, 'publishDraft']); // 임시저장 발행

    // 일반적인 RFP 라우트 (모델 바인딩, 마지막에 정의)
>>>>>>> Stashed changes
    Route::get('/rfps/{rfp}', [RfpController::class, 'show']);

    // RFP 요소 정의 관련 라우트 (기존 index 포함, NEW: store, update, destroy)
    Route::get('/element-definitions', [ElementDefinitionController::class, 'index']);
    Route::post('/element-definitions', [ElementDefinitionController::class, 'store']); // NEW: 생성
    Route::put('/element-definitions/{elementDefinition}', [ElementDefinitionController::class, 'update']); // NEW: 수정
    Route::delete('/element-definitions/{elementDefinition}', [ElementDefinitionController::class, 'destroy']); // NEW: 삭제

    // RFP 결재 관련 라우트
    Route::post('/rfps/{rfp}/request-approval', [RfpApprovalController::class, 'requestApproval']);
    Route::post('/rfps/{rfp}/process-approval', [RfpApprovalController::class, 'processApproval']);

    // 공고 관련 라우트
    Route::post('/rfps/{rfp}/publish', [AnnouncementController::class, 'publish']); // RFP를 공고로 발행
    Route::get('/announcements', [AnnouncementController::class, 'index']);         // 공고 목록 조회
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']); // 특정 공고 상세 조회

    // 제안서 관련 라우트
    Route::post('/announcements/{announcement}/proposals', [ProposalController::class, 'store']); // 제안서 제출
    Route::get('/announcements/{announcement}/proposals', [ProposalController::class, 'index']);  // 공고별 제안서 목록 조회
    Route::get('/proposals/{proposal}', [ProposalController::class, 'show']); // 제안서 상세 조회
    Route::post('/proposals/{proposal}/award', [ProposalController::class, 'award']);   // 제안서 낙찰 처리
    Route::post('/proposals/{proposal}/reject', [ProposalController::class, 'reject']); // 제안서 유찰 처리
    Route::patch('/proposals/{proposal}/set-reserve-rank', [ProposalController::class, 'setReserveRank']); // 제안서 예비 순위 설정
    Route::post('/proposals/{proposal}/promote-from-reserve', [ProposalController::class, 'promoteFromReserve']); // 예비 순위 승격

    // 평가 시스템 관련 라우트 (Phase 2)
    Route::post('/announcements/{announcement}/assign-evaluators', [EvaluationController::class, 'assignEvaluators']); // 심사위원 배정
    Route::post('/proposals/{proposal}/submit-score', [EvaluationController::class, 'submitScore']); // 점수 제출
    Route::get('/announcements/{announcement}/evaluation-summary', [EvaluationController::class, 'getEvaluationSummary']); // 평가 현황 조회
    Route::get('/my-evaluations', [EvaluationController::class, 'getMyEvaluations']); // 내 평가 과제 조회

    // 계약 관리 관련 라우트 (NEW: Contract Management)
    Route::get('/contracts', [ContractController::class, 'index']); // 계약 목록 조회
    Route::get('/contracts/{contract}', [ContractController::class, 'show']); // 특정 계약 상세 조회
    Route::patch('/contracts/{contract}/update-payment-status', [ContractController::class, 'updatePaymentStatus']); // 계약 결제 상태 업데이트

    // 스케줄 관리 관련 라우트 (NEW: Schedule Management)
    Route::get('/schedules', [ScheduleController::class, 'index']); // 스케줄 목록 조회
    Route::post('/schedules', [ScheduleController::class, 'store']); // 새 스케줄 생성
    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']); // 특정 스케줄 상세 조회
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']); // 스케줄 수정
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']); // 스케줄 삭제

    // 스케줄 첨부 파일 관련 라우트 (NEW: Schedule Attachments)
    Route::post('/schedules/{schedule}/attachments', [ScheduleAttachmentController::class, 'store']); // 파일 업로드
    Route::get('/schedules/{schedule}/attachments', [ScheduleAttachmentController::class, 'index']); // 첨부 파일 목록 조회
    Route::get('/schedule-attachments/{attachment}', [ScheduleAttachmentController::class, 'download']); // 파일 다운로드
    Route::delete('/schedule-attachments/{attachment}', [ScheduleAttachmentController::class, 'destroy']); // 첨부 파일 삭제

<<<<<<< Updated upstream
=======
    // 카테고리 추천 관리 (관리자 전용)
    Route::post('/categories/{sourceCategory}/recommendations/{targetCategory}', [CategoryController::class, 'addRecommendation']);
    Route::put('/categories/{sourceCategory}/recommendations/{targetCategory}', [CategoryController::class, 'updateRecommendation']);
    Route::delete('/categories/{sourceCategory}/recommendations/{targetCategory}', [CategoryController::class, 'removeRecommendation']);
    
    // 테스트용 라우트
    Route::post('/categories/test-recommendation', [CategoryController::class, 'testAddRecommendation']);

    // 요소 추천 관리 (관리자 전용)
    Route::post('/element-definitions/{sourceElement}/recommendations/{targetElement}', [ElementDefinitionController::class, 'addRecommendation']);
    Route::put('/element-definitions/{sourceElement}/recommendations/{targetElement}', [ElementDefinitionController::class, 'updateRecommendation']);
    Route::delete('/element-definitions/{sourceElement}/recommendations/{targetElement}', [ElementDefinitionController::class, 'removeRecommendation']);

    // 관리자 전용 라우트 (Admin Management)
    Route::prefix('admin')->group(function () {
        Route::get('/agencies', [AdminController::class, 'getAgencies']); // 대행사 목록 조회
        Route::get('/vendors', [AdminController::class, 'getVendors']); // 용역사 목록 조회
        Route::put('/agencies/{agency}', [AdminController::class, 'updateAgency']); // 대행사 정보 수정
        Route::put('/vendors/{vendor}', [AdminController::class, 'updateVendor']); // 용역사 정보 수정
        Route::put('/users/{user}/status', [AdminController::class, 'updateUserStatus']); // 사용자 상태 수정
        
        // 🆕 동적 스펙 템플릿 관리 라우트
        Route::get('/element-templates', [AdminController::class, 'getElementTemplates']); // 모든 요소 템플릿 목록
        Route::get('/element-templates/{element}', [AdminController::class, 'getElementTemplate']); // 특정 요소 템플릿 상세
        Route::put('/element-templates/{element}', [AdminController::class, 'updateElementTemplate']); // 요소 템플릿 업데이트
        Route::post('/element-templates/{element}/reset', [AdminController::class, 'resetElementTemplate']); // 요소 템플릿 초기화
    });

>>>>>>> Stashed changes
    // 여기에 향후 다른 인증 필요한 API 라우트를 추가합니다.
});