<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Announcement;
use App\Models\AnnouncementEvaluator;
use App\Models\Evaluation;
use App\Models\EvaluatorHistory;
use App\Models\Proposal;
use App\Models\User;

class EvaluationController extends Controller
{
    /**
     * 심사위원 배정 API
     * POST /api/announcements/{announcement}/assign-evaluators
     */
    public function assignEvaluators(Request $request, Announcement $announcement)
    {
        $user = Auth::user();

        // 권한 확인: 해당 공고를 발행한 대행사의 관리자/마스터만 심사위원 배정 가능
        if ($user->user_type !== 'admin' && 
            ($user->user_type !== 'agency_member' || 
             ($user->agency_members->first()->agency_id ?? null) !== $announcement->agency_id)) {
            return response()->json(['message' => '심사위원 배정 권한이 없습니다.'], 403);
        }

        // 요청 데이터 유효성 검사
        $validatedData = $request->validate([
            'evaluator_user_ids' => 'required|array|min:1',
            'evaluator_user_ids.*' => 'required|string|exists:users,id',
            'assignment_type' => 'in:designated,random',
        ]);

        DB::beginTransaction();

        try {
            // 기존 심사위원 배정 삭제 (재배정 시)
            AnnouncementEvaluator::where('announcement_id', $announcement->id)->delete();

            $assignedEvaluators = [];

            foreach ($validatedData['evaluator_user_ids'] as $evaluatorUserId) {
                // 심사위원으로 지정할 사용자 검증
                $evaluatorUser = User::find($evaluatorUserId);
                
                if (!$evaluatorUser || $evaluatorUser->user_type !== 'agency_member') {
                    throw new \Exception("유효하지 않은 심사위원입니다: {$evaluatorUserId}");
                }

                // 같은 대행사 소속인지 확인
                $evaluatorAgencyId = $evaluatorUser->agency_members->first()->agency_id ?? null;
                if ($evaluatorAgencyId !== $announcement->agency_id) {
                    throw new \Exception("심사위원은 해당 공고 발행 대행사의 멤버여야 합니다.");
                }

                $announcementEvaluator = AnnouncementEvaluator::create([
                    'announcement_id' => $announcement->id,
                    'user_id' => $evaluatorUserId,
                    'assignment_type' => $validatedData['assignment_type'] ?? 'designated',
                    'assigned_at' => now(),
                ]);

                $assignedEvaluators[] = $announcementEvaluator->load('evaluator');
            }

            DB::commit();

            return response()->json([
                'message' => '심사위원이 성공적으로 배정되었습니다.',
                'announcement_id' => $announcement->id,
                'assigned_evaluators' => $assignedEvaluators,
                'total_evaluators' => count($assignedEvaluators),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '심사위원 배정 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 심사위원 점수 제출 API
     * POST /api/proposals/{proposal}/submit-score
     */
    public function submitScore(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // 심사위원으로 배정되었는지 확인
        $announcementEvaluator = AnnouncementEvaluator::where('announcement_id', $proposal->announcement_id)
                                                     ->where('user_id', $user->id)
                                                     ->first();

        if (!$announcementEvaluator) {
            return response()->json(['message' => '이 제안서에 대한 평가 권한이 없습니다.'], 403);
        }

        // 이미 평가했는지 확인
        $existingEvaluation = Evaluation::where('proposal_id', $proposal->id)
                                        ->where('evaluator_user_id', $user->id)
                                        ->first();

        if ($existingEvaluation) {
            return response()->json(['message' => '이미 이 제안서에 대한 평가를 완료했습니다.'], 409);
        }

        // 요청 데이터 유효성 검사
        $validatedData = $request->validate([
            'portfolio_score' => 'required|numeric|min:0|max:100',
            'additional_score' => 'required|numeric|min:0|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // 가격 점수는 시스템이 자동으로 계산 (나중에 구현)
            // 현재는 임시로 0으로 설정
            $priceScore = 0; // TODO: 가격 점수 계산 로직 구현

            // 총점 계산
            $totalScore = $priceScore + $validatedData['portfolio_score'] + $validatedData['additional_score'];

            $evaluation = Evaluation::create([
                'proposal_id' => $proposal->id,
                'evaluator_user_id' => $user->id,
                'price_score' => $priceScore,
                'portfolio_score' => $validatedData['portfolio_score'],
                'additional_score' => $validatedData['additional_score'],
                'total_score' => $totalScore,
                'evaluation_comment' => $validatedData['comment'] ?? null,
                'submitted_at' => now(),
            ]);

            // 📝 심사위원 이력 자동 생성
            $announcement = $proposal->announcement;
            $rfp = $announcement->rfp;
            $project = $rfp->project;

            // RFP 요소 타입 결정 (공고가 특정 요소에 대한 것인지 확인)
            $elementType = 'general'; // 기본값
            if ($announcement->rfp_element_id) {
                $rfpElement = $announcement->rfpElement;
                $elementType = $rfpElement->element_type ?? 'general';
            } else {
                // 통합 발주인 경우 첫 번째 요소 타입 사용
                $firstElement = $rfp->elements()->first();
                if ($firstElement) {
                    $elementType = $firstElement->element_type;
                }
            }

            EvaluatorHistory::create([
                'evaluator_user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'proposal_id' => $proposal->id,
                'element_type' => $elementType,
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'evaluation_score' => $totalScore,
                'evaluation_completed' => true,
                'evaluation_completed_at' => now(),
                'evaluation_notes' => $validatedData['comment'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => '평가 점수가 성공적으로 제출되었습니다.',
                'evaluation' => $evaluation->load('proposal', 'evaluator'),
                'evaluator_history_recorded' => true,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '점수 제출 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 평가 현황 및 최종 점수 계산 API
     * GET /api/announcements/{announcement}/evaluation-summary
     */
    public function getEvaluationSummary(Announcement $announcement)
    {
        $user = Auth::user();

        // 권한 확인: 해당 공고의 대행사 멤버 또는 관리자만 조회 가능
        if ($user->user_type !== 'admin' && 
            ($user->user_type !== 'agency_member' || 
             ($user->agency_members->first()->agency_id ?? null) !== $announcement->agency_id)) {
            return response()->json(['message' => '평가 현황 조회 권한이 없습니다.'], 403);
        }

        try {
            // 공고의 모든 제안서와 관련 평가 데이터 로드
            $proposals = Proposal::where('announcement_id', $announcement->id)
                                ->with(['vendor', 'evaluations.evaluator'])
                                ->get();

            // 배정된 심사위원 목록
            $assignedEvaluators = AnnouncementEvaluator::where('announcement_id', $announcement->id)
                                                      ->with('evaluator')
                                                      ->get();

            // 평가 기준 가져오기
            $evaluationCriteria = $announcement->evaluation_criteria;

            // 각 제안서별 평가 현황 계산
            $proposalSummaries = [];
            $allProposedPrices = $proposals->pluck('proposed_price')->toArray();
            $lowestPrice = min($allProposedPrices);

            foreach ($proposals as $proposal) {
                $evaluations = $proposal->evaluations;
                
                // 심사위원별 점수 (블라인드 처리: 심사위원 이름은 숨김)
                $evaluatorScores = $evaluations->map(function ($evaluation) {
                    return [
                        'evaluator_id' => $evaluation->evaluator_user_id,
                        'evaluator_name' => '심사위원 ' . substr($evaluation->evaluator_user_id, -4), // 마지막 4자리만 표시
                        'portfolio_score' => $evaluation->portfolio_score,
                        'additional_score' => $evaluation->additional_score,
                        'comment' => $evaluation->comment,
                        'submitted_at' => $evaluation->created_at,
                    ];
                });

                // 평균 점수 계산
                $avgPortfolioScore = $evaluations->avg('portfolio_score') ?? 0;
                $avgAdditionalScore = $evaluations->avg('additional_score') ?? 0;

                // 가격 점수 계산 (가격이 낮을수록 높은 점수)
                $priceScore = $lowestPrice > 0 ? ($lowestPrice / $proposal->proposed_price) * 100 : 0;
                $priceScore = min($priceScore, 100); // 최대 100점

                // 최종 가중 점수 계산
                $finalScore = 0;
                if ($evaluationCriteria) {
                    $priceWeight = $evaluationCriteria['price_weight'] ?? 0;
                    $portfolioWeight = $evaluationCriteria['portfolio_weight'] ?? 0;
                    $additionalWeight = $evaluationCriteria['additional_weight'] ?? 0;

                    $finalScore = ($priceScore * $priceWeight / 100) +
                                 ($avgPortfolioScore * $portfolioWeight / 100) +
                                 ($avgAdditionalScore * $additionalWeight / 100);
                }

                $proposalSummaries[] = [
                    'proposal_id' => $proposal->id,
                    'vendor_name' => $proposal->vendor->name,
                    'proposed_price' => $proposal->proposed_price,
                    'price_score' => round($priceScore, 2),
                    'avg_portfolio_score' => round($avgPortfolioScore, 2),
                    'avg_additional_score' => round($avgAdditionalScore, 2),
                    'final_weighted_score' => round($finalScore, 2),
                    'evaluations_count' => $evaluations->count(),
                    'evaluator_scores' => $evaluatorScores,
                ];
            }

            // 최종 점수 기준으로 정렬 (높은 점수순)
            usort($proposalSummaries, function ($a, $b) {
                return $b['final_weighted_score'] <=> $a['final_weighted_score'];
            });

            // 순위 추가
            foreach ($proposalSummaries as $index => &$summary) {
                $summary['rank'] = $index + 1;
            }

            return response()->json([
                'message' => '평가 현황을 성공적으로 불러왔습니다.',
                'announcement' => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'evaluation_criteria' => $evaluationCriteria,
                ],
                'assigned_evaluators' => $assignedEvaluators->map(function ($ae) {
                    return [
                        'user_id' => $ae->user_id,
                        'name' => $ae->evaluator->name,
                        'assigned_at' => $ae->assigned_at,
                    ];
                }),
                'proposal_summaries' => $proposalSummaries,
                'statistics' => [
                    'total_proposals' => $proposals->count(),
                    'total_evaluators' => $assignedEvaluators->count(),
                    'completed_evaluations' => Evaluation::whereIn('proposal_id', $proposals->pluck('id'))->count(),
                    'lowest_price' => $lowestPrice,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '평가 현황 조회 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 내가 평가해야 할 제안서 목록 조회
     * GET /api/my-evaluations
     */
    public function getMyEvaluations()
    {
        $user = Auth::user();

        if ($user->user_type !== 'agency_member') {
            return response()->json(['message' => '심사위원만 접근 가능합니다.'], 403);
        }

        try {
            // 내가 심사위원으로 배정된 공고들
            $myAssignments = AnnouncementEvaluator::where('user_id', $user->id)
                                                 ->with('announcement')
                                                 ->get();

            $evaluationTasks = [];

            foreach ($myAssignments as $assignment) {
                $announcement = $assignment->announcement;
                
                // 해당 공고의 모든 제안서
                $proposals = Proposal::where('announcement_id', $announcement->id)->with('vendor')->get();

                foreach ($proposals as $proposal) {
                    // 내가 이미 평가했는지 확인
                    $myEvaluation = Evaluation::where('proposal_id', $proposal->id)
                                             ->where('evaluator_user_id', $user->id)
                                             ->first();

                    $evaluationTasks[] = [
                        'announcement_id' => $announcement->id,
                        'announcement_title' => $announcement->title,
                        'proposal_id' => $proposal->id,
                        'vendor_name' => $proposal->vendor->name,
                        'proposed_price' => $proposal->proposed_price,
                        'is_evaluated' => !is_null($myEvaluation),
                        'my_evaluation' => $myEvaluation ? [
                            'portfolio_score' => $myEvaluation->portfolio_score,
                            'additional_score' => $myEvaluation->additional_score,
                            'comment' => $myEvaluation->comment,
                            'submitted_at' => $myEvaluation->created_at,
                        ] : null,
                    ];
                }
            }

            return response()->json([
                'message' => '내 평가 과제를 성공적으로 불러왔습니다.',
                'evaluation_tasks' => $evaluationTasks,
                'statistics' => [
                    'total_tasks' => count($evaluationTasks),
                    'completed_tasks' => count(array_filter($evaluationTasks, fn($task) => $task['is_evaluated'])),
                    'pending_tasks' => count(array_filter($evaluationTasks, fn($task) => !$task['is_evaluated'])),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '평가 과제 조회 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
