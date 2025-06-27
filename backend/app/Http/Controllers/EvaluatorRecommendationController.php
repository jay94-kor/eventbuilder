<?php

namespace App\Http\Controllers;

use App\Models\EvaluatorHistory;
use App\Models\User;
use App\Models\AgencyMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Evaluator Recommendation",
 *     description="심사위원 추천 시스템 API"
 * )
 */
class EvaluatorRecommendationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/evaluators/recommendations/{elementType}",
     *     summary="요소별 심사위원 추천",
     *     description="특정 요소(무대, 음향, 조명 등)에 대한 경험 많은 심사위원 추천",
     *     tags={"Evaluator Recommendation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="elementType",
     *         in="path",
     *         required=true,
     *         description="요소 타입",
     *         @OA\Schema(
     *             type="string",
     *             enum={"stage", "sound", "lighting", "casting", "security", "video", "photo", "electric", "transport", "printing", "LED_screen", "equipment_rental"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="추천할 심사위원 수 (기본값: 5)",
     *         @OA\Schema(type="integer", minimum=1, maximum=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 추천 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="무대 요소 심사위원 추천 목록입니다."),
     *             @OA\Property(property="element_type", type="string", example="stage"),
     *             @OA\Property(
     *                 property="recommendations",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="user_id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                     @OA\Property(property="name", type="string", example="김심사"),
     *                     @OA\Property(property="email", type="string", example="evaluator@agency.com"),
     *                     @OA\Property(property="evaluation_count", type="integer", example=15),
     *                     @OA\Property(property="avg_score", type="number", format="float", example=85.5),
     *                     @OA\Property(property="expertise_level", type="string", example="전문가"),
     *                     @OA\Property(property="last_evaluation_date", type="string", format="date", example="2024-01-15")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="해당 요소에 대한 경험자 없음")
     * )
     */
    public function getRecommendationsByElement(Request $request, string $elementType): JsonResponse
    {
        $limit = $request->query('limit', 5);
        $user = Auth::user();

        // 대행사 멤버만 접근 가능
        if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
            return response()->json([
                'message' => '심사위원 추천은 대행사 멤버만 조회할 수 있습니다.'
            ], 403);
        }

        // 같은 대행사 소속 심사위원만 추천 (관리자는 전체)
        $agencyId = null;
        if ($user->user_type === 'agency_member') {
            $agencyMember = AgencyMember::where('user_id', $user->id)->first();
            if (!$agencyMember) {
                return response()->json(['message' => '소속 대행사를 찾을 수 없습니다.'], 404);
            }
            $agencyId = $agencyMember->agency_id;
        }

        $query = EvaluatorHistory::select('evaluator_user_id')
            ->selectRaw('COUNT(*) as evaluation_count')
            ->selectRaw('AVG(evaluation_score) as avg_score')
            ->selectRaw('MAX(evaluation_completed_at) as last_evaluation_date')
            ->where('element_type', $elementType)
            ->where('evaluation_completed', true)
            ->groupBy('evaluator_user_id')
            ->orderByDesc('evaluation_count')
            ->orderByDesc('avg_score')
            ->limit($limit);

        // 같은 대행사 소속으로 제한
        if ($agencyId) {
            $query->whereHas('evaluator.agencyMember', function ($q) use ($agencyId) {
                $q->where('agency_id', $agencyId);
            });
        }

        $recommendations = $query->with('evaluator:id,name,email')->get();

        if ($recommendations->isEmpty()) {
            return response()->json([
                'message' => "해당 요소({$elementType})에 대한 경험 있는 심사위원을 찾을 수 없습니다.",
                'element_type' => $elementType,
                'recommendations' => []
            ], 404);
        }

        $formattedRecommendations = $recommendations->map(function ($item) {
            return [
                'user_id' => $item->evaluator_user_id,
                'name' => $item->evaluator->name,
                'email' => $item->evaluator->email,
                'evaluation_count' => $item->evaluation_count,
                'avg_score' => round($item->avg_score, 1),
                'expertise_level' => $this->getExpertiseLevel($item->evaluation_count, $item->avg_score),
                'last_evaluation_date' => $item->last_evaluation_date->format('Y-m-d')
            ];
        });

        return response()->json([
            'message' => $this->getElementName($elementType) . ' 요소 심사위원 추천 목록입니다.',
            'element_type' => $elementType,
            'recommendations' => $formattedRecommendations
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/evaluators/{userId}/expertise",
     *     summary="심사위원 전문성 분석",
     *     description="특정 심사위원의 요소별 평가 경험 및 전문성 분석",
     *     tags={"Evaluator Recommendation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="심사위원 사용자 ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 전문성 분석 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="심사위원 전문성 분석 결과입니다."),
     *             @OA\Property(
     *                 property="evaluator",
     *                 @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                 @OA\Property(property="name", type="string", example="김심사"),
     *                 @OA\Property(property="email", type="string", example="evaluator@agency.com")
     *             ),
     *             @OA\Property(
     *                 property="expertise",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="element_type", type="string", example="stage"),
     *                     @OA\Property(property="element_name", type="string", example="무대"),
     *                     @OA\Property(property="evaluation_count", type="integer", example=15),
     *                     @OA\Property(property="avg_score", type="number", format="float", example=85.5),
     *                     @OA\Property(property="expertise_level", type="string", example="전문가"),
     *                     @OA\Property(property="last_evaluation_at", type="string", format="date", example="2024-01-15")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="심사위원을 찾을 수 없음")
     * )
     */
    public function getEvaluatorExpertise(string $userId): JsonResponse
    {
        $user = Auth::user();

        // 대행사 멤버만 접근 가능
        if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
            return response()->json([
                'message' => '심사위원 전문성 분석은 대행사 멤버만 조회할 수 있습니다.'
            ], 403);
        }

        $evaluator = User::find($userId);
        if (!$evaluator) {
            return response()->json(['message' => '심사위원을 찾을 수 없습니다.'], 404);
        }

        $expertise = EvaluatorHistory::getEvaluatorExpertise($userId);

        if ($expertise->isEmpty()) {
            return response()->json([
                'message' => '해당 심사위원의 평가 이력이 없습니다.',
                'evaluator' => [
                    'id' => $evaluator->id,
                    'name' => $evaluator->name,
                    'email' => $evaluator->email
                ],
                'expertise' => []
            ]);
        }

        $formattedExpertise = $expertise->map(function ($item) {
            return [
                'element_type' => $item->element_type,
                'element_name' => $this->getElementName($item->element_type),
                'evaluation_count' => $item->evaluation_count,
                'avg_score' => round($item->avg_score, 1),
                'expertise_level' => $this->getExpertiseLevel($item->evaluation_count, $item->avg_score),
                'last_evaluation_at' => $item->last_evaluation_at->format('Y-m-d')
            ];
        });

        return response()->json([
            'message' => '심사위원 전문성 분석 결과입니다.',
            'evaluator' => [
                'id' => $evaluator->id,
                'name' => $evaluator->name,
                'email' => $evaluator->email
            ],
            'expertise' => $formattedExpertise
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/evaluators/statistics",
     *     summary="심사위원 통계 현황",
     *     description="대행사별 심사위원 활동 통계 및 요소별 전문가 현황",
     *     tags={"Evaluator Recommendation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="심사위원 통계 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="심사위원 통계 현황입니다."),
     *             @OA\Property(
     *                 property="statistics",
     *                 @OA\Property(property="total_evaluators", type="integer", example=25),
     *                 @OA\Property(property="active_evaluators", type="integer", example=18),
     *                 @OA\Property(property="total_evaluations", type="integer", example=342),
     *                 @OA\Property(
     *                     property="element_experts",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="element_type", type="string", example="stage"),
     *                         @OA\Property(property="element_name", type="string", example="무대"),
     *                         @OA\Property(property="expert_count", type="integer", example=5),
     *                         @OA\Property(property="total_evaluations", type="integer", example=87)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음")
     * )
     */
    public function getEvaluatorStatistics(): JsonResponse
    {
        $user = Auth::user();

        // 대행사 멤버만 접근 가능
        if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
            return response()->json([
                'message' => '심사위원 통계는 대행사 멤버만 조회할 수 있습니다.'
            ], 403);
        }

        // 같은 대행사 소속으로 제한
        $agencyId = null;
        if ($user->user_type === 'agency_member') {
            $agencyMember = AgencyMember::where('user_id', $user->id)->first();
            if ($agencyMember) {
                $agencyId = $agencyMember->agency_id;
            }
        }

        $query = EvaluatorHistory::query();
        if ($agencyId) {
            $query->whereHas('evaluator.agencyMember', function ($q) use ($agencyId) {
                $q->where('agency_id', $agencyId);
            });
        }

        $totalEvaluators = $query->distinct('evaluator_user_id')->count();
        $activeEvaluators = $query->where('evaluation_completed_at', '>=', now()->subMonths(6))
            ->distinct('evaluator_user_id')->count();
        $totalEvaluations = $query->where('evaluation_completed', true)->count();

        // 요소별 전문가 통계
        $elementStats = $query->select('element_type')
            ->selectRaw('COUNT(DISTINCT evaluator_user_id) as expert_count')
            ->selectRaw('COUNT(*) as total_evaluations')
            ->where('evaluation_completed', true)
            ->groupBy('element_type')
            ->orderByDesc('expert_count')
            ->get();

        $formattedElementStats = $elementStats->map(function ($item) {
            return [
                'element_type' => $item->element_type,
                'element_name' => $this->getElementName($item->element_type),
                'expert_count' => $item->expert_count,
                'total_evaluations' => $item->total_evaluations
            ];
        });

        return response()->json([
            'message' => '심사위원 통계 현황입니다.',
            'statistics' => [
                'total_evaluators' => $totalEvaluators,
                'active_evaluators' => $activeEvaluators,
                'total_evaluations' => $totalEvaluations,
                'element_experts' => $formattedElementStats
            ]
        ]);
    }

    /**
     * 전문성 레벨 결정
     */
    private function getExpertiseLevel(int $evaluationCount, float $avgScore): string
    {
        if ($evaluationCount >= 20 && $avgScore >= 85) {
            return '전문가';
        } elseif ($evaluationCount >= 10 && $avgScore >= 80) {
            return '숙련자';
        } elseif ($evaluationCount >= 5 && $avgScore >= 75) {
            return '경험자';
        } else {
            return '초보자';
        }
    }

    /**
     * 요소 타입을 한국어 이름으로 변환
     */
    private function getElementName(string $elementType): string
    {
        $elementNames = [
            'stage' => '무대',
            'sound' => '음향',
            'lighting' => '조명',
            'casting' => '섭외',
            'security' => '경호/의전/안전',
            'video' => '영상',
            'photo' => '사진',
            'electric' => '전기',
            'transport' => '운송',
            'printing' => '인쇄',
            'LED_screen' => 'LED 전광판',
            'equipment_rental' => '물품 대여'
        ];

        return $elementNames[$elementType] ?? $elementType;
    }
} 