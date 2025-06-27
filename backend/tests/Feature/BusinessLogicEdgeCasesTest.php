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
use App\Models\Proposal;
use App\Models\Contract;
use Carbon\Carbon;

class BusinessLogicEdgeCasesTest extends TestCase
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
     * 마감 시간이 지난 공고에 제안서 제출 시도 테스트
     */
    public function test_cannot_submit_proposal_to_closed_announcement()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);
        
        // 이미 마감된 공고 생성
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->subHour() // 1시간 전에 마감
        ]);

        $proposalData = [
            'cover_letter' => 'Test cover letter',
            'price_proposal' => 1000000,
            'portfolio_content' => 'Test portfolio',
            'additional_proposal' => 'Test additional'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $proposalData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 제안서를 제출할 수 없는 공고입니다. 공고가 마감되었거나 종료되었습니다.'
                 ]);
    }

    /**
     * 동일한 용역사가 같은 공고에 중복 제안서 제출 시도 테스트
     */
    public function test_cannot_submit_duplicate_proposal_from_same_vendor()
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

        // 첫 번째 제안서 제출
        $proposalData = [
            'cover_letter' => 'First proposal',
            'price_proposal' => 1000000,
            'portfolio_content' => 'Test portfolio',
            'additional_proposal' => 'Test additional'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $proposalData);
        $response->assertStatus(201);

        // 동일한 용역사가 두 번째 제안서 제출 시도
        $duplicateProposalData = [
            'cover_letter' => 'Second proposal attempt',
            'price_proposal' => 900000,
            'portfolio_content' => 'Different portfolio',
            'additional_proposal' => 'Different additional'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $duplicateProposalData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '이미 해당 공고에 제안서를 제출했습니다.'
                 ]);
    }

    /**
     * 이미 낙찰된 제안서를 다시 낙찰 시도 테스트
     */
    public function test_cannot_award_already_awarded_proposal()
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

        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'awarded' // 이미 낙찰된 상태
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal->id}/award");

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 상태에서는 제안서를 낙찰할 수 없습니다.'
                 ]);
    }

    /**
     * 이미 유찰된 제안서를 낙찰 시도 테스트
     */
    public function test_cannot_award_rejected_proposal()
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

        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'rejected' // 이미 유찰된 상태
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal->id}/award");

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 상태에서는 제안서를 낙찰할 수 없습니다.'
                 ]);
    }

    /**
     * 한 공고에서 여러 제안서를 동시에 낙찰 시도 테스트
     */
    public function test_cannot_award_multiple_proposals_in_same_announcement()
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

        // 두 번째 용역사 생성
        $vendor2 = Vendor::factory()->create();
        $vendor2User = User::factory()->create(['user_type' => 'vendor_member']);
        VendorMember::create([
            'vendor_id' => $vendor2->id,
            'user_id' => $vendor2User->id
        ]);

        // 첫 번째 제안서 생성 및 낙찰
        $proposal1 = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $proposal2 = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $vendor2->id,
            'status' => 'submitted'
        ]);

        // 첫 번째 제안서 낙찰
        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal1->id}/award");
        $response->assertStatus(200);

        // 두 번째 제안서도 낙찰 시도 (실패해야 함)
        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal2->id}/award");

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 상태에서는 제안서를 낙찰할 수 없습니다.'
                 ]);
    }

    /**
     * 동일한 예비 순위 중복 설정 시도 테스트
     */
    public function test_cannot_set_duplicate_reserve_rank()
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

        // 두 번째 용역사 생성
        $vendor2 = Vendor::factory()->create();

        $proposal1 = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $proposal2 = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $vendor2->id,
            'status' => 'submitted'
        ]);

        // 첫 번째 제안서를 예비 1순위로 설정
        $response = $this->actingAs($this->agencyUser)
                         ->patchJson("/api/proposals/{$proposal1->id}/set-reserve-rank", [
                             'reserve_rank' => 1
                         ]);
        $response->assertStatus(200);

        // 두 번째 제안서도 예비 1순위로 설정 시도 (실패해야 함)
        $response = $this->actingAs($this->agencyUser)
                         ->patchJson("/api/proposals/{$proposal2->id}/set-reserve-rank", [
                             'reserve_rank' => 1
                         ]);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '해당 예비 순위는 이미 다른 제안서에 설정되어 있습니다.'
                 ]);
    }

    /**
     * 낙찰된 제안서에 예비 순위 설정 시도 테스트
     */
    public function test_cannot_set_reserve_rank_for_awarded_proposal()
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

        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'awarded' // 이미 낙찰된 상태
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->patchJson("/api/proposals/{$proposal->id}/set-reserve-rank", [
                             'reserve_rank' => 1
                         ]);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '낙찰된 제안서에는 예비 순위를 설정할 수 없습니다.'
                 ]);
    }

    /**
     * 이미 계약이 생성된 제안서의 상태 변경 시도 테스트
     */
    public function test_cannot_change_proposal_status_when_contract_exists()
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

        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'awarded'
        ]);

        // 계약 생성
        Contract::factory()->create([
            'proposal_id' => $proposal->id,
            'vendor_id' => $this->vendor->id,
            'announcement_id' => $announcement->id,
            'payment_status' => 'pending'
        ]);

        // 계약이 있는 제안서를 유찰 시도
        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '이미 계약이 체결된 제안서는 상태를 변경할 수 없습니다.'
                 ]);
    }

    /**
     * 공고 마감 직전 (1분 전) 제안서 제출 테스트
     */
    public function test_proposal_submission_just_before_deadline()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);
        
        // 1분 후 마감되는 공고
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->addMinute()
        ]);

        $proposalData = [
            'cover_letter' => 'Last minute proposal',
            'price_proposal' => 1000000,
            'portfolio_content' => 'Test portfolio',
            'additional_proposal' => 'Test additional'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $proposalData);

        $response->assertStatus(201);
    }

    /**
     * 공고 마감 직후 (1분 후) 제안서 제출 실패 테스트
     */
    public function test_proposal_submission_just_after_deadline()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);
        
        // 1분 전에 마감된 공고
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->subMinute()
        ]);

        $proposalData = [
            'cover_letter' => 'Too late proposal',
            'price_proposal' => 1000000,
            'portfolio_content' => 'Test portfolio',
            'additional_proposal' => 'Test additional'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", $proposalData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 제안서를 제출할 수 없는 공고입니다. 공고가 마감되었거나 종료되었습니다.'
                 ]);
    }

    /**
     * 잘못된 상태의 RFP 발행 시도 테스트
     */
    public function test_cannot_publish_rfp_with_invalid_status()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        
        // draft 상태의 RFP
        $draftRfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'draft'
        ]);

        // published 상태의 RFP
        $publishedRfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'published'
        ]);

        $publishData = [
            'closing_at' => now()->addDays(14)->toISOString(),
            'channel_type' => 'public',
            'contact_info_private' => false,
            'evaluation_criteria' => [
                'price_weight' => 50,
                'portfolio_weight' => 30,
                'additional_weight' => 20,
                'price_deduction_rate' => 5,
                'price_rank_deduction_points' => [10, 20]
            ]
        ];

        // draft 상태 RFP 발행 시도
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$draftRfp->id}/publish", $publishData);
        $response->assertStatus(409);

        // 이미 published 상태인 RFP 재발행 시도
        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$publishedRfp->id}/publish", $publishData);
        $response->assertStatus(409);
    }
} 