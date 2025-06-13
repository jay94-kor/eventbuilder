<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\FeatureCategory;
use App\Models\Feature;

class FeatureController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/features",
     *     summary="모든 FeatureCategory와 관련 Features 조회",
     *     tags={"Feature"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FeatureCategory")
     *             ),
     *             @OA\Property(property="message", type="string", example="Features retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve features"),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            // 활성화된 카테고리와 그에 속한 활성화된 기능들을 로드
            $categories = FeatureCategory::with(['features.recommendations', 'features.category'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // 기능이 있는 카테고리만 필터링
            $categories = $categories->filter(function ($category) {
                return $category->features->count() > 0;
            })->values();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Features retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve features',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/features/{feature}/recommendations",
     *     summary="특정 Feature의 추천 기능 목록 조회",
     *     tags={"Feature"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="feature",
     *         in="path",
     *         required=true,
     *         description="Feature ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="r1",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/FeatureRecommendation"),
     *                     description="1차 추천 기능 목록"
     *                 ),
     *                 @OA\Property(
     *                     property="r2",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/FeatureRecommendation"),
     *                     description="2차 추천 기능 목록"
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Recommendations retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feature를 찾을 수 없음"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve recommendations"),
     *             @OA\Property(property="error", type="string", example="에러 메시지")
     *         )
     *     )
     * )
     */
    public function showRecommendations(Feature $feature): JsonResponse
    {
        try {
            // R1 (1차 추천)과 R2 (2차 추천)을 구분하여 로드
            $recommendations = $feature->recommendations()->withPivot('level', 'priority')->get();

            $r1 = $recommendations->where('pivot.level', 'R1')->sortBy('pivot.priority')->values();
            $r2 = $recommendations->where('pivot.level', 'R2')->sortBy('pivot.priority')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'r1' => $r1,
                    'r2' => $r2,
                ],
                'message' => 'Recommendations retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
