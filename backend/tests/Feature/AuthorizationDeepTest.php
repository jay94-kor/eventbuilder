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
use App\Models\Schedule;

class AuthorizationDeepTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $agency1User;
    protected $agency2User;
    protected $vendor1User;
    protected $vendor2User;
    protected $agency1;
    protected $agency2;
    protected $vendor1;
    protected $vendor2;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'user_type' => 'admin'
        ]);

        // 첫 번째 대행사 및 사용자 생성
        $this->agency1 = Agency::factory()->create(['name' => 'Agency 1']);
        $this->agency1User = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $this->agency1->id,
            'user_id' => $this->agency1User->id
        ]);

        // 두 번째 대행사 및 사용자 생성
        $this->agency2 = Agency::factory()->create(['name' => 'Agency 2']);
        $this->agency2User = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $this->agency2->id,
            'user_id' => $this->agency2User->id
        ]);

        // 첫 번째 용역사 및 사용자 생성
        $this->vendor1 = Vendor::factory()->create(['name' => 'Vendor 1']);
        $this->vendor1User = User::factory()->create(['user_type' => 'vendor_member']);
        VendorMember::create([
            'vendor_id' => $this->vendor1->id,
            'user_id' => $this->vendor1User->id
        ]);

        // 두 번째 용역사 및 사용자 생성
        $this->vendor2 = Vendor::factory()->create(['name' => 'Vendor 2']);
        $this->vendor2User = User::factory()->create(['user_type' => 'vendor_member']);
        VendorMember::create([
            'vendor_id' => $this->vendor2->id,
            'user_id' => $this->vendor2User->id
        ]);
    }

    /**
     * 인증되지 않은 사용자가 보호된 엔드포인트에 접근 시도 테스트
     */
    public function test_unauthenticated_user_cannot_access_protected_endpoints()
    {
        // RFP 목록 조회 시도
        $response = $this->getJson('/api/rfps');
        $response->assertStatus(401);

        // 공고 목록 조회 시도
        $response = $this->getJson('/api/announcements');
        $response->assertStatus(401);

        // 스케줄 목록 조회 시도
        $response = $this->getJson('/api/schedules');
        $response->assertStatus(401);

        // 계약 목록 조회 시도
        $response = $this->getJson('/api/contracts');
        $response->assertStatus(401);
    }

    /**
     * 용역사 사용자가 관리자 전용 엔드포인트에 접근 시도 테스트
     */
    public function test_vendor_user_cannot_access_admin_endpoints()
    {
        $project = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency1->id,
            'current_status' => 'approved'
        ]);

        // RFP 발행 시도 (관리자 전용)
        $response = $this->actingAs($this->vendor1User)
                         ->postJson("/api/rfps/{$rfp->id}/publish", [
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
                         ]);

        $response->assertStatus(403);
    }

    /**
     * 대행사 사용자가 다른 대행사의 리소스에 접근 시도 테스트
     */
    public function test_agency_user_cannot_access_other_agency_resources()
    {
        // Agency1의 프로젝트 및 RFP 생성
        $project1 = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp1 = Rfp::factory()->create([
            'project_id' => $project1->id,
            'agency_id' => $this->agency1->id
        ]);

        // Agency2 사용자가 Agency1의 RFP 조회 시도
        $response = $this->actingAs($this->agency2User)
                         ->getJson("/api/rfps/{$rfp1->id}");

        $response->assertStatus(403);

        // Agency2 사용자가 Agency1의 RFP 목록 조회 시도
        $response = $this->actingAs($this->agency2User)
                         ->getJson('/api/rfps');

        $response->assertStatus(200);
        $rfps = $response->json('rfps.data');
        
        // Agency2의 사용자는 Agency1의 RFP를 볼 수 없어야 함
        $this->assertEmpty($rfps);
    }

    /**
     * 용역사 사용자가 다른 용역사의 제안서에 접근 시도 테스트
     */
    public function test_vendor_user_cannot_access_other_vendor_proposals()
    {
        // 공고 생성
        $project = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency1->id,
            'current_status' => 'published'
        ]);
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency1->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);

        // Vendor1이 제안서 제출
        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor1->id,
            'status' => 'submitted'
        ]);

        // Vendor2가 Vendor1의 제안서 조회 시도
        $response = $this->actingAs($this->vendor2User)
                         ->getJson("/api/proposals/{$proposal->id}");

        $response->assertStatus(403);
    }

    /**
     * 대행사 사용자가 다른 대행사의 스케줄에 접근 시도 테스트
     */
    public function test_agency_user_cannot_access_other_agency_schedules()
    {
        // Agency1의 프로젝트 및 스케줄 생성
        $project1 = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $schedule1 = Schedule::factory()->create([
            'schedulable_id' => $project1->id,
            'schedulable_type' => 'App\\Models\\Project'
        ]);

        // Agency2 사용자가 Agency1의 스케줄 조회 시도
        $response = $this->actingAs($this->agency2User)
                         ->getJson("/api/schedules/{$schedule1->id}");

        $response->assertStatus(403);

        // Agency2 사용자가 Agency1의 스케줄 수정 시도
        $response = $this->actingAs($this->agency2User)
                         ->putJson("/api/schedules/{$schedule1->id}", [
                             'title' => 'Updated Title'
                         ]);

        $response->assertStatus(403);

        // Agency2 사용자가 Agency1의 스케줄 삭제 시도
        $response = $this->actingAs($this->agency2User)
                         ->deleteJson("/api/schedules/{$schedule1->id}");

        $response->assertStatus(403);
    }

    /**
     * 용역사 사용자가 제안서 제출 외의 대행사 전용 작업 시도 테스트
     */
    public function test_vendor_user_cannot_perform_agency_only_actions()
    {
        // 공고 생성
        $project = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency1->id,
            'current_status' => 'published'
        ]);
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency1->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);

        $proposal = Proposal::factory()->create([
            'announcement_id' => $announcement->id,
            'vendor_id' => $this->vendor1->id,
            'status' => 'submitted'
        ]);

        // 용역사 사용자가 제안서 낙찰 시도 (대행사 전용)
        $response = $this->actingAs($this->vendor1User)
                         ->postJson("/api/proposals/{$proposal->id}/award");

        $response->assertStatus(403);

        // 용역사 사용자가 제안서 유찰 시도 (대행사 전용)
        $response = $this->actingAs($this->vendor1User)
                         ->postJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(403);

        // 용역사 사용자가 예비 순위 설정 시도 (대행사 전용)
        $response = $this->actingAs($this->vendor1User)
                         ->patchJson("/api/proposals/{$proposal->id}/set-reserve-rank", [
                             'rank' => 1
                         ]);

        $response->assertStatus(403);
    }

    /**
     * 대행사 사용자가 제안서 제출 시도 테스트 (용역사 전용)
     */
    public function test_agency_user_cannot_submit_proposals()
    {
        // 공고 생성
        $project = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency1->id,
            'current_status' => 'published'
        ]);
        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency1->id,
            'status' => 'open',
            'closing_at' => now()->addDays(7)
        ]);

        // 대행사 사용자가 제안서 제출 시도
        $response = $this->actingAs($this->agency1User)
                         ->postJson("/api/announcements/{$announcement->id}/proposals", [
                             'cover_letter' => 'Test cover letter',
                             'price_proposal' => 1000000,
                             'portfolio_content' => 'Test portfolio',
                             'additional_proposal' => 'Test additional'
                         ]);

        $response->assertStatus(403);
    }

    /**
     * 관리자가 모든 리소스에 접근 가능한지 테스트
     */
    public function test_admin_can_access_all_resources()
    {
        // Agency1의 리소스들 생성
        $project1 = Project::factory()->create(['agency_id' => $this->agency1->id]);
        $rfp1 = Rfp::factory()->create([
            'project_id' => $project1->id,
            'agency_id' => $this->agency1->id
        ]);
        $schedule1 = Schedule::factory()->create([
            'schedulable_id' => $project1->id,
            'schedulable_type' => 'App\\Models\\Project'
        ]);

        // Agency2의 리소스들 생성
        $project2 = Project::factory()->create(['agency_id' => $this->agency2->id]);
        $rfp2 = Rfp::factory()->create([
            'project_id' => $project2->id,
            'agency_id' => $this->agency2->id
        ]);

        // 관리자가 Agency1의 RFP 조회
        $response = $this->actingAs($this->adminUser)
                         ->getJson("/api/rfps/{$rfp1->id}");
        $response->assertStatus(200);

        // 관리자가 Agency2의 RFP 조회
        $response = $this->actingAs($this->adminUser)
                         ->getJson("/api/rfps/{$rfp2->id}");
        $response->assertStatus(200);

        // 관리자가 모든 스케줄 조회
        $response = $this->actingAs($this->adminUser)
                         ->getJson('/api/schedules');
        $response->assertStatus(200);
        
        $schedules = $response->json('schedules.data');
        $this->assertNotEmpty($schedules);
    }

    /**
     * 잘못된 Bearer 토큰으로 접근 시도 테스트
     */
    public function test_invalid_bearer_token_access()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->getJson('/api/rfps');

        $response->assertStatus(401);
    }

    /**
     * 만료된 토큰으로 접근 시도 시뮬레이션
     */
    public function test_expired_token_simulation()
    {
        // 사용자 생성하고 토큰 발급
        $user = User::factory()->create(['user_type' => 'agency_member']);
        $token = $user->createToken('test-token');

        // 토큰을 수동으로 만료시킴 (실제 환경에서는 시간이 지나면 자동 만료)
        $token->accessToken->delete();

        // 만료된 토큰으로 접근 시도
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
            'Accept' => 'application/json'
        ])->getJson('/api/rfps');

        $response->assertStatus(401);
    }
} 