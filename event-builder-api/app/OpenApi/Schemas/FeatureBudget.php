<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FeatureBudget",
    type: "object",
    required: ["feature_id"],
    properties: [
        new OA\Property(property: "feature_id", type: "integer", description: "기능 ID"),
        new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, nullable: true, description: "할당된 예산 금액"),
        new OA\Property(property: "is_undecided", type: "boolean", description: "예산 미정 여부"),
    ],
    example: [
        "feature_id" => 101,
        "amount" => 500000,
        "is_undecided" => false
    ]
)]
class FeatureBudget {}