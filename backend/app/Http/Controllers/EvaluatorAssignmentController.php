<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Rfp;
use App\Models\Project;
use App\Models\Announcement;
use App\Models\AnnouncementEvaluator;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Evaluator Assignment",
 *     description="심사위원 배정 관리 API"
 * )
 */
class EvaluatorAssignmentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/projects/{project}/assign-evaluators",
     *     summary="프로젝트 전체 심사위원 배정",
     *     description="프로젝트와 연결된 모든 현재 및 미래 공고에 심사위원을 배정합니다.",
     *     tags={"Evaluator Assignment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         description="프로젝트 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"evaluator_user_ids","assignment_type"},
     *             @OA\Property(
     *                 property="evaluator_user_ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 example={"01234567-89ab-cdef-0123-456789abcdef", "fedcba98-7654-3210-fedc-ba9876543210"}
     *             ),
     *             @OA\Property(property="assignment_type", type="string", enum={"designated","random"}, example="designated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 배정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="프로젝트 전체에 심사위원이 성공적으로 배정되었습니다."),
     *             @OA\Property(property="assigned_evaluators_count", type="integer", example=2),
     *             @OA\Property(property="affected_announcements_count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="프로젝트를 찾을 수 없음"),
     *     @OA\Response(response=422, description="유효성 검사 실패")
     * )
     */
    public function assignToProject(Request $request, Project $project)
    {
        $user = Auth::user();

        // 권한 확인: 대행사 멤버 또는 관리자만 가능
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => '심사위원 배정 권한이 없습니다.'], 403);
        }

        // 대행사 멤버인 경우 자신의 대행사 프로젝트만 가능
        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $project->agency_id) {
                return response()->json(['message' => '이 프로젝트에 대한 권한이 없습니다.'], 403);
            }
        }

        // 요청 데이터 유효성 검사
        $request->validate([
            'evaluator_user_ids' => 'required|array|min:1',
            'evaluator_user_ids.*' => 'required|string|exists:users,id',
            'assignment_type' => 'required|in:designated,random'
        ]);

        // 심사위원이 모두 대행사 멤버인지 확인
        $evaluatorIds = $request->evaluator_user_ids;
        $evaluators = User::whereIn('id', $evaluatorIds)
            ->where('user_type', 'agency_member')
            ->get();

        if ($evaluators->count() !== count($evaluatorIds)) {
            return response()->json(['message' => '모든 심사위원은 대행사 멤버여야 합니다.'], 422);
        }

        DB::beginTransaction();
        try {
            // 프로젝트와 연결된 모든 공고 조회
            $announcements = Announcement::whereHas('rfp', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })->get();

            $totalAssigned = 0;

            foreach ($announcements as $announcement) {
                foreach ($evaluatorIds as $evaluatorId) {
                    // 중복 배정 방지
                    $existingAssignment = AnnouncementEvaluator::where('announcement_id', $announcement->id)
                        ->where('evaluator_user_id', $evaluatorId)
                        ->first();

                    if (!$existingAssignment) {
                        AnnouncementEvaluator::create([
                            'announcement_id' => $announcement->id,
                            'evaluator_user_id' => $evaluatorId,
                            'assignment_type' => $request->assignment_type,
                            'scope_type' => 'project', // 프로젝트 전체 배정임을 표시
                            'assigned_at' => now()
                        ]);
                        $totalAssigned++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => '프로젝트 전체에 심사위원이 성공적으로 배정되었습니다.',
                'assigned_evaluators_count' => count($evaluatorIds),
                'affected_announcements_count' => $announcements->count(),
                'total_assignments_created' => $totalAssigned
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '심사위원 배정 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/rfps/{rfp}/assign-evaluators",
     *     summary="RFP 전체 심사위원 배정",
     *     description="RFP와 연결된 모든 현재 및 미래 공고에 심사위원을 배정합니다.",
     *     tags={"Evaluator Assignment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"evaluator_user_ids","assignment_type"},
     *             @OA\Property(
     *                 property="evaluator_user_ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 example={"01234567-89ab-cdef-0123-456789abcdef", "fedcba98-7654-3210-fedc-ba9876543210"}
     *             ),
     *             @OA\Property(property="assignment_type", type="string", enum={"designated","random"}, example="designated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 배정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 전체에 심사위원이 성공적으로 배정되었습니다."),
     *             @OA\Property(property="assigned_evaluators_count", type="integer", example=2),
     *             @OA\Property(property="affected_announcements_count", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="RFP를 찾을 수 없음"),
     *     @OA\Response(response=422, description="유효성 검사 실패")
     * )
     */
    public function assignToRfp(Request $request, Rfp $rfp)
    {
        $user = Auth::user();

        // 권한 확인: 대행사 멤버 또는 관리자만 가능
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => '심사위원 배정 권한이 없습니다.'], 403);
        }

        // 대행사 멤버인 경우 자신의 대행사 RFP만 가능
        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $rfp->agency_id) {
                return response()->json(['message' => '이 RFP에 대한 권한이 없습니다.'], 403);
            }
        }

        // 요청 데이터 유효성 검사
        $request->validate([
            'evaluator_user_ids' => 'required|array|min:1',
            'evaluator_user_ids.*' => 'required|string|exists:users,id',
            'assignment_type' => 'required|in:designated,random'
        ]);

        // 심사위원이 모두 대행사 멤버인지 확인
        $evaluatorIds = $request->evaluator_user_ids;
        $evaluators = User::whereIn('id', $evaluatorIds)
            ->where('user_type', 'agency_member')
            ->get();

        if ($evaluators->count() !== count($evaluatorIds)) {
            return response()->json(['message' => '모든 심사위원은 대행사 멤버여야 합니다.'], 422);
        }

        DB::beginTransaction();
        try {
            // RFP와 연결된 모든 공고 조회
            $announcements = Announcement::where('rfp_id', $rfp->id)->get();

            $totalAssigned = 0;

            foreach ($announcements as $announcement) {
                foreach ($evaluatorIds as $evaluatorId) {
                    // 중복 배정 방지
                    $existingAssignment = AnnouncementEvaluator::where('announcement_id', $announcement->id)
                        ->where('evaluator_user_id', $evaluatorId)
                        ->first();

                    if (!$existingAssignment) {
                        AnnouncementEvaluator::create([
                            'announcement_id' => $announcement->id,
                            'evaluator_user_id' => $evaluatorId,
                            'assignment_type' => $request->assignment_type,
                            'scope_type' => 'rfp', // RFP 전체 배정임을 표시
                            'assigned_at' => now()
                        ]);
                        $totalAssigned++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'RFP 전체에 심사위원이 성공적으로 배정되었습니다.',
                'assigned_evaluators_count' => count($evaluatorIds),
                'affected_announcements_count' => $announcements->count(),
                'total_assignments_created' => $totalAssigned
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '심사위원 배정 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/announcements/{announcement}/assign-evaluators",
     *     summary="개별 공고 심사위원 배정",
     *     description="특정 공고에만 심사위원을 배정합니다.",
     *     tags={"Evaluator Assignment"},
     *     security={{"bearerAuth":{}}},
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
     *             required={"evaluator_user_ids","assignment_type"},
     *             @OA\Property(
     *                 property="evaluator_user_ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 example={"01234567-89ab-cdef-0123-456789abcdef", "fedcba98-7654-3210-fedc-ba9876543210"}
     *             ),
     *             @OA\Property(property="assignment_type", type="string", enum={"designated","random"}, example="designated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 배정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="공고에 심사위원이 성공적으로 배정되었습니다."),
     *             @OA\Property(property="assigned_evaluators_count", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="공고를 찾을 수 없음"),
     *     @OA\Response(response=422, description="유효성 검사 실패")
     * )
     */
    public function assignToAnnouncement(Request $request, Announcement $announcement)
    {
        $user = Auth::user();

        // 권한 확인: 대행사 멤버 또는 관리자만 가능
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => '심사위원 배정 권한이 없습니다.'], 403);
        }

        // 대행사 멤버인 경우 자신의 대행사 공고만 가능
        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $announcement->agency_id) {
                return response()->json(['message' => '이 공고에 대한 권한이 없습니다.'], 403);
            }
        }

        // 요청 데이터 유효성 검사
        $request->validate([
            'evaluator_user_ids' => 'required|array|min:1',
            'evaluator_user_ids.*' => 'required|string|exists:users,id',
            'assignment_type' => 'required|in:designated,random'
        ]);

        // 심사위원이 모두 대행사 멤버인지 확인
        $evaluatorIds = $request->evaluator_user_ids;
        $evaluators = User::whereIn('id', $evaluatorIds)
            ->where('user_type', 'agency_member')
            ->get();

        if ($evaluators->count() !== count($evaluatorIds)) {
            return response()->json(['message' => '모든 심사위원은 대행사 멤버여야 합니다.'], 422);
        }

        DB::beginTransaction();
        try {
            $totalAssigned = 0;

            foreach ($evaluatorIds as $evaluatorId) {
                // 중복 배정 방지
                $existingAssignment = AnnouncementEvaluator::where('announcement_id', $announcement->id)
                    ->where('evaluator_user_id', $evaluatorId)
                    ->first();

                if (!$existingAssignment) {
                    AnnouncementEvaluator::create([
                        'announcement_id' => $announcement->id,
                        'evaluator_user_id' => $evaluatorId,
                        'assignment_type' => $request->assignment_type,
                        'scope_type' => 'announcement', // 개별 공고 배정임을 표시
                        'assigned_at' => now()
                    ]);
                    $totalAssigned++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => '공고에 심사위원이 성공적으로 배정되었습니다.',
                'assigned_evaluators_count' => count($evaluatorIds),
                'total_assignments_created' => $totalAssigned
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '심사위원 배정 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{project}/announcements",
     *     summary="프로젝트 연결 공고 목록",
     *     description="특정 프로젝트와 연결된 모든 공고 목록을 조회합니다.",
     *     tags={"Evaluator Assignment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         description="프로젝트 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="프로젝트 공고 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="프로젝트 공고 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="announcements",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="closing_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="프로젝트를 찾을 수 없음")
     * )
     */
    public function getProjectAnnouncements(Project $project)
    {
        $user = Auth::user();

        // 권한 확인
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => '접근 권한이 없습니다.'], 403);
        }

        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $project->agency_id) {
                return response()->json(['message' => '이 프로젝트에 대한 권한이 없습니다.'], 403);
            }
        }

        $announcements = Announcement::whereHas('rfp', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->select('id', 'title', 'status', 'closing_at', 'published_at')
        ->orderBy('published_at', 'desc')
        ->get();

        return response()->json([
            'message' => '프로젝트 공고 목록을 성공적으로 불러왔습니다.',
            'announcements' => $announcements,
            'total_count' => $announcements->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/rfps/{rfp}/announcements",
     *     summary="RFP 연결 공고 목록",
     *     description="특정 RFP와 연결된 모든 공고 목록을 조회합니다.",
     *     tags={"Evaluator Assignment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RFP 공고 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 공고 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="announcements",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="closing_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="RFP를 찾을 수 없음")
     * )
     */
    public function getRfpAnnouncements(Rfp $rfp)
    {
        $user = Auth::user();

        // 권한 확인
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => '접근 권한이 없습니다.'], 403);
        }

        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $rfp->agency_id) {
                return response()->json(['message' => '이 RFP에 대한 권한이 없습니다.'], 403);
            }
        }

        $announcements = Announcement::where('rfp_id', $rfp->id)
            ->select('id', 'title', 'status', 'closing_at', 'published_at')
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'RFP 공고 목록을 성공적으로 불러왔습니다.',
            'announcements' => $announcements,
            'total_count' => $announcements->count()
        ]);
    }
} 