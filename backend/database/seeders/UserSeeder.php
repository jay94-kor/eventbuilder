<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 핵심 관리자(admin) 계정 생성
        // 이 계정은 반드시 존재해야 합니다.
        User::firstOrCreate(
            ['email' => 'admin@bidly.com'],
            [
                'name' => 'Bidly 관리자',
                'password' => Hash::make('bidlyadmin123!'), // 강력한 비밀번호 사용 권장
                'phone_number' => '010-1111-2222',
                'user_type' => 'admin',
            ]
        );
        $this->command->info('Core admin user created/found: admin@bidly.com');
    }
}
