<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Agency;
use App\Models\Vendor;
use App\Models\AgencyMember;
use App\Models\VendorMember;
use Illuminate\Support\Facades\DB;

class AgencyVendorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. 관리자(Admin) 사용자 생성 (UserSeeder에서 생성되므로 firstOrCreate 사용)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@bidly.com'],
            [
                'name' => 'Bidly 관리자',
                'password' => Hash::make('bidlyadmin123!'),
                'phone_number' => '010-1111-2222',
                'user_type' => 'admin',
            ]
        );
        $this->command->info('Admin user created/found: ' . $adminUser->email);


        // 2. 대행사 마스터 사용자들 먼저 생성
        $agencyA_masterUser = User::firstOrCreate(
            ['email' => 'agency.a.master@bidly.com'],
            [
                'name' => '대행사A 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-3333-4444',
                'user_type' => 'agency_member',
            ]
        );
        
        $agencyB_masterUser = User::firstOrCreate(
            ['email' => 'agency.b.master@bidly.com'],
            [
                'name' => '대행사B 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-1212-3434',
                'user_type' => 'agency_member',
            ]
        );
        
        $agencyC_masterUser = User::firstOrCreate(
            ['email' => 'agency.c.master@bidly.com'],
            [
                'name' => '대행사C 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-9898-7676',
                'user_type' => 'agency_member',
            ]
        );

        // 이제 대행사들을 마스터 사용자와 함께 생성
        $agencyA = Agency::firstOrCreate(
            ['business_registration_number' => '123-45-67890'],
            [
                'name' => '대행사 A (기본)',
                'address' => '서울시 강남구 테헤란로 123',
                'master_user_id' => $agencyA_masterUser->id,
                'subscription_status' => 'active',
            ]
        );
        $agencyB = Agency::firstOrCreate(
            ['business_registration_number' => '987-65-43210'],
            [
                'name' => '대행사 B (파트너)',
                'address' => '부산시 해운대구 센텀로 456',
                'master_user_id' => $agencyB_masterUser->id,
                'subscription_status' => 'inactive', // inactive 예시
            ]
        );
        // 새로운 대행사 C (추가)
        $agencyC = Agency::firstOrCreate(
            ['business_registration_number' => '111-22-33444'],
            [
                'name' => '대행사 C (신규)',
                'address' => '대구시 달서구 테스트로 789',
                'master_user_id' => $agencyC_masterUser->id,
                'subscription_status' => 'active',
            ]
        );
        $this->command->info('Agencies created/found.');

        // 대행사 A의 멤버 관계 설정
        AgencyMember::firstOrCreate(
            ['user_id' => $agencyA_masterUser->id, 'agency_id' => $agencyA->id]
        );
        $this->command->info('Agency A master user and member created/found.');

        // 대행사 A의 일반 직원
        $agencyA_member1 = User::firstOrCreate(
            ['email' => 'agency.a.member1@bidly.com'],
            [
                'name' => '대행사A 직원1',
                'password' => Hash::make('password'),
                'phone_number' => '010-5555-6666',
                'user_type' => 'agency_member',
            ]
        );
        AgencyMember::firstOrCreate(
            ['user_id' => $agencyA_member1->id, 'agency_id' => $agencyA->id]
        );
        $this->command->info('Agency A member 1 created/found.');

        // 대행사 B의 멤버 관계 설정
        AgencyMember::firstOrCreate(
            ['user_id' => $agencyB_masterUser->id, 'agency_id' => $agencyB->id]
        );
        $this->command->info('Agency B master user and member created/found.');

        // 대행사 C의 멤버 관계 설정
        AgencyMember::firstOrCreate(
            ['user_id' => $agencyC_masterUser->id, 'agency_id' => $agencyC->id]
        );
        $this->command->info('Agency C master user and member created/found.');


        // 3. 용역사 마스터 사용자들 먼저 생성
        $vendorX_masterUser = User::firstOrCreate(
            ['email' => 'vendor.x.master@bidly.com'],
            [
                'name' => '용역사X 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-7777-8888',
                'user_type' => 'vendor_member',
            ]
        );
        
        $vendorY_masterUser = User::firstOrCreate(
            ['email' => 'vendor.y.master@bidly.com'],
            [
                'name' => '용역사Y 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-8888-9999',
                'user_type' => 'vendor_member',
            ]
        );
        
        $vendorZ_masterUser = User::firstOrCreate(
            ['email' => 'vendor.z.master@bidly.com'],
            [
                'name' => '용역사Z 마스터',
                'password' => Hash::make('password'),
                'phone_number' => '010-1010-2020',
                'user_type' => 'vendor_member',
            ]
        );

        // 용역사들을 마스터 사용자와 함께 생성
        $vendorX = Vendor::firstOrCreate(
            ['business_registration_number' => '555-66-77888'],
            [
                'name' => '용역사 X (무대/음향)',
                'address' => '서울시 강서구 양천로 789',
                'description' => '최고의 무대 및 음향 장비를 보유한 전문 용역사입니다.',
                'specialties' => ['stage', 'sound'],
                'master_user_id' => $vendorX_masterUser->id,
                'status' => 'active',
            ]
        );
        $vendorY = Vendor::firstOrCreate(
            ['business_registration_number' => '444-55-66777'],
            [
                'name' => '용역사 Y (조명/영상)',
                'address' => '경기도 성남시 분당구 판교로 111',
                'description' => '다양한 조명 및 영상 연출 서비스를 제공합니다.',
                'specialties' => ['lighting', 'video'],
                'master_user_id' => $vendorY_masterUser->id,
                'status' => 'active',
            ]
        );
        // 새로운 용역사 Z (비활성 계정)
        $vendorZ = Vendor::firstOrCreate(
            ['business_registration_number' => '777-88-99000'],
            [
                'name' => '용역사 Z (운송/전기)',
                'address' => '인천시 남동구 테스트로 333',
                'description' => '운송 및 전기 설비 전문 용역사입니다.',
                'specialties' => ['transport', 'electric'],
                'master_user_id' => $vendorZ_masterUser->id,
                'status' => 'inactive',
            ]
        );
        $this->command->info('Vendors created/found.');

        // 용역사 X의 멤버 관계 설정
        VendorMember::firstOrCreate(
            ['user_id' => $vendorX_masterUser->id, 'vendor_id' => $vendorX->id]
        );
        $this->command->info('Vendor X master user and member created/found.');

        // 용역사 Y의 일반 직원
        $vendorY_member1 = User::firstOrCreate(
            ['email' => 'vendor.y.member1@bidly.com'],
            [
                'name' => '용역사Y 직원1',
                'password' => Hash::make('password'),
                'phone_number' => '010-9999-0000',
                'user_type' => 'vendor_member',
            ]
        );
        VendorMember::firstOrCreate(
            ['user_id' => $vendorY_member1->id, 'vendor_id' => $vendorY->id]
        );
        VendorMember::firstOrCreate(
            ['user_id' => $vendorY_masterUser->id, 'vendor_id' => $vendorY->id]
        );
        $this->command->info('Vendor Y members created/found.');

        // 용역사 Z의 멤버 관계 설정
        VendorMember::firstOrCreate(
            ['user_id' => $vendorZ_masterUser->id, 'vendor_id' => $vendorZ->id]
        );
        $this->command->info('Vendor Z master user and member created/found.');


        // 4. 대행사-용역사 승인 관계 설정 (agency_approved_vendors)
        // 대행사 A가 용역사 X를 승인
        DB::table('agency_approved_vendors')->insertOrIgnore([ // firstOrCreate 대신 insertOrIgnore 사용
            'agency_id' => $agencyA->id,
            'vendor_id' => $vendorX->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Agency A approved Vendor X.');
        
        // 대행사 A가 용역사 Y도 승인
        DB::table('agency_approved_vendors')->insertOrIgnore([
            'agency_id' => $agencyA->id,
            'vendor_id' => $vendorY->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Agency A approved Vendor Y.');

        // 대행사 B가 용역사 Y를 승인
        DB::table('agency_approved_vendors')->insertOrIgnore([
            'agency_id' => $agencyB->id,
            'vendor_id' => $vendorY->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Agency B approved Vendor Y.');

        // 5. 추가 관리자 계정 생성
        $anotherAdminUser = User::firstOrCreate(
            ['email' => 'admin2@bidly.com'],
            [
                'name' => '보조 관리자',
                'password' => Hash::make('bidlyadmin123!'),
                'phone_number' => '010-1234-5678',
                'user_type' => 'admin',
            ]
        );
        $this->command->info('Another admin user created/found: ' . $anotherAdminUser->email);
    }
}
