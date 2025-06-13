<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "BudgetValidationRequest",
    type: "object",
    properties: [
        new OA\Property(property: "total_budget", type: "number", format: "float", minimum: 0, nullable: true, description: "총 예산"),
        new OA\Property(property: "is_total_budget_undecided", type: "boolean", description: "총 예산 미정 여부"),
        new OA\Property(property: "category_budgets", type: "array", items: new OA\Items(ref: "#/components/schemas/CategoryBudget"), nullable: true, description: "카테고리별 예산 목록"),
        new OA\Property(property: "feature_budgets", type: "array", items: new OA\Items(ref: "#/components/schemas/FeatureBudget"), nullable: true, description: "기능별 예산 목록"),
    ],
    example: [
        "total_budget" => 10000000,
        "is_total_budget_undecided" => false,
        "category_budgets" => [
            ["category_id" => 1, "amount" => 5000000, "is_undecided" => false],
            ["category_id" => 2, "amount" => null, "is_undecided" => true]
        ],
        "feature_budgets" => [
            ["feature_id" => 101, "amount" => 1000000, "is_undecided" => false]
        ]
    ]
)]
class BudgetValidationRequest {}