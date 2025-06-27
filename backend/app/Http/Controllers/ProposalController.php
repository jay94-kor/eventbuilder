<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\VendorMember;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProposalController extends Controller
{
    /**
     * 특정 공고에 제안서 제출 (POST /api/announcements/{announcement}/proposals)
     *
     * @OA\Post(
     *     path="/api/announcements/{announcement}/proposals",
     *     tags={"Proposal Management"},
     *     summary="제안서 제출",
     *     description="특정 공고에 제안서를 제출합니다. 용역사만 제출 가능하며, 공고당 한 번만 제출할 수 있습니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="announcement",
     *         in="path",
     *         required=true,
     *         description="공고 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="proposed_price", type="number", example=42000000, description="제안 금액"),
     *             @OA\Property(property="proposal_text", type="string", example="저희 회사는 10년간의 무대 설치 경험을 바탕으로...", description="제안서 내용")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="제안서 제출 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="제안서가 성공적으로 제출되었습니다."),
     *             @OA\Property(property="proposal", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="proposed_price", type="number"),
     *                 @OA\Property(property="proposal_text", type="string"),
     *                 @OA\Property(property="status", type="string", example="submitted")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="제안서 제출 권한이 없습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="중복 제출",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="이미 해당 공고에 제안서를 제출했습니다.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Announcement $announcement)
    {
        $user = Auth::user();

        // 1. 권한 확인: 용역사(vendor_member)만 제안서 제출 가능
        if ($user->user_type !== 'vendor_member') {
            return response()->json(['message' => '제안서 제출 권한이 없습니다.'], 403);
        }

        // 2. 용역사 정보 가져오기
        $vendorMember = $user->vendor_members->first();
        if (!$vendorMember) {
            return response()->json(['message' => '소속된 용역사 정보를 찾을 수 없습니다.'], 403);
        }
        $vendorId = $vendorMember->vendor_id;

        // 3. 공고 상태 확인: 'open' 상태에서만 제안서 제출 가능
        if ($announcement->status !== 'open' || $announcement->closing_at <= now()) {
            return response()->json(['message' => '현재 제안서를 제출할 수 없는 공고입니다. 공고가 마감되었거나 종료되었습니다.'], 409); // 409 Conflict
        }

        // 4. 이미 해당 용역사가 이 공고에 제안서를 제출했는지 확인 (unique 제약 조건)
        if (Proposal::where('announcement_id', $announcement->id)->where('vendor_id', $vendorId)->exists()) {
            return response()->json(['message' => '이미 해당 공고에 제안서를 제출했습니다.'], 409);
        }

        // 5. 요청 유효성 검사
        $request->validate([
            'proposed_price' => 'nullable|numeric|min:0', // 제안 금액은 필수 아님 (선택)
            'proposal_text' => 'nullable|string',
            // 'proposal_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240', // 파일 업로드는 추후 구현
        ]);

        DB::beginTransaction();
        try {
            // 6. 제안서 생성
            $proposal = Proposal::create([
                'announcement_id' => $announcement->id,
                'vendor_id' => $vendorId,
                'proposed_price' => $request->input('proposed_price'),
                'proposal_text' => $request->input('proposal_text'),
                // 'proposal_file_path' => null, // 파일 업로드 로직 구현 후 추가
                'status' => 'submitted', // 초기 상태는 'submitted'
            ]);

            // (선택 사항) 대행사 담당자에게 제안서 제출 알림 로직 추가

            DB::commit();
            return response()->json([
                'message' => '제안서가 성공적으로 제출되었습니다.',
                'proposal' => $proposal,
            ], 201); // 201 Created

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '제안서 제출 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 특정 공고에 제출된 제안서 목록 조회 (GET /api/announcements/{announcement}/proposals)
     * (대행사 또는 관리자만 접근 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Announcement $announcement)
    {
        $user = Auth::user();

        // 1. 권한 확인: 공고를 올린 대행사의 멤버이거나 관리자만 접근 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            if (($user->agency_members->first()->agency_id ?? null) === $announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 공고의 제안서 목록에 접근할 권한이 없습니다.'], 403);
        }

        // 2. 제안서 목록 조회
        $proposals = Proposal::where('announcement_id', $announcement->id)
                             ->with('vendor.masterUser') // 제안한 용역사 정보와 마스터 사용자 정보 함께 로드
                             ->orderBy('created_at', 'asc')
                             ->paginate(10); // 페이지네이션

        return response()->json([
            'message' => '제안서 목록을 성공적으로 불러왔습니다.',
            'proposals' => $proposals,
        ], 200);
    }

    /**
     * 특정 제안서 상세 조회 (GET /api/proposals/{proposal})
     *
     * @param  \App\Models\Proposal  $proposal (모델 바인딩)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Proposal $proposal)
    {
        $user = Auth::user();

        // 1. 공고 관련자만 접근 가능하도록 권한 확인
        $hasAccess = false;

        // 플랫폼 관리자 (admin)는 모든 제안서 접근 가능
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        }
        // 공고를 올린 대행사의 멤버만 접근 가능
        elseif ($user->user_type === 'agency_member') {
            // 제안서와 연결된 공고의 대행사 ID와 사용자 소속 대행사 ID 비교
            $announcementAgencyId = $proposal->announcement->agency_id;
            if (($user->agency_members->first()->agency_id ?? null) === $announcementAgencyId) {
                $hasAccess = true;
            }
        }
        // 제안서를 제출한 용역사 본인만 접근 가능
        elseif ($user->user_type === 'vendor_member') {
            // 제안서의 vendor_id와 현재 로그인한 용역사의 vendor_id 비교
            $loggedInVendorId = $user->vendor_members->first()->vendor_id ?? null;
            if ($loggedInVendorId === $proposal->vendor_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 제안서에 접근할 권한이 없습니다.'], 403);
        }

        // 2. 제안서 상세 정보 로드 (필요한 관계와 함께)
        $proposal->load('announcement.agency', 'vendor.masterUser'); // 공고, 대행사, 용역사, 마스터 사용자 정보 로드

        return response()->json([
            'message' => '제안서 상세 정보를 성공적으로 불러왔습니다.',
            'proposal' => $proposal,
        ], 200);
    }

    /**
     * 제안서 낙찰 처리 (POST /api/proposals/{proposal}/award)
     * (대행사 또는 관리자만 가능)
     *
     * @OA\Post(
     *     path="/api/proposals/{proposal}/award",
     *     tags={"Proposal Management"},
     *     summary="제안서 낙찰",
     *     description="특정 제안서를 낙찰 처리합니다. 계약이 자동으로 생성되고 다른 제안서들은 유찰 처리됩니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="proposal",
     *         in="path",
     *         required=true,
     *         description="제안서 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="final_price", type="number", example=40000000, description="최종 계약 금액")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="낙찰 처리 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="제안서가 성공적으로 낙찰되었습니다."),
     *             @OA\Property(property="proposal", type="object"),
     *             @OA\Property(property="contract", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="final_price", type="number"),
     *                 @OA\Property(property="payment_status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="제안서를 낙찰할 권한이 없습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="상태 충돌",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="현재 상태에서는 제안서를 낙찰할 수 없습니다.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function award(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 공고를 올린 대행사 멤버만 낙찰 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            if (($user->agency_members->first()->agency_id ?? null) === $proposal->announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '제안서를 낙찰할 권한이 없습니다.'], 403);
        }

        // 2. 제안서 상태 확인: 'submitted' 또는 'under_review' 상태에서만 낙찰 가능
        if (!in_array($proposal->status, ['submitted', 'under_review'])) {
            return response()->json(['message' => '현재 상태에서는 제안서를 낙찰할 수 없습니다.'], 409); // 409 Conflict
        }

        // 3. (중요) 해당 공고에 이미 낙찰된 제안서가 있는지 확인
        if ($proposal->announcement->proposals()->where('status', 'awarded')->exists()) {
            return response()->json(['message' => '이 공고에는 이미 낙찰된 제안서가 있습니다.'], 409);
        }

        // 4. 요청 유효성 검사
        $request->validate([
            'final_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 5. 제안서 상태를 'awarded'로 변경
            $proposal->status = 'awarded';
            $proposal->save();

            // 6. Contracts 테이블에 계약 레코드 생성
            $finalPrice = $request->input('final_price') ?? $proposal->proposed_price;
            
            $contract = Contract::create([
                'announcement_id' => $proposal->announcement_id,
                'proposal_id' => $proposal->id,
                'vendor_id' => $proposal->vendor_id,
                'final_price' => $finalPrice,
                'contract_file_path' => null, // 계약서 파일은 추후 업로드
                'contract_signed_at' => null,
                'prepayment_amount' => $finalPrice * 0.3, // 30% 선금 (기본값)
                'balance_amount' => $finalPrice * 0.7, // 70% 잔금 (기본값)
                'payment_status' => 'pending',
            ]);

            // 7. 같은 공고의 다른 제안서들은 자동으로 'rejected'로 처리 (강화된 로직)
            $proposal->announcement->proposals()
                     ->where('id', '!=', $proposal->id)
                     ->whereIn('status', ['submitted', 'under_review']) // 제출 또는 검토 중인 것만 유찰
                     ->update(['status' => 'rejected']);

            // 8. 공고 상태를 'closed'로 변경 (더 이상 제안서 받지 않음)
            $proposal->announcement->status = 'closed';
            $proposal->announcement->save();

            DB::commit();
            return response()->json([
                'message' => '제안서가 성공적으로 낙찰되었습니다.',
                'proposal' => $proposal,
                'contract' => $contract,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '제안서 낙찰 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 제안서 유찰 처리 (POST /api/proposals/{proposal}/reject)
     * (대행사 또는 관리자만 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 공고를 올린 대행사 멤버만 유찰 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            if (($user->agency_members->first()->agency_id ?? null) === $proposal->announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '제안서를 유찰할 권한이 없습니다.'], 403);
        }

        // 2. 계약 존재 확인: 이미 계약이 생성된 제안서는 상태 변경 불가 (우선 순위)
        if (Contract::where('proposal_id', $proposal->id)->exists()) {
            return response()->json(['message' => '이미 계약이 체결된 제안서는 상태를 변경할 수 없습니다.'], 409);
        }

        // 3. 제안서 상태 확인: 'submitted' 또는 'under_review' 상태에서만 유찰 가능
        if (!in_array($proposal->status, ['submitted', 'under_review'])) {
            return response()->json(['message' => '현재 상태에서는 제안서를 유찰할 수 없습니다.'], 409); // 409 Conflict
        }

        DB::beginTransaction();
        try {
            // 3. 제안서 상태를 'rejected'로 변경
            $proposal->status = 'rejected';
            $proposal->save();

            // 만약 이 제안서가 예비 순위였다면, 해당 순위는 비워집니다.
            // (추후 예비 순위 자동 조정 로직 추가 가능)

            DB::commit();
            return response()->json([
                'message' => '제안서가 성공적으로 유찰되었습니다.',
                'proposal' => $proposal,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '제안서 유찰 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 제안서의 예비 순위 설정 (PATCH /api/proposals/{proposal}/set-reserve-rank)
     * (대행사 또는 관리자만 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function setReserveRank(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 공고를 올린 대행사 멤버만 예비 순위 설정 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            if (($user->agency_members->first()->agency_id ?? null) === $proposal->announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '예비 순위를 설정할 권한이 없습니다.'], 403);
        }

        // 2. 제안서 상태 확인: 'awarded' 상태에서는 예비 순위 설정 불가
        if ($proposal->status === 'awarded') {
            return response()->json(['message' => '낙찰된 제안서에는 예비 순위를 설정할 수 없습니다.'], 409);
        }

        // 3. 유효성 검사
        $request->validate([
            'reserve_rank' => [
                'nullable', // null을 허용하여 예비 순위 해제 가능
                'integer',
                'min:1',
            ],
        ]);

        // 4. 중복된 예비 순위 검사
        $reserveRank = $request->input('reserve_rank');
        if ($reserveRank !== null) {
            $existingProposal = Proposal::where('announcement_id', $proposal->announcement_id)
                                      ->where('reserve_rank', $reserveRank)
                                      ->where('id', '!=', $proposal->id)
                                      ->first();
            
            if ($existingProposal) {
                return response()->json(['message' => '해당 예비 순위는 이미 다른 제안서에 설정되어 있습니다.'], 409);
            }
        }

        DB::beginTransaction();
        try {
            // 3. 제안서의 예비 순위 업데이트
            $proposal->reserve_rank = $request->input('reserve_rank');
            $proposal->save();

            DB::commit();
            return response()->json([
                'message' => '제안서의 예비 순위가 성공적으로 설정되었습니다.',
                'proposal' => $proposal,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => '유효성 검사 오류',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '예비 순위 설정 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 예비 제안서를 정식 낙찰자로 승격 (POST /api/proposals/{proposal}/promote-from-reserve)
     * (대행사 또는 관리자만 가능)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal  (승격 대상 예비 제안서)
     * @return \Illuminate\Http\JsonResponse
     */
    public function promoteFromReserve(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 공고를 올린 대행사 멤버만 승격 가능
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            if (($user->agency_members->first()->agency_id ?? null) === $proposal->announcement->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 제안서를 승격할 권한이 없습니다.'], 403);
        }

        // 2. 승격 대상 제안서의 상태 및 순위 확인
        if ($proposal->reserve_rank === null || !in_array($proposal->status, ['submitted', 'under_review', 'rejected'])) {
            // 예비 순위가 아니거나 이미 낙찰된 상태 등 승격 불가 상태인 경우
            return response()->json(['message' => '이 제안서는 승격할 수 있는 예비 후보가 아닙니다.'], 409); // 409 Conflict
        }
        
        // 3. (선택 사항) 요청 시 이전 낙찰자 계약 파기 사유 등 받을 수 있음
        $request->validate([
            'rejection_reason_for_previous_winner' => 'nullable|string|max:1000',
            'final_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 4. 같은 공고 내의 기존 낙찰자 처리
            // 가장 최근 낙찰된 제안서가 있다면 상태를 변경
            $existingAwardedProposal = $proposal->announcement->proposals()
                                                ->where('status', 'awarded')
                                                ->first();
            if ($existingAwardedProposal) {
                $existingAwardedProposal->status = 'rejected'; // 이전 낙찰자는 'rejected'로 변경
                $existingAwardedProposal->save();
                
                // 기존 계약도 상태 업데이트
                $existingContract = Contract::where('proposal_id', $existingAwardedProposal->id)->first();
                if ($existingContract) {
                    $existingContract->payment_status = 'pending'; // 기존 계약을 pending 상태로 변경 (terminated는 enum에 없음)
                    $existingContract->save();
                }
            }

            // 5. 예비 제안서 상태를 'awarded'로 변경
            $proposal->status = 'awarded';
            $proposal->reserve_rank = null; // 정식 낙찰되었으므로 예비 순위 해제
            $proposal->save();

            // 6. Contracts 테이블에 새로운 계약 레코드 생성 (승격된 제안서 기준)
            $finalPrice = $request->input('final_price') ?? $proposal->proposed_price;
            
            $contract = Contract::create([
                'announcement_id' => $proposal->announcement_id,
                'proposal_id' => $proposal->id,
                'vendor_id' => $proposal->vendor_id,
                'final_price' => $finalPrice,
                'contract_file_path' => null,
                'contract_signed_at' => null,
                'prepayment_amount' => $finalPrice * 0.3, // 30% 선금 (기본값)
                'balance_amount' => $finalPrice * 0.7, // 70% 잔금 (기본값)
                'payment_status' => 'pending', // 새로운 계약은 'pending'으로 시작
            ]);

            // 7. 공고 상태를 'closed'로 재변경 (새로운 낙찰자가 생겼으니 다시 마감)
            $proposal->announcement->status = 'closed';
            $proposal->announcement->save();

            DB::commit();
            return response()->json([
                'message' => '예비 제안서가 성공적으로 정식 낙찰자로 승격되었습니다.',
                'proposal' => $proposal,
                'contract' => $contract,
                'previous_winner_rejection_reason' => $request->input('rejection_reason_for_previous_winner'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '제안서 승격 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
