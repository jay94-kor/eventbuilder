<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TopFeature",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "Feature ID"),
        new OA\Property(property: "name", type: "string", description: "Feature 이름"),
        new OA\Property(property: "icon", type: "string", description: "Feature 아이콘 URL"),
        new OA\Property(property: "usage_count", type: "integer", description: "사용 횟수"),
    ],
    example: [
        "id" => 1,
        "name" => "Feature A",
        "icon" => "icon-a.png",
        "usage_count" => 10
    ]
)]
class TopFeature {}