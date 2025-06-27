<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencyMember;
use App\Models\Project;
use App\Models\Rfp;
use App\Models\Announcement;
use App\Models\AnnouncementEvaluator;
use App\Models\Evaluation;
use App\Models\Proposal;
use App\Models\Vendor;
use App\Models\VendorMember;

class EvaluationApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $agencyUser;
    protected $evaluatorUser;
    protected $agency;
    protected $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 생성
        $this->adminUser = User::factory()->create(['user_type' => 'admin']);

        // 대행사 및 사용자들 생성
        $this->agency = Agency::factory()->create();
        
        $this->agencyUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->agencyUser->id
        ]);

        $this->evaluatorUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->evaluatorUser->id
        ]);

        // 공고 생성
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id
        ]);
        $this->announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'evaluation_criteria' => [
                'price_weight' => 40,
                'portfolio_weight' => 35,
                'additional_weight' => 25
            ]
        ]);
    }

    /**
     * 심사위원 배정 테스트
     */
    public function test_admin_can_assign_evaluators_to_announcement()
    {
        $assignmentData = [
            'evaluator_user_ids' => [$this->evaluatorUser->id],
            'assignment_type' => 'designated'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/assign-evaluators", $assignmentData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'announcement_id',
                     'assigned_evaluators',
                     'total_evaluators'
                 ]);

        // 데이터베이스 확인
        $this->assertDatabaseHas('announcement_evaluators', [
            'announcement_id' => $this->announcement->id,
            'user_id' => $this->evaluatorUser->id,
            'assignment_type' => 'designated'
        ]);
    }

    /**
     * 심사위원이 점수를 제출할 수 있는지 테스트
     */
    public function test_evaluator_can_submit_scores()
    {
        // 심사위원 배정
        AnnouncementEvaluator::create([
            'announcement_id' => $this->announcement->id,
            'user_id' => $this->evaluatorUser->id,
            'assignment_type' => 'designated'
        ]);

        // 제안서 생성
        $vendor = Vendor::factory()->create();
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor->id
        ]);

        $scoreData = [
            'portfolio_score' => 85,
            'additional_score' => 90,
            'comment' => '우수한 포트폴리오와 창의적인 제안입니다.'
        ];

        $response = $this->actingAs($this->evaluatorUser)
                         ->postJson("/api/proposals/{$proposal->id}/submit-score", $scoreData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'evaluation' => [
                         'id',
                         'portfolio_score',
                         'additional_score',
                         'comment'
                     ]
                 ]);

        // 데이터베이스 확인
        $this->assertDatabaseHas('evaluations', [
            'proposal_id' => $proposal->id,
            'evaluator_user_id' => $this->evaluatorUser->id,
            'portfolio_score' => 85,
            'additional_score' => 90
        ]);
    }

    /**
     * 중복 평가 방지 테스트
     */
    public function test_evaluator_cannot_submit_duplicate_evaluation()
    {
        // 심사위원 배정
        AnnouncementEvaluator::create([
            'announcement_id' => $this->announcement->id,
            'user_id' => $this->evaluatorUser->id
        ]);

        // 제안서 및 기존 평가 생성
        $vendor = Vendor::factory()->create();
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor->id
        ]);

        Evaluation::factory()->create([
            'proposal_id' => $proposal->id,
            'evaluator_user_id' => $this->evaluatorUser->id
        ]);

        // 중복 평가 시도
        $scoreData = [
            'portfolio_score' => 75,
            'additional_score' => 80
        ];

        $response = $this->actingAs($this->evaluatorUser)
                         ->postJson("/api/proposals/{$proposal->id}/submit-score", $scoreData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '이미 이 제안서에 대한 평가를 완료했습니다.'
                 ]);
    }

    /**
     * 배정되지 않은 심사위원이 평가할 수 없는지 테스트
     */
    public function test_non_assigned_evaluator_cannot_submit_score()
    {
        $vendor = Vendor::factory()->create();
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor->id
        ]);

        $scoreData = [
            'portfolio_score' => 85,
            'additional_score' => 90
        ];

        $response = $this->actingAs($this->evaluatorUser)
                         ->postJson("/api/proposals/{$proposal->id}/submit-score", $scoreData);

        $response->assertStatus(403)
                 ->assertJson([
                     'message' => '이 제안서에 대한 평가 권한이 없습니다.'
                 ]);
    }

    /**
     * 평가 현황 조회 테스트
     */
    public function test_agency_user_can_view_evaluation_summary()
    {
        // 심사위원 배정
        AnnouncementEvaluator::create([
            'announcement_id' => $this->announcement->id,
            'user_id' => $this->evaluatorUser->id
        ]);

        // 제안서들 생성
        $vendor1 = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();
        
        $proposal1 = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor1->id,
            'proposed_price' => 10000000
        ]);

        $proposal2 = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor2->id,
            'proposed_price' => 12000000
        ]);

        // 평가 생성
        Evaluation::factory()->create([
            'proposal_id' => $proposal1->id,
            'evaluator_user_id' => $this->evaluatorUser->id,
            'portfolio_score' => 85,
            'additional_score' => 90
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/announcements/{$this->announcement->id}/evaluation-summary");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'announcement',
                     'assigned_evaluators',
                     'proposal_summaries' => [
                         '*' => [
                             'proposal_id',
                             'vendor_name',
                             'proposed_price',
                             'final_weighted_score',
                             'rank'
                         ]
                     ],
                     'statistics'
                 ]);
    }

    /**
     * 내 평가 과제 조회 테스트
     */
    public function test_evaluator_can_view_their_evaluation_tasks()
    {
        // 심사위원 배정
        AnnouncementEvaluator::create([
            'announcement_id' => $this->announcement->id,
            'user_id' => $this->evaluatorUser->id
        ]);

        // 제안서 생성
        $vendor = Vendor::factory()->create();
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $vendor->id
        ]);

        $response = $this->actingAs($this->evaluatorUser)
                         ->getJson('/api/my-evaluations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'evaluation_tasks' => [
                         '*' => [
                             'announcement_id',
                             'announcement_title',
                             'proposal_id',
                             'vendor_name',
                             'is_evaluated'
                         ]
                     ],
                     'statistics'
                 ]);
    }

    /**
     * 다른 대행사 사용자는 평가 현황을 조회할 수 없는지 테스트
     */
    public function test_other_agency_user_cannot_view_evaluation_summary()
    {
        // 다른 대행사 생성
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $otherAgency->id,
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($otherUser)
                         ->getJson("/api/announcements/{$this->announcement->id}/evaluation-summary");

        $response->assertStatus(403);
    }

    /**
     * 다른 대행사 사용자를 심사위원으로 배정할 수 없는지 테스트
     */
    public function test_cannot_assign_evaluator_from_other_agency()
    {
        // 다른 대행사의 사용자 생성
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $otherAgency->id,
            'user_id' => $otherUser->id
        ]);

        $assignmentData = [
            'evaluator_user_ids' => [$otherUser->id],
            'assignment_type' => 'designated'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/assign-evaluators", $assignmentData);

        $response->assertStatus(500); // 서버 오류로 처리됨
    }
} 