<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest; // UpdateUserRequest 추가
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="사용자 회원가입",
     *     tags={"인증"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="회원가입 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="회원가입이 완료되었습니다."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="1|abcdef123456")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="유효성 검사 실패"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        Log::info('Register method entered.');
        try {
            // RegisterRequest에서 이미 유효성 검사를 완료했으므로 바로 사용자 생성
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'onboarded' => false, // 신규 사용자 온보딩 상태 초기화
            ]);

            Log::info('User registered successfully: ' . $user->email);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            Log::error('Registration error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => '회원가입 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="사용자 로그인",
     *     tags={"인증"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="로그인 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="로그인에 성공했습니다."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="2|abcdef123456")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="인증 실패"),
     *     @OA\Response(response=422, description="유효성 검사 실패"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => '이메일은 필수입니다.',
                'email.email' => '올바른 이메일 형식이 아닙니다.',
                'password.required' => '비밀번호는 필수입니다.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력 데이터가 올바르지 않습니다.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => '로그인에 성공했습니다.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'onboarded', 'skip_onboarding']), // 필요한 사용자 정보만 반환
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '로그인 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="사용자 로그아웃",
     *     tags={"인증"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="로그아웃 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="로그아웃되었습니다.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="인증되지 않음"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 현재 사용자의 모든 토큰 삭제
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => '로그아웃되었습니다.'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '로그아웃 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="현재 인증된 사용자 정보 조회",
     *     tags={"인증"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="사용자 정보 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="인증되지 않음"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => $user->only(['id', 'name', 'email', 'onboarded', 'skip_onboarding'])
            ]);

        } catch (\Exception $e) {
            Log::error('User info error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '사용자 정보 조회 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user",
     *     summary="사용자 정보 업데이트",
     *     tags={"인증"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="업데이트 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="사용자 정보가 성공적으로 업데이트되었습니다."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="인증되지 않음"),
     *     @OA\Response(response=422, description="유효성 검사 실패"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        try {
            $user = $request->user(); // 현재 인증된 사용자 가져오기

            // 이름과 이메일 업데이트
            $user->name = $request->name;
            $user->email = $request->email;

            // 비밀번호가 제공된 경우에만 업데이트
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // skip_onboarding 필드 업데이트
            if ($request->has('skip_onboarding')) {
                $user->skip_onboarding = $request->skip_onboarding;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => '사용자 정보가 성공적으로 업데이트되었습니다.',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '사용자 정보 업데이트 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/user/mark-onboarded",
     *     summary="사용자 온보딩 상태를 완료로 표시",
     *     tags={"인증"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="온보딩 상태 업데이트 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="온보딩 상태가 성공적으로 업데이트되었습니다."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="인증되지 않음"),
     *     @OA\Response(response=500, description="서버 오류")
     * )
     */
    public function markOnboarded(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->onboarded = true;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => '온보딩 상태가 성공적으로 업데이트되었습니다.',
                'data' => $user->only(['id', 'name', 'email', 'onboarded', 'skip_onboarding'])
            ]);

        } catch (\Exception $e) {
            Log::error('Mark onboarded error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '온보딩 상태 업데이트 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
