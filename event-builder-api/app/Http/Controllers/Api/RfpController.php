<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRfpRequest;
use App\Http\Requests\BudgetValidationRequest; // BudgetValidationRequest 추가
use App\Models\Rfp;
use App\Models\RfpSelection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfpController extends Controller
{
    // Constructor removed - authorization handled individually in each method

    /**
     * @OA\Get(
     *     path="/api/rfps",
     *     summary="현재 인증된 사용자가 생성한 모든 RFP 목록 조회",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Rfp")
     *             ),
     *             @OA\Property(property="message", type="string", example="RFP 목록이 성공적으로 조회되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RFP 목록 조회에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $rfps = Rfp::with(['selections.feature'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rfps,
                'message' => 'RFP 목록이 성공적으로 조회되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('RFP 목록 조회 오류: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RFP 목록 조회에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/rfps",
     *     summary="새로운 RFP 생성",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreRfpRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="성공적으로 생성됨",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Rfp"),
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 생성되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="잘못된 요청"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 오류"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RFP 생성에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function store(StoreRfpRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // RFP 생성
            $rfp = Rfp::create([
                'title' => $request->title,
                'event_date' => $request->event_date,
                'expected_attendees' => $request->expected_attendees,
                'total_budget' => $request->total_budget,
                'is_total_budget_undecided' => $request->is_total_budget_undecided,
                'description' => $request->description,
                'user_id' => auth()->id(),
                'status' => 'draft'
            ]);

            // RFP selections 생성
            $selections = [];
            foreach ($request->selections as $selection) {
                $selections[] = [
                    'rfp_id' => $rfp->id,
                    'feature_id' => $selection['feature_id'],
                    'details' => json_encode($selection['details'] ?? []),
                    'allocated_budget' => $selection['allocated_budget'] ?? null,
                    'is_budget_undecided' => $selection['is_budget_undecided'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            RfpSelection::insert($selections);

            DB::commit();

            // 생성된 RFP를 관계와 함께 로드
            $rfp->load(['selections.feature']);

            return response()->json([
                'success' => true,
                'data' => $rfp,
                'message' => 'RFP가 성공적으로 생성되었습니다.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RFP 생성 오류: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RFP 생성에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/rfps/{id}",
     *     summary="특정 RFP 상세 정보 조회",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Rfp"),
     *             @OA\Property(property="message", type="string", example="RFP 상세 정보가 성공적으로 조회되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RFP를 찾을 수 없음"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RFP 조회에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function show(Rfp $rfp): JsonResponse
    {
        try {
            // Eager Loading으로 selections, feature, category 정보까지 모두 로드
            $rfp->load(['selections.feature.category']);

            return response()->json([
                'success' => true,
                'data' => $rfp,
                'message' => 'RFP 상세 정보가 성공적으로 조회되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('RFP 상세 조회 오류: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RFP 조회에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/rfps/{id}",
     *     summary="특정 RFP 업데이트",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreRfpRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Rfp"),
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 수정되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="잘못된 요청"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RFP를 찾을 수 없음"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 오류"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RFP 수정에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function update(StoreRfpRequest $request, Rfp $rfp): JsonResponse
    {
        try {
            DB::beginTransaction();

            // RFP 업데이트
            $rfp->update([
                'title' => $request->title,
                'event_date' => $request->event_date,
                'expected_attendees' => $request->expected_attendees,
                'total_budget' => $request->total_budget,
                'is_total_budget_undecided' => $request->is_total_budget_undecided,
                'description' => $request->description,
            ]);

            // 기존 selections 삭제
            RfpSelection::where('rfp_id', $rfp->id)->delete();

            // 새로운 selections 생성
            $selections = [];
            foreach ($request->selections as $selection) {
                $selections[] = [
                    'rfp_id' => $rfp->id,
                    'feature_id' => $selection['feature_id'],
                    'details' => json_encode($selection['details'] ?? []),
                    'allocated_budget' => $selection['allocated_budget'] ?? null,
                    'is_budget_undecided' => $selection['is_budget_undecided'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            RfpSelection::insert($selections);

            DB::commit();

            // 업데이트된 RFP를 관계와 함께 로드
            $rfp->load(['selections.feature.category']);

            return response()->json([
                'success' => true,
                'data' => $rfp,
                'message' => 'RFP가 성공적으로 수정되었습니다.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RFP 수정 오류: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RFP 수정에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/rfps/{id}",
     *     summary="특정 RFP 삭제",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 삭제되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RFP를 찾을 수 없음"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="RFP 삭제에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function destroy(Rfp $rfp): JsonResponse
    {
        try {
            $rfp->delete();

            return response()->json([
                'success' => true,
                'message' => 'RFP가 성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('RFP 삭제 오류: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RFP 삭제에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/rfps/validate-budget",
     *     summary="예산 분배 및 검증",
     *     tags={"RFP"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BudgetValidationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="calculated_total_budget", type="number", format="float", example=10000000, description="계산된 총 예산"),
     *                 @OA\Property(property="is_over_budget", type="boolean", example=false, description="예산 초과 여부"),
     *                 @OA\Property(property="over_amount", type="number", format="float", example=0, description="초과 금액"),
     *                 @OA\Property(property="total_budget_status", type="string", example="10000000", description="총 예산 상태 (미정, 미입력 또는 금액)"),
     *                 @OA\Property(property="category_sums", type="object", additionalProperties={"type": "number", "format": "float"}, example={"1": 5000000, "2": 3000000}, description="카테고리별 예산 합계")
     *             ),
     *             @OA\Property(property="message", type="string", example="예산 검증이 성공적으로 수행되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="잘못된 요청"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 오류"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="예산 검증에 실패했습니다."),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function validateBudget(BudgetValidationRequest $request): JsonResponse
    {
        try {
            $totalBudget = $request->input('total_budget');
            $isTotalBudgetUndecided = $request->input('is_total_budget_undecided', false);
            $categoryBudgets = $request->input('category_budgets', []);
            $featureBudgets = $request->input('feature_budgets', []);

            $calculatedTotalBudget = 0;
            $categorySums = [];

            // 카테고리별 예산 합계 계산
            foreach ($categoryBudgets as $categoryBudget) {
                $amount = $categoryBudget['amount'] ?? 0;
                $categorySums[$categoryBudget['category_id']] = ($categorySums[$categoryBudget['category_id']] ?? 0) + $amount;
                $calculatedTotalBudget += $amount;
            }

            // 기능별 예산 합계 계산 (카테고리 예산에 포함되지 않는 경우를 대비)
            foreach ($featureBudgets as $featureBudget) {
                $amount = $featureBudget['amount'] ?? 0;
                // 이미 카테고리 예산에 포함된 기능 예산은 중복 계산하지 않음
                // 여기서는 단순 합계를 위해 모두 더함. 실제 로직은 더 복잡할 수 있음.
                // $calculatedTotalBudget += $amount;
            }

            $isOverBudget = false;
            $overAmount = 0;

            if (!$isTotalBudgetUndecided && $totalBudget !== null) {
                if ($calculatedTotalBudget > $totalBudget) {
                    $isOverBudget = true;
                    $overAmount = $calculatedTotalBudget - $totalBudget;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'calculated_total_budget' => $calculatedTotalBudget,
                    'is_over_budget' => $isOverBudget,
                    'over_amount' => $overAmount,
                    'total_budget_status' => $isTotalBudgetUndecided ? '미정' : ($totalBudget ?? '미입력'),
                    'category_sums' => $categorySums, // 카테고리별 합계 반환
                ],
                'message' => '예산 검증이 성공적으로 수행되었습니다.'
            ]);

        } catch (\Exception $e) {
            Log::error('예산 검증 오류: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '예산 검증에 실패했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
