<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Evaluation Steps",
 *     description="평가 단계 관리 API"
 * )
 */
class EvaluationStepController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Post(
     *     path="/api/announcements/{announcement}/evaluate-step",
     *     tags={"Evaluation Steps"},
     *     summary="평가 단계별 결과 처리",
     *     description="특정 평가 단계의 통과/탈락 결과를 처리하고 용역사에게 알림을 발송합니다",
     *     @OA\Parameter(
     *         name="announcement",
     *         in="path",
     *         required=true,
     *         description="공고 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"evaluation_step_name", "results"},
     *             @OA\Property(property="evaluation_step_name", type="string", example="1차 서류 심사"),
     *             @OA\Property(
     *                 property="results",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="proposal_id", type="string", format="uuid"),
     *                     @OA\Property(property="status", type="string", enum={"passed", "failed"})
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="평가 단계 결과 처리 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="평가 단계 결과가 성공적으로 처리되었습니다"),
     *             @OA\Property(property="processed_count", type="integer", example=5),
     *             @OA\Property(property="notifications_sent", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=404, description="공고를 찾을 수 없습니다"),
     *     @OA\Response(response=422, description="유효하지 않은 입력 데이터")
     * )
     */
    public function evaluateStep(Request $request, $announcementId)
    {
        $validator = Validator::make($request->all(), [
            'evaluation_step_name' => 'required|string|max:255',
            'results' => 'required|array|min:1',
            'results.*.proposal_id' => 'required|uuid|exists:proposals,id',
            'results.*.status' => 'required|in:passed,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '유효하지 않은 입력 데이터입니다',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::findOrFail($announcementId);
        $stepName = $request->evaluation_step_name;
        $results = $request->results;

        DB::beginTransaction();
        try {
            $processedCount = 0;
            $notificationsSent = 0;

            foreach ($results as $result) {
                $proposal = Proposal::with('vendor.vendorMembers.user')
                    ->findOrFail($result['proposal_id']);

                // 평가 과정 상태 업데이트
                $evaluationStatus = $proposal->evaluation_process_status ?? [];
                $evaluationStatus[$stepName] = $result['status'];
                
                $proposal->update([
                    'evaluation_process_status' => $evaluationStatus
                ]);

                // 용역사의 마스터 계정에게 알림 발송
                $masterUser = $proposal->vendor->vendorMembers()
                    ->where('is_master', true)
                    ->first()?->user;

                if ($masterUser) {
                    if ($result['status'] === 'passed') {
                        $this->notificationService->sendEvaluationStepPassedNotification(
                            $masterUser->id,
                            $stepName,
                            $announcement->title,
                            [
                                'announcement_id' => $announcement->id,
                                'proposal_id' => $proposal->id,
                            ]
                        );
                    } else {
                        $this->notificationService->sendEvaluationStepFailedNotification(
                            $masterUser->id,
                            $stepName,
                            $announcement->title,
                            [
                                'announcement_id' => $announcement->id,
                                'proposal_id' => $proposal->id,
                            ]
                        );
                    }
                    $notificationsSent++;
                }

                $processedCount++;
            }

            DB::commit();

            return response()->json([
                'message' => '평가 단계 결과가 성공적으로 처리되었습니다',
                'processed_count' => $processedCount,
                'notifications_sent' => $notificationsSent,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => '평가 단계 결과 처리 중 오류가 발생했습니다',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/announcements/{announcement}/randomize-proposal-order",
     *     tags={"Evaluation Steps"},
     *     summary="제안서 발표 순서 랜덤 배정",
     *     description="공고 마감 후 제안서들의 발표 순서를 랜덤으로 배정합니다",
     *     @OA\Parameter(
     *         name="announcement",
     *         in="path",
     *         required=true,
     *         description="공고 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="발표 순서 배정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="발표 순서가 성공적으로 배정되었습니다"),
     *             @OA\Property(
     *                 property="presentation_order",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="proposal_id", type="string", format="uuid"),
     *                     @OA\Property(property="vendor_name", type="string"),
     *                     @OA\Property(property="order", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="공고를 찾을 수 없습니다"),
     *     @OA\Response(response=400, description="아직 마감되지 않은 공고입니다")
     * )
     */
    public function randomizePresentationOrder($announcementId)
    {
        $announcement = Announcement::findOrFail($announcementId);

        if ($announcement->closing_at > now()) {
            return response()->json([
                'message' => '아직 마감되지 않은 공고입니다'
            ], 400);
        }

        $proposals = Proposal::where('announcement_id', $announcementId)
            ->with('vendor')
            ->get();

        if ($proposals->isEmpty()) {
            return response()->json([
                'message' => '제출된 제안서가 없습니다'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 랜덤 순서 생성
            $shuffledProposals = $proposals->shuffle();
            $presentationOrder = [];

            foreach ($shuffledProposals as $index => $proposal) {
                $order = $index + 1;
                $proposal->update(['presentation_order' => $order]);
                
                $presentationOrder[] = [
                    'proposal_id' => $proposal->id,
                    'vendor_name' => $proposal->vendor->company_name,
                    'order' => $order,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => '발표 순서가 성공적으로 배정되었습니다',
                'presentation_order' => $presentationOrder,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => '발표 순서 배정 중 오류가 발생했습니다',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/announcements/{announcement}/schedule-presentations",
     *     tags={"Evaluation Steps"},
     *     summary="발표 시간 할당 및 일정 생성",
     *     description="제안서별로 발표 시간을 할당하고 스케줄을 생성합니다",
     *     @OA\Parameter(
     *         name="announcement",
     *         in="path",
     *         required=true,
     *         description="공고 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"presentations"},
     *             @OA\Property(
     *                 property="presentations",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="proposal_id", type="string", format="uuid"),
     *                     @OA\Property(property="scheduled_at", type="string", format="datetime"),
     *                     @OA\Property(property="duration_minutes", type="integer", example=30)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="발표 일정 생성 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="발표 일정이 성공적으로 생성되었습니다"),
     *             @OA\Property(property="schedules_created", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=404, description="공고를 찾을 수 없습니다"),
     *     @OA\Response(response=422, description="유효하지 않은 입력 데이터")
     * )
     */
    public function schedulePresentations(Request $request, $announcementId)
    {
        $validator = Validator::make($request->all(), [
            'presentations' => 'required|array|min:1',
            'presentations.*.proposal_id' => 'required|uuid|exists:proposals,id',
            'presentations.*.scheduled_at' => 'required|date',
            'presentations.*.duration_minutes' => 'required|integer|min:1|max:180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '유효하지 않은 입력 데이터입니다',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::findOrFail($announcementId);
        $presentations = $request->presentations;

        DB::beginTransaction();
        try {
            $schedulesCreated = 0;

            foreach ($presentations as $presentation) {
                $proposal = Proposal::with('vendor')->findOrFail($presentation['proposal_id']);
                
                // 제안서에 발표 일정 정보 업데이트
                $proposal->update([
                    'presentation_scheduled_at' => $presentation['scheduled_at'],
                    'presentation_duration_minutes' => $presentation['duration_minutes'],
                ]);

                // 스케줄 생성
                $schedule = $announcement->schedules()->create([
                    'title' => $proposal->vendor->company_name . ' 제안서 발표',
                    'description' => $announcement->title . ' - ' . $proposal->vendor->company_name . ' 발표',
                    'start_datetime' => $presentation['scheduled_at'],
                    'end_datetime' => date('Y-m-d H:i:s', strtotime($presentation['scheduled_at'] . ' +' . $presentation['duration_minutes'] . ' minutes')),
                    'type' => 'presentation',
                    'created_by_user_id' => auth()->id(),
                ]);

                $schedulesCreated++;
            }

            DB::commit();

            return response()->json([
                'message' => '발표 일정이 성공적으로 생성되었습니다',
                'schedules_created' => $schedulesCreated,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => '발표 일정 생성 중 오류가 발생했습니다',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 