<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait HasAuthorizationChecks
{
    /**
     * 현재 사용자가 특정 대행사의 멤버인지 확인
     */
    protected function isAgencyMember(?string $agencyId): bool
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'agency_member') {
            return false;
        }
        
        $userAgencyId = $user->agency_members->first()->agency_id ?? null;
        return $userAgencyId === $agencyId;
    }

    /**
     * 현재 사용자가 특정 용역사의 멤버인지 확인
     */
    protected function isVendorMember(?string $vendorId): bool
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'vendor_member') {
            return false;
        }
        
        $userVendorId = $user->vendor_members->first()->vendor_id ?? null;
        return $userVendorId === $vendorId;
    }

    /**
     * 현재 사용자가 관리자인지 확인
     */
    protected function isAdmin(): bool
    {
        return Auth::user()->user_type === 'admin';
    }

    /**
     * 대행사 권한 확인 (관리자 또는 해당 대행사 멤버)
     */
    protected function checkAgencyAccess(?string $agencyId): ?JsonResponse
    {
        if ($this->isAdmin() || $this->isAgencyMember($agencyId)) {
            return null;
        }
        
        return response()->json(['message' => '접근 권한이 없습니다.'], 403);
    }

    /**
     * 용역사 권한 확인 (관리자 또는 해당 용역사 멤버)
     */
    protected function checkVendorAccess(?string $vendorId): ?JsonResponse
    {
        if ($this->isAdmin() || $this->isVendorMember($vendorId)) {
            return null;
        }
        
        return response()->json(['message' => '접근 권한이 없습니다.'], 403);
    }

    /**
     * 관리자 전용 권한 확인
     */
    protected function checkAdminAccess(): ?JsonResponse
    {
        if ($this->isAdmin()) {
            return null;
        }
        
        return response()->json(['message' => '관리자 권한이 필요합니다.'], 403);
    }

    /**
     * 현재 사용자의 대행사 ID 가져오기
     */
    protected function getUserAgencyId(): ?string
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'agency_member') {
            return null;
        }
        
        return $user->agency_members->first()->agency_id ?? null;
    }

    /**
     * 현재 사용자의 용역사 ID 가져오기
     */
    protected function getUserVendorId(): ?string
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'vendor_member') {
            return null;
        }
        
        return $user->vendor_members->first()->vendor_id ?? null;
    }
} 