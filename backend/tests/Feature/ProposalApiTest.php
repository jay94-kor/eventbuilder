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
use Illuminate\Support\Facades\Hash;

class ProposalApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $vendorUser;
    protected $agencyUser;
    protected $adminUser;
    protected $vendor;
    protected $agency;
    protected $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 생성
        $this->adminUser = User::factory()->create(['user_type' => 'admin']);

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

        // 공고 생성
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id
        ]);
        $this->announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);
    }

    /**
     * 용역사가 제안서를 제출할 수 있는지 테스트
     */
    public function test_vendor_can_submit_proposal_to_open_announcement()
    {
        $proposalData = [
            'proposed_price' => 42000000,
            'proposal_text' => '저희 회사는 10년간의 무대 설치 경험을 바탕으로 최고의 서비스를 제공하겠습니다.'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/proposals", $proposalData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'proposal' => [
                         'id',
                         'proposed_price',
                         'proposal_text',
                         'status'
                     ]
                 ])
                 ->assertJson([
                     'proposal' => [
                         'status' => 'submitted'
                     ]
                 ]);

        // 데이터베이스 확인
        $this->assertDatabaseHas('proposals', [
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'proposed_price' => 42000000,
            'status' => 'submitted'
        ]);
    }

    /**
     * 중복 제안서 제출이 금지되는지 테스트
     */
    public function test_vendor_cannot_submit_duplicate_proposal()
    {
        // 첫 번째 제안서 제출
        Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id
        ]);

        // 두 번째 제안서 제출 시도
        $proposalData = [
            'proposed_price' => 40000000,
            'proposal_text' => '두 번째 제안서'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/proposals", $proposalData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '이미 해당 공고에 제안서를 제출했습니다.'
                 ]);
    }

    /**
     * 마감된 공고에는 제안서를 제출할 수 없는지 테스트
     */
    public function test_cannot_submit_proposal_to_closed_announcement()
    {
        // 공고 마감
        $this->announcement->update([
            'status' => 'closed',
            'closing_at' => now()->subDay()
        ]);

        $proposalData = [
            'proposed_price' => 42000000,
            'proposal_text' => '마감된 공고에 제출하는 제안서'
        ];

        $response = $this->actingAs($this->vendorUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/proposals", $proposalData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '현재 제안서를 제출할 수 없는 공고입니다. 공고가 마감되었거나 종료되었습니다.'
                 ]);
    }

    /**
     * 대행사 사용자는 제안서를 제출할 수 없는지 테스트
     */
    public function test_agency_user_cannot_submit_proposal()
    {
        $proposalData = [
            'proposed_price' => 42000000,
            'proposal_text' => '대행사 사용자의 제안서'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/announcements/{$this->announcement->id}/proposals", $proposalData);

        $response->assertStatus(403)
                 ->assertJson([
                     'message' => '제안서 제출 권한이 없습니다.'
                 ]);
    }

    /**
     * 대행사 사용자가 자신의 공고에 제출된 제안서 목록을 조회할 수 있는지 테스트
     */
    public function test_agency_user_can_view_proposals_for_their_announcement()
    {
        // 제안서 생성
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/announcements/{$this->announcement->id}/proposals");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'proposals' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'proposed_price',
                                 'status',
                                 'vendor'
                             ]
                         ]
                     ]
                 ]);
    }

    /**
     * 다른 대행사 사용자는 제안서 목록을 조회할 수 없는지 테스트
     */
    public function test_other_agency_user_cannot_view_proposals()
    {
        // 다른 대행사 생성
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $otherAgency->id,
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($otherUser)
                         ->getJson("/api/announcements/{$this->announcement->id}/proposals");

        $response->assertStatus(403);
    }

    /**
     * 제안서 낙찰 처리 테스트
     */
    public function test_agency_user_can_award_proposal()
    {
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $awardData = [
            'final_price' => 40000000
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal->id}/award", $awardData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'proposal',
                     'contract' => [
                         'id',
                         'final_price',
                         'payment_status'
                     ]
                 ]);

        // 제안서 상태 확인
        $proposal->refresh();
        $this->assertEquals('awarded', $proposal->status);

        // 계약 생성 확인
        $this->assertDatabaseHas('contracts', [
            'proposal_id' => $proposal->id,
            'vendor_id' => $this->vendor->id,
            'final_price' => 40000000,
            'payment_status' => 'pending'
        ]);

        // 공고 상태가 closed로 변경되었는지 확인
        $this->announcement->refresh();
        $this->assertEquals('closed', $this->announcement->status);
    }

    /**
     * 제안서 낙찰 시 다른 제안서들이 자동으로 유찰 처리되는지 테스트
     */
    public function test_awarding_proposal_rejects_other_proposals()
    {
        // 여러 제안서 생성
        $winningProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $otherVendor = Vendor::factory()->create();
        $rejectedProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $otherVendor->id,
            'status' => 'submitted'
        ]);

        // 낙찰 처리
        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$winningProposal->id}/award");

        $response->assertStatus(200);

        // 다른 제안서가 자동으로 유찰 처리되었는지 확인
        $rejectedProposal->refresh();
        $this->assertEquals('rejected', $rejectedProposal->status);
    }

    /**
     * 이미 낙찰된 공고에는 추가 낙찰이 불가능한지 테스트
     */
    public function test_cannot_award_multiple_proposals_in_same_announcement()
    {
        // 첫 번째 제안서 낙찰
        $firstProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'awarded'
        ]);

        // 두 번째 제안서 생성
        $otherVendor = Vendor::factory()->create();
        $secondProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $otherVendor->id,
            'status' => 'submitted'
        ]);

        // 두 번째 제안서 낙찰 시도
        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$secondProposal->id}/award");

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '이 공고에는 이미 낙찰된 제안서가 있습니다.'
                 ]);
    }

    /**
     * 제안서 유찰 처리 테스트
     */
    public function test_agency_user_can_reject_proposal()
    {
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => '제안서가 성공적으로 유찰되었습니다.'
                 ]);

        // 제안서 상태 확인
        $proposal->refresh();
        $this->assertEquals('rejected', $proposal->status);
    }

    /**
     * 예비 순위 설정 테스트
     */
    public function test_agency_user_can_set_reserve_rank()
    {
        $proposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted'
        ]);

        $rankData = [
            'reserve_rank' => 1
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->patchJson("/api/proposals/{$proposal->id}/set-reserve-rank", $rankData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => '제안서의 예비 순위가 성공적으로 설정되었습니다.'
                 ]);

        // 예비 순위 설정 확인
        $proposal->refresh();
        $this->assertEquals(1, $proposal->reserve_rank);
    }

    /**
     * 중복된 예비 순위 설정이 금지되는지 테스트
     */
    public function test_duplicate_reserve_rank_not_allowed()
    {
        // 첫 번째 제안서에 예비 순위 1 설정
        $firstProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id,
            'reserve_rank' => 1
        ]);

        // 두 번째 제안서 생성
        $otherVendor = Vendor::factory()->create();
        $secondProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $otherVendor->id
        ]);

        // 같은 예비 순위 설정 시도
        $rankData = [
            'reserve_rank' => 1
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->patchJson("/api/proposals/{$secondProposal->id}/set-reserve-rank", $rankData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '해당 예비 순위는 이미 다른 제안서에 설정되어 있습니다.'
                 ]);
    }

    /**
     * 용역사가 자신의 제안서만 조회할 수 있는지 테스트
     */
    public function test_vendor_can_only_view_own_proposals()
    {
        $ownProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $this->vendor->id
        ]);

        $otherVendor = Vendor::factory()->create();
        $otherProposal = Proposal::factory()->create([
            'announcement_id' => $this->announcement->id,
            'vendor_id' => $otherVendor->id
        ]);

        // 자신의 제안서 조회 - 성공
        $response = $this->actingAs($this->vendorUser)
                         ->getJson("/api/proposals/{$ownProposal->id}");
        $response->assertStatus(200);

        // 다른 용역사의 제안서 조회 - 실패
        $response = $this->actingAs($this->vendorUser)
                         ->getJson("/api/proposals/{$otherProposal->id}");
        $response->assertStatus(403);
    }
} 