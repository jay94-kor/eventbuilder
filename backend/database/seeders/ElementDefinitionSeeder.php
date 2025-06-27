<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ElementDefinition;

class ElementDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elements = [
            [
                'element_type' => 'stage',
                'display_name' => '무대',
                'description' => '메인, 서브 무대 및 백월 등 무대 관련 모든 요소입니다.',
                'input_schema' => json_encode([ // JSON Schema (추후 구체화)
                    'size_m' => ['type' => 'object', 'properties' => ['width' => ['type' => 'number'], 'height' => ['type' => 'number'], 'depth' => ['type' => 'number']]],
                    'structure_type' => ['type' => 'string', 'enum' => ['트러스 구조', '팝업 구조', '일반 조립식']],
                    'features' => ['type' => 'array', 'items' => ['type' => 'string']],
                ]),
                'default_details_template' => json_encode([ // 기본값 템플릿
                    'size_m' => ['width' => null, 'height' => null, 'depth' => null],
                    'structure_type' => null,
                    'features' => [],
                ]),
                'recommended_elements' => json_encode(['sound', 'lighting', 'video', 'LED_screen']), // 추천 요소
            ],
            [
                'element_type' => 'sound',
                'display_name' => '음향',
                'description' => '행사 전반의 음향 시스템 및 장비 일체입니다.',
                'input_schema' => json_encode([
                    'system_type' => ['type' => 'string'],
                    'power_output_watt' => ['type' => 'number'],
                    'microphone_count' => ['type' => 'object'],
                ]),
                'default_details_template' => json_encode([
                    'system_type' => null,
                    'power_output_watt' => null,
                    'microphone_count' => ['wireless' => null, 'wired' => null],
                ]),
                'recommended_elements' => json_encode(['stage', 'lighting', 'electric']),
            ],
            [
                'element_type' => 'lighting',
                'display_name' => '조명',
                'description' => '행사 분위기 연출을 위한 조명 시스템 및 특수 조명입니다.',
                'input_schema' => json_encode([
                    'lighting_concept' => ['type' => 'string'],
                    'fixture_count' => ['type' => 'object'],
                    'power_consumption_kwh' => ['type' => 'number'],
                ]),
                'default_details_template' => json_encode([
                    'lighting_concept' => null,
                    'fixture_count' => ['moving_head' => null, 'par_can' => null],
                    'power_consumption_kwh' => null,
                ]),
                'recommended_elements' => json_encode(['stage', 'sound', 'electric']),
            ],
            [
                'element_type' => 'casting',
                'display_name' => '섭외',
                'description' => '행사에 필요한 아티스트, 연사, 사회자 등 인력 섭외입니다.',
                'input_schema' => json_encode([
                    'casting_items' => ['type' => 'array', 'items' => ['type' => 'object']],
                ]),
                'default_details_template' => json_encode([
                    'casting_items' => [],
                ]),
                'recommended_elements' => json_encode(['sound', 'security']),
            ],
            [
                'element_type' => 'security',
                'display_name' => '경호/의전/안전',
                'description' => '행사장 경호, 의전, 안전 관리 인력 및 시스템입니다.',
                'input_schema' => json_encode([
                    'personnel_count' => ['type' => 'object'],
                    'scope' => ['type' => 'string'],
                ]),
                'default_details_template' => json_encode([
                    'personnel_count' => ['security_guards' => null],
                    'scope' => null,
                ]),
                'recommended_elements' => json_encode(['casting', 'equipment_rental']),
            ],
            [
                'element_type' => 'video',
                'display_name' => '영상',
                'description' => '행사 중계, 기록, 송출을 위한 영상 장비 및 기술입니다.',
                'input_schema' => json_encode([
                    'purpose' => ['type' => 'string'],
                    'camera_count' => ['type' => 'object'],
                    'output_format' => ['type' => 'string'],
                ]),
                'default_details_template' => json_encode([
                    'purpose' => null,
                    'camera_count' => ['main' => null],
                    'output_format' => null,
                ]),
                'recommended_elements' => json_encode(['stage', 'LED_screen', 'sound']),
            ],
            [
                'element_type' => 'photo',
                'display_name' => '사진',
                'description' => '행사 현장 기록 및 홍보를 위한 사진 촬영입니다.',
                'input_schema' => json_encode([
                    'purpose' => ['type' => 'string'],
                    'photographer_count' => ['type' => 'number'],
                ]),
                'default_details_template' => json_encode([
                    'purpose' => null,
                    'photographer_count' => null,
                ]),
                'recommended_elements' => json_encode([]),
            ],
            [
                'element_type' => 'electric',
                'display_name' => '전기',
                'description' => '행사에 필요한 전력 공급 및 배전 시스템입니다.',
                'input_schema' => json_encode([
                    'total_power_needed_kw' => ['type' => 'number'],
                    'power_sources' => ['type' => 'array'],
                ]),
                'default_details_template' => json_encode([
                    'total_power_needed_kw' => null,
                    'power_sources' => [],
                ]),
                'recommended_elements' => json_encode(['sound', 'lighting', 'LED_screen', 'equipment_rental']),
            ],
            [
                'element_type' => 'transport',
                'display_name' => '운송',
                'description' => '장비 및 물품 운송, 인력 이동을 위한 운송 서비스입니다.',
                'input_schema' => json_encode([
                    'transport_type' => ['type' => 'string'],
                    'item_description' => ['type' => 'string'],
                    'volume_cbm' => ['type' => 'number'],
                ]),
                'default_details_template' => json_encode([
                    'transport_type' => null,
                    'item_description' => null,
                    'volume_cbm' => null,
                ]),
                'recommended_elements' => json_encode(['equipment_rental']),
            ],
            [
                'element_type' => 'printing',
                'display_name' => '인쇄',
                'description' => '포스터, 리플렛, 배너 등 각종 인쇄물 제작입니다.',
                'input_schema' => json_encode([
                    'item_type' => ['type' => 'string'],
                    'quantity' => ['type' => 'number'],
                ]),
                'default_details_template' => json_encode([
                    'item_type' => null,
                    'quantity' => null,
                ]),
                'recommended_elements' => json_encode([]),
            ],
            [
                'element_type' => 'LED_screen',
                'display_name' => 'LED 전광판',
                'description' => '행사용 대형 LED 스크린 설치 및 운영입니다.',
                'input_schema' => json_encode([
                    'screen_type' => ['type' => 'string'],
                    'size_m' => ['type' => 'object'],
                    'pixel_pitch_mm' => ['type' => 'number'],
                ]),
                'default_details_template' => json_encode([
                    'screen_type' => null,
                    'size_m' => ['width' => null, 'height' => null],
                    'pixel_pitch_mm' => null,
                ]),
                'recommended_elements' => json_encode(['stage', 'video', 'electric']),
            ],
            [
                'element_type' => 'equipment_rental',
                'display_name' => '물품 대여',
                'description' => '몽골텐트, 부스, 테이블 등 각종 행사장 물품 대여입니다.',
                'input_schema' => json_encode([
                    'rental_items' => ['type' => 'array', 'items' => ['type' => 'object']],
                ]),
                'default_details_template' => json_encode([
                    'rental_items' => [],
                ]),
                'recommended_elements' => json_encode(['electric', 'security', 'transport']),
            ],
        ];

        foreach ($elements as $elementData) {
            // firstOrCreate 대신 create (migrate:fresh --seed 시 truncate 되므로)
            ElementDefinition::create($elementData); 
        }

        $this->command->info('Element definitions seeded successfully.');
    }
}
