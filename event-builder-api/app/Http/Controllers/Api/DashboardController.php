<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rfp;
use App\Models\RfpSelection;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/dashboard/stats",
 *     summary="사용자 대시보드 통계 데이터 조회",
 *     tags={"Dashboard"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="성공",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/DashboardStats")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="서버 오류",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="통계 데이터를 가져오는 중 오류가 발생했습니다."),
 *             @OA\Property(property="error", type="string", example="에러 메시지")
 *         )
 *     )
 * )
 */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            Log::info('Dashboard stats: Start processing for user ' . $user->id);

            // 1. 총 RFP 개수
            $startTotalRfps = microtime(true);
            $totalRfps = Rfp::where('user_id', $user->id)->count();
            Log::info('Dashboard stats: Total RFPs calculated in ' . (microtime(true) - $startTotalRfps) . ' seconds');

            // 2. 완료된 RFP 개수
            $startCompletedRfps = microtime(true);
            $completedRfps = Rfp::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            Log::info('Dashboard stats: Completed RFPs calculated in ' . (microtime(true) - $startCompletedRfps) . ' seconds');

            // 3. 최근 6개월간 월별 RFP 생성 개수
            $startMonthlyRfpCounts = microtime(true);
            $monthlyRfpCounts = $this->getMonthlyRfpCounts($user->id);
            Log::info('Dashboard stats: Monthly RFP counts calculated in ' . (microtime(true) - $startMonthlyRfpCounts) . ' seconds');

            // 4. 가장 많이 사용된 상위 5개 Feature
            $startTopFeatures = microtime(true);
            $topFeatures = $this->getTopFeatures($user->id);
            Log::info('Dashboard stats: Top features calculated in ' . (microtime(true) - $startTopFeatures) . ' seconds');

            Log::info('Dashboard stats: All data processed for user ' . $user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_rfps' => $totalRfps,
                    'completed_rfps' => $completedRfps,
                    'monthly_rfp_counts' => $monthlyRfpCounts,
                    'top_features' => $topFeatures,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '통계 데이터를 가져오는 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 최근 6개월간 월별 RFP 생성 개수 계산
     */
    private function getMonthlyRfpCounts(int $userId): array
    {
        Log::info('Dashboard stats: getMonthlyRfpCounts started for user ' . $userId);
        // 최근 6개월 날짜 범위 계산
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(5)->startOfMonth();
        
        // 월별 RFP 개수 조회 (PostgreSQL 호환)
        $monthlyData = Rfp::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        // 6개월 전체 기간에 대해 0으로 초기화된 배열 생성
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $endDate->copy()->subMonths($i)->format('Y-m');
            $result[$month] = $monthlyData[$month] ?? 0;
        }
        Log::info('Dashboard stats: getMonthlyRfpCounts finished for user ' . $userId);
        return $result;
    }
    
    /**
     * 가장 많이 사용된 상위 5개 Feature 계산
     */
    private function getTopFeatures(int $userId): array
    {
        Log::info('Dashboard stats: getTopFeatures started for user ' . $userId);
        // 사용자의 RFP에서 사용된 Feature들의 사용 횟수 계산
        $topFeatures = DB::table('rfp_selections')
            ->join('rfps', 'rfp_selections.rfp_id', '=', 'rfps.id')
            ->join('features', 'rfp_selections.feature_id', '=', 'features.id')
            ->where('rfps.user_id', $userId)
            ->select(
                'features.id',
                'features.name',
                'features.icon', // icon 필드 추가
                DB::raw('COUNT(rfp_selections.id) as usage_count')
            )
            ->groupBy('features.id', 'features.name', 'features.icon') // icon 필드도 groupBy에 추가
            ->orderByDesc('usage_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'icon' => $item->icon, // icon 필드 추가
                    'usage_count' => (int) $item->usage_count,
                ];
            })
            ->toArray();
        Log::info('Dashboard stats: getTopFeatures finished for user ' . $userId);
        return $topFeatures;
    }
}
