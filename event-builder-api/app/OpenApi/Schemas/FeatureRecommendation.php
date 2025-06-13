<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FeatureRecommendation",
    type: "object",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/Feature"),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: "pivot",
                    type: "object",
                    properties: [
                        new OA\Property(property: "feature_id", type: "integer", description: "기능 ID"),
                        new OA\Property(property: "recommended_feature_id", type: "integer", description: "추천 기능 ID"),
                        new OA\Property(property: "level", type: "string", enum: ["R1", "R2"], description: "추천 레벨 (R1: 1차 추천, R2: 2차 추천)"),
                        new OA\Property(property: "priority", type: "integer", description: "추천 우선순위"),
                    ],
                    example: [
                        "feature_id" => 1,
                        "recommended_feature_id" => 2,
                        "level" => "R1",
                        "priority" => 1
                    ]
                )
            ]
        )
    ],
    example: [
        "id" => 102,
        "name" => "조명 시스템",
        "icon" => "light-icon.png",
        "description" => "다양한 조명 효과 제공",
        "category_id" => 1,
        "sort_order" => 2,
        "is_active" => true,
        "is_premium" => true,
        "config" => [],
        "budget_allocation" => true,
        "internal_resource_flag" => false,
        "created_at" => "2024-01-01T00:00:00.000000Z",
        "updated_at" => "2024-01-01T00:00:00.000000Z",
        "pivot" => [
            "feature_id" => 101,
            "recommended_feature_id" => 102,
            "level" => "R1",
            "priority" => 1
        ]
    ]
)]
class FeatureRecommendation {}