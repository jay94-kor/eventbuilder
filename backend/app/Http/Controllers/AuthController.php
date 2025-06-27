<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Auth 퍼사드 추가
use Illuminate\Support\Facades\Hash; // Hash 퍼사드 추가
use App\Models\User; // User 모델 추가 (선택 사항, 필요 시 직접 User 객체 접근용)

class AuthController extends Controller
{
    /**
     * 사용자 로그인 및 API 토큰 발행
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="사용자 로그인",
     *     description="이메일과 비밀번호로 로그인하고 API 토큰을 발급받습니다.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@bidly.com"),
     *             @OA\Property(property="password", type="string", format="password", example="bidlyadmin123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="로그인 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                 @OA\Property(property="name", type="string", example="관리자"),
     *                 @OA\Property(property="email", type="string", example="admin@bidly.com"),
     *                 @OA\Property(property="user_type", type="string", example="admin")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *             @OA\Property(property="message", type="string", example="로그인 성공")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="인증 정보가 올바르지 않습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 1. 요청 유효성 검사
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. 사용자 인증 시도
        // Auth::attempt는 사용자의 자격 증명(credentials)을 확인하고 세션을 시작합니다.
        // API 인증에서는 세션이 중요하지 않지만, 자격 증명 확인 로직은 동일합니다.
        if (!Auth::attempt($request->only('email', 'password'))) {
            // 인증 실패 시
            return response()->json([
                'message' => '인증 정보가 올바르지 않습니다.'
            ], 401); // 401 Unauthorized
        }

        // 3. 인증 성공 시 사용자 정보 가져오기
        $user = $request->user(); // Auth::attempt 성공 후 Request 객체에서 user()를 통해 현재 인증된 사용자 가져옴

        // 4. API 토큰 생성 (Sanctum)
        // 토큰 이름은 자유롭게 지정할 수 있습니다. 여기서는 'api-token'을 사용합니다.
        $token = $user->createToken('api-token')->plainTextToken;

        // 5. 성공 응답 반환
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type, // 사용자 타입 포함
            ],
            'token' => $token,
            'message' => '로그인 성공',
        ], 200);
    }

    /**
     * 사용자 로그아웃 및 현재 API 토큰 폐기
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="사용자 로그아웃",
     *     description="현재 API 토큰을 폐기하고 로그아웃합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="로그아웃 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="로그아웃 성공")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        
        if ($currentToken && method_exists($currentToken, 'delete')) {
            // 실제 토큰인 경우 현재 토큰만 삭제
            $currentToken->delete();
        } else {
            // TransientToken이거나 null인 경우 모든 토큰 삭제
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'message' => '로그아웃 성공'
        ], 200);
    }

    /**
     * 현재 인증된 사용자 정보 가져오기
     *
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="현재 사용자 정보 조회",
     *     description="Bearer 토큰으로 인증된 현재 사용자의 정보를 조회합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="사용자 정보 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                 @OA\Property(property="name", type="string", example="관리자"),
     *                 @OA\Property(property="email", type="string", example="admin@bidly.com"),
     *                 @OA\Property(property="user_type", type="string", example="admin")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'user_type' => $request->user()->user_type,
            ]
        ], 200);
    }
}
