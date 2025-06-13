<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeatureCategory;
use App\Models\Feature;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 중복 실행 방지: 기존 데이터가 있으면 건너뛰기
        if (FeatureCategory::count() > 0 || Feature::count() > 0) {
            $this->command->info('시드 데이터가 이미 존재합니다. 건너뜁니다.');
            return;
        }

        // Feature Categories 생성
        $categoriesData = [
            ['name' => '장소', 'description' => '행사장 대관, 홀·객실 크기, 교통·주차·숙박 지원', 'sort_order' => 1, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '기술 장비', 'description' => '음향·조명·영상·무대·인터랙티브 장비', 'sort_order' => 2, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '케이터링', 'description' => '식음료 형태, 메뉴, 인원수·알레르기·식사 제한', 'sort_order' => 3, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '인력·진행 지원', 'description' => 'MC·사회자, 운영 스태프, 보안·안내, 통·번역 인력', 'sort_order' => 4, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '디자인·브랜딩', 'description' => '현수막·배너·굿즈·기념품 디자인·제작', 'sort_order' => 5, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '홍보·마케팅', 'description' => '온라인 광고, 오프라인 홍보물, SNS 운영, 현장 홍보 지원', 'sort_order' => 6, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '영상 시스템', 'description' => '행사 중계 및 사전 영상 제작 관련 시스템', 'sort_order' => 7, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
            ['name' => '특별 요청사항', 'description' => '셔틀버스, 통신·인터넷, 방역·의료, 안전·보험 등 맞춤 서비스', 'sort_order' => 8, 'is_active' => true, 'budget_allocation' => true, 'internal_resource_flag' => true],
        ];

        $createdCategories = [];
        foreach ($categoriesData as $categoryData) {
            // 한글 슬러그 생성을 위해 직접 처리
            $slug = Str::slug($categoryData['name']);
            if (empty($slug)) {
                // Str::slug가 빈 문자열을 반환하는 경우를 대비하여 대체 슬러그 생성
                $slug = Str::snake($categoryData['name']); // 예: "기술 장비" -> "기술_장비"
            }
            $categoryData['slug'] = $slug;
            $category = FeatureCategory::create($categoryData);
            $createdCategories[$category->name] = $category->id; // 이름으로 ID 저장
        }

        // Features 데이터 정의 (config.fields 포함)
        $featuresData = [
            [
                'name' => '컨퍼런스 홀 대관',
                'category' => '장소',
                'icon' => '🏢',
                'description' => '수용 인원, 면적, 위치, 대관 가능 시간대를 입력',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '행사장 이름', 'key' => 'venue_name', 'unit' => null, 'type' => 'text', 'placeholder' => '예) 그랜드 컨벤션 센터', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '수용 인원', 'key' => 'venue_capacity', 'unit' => '명', 'type' => 'number', 'placeholder' => '최대 수용 인원', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '대관 시작일', 'key' => 'venue_start_date', 'unit' => null, 'type' => 'date', 'placeholder' => 'YYYY-MM-DD', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '대관 종료일', 'key' => 'venue_end_date', 'unit' => null, 'type' => 'date', 'placeholder' => 'YYYY-MM-DD ≥ 시작일', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => '빔 프로젝터', 'level' => 'R1'],
                    ['name' => '음향 시스템', 'level' => 'R1'],
                    ['name' => '무대·백드롭', 'level' => 'R1'],
                    ['name' => '스크린·프로젝터 스탠드', 'level' => 'R2'],
                    ['name' => '전기·배전반', 'level' => 'R2'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '야외 행사장 대관',
                'category' => '장소',
                'icon' => '🌳',
                'description' => '전기·음향·조명 인프라, 바닥 포장 여부, 야외 사용 허가 여부',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => ['fields' => []],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '빔 프로젝터',
                'category' => '기술 장비',
                'icon' => '📽️',
                'description' => '해상도, 밝기(lm), 렌즈 줌, 렌탈 기간',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '밝기 (lumens)', 'key' => 'projector_lumens', 'unit' => 'lm', 'type' => 'number', 'placeholder' => '최소 3000lm 권장', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '해상도', 'key' => 'projector_resolution', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '옵션 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => 'HD', 'value' => 'HD'], ['label' => 'Full HD', 'value' => 'FHD'], ['label' => '4K', 'value' => '4K']], 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => '음향 시스템', 'level' => 'R1'],
                    ['name' => '스크린', 'level' => 'R1'],
                    ['name' => '프로젝터 스탠드', 'level' => 'R2'],
                    ['name' => '추가 렌즈 옵션', 'level' => 'R2'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '음향 시스템',
                'category' => '기술 장비',
                'icon' => '🔊',
                'description' => '마이크 유형·스피커 출력(W)·믹서 채널·현장 엔지니어 지원 여부',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '무대·백드롭',
                'category' => '기술 장비',
                'icon' => '🎪',
                'description' => '크기, 형태(모듈·트러스), 배경 디자인 옵션, 설치·철거 시간',
                'sort_order' => 3,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '케이터링 형태 선택',
                'category' => '케이터링',
                'icon' => '🍽️',
                'description' => '뷔페·코스·다과·간식·음료 서비스 중 선택',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '서비스 형태', 'key' => 'catering_type', 'unit' => null, 'type' => 'radio', 'placeholder' => null, 'required' => true, 'field_level' => 'parent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '뷔페', 'value' => 'buffet'], ['label' => '코스', 'value' => 'course'], ['label' => '다과', 'value' => 'snack'], ['label' => '간식', 'value' => 'light']], 'allow_undecided' => true],
                        ['name' => '1인당 단가', 'key' => 'catering_unit_price', 'unit' => '원', 'type' => 'number', 'placeholder' => '단위: 원', 'required' => true, 'field_level' => 'child', 'parent_field' => 'catering_type', 'show_when_value' => 'any', 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => '케이터링 스태프', 'level' => 'R1'],
                    ['name' => '서빙 요원', 'level' => 'R1'],
                    ['name' => '테이블 세팅·철거 인력', 'level' => 'R2'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '메뉴 커스터마이징',
                'category' => '케이터링',
                'icon' => '🍎',
                'description' => '채식·할랄·코셔·알레르기 대응 메뉴 등 특수식 요청',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '특수 식단 필요 여부', 'key' => 'special_menu_required', 'unit' => null, 'type' => 'toggle', 'placeholder' => 'ON 시 하위 메뉴 옵션 노출', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '특수식단 옵션 선택', 'key' => 'special_menu_options', 'unit' => null, 'type' => 'checkbox', 'placeholder' => null, 'required' => true, 'field_level' => 'child', 'parent_field' => 'special_menu_required', 'show_when_value' => 'true', 'options' => [['label' => '채식', 'value' => 'vegetarian'], ['label' => '할랄', 'value' => 'halal'], ['label' => '코셔', 'value' => 'kosher']], 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '사회자(MC) 지원',
                'category' => '인력·진행 지원',
                'icon' => '🎤',
                'description' => 'MC 필요 여부·대본 작성·리허설 포함 여부',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => true,
                'config' => [
                    'fields' => [
                        ['name' => '전문 MC 필요 여부', 'key' => 'mc_required', 'unit' => null, 'type' => 'toggle', 'placeholder' => null, 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '스크립트 제공 여부', 'key' => 'mc_script_provided', 'unit' => null, 'type' => 'toggle', 'placeholder' => null, 'required' => true, 'field_level' => 'child', 'parent_field' => 'mc_required', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => '운영 스태프', 'level' => 'R1'],
                    ['name' => '음향 시스템', 'level' => 'R1'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '운영 스태프',
                'category' => '인력·진행 지원',
                'icon' => '👥',
                'description' => '안내·접수·행사 진행 지원 인원수, 근무 시간, 복장·장비',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '현수막·배너 제작',
                'category' => '디자인·브랜딩',
                'icon' => '🎨',
                'description' => '사이즈·수량·재질·출력·설치 포함 여부',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '기념품·굿즈 제작',
                'category' => '디자인·브랜딩',
                'icon' => '🎁',
                'description' => '품목 종류, 로고·패키지 디자인, 최소 제작 수량, 납기 일정',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '온라인 광고 캠페인',
                'category' => '홍보·마케팅',
                'icon' => '📢',
                'description' => '플랫폼·예산·기간·타깃 설정',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => 'SNS 운영', 'level' => 'R1'],
                    ['name' => '현장 배너 제작', 'level' => 'R1'],
                    ['name' => '기념품·굿즈 제작', 'level' => 'R2'],
                    ['name' => '포토월 디자인', 'level' => 'R2'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '셔틀버스 지원',
                'category' => '특별 요청사항',
                'icon' => '🚌',
                'description' => '운행 경로·횟수·차량 종류·승객 수용 인원',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '방역·의료 지원',
                'category' => '특별 요청사항',
                'icon' => '🛡️',
                'description' => '방역 인력 투입, 의료진·구급차 대기, 방역 물품 제공',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '중계 시스템',
                'category' => '영상 시스템',
                'icon' => '📹',
                'description' => '실시간 행사 중계 및 송출 관련 설정을 입력합니다.',
                'sort_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '카메라 대수', 'key' => 'live_camera_count', 'unit' => '대', 'type' => 'number', 'placeholder' => '필요한 카메라 대수를 입력하세요.', 'required' => false, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '카메라 대수 확정 여부', 'key' => 'live_camera_count_confirmed', 'unit' => null, 'type' => 'radio', 'placeholder' => '카메라 대수가 확정되었는지 선택해주세요.', 'required' => true, 'field_level' => 'parent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '확정', 'value' => 'confirmed'], ['label' => '미확정 (추가 정보 필요)', 'value' => 'unconfirmed']], 'allow_undecided' => false],
                        ['name' => '행사 종류 (카메라 미확정 시)', 'key' => 'live_event_type_for_camera', 'unit' => null, 'type' => 'text', 'placeholder' => '예) 컨퍼런스, 워크샵, 공연 등', 'required' => true, 'field_level' => 'child', 'parent_field' => 'live_camera_count_confirmed', 'show_when_value' => 'unconfirmed', 'allow_undecided' => true],
                        ['name' => 'MC 유무 (카메라 미확정 시)', 'key' => 'live_mc_presence_for_camera', 'unit' => null, 'type' => 'toggle', 'placeholder' => 'MC가 있는지 여부', 'required' => true, 'field_level' => 'child', 'parent_field' => 'live_camera_count_confirmed', 'show_when_value' => 'unconfirmed', 'allow_undecided' => true],
                        ['name' => '공연 유무 (카메라 미확정 시)', 'key' => 'live_performance_presence_for_camera', 'unit' => null, 'type' => 'toggle', 'placeholder' => '공연이 있는지 여부', 'required' => true, 'field_level' => 'child', 'parent_field' => 'live_camera_count_confirmed', 'show_when_value' => 'unconfirmed', 'allow_undecided' => true],
                        ['name' => '인터넷 생방송 필요 여부', 'key' => 'live_streaming_needed', 'unit' => null, 'type' => 'toggle', 'placeholder' => '인터넷으로 실시간 송출이 필요한가요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '송출 플랫폼 (생방송 시)', 'key' => 'live_streaming_platforms', 'unit' => null, 'type' => 'checkbox', 'placeholder' => '송출할 플랫폼을 모두 선택하세요.', 'required' => true, 'field_level' => 'child', 'parent_field' => 'live_streaming_needed', 'show_when_value' => 'true', 'options' => [['label' => 'YouTube', 'value' => 'youtube'], ['label' => 'Zoom', 'value' => 'zoom'], ['label' => '자체 플랫폼', 'value' => 'custom'], ['label' => '기타', 'value' => 'other']], 'allow_undecided' => true],
                        ['name' => '발표자료/PC 화면 동시 송출 필요 여부', 'key' => 'live_presentation_sharing_needed', 'unit' => null, 'type' => 'toggle', 'placeholder' => '발표 자료나 컴퓨터 화면을 라이브에 같이 송출해야 하나요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '이름 자막 등 사전 그래픽 작업 필요 여부', 'key' => 'live_graphics_preparation_needed', 'unit' => null, 'type' => 'toggle', 'placeholder' => '강연자 이름 자막, 세션 제목 등 그래픽 요소 준비가 필요한가요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '중계 영상 내 자료화면 삽입 필요 여부', 'key' => 'live_video_insert_needed', 'unit' => null, 'type' => 'toggle', 'placeholder' => '중계 영상 중간에 별도로 제작된 자료화면(VCR) 등을 삽입해야 하나요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false, 'hint' => '자료화면이 필요하다면 "사전 영상 제작" 기능도 함께 고려해주세요.'],
                        ['name' => '자료화면 상세 내용 (필요 시)', 'key' => 'live_video_insert_details', 'unit' => null, 'type' => 'textarea', 'placeholder' => '삽입될 자료화면의 내용, 길이, 개수 등을 간략히 설명해주세요.', 'required' => false, 'field_level' => 'child', 'parent_field' => 'live_video_insert_needed', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '세팅 시작 가능 시간', 'key' => 'live_setup_start_time', 'unit' => null, 'type' => 'datetime', 'placeholder' => '중계 장비 세팅을 언제부터 시작할 수 있나요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [
                    ['name' => '사전 영상 제작', 'level' => 'R2'],
                    ['name' => '인터넷 회선 점검', 'level' => 'R1'],
                ],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
            [
                'name' => '사전 영상 제작',
                'category' => '영상 시스템',
                'icon' => '🎬',
                'description' => '행사 전 필요한 영상 콘텐츠 제작 관련 설정을 입력합니다.',
                'sort_order' => 2,
                'is_active' => true,
                'is_premium' => false,
                'config' => [
                    'fields' => [
                        ['name' => '영상 제작 유형', 'key' => 'pre_video_type', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '제작할 영상의 유형을 선택하세요.', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '인터뷰 영상', 'value' => 'interview'], ['label' => '홍보 영상', 'value' => 'promotional'], ['label' => '행사 스케치 영상', 'value' => 'sketch'], ['label' => '사진 편집 영상', 'value' => 'photo_montage'], ['label' => '애니메이션/모션그래픽', 'value' => 'motion_graphic'], ['label' => '기타', 'value' => 'other']], 'allow_undecided' => true],
                        ['name' => '기타 영상 제작 유형 (선택 시)', 'key' => 'pre_video_type_other_details', 'unit' => null, 'type' => 'text', 'placeholder' => '영상 유형을 직접 입력해주세요.', 'required' => true, 'field_level' => 'child', 'parent_field' => 'pre_video_type', 'show_when_value' => 'other', 'allow_undecided' => true],
                        ['name' => '총 예상 영상 길이', 'key' => 'pre_video_total_length', 'unit' => null, 'type' => 'text', 'placeholder' => '예) 3분 ~ 5분 사이, 또는 총 15분 (3편)', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '촬영 필요 여부', 'key' => 'pre_video_shooting_needed', 'unit' => null, 'type' => 'toggle', 'placeholder' => '별도의 촬영이 필요한 영상인가요?', 'required' => true, 'field_level' => 'parent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '촬영 장소 유형 (촬영 필요 시)', 'key' => 'pre_video_shooting_location_type', 'unit' => null, 'type' => 'radio', 'placeholder' => '촬영 장소 유형을 선택하세요.', 'required' => true, 'field_level' => 'child', 'parent_field' => 'pre_video_shooting_needed', 'show_when_value' => 'true', 'options' => [['label' => '단일 장소 (출연자 방문)', 'value' => 'single_location_actors_visit'], ['label' => '단일 장소 (제작팀 방문)', 'value' => 'single_location_crew_visit'], ['label' => '복수 장소 (제작팀 방문)', 'value' => 'multiple_locations_crew_visit'], ['label' => '스튜디오 촬영', 'value' => 'studio']], 'allow_undecided' => true],
                        ['name' => '촬영 장소 상세 (해당 시)', 'key' => 'pre_video_shooting_location_details', 'unit' => null, 'type' => 'textarea', 'placeholder' => '촬영 장소 주소, 특징 등을 입력해주세요. 복수 장소일 경우 목록으로 작성 가능합니다.', 'required' => false, 'field_level' => 'child', 'parent_field' => 'pre_video_shooting_location_type', 'show_when_value' => ['single_location_actors_visit', 'single_location_crew_visit', 'multiple_locations_crew_visit', 'studio'], 'allow_undecided' => true],
                        ['name' => '총 촬영 예상 일수 (촬영 필요 시)', 'key' => 'pre_video_shooting_days', 'unit' => '일', 'type' => 'number', 'placeholder' => '총 며칠의 촬영이 필요할 것으로 예상되나요?', 'required' => true, 'field_level' => 'child', 'parent_field' => 'pre_video_shooting_needed', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '희망 납품일', 'key' => 'pre_video_delivery_deadline', 'unit' => null, 'type' => 'date', 'placeholder' => '영상을 언제까지 받고 싶으신가요?', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '참고 레퍼런스 영상 (URL)', 'key' => 'pre_video_reference_url', 'unit' => null, 'type' => 'text', 'placeholder' => '참고할 만한 영상의 링크를 입력해주세요.', 'required' => false, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '기타 요청사항', 'key' => 'pre_video_other_requests', 'unit' => null, 'type' => 'textarea', 'placeholder' => '나레이션, 자막 스타일, BGM, 효과 등 구체적인 요청사항을 입력해주세요.', 'required' => false, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '준비 마감 시점', 'key' => 'feature_prepare_deadline', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 준비 완료 마감일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '납품 완료 시점', 'key' => 'feature_delivery_date', 'unit' => null, 'type' => 'datetime', 'placeholder' => '기능별 납품·설치 완료일 지정', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => true],
                        ['name' => '내부 리소스 사용 여부', 'key' => 'internal_resource', 'unit' => null, 'type' => 'toggle', 'placeholder' => '내부 리소스 사용 시 예산 입력 비활성화', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'allow_undecided' => false],
                        ['name' => '내부 담당자 이름', 'key' => 'internal_resource_person', 'unit' => null, 'type' => 'text', 'placeholder' => '내부 리소스 사용 시 담당자 입력', 'required' => true, 'field_level' => 'child', 'parent_field' => 'internal_resource', 'show_when_value' => 'true', 'allow_undecided' => true],
                        ['name' => '적용 범위', 'key' => 'feature_scope', 'unit' => null, 'type' => 'dropdown', 'placeholder' => '‘전체 일괄’ 또는 ‘존별 설정’ 선택', 'required' => true, 'field_level' => 'independent', 'parent_field' => null, 'show_when_value' => null, 'options' => [['label' => '전체 일괄', 'value' => 'all'], ['label' => '존별 설정', 'value' => 'by_zone']], 'allow_undecided' => false],
                        ['name' => '대상 존 선택', 'key' => 'feature_zones', 'unit' => null, 'type' => 'multiselect', 'placeholder' => 'by_zone 선택 시 활성화, 적용할 존 선택', 'required' => true, 'field_level' => 'child', 'parent_field' => 'feature_scope', 'show_when_value' => 'by_zone', 'options' => [], 'allow_undecided' => true],
                    ]
                ],
                'recommendations' => [],
                'budget_allocation' => true,
                'internal_resource_flag' => true,
            ],
        ];

        $createdFeatures = [];
        foreach ($featuresData as $featureData) {
            $categoryName = $featureData['category'];
            $featureData['category_id'] = $createdCategories[$categoryName];
            unset($featureData['category']);

            $recommendations = $featureData['recommendations'];
            unset($featureData['recommendations']);

            // config 필드에 allow_undecided 기본값 추가
            if (isset($featureData['config']['fields'])) {
                foreach ($featureData['config']['fields'] as &$field) {
                    if (!isset($field['allow_undecided'])) {
                        $field['allow_undecided'] = false; // 기본값 설정
                    }
                }
            }

            $feature = Feature::create($featureData);
            $createdFeatures[$feature->name] = $feature;
        }

        // 추천 기능 연결 (R1/R2 레벨 포함)
        foreach ($featuresData as $featureData) {
            $featureName = $featureData['name'];
            $feature = $createdFeatures[$featureName];
            $recommendations = $featureData['recommendations']; // recommendations 다시 가져옴

            $syncData = [];
            foreach ($recommendations as $rec) {
                if (isset($createdFeatures[$rec['name']])) {
                    $syncData[$createdFeatures[$rec['name']]->id] = ['level' => $rec['level']];
                }
            }
            if (!empty($syncData)) {
                $feature->recommendations()->sync($syncData);
            }
        }

        $this->command->info('Feature Categories: ' . count($categoriesData) . '개 생성');
        $this->command->info('Features: ' . count($featuresData) . '개 생성');
        $this->command->info('시드 데이터 생성이 완료되었습니다!');
    }
}
