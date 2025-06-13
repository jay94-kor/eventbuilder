<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "EventBasic",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", readOnly: true, description: "이벤트 ID"),
        new OA\Property(property: "client_name", type: "string", description: "클라이언트 이름"),
        new OA\Property(property: "event_title", type: "string", description: "이벤트 제목"),
        new OA\Property(property: "event_location", type: "string", description: "이벤트 장소"),
        new OA\Property(property: "venue_type", type: "string", enum: ["실내", "실외", "혼합"], description: "장소 유형"),
        new OA\Property(property: "zones", type: "array", items: new OA\Items(ref: "#/components/schemas/EventZone"), description: "구역 정보"),
        new OA\Property(property: "total_budget", type: "number", format: "float", description: "총 예산"),
        new OA\Property(property: "is_total_budget_undecided", type: "boolean", description: "총 예산 미정 여부"),
        new OA\Property(property: "event_start_date_range_min", type: "string", format: "date", nullable: true, description: "이벤트 시작일 최소 범위"),
        new OA\Property(property: "event_start_date_range_max", type: "string", format: "date", nullable: true, description: "이벤트 시작일 최대 범위"),
        new OA\Property(property: "event_end_date_range_min", type: "string", format: "date", nullable: true, description: "이벤트 종료일 최소 범위"),
        new OA\Property(property: "event_end_date_range_max", type: "string", format: "date", nullable: true, description: "이벤트 종료일 최대 범위"),
        new OA\Property(property: "event_duration_days", type: "integer", nullable: true, description: "이벤트 기간 (일)"),
        new OA\Property(property: "setup_start_date", type: "string", format: "date", nullable: true, description: "설치 시작일"),
        new OA\Property(property: "teardown_end_date", type: "string", format: "date", nullable: true, description: "철수 종료일"),
        new OA\Property(property: "project_kickoff_date", type: "string", format: "date", description: "프로젝트 시작일"),
        new OA\Property(property: "settlement_close_date", type: "string", format: "date", description: "정산 마감일"),
        new OA\Property(property: "contact_person_name", type: "string", description: "담당자 이름"),
        new OA\Property(property: "contact_person_contact", type: "string", description: "담당자 연락처"),
        new OA\Property(property: "admin_person_name", type: "string", description: "관리자 이름"),
        new OA\Property(property: "admin_person_contact", type: "string", description: "관리자 연락처"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, description: "생성일시"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, description: "수정일시"),
    ],
    example: [
        "id" => 1,
        "client_name" => "클라이언트 A",
        "event_title" => "신제품 런칭 행사",
        "event_location" => "코엑스",
        "venue_type" => "실내",
        "zones" => [
            ["name" => "메인 홀", "type" => "실내", "quantity" => 1],
            ["name" => "야외 부스", "type" => "실외", "quantity" => 2]
        ],
        "total_budget" => 10000000.00,
        "is_total_budget_undecided" => false,
        "event_start_date_range_min" => "2024-07-01",
        "event_start_date_range_max" => "2024-07-05",
        "event_end_date_range_min" => "2024-07-01",
        "event_end_date_range_max" => "2024-07-05",
        "event_duration_days" => 5,
        "setup_start_date" => "2024-06-28",
        "teardown_end_date" => "2024-07-06",
        "project_kickoff_date" => "2024-05-01",
        "settlement_close_date" => "2024-08-31",
        "contact_person_name" => "홍길동",
        "contact_person_contact" => "010-1234-5678",
        "admin_person_name" => "김관리",
        "admin_person_contact" => "010-9876-5432",
        "created_at" => "2024-06-01T10:00:00.000000Z",
        "updated_at" => "2024-06-01T10:00:00.000000Z"
    ]
)]
class EventBasic {}