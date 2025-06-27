<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencyMember;
use App\Models\Project;
use App\Models\Rfp;
use App\Models\RfpElement;
use App\Models\ElementDefinition;
use Illuminate\Support\Facades\Hash;

class RfpApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $agencyUser;
    protected $agency;
    protected $elementDefinitions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 대행사 및 사용자 생성
        $this->agency = Agency::factory()->create([
            'name' => '테스트 대행사'
        ]);

        $this->agencyUser = User::factory()->create([
            'email' => 'agency@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'agency_member'
        ]);

        AgencyMember::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->agencyUser->id
        ]);

        // 테스트용 요소 정의 생성
        $this->elementDefinitions = [
            ElementDefinition::factory()->create(['element_type' => 'stage']),
            ElementDefinition::factory()->create(['element_type' => 'lighting']),
            ElementDefinition::factory()->create(['element_type' => 'sound'])
        ];
    }

    /**
     * RFP 생성 성공 테스트
     */
    public function test_agency_user_can_create_rfp_with_elements()
    {
        $rfpData = [
            'project_name' => '2024 신년 행사',
            'start_datetime' => '2024-02-01T09:00:00Z',
            'end_datetime' => '2024-02-01T18:00:00Z',
            'is_indoor' => true,
            'location' => '서울시 강남구 코엑스',
            'issue_type' => 'integrated',
            'closing_at' => now()->addDays(7)->toISOString(),
            'budget_including_vat' => 50000000,
            'elements' => [
                [
                    'element_type' => 'stage',
                    'details' => ['size' => '10m x 8m', 'height' => '1.2m'],
                    'allocated_budget' => 20000000,
                    'prepayment_ratio' => 0.3,
                    'balance_ratio' => 0.7
                ],
                [
                    'element_type' => 'lighting',
                    'details' => ['fixtures' => 'LED 조명 시스템'],
                    'allocated_budget' => 15000000,
                    'prepayment_ratio' => 0.4,
                    'balance_ratio' => 0.6
                ]
            ]
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/rfps', $rfpData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'rfp' => [
                         'id',
                         'current_status',
                         'issue_type',
                         'project' => ['id', 'project_name'],
                         'elements' => [
                             '*' => ['id', 'element_type', 'allocated_budget']
                         ]
                     ]
                 ]);

        // 데이터베이스 확인
        $this->assertDatabaseHas('projects', [
            'project_name' => '2024 신년 행사',
            'agency_id' => $this->agency->id
        ]);

        $this->assertDatabaseHas('rfps', [
            'current_status' => 'draft',
            'issue_type' => 'integrated'
        ]);

        $this->assertDatabaseCount('rfp_elements', 2);
    }

    /**
     * RFP 생성 시 유효성 검사 실패 테스트
     */
    public function test_rfp_creation_fails_with_invalid_data()
    {
        $invalidData = [
            'project_name' => '', // 필수 필드 누락
            'start_datetime' => 'invalid-date',
            'elements' => [] // 최소 1개 요소 필요
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/rfps', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'project_name',
                     'start_datetime',
                     'elements'
                 ]);
    }

    /**
     * RFP 목록 조회 테스트
     */
    public function test_agency_user_can_view_their_rfps()
    {
        // 테스트용 RFP 생성
        $project = Project::factory()->create(['agency_id' => $this->agency->id]);
        $rfp = Rfp::factory()->create([
            'project_id' => $project->id,
            'agency_id' => $this->agency->id
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson('/api/rfps');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'rfps' => [
                         'data' => [
                             '*' => ['id', 'current_status', 'project', 'elements']
                         ]
                     ]
                 ]);
    }

    /**
     * 다른 대행사의 RFP는 조회할 수 없음을 테스트
     */
    public function test_agency_user_cannot_view_other_agency_rfps()
    {
        // 다른 대행사 생성
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['user_type' => 'agency_member']);
        AgencyMember::create([
            'agency_id' => $otherAgency->id,
            'user_id' => $otherUser->id
        ]);

        // 다른 대행사의 RFP 생성
        $otherProject = Project::factory()->create(['agency_id' => $otherAgency->id]);
        $otherRfp = Rfp::factory()->create([
            'project_id' => $otherProject->id,
            'agency_id' => $otherAgency->id
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/rfps/{$otherRfp->id}");

        $response->assertStatus(403);
    }

    /**
     * 권한 없는 사용자는 RFP를 생성할 수 없음을 테스트
     */
    public function test_unauthorized_user_cannot_create_rfp()
    {
        $vendorUser = User::factory()->create(['user_type' => 'vendor_member']);

        $rfpData = [
            'project_name' => '테스트 프로젝트',
            'start_datetime' => now()->addWeek()->toISOString(),
            'end_datetime' => now()->addWeek()->addHours(8)->toISOString(),
            'is_indoor' => true,
            'location' => '테스트 장소',
            'issue_type' => 'integrated',
            'closing_at' => now()->addDays(7)->toISOString(),
            'elements' => [
                [
                    'element_type' => 'stage',
                    'details' => ['test' => 'data']
                ]
            ]
        ];

        $response = $this->actingAs($vendorUser)
                         ->postJson('/api/rfps', $rfpData);

        $response->assertStatus(403);
    }

    /**
     * RFP 생성 시 트랜잭션 롤백 테스트 (유효성 검사 실패)
     */
    public function test_rfp_creation_rolls_back_on_error()
    {
        // 유효하지 않은 날짜로 유효성 검사 실패 유발
        $rfpData = [
            'project_name' => '오류 테스트 프로젝트',
            'start_datetime' => now()->addWeek()->toISOString(),
            'end_datetime' => now()->subWeek()->toISOString(), // end_datetime가 start_datetime보다 이전 
            'is_indoor' => true,
            'location' => '테스트 장소',
            'issue_type' => 'integrated',
            'closing_at' => now()->addDays(7)->toISOString(),
            'elements' => [
                [
                    'element_type' => 'stage', // 유효한 타입 사용
                    'details' => ['test' => 'data']
                ]
            ]
        ];

        $initialProjectCount = Project::count();
        $initialRfpCount = Rfp::count();

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/rfps', $rfpData);

        $response->assertStatus(422); // 유효성 검사 실패

        // 롤백으로 인해 레코드 수가 변하지 않았는지 확인
        $this->assertEquals($initialProjectCount, Project::count());
        $this->assertEquals($initialRfpCount, Rfp::count());
    }
} 