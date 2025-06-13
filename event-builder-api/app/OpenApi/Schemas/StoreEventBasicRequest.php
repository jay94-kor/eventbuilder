<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StoreEventBasicRequest",
    type: "object",
    required: [
        "client_name",
        "event_title",
        "event_location",
        "venue_type",
        "project_kickoff_date",
        "settlement_close_date",
        "contact_person_name",
        "contact_person_contact",
        "admin_person_name",
        "admin_person_contact"
    ],
    properties: [
        new OA\Property(property: "client_name", type: "string", maxLength: 255, description: "클라이언트 이름"),
        new OA\Property(property: "event_title", type: "string", maxLength: 255, description: "이벤트 제목"),
        new OA\Property(property: "event_location", type: "string", maxLength: 255, description: "이벤트 장소"),
        new OA\Property(property: "venue_type", type: "string", enum: ["실내", "실외", "혼합"], description: "장소 유형"),
        new OA\Property(property: "zones", type: "array", items: new OA\Items(ref: "#/components/schemas/EventZone"), nullable: true, description: "구역 정보"),
        new OA\Property(property: "total_budget", type: "number", format: "float", minimum: 0, nullable: true, description: "총 예산"),
        new OA\Property(property: "is_total_budget_undecided", type: "boolean", description: "총 예산 미정 여부"),
        new OA\Property(property: "event_start_date_range_min", type: "string", format: "date", nullable: true, description: "이벤트 시작일 최소 범위"),
        new OA\Property(property: "event_start_date_range_max", type: "string", format: "date", nullable: true, description: "이벤트 시작일 최대 범위 (event_start_date_range_min 이후)"),
        new OA\Property(property: "event_end_date_range_min", type: "string", format: "date", nullable: true, description: "이벤트 종료일 최소 범위 (event_start_date_range_min 이후)"),
        new OA\Property(property: "event_end_date_range_max", type: "string", format: "date", nullable: true, description: "이벤트 종료일 최대 범위 (event_end_date_range_min 이후)"),
        new OA\Property(property: "event_duration_days", type: "integer", minimum: 0, nullable: true, description: "이벤트 기간 (일)"),
        new OA\Property(property: "setup_start_date", type: "string", format: "date", nullable: true, description: "설치 시작일"),
        new OA\Property(property: "teardown_end_date", type: "string", format: "date", nullable: true, description: "철수 종료일 (setup_start_date 이후)"),
        new OA\Property(property: "project_kickoff_date", type: "string", format: "date", description: "프로젝트 시작일"),
        new OA\Property(property: "settlement_close_date", type: "string", format: "date", description: "정산 마감일 (project_kickoff_date 이후)"),
        new OA\Property(property: "contact_person_name", type: "string", maxLength: 255, description: "담당자 이름"),
        new OA\Property(property: "contact_person_contact", type: "string", maxLength: 255, description: "담당자 연락처"),
        new OA\Property(property: "admin_person_name", type: "string", maxLength: 255, description: "관리자 이름"),
        new OA\Property(property: "admin_person_contact", type: "string", maxLength: 255, description: "관리자 연락처"),
    ],
    example: [
        "client_name" => "클라이언트 B",
        "event_title" => "연말 시상식",
        "event_location" => "그랜드볼룸",
        "venue_type" => "실내",
        "zones" => [
            ["name" => "VIP 라운지", "type" => "실내", "quantity" => 1]
        ],
        "total_budget" => 5000000.00,
        "is_total_budget_undecided" => false,
        "event_start_date_range_min" => "2024-12-10",
        "event_start_date_range_max" => "2024-12-10",
        "event_end_date_range_min" => "2024-12-10",
        "event_end_date_range_max" => "2024-12-10",
        "event_duration_days" => 1,
        "setup_start_date" => "2024-12-08",
        "teardown_end_date" => "2024-12-11",
        "project_kickoff_date" => "2024-10-01",
        "settlement_close_date" => "2025-01-31",
        "contact_person_name" => "이순신",
        "contact_person_contact" => "010-1111-2222",
        "admin_person_name" => "박관리",
        "admin_person_contact" => "010-3333-4444"
    ]
)]
class StoreEventBasicRequest {}