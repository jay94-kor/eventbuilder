<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 요소 템플릿 관리 설정
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'quantity_config' => [
            'unit' => '개',
            'min' => 1,
            'max' => 10,
            'typical' => 1,
            'allow_variants' => false
        ],
        
        'variant_rules' => [
            'allowed_fields' => [],
            'max_variants' => 3,
            'require_name' => true
        ],
    ],

    'default_templates' => [
        'led_screen' => [
            'spec_template' => [
                ['name' => '가로', 'type' => 'number', 'unit' => 'm', 'default_value' => 3.0, 'required' => true],
                ['name' => '세로', 'type' => 'number', 'unit' => 'm', 'default_value' => 2.0, 'required' => true],
                ['name' => '해상도', 'type' => 'select', 'options' => ['HD', 'Full HD', '4K'], 'default_value' => 'Full HD']
            ],
            'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 20, 'typical' => 3, 'allow_variants' => true]
        ],
        
        'beam_projector' => [
            'spec_template' => [
                ['name' => '밝기', 'type' => 'number', 'unit' => '루멘', 'default_value' => 5000, 'required' => true],
                ['name' => '해상도', 'type' => 'select', 'options' => ['XGA', 'Full HD', '4K'], 'default_value' => 'Full HD']
            ],
            'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 10, 'typical' => 2, 'allow_variants' => true]
        ],
        
        'sound_system' => [
            'spec_template' => [
                ['name' => '출력', 'type' => 'number', 'unit' => 'W', 'default_value' => 1000, 'required' => true],
                ['name' => '채널', 'type' => 'select', 'options' => ['스테레오', '5.1', '7.1'], 'default_value' => '스테레오']
            ],
            'quantity_config' => ['unit' => '세트', 'min' => 1, 'max' => 5, 'typical' => 1, 'allow_variants' => true]
        ],
        
        'stage' => [
            'spec_template' => [
                ['name' => '가로', 'type' => 'number', 'unit' => 'm', 'default_value' => 10.0, 'required' => true],
                ['name' => '세로', 'type' => 'number', 'unit' => 'm', 'default_value' => 8.0, 'required' => true],
                ['name' => '높이', 'type' => 'number', 'unit' => 'm', 'default_value' => 1.2, 'required' => true]
            ],
            'quantity_config' => ['unit' => '개', 'min' => 1, 'max' => 5, 'typical' => 1, 'allow_variants' => false]
        ],
        
        'default' => [
            'spec_template' => [
                ['name' => '수량', 'type' => 'number', 'unit' => '개', 'default_value' => 1, 'required' => true]
            ],
            'quantity_config' => ['unit' => '개', 'min' => 1, 'max' => 10, 'typical' => 1, 'allow_variants' => false]
        ]
    ],

    'field_types' => [
        'number' => 'number',
        'text' => 'text',
        'select' => 'select',
        'boolean' => 'boolean',
    ],

    'validation' => [
        'max_spec_fields' => 20,
        'max_field_name_length' => 50,
        'max_unit_length' => 10,
        'max_options_count' => 50,
        'max_variants' => 10,
    ],
];