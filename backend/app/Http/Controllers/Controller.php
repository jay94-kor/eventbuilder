<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Bidly API Documentation",
 *     version="1.0.0",
 *     description="행사 기획 대행사와 용역사를 연결하는 입찰 플랫폼 API",
 *     @OA\Contact(
 *         email="support@bidly.com",
 *         name="Bidly Support Team"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Sanctum Bearer Token (형식: Bearer {token})"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="사용자 인증 관련 API"
 * )
 * 
 * @OA\Tag(
 *     name="RFP Management",
 *     description="RFP 생성 및 관리 API"
 * )
 * 
 * @OA\Tag(
 *     name="Announcements",
 *     description="공고 발행 및 조회 API"
 * )
 * 
 * @OA\Tag(
 *     name="Proposals",
 *     description="제안서 제출 및 관리 API"
 * )
 * 
 * @OA\Tag(
 *     name="Evaluation",
 *     description="제안서 평가 시스템 API"
 * )
 * 
 * @OA\Tag(
 *     name="Contracts",
 *     description="계약 관리 API"
 * )
 * 
 * @OA\Tag(
 *     name="Schedules",
 *     description="스케줄 관리 API"
 * )
 * 
 * @OA\Tag(
 *     name="Schedule Attachments",
 *     description="스케줄 첨부파일 관리 API"
 * )
 */
abstract class Controller
{
    //
}
