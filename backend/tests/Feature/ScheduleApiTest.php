<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencyMember;
use App\Models\Project;
use App\Models\Announcement;
use App\Models\Schedule;
use App\Models\Rfp;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $agencyUser;
    protected $agency;
    protected $project;
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

        // 프로젝트 생성
        $this->project = Project::factory()->create(['agency_id' => $this->agency->id]);

        // 공고 생성
        $rfp = Rfp::factory()->create([
            'project_id' => $this->project->id,
            'agency_id' => $this->agency->id
        ]);
        $this->announcement = Announcement::factory()->create([
            'rfp_id' => $rfp->id,
            'agency_id' => $this->agency->id
        ]);
    }

    /**
     * 프로젝트에 스케줄 생성 테스트
     */
    public function test_agency_user_can_create_project_schedule()
    {
        $scheduleData = [
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '프로젝트 킥오프 미팅',
            'description' => '프로젝트 시작을 위한 킥오프 미팅',
            'start_datetime' => now()->addDays(1)->toISOString(),
            'end_datetime' => now()->addDays(1)->addHours(2)->toISOString(),
            'location' => '회의실 A',
            'type' => 'meeting'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $scheduleData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'schedule' => [
                         'id',
                         'title',
                         'description',
                         'start_datetime',
                         'end_datetime',
                         'location',
                         'type',
                         'status'
                     ]
                 ]);

        // 데이터베이스 확인
        $this->assertDatabaseHas('schedules', [
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '프로젝트 킥오프 미팅',
            'type' => 'meeting'
        ]);
    }

    /**
     * 공고에 스케줄 생성 테스트
     */
    public function test_agency_user_can_create_announcement_schedule()
    {
        $scheduleData = [
            'schedulable_type' => 'App\\Models\\Announcement',
            'schedulable_id' => $this->announcement->id,
            'title' => '발표 심사',
            'description' => '용역사 발표 및 심사',
            'start_datetime' => now()->addDays(3)->toISOString(),
            'end_datetime' => now()->addDays(3)->addHours(4)->toISOString(),
            'location' => '대회의실',
            'type' => 'technical_rehearsal'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $scheduleData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('schedules', [
            'schedulable_type' => 'App\\Models\\Announcement',
            'schedulable_id' => $this->announcement->id,
            'title' => '발표 심사',
            'type' => 'technical_rehearsal'
        ]);
    }

    /**
     * 스케줄 목록 조회 테스트
     */
    public function test_agency_user_can_view_their_schedules()
    {
        // 테스트용 스케줄 생성
        Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '테스트 스케줄'
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson('/api/schedules');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'schedules' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'start_datetime',
                                 'end_datetime',
                                 'status',
                                 'type'
                             ]
                         ]
                     ]
                 ]);
    }

    /**
     * 특정 프로젝트의 스케줄만 조회 테스트
     */
    public function test_can_filter_schedules_by_project()
    {
        // 다른 프로젝트 생성
        $otherProject = Project::factory()->create(['agency_id' => $this->agency->id]);

        // 각 프로젝트에 스케줄 생성
        Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '프로젝트 A 스케줄'
        ]);

        Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $otherProject->id,
            'title' => '프로젝트 B 스케줄'
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/schedules?schedulable_type=App\\Models\\Project&schedulable_id={$this->project->id}");

        $response->assertStatus(200);

        $schedules = $response->json('schedules.data');
        $this->assertCount(1, $schedules);
        $this->assertEquals('프로젝트 A 스케줄', $schedules[0]['title']);
    }

    /**
     * 스케줄 수정 테스트
     */
    public function test_agency_user_can_update_schedule()
    {
        $schedule = Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '원본 제목',
            'status' => 'planned'
        ]);

        $updateData = [
            'title' => '수정된 제목',
            'status' => 'ongoing',
            'location' => '새로운 장소'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->putJson("/api/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'schedule' => [
                         'title' => '수정된 제목',
                         'status' => 'ongoing',
                         'location' => '새로운 장소'
                     ]
                 ]);

        // 데이터베이스 확인
        $schedule->refresh();
        $this->assertEquals('수정된 제목', $schedule->title);
        $this->assertEquals('ongoing', $schedule->status);
    }

    /**
     * 스케줄 삭제 테스트
     */
    public function test_agency_user_can_delete_schedule()
    {
        $schedule = Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id
        ]);

        $response = $this->actingAs($this->agencyUser)
                         ->deleteJson("/api/schedules/{$schedule->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => '스케줄이 성공적으로 삭제되었습니다.'
                 ]);

        // 데이터베이스에서 삭제되었는지 확인
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    /**
     * 다른 대행사의 스케줄에 접근할 수 없음을 테스트
     */
    public function test_agency_user_cannot_access_other_agency_schedules()
    {
        // 다른 대행사 생성
        $otherAgency = Agency::factory()->create();
        $otherProject = Project::factory()->create(['agency_id' => $otherAgency->id]);
        $otherSchedule = Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $otherProject->id
        ]);

        // 다른 대행사의 스케줄 조회 시도
        $response = $this->actingAs($this->agencyUser)
                         ->getJson("/api/schedules/{$otherSchedule->id}");

        $response->assertStatus(403);

        // 다른 대행사의 스케줄 수정 시도
        $response = $this->actingAs($this->agencyUser)
                         ->putJson("/api/schedules/{$otherSchedule->id}", ['title' => '수정 시도']);

        $response->assertStatus(403);

        // 다른 대행사의 스케줄 삭제 시도
        $response = $this->actingAs($this->agencyUser)
                         ->deleteJson("/api/schedules/{$otherSchedule->id}");

        $response->assertStatus(403);
    }

    /**
     * 관리자는 모든 스케줄에 접근할 수 있음을 테스트
     */
    public function test_admin_can_access_all_schedules()
    {
        $schedule = Schedule::factory()->create([
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->getJson("/api/schedules/{$schedule->id}");

        $response->assertStatus(200);
    }

    /**
     * 잘못된 스케줄 타입으로 생성할 수 없음을 테스트
     */
    public function test_cannot_create_schedule_with_invalid_type()
    {
        $scheduleData = [
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '잘못된 타입 스케줄',
            'start_datetime' => now()->addDays(1)->toISOString(),
            'end_datetime' => now()->addDays(1)->addHours(2)->toISOString(),
            'type' => 'invalid_type' // 잘못된 타입
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $scheduleData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['type']);
    }

    /**
     * 시작 시간이 종료 시간보다 늦을 수 없음을 테스트
     */
    public function test_cannot_create_schedule_with_invalid_datetime()
    {
        $scheduleData = [
            'schedulable_type' => 'App\\Models\\Project',
            'schedulable_id' => $this->project->id,
            'title' => '잘못된 시간 스케줄',
            'start_datetime' => now()->addDays(1)->addHours(2)->toISOString(),
            'end_datetime' => now()->addDays(1)->toISOString(), // 시작보다 빠른 종료 시간
            'type' => 'meeting'
        ];

        $response = $this->actingAs($this->agencyUser)
                         ->postJson('/api/schedules', $scheduleData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['end_datetime']);
    }
} 