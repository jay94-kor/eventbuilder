<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RfpSelection",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", readOnly: true, description: "RFP 선택 ID"),
        new OA\Property(property: "rfp_id", type: "integer", description: "RFP ID"),
        new OA\Property(property: "feature_id", type: "integer", description: "기능 ID"),
        new OA\Property(property: "details", type: "object", nullable: true, description: "선택된 기능의 상세 정보 (JSON)"),
        new OA\Property(property: "allocated_budget", type: "integer", nullable: true, description: "할당된 예산"),
        new OA\Property(property: "is_budget_undecided", type: "boolean", description: "예산 미정 여부"),
        new OA\Property(property: "feature", ref: "#/components/schemas/Feature", description: "선택된 기능 정보", readOnly: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, description: "생성일시"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, description: "수정일시"),
    ],
    example: [
        "id" => 1,
        "rfp_id" => 1,
        "feature_id" => 101,
        "details" => ["color" => "blue", "size" => "large"],
        "allocated_budget" => 100000,
        "is_budget_undecided" => false,
        "feature" => [
            "id" => 101,
            "name" => "무대 설치",
            "icon" => "stage-icon.png",
            "description" => "행사 무대 설치 및 해체",
            "category_id" => 1,
            "sort_order" => 1,
            "is_active" => true,
            "is_premium" => false,
            "config" => [],
            "budget_allocation" => true,
            "internal_resource_flag" => false,
            "created_at" => "2024-01-01T00:00:00.000000Z",
            "updated_at" => "2024-01-01T00:00:00.000000Z"
        ],
        "created_at" => "2024-06-01T10:00:00.000000Z",
        "updated_at" => "2024-06-01T10:00:00.000000Z"
    ]
)]
class RfpSelection {}