<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $notificationService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new NotificationService();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'user_type' => 'vendor_member'
        ]);
    }

    /**
     * 단일 알림 발송 테스트
     */
    public function test_send_single_notification()
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Notification sent', \Mockery::type('array'));

        $result = $this->notificationService->sendNotification(
            $this->user,
            'test_type',
            'Test Title',
            'Test Message',
            ['key' => 'value']
        );

        $this->assertTrue($result);
    }

    /**
     * 대량 알림 발송 테스트
     */
    public function test_send_bulk_notification()
    {
        $users = User::factory()->count(3)->create();

        Log::shouldReceive('info')
           ->times(3)
           ->with('Notification sent', \Mockery::type('array'));

        $results = $this->notificationService->sendBulkNotification(
            $users->toArray(),
            'bulk_test',
            'Bulk Title',
            'Bulk Message'
        );

        $this->assertCount(3, $results);
        $this->assertTrue(array_reduce($results, fn($carry, $result) => $carry && $result, true));
    }

    /**
     * 제안서 제출 알림 테스트
     */
    public function test_notify_proposal_submitted()
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Notification sent', \Mockery::on(function ($data) {
               return $data['type'] === 'proposal_submitted' &&
                      str_contains($data['message'], '새로운 제안서가 제출되었습니다');
           }));

        $proposal = (object)['id' => 'test-proposal-id'];
        $announcement = (object)['title' => 'Test Announcement', 'id' => 'test-announcement-id'];

        $result = $this->notificationService->notifyProposalSubmitted(
            $this->user,
            $proposal,
            $announcement
        );

        $this->assertTrue($result);
    }

    /**
     * 낙찰 알림 테스트
     */
    public function test_notify_proposal_awarded()
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Notification sent', \Mockery::on(function ($data) {
               return $data['type'] === 'proposal_awarded' &&
                      str_contains($data['message'], '낙찰되었습니다');
           }));

        $proposal = (object)['id' => 'test-proposal-id'];
        $announcement = (object)['title' => 'Test Announcement', 'id' => 'test-announcement-id'];

        $result = $this->notificationService->notifyProposalAwarded(
            $this->user,
            $proposal,
            $announcement
        );

        $this->assertTrue($result);
    }

    /**
     * 유찰 알림 테스트
     */
    public function test_notify_proposal_rejected()
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Notification sent', \Mockery::on(function ($data) {
               return $data['type'] === 'proposal_rejected' &&
                      str_contains($data['message'], '유찰되었습니다');
           }));

        $proposal = (object)['id' => 'test-proposal-id'];
        $announcement = (object)['title' => 'Test Announcement', 'id' => 'test-announcement-id'];

        $result = $this->notificationService->notifyProposalRejected(
            $this->user,
            $proposal,
            $announcement
        );

        $this->assertTrue($result);
    }

    /**
     * 예비 순위 설정 알림 테스트
     */
    public function test_notify_reserve_rank_set()
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Notification sent', \Mockery::on(function ($data) {
               return $data['type'] === 'reserve_rank_set' &&
                      str_contains($data['message'], '예비 1순위로 설정되었습니다') &&
                      $data['data']['rank'] === 1;
           }));

        $proposal = (object)['id' => 'test-proposal-id'];
        $announcement = (object)['title' => 'Test Announcement', 'id' => 'test-announcement-id'];

        $result = $this->notificationService->notifyReserveRankSet(
            $this->user,
            $proposal,
            $announcement,
            1
        );

        $this->assertTrue($result);
    }

    /**
     * 알림 발송 실패 시 오류 처리 테스트
     */
    public function test_notification_failure_handling()
    {
        // Log::info에서 예외 발생 시뮬레이션
        Log::shouldReceive('info')
           ->once()
           ->andThrow(new \Exception('Log write failed'));

        Log::shouldReceive('error')
           ->once()
           ->with('Notification send failed', \Mockery::type('array'));

        $result = $this->notificationService->sendNotification(
            $this->user,
            'test_type',
            'Test Title',
            'Test Message'
        );

        $this->assertFalse($result);
    }
} 