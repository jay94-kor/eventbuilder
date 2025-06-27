<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Schedule;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Contract Meetings",
 *     description="계약 미팅 관리 API"
 * )
 */
class ContractMeetingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Post(
     *     path="/api/contracts/{contract}/propose-meeting-dates",
     *     tags={"Contract Meetings"},
     *     summary="미팅 일정 제안",
     *     description="대행사가 용역사에게 미팅 일정을 제안합니다",
     *     @OA\Parameter(
     *         name="contract",
     *         in="path",
     *         required=true,
     *         description="계약 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"proposed_dates"},
     *             @OA\Property(
     *                 property="proposed_dates",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="datetime", type="string", format="datetime"),
     *                     @OA\Property(property="location", type="string", example="회사 회의실"),
     *                     @OA\Property(property="note", type="string", example="계약 조건 협의")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="미팅 일정 제안 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="미팅 일정이 성공적으로 제안되었습니다"),
     *             @OA\Property(property="proposed_dates_count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=404, description="계약을 찾을 수 없습니다"),
     *     @OA\Response(response=422, description="유효하지 않은 입력 데이터")
     * )
     */
    public function proposeMeetingDates(Request $request, $contractId)
    {
        $validator = Validator::make($request->all(), [
            'proposed_dates' => 'required|array|min:1|max:5',
            'proposed_dates.*.datetime' => 'required|date|after:now',
            'proposed_dates.*.location' => 'nullable|string|max:255',
            'proposed_dates.*.note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '유효하지 않은 입력 데이터입니다',
                'errors' => $validator->errors()
            ], 422);
        }

        $contract = Contract::with(['announcement', 'vendor.vendorMembers.user'])
            ->findOrFail($contractId);

        DB::beginTransaction();
        try {
            // 제안된 미팅 일정 저장
            $contract->update([
                'proposed_meeting_dates' => $request->proposed_dates,
                'meeting_status' => 'dates_proposed',
            ]);

            // 용역사 마스터 계정에게 알림 발송
            $masterUser = $contract->vendor->vendorMembers()
                ->where('is_master', true)
                ->first()?->user;

            if ($masterUser) {
                $this->notificationService->sendMeetingDateProposedNotification(
                    $masterUser->id,
                    $contract->announcement->title,
                    $request->proposed_dates,
                    [
                        'contract_id' => $contract->id,
                        'announcement_id' => $contract->announcement->id,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => '미팅 일정이 성공적으로 제안되었습니다',
                'proposed_dates_count' => count($request->proposed_dates),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => '미팅 일정 제안 중 오류가 발생했습니다',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/contracts/{contract}/select-meeting-date",
     *     tags={"Contract Meetings"},
     *     summary="미팅 일정 선택",
     *     description="용역사가 제안된 미팅 일정 중 하나를 선택합니다",
     *     @OA\Parameter(
     *         name="contract",
     *         in="path",
     *         required=true,
     *         description="계약 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"selected_datetime"},
     *             @OA\Property(property="selected_datetime", type="string", format="datetime"),
     *             @OA\Property(property="location", type="string", example="회사 회의실"),
     *             @OA\Property(property="note", type="string", example="계약 조건 협의")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="미팅 일정 선택 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="미팅 일정이 성공적으로 확정되었습니다"),
     *             @OA\Property(property="schedule_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(response=404, description="계약을 찾을 수 없습니다"),
     *     @OA\Response(response=422, description="유효하지 않은 입력 데이터")
     * )
     */
    public function selectMeetingDate(Request $request, $contractId)
    {
        $validator = Validator::make($request->all(), [
            'selected_datetime' => 'required|date',
            'location' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '유효하지 않은 입력 데이터입니다',
                'errors' => $validator->errors()
            ], 422);
        }

        $contract = Contract::with(['announcement', 'proposal.vendor'])
            ->findOrFail($contractId);

        if ($contract->meeting_status !== 'dates_proposed') {
            return response()->json([
                'message' => '미팅 일정이 제안되지 않았거나 이미 선택되었습니다'
            ], 400);
        }

        // 제안된 일정 중에 선택한 일정이 있는지 확인
        $selectedDatetime = $request->selected_datetime;
        $proposedDates = $contract->proposed_meeting_dates;
        
        $isValidSelection = collect($proposedDates)->contains(function ($proposedDate) use ($selectedDatetime) {
            return $proposedDate['datetime'] === $selectedDatetime;
        });

        if (!$isValidSelection) {
            return response()->json([
                'message' => '제안된 일정 중에서 선택해주세요'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 선택된 미팅 일정 저장
            $contract->update([
                'selected_meeting_date' => $selectedDatetime,
                'meeting_status' => 'date_selected',
            ]);

            // 스케줄 생성
            $schedule = Schedule::create([
                'title' => '계약 미팅 - ' . $contract->proposal->vendor->company_name,
                'description' => $contract->announcement->title . ' 계약 관련 미팅',
                'start_datetime' => $selectedDatetime,
                'end_datetime' => date('Y-m-d H:i:s', strtotime($selectedDatetime . ' +2 hours')), // 기본 2시간
                'location' => $request->location,
                'type' => 'meeting',
                'created_by_user_id' => auth()->id(),
                'schedulable_type' => Contract::class,
                'schedulable_id' => $contract->id,
            ]);

            // 대행사 담당자들에게 알림 발송
            $project = $contract->announcement->rfp->project;
            $agencyUsers = collect([
                $project->main_agency_contact_user_id,
                $project->sub_agency_contact_user_id
            ])->filter();

            foreach ($agencyUsers as $userId) {
                if ($userId) {
                    $this->notificationService->sendMeetingDateSelectedNotification(
                        $userId,
                        $contract->announcement->title,
                        $selectedDatetime,
                        [
                            'contract_id' => $contract->id,
                            'schedule_id' => $schedule->id,
                            'location' => $request->location,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => '미팅 일정이 성공적으로 확정되었습니다',
                'schedule_id' => $schedule->id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => '미팅 일정 선택 중 오류가 발생했습니다',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contracts/{contract}/meeting-status",
     *     tags={"Contract Meetings"},
     *     summary="미팅 상태 조회",
     *     description="계약의 미팅 진행 상태를 조회합니다",
     *     @OA\Parameter(
     *         name="contract",
     *         in="path",
     *         required=true,
     *         description="계약 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="미팅 상태 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="contract_id", type="string", format="uuid"),
     *             @OA\Property(property="meeting_status", type="string", example="date_selected"),
     *             @OA\Property(property="proposed_meeting_dates", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="selected_meeting_date", type="string", format="datetime"),
     *             @OA\Property(property="schedule", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="계약을 찾을 수 없습니다")
     * )
     */
    public function getMeetingStatus($contractId)
    {
        $contract = Contract::with(['schedules' => function ($query) {
            $query->where('type', 'meeting');
        }])->findOrFail($contractId);

        return response()->json([
            'contract_id' => $contract->id,
            'meeting_status' => $contract->meeting_status,
            'proposed_meeting_dates' => $contract->proposed_meeting_dates,
            'selected_meeting_date' => $contract->selected_meeting_date,
            'schedule' => $contract->schedules->first(),
        ]);
    }
} 