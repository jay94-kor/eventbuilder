<?php

namespace App\Policies;

use App\Models\Rfp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RfpPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // 모든 인증된 사용자는 자신의 RFP 목록을 볼 수 있음
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Rfp $rfp): bool
    {
        return $user->id === $rfp->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // 모든 인증된 사용자는 RFP를 생성할 수 있음
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rfp $rfp): bool
    {
        return $user->id === $rfp->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rfp $rfp): bool
    {
        return $user->id === $rfp->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rfp $rfp): bool
    {
        return $user->id === $rfp->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rfp $rfp): bool
    {
        return $user->id === $rfp->user_id;
    }
}
