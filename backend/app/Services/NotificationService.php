<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * 평가 단계 통과 알림 발송
     */
    public function sendEvaluationStepPassedNotification($userId, $stepName, $announcementTitle, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'evaluation_step_passed',
            'title' => "{$stepName} 통과 안내",
            'message' => "'{$announcementTitle}' 공고의 {$stepName}에 통과하셨습니다.",
            'data' => array_merge([
                'step_name' => $stepName,
                'announcement_title' => $announcementTitle,
            ], $additionalData)
        ]);
    }

    /**
     * 평가 단계 탈락 알림 발송
     */
    public function sendEvaluationStepFailedNotification($userId, $stepName, $announcementTitle, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'evaluation_step_failed',
            'title' => "{$stepName} 결과 안내",
            'message' => "'{$announcementTitle}' 공고의 {$stepName}에서 아쉽게도 선정되지 않았습니다.",
            'data' => array_merge([
                'step_name' => $stepName,
                'announcement_title' => $announcementTitle,
            ], $additionalData)
        ]);
    }

    /**
     * 예비 번호 부여 알림 발송
     */
    public function sendReserveRankAssignedNotification($userId, $reserveRank, $announcementTitle, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'reserve_rank_assigned',
            'title' => "예비 {$reserveRank}번 선정 안내",
            'message' => "'{$announcementTitle}' 공고에서 예비 {$reserveRank}번으로 선정되셨습니다.",
            'data' => array_merge([
                'reserve_rank' => $reserveRank,
                'announcement_title' => $announcementTitle,
            ], $additionalData)
        ]);
    }

    /**
     * 낙찰 알림 발송
     */
    public function sendProposalAwardedNotification($userId, $announcementTitle, $finalPrice, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'proposal_awarded',
            'title' => "낙찰 안내",
            'message' => "축하합니다! '{$announcementTitle}' 공고에서 낙찰되셨습니다.",
            'data' => array_merge([
                'announcement_title' => $announcementTitle,
                'final_price' => $finalPrice,
            ], $additionalData)
        ]);
    }

    /**
     * 미팅 일정 제안 알림 발송
     */
    public function sendMeetingDateProposedNotification($userId, $announcementTitle, $proposedDates, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'meeting_date_proposed',
            'title' => "미팅 일정 제안",
            'message' => "'{$announcementTitle}' 계약 관련 미팅 일정이 제안되었습니다. 원하시는 일정을 선택해주세요.",
            'data' => array_merge([
                'announcement_title' => $announcementTitle,
                'proposed_dates' => $proposedDates,
            ], $additionalData)
        ]);
    }

    /**
     * 미팅 일정 선택 완료 알림 발송
     */
    public function sendMeetingDateSelectedNotification($userId, $announcementTitle, $selectedDate, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'meeting_date_selected',
            'title' => "미팅 일정 확정",
            'message' => "'{$announcementTitle}' 계약 관련 미팅 일정이 확정되었습니다.",
            'data' => array_merge([
                'announcement_title' => $announcementTitle,
                'selected_date' => $selectedDate,
            ], $additionalData)
        ]);
    }

    /**
     * 공고 발행 알림 발송
     */
    public function sendAnnouncementPublishedNotification($userId, $announcementTitle, $closingAt, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'announcement_published',
            'title' => "새 공고 발행",
            'message' => "새로운 공고 '{$announcementTitle}'가 발행되었습니다.",
            'data' => array_merge([
                'announcement_title' => $announcementTitle,
                'closing_at' => $closingAt,
            ], $additionalData)
        ]);
    }

    /**
     * 공고 마감 알림 발송
     */
    public function sendAnnouncementClosedNotification($userId, $announcementTitle, $proposalCount, $additionalData = [])
    {
        return $this->createNotification($userId, [
            'notification_type' => 'announcement_closed',
            'title' => "공고 마감",
            'message' => "'{$announcementTitle}' 공고가 마감되었습니다. 총 {$proposalCount}개의 제안서가 접수되었습니다.",
            'data' => array_merge([
                'announcement_title' => $announcementTitle,
                'proposal_count' => $proposalCount,
            ], $additionalData)
        ]);
    }

    /**
     * 알림 생성 (공통 메서드)
     */
    private function createNotification($userId, $data)
    {
        return Notification::create(array_merge([
            'user_id' => $userId,
        ], $data));
    }

    /**
     * 사용자의 읽지 않은 알림 개수 조회
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * 사용자의 알림 목록 조회
     */
    public function getUserNotifications($userId, $limit = 20, $onlyUnread = false)
    {
        $query = Notification::where('user_id', $userId);
        
        if ($onlyUnread) {
            $query->where('is_read', false);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 알림 읽음 처리
     */
    public function markAsRead($notificationId, $userId)
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * 모든 알림 읽음 처리
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
} 