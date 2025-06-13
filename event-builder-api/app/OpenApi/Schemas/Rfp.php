<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Rfp",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", readOnly: true, description: "RFP ID"),
        new OA\Property(property: "title", type: "string", description: "RFP 제목"),
        new OA\Property(property: "status", type: "string", enum: ["draft", "submitted", "completed", "cancelled"], description: "RFP 상태"),
        new OA\Property(property: "event_date", type: "string", format: "date", nullable: true, description: "행사 날짜"),
        new OA\Property(property: "user_id", type: "integer", readOnly: true, description: "사용자 ID"),
        new OA\Property(property: "total_budget", type: "integer", nullable: true, description: "총 예산"),
        new OA\Property(property: "is_total_budget_undecided", type: "boolean", description: "총 예산 미정 여부"),
        new OA\Property(property: "expected_attendees", type: "integer", nullable: true, description: "예상 참가자 수"),
        new OA\Property(property: "description", type: "string", nullable: true, description: "설명"),
        new OA\Property(property: "selections", type: "array", items: new OA\Items(ref: "#/components/schemas/RfpSelection"), description: "선택된 기능 목록"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, description: "생성일시"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, description: "수정일시"),
    ],
    example: [
        "id" => 1,
        "title" => "2024년 연례 컨퍼런스",
        "status" => "draft",
        "event_date" => "2024-10-26",
        "user_id" => 1,
        "total_budget" => 50000000,
        "is_total_budget_undecided" => false,
        "expected_attendees" => 500,
        "description" => "연례 컨퍼런스 개최를 위한 RFP",
        "selections" => [
            [
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
        ],
        "created_at" => "2024-06-01T10:00:00.000000Z",
        "updated_at" => "2024-06-01T10:00:00.000000Z"
    ]
)]
class Rfp {}