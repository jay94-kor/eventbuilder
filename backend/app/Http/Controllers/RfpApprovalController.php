<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rfp;
use App\Models\RfpApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RfpApprovalController extends Controller
{
    /**
     * RFP 결재 요청 (POST /api/rfps/{rfp}/request-approval)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestApproval(Request $request, Rfp $rfp)
    {
        $user = Auth::user();

        // 1. 권한 확인: RFP를 생성한 대행사 멤버만 요청 가능
        // 또는 결재 요청 권한이 있는 사용자만 가능하도록 확장
        if (($user->agency_members->first()->agency_id ?? null) !== $rfp->agency_id) {
            return response()->json(['message' => '이 RFP에 대한 결재 요청 권한이 없습니다.'], 403);
        }

        // 2. RFP 상태 확인: 'draft' 상태에서만 요청 가능
        if ($rfp->current_status !== 'draft') {
            return response()->json(['message' => '이미 결재가 진행 중이거나 완료된 RFP입니다.'], 409); // 409 Conflict
        }

        DB::beginTransaction();
        try {
                    // 3. RFP 상태 변경
        $rfp->current_status = 'approval_pending'; // DB enum에 맞춘 상태값 사용
            $rfp->save();

            // 4. 결재 요청 이력 생성 (간소화된 결재 라인: 일단 단일 요청/처리)
            // 실제 시스템에서는 대행사별 결재 라인에 따라 여러 RfpApproval 레코드를 생성해야 함
            // 지금은 생성자가 요청하면, 관리자(또는 특정 역할)가 처리한다고 가정
            RfpApproval::create([
                'rfp_id' => $rfp->id,
                'approver_user_id' => $user->id, // 요청한 사용자 ID를 임시 결재자로 기록 (실제 결재자가 아님, 그냥 요청자)
                'status' => 'pending',
                'comment' => '결재 요청됨',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'RFP 결재 요청이 성공적으로 접수되었습니다.',
                'rfp_status' => $rfp->current_status,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'RFP 결재 요청 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * RFP 결재 처리 (승인 또는 반려) (POST /api/rfps/{rfp}/process-approval)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function processApproval(Request $request, Rfp $rfp)
    {
        $user = Auth::user();

        // 1. 요청 유효성 검사
        $request->validate([
            'action' => 'required|in:approve,reject', // 'approve' 또는 'reject'
            'comment' => 'nullable|string|max:1000',
        ]);

        // 2. 권한 확인: 관리자 또는 결재 권한이 있는 사용자만 처리 가능
        // 예시: user_type이 'admin'인 경우에만 허용
        if ($user->user_type !== 'admin') {
            return response()->json(['message' => '이 RFP에 대한 결재 처리 권한이 없습니다.'], 403);
        }

        // 3. RFP 상태 확인: 'approval_pending' 또는 'approval_in_progress' 상태에서만 처리 가능
        if (!in_array($rfp->current_status, ['approval_pending', 'approval_in_progress'])) {
            return response()->json(['message' => '현재 결재를 처리할 수 없는 RFP 상태입니다.'], 409);
        }

        DB::beginTransaction();
        try {
            $action = $request->input('action');
            $comment = $request->input('comment');

            // 4. 기존 결재 요청 이력 업데이트 (가장 최근의 pending 요청을 업데이트한다고 가정)
            $rfpApproval = RfpApproval::where('rfp_id', $rfp->id)
                                        ->where('status', 'pending')
                                        ->orderBy('created_at', 'desc')
                                        ->first();

            if (!$rfpApproval) {
                // 이미 처리되었거나 요청이 없는 경우
                return response()->json(['message' => '처리할 결재 요청이 없습니다.'], 404);
            }

            $rfpApproval->approver_user_id = $user->id; // 실제 처리한 사용자 ID 기록
            $rfpApproval->status = $action === 'approve' ? 'approved' : 'rejected';
            $rfpApproval->comment = $comment;
            $rfpApproval->approved_at = now();
            $rfpApproval->save();

            // 5. RFP 상태 변경 로직
            if ($action === 'approve') {
                // 승인 시 RFP 상태를 'approved'로 변경 (공고 발행 준비 완료)
                $rfp->current_status = 'approved';
                $message = 'RFP가 성공적으로 승인되었습니다.';
            } else { // 'reject'
                $rfp->current_status = 'rejected';
                $message = 'RFP가 반려되었습니다.';
            }
            $rfp->save();

            DB::commit();
            return response()->json([
                'message' => $message,
                'rfp_status' => $rfp->current_status, // 이제 'approved'가 반환될 것입니다.
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'RFP 결재 처리 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
