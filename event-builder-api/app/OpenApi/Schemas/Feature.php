<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Feature",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", readOnly: true, description: "기능 ID"),
        new OA\Property(property: "name", type: "string", description: "기능 이름"),
        new OA\Property(property: "icon", type: "string", nullable: true, description: "기능 아이콘 URL"),
        new OA\Property(property: "description", type: "string", nullable: true, description: "기능 설명"),
        new OA\Property(property: "category_id", type: "integer", description: "카테고리 ID"),
        new OA\Property(property: "sort_order", type: "integer", description: "정렬 순서"),
        new OA\Property(property: "is_active", type: "boolean", description: "활성화 여부"),
        new OA\Property(property: "is_premium", type: "boolean", description: "프리미엄 기능 여부"),
        new OA\Property(property: "config", type: "object", nullable: true, description: "기능 설정 (JSON)"),
        new OA\Property(property: "budget_allocation", type: "boolean", description: "예산 할당 여부"),
        new OA\Property(property: "internal_resource_flag", type: "boolean", description: "내부 리소스 플래그"),
        new OA\Property(property: "category", ref: "#/components/schemas/FeatureCategory", description: "기능이 속한 카테고리", readOnly: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, description: "생성일시"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, description: "수정일시"),
    ],
    example: [
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
        "category" => [
            "id" => 1,
            "name" => "기본 기능",
            "slug" => "basic-features",
            "description" => "모든 이벤트에 필요한 기본 기능",
            "sort_order" => 1,
            "is_active" => true,
            "budget_allocation" => false,
            "internal_resource_flag" => false,
            "created_at" => "2024-01-01T00:00:00.000000Z",
            "updated_at" => "2024-01-01T00:00:00.000000Z"
        ],
        "created_at" => "2024-01-01T00:00:00.000000Z",
        "updated_at" => "2024-01-01T00:00:00.000000Z"
    ]
)]
class Feature {}