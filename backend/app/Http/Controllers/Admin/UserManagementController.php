<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserManagementService;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserManagementController extends Controller
{
    protected $userManagementService;

    public function __construct(UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    /**
     * 사용자 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'account_status' => $request->query('account_status'),
                'user_type' => $request->query('user_type'),
                'search' => $request->query('search'),
                'per_page' => min($request->query('per_page', 15), config('user.pagination.max_per_page')),
            ];

            $users = $this->userManagementService->getUserList($filters);

            return ApiResponse::success('사용자 목록을 성공적으로 조회했습니다.', [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('사용자 목록 조회에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 사용자 상세 정보 조회
     */
    public function show(string $userId): JsonResponse
    {
        try {
            $user = $this->userManagementService->getUserDetails($userId);

            return ApiResponse::success('사용자 정보를 성공적으로 조회했습니다.', [
                'user' => $user,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('사용자를 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('사용자 정보 조회에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 사용자 계정 상태 업데이트 (승인/거절/정지)
     */
    public function updateStatus(UpdateUserStatusRequest $request, string $userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            
            $updatedUser = $this->userManagementService->updateUserStatus(
                $user, 
                $request->validated()
            );

            return ApiResponse::success('사용자 상태가 성공적으로 업데이트되었습니다.', [
                'user' => $updatedUser,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('사용자를 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('사용자 상태 업데이트에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 승인 대기 사용자 통계
     */
    public function getPendingStats(): JsonResponse
    {
        try {
            $pendingCount = $this->userManagementService->getPendingUsersCount();

            return ApiResponse::success('승인 대기 통계를 조회했습니다.', [
                'pending_count' => $pendingCount,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('통계 조회에 실패했습니다.', $e->getMessage());
        }
    }
}