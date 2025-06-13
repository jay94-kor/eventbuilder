<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StoreRfpRequest",
    type: "object",
    required: [
        "title",
        "selections"
    ],
    properties: [
        new OA\Property(property: "title", type: "string", maxLength: 255, description: "RFP 제목"),
        new OA\Property(property: "event_date", type: "string", format: "date", nullable: true, description: "행사 날짜 (오늘 이후)"),
        new OA\Property(property: "expected_attendees", type: "integer", minimum: 1, nullable: true, description: "예상 참가자 수"),
        new OA\Property(property: "total_budget", type: "integer", minimum: 0, nullable: true, description: "총 예산"),
        new OA\Property(property: "is_total_budget_undecided", type: "boolean", description: "총 예산 미정 여부"),
        new OA\Property(property: "description", type: "string", nullable: true, description: "설명"),
        new OA\Property(
            property: "selections",
            type: "array",
            minItems: 1,
            description: "선택된 기능 목록",
            items: new OA\Items(
                type: "object",
                required: ["feature_id"],
                properties: [
                    new OA\Property(property: "feature_id", type: "integer", description: "기능 ID"),
                    new OA\Property(property: "details", type: "object", nullable: true, description: "선택된 기능의 상세 정보 (JSON)"),
                    new OA\Property(property: "allocated_budget", type: "integer", minimum: 0, nullable: true, description: "할당된 예산"),
                    new OA\Property(property: "is_budget_undecided", type: "boolean", description: "예산 미정 여부"),
                ],
                example: [
                    "feature_id" => 101,
                    "details" => ["color" => "blue"],
                    "allocated_budget" => 50000,
                    "is_budget_undecided" => false
                ]
            )
        ),
    ],
    example: [
        "title" => "새로운 RFP",
        "event_date" => "2024-11-15",
        "expected_attendees" => 100,
        "total_budget" => 10000000,
        "is_total_budget_undecided" => false,
        "description" => "새로운 프로젝트를 위한 RFP",
        "selections" => [
            [
                "feature_id" => 101,
                "details" => ["color" => "red"],
                "allocated_budget" => 2000000,
                "is_budget_undecided" => false
            ],
            [
                "feature_id" => 102,
                "details" => [],
                "allocated_budget" => null,
                "is_budget_undecided" => true
            ]
        ]
    ]
)]
class StoreRfpRequest {}