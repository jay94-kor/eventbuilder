<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Vendor;
use Carbon\Carbon;

class AgencyManagementService
{
    /**
     * 모든 대행사와 멤버 정보 조회
     */
    public function getAllAgencies()
    {
        $agencies = Agency::with([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ])
            ->select([
                'id', 'name', 'business_registration_number', 'address', 
                'master_user_id', 'subscription_status', 'subscription_end_date',
                'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // 각 대행사별 멤버 수 추가
        $agencies->each(function ($agency) {
            $agency->member_count = $agency->members->count();
            
            // 멤버 정보에 가입일 추가
            $agency->members->each(function ($member) {
                $member->joined_at = $member->created_at;
            });
        });

        return $agencies;
    }

    /**
     * 모든 용역사와 멤버 정보 조회
     */
    public function getAllVendors()
    {
        $vendors = Vendor::with([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ])
            ->select([
                'id', 'name', 'business_registration_number', 'address', 
                'description', 'specialties', 'master_user_id', 'status',
                'ban_reason', 'banned_at', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // 각 용역사별 멤버 수 추가
        $vendors->each(function ($vendor) {
            $vendor->member_count = $vendor->members->count();
            
            // 멤버 정보에 가입일 추가
            $vendor->members->each(function ($member) {
                $member->joined_at = $member->created_at;
            });
        });

        return $vendors;
    }

    /**
     * 대행사 정보 업데이트
     */
    public function updateAgency(Agency $agency, array $data): Agency
    {
        $updateData = [];
        
        if (isset($data['subscription_end_date'])) {
            $updateData['subscription_end_date'] = $data['subscription_end_date'] 
                ? Carbon::parse($data['subscription_end_date']) 
                : null;
        }
        
        if (isset($data['subscription_status'])) {
            $updateData['subscription_status'] = $data['subscription_status'];
        }
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['address'])) {
            $updateData['address'] = $data['address'];
        }

        $agency->update($updateData);

        // 업데이트된 정보와 함께 다시 로드
        return $agency->load([
            'masterUser:id,name,email,position,job_title',
            'members.user:id,name,email,position,job_title,created_at'
        ]);
    }

    /**
     * 용역사 정보 업데이트
     */
    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $updateData = [];
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            
            // 상태가 banned로 변경되면 banned_at 설정
            if ($data['status'] === 'banned') {
                $updateData['banned_at'] = now();
            } else {
                $updateData['banned_at'] = null;
                $updateData['ban_reason'] = null; // 밴 해제시 사유도 초기화
            }
        }
        
        if (isset($data['ban_reason'])) {
            $updateData['ban_reason'] = $data['ban_reason'];
        }
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['address'])) {
            $updateData['address'] = $data['address'];
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

        $vendor->update($updateData);

        // 업데이트된 정보와 함께 다시 로드
        return $vendor->load([
            'masterUser:id,name,email,position,job_title',
            'members.user:id,name,email,position,job_title,created_at'
        ]);
    }

    /**
     * 대행사 통계 조회
     */
    public function getAgencyStats(): array
    {
        return [
            'total_agencies' => Agency::count(),
            'active_agencies' => Agency::where('subscription_status', 'active')->count(),
            'inactive_agencies' => Agency::where('subscription_status', 'inactive')->count(),
            'suspended_agencies' => Agency::where('subscription_status', 'suspended')->count(),
        ];
    }

    /**
     * 용역사 통계 조회
     */
    public function getVendorStats(): array
    {
        return [
            'total_vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('status', 'active')->count(),
            'inactive_vendors' => Vendor::where('status', 'inactive')->count(),
            'banned_vendors' => Vendor::where('status', 'banned')->count(),
        ];
    }
}