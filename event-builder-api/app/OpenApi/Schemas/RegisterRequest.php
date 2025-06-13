<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", description="사용자 이름", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", description="사용자 이메일", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", format="password", description="비밀번호", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", description="비밀번호 확인", example="password123")
 * )
 */
class RegisterRequest
{
}