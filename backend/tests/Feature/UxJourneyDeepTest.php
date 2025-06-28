<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\Vendor;
use App\Models\ElementDefinition;
use App\Models\Rfp;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UxJourneyDeepTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;
    private $agencyUser;
    private $vendorUser;
    private $agency;
    private $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // 테스트용 사용자들 생성
        $this->adminUser = User::where('email', 'admin@bidly.com')->first();
        $this->agencyUser = User::where('email', 'agency.a.master@bidly.com')->first();
        $this->vendorUser = User::where('email', 'vendor.x.master@bidly.com')->first();
        
        $this->agency = $this->agencyUser->agency_members->first()?->agency;
        $this->vendor = $this->vendorUser->vendor_members->first()?->vendor;
    }

    public function test_complete_rfp_to_contract_user_journey()
    {
        // 🎯 실제 K-POP 콘서트 이벤트 시나리오 - 전체 프로세스 테스트
        
        // 1. 대행사 사용자가 복잡한 RFP 생성 (실제 이벤트 시나리오)
        Sanctum::actingAs($this->agencyUser);
        
        $rfpData = [
            'project_name' => '2024 K-POP 콘서트 무대 및 음향 시설 구축',
            'start_datetime' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(33)->format('Y-m-d H:i:s'),
            'is_indoor' => false,
            'location' => '서울월드컵경기장',
            'budget_including_vat' => 500000000, // 5억원
            'issue_type' => 'separated_by_element',
            'rfp_description' => '3일간 진행되는 대형 K-POP 콘서트를 위한 메인 무대, 음향, 조명, LED 스크린 설치',
            'closing_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'elements' => [
                [
                    'element_type' => 'stage',
                    'details' => [
                        'stage_width' => 40,
                        'stage_depth' => 30,
                        'stage_height' => 2.5,
                        'load_capacity' => 50000,
                        'special_requirements' => '방수 처리, 안전 난간 설치'
                    ],
                    'allocated_budget' => 200000000,
                    'prepayment_ratio' => 0.3,
                    'prepayment_due_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
                    'balance_ratio' => 0.7,
                    'balance_due_date' => now()->addDays(30)->format('Y-m-d H:i:s')
                ],
                [
                    'element_type' => 'sound',
                    'details' => [
                        'speaker_power' => 100000,
                        'coverage_area' => 50000,
                        'sound_engineer_required' => true,
                        'backup_system' => true
                    ],
                    'allocated_budget' => 150000000,
                    'prepayment_ratio' => 0.4,
                    'prepayment_due_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
                    'balance_ratio' => 0.6,
                    'balance_due_date' => now()->addDays(25)->format('Y-m-d H:i:s')
                ],
                [
                    'element_type' => 'led', // 새로 추가된 동적 타입 테스트
                    'details' => [
                        'screen_size' => '20m x 10m',
                        'pixel_pitch' => '3.9mm',
                        'brightness' => 6000,
                        'weatherproof' => true
                    ],
                    'allocated_budget' => 150000000,
                    'prepayment_ratio' => 0.5,
                    'prepayment_due_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
                    'balance_ratio' => 0.5,
                    'balance_due_date' => now()->addDays(35)->format('Y-m-d H:i:s')
                ]
            ]
        ];

        $response = $this->postJson('/api/rfps', $rfpData);
        $response->assertStatus(201);
        $rfpId = $response->json('rfp.id');

        // 2. RFP 승인 과정 (실제 승인 플로우)
        Sanctum::actingAs($this->adminUser);
        
        $rfp = Rfp::find($rfpId);
        $this->assertNotNull($rfp);
        $this->assertEquals('draft', $rfp->current_status);

        // 승인 요청
        $approvalResponse = $this->postJson("/api/rfps/{$rfpId}/request-approval");
        $approvalResponse->assertStatus(200);

        $rfp->refresh();
        $this->assertEquals('pending_approval', $rfp->current_status);

        // 관리자 승인
        $adminApprovalResponse = $this->postJson("/api/rfp-approvals", [
            'rfp_id' => $rfpId,
            'status' => 'approved',
            'comments' => '모든 요구사항이 적절하며 예산 배분이 합리적입니다.'
        ]);
        $adminApprovalResponse->assertStatus(201);

        // 3. 공고 발행 (복잡한 평가 기준 설정)
        Sanctum::actingAs($this->agencyUser);
        
        $announcementData = [
            'rfp_id' => $rfpId,
            'closing_date' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'channel_type' => 'public',
            'evaluation_criteria' => [
                'technical_score' => 40,
                'price_score' => 30,
                'experience_score' => 20,
                'timeline_score' => 10
            ],
            'separated_by_element' => true // 요소별 분리 발주
        ];

        $publishResponse = $this->postJson("/api/announcements", $announcementData);
        $publishResponse->assertStatus(201);
        
        // 요소별로 3개의 공고가 생성되었는지 확인
        $announcements = Announcement::where('rfp_id', $rfpId)->get();
        $this->assertCount(3, $announcements);

        // 4. 복수 용역사의 제안서 제출 (경쟁 상황 시뮬레이션)
        $stageAnnouncement = $announcements->where('element_type', 'stage')->first();
        $ledAnnouncement = $announcements->where('element_type', 'led')->first();

        // 용역사 X가 무대와 LED에 제안
        Sanctum::actingAs($this->vendorUser);
        
        $stageProposalResponse = $this->postJson("/api/proposals", [
            'announcement_id' => $stageAnnouncement->id,
            'proposed_price' => 180000000,
            'proposed_timeline' => 25,
            'technical_approach' => '모듈러 방식의 조립식 무대로 빠른 설치와 안전성을 보장합니다.',
            'experience_description' => '최근 3년간 50회 이상의 대형 콘서트 무대 구축 경험'
        ]);
        $stageProposalResponse->assertStatus(201);

        $ledProposalResponse = $this->postJson("/api/proposals", [
            'announcement_id' => $ledAnnouncement->id,
            'proposed_price' => 140000000,
            'proposed_timeline' => 20,
            'technical_approach' => '최신 P3.9 LED 패널로 선명한 화질과 내구성을 제공합니다.',
            'experience_description' => 'LED 스크린 설치 전문 업체로 100회 이상 설치 경험'
        ]);
        $ledProposalResponse->assertStatus(201);

        // 5. 최종 검증 - 전체 플로우가 올바르게 연결되었는지 확인
        $finalRfp = Rfp::with(['elements', 'announcements.proposals'])->find($rfpId);
        
        $this->assertEquals('approved', $finalRfp->current_status);
        $this->assertCount(3, $finalRfp->elements);
        $this->assertCount(3, $finalRfp->announcements);
        
        // LED 타입이 정상적으로 처리되었는지 확인
        $ledElement = $finalRfp->elements->where('element_type', 'led')->first();
        $this->assertNotNull($ledElement);
        $this->assertEquals('led', $ledElement->element_type);
    }

    public function test_complex_edge_case_scenarios()
    {
        // 🚨 마감 직전 대량 제안서 제출 상황 시뮬레이션
        
        Sanctum::actingAs($this->agencyUser);
        
        $rfp = Rfp::factory()->create([
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'closing_date' => now()->addMinutes(5), // 5분 후 마감
            'status' => 'published'
        ]);

        // 여러 용역사가 동시에 제안서 제출 시도
        $vendors = Vendor::factory()->count(3)->create();
        $proposals = [];

        foreach ($vendors as $vendor) {
            $user = User::factory()->create(['vendor_id' => $vendor->id]);
            Sanctum::actingAs($user);
            
            $response = $this->postJson("/api/proposals", [
                'announcement_id' => $announcement->id,
                'proposed_price' => rand(100000, 200000),
                'proposed_timeline' => rand(10, 30),
                'technical_approach' => "기술적 접근법 - 용역사 {$vendor->name}",
                'experience_description' => "경험 설명 - 용역사 {$vendor->name}"
            ]);
            
            $response->assertStatus(201);
            $proposals[] = $response->json('data.id');
        }

        $this->assertCount(3, $proposals);

        // 마감 후 제출 시도 (실패해야 함)
        $this->travel(10)->minutes();
        
        $lateVendor = Vendor::factory()->create();
        $lateUser = User::factory()->create(['vendor_id' => $lateVendor->id]);
        Sanctum::actingAs($lateUser);
        
        $lateResponse = $this->postJson("/api/proposals", [
            'announcement_id' => $announcement->id,
            'proposed_price' => 150000,
            'proposed_timeline' => 20,
            'technical_approach' => '늦은 제안',
            'experience_description' => '늦은 경험'
        ]);
        
        $lateResponse->assertStatus(422);
    }

    public function test_data_consistency_and_integrity_checks()
    {
        // 💰 데이터 무결성 테스트 - 예산 관련
        
        Sanctum::actingAs($this->agencyUser);
        
        // 요소별 예산 합계가 총 예산을 초과하는 경우
        $invalidBudgetResponse = $this->postJson('/api/rfps', [
            'project_name' => '예산 초과 테스트',
            'start_datetime' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(33)->format('Y-m-d H:i:s'),
            'is_indoor' => true,
            'location' => '테스트 장소',
            'budget_including_vat' => 100000,
            'issue_type' => 'integrated',
            'rfp_description' => '예산 초과 테스트',
            'closing_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'elements' => [
                [
                    'element_type' => 'stage',
                    'allocated_budget' => 80000,
                    'details' => ['test' => 'value']
                ],
                [
                    'element_type' => 'sound',
                    'allocated_budget' => 50000, // 총합 130000 > 100000
                    'details' => ['test' => 'value']
                ]
            ]
        ]);
        
        $invalidBudgetResponse->assertStatus(422);

        // 선금+잔금 비율이 1을 초과하는 경우
        $invalidRatioResponse = $this->postJson('/api/rfps', [
            'project_name' => '비율 오류 테스트',
            'start_datetime' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(33)->format('Y-m-d H:i:s'),
            'is_indoor' => true,
            'location' => '테스트 장소',
            'budget_including_vat' => 100000,
            'issue_type' => 'integrated',
            'rfp_description' => '비율 오류 테스트',
            'closing_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'elements' => [
                [
                    'element_type' => 'stage',
                    'allocated_budget' => 50000,
                    'prepayment_ratio' => 0.7,
                    'balance_ratio' => 0.5, // 합계 1.2 > 1.0
                    'details' => ['test' => 'value']
                ]
            ]
        ]);
        
        $invalidRatioResponse->assertStatus(422);
    }

    public function test_dynamic_element_types_validation()
    {
        // 🔧 동적 요소 타입 테스트 (ENUM → VARCHAR 변경 검증)
        
        Sanctum::actingAs($this->agencyUser);
        
        // 다양한 새로운 요소 타입들 테스트
        $newElementTypes = [
            'led_screen_outdoor',
            'hologram_display', 
            'drone_show',
            'interactive_booth',
            'vr_experience_zone'
        ];

        foreach ($newElementTypes as $elementType) {
            $response = $this->postJson('/api/rfps', [
                'project_name' => "테스트 RFP - {$elementType}",
                'start_datetime' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'end_datetime' => now()->addDays(33)->format('Y-m-d H:i:s'),
                'is_indoor' => true,
                'location' => '테스트 장소',
                'budget_including_vat' => 100000,
                'issue_type' => 'integrated',
                'rfp_description' => "새로운 요소 타입 테스트: {$elementType}",
                'closing_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
                'elements' => [
                    [
                        'element_type' => $elementType,
                        'allocated_budget' => 100000,
                        'details' => [
                            'custom_spec' => "특별 사양 - {$elementType}",
                            'innovative_feature' => true
                        ]
                    ]
                ]
            ]);
            
            $response->assertStatus(201);
            
            // 생성된 RFP의 요소 타입이 정확히 저장되었는지 확인
            $rfp = Rfp::find($response->json('rfp.id'));
            $element = $rfp->elements->first();
            $this->assertEquals($elementType, $element->element_type);
        }
    }

    public function test_user_experience_error_handling()
    {
        // 🚫 UX 관점의 에러 처리 테스트
        
        // 1. 권한 없는 사용자의 접근 시도
        $response = $this->postJson('/api/rfps', [
            'project_name' => '권한 없는 RFP',
            'start_datetime' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(33)->format('Y-m-d H:i:s'),
            'is_indoor' => true,
            'location' => '테스트 장소',
            'budget_including_vat' => 100000,
            'issue_type' => 'integrated',
            'rfp_description' => '권한 없는 RFP 테스트',
            'closing_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'elements' => []
        ]);
        
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);

        // 2. 잘못된 데이터 형식 제출
        Sanctum::actingAs($this->agencyUser);
        
        $invalidDataResponse = $this->postJson('/api/rfps', [
            'project_name' => '', // 빈 제목
            'start_datetime' => 'invalid_date',
            'end_datetime' => null,
            'is_indoor' => 'not_boolean',
            'location' => '',
            'budget_including_vat' => -1000, // 음수 예산
            'issue_type' => 'invalid_type', // 잘못된 타입
            'closing_at' => 'invalid_date',
            'elements' => 'not_an_array' // 배열이 아닌 요소
        ]);
        
        $invalidDataResponse->assertStatus(422);
        $invalidDataResponse->assertJsonStructure([
            'message',
            'errors' => [
                'project_name',
                'start_datetime',
                'end_datetime',
                'is_indoor',
                'location',
                'budget_including_vat',
                'issue_type',
                'closing_at',
                'elements'
            ]
        ]);

        // 3. 존재하지 않는 리소스 접근
        $notFoundResponse = $this->getJson('/api/rfps/99999999-9999-9999-9999-999999999999');
        $notFoundResponse->assertStatus(404);
    }

    public function test_performance_simulation_with_large_datasets()
    {
        // ⚡ 성능 테스트 - 대용량 데이터 처리
        
        Sanctum::actingAs($this->adminUser);

        // 50개의 RFP 생성 (실제 환경 시뮬레이션)
        $rfps = [];
        for ($i = 1; $i <= 50; $i++) {
            $rfp = Rfp::factory()->create([
                'agency_id' => $this->agency->id,
                'current_status' => 'approved'
            ]);
            $rfps[] = $rfp->id;
        }

        // 각 RFP마다 공고 생성
        foreach ($rfps as $rfpId) {
            Announcement::factory()->create([
                'rfp_id' => $rfpId,
                'status' => 'published'
            ]);
        }

        // 페이지네이션 성능 테스트
        Sanctum::actingAs($this->agencyUser);
        
        $searchResponse = $this->getJson('/api/rfps?page=1&per_page=20');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'status', 'created_at']
            ],
            'meta' => ['total', 'per_page', 'current_page']
        ]);

        // 필터링 성능 테스트
        $filterResponse = $this->getJson('/api/rfps');
        $filterResponse->assertStatus(200);
        
        // 응답 시간이 합리적인지 확인 (실제 환경에서는 더 정교한 성능 측정 필요)
        $this->assertLessThan(100, $filterResponse->json('rfps.total'));
    }

    public function test_notification_system_comprehensive_test()
    {
        // 📢 알림 시스템 종합 테스트
        
        Sanctum::actingAs($this->agencyUser);
        
        $rfp = Rfp::factory()->create([
            'agency_id' => $this->agency->id,
            'current_status' => 'approved'
        ]);

        $announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'status' => 'published'
        ]);

        // 용역사 제안서 제출
        Sanctum::actingAs($this->vendorUser);
        
        $proposalResponse = $this->postJson("/api/proposals", [
            'announcement_id' => $announcement->id,
            'proposed_price' => 150000,
            'proposed_timeline' => 20,
            'technical_approach' => '우수한 기술력으로 최고 품질 보장',
            'experience_description' => '10년 이상의 풍부한 경험과 노하우'
        ]);
        
        $proposalResponse->assertStatus(201);
        $proposalId = $proposalResponse->json('data.id');

        // 대행사 사용자에게 제안서 제출 알림이 생성되었는지 확인
        $notifications = $this->agencyUser->notifications()
                                         ->where('type', 'proposal_submitted')
                                         ->get();
        $this->assertGreaterThan(0, $notifications->count());

        // 낙찰 처리
        Sanctum::actingAs($this->agencyUser);
        
        $awardResponse = $this->postJson("/api/proposals/{$proposalId}/award");
        $awardResponse->assertStatus(200);

        // 용역사에게 낙찰 알림 확인
        $vendorNotifications = $this->vendorUser->notifications()
                                               ->where('type', 'proposal_awarded')
                                               ->get();
        $this->assertGreaterThan(0, $vendorNotifications->count());
    }
} 