<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     type="object",
 *     required={"name", "email"},
 *     @OA\Property(property="name", type="string", description="사용자 이름", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", description="사용자 이메일", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", format="password", description="새 비밀번호 (선택 사항)", example="new_password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", description="새 비밀번호 확인 (비밀번호 변경 시 필수)", example="new_password123"),
 *     @OA\Property(property="skip_onboarding", type="boolean", description="온보딩 건너뛰기 여부", example=false)
 * )
 */
class UpdateUserRequest
{
}