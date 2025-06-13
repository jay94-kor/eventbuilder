<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rfp;
use App\Models\User;
use App\Models\Feature;
use App\Models\RfpSelection;
use Carbon\Carbon;

class RfpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 기존 RFP 데이터가 없으면 생성
        if (Rfp::count() > 0) {
            $this->command->info('RFP 시드 데이터가 이미 존재합니다. 건너뜁니다.');
            return;
        }

        $user = User::first(); // 첫 번째 사용자 가져오기 (DatabaseSeeder에서 생성된 Admin User)

        if (!$user) {
            $this->command->error('사용자가 존재하지 않아 RFP 시드 데이터를 생성할 수 없습니다.');
            return;
        }

        // 더미 RFP 데이터 생성
        $rfpsData = [
            [
                'title' => '2024년 연례 파트너십 컨퍼런스',
                'status' => 'completed',
                'event_date' => '2024-05-10',
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3),
            ],
            [
                'title' => '신규 서비스 런칭 기념 세미나',
                'status' => 'draft',
                'event_date' => '2024-07-20',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now()->subMonth(),
            ],
            [
                'title' => '가을 고객 감사 페스티벌',
                'status' => 'archived',
                'event_date' => '2023-10-05',
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()->subMonths(8),
            ],
            [
                'title' => '겨울 자선 갈라 디너',
                'status' => 'completed',
                'event_date' => '2023-12-15',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(6),
            ],
            [
                'title' => '2025년 신년회 및 비전 선포식',
                'status' => 'draft',
                'event_date' => '2025-01-05',
                'created_at' => Carbon::now()->subWeeks(2),
                'updated_at' => Carbon::now()->subWeeks(2),
            ],
        ];

        foreach ($rfpsData as $data) {
            $rfp = Rfp::create(array_merge($data, ['user_id' => $user->id]));

            // 각 RFP에 임의의 Feature Selection 추가
            $features = Feature::inRandomOrder()->limit(rand(2, 5))->get();
            foreach ($features as $feature) {
                RfpSelection::create([
                    'rfp_id' => $rfp->id,
                    'feature_id' => $feature->id,
                    'details' => json_encode([
                        'attendee_limit' => rand(50, 500),
                        'has_catering' => (bool)rand(0, 1),
                        'notes' => '자동 생성된 상세 정보입니다.',
                    ]),
                ]);
            }
        }

        $this->command->info('RFP 시드 데이터가 생성되었습니다.');
    }
}