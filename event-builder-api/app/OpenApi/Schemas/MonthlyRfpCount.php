<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "MonthlyRfpCount",
    type: "object",
    additionalProperties: new OA\AdditionalProperties(
        type: "integer",
        description: "월별 RFP 생성 개수 (YYYY-MM 형식의 키)"
    ),
    example: [
        "2024-01" => 5,
        "2024-02" => 3,
        "2024-03" => 7
    ]
)]
class MonthlyRfpCount {}