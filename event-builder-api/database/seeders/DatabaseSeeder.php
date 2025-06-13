<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Hash 퍼사드 추가

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 기존 사용자 데이터가 없으면 생성
        if (User::where('email', 'wo324wo@naver.com')->doesntExist()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'wo324wo@naver.com',
                'password' => Hash::make('Dj1303044^^'),
            ]);
        }

        // FeatureSeeder 실행
        $this->call([
            FeatureSeeder::class,
            EventBasicSeeder::class,
            RfpSeeder::class, // RfpSeeder 추가
        ]);

        // 기존 테스트 사용자 생성 로직은 주석 처리 또는 제거
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
