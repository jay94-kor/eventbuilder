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
use App\Models\RfpElement;
use App\Models\Announcement;
use App\Models\ElementDefinition;
use Illuminate\Support\Facades\Hash;

class AnnouncementApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $agencyUser;
    protected $vendorUser;
    protected $agency;
    protected $vendor;
    protected $approvedRfp;

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
        $this->vendor = Vendor::factory()->create([
            'specialties' => ['stage', 'lighting']
        ]);
        $this->vendorUser = User::factory()->create(['user_type' => 'vendor_member']);
        VendorMember::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->vendorUser->id
        ]);

        // 승인된 RFP 생성
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $this->approvedRfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        // RFP 요소 생성
        ElementDefinition::factory()->create(['element_type' => 'stage']);
        ElementDefinition::factory()->create(['element_type' => 'lighting']);
        
        RfpElement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'element_type' => 'stage'
        ]);
        RfpElement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'element_type' => 'lighting'
        ]);
    }

    /**
     * 관리자가 승인된 RFP를 공고로 발행할 수 있는지 테스트
     */
    public function test_admin_can_publish_approved_rfp_as_announcement()
    {
        $publishData = [
            'closing_at' => now()->addDays(14)->toISOString(),
            'estimated_price' => 45000000,
            'channel_type' => 'public',
            'contact_info_private' => false,
            'evaluation_criteria' => [
                'price_weight' => 40,
                'portfolio_weight' => 35,
                'additional_weight' => 25,
                'price_deduction_rate' => 5,
                'price_rank_deduction_points' => [10, 20, 30]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$this->approvedRfp->id}/publish", $publishData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'rfp_status',
                     'announcements_count'
                 ])
                 ->assertJson([
                     'rfp_status' => 'published'
                 ]);

        // RFP 상태가 published로 변경되었는지 확인
        $this->approvedRfp->refresh();
        $this->assertEquals('published', $this->approvedRfp->current_status);

        // 공고가 생성되었는지 확인
        $this->assertDatabaseHas('announcements', [
            'rfp_id' => $this->approvedRfp->id,
            'status' => 'open',
            'channel_type' => 'public'
        ]);
    }

    /**
     * 평가 기준 총합이 100%가 아닐 때 발행이 실패하는지 테스트
     */
    public function test_rfp_publish_fails_when_evaluation_criteria_sum_not_100()
    {
        $publishData = [
            'closing_at' => now()->addDays(14)->toISOString(),
            'channel_type' => 'public',
            'contact_info_private' => false,
            'evaluation_criteria' => [
                'price_weight' => 40,
                'portfolio_weight' => 35,
                'additional_weight' => 30, // 총합이 105%
                'price_deduction_rate' => 5,
                'price_rank_deduction_points' => [10, 20]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$this->approvedRfp->id}/publish", $publishData);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => '평가 기준의 총 비중은 100%여야 합니다.'
                 ]);
    }

    /**
     * 승인되지 않은 RFP는 발행할 수 없는지 테스트
     */
    public function test_non_approved_rfp_cannot_be_published()
    {
        $draftRfp = Rfp::factory()->create([
            'project_id' => $this->approvedRfp->project_id,
            'agency_id' => $this->agency->id,
            'current_status' => 'draft'
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

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$draftRfp->id}/publish", $publishData);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => '승인되지 않았거나 이미 공고된 RFP입니다.'
                 ]);
    }

    /**
     * 요소별 분리 발주 시 여러 공고가 생성되는지 테스트
     */
    public function test_separated_by_element_creates_multiple_announcements()
    {
        $separatedRfp = Rfp::factory()->create([
            'project_id' => $this->approvedRfp->project_id,
            'agency_id' => $this->agency->id,
            'current_status' => 'approved',
            'issue_type' => 'separated_by_element'
        ]);

        RfpElement::factory()->create([
            'rfp_id' => $separatedRfp->id,
            'element_type' => 'stage'
        ]);
        RfpElement::factory()->create([
            'rfp_id' => $separatedRfp->id,
            'element_type' => 'lighting'
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

        $response = $this->actingAs($this->adminUser)
                         ->postJson("/api/rfps/{$separatedRfp->id}/publish", $publishData);

        $response->assertStatus(200)
                 ->assertJson([
                     'announcements_count' => 2
                 ]);

        // 2개의 공고가 생성되었는지 확인
        $this->assertDatabaseCount('announcements', 2);
    }

    /**
     * 용역사가 자신의 전문 분야 공고를 조회할 수 있는지 테스트
     */
    public function test_vendor_can_view_announcements_in_their_specialty()
    {
        // 공고 생성
        $announcement = Announcement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'channel_type' => 'public',
            'closing_at' => now()->addDays(7)
        ]);

        $rfpElement = RfpElement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'element_type' => 'stage' // 용역사의 전문 분야
        ]);

        $announcement->update(['rfp_element_id' => $rfpElement->id]);

        $response = $this->actingAs($this->vendorUser)
                         ->getJson('/api/announcements');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'announcements' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'status',
                                 'closing_at',
                                 'channel_type'
                             ]
                         ]
                     ]
                 ]);
    }

    /**
     * 대행사 전용 채널 공고에 대한 접근 권한 테스트
     */
    public function test_agency_private_announcement_access_control()
    {
        $privateAnnouncement = Announcement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'channel_type' => 'agency_private',
            'closing_at' => now()->addDays(7)
        ]);

        // 승인되지 않은 용역사는 접근 불가
        $response = $this->actingAs($this->vendorUser)
                         ->getJson("/api/announcements/{$privateAnnouncement->id}");

        $response->assertStatus(403);

        // 해당 대행사 멤버는 접근 가능
        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/announcements/{$privateAnnouncement->id}");

        $response->assertStatus(200);
    }

    /**
     * 마감된 공고는 목록에서 제외되는지 테스트
     */
    public function test_closed_announcements_not_shown_to_vendors()
    {
        $closedAnnouncement = Announcement::factory()->create([
            'rfp_id' => $this->approvedRfp->id,
            'agency_id' => $this->agency->id,
            'status' => 'open',
            'channel_type' => 'public',
            'closing_at' => now()->subDay() // 이미 마감
        ]);

        $response = $this->actingAs($this->vendorUser)
                         ->getJson('/api/announcements');

        $response->assertStatus(200);
        
        $announcements = $response->json('announcements.data');
        $this->assertEmpty($announcements);
    }

    /**
     * 권한 없는 사용자는 RFP를 발행할 수 없는지 테스트
     */
    public function test_non_admin_cannot_publish_rfp()
    {
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

        $response = $this->actingAs($this->agencyUser)
                         ->postJson("/api/rfps/{$this->approvedRfp->id}/publish", $publishData);

        $response->assertStatus(403);
    }
} 