<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RFP 기본 설정
    |--------------------------------------------------------------------------
    |
    | RFP 관련 기본 설정값들을 정의합니다.
    |
    */

    'defaults' => [
        'project_name' => '(임시저장)',
        'closing_days' => 7, // 기본 마감일까지 일수
        'preparation_days' => 30, // 기본 준비 시작일까지 일수
        'event_duration_hours' => 8, // 기본 행사 진행 시간
        'venue_type' => 'indoor', // 기본 장소 타입
        'issue_type' => 'integrated', // 기본 발주 타입
    ],

    'status' => [
        'draft' => 'draft',
        'published' => 'published',
        'closed' => 'closed',
        'cancelled' => 'cancelled',
    ],

    'issue_types' => [
        'integrated' => 'integrated',
        'separated_by_element' => 'separated_by_element',
        'separated_by_group' => 'separated_by_group',
    ],

    'venue_types' => [
        'indoor' => 'indoor',
        'outdoor' => 'outdoor',
        'both' => 'both',
    ],

    'validation' => [
        'project_name_max_length' => 255,
        'location_max_length' => 255,
        'contact_number_max_length' => 20,
        'min_elements' => 1,
        'max_prepayment_ratio' => 1.0,
        'min_budget' => 0,
    ],
];