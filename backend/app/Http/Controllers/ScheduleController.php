<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ScheduleLog;
use App\Models\Project;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * 스케줄 목록 조회 (GET /api/schedules)
     *
     * @OA\Get(
     *     path="/api/schedules",
     *     tags={"Schedule Management"},
     *     summary="스케줄 목록 조회",
     *     description="사용자별, 기간별, 타입별로 스케줄 목록을 조회합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="schedulable_type",
     *         in="query",
     *         description="스케줄 타입 (App\\Models\\Project, App\\Models\\Announcement, App\\Models\\Contract)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="schedulable_id",
     *         in="query",
     *         description="스케줄 대상 ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="조회 시작 날짜 (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="조회 종료 날짜 (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="my_schedules_only",
     *         in="query",
     *         description="내 일정만 조회 (true/false)",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="스케줄 활동 타입",
     *         @OA\Schema(type="string", enum={"meeting","site_visit","preparation","event_execution","cleanup","evaluation","contract_signing","other"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="스케줄 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="스케줄 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="schedules",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="schedulable_type", type="string"),
     *                     @OA\Property(property="schedulable_id", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="total_count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음")
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 기본 쿼리 빌더
        $query = Schedule::query();

        // 사용자별 권한에 따른 필터링
        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if (!$userAgencyId) {
                return response()->json(['message' => '소속 대행사 정보를 찾을 수 없습니다.'], 403);
            }

            // 대행사 멤버는 자신의 대행사와 관련된 스케줄만 조회
            $query->where(function ($q) use ($userAgencyId) {
                $q->whereHasMorph('schedulable', ['App\\Models\\Project'], function ($subQuery) use ($userAgencyId) {
                    $subQuery->where('agency_id', $userAgencyId);
                })
                ->orWhereHasMorph('schedulable', ['App\\Models\\Announcement'], function ($subQuery) use ($userAgencyId) {
                    $subQuery->where('agency_id', $userAgencyId);
                })
                ->orWhereHasMorph('schedulable', ['App\\Models\\Contract'], function ($subQuery) use ($userAgencyId) {
                    $subQuery->whereHas('proposal.announcement', function ($announcementQuery) use ($userAgencyId) {
                        $announcementQuery->where('agency_id', $userAgencyId);
                    });
                });
            });
        } elseif ($user->user_type === 'vendor_member') {
            $userVendorId = $user->vendor_members->first()->vendor_id ?? null;
            if (!$userVendorId) {
                return response()->json(['message' => '소속 용역사 정보를 찾을 수 없습니다.'], 403);
            }

            // 용역사 멤버는 자신의 용역사와 관련된 스케줄만 조회
            $query->whereHasMorph('schedulable', ['App\\Models\\Contract'], function ($subQuery) use ($userVendorId) {
                $subQuery->whereHas('proposal', function ($proposalQuery) use ($userVendorId) {
                    $proposalQuery->where('vendor_id', $userVendorId);
                });
            });
        }
        // admin은 모든 스케줄 조회 가능

        // 내 일정만 조회 필터
        if ($request->query('my_schedules_only', false)) {
            // 현재 사용자가 관련된 스케줄만 조회 (구체적인 로직은 비즈니스 요구사항에 따라 조정)
            // 예: 사용자가 담당자로 지정된 프로젝트/공고/계약의 스케줄
        }

        // 스케줄 타입 필터
        if ($request->has('schedulable_type')) {
            $query->where('schedulable_type', $request->schedulable_type);
        }

        // 스케줄 대상 ID 필터
        if ($request->has('schedulable_id')) {
            $query->where('schedulable_id', $request->schedulable_id);
        }

        // 기간 필터
        if ($request->has('start_date')) {
            $startDate = $request->start_date . ' 00:00:00';
            $query->where('scheduled_at', '>=', $startDate);
        }

        if ($request->has('end_date')) {
            $endDate = $request->end_date . ' 23:59:59';
            $query->where('scheduled_at', '<=', $endDate);
        }

        // 활동 타입 필터
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // 관련 모델과 함께 로드
        $schedules = $query->with(['schedulable', 'attachments'])
                          ->orderBy('scheduled_at', 'asc')
                          ->get();

        // 응답 데이터 포맷팅
        $formattedSchedules = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => $schedule->description,
                'scheduled_at' => $schedule->scheduled_at,
                'type' => $schedule->type,
                'status' => $schedule->status,
                'schedulable_type' => $schedule->schedulable_type,
                'schedulable_id' => $schedule->schedulable_id,
                'schedulable' => $schedule->schedulable,
                'attachments_count' => $schedule->attachments->count(),
                'created_at' => $schedule->created_at,
                'updated_at' => $schedule->updated_at
            ];
        });

        return response()->json([
            'message' => '스케줄 목록을 성공적으로 불러왔습니다.',
            'schedules' => $formattedSchedules,
            'total_count' => $formattedSchedules->count()
        ]);
    }

    /**
     * 새 스케줄 생성 (POST /api/schedules)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 유효성 검사
        $request->validate([
            'schedulable_type' => ['required', 'string', Rule::in(['App\\Models\\Project', 'App\\Models\\Announcement'])],
            'schedulable_id' => 'required|uuid',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'location' => 'nullable|string|max:255',
            'status' => ['nullable', 'string', Rule::in(['planned', 'ongoing', 'completed', 'cancelled'])],
            'type' => ['required', 'string', Rule::in([
                'meeting', 'delivery', 'installation', 'dismantling', 'rehearsal',
                'event_execution', 'setup', 'testing', 'load_in', 'load_out',
                'storage', 'breakdown', 'cleaning', 'training', 'briefing', 'pickup', 'transportation',
                'site_visit', 'concept_meeting', 'technical_rehearsal', 'dress_rehearsal', 'final_inspection', 'wrap_up'
            ])],
        ]);

        // 대상 엔티티 확인 및 권한 검사
        $schedulableType = $request->schedulable_type;
        $schedulableId = $request->schedulable_id;
        
        if ($schedulableType === 'App\\Models\\Project') {
            $entity = Project::find($schedulableId);
            if (!$entity) {
                return response()->json(['message' => '존재하지 않는 프로젝트입니다.'], 404);
            }
            
            // 권한 확인: 관리자 또는 해당 대행사 멤버만 생성 가능
            if ($user->user_type !== 'admin' && 
                ($user->user_type !== 'agency_member' || 
                 ($user->agency_members->first()->agency_id ?? null) !== $entity->agency_id)) {
                return response()->json(['message' => '이 프로젝트에 스케줄을 생성할 권한이 없습니다.'], 403);
            }
        } elseif ($schedulableType === 'App\\Models\\Announcement') {
            $entity = Announcement::find($schedulableId);
            if (!$entity) {
                return response()->json(['message' => '존재하지 않는 공고입니다.'], 404);
            }
            
            // 권한 확인: 관리자 또는 해당 대행사 멤버만 생성 가능
            if ($user->user_type !== 'admin' && 
                ($user->user_type !== 'agency_member' || 
                 ($user->agency_members->first()->agency_id ?? null) !== $entity->agency_id)) {
                return response()->json(['message' => '이 공고에 스케줄을 생성할 권한이 없습니다.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $schedule = Schedule::create([
                'schedulable_type' => $schedulableType,
                'schedulable_id' => $schedulableId,
                'title' => $request->title,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'location' => $request->location,
                'status' => $request->status ?? 'planned',
                'type' => $request->type,
            ]);

            // --- NEW: 로그 기록 ---
            ScheduleLog::create([
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'action' => 'created',
                'old_values' => null,
                'new_values' => $schedule->toArray(), // 새로 생성된 스케줄의 모든 데이터
            ]);
            // --- NEW: 로그 기록 끝 ---

            $schedule->load('schedulable');

            DB::commit();
            return response()->json([
                'message' => '스케줄이 성공적으로 생성되었습니다.',
                'schedule' => $schedule,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '스케줄 생성 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 특정 스케줄 상세 조회 (GET /api/schedules/{schedule})
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Schedule $schedule)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $agencyId = $user->agency_members->first()->agency_id ?? null;
            
            if ($schedule->schedulable_type === 'App\\Models\\Project') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            } elseif ($schedule->schedulable_type === 'App\\Models\\Announcement') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 스케줄에 접근할 권한이 없습니다.'], 403);
        }

        $schedule->load('schedulable');

        return response()->json([
            'message' => '스케줄 상세 정보를 성공적으로 불러왔습니다.',
            'schedule' => $schedule,
        ], 200);
    }

    /**
     * 스케줄 수정 (PUT /api/schedules/{schedule})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Schedule $schedule)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $agencyId = $user->agency_members->first()->agency_id ?? null;
            
            if ($schedule->schedulable_type === 'App\\Models\\Project') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            } elseif ($schedule->schedulable_type === 'App\\Models\\Announcement') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 스케줄을 수정할 권한이 없습니다.'], 403);
        }

        // 유효성 검사
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'sometimes|required|date',
            'end_datetime' => 'sometimes|required|date|after:start_datetime',
            'location' => 'nullable|string|max:255',
            'status' => ['nullable', 'string', Rule::in(['planned', 'ongoing', 'completed', 'cancelled'])],
            'type' => ['sometimes', 'required', 'string', Rule::in([
                'meeting', 'delivery', 'installation', 'dismantling', 'rehearsal',
                'event_execution', 'setup', 'testing', 'load_in', 'load_out',
                'storage', 'breakdown', 'cleaning', 'training', 'briefing', 'pickup', 'transportation',
                'site_visit', 'concept_meeting', 'technical_rehearsal', 'dress_rehearsal', 'final_inspection', 'wrap_up'
            ])],
        ]);

        DB::beginTransaction();
        try {
            // --- NEW: 로그 기록을 위한 변경 전 데이터 캡처 ---
            $oldValues = $schedule->toArray();
            // --- NEW: 로그 기록을 위한 변경 전 데이터 캡처 끝 ---

            $schedule->update($request->only([
                'title', 'description', 'start_datetime', 'end_datetime', 'location', 'status', 'type'
            ]));

            // --- NEW: 로그 기록 ---
            ScheduleLog::create([
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'action' => 'updated',
                'old_values' => $oldValues,
                'new_values' => $schedule->toArray(), // 업데이트 후 스케줄의 모든 데이터
            ]);
            // --- NEW: 로그 기록 끝 ---

            $schedule->load('schedulable');

            DB::commit();
            return response()->json([
                'message' => '스케줄이 성공적으로 수정되었습니다.',
                'schedule' => $schedule,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '스케줄 수정 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 스케줄 삭제 (DELETE /api/schedules/{schedule})
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Schedule $schedule)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $agencyId = $user->agency_members->first()->agency_id ?? null;
            
            if ($schedule->schedulable_type === 'App\\Models\\Project') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            } elseif ($schedule->schedulable_type === 'App\\Models\\Announcement') {
                $hasAccess = $schedule->schedulable->agency_id === $agencyId;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '이 스케줄을 삭제할 권한이 없습니다.'], 403);
        }

        DB::beginTransaction();
        try {
            // --- NEW: 로그 기록 (삭제 전 데이터) ---
            ScheduleLog::create([
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'action' => 'deleted',
                'old_values' => $schedule->toArray(), // 삭제될 스케줄의 모든 데이터
                'new_values' => null,
            ]);
            // --- NEW: 로그 기록 끝 ---

            $schedule->delete();
            
            DB::commit();
            return response()->json([
                'message' => '스케줄이 성공적으로 삭제되었습니다.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '스케줄 삭제 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 내 일정 조회 (GET /api/my-schedules)
     *
     * @OA\Get(
     *     path="/api/my-schedules",
     *     tags={"Schedule Management"},
     *     summary="내 일정 조회",
     *     description="현재 사용자와 관련된 모든 일정을 조회합니다. 대시보드에서 활용됩니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="조회 시작 날짜 (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="조회 종료 날짜 (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="내 일정 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="내 일정을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="schedules",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="related_project", type="string", description="관련 프로젝트명"),
     *                     @OA\Property(property="my_role", type="string", description="나의 역할")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음")
     * )
     */
    public function mySchedules(Request $request)
    {
        $user = Auth::user();
        $query = Schedule::query();

        // 사용자 타입별 내 일정 조회 로직
        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if (!$userAgencyId) {
                return response()->json(['message' => '소속 대행사 정보를 찾을 수 없습니다.'], 403);
            }

            // 내가 담당자로 지정된 프로젝트의 스케줄
            $query->where(function ($q) use ($user, $userAgencyId) {
                // 내가 주담당자 또는 부담당자인 프로젝트
                $q->whereHasMorph('schedulable', ['App\\Models\\Project'], function ($subQuery) use ($user) {
                    $subQuery->where(function ($projectQuery) use ($user) {
                        $projectQuery->where('main_agency_contact_user_id', $user->id)
                                   ->orWhere('sub_agency_contact_user_id', $user->id);
                    });
                })
                // 내가 심사위원으로 배정된 공고
                ->orWhereHasMorph('schedulable', ['App\\Models\\Announcement'], function ($subQuery) use ($user) {
                    $subQuery->whereHas('evaluators', function ($evaluatorQuery) use ($user) {
                        $evaluatorQuery->where('evaluator_user_id', $user->id);
                    });
                })
                // 내가 관련된 계약
                ->orWhereHasMorph('schedulable', ['App\\Models\\Contract'], function ($subQuery) use ($userAgencyId) {
                    $subQuery->whereHas('proposal.announcement', function ($announcementQuery) use ($userAgencyId) {
                        $announcementQuery->where('agency_id', $userAgencyId);
                    });
                });
            });

        } elseif ($user->user_type === 'vendor_member') {
            $userVendorId = $user->vendor_members->first()->vendor_id ?? null;
            if (!$userVendorId) {
                return response()->json(['message' => '소속 용역사 정보를 찾을 수 없습니다.'], 403);
            }

            // 내 용역사의 제안서/계약 관련 스케줄
            $query->whereHasMorph('schedulable', ['App\\Models\\Contract'], function ($subQuery) use ($userVendorId) {
                $subQuery->whereHas('proposal', function ($proposalQuery) use ($userVendorId) {
                    $proposalQuery->where('vendor_id', $userVendorId);
                });
            });
        }

        // 기간 필터
        if ($request->has('start_date')) {
            $startDate = $request->start_date . ' 00:00:00';
            $query->where('scheduled_at', '>=', $startDate);
        }

        if ($request->has('end_date')) {
            $endDate = $request->end_date . ' 23:59:59';
            $query->where('scheduled_at', '<=', $endDate);
        }

        $schedules = $query->with(['schedulable'])
                          ->orderBy('scheduled_at', 'asc')
                          ->get();

        // 응답 데이터 포맷팅 (내 역할 정보 포함)
        $formattedSchedules = $schedules->map(function ($schedule) use ($user) {
            $relatedProject = null;
            $myRole = null;

            if ($schedule->schedulable_type === 'App\\Models\\Project') {
                $project = $schedule->schedulable;
                $relatedProject = $project->project_name;
                if ($project->main_agency_contact_user_id === $user->id) {
                    $myRole = '주담당자';
                } elseif ($project->sub_agency_contact_user_id === $user->id) {
                    $myRole = '부담당자';
                }
            } elseif ($schedule->schedulable_type === 'App\\Models\\Announcement') {
                $announcement = $schedule->schedulable;
                $relatedProject = $announcement->rfp->project->project_name ?? '알 수 없음';
                $myRole = '심사위원';
            } elseif ($schedule->schedulable_type === 'App\\Models\\Contract') {
                $contract = $schedule->schedulable;
                $relatedProject = $contract->proposal->announcement->rfp->project->project_name ?? '알 수 없음';
                $myRole = $user->user_type === 'agency_member' ? '대행사 담당자' : '용역사 담당자';
            }

            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => $schedule->description,
                'scheduled_at' => $schedule->scheduled_at,
                'type' => $schedule->type,
                'status' => $schedule->status,
                'related_project' => $relatedProject,
                'my_role' => $myRole,
                'schedulable_type' => $schedule->schedulable_type,
                'schedulable_id' => $schedule->schedulable_id
            ];
        });

        return response()->json([
            'message' => '내 일정을 성공적으로 불러왔습니다.',
            'schedules' => $formattedSchedules,
            'total_count' => $formattedSchedules->count()
        ]);
    }
}
