<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Vendor;
use App\Services\AgencyManagementService;
use App\Http\Requests\Admin\UpdateAgencyRequest;
use App\Http\Requests\Admin\UpdateVendorRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AgencyManagementController extends Controller
{
    protected $agencyManagementService;

    public function __construct(AgencyManagementService $agencyManagementService)
    {
        $this->agencyManagementService = $agencyManagementService;
    }

    /**
     * 모든 대행사와 멤버 정보를 조회
     */
    public function getAgencies(): JsonResponse
    {
        try {
            $agencies = $this->agencyManagementService->getAllAgencies();

            return ApiResponse::success('대행사 목록을 성공적으로 조회했습니다.', [
                'agencies' => $agencies,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('대행사 목록을 불러오는데 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 모든 용역사와 멤버 정보를 조회
     */
    public function getVendors(): JsonResponse
    {
        try {
            $vendors = $this->agencyManagementService->getAllVendors();

            return ApiResponse::success('용역사 목록을 성공적으로 조회했습니다.', [
                'vendors' => $vendors,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('용역사 목록을 불러오는데 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 대행사 정보 업데이트 (구독 만료일 포함)
     */
    public function updateAgency(UpdateAgencyRequest $request, string $agencyId): JsonResponse
    {
        try {
            $agency = Agency::findOrFail($agencyId);
            
            $updatedAgency = $this->agencyManagementService->updateAgency(
                $agency, 
                $request->validated()
            );

            return ApiResponse::success('대행사 정보가 성공적으로 업데이트되었습니다.', [
                'agency' => $updatedAgency,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('대행사를 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('대행사 정보 업데이트에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 용역사 정보 업데이트
     */
    public function updateVendor(UpdateVendorRequest $request, string $vendorId): JsonResponse
    {
        try {
            $vendor = Vendor::findOrFail($vendorId);
            
            $updatedVendor = $this->agencyManagementService->updateVendor(
                $vendor, 
                $request->validated()
            );

            return ApiResponse::success('용역사 정보가 성공적으로 업데이트되었습니다.', [
                'vendor' => $updatedVendor,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('용역사를 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('용역사 정보 업데이트에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 대행사 통계 조회
     */
    public function getAgencyStats(): JsonResponse
    {
        try {
            $stats = $this->agencyManagementService->getAgencyStats();

            return ApiResponse::success('대행사 통계를 조회했습니다.', $stats);

        } catch (\Exception $e) {
            return ApiResponse::serverError('대행사 통계 조회에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 용역사 통계 조회
     */
    public function getVendorStats(): JsonResponse
    {
        try {
            $stats = $this->agencyManagementService->getVendorStats();

            return ApiResponse::success('용역사 통계를 조회했습니다.', $stats);

        } catch (\Exception $e) {
            return ApiResponse::serverError('용역사 통계 조회에 실패했습니다.', $e->getMessage());
        }
    }
}