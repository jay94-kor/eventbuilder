<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\User;
use App\Models\Agency;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    /**
     * 계약 목록 조회 (GET /api/contracts)
     * (관리자, 해당 대행사/용역사 멤버만 접근 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Contract::query();

        // 1. 권한에 따른 계약 필터링
        if ($user->user_type === 'admin') {
            // 관리자는 모든 계약 조회 가능
        } elseif ($user->user_type === 'agency_member') {
            // 대행사 멤버는 자신의 대행사와 관련된 계약만 조회
            $agencyId = $user->agency_members->first()->agency_id ?? null;
            if (!$agencyId) {
                return response()->json(['message' => '소속된 대행사 정보를 찾을 수 없습니다.'], 403);
            }
            $query->whereHas('announcement', function($q) use ($agencyId) {
                $q->where('agency_id', $agencyId);
            });
        } elseif ($user->user_type === 'vendor_member') {
            // 용역사 멤버는 자신의 용역사와 관련된 계약만 조회
            $vendorId = $user->vendor_members->first()->vendor_id ?? null;
            if (!$vendorId) {
                return response()->json(['message' => '소속된 용역사 정보를 찾을 수 없습니다.'], 403);
            }
            $query->where('vendor_id', $vendorId);
        } else {
            return response()->json(['message' => '계약 목록을 조회할 권한이 없습니다.'], 403);
        }

        // 2. 계약 목록 로드 (필요한 관계와 함께)
        $contracts = $query->with('announcement.rfp.project', 'proposal.vendor', 'vendor')
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json([
            'message' => '계약 목록을 성공적으로 불러왔습니다.',
            'contracts' => $contracts,
        ], 200);
    }

    /**
     * 특정 계약 상세 조회 (GET /api/contracts/{contract})
     * (관리자, 해당 대행사/용역사 멤버만 접근 가능)
     *
     * @param  \App\Models\Contract  $contract (모델 바인딩)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Contract $contract)
    {
        $user = Auth::user();

        // 1. 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            // 계약과 연결된 공고의 대행사 ID와 사용자 소속 대행사 ID 비교
            if (($user->agency_members->first()->agency_id ?? null) === $contract->announcement->agency_id) {
                $hasAccess = true;
            }
        } elseif ($user->user_type === 'vendor_member') {
            // 계약과 연결된 용역사 ID와 사용자 소속 용역사 ID 비교
            if (($user->vendor_members->first()->vendor_id ?? null) === $contract->vendor_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 계약에 접근할 권한이 없습니다.'], 403);
        }

        // 2. 계약 상세 정보 로드 (필요한 관계와 함께)
        $contract->load('announcement.rfp.project', 'proposal.vendor.masterUser', 'vendor.masterUser');

        return response()->json([
            'message' => '계약 상세 정보를 성공적으로 불러왔습니다.',
            'contract' => $contract,
        ], 200);
    }

    /**
     * 계약 결제 상태 업데이트 (PATCH /api/contracts/{contract}/update-payment-status)
     * (관리자 또는 해당 대행사 멤버만 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentStatus(Request $request, Contract $contract)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 해당 대행사 멤버만 업데이트 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            // 계약과 연결된 공고의 대행사 ID와 사용자 소속 대행사 ID 비교
            if (($user->agency_members->first()->agency_id ?? null) === $contract->announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '계약 결제 상태를 업데이트할 권한이 없습니다.'], 403);
        }

        // 2. 요청 유효성 검사
        $request->validate([
            'payment_status' => ['required', 'string', Rule::in(['pending', 'prepayment_paid', 'balance_paid', 'all_paid'])],
            'prepayment_paid_at' => 'nullable|date',
            'balance_paid_at' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $newStatus = $request->input('payment_status');

            // 3. 상태 업데이트 및 지급일 기록
            $contract->payment_status = $newStatus;

            if ($newStatus === 'prepayment_paid') {
                $contract->prepayment_paid_at = $request->input('prepayment_paid_at') ?? now();
            } elseif ($newStatus === 'balance_paid' || $newStatus === 'all_paid') {
                $contract->balance_paid_at = $request->input('balance_paid_at') ?? now();
            }
            
            // 'all_paid' 상태일 경우, 선금 지급일도 함께 업데이트 (만약 기록되지 않았다면)
            if ($newStatus === 'all_paid' && is_null($contract->prepayment_paid_at)) {
                $contract->prepayment_paid_at = $request->input('prepayment_paid_at') ?? now();
            }

            $contract->save();

            DB::commit();
            return response()->json([
                'message' => '계약 결제 상태가 성공적으로 업데이트되었습니다.',
                'contract' => $contract,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '계약 결제 상태 업데이트 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
