<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="알림 관리 API"
 * )
 * 
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="user_id", type="string", format="uuid"),
 *     @OA\Property(property="notification_type", type="string", example="evaluation_step_passed"),
 *     @OA\Property(property="title", type="string", example="1차 서류 심사 통과 안내"),
 *     @OA\Property(property="message", type="string", example="'신년 행사' 공고의 1차 서류 심사에 통과하셨습니다."),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="read_at", type="string", format="datetime", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     tags={"Notifications"},
     *     summary="내 알림 목록 조회",
     *     description="현재 사용자의 알림 목록을 조회합니다",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="조회할 알림 개수 (기본값: 20)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="unread_only",
     *         in="query",
     *         description="읽지 않은 알림만 조회 (기본값: false)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="알림 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="notifications", type="array", @OA\Items(ref="#/components/schemas/Notification")),
     *             @OA\Property(property="unread_count", type="integer", example=5),
     *             @OA\Property(property="total_count", type="integer", example=25)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = min($request->get('limit', 20), 100);
        $unreadOnly = $request->boolean('unread_only', false);
        $userId = auth()->id();

        $notifications = $this->notificationService->getUserNotifications($userId, $limit, $unreadOnly);
        $unreadCount = $this->notificationService->getUnreadCount($userId);
        $totalCount = Notification::where('user_id', $userId)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     tags={"Notifications"},
     *     summary="읽지 않은 알림 개수 조회",
     *     description="현재 사용자의 읽지 않은 알림 개수를 조회합니다",
     *     @OA\Response(
     *         response=200,
     *         description="읽지 않은 알림 개수 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="unread_count", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function getUnreadCount()
    {
        $unreadCount = $this->notificationService->getUnreadCount(auth()->id());

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/notifications/{notification}/read",
     *     tags={"Notifications"},
     *     summary="알림 읽음 처리",
     *     description="특정 알림을 읽음 처리합니다",
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="알림 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="알림 읽음 처리 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="알림이 읽음 처리되었습니다")
     *         )
     *     ),
     *     @OA\Response(response=404, description="알림을 찾을 수 없습니다")
     * )
     */
    public function markAsRead($notificationId)
    {
        $updated = $this->notificationService->markAsRead($notificationId, auth()->id());

        if (!$updated) {
            return response()->json([
                'message' => '알림을 찾을 수 없습니다'
            ], 404);
        }

        return response()->json([
            'message' => '알림이 읽음 처리되었습니다',
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/notifications/mark-all-read",
     *     tags={"Notifications"},
     *     summary="모든 알림 읽음 처리",
     *     description="현재 사용자의 모든 읽지 않은 알림을 읽음 처리합니다",
     *     @OA\Response(
     *         response=200,
     *         description="모든 알림 읽음 처리 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="모든 알림이 읽음 처리되었습니다"),
     *             @OA\Property(property="updated_count", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function markAllAsRead()
    {
        $updatedCount = $this->notificationService->markAllAsRead(auth()->id());

        return response()->json([
            'message' => '모든 알림이 읽음 처리되었습니다',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/{notification}",
     *     tags={"Notifications"},
     *     summary="알림 상세 조회",
     *     description="특정 알림의 상세 정보를 조회합니다",
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="알림 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="알림 상세 조회 성공",
     *         @OA\JsonContent(ref="#/components/schemas/Notification")
     *     ),
     *     @OA\Response(response=404, description="알림을 찾을 수 없습니다")
     * )
     */
    public function show($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => '알림을 찾을 수 없습니다'
            ], 404);
        }

        // 알림을 조회할 때 자동으로 읽음 처리
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json($notification);
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{notification}",
     *     tags={"Notifications"},
     *     summary="알림 삭제",
     *     description="특정 알림을 삭제합니다",
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="알림 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="알림 삭제 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="알림이 삭제되었습니다")
     *         )
     *     ),
     *     @OA\Response(response=404, description="알림을 찾을 수 없습니다")
     * )
     */
    public function destroy($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => '알림을 찾을 수 없습니다'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => '알림이 삭제되었습니다',
        ]);
    }
} 