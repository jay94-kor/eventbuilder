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
                'input_schema' => json_encode([
                    'size_m' => ['type' => 'object', 'properties' => ['width' => ['type' => 'number'], 'height' => ['type' => 'number'], 'depth' => ['type' => 'number']]],
                    'structure_type' => ['type' => 'string', 'enum' => ['트러스 구조', '팝업 구조', '일반 조립식']],
                    'features' => ['type' => 'array', 'items' => ['type' => 'string']],
                ]),
                'default_details_template' => json_encode([
                    'size_m' => ['width' => null, 'height' => null, 'depth' => null],
                    'structure_type' => null,
                    'features' => [],
                ]),
                'recommended_elements' => json_encode(['sound', 'lighting', 'video', 'LED_screen']),
                'default_spec_template' => json_encode([
                    [
                        'name' => '무대 크기',
                        'type' => 'text',
                        'unit' => 'm',
                        'default_value' => '10 x 8',
                        'required' => true,
                        'validation' => ['min_length' => 1]
                    ],
                    [
                        'name' => '무대 높이',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => '1.2',
                        'required' => true,
                        'validation' => ['min' => 0.5, 'max' => 3.0]
                    ],
                    [
                        'name' => '구조 타입',
                        'type' => 'select',
                        'options' => ['트러스 구조', '팝업 구조', '일반 조립식'],
                        'default_value' => '트러스 구조',
                        'required' => true
                    ],
                    [
                        'name' => '특수 기능',
                        'type' => 'textarea',
                        'default_value' => '',
                        'required' => false
                    ]
                ]),
                'quantity_config' => json_encode([
                    'unit' => '개',
                    'typical' => 1,
                    'min' => 1,
                    'max' => 3,
                    'allow_variants' => true
                ]),
                'variant_rules' => json_encode([
                    'allowed_fields' => ['size', 'height', 'structure_type'],
                    'max_variants' => 3,
                    'require_name' => true
                ]),
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
                'default_spec_template' => json_encode([
                    [
                        'name' => '시스템 타입',
                        'type' => 'select',
                        'options' => ['라인 어레이', '포인트 소스', '분산 시스템'],
                        'default_value' => '라인 어레이',
                        'required' => true
                    ],
                    [
                        'name' => '출력 파워',
                        'type' => 'number',
                        'unit' => 'W',
                        'default_value' => '5000',
                        'required' => true,
                        'validation' => ['min' => 1000, 'max' => 50000]
                    ],
                    [
                        'name' => '무선 마이크 수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => '4',
                        'required' => true,
                        'validation' => ['min' => 1, 'max' => 20]
                    ],
                    [
                        'name' => '유선 마이크 수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => '2',
                        'required' => false,
                        'validation' => ['min' => 0, 'max' => 10]
                    ],
                    [
                        'name' => '커버리지',
                        'type' => 'text',
                        'unit' => '평',
                        'default_value' => '500',
                        'required' => true
                    ]
                ]),
                'quantity_config' => json_encode([
                    'unit' => '세트',
                    'typical' => 1,
                    'min' => 1,
                    'max' => 5,
                    'allow_variants' => false
                ]),
                'variant_rules' => json_encode([
                    'allowed_fields' => [],
                    'max_variants' => 0,
                    'require_name' => false
                ]),
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
                'default_spec_template' => json_encode([
                    [
                        'name' => '조명 컨셉',
                        'type' => 'select',
                        'options' => ['웜톤 기본 조명', '컬러풀 연출 조명', '드라마틱 무드 조명', '미니멀 조명'],
                        'default_value' => '웜톤 기본 조명',
                        'required' => true
                    ],
                    [
                        'name' => '무빙 라이트 수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => '8',
                        'required' => true,
                        'validation' => ['min' => 2, 'max' => 50]
                    ],
                    [
                        'name' => 'PAR 조명 수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => '12',
                        'required' => true,
                        'validation' => ['min' => 4, 'max' => 100]
                    ],
                    [
                        'name' => '전력 소비량',
                        'type' => 'number',
                        'unit' => 'kW',
                        'default_value' => '15',
                        'required' => true,
                        'validation' => ['min' => 5, 'max' => 100]
                    ],
                    [
                        'name' => '특수 효과',
                        'type' => 'textarea',
                        'default_value' => '',
                        'required' => false
                    ]
                ]),
                'quantity_config' => json_encode([
                    'unit' => '세트',
                    'typical' => 1,
                    'min' => 1,
                    'max' => 3,
                    'allow_variants' => true
                ]),
                'variant_rules' => json_encode([
                    'allowed_fields' => ['concept', 'fixture_count'],
                    'max_variants' => 3,
                    'require_name' => true
                ]),
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
                'default_spec_template' => json_encode([
                    [
                        'name' => '스크린 타입',
                        'type' => 'select',
                        'options' => ['실내용 LED', '실외용 LED', '곡면 LED', '투명 LED'],
                        'default_value' => '실내용 LED',
                        'required' => true
                    ],
                    [
                        'name' => '스크린 크기 (가로)',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => '6',
                        'required' => true,
                        'validation' => ['min' => 2, 'max' => 20]
                    ],
                    [
                        'name' => '스크린 크기 (세로)',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => '4',
                        'required' => true,
                        'validation' => ['min' => 1.5, 'max' => 15]
                    ],
                    [
                        'name' => '픽셀 피치',
                        'type' => 'select',
                        'options' => ['2.5mm', '3.91mm', '4.81mm', '6.67mm'],
                        'default_value' => '3.91mm',
                        'required' => true
                    ],
                    [
                        'name' => '해상도',
                        'type' => 'select',
                        'options' => ['1920x1080', '1920x1200', '2560x1440', '3840x2160'],
                        'default_value' => '1920x1080',
                        'required' => true
                    ],
                    [
                        'name' => '밝기',
                        'type' => 'number',
                        'unit' => 'nits',
                        'default_value' => '5000',
                        'required' => true,
                        'validation' => ['min' => 1000, 'max' => 10000]
                    ]
                ]),
                'quantity_config' => json_encode([
                    'unit' => '개',
                    'typical' => 1,
                    'min' => 1,
                    'max' => 5,
                    'allow_variants' => true
                ]),
                'variant_rules' => json_encode([
                    'allowed_fields' => ['size', 'pixel_pitch', 'resolution'],
                    'max_variants' => 3,
                    'require_name' => true
                ]),
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
            // updateOrCreate로 변경하여 기존 데이터 업데이트
            ElementDefinition::updateOrCreate(
                ['element_type' => $elementData['element_type']],
                $elementData
            );
        }

        $this->command->info('Element definitions seeded successfully.');
    }
}
