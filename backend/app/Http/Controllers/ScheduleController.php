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
     * 특정 엔티티(프로젝트/공고)의 스케줄 또는 전체 스케줄 조회
     *
     * @OA\Get(
     *     path="/api/schedules",
     *     tags={"Schedule Management"},
     *     summary="스케줄 목록 조회",
     *     description="프로젝트 또는 공고에 연결된 스케줄 목록을 조회합니다. 대행사 멤버는 자신의 대행사 스케줄만 조회할 수 있습니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="schedulable_type",
     *         in="query",
     *         description="스케줄이 연결된 엔티티 타입",
     *         @OA\Schema(type="string", enum={"App\\Models\\Project", "App\\Models\\Announcement"})
     *     ),
     *     @OA\Parameter(
     *         name="schedulable_id",
     *         in="query",
     *         description="스케줄이 연결된 엔티티 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="스케줄 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="스케줄 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="schedules", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="start_datetime", type="string", format="date-time"),
     *                         @OA\Property(property="end_datetime", type="string", format="date-time"),
     *                         @OA\Property(property="location", type="string"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="type", type="string"),
     *                         @OA\Property(property="schedulable", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="스케줄을 조회할 권한이 없습니다.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Schedule::query();

        // 특정 엔티티의 스케줄만 조회하는 경우
        if ($request->has('schedulable_type') && $request->has('schedulable_id')) {
            $query->where('schedulable_type', $request->schedulable_type)
                  ->where('schedulable_id', $request->schedulable_id);
        }

        // 권한에 따른 필터링
        if ($user->user_type === 'admin') {
            // 관리자는 모든 스케줄 조회 가능
        } elseif ($user->user_type === 'agency_member') {
            $agencyId = $user->agency_members->first()->agency_id ?? null;
            if (!$agencyId) {
                return response()->json(['message' => '소속된 대행사 정보를 찾을 수 없습니다.'], 403);
            }
            
            // 대행사 멤버는 자신의 대행사 프로젝트/공고 스케줄만 조회
            $query->where(function($q) use ($agencyId) {
                $q->where(function($subQ) use ($agencyId) {
                    $subQ->where('schedulable_type', 'App\\Models\\Project')
                         ->whereHas('schedulable', function($projectQ) use ($agencyId) {
                             $projectQ->where('agency_id', $agencyId);
                         });
                })->orWhere(function($subQ) use ($agencyId) {
                    $subQ->where('schedulable_type', 'App\\Models\\Announcement')
                         ->whereHas('schedulable', function($announcementQ) use ($agencyId) {
                             $announcementQ->where('agency_id', $agencyId);
                         });
                });
            });
        } else {
            return response()->json(['message' => '스케줄을 조회할 권한이 없습니다.'], 403);
        }

        $schedules = $query->with('schedulable')
                          ->orderBy('start_datetime', 'asc')
                          ->paginate(20);

        return response()->json([
            'message' => '스케줄 목록을 성공적으로 불러왔습니다.',
            'schedules' => $schedules,
        ], 200);
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
}
