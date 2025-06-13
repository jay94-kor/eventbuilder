<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventBasic;
use Carbon\Carbon;

class EventBasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 중복 실행 방지
        if (EventBasic::count() > 0) {
            $this->command->info('EventBasic 시드 데이터가 이미 존재합니다. 건너뜁니다.');
            return;
        }

        EventBasic::create([
            'client_name' => 'Bidly Corp.',
            'event_title' => '2025 글로벌 테크 컨퍼런스',
            'event_location' => '서울, 대한민국, 코엑스',
            'venue_type' => '혼합', // 실내, 실외, 혼합 중 선택
            'zones' => json_encode([
                ['name' => '존 A', 'type' => '실내', 'quantity' => 1],
                ['name' => '존 B', 'type' => '실외', 'quantity' => 1],
            ]),
            'total_budget' => 50000000, // 5천만원
            'event_start_date_range_min' => '2025-10-20',
            'event_start_date_range_max' => '2025-10-25',
            'event_end_date_range_min' => '2025-10-22',
            'event_end_date_range_max' => '2025-10-27',
            'event_duration_days' => 5, // 날짜가 확정되면 자동 계산; 직접 입력 시 필수
            'setup_start_date' => '2025-10-18', // 행사 시작일 최소값 기준 자동 산출 또는 수동 선택
            'teardown_end_date' => '2025-10-29', // 행사 종료일 최대값 기준 자동 산출 또는 수동 선택
            'project_kickoff_date' => '2025-07-01', // 내부 준비 착수일
            'settlement_close_date' => '2025-11-30', // 비용 정산 완료 예정일
            'contact_person_name' => '김철수',
            'contact_person_contact' => '010-1234-5678',
            'admin_person_name' => '이영희',
            'admin_person_contact' => '010-9876-5432',
        ]);

        $this->command->info('EventBasic 시드 데이터가 생성되었습니다.');
    }
}