<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ScheduleAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ScheduleAttachmentController extends Controller
{
    /**
     * 특정 스케줄에 파일 업로드
     *
     * @OA\Post(
     *     path="/api/schedules/{schedule}/attachments",
     *     tags={"Schedule Attachments"},
     *     summary="스케줄에 파일 업로드",
     *     description="특정 스케줄에 사진이나 문서 파일을 업로드합니다. (관리자 또는 해당 대행사 멤버만 가능)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         description="스케줄 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="업로드할 이미지 파일 (최대 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="파일 업로드 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="파일이 성공적으로 업로드되었습니다."),
     *             @OA\Property(property="attachment", type="object",
     *                 @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                 @OA\Property(property="file_name", type="string", example="설치_완료_사진.jpg"),
     *                 @OA\Property(property="file_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="file_size", type="integer", example=1024000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="파일을 업로드할 권한이 없습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Schedule $schedule)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 해당 스케줄의 대행사 멤버
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $schedulable = $schedule->schedulable;
            if ($schedulable && ($user->agency_members->first()->agency_id ?? null) === $schedulable->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '파일을 업로드할 권한이 없습니다.'], 403);
        }

        // 2. 요청 유효성 검사 (파일 타입 및 크기 제한)
        $request->validate([
            'file' => 'required|file|image|max:10240', // 이미지 파일만 (jpeg, png, gif, bmp, svg, webp) 최대 10MB
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('schedule_attachments/' . $schedule->id, 'public'); // 'public' 디스크에 저장

                // 3. 파일 정보 저장
                $attachment = ScheduleAttachment::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $user->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                DB::commit();
                return response()->json([
                    'message' => '파일이 성공적으로 업로드되었습니다.',
                    'attachment' => $attachment,
                ], 201);
            }

            return response()->json(['message' => '업로드할 파일이 없습니다.'], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '파일 업로드 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 특정 스케줄의 첨부 파일 목록 조회
     *
     * @OA\Get(
     *     path="/api/schedules/{schedule}/attachments",
     *     tags={"Schedule Attachments"},
     *     summary="스케줄 첨부파일 목록 조회",
     *     description="특정 스케줄에 업로드된 모든 첨부파일 목록을 조회합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         description="스케줄 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="첨부파일 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="첨부 파일 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="attachments", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="file_name", type="string"),
     *                     @OA\Property(property="file_type", type="string"),
     *                     @OA\Property(property="file_size", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Schedule $schedule)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $schedulable = $schedule->schedulable;
            if ($schedulable && ($user->agency_members->first()->agency_id ?? null) === $schedulable->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '첨부 파일 목록을 조회할 권한이 없습니다.'], 403);
        }

        $attachments = $schedule->attachments()->with('user:id,name')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => '첨부 파일 목록을 성공적으로 불러왔습니다.',
            'attachments' => $attachments,
        ], 200);
    }

    /**
     * 첨부 파일 다운로드 (GET /api/schedule-attachments/{attachment})
     * (관리자 또는 해당 스케줄의 대행사 멤버만)
     *
     * @param  \App\Models\ScheduleAttachment  $attachment
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function download(ScheduleAttachment $attachment)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $schedule = $attachment->schedule;
            $schedulable = $schedule->schedulable;
            if ($schedulable && ($user->agency_members->first()->agency_id ?? null) === $schedulable->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '파일을 다운로드할 권한이 없습니다.'], 403);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['message' => '파일을 찾을 수 없습니다.'], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * 특정 첨부 파일 삭제 (DELETE /api/schedule-attachments/{attachment})
     * (관리자 또는 해당 스케줄의 대행사 멤버만)
     *
     * @param  \App\Models\ScheduleAttachment  $attachment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ScheduleAttachment $attachment)
    {
        $user = Auth::user();

        // 권한 확인
        $hasAccess = false;
        if ($user->user_type === 'admin') {
            $hasAccess = true;
        } elseif ($user->user_type === 'agency_member') {
            $schedule = $attachment->schedule;
            $schedulable = $schedule->schedulable;
            if ($schedulable && ($user->agency_members->first()->agency_id ?? null) === $schedulable->agency_id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json(['message' => '파일을 삭제할 권한이 없습니다.'], 403);
        }

        DB::beginTransaction();
        try {
            // 파일 스토리지에서 실제 파일 삭제
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // DB 레코드 삭제
            $attachment->delete();

            DB::commit();
            return response()->json([
                'message' => '파일이 성공적으로 삭제되었습니다.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '파일 삭제 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
