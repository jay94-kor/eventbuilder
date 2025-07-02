<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ElementDefinition;

class DynamicSpecTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🔥 동적 스펙 템플릿 정의
        $templates = [
            // 🎬 영상 장비
            'led_screen' => [
                'display_name' => 'LED 스크린',
                'default_spec_template' => [
                    [
                        'name' => '가로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 3.0,
                        'required' => true,
                        'validation' => ['min' => 0.5, 'max' => 20.0]
                    ],
                    [
                        'name' => '세로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 2.0,
                        'required' => true,
                        'validation' => ['min' => 0.5, 'max' => 15.0]
                    ],
                    [
                        'name' => '픽셀피치',
                        'type' => 'select',
                        'options' => ['P1.56', 'P1.875', 'P2.5', 'P3.9', 'P4.8', 'P6.25', 'P10'],
                        'default_value' => 'P3.9'
                    ],
                    [
                        'name' => '해상도',
                        'type' => 'select',
                        'options' => ['HD', 'Full HD', '4K', '8K'],
                        'default_value' => 'Full HD',
                        'required' => true
                    ],
                    [
                        'name' => '밝기',
                        'type' => 'number',
                        'unit' => 'nits',
                        'default_value' => 5000,
                        'validation' => ['min' => 1000, 'max' => 15000]
                    ],
                    [
                        'name' => '설치방식',
                        'type' => 'select',
                        'options' => ['트러스행잉', '스탠드형', '벽부착', '바닥설치'],
                        'default_value' => '트러스행잉'
                    ]
                ],
                'quantity_config' => [
                    'unit' => '대',
                    'min' => 1,
                    'max' => 20,
                    'typical' => 3,
                    'allow_variants' => true
                ],
                'variant_rules' => [
                    'allowed_fields' => ['가로', '세로', '해상도', '설치방식'],
                    'max_variants' => 5,
                    'require_name' => true
                ]
            ],

            'beam_projector' => [
                'display_name' => '빔 프로젝터',
                'default_spec_template' => [
                    [
                        'name' => '밝기',
                        'type' => 'number',
                        'unit' => '루멘',
                        'default_value' => 5000,
                        'required' => true,
                        'validation' => ['min' => 1000, 'max' => 30000]
                    ],
                    [
                        'name' => '해상도',
                        'type' => 'select',
                        'options' => ['SVGA', 'XGA', 'WXGA', 'Full HD', '4K'],
                        'default_value' => 'Full HD',
                        'required' => true
                    ],
                    [
                        'name' => '투사거리',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 5.0,
                        'validation' => ['min' => 1.0, 'max' => 50.0]
                    ],
                    [
                        'name' => '렌즈타입',
                        'type' => 'select',
                        'options' => ['표준', '단초점', '초단초점', '줌'],
                        'default_value' => '표준'
                    ],
                    [
                        'name' => '설치방식',
                        'type' => 'select',
                        'options' => ['천장행잉', '스탠드형', '테이블형', '트러스행잉'],
                        'default_value' => '천장행잉'
                    ]
                ],
                'quantity_config' => [
                    'unit' => '대',
                    'min' => 1,
                    'max' => 10,
                    'typical' => 2,
                    'allow_variants' => true
                ]
            ],

            'lcd_pdp_monitor' => [
                'display_name' => 'LCD/PDP 모니터',
                'default_spec_template' => [
                    [
                        'name' => '크기',
                        'type' => 'select',
                        'options' => ['32인치', '43인치', '55인치', '65인치', '75인치', '85인치'],
                        'default_value' => '55인치',
                        'required' => true
                    ],
                    [
                        'name' => '해상도',
                        'type' => 'select',
                        'options' => ['Full HD', '4K', '8K'],
                        'default_value' => '4K',
                        'required' => true
                    ],
                    [
                        'name' => '패널타입',
                        'type' => 'select',
                        'options' => ['LCD', 'OLED', 'QLED'],
                        'default_value' => 'LCD'
                    ],
                    [
                        'name' => '설치방식',
                        'type' => 'select',
                        'options' => ['벽부착', '스탠드형', '천장행잉', '이동형'],
                        'default_value' => '스탠드형'
                    ],
                    [
                        'name' => '터치스크린',
                        'type' => 'boolean',
                        'default_value' => false
                    ]
                ],
                'quantity_config' => [
                    'unit' => '대',
                    'min' => 1,
                    'max' => 15,
                    'typical' => 4,
                    'allow_variants' => true
                ]
            ],

            // 🎵 음향 장비
            'speech_lecture_audio' => [
                'display_name' => '강연/세미나용 음향',
                'default_spec_template' => [
                    [
                        'name' => '스피커출력',
                        'type' => 'number',
                        'unit' => 'W',
                        'default_value' => 500,
                        'required' => true,
                        'validation' => ['min' => 100, 'max' => 2000]
                    ],
                    [
                        'name' => '스피커타입',
                        'type' => 'select',
                        'options' => ['포인트소스', '라인어레이', '컬럼스피커'],
                        'default_value' => '포인트소스'
                    ],
                    [
                        'name' => '마이크타입',
                        'type' => 'select',
                        'options' => ['핸드헬드', '라발리어', '헤드셋', '구즈넥'],
                        'default_value' => '핸드헬드'
                    ],
                    [
                        'name' => '마이크수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => 2,
                        'validation' => ['min' => 1, 'max' => 20]
                    ],
                    [
                        'name' => '믹서채널',
                        'type' => 'select',
                        'options' => ['8채널', '12채널', '16채널', '24채널', '32채널'],
                        'default_value' => '12채널'
                    ],
                    [
                        'name' => '녹음기능',
                        'type' => 'boolean',
                        'default_value' => false
                    ]
                ],
                'quantity_config' => [
                    'unit' => '세트',
                    'min' => 1,
                    'max' => 5,
                    'typical' => 1,
                    'allow_variants' => false
                ]
            ],

            'performance_band_audio' => [
                'display_name' => '공연/밴드용 음향',
                'default_spec_template' => [
                    [
                        'name' => '메인스피커출력',
                        'type' => 'number',
                        'unit' => 'W',
                        'default_value' => 2000,
                        'required' => true,
                        'validation' => ['min' => 500, 'max' => 10000]
                    ],
                    [
                        'name' => '시스템타입',
                        'type' => 'select',
                        'options' => ['라인어레이', '포인트소스', '혼합시스템'],
                        'default_value' => '라인어레이'
                    ],
                    [
                        'name' => '서브우퍼',
                        'type' => 'boolean',
                        'default_value' => true
                    ],
                    [
                        'name' => '모니터스피커수량',
                        'type' => 'number',
                        'unit' => '개',
                        'default_value' => 4,
                        'validation' => ['min' => 0, 'max' => 20]
                    ],
                    [
                        'name' => '믹서타입',
                        'type' => 'select',
                        'options' => ['아날로그', '디지털', '디지털콘솔'],
                        'default_value' => '디지털'
                    ],
                    [
                        'name' => '채널수',
                        'type' => 'select',
                        'options' => ['16채널', '24채널', '32채널', '48채널', '64채널'],
                        'default_value' => '32채널'
                    ],
                    [
                        'name' => '이펙터',
                        'type' => 'boolean',
                        'default_value' => true
                    ]
                ],
                'quantity_config' => [
                    'unit' => '세트',
                    'min' => 1,
                    'max' => 3,
                    'typical' => 1,
                    'allow_variants' => false
                ]
            ],

            // 🏗️ 무대 설치
            'main_stage_installation' => [
                'display_name' => '메인 무대 설치',
                'default_spec_template' => [
                    [
                        'name' => '가로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 10.0,
                        'required' => true,
                        'validation' => ['min' => 3.0, 'max' => 50.0]
                    ],
                    [
                        'name' => '세로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 5.0,
                        'required' => true,
                        'validation' => ['min' => 2.0, 'max' => 30.0]
                    ],
                    [
                        'name' => '높이',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 0.6,
                        'required' => true,
                        'validation' => ['min' => 0.2, 'max' => 3.0]
                    ],
                    [
                        'name' => '형태',
                        'type' => 'select',
                        'options' => ['직사각형', '정사각형', 'T자형', 'L자형', '원형', '타원형'],
                        'default_value' => '직사각형'
                    ],
                    [
                        'name' => '재질',
                        'type' => 'select',
                        'options' => ['일반합판', '방화합판', '카펫', '리노륨', '원목'],
                        'default_value' => '일반합판'
                    ],
                    [
                        'name' => '안전난간',
                        'type' => 'boolean',
                        'default_value' => true
                    ],
                    [
                        'name' => '계단',
                        'type' => 'select',
                        'options' => ['없음', '전면', '후면', '좌우', '전체'],
                        'default_value' => '전면'
                    ]
                ],
                'quantity_config' => [
                    'unit' => '개',
                    'min' => 1,
                    'max' => 5,
                    'typical' => 1,
                    'allow_variants' => true
                ]
            ],

            'sub_stage_installation' => [
                'display_name' => '서브 무대 설치',
                'default_spec_template' => [
                    [
                        'name' => '가로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 6.0,
                        'required' => true,
                        'validation' => ['min' => 2.0, 'max' => 20.0]
                    ],
                    [
                        'name' => '세로',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 4.0,
                        'required' => true,
                        'validation' => ['min' => 2.0, 'max' => 15.0]
                    ],
                    [
                        'name' => '높이',
                        'type' => 'number',
                        'unit' => 'm',
                        'default_value' => 0.4,
                        'required' => true,
                        'validation' => ['min' => 0.2, 'max' => 2.0]
                    ],
                    [
                        'name' => '형태',
                        'type' => 'select',
                        'options' => ['직사각형', '정사각형', '원형'],
                        'default_value' => '직사각형'
                    ],
                    [
                        'name' => '재질',
                        'type' => 'select',
                        'options' => ['일반합판', '카펫', '원목'],
                        'default_value' => '일반합판'
                    ]
                ],
                'quantity_config' => [
                    'unit' => '개',
                    'min' => 1,
                    'max' => 10,
                    'typical' => 2,
                    'allow_variants' => true
                ]
            ]
        ];

        // 🚀 ElementDefinition 업데이트
        foreach ($templates as $elementType => $templateData) {
            $element = ElementDefinition::where('element_type', $elementType)->first();
            
            if ($element) {
                $element->update([
                    'default_spec_template' => $templateData['default_spec_template'],
                    'quantity_config' => $templateData['quantity_config'],
                    'variant_rules' => $templateData['variant_rules'] ?? null
                ]);
                
                $this->command->info("✅ {$templateData['display_name']} 템플릿 적용 완료");
            } else {
                $this->command->warn("⚠️  {$elementType} 요소를 찾을 수 없습니다.");
            }
        }

        $this->command->info("🎉 동적 스펙 템플릿 시딩 완료!");
    }
}
