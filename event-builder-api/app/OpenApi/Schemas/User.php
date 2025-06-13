<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64", description="사용자 ID", example=1),
 *     @OA\Property(property="name", type="string", description="사용자 이름", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", description="사용자 이메일", example="john.doe@example.com"),
 *     @OA\Property(property="onboarded", type="boolean", description="온보딩 완료 여부", example=false),
 *     @OA\Property(property="skip_onboarding", type="boolean", description="온보딩 건너뛰기 여부", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="생성일시"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="수정일시")
 * )
 */
class User
{
}