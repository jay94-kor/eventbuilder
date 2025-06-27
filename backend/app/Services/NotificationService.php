<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * 알림 발송 (현재는 로그만 기록, 추후 실제 알림 시스템 연동)
     *
     * @param User $user 알림을 받을 사용자
     * @param string $type 알림 타입
     * @param string $title 알림 제목
     * @param string $message 알림 내용
     * @param array $data 추가 데이터
     * @return bool
     */
    public function sendNotification(User $user, string $type, string $title, string $message, array $data = []): bool
    {
        try {
            // 현재는 로그로 알림 발송을 시뮬레이션
            // 추후 실제 알림 시스템(푸시, 이메일, SMS 등)과 연동
            Log::info('Notification sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'timestamp' => now()
            ]);

            // 실제 알림 발송 로직 구현 예시:
            // - 데이터베이스에 알림 레코드 저장
            // - 푸시 알림 발송
            // - 이메일 발송
            // - SMS 발송 등

            return true;
        } catch (\Exception $e) {
            Log::error('Notification send failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 여러 사용자에게 동일한 알림 발송
     *
     * @param array $users User 객체 배열
     * @param string $type 알림 타입
     * @param string $title 알림 제목
     * @param string $message 알림 내용
     * @param array $data 추가 데이터
     * @return array 발송 결과 배열
     */
    public function sendBulkNotification(array $users, string $type, string $title, string $message, array $data = []): array
    {
        $results = [];
        
        foreach ($users as $user) {
            $results[$user->id] = $this->sendNotification($user, $type, $title, $message, $data);
        }
        
        return $results;
    }

    /**
     * 제안서 제출 알림
     */
    public function notifyProposalSubmitted(User $agencyUser, $proposal, $announcement): bool
    {
        return $this->sendNotification(
            $agencyUser,
            'proposal_submitted',
            '새로운 제안서 제출',
            "'{$announcement->title}' 공고에 새로운 제안서가 제출되었습니다.",
            ['proposal_id' => $proposal->id, 'announcement_id' => $announcement->id]
        );
    }

    /**
     * 낙찰 알림
     */
    public function notifyProposalAwarded(User $vendorUser, $proposal, $announcement): bool
    {
        return $this->sendNotification(
            $vendorUser,
            'proposal_awarded',
            '제안서 낙찰',
            "축하합니다! '{$announcement->title}' 공고에서 귀하의 제안서가 낙찰되었습니다.",
            ['proposal_id' => $proposal->id, 'announcement_id' => $announcement->id]
        );
    }

    /**
     * 유찰 알림
     */
    public function notifyProposalRejected(User $vendorUser, $proposal, $announcement): bool
    {
        return $this->sendNotification(
            $vendorUser,
            'proposal_rejected',
            '제안서 유찰',
            "'{$announcement->title}' 공고에서 귀하의 제안서가 유찰되었습니다.",
            ['proposal_id' => $proposal->id, 'announcement_id' => $announcement->id]
        );
    }

    /**
     * 예비 순위 설정 알림
     */
    public function notifyReserveRankSet(User $vendorUser, $proposal, $announcement, int $rank): bool
    {
        return $this->sendNotification(
            $vendorUser,
            'reserve_rank_set',
            '예비 순위 설정',
            "'{$announcement->title}' 공고에서 귀하의 제안서가 예비 {$rank}순위로 설정되었습니다.",
            ['proposal_id' => $proposal->id, 'announcement_id' => $announcement->id, 'rank' => $rank]
        );
    }

    /**
     * 평가 점수 제출 알림
     */
    public function notifyEvaluationSubmitted(User $agencyUser, $evaluation, $proposal): bool
    {
        return $this->sendNotification(
            $agencyUser,
            'evaluation_submitted',
            '평가 점수 제출',
            "제안서에 대한 평가 점수가 제출되었습니다.",
            ['evaluation_id' => $evaluation->id, 'proposal_id' => $proposal->id]
        );
    }
} 