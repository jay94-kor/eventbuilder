<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * 성공 응답
     */
    public static function success(string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 생성 성공 응답
     */
    public static function created(string $message, $data = null): JsonResponse
    {
        return self::success($message, $data, 201);
    }

    /**
     * 에러 응답
     */
    public static function error(string $message, $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 유효성 검사 에러 응답
     */
    public static function validationError(string $message, $errors = null): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * 권한 없음 응답
     */
    public static function forbidden(string $message = '권한이 없습니다.'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * 찾을 수 없음 응답
     */
    public static function notFound(string $message = '요청한 리소스를 찾을 수 없습니다.'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * 서버 에러 응답
     */
    public static function serverError(string $message = '서버 오류가 발생했습니다.', $error = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($error !== null && config('app.debug')) {
            $response['error'] = $error;
        }

        return response()->json($response, 500);
    }
}