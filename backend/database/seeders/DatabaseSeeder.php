<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// 다음 use 문을 추가합니다.
use Database\Seeders\UserSeeder;
use Database\Seeders\ElementDefinitionSeeder;
use Database\Seeders\AgencyVendorUserSeeder; // 새로 추가

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class, // 핵심 admin@bidly.com 계정만 생성
            ElementDefinitionSeeder::class, // RFP 요소 정의
            AgencyVendorUserSeeder::class, // 나머지 모든 테스트 데이터 생성
        ]);
    }
}
