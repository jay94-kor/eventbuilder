<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 사용자 관리 설정
    |--------------------------------------------------------------------------
    */

    'status' => [
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'suspended' => 'suspended',
    ],

    'types' => [
        'agency_member' => 'agency_member',
        'vendor_member' => 'vendor_member',
        'admin' => 'admin',
    ],

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],
];