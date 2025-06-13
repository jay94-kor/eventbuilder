<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "DashboardStats",
    type: "object",
    properties: [
        new OA\Property(property: "total_rfps", type: "integer", description: "총 RFP 개수"),
        new OA\Property(property: "completed_rfps", type: "integer", description: "완료된 RFP 개수"),
        new OA\Property(property: "monthly_rfp_counts", ref: "#/components/schemas/MonthlyRfpCount", description: "최근 6개월간 월별 RFP 생성 개수"),
        new OA\Property(property: "top_features", type: "array", items: new OA\Items(ref: "#/components/schemas/TopFeature"), description: "가장 많이 사용된 상위 5개 Feature"),
    ],
    example: [
        "total_rfps" => 10,
        "completed_rfps" => 5,
        "monthly_rfp_counts" => [
            "2024-01" => 2,
            "2024-02" => 1,
            "2024-03" => 3,
            "2024-04" => 0,
            "2024-05" => 4,
            "2024-06" => 0
        ],
        "top_features" => [
            ["id" => 1, "name" => "Feature A", "icon" => "icon-a.png", "usage_count" => 8],
            ["id" => 2, "name" => "Feature B", "icon" => "icon-b.png", "usage_count" => 6],
            ["id" => 3, "name" => "Feature C", "icon" => "icon-c.png", "usage_count" => 5]
        ]
    ]
)]
class DashboardStats {}