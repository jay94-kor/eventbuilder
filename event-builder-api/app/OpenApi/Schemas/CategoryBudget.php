<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CategoryBudget",
    type: "object",
    required: ["category_id"],
    properties: [
        new OA\Property(property: "category_id", type: "integer", description: "카테고리 ID"),
        new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, nullable: true, description: "할당된 예산 금액"),
        new OA\Property(property: "is_undecided", type: "boolean", description: "예산 미정 여부"),
    ],
    example: [
        "category_id" => 1,
        "amount" => 1000000,
        "is_undecided" => false
    ]
)]
class CategoryBudget {}