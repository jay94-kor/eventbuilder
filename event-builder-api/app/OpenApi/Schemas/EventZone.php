<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "EventZone",
    type: "object",
    properties: [
        new OA\Property(property: "name", type: "string", description: "구역 이름"),
        new OA\Property(property: "type", type: "string", enum: ["실내", "실외"], description: "구역 유형"),
        new OA\Property(property: "quantity", type: "integer", description: "구역 수량"),
    ],
    example: [
        "name" => "메인 홀",
        "type" => "실내",
        "quantity" => 1
    ]
)]
class EventZone {}