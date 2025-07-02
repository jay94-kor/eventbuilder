<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserManagementService
{
    /**
     * 사용자 상태 업데이트
     */
    public function updateUserStatus(User $user, array $data): User
    {
        $updateData = [
            'account_status' => $data['account_status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ];

        // 승인된 경우 승인 정보 추가
        if ($data['account_status'] === config('user.status.approved')) {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = now();
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * 사용자 목록 조회 (필터링 옵션 포함)
     */
    public function getUserList(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::query()
            ->select([
                'id', 'name', 'email', 'user_type', 'account_status',
                'position', 'job_title', 'admin_notes', 'approved_by',
                'approved_at', 'created_at', 'updated_at'
            ]);

        // 필터 적용
        if (!empty($filters['account_status'])) {
            $query->where('account_status', $filters['account_status']);
        }

        if (!empty($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 사용자 상세 정보 조회
     */
    public function getUserDetails(string $userId): User
    {
        return User::with(['agencyMembers.agency', 'vendorMembers.vendor'])
                   ->findOrFail($userId);
    }

    /**
     * 승인 대기 중인 사용자 수 조회
     */
    public function getPendingUsersCount(): int
    {
        return User::where('account_status', config('user.status.pending', 'pending'))->count();
    }
}