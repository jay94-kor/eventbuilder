<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\Vendor;
use App\Models\AgencyMember;
use App\Models\VendorMember;
use App\Models\Project;
use App\Models\Rfp;
use App\Models\Announcement;

class ValidationApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $agencyUser;
    protected $vendorUser;
    protected $agency;
    protected $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'user_type' => 'admin'
        ]);

        // 대행사 및 사용자 생성
        $this->agency = Agency::factory()->create();
        $this->agencyUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->agencyUser->id
        ]);

        // 용역사 및 사용자 생성
        $this->vendor = Vendor::factory()->create();
        $this->vendorUser = User::factory()->create(['user_type' => 'vendor_member']);
        VendorMember::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->vendorUser->id
        ]);
    }

    /**
     * RFP 생성 시 필수 필드 누락 테스트
     */
    public function test_rfp_creation_missing_required_fields()
    {
        $incompleteData = [
            'rfp_description' => 'Test RFP',
            // project_name 누락
            // start_datetime 누락
            // end_datetime 누락
            // is_indoor 누락
            // location 누락
            // issue_type 누락
            // closing_at 누락
            // elements 누락
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/rfps', $incompleteData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'project_name',
                     'start_datetime', 
                     'end_datetime',
                     'is_indoor',
                     'location',
                     'issue_type',
                     'closing_at',
                     'elements'
                 ]);
    }

    /**
     * RFP 생성 시 잘못된 issue_type 테스트
     */
    public function test_rfp_creation_invalid_issue_type()
    {
        $invalidData = [
            'project_name' => 'Test Project',
            'start_datetime' => now()->addDays(1)->toISOString(),
            'end_datetime' => now()->addDays(2)->toISOString(),
            'is_indoor' => true,
            'location' => 'Test Location',
            'rfp_description' => 'Test RFP',
            'closing_at' => now()->addDays(5)->toISOString(),
            'issue_type' => 'invalid_type', // 잘못된 enum 값
            'elements' => [
                [
                    'element_type' => 'stage',
                    'details' => ['requirement' => 'test']
                ]
            ]
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/rfps', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['issue_type']);
    }

    /**
     * 공고 발행 시 평가 기준 유효성 검사 테스트
     */
    public function test_announcement_publish_invalid_evaluation_criteria()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        // 1. 평가 기준 누락
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
                             'closing_at' => now()->addDays(14)->toISOString(),
                             'channel_type' => 'public',
                             'contact_info_private' => false,
                             // evaluation_criteria 누락
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['evaluation_criteria']);

        // 2. 비중 값이 음수인 경우
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
                             'closing_at' => now()->addDays(14)->toISOString(),
                             'channel_type' => 'public',
                             'contact_info_private' => false,
                             'evaluation_criteria' => [
                                 'price_weight' => -10, // 음수
                                 'portfolio_weight' => 60,
                                 'additional_weight' => 50,
                                 'price_deduction_rate' => 5,
                                 'price_rank_deduction_points' => [10, 20]
                             ]
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['evaluation_criteria.price_weight']);

        // 3. 비중 값이 100을 초과하는 경우
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
                             'closing_at' => now()->addDays(14)->toISOString(),
                             'channel_type' => 'public',
                             'contact_info_private' => false,
                             'evaluation_criteria' => [
                                 'price_weight' => 150, // 100 초과
                                 'portfolio_weight' => 30,
                                 'additional_weight' => 20,
                                 'price_deduction_rate' => 5,
                                 'price_rank_deduction_points' => [10, 20]
                             ]
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['evaluation_criteria.price_weight']);
    }

    /**
     * 마감일이 과거인 공고 발행 시도 테스트
     */
    public function test_announcement_publish_past_closing_date()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
                             'closing_at' => now()->subDay()->toISOString(), // 과거 날짜
                             'channel_type' => 'public',
                             'contact_info_private' => false,
                             'evaluation_criteria' => [
                                 'price_weight' => 50,
                                 'portfolio_weight' => 30,
                                 'additional_weight' => 20,
                                 'price_deduction_rate' => 5,
                                 'price_rank_deduction_points' => [10, 20]
                             ]
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['closing_at']);
    }

    /**
     * 제안서 제출 시 올바른 필드명 테스트
     */
    public function test_proposal_submission_with_correct_fields()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);

        $proposalData = [
            'proposed_price' => 1000000,
            'proposal_text' => 'Test proposal content'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $proposalData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'proposal' => [
                         'id',
                         'proposed_price',
                         'proposal_text',
                         'status'
                     ]
                 ]);
    }

    /**
     * 제안서 제출 시 음수 가격 제안 테스트
     */
    public function test_proposal_submission_negative_price()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);

        $invalidData = [
            'proposed_price' => -1000000, // 음수 가격
            'proposal_text' => 'Test proposal content'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['proposed_price']);
    }

    /**
     * 잘못된 채널 타입으로 공고 발행 시도 테스트
     */
    public function test_announcement_publish_invalid_channel_type()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
                             'closing_at' => now()->addDays(14)->toISOString(),
                             'channel_type' => 'invalid_channel', // 잘못된 enum 값
                             'contact_info_private' => false,
                             'evaluation_criteria' => [
                                 'price_weight' => 50,
                                 'portfolio_weight' => 30,
                                 'additional_weight' => 20,
                                 'price_deduction_rate' => 5,
                                 'price_rank_deduction_points' => [10, 20]
                             ]
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['channel_type']);
    }

    /**
     * 스케줄 생성 시 잘못된 날짜 형식 테스트
     */
    public function test_schedule_creation_invalid_datetime()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);

        $invalidData = [
            'title' => 'Test Schedule',
            'description' => 'Test Description',
            'start_datetime' => 'invalid-date-format', // 잘못된 날짜 형식
            'end_datetime' => now()->addHours(2)->toISOString(),
            'schedulable_id' => $project->id,
            'schedulable_type' => 'App\\Models\\Project'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['start_datetime']);
    }

    /**
     * 스케줄 생성 시 잘못된 타입 테스트
     */
    public function test_schedule_creation_invalid_type()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);

        $invalidData = [
            'title' => 'Test Schedule',
            'description' => 'Test Description',
            'start_datetime' => now()->addDays(1)->toISOString(),
            'end_datetime' => now()->addDays(1)->addHours(2)->toISOString(),
            'status' => 'invalid_status', // 잘못된 enum 값
            'schedulable_id' => $project->id,
            'schedulable_type' => 'App\\Models\\Project'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }
} 