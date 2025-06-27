<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rfp;
use App\Models\Announcement;
use App\Models\RfpElement;
use App\Models\Vendor; // Vendor 모델 추가
use App\Models\Agency; // Agency 모델 추가 (현재 사용되지 않지만, 관계를 위해 유지)
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * 승인된 RFP를 입찰 공고로 발행 (POST /api/rfps/{rfp}/publish)
     *
     * @OA\Post(
     *     path="/api/rfps/{rfp}/publish",
     *     tags={"Announcement Management"},
     *     summary="RFP를 공고로 발행",
     *     description="승인된 RFP를 입찰 공고로 발행합니다. 발주 타입에 따라 하나 또는 여러 개의 공고가 생성됩니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"closing_at","channel_type","contact_info_private","evaluation_criteria"},
     *             @OA\Property(property="closing_at", type="string", format="date-time", example="2024-01-30T17:00:00Z"),
     *             @OA\Property(property="estimated_price", type="number", example=45000000),
     *             @OA\Property(property="channel_type", type="string", enum={"agency_private","public"}, example="public"),
     *             @OA\Property(property="contact_info_private", type="boolean", example=false),
     *             @OA\Property(property="evaluation_criteria", type="object",
     *                 @OA\Property(property="price_weight", type="number", example=40),
     *                 @OA\Property(property="portfolio_weight", type="number", example=35),
     *                 @OA\Property(property="additional_weight", type="number", example=25),
     *                 @OA\Property(property="price_deduction_rate", type="number", example=5),
     *                 @OA\Property(property="price_rank_deduction_points", type="array", @OA\Items(type="number"), example={10, 20, 30})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="공고 발행 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 공고로 발행되었습니다. (총 1개 공고 생성)"),
     *             @OA\Property(property="rfp_status", type="string", example="published"),
     *             @OA\Property(property="announcements_count", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="공고 발행 권한이 없습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="상태 충돌",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="승인되지 않았거나 이미 공고된 RFP입니다.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Request $request, Rfp $rfp)
    {
        $user = Auth::user();

        // 1. 권한 확인: 관리자 또는 공고 발행 권한이 있는 사용자만 가능
        if ($user->user_type !== 'admin') { // 현재는 admin만 가능하도록 가정
            return response()->json(['message' => '공고 발행 권한이 없습니다.'], 403);
        }

        // 2. RFP 상태 확인: 'approved' 상태에서만 공고 발행 가능
        // (RFP가 승인되면 'approved' 상태가 되고, 공고 발행 시 'published'로 변경됨)
        if ($rfp->current_status !== 'approved') {
            return response()->json(['message' => '승인되지 않았거나 이미 공고된 RFP입니다.'], 409); // 409 Conflict
        }

        // 3. 요청 유효성 검사 (공고 마감일시 등, RFP의 closing_at을 그대로 사용할 수도 있음)
        $request->validate([
            'closing_at' => 'required|date|after:now', // 공고 마감일시 (현재 시간보다 미래)
            'estimated_price' => 'nullable|numeric|min:0', // 공고에 명시할 예상 금액
            'channel_type' => 'required|in:agency_private,public', // 공용 채널 또는 대행사 전용 채널
            'contact_info_private' => 'required|boolean', // 연락처 비공개 여부
            'evaluation_criteria' => 'required|array', // <--- 이 라인을 추가합니다. (평가 기준)
            'evaluation_criteria.price_weight' => 'required|numeric|min:0|max:100', // 가격 점수 비중
            'evaluation_criteria.portfolio_weight' => 'required|numeric|min:0|max:100', // 포트폴리오 점수 비중
            'evaluation_criteria.additional_weight' => 'required|numeric|min:0|max:100', // 추가 제안 점수 비중
            'evaluation_criteria.price_deduction_rate' => 'required|numeric|min:0|max:100', // 2등부터 깎이는 비율
            'evaluation_criteria.price_rank_deduction_points' => 'required|array', // 2등, 3등 등에 대한 깎이는 점수 배열 (예: [10, 20])
            'evaluation_criteria.price_rank_deduction_points.*' => 'numeric|min:0|max:100',
        ]);

        // 총 비중 100% 검증 (서버 측에서 다시 한번 확인)
        $totalWeight = $request->input('evaluation_criteria.price_weight') +
                       $request->input('evaluation_criteria.portfolio_weight') +
                       $request->input('evaluation_criteria.additional_weight');
        if ($totalWeight !== 100) {
            return response()->json(['message' => '평가 기준의 총 비중은 100%여야 합니다.'], 422);
        }

        DB::beginTransaction();
        try {
            $closingAt = $request->input('closing_at');
            $estimatedPrice = $request->input('estimated_price') ?? $rfp->project->budget_including_vat;
            $channelType = $request->input('channel_type');
            $contactInfoPrivate = $request->input('contact_info_private');
            $evaluationCriteria = $request->input('evaluation_criteria');

            $announcementsCount = 0;

            if ($rfp->issue_type === 'integrated') {
                $announcementTitle = $rfp->project->project_name . ' 행사 (통합 발주)';
                
                $descriptionParts = [
                    $rfp->rfp_description, // 프론트엔드에서 입력된 원본 설명
                    "", // 빈 줄
                    "[필수 용역]",
                ];
                
                $rfp->load('elements'); // RFP 요소 로드
                foreach ($rfp->elements as $el) {
                    // element->details는 이미 PHP 배열/객체 상태이므로, json_encode로 문자열화
                    $detailsString = $el->details ? json_encode($el->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '상세정보 없음';
                    $descriptionParts[] = "- " . $el->element_type . ": " . $detailsString;
                }
                $descriptionParts[] = "";
                $descriptionParts[] = "[발주 타입]: " . ($rfp->issue_type === 'integrated' ? '통합' : ($rfp->issue_type === 'separated_by_element' ? '요소별 분리' : '부분 묶음'));

                $finalAnnouncementDescription = implode("\n", $descriptionParts);
                // 중요: \n을 \\n으로 직접 치환 (jq 오류 방지용)
                $finalAnnouncementDescription = str_replace(["\r\n", "\r", "\n"], "\\n", $finalAnnouncementDescription); // <--- 다시 추가된 줄

                Announcement::create([
                    'rfp_id' => $rfp->id,
                    'rfp_element_id' => null,
                    'agency_id' => $rfp->agency_id,
                    'title' => $announcementTitle,
                    'description' => $finalAnnouncementDescription, // 수정된 description 사용
                    'estimated_price' => $estimatedPrice,
                    'closing_at' => $closingAt,
                    'channel_type' => $channelType,
                    'contact_info_private' => $contactInfoPrivate,
                    'published_at' => now(),
                    'status' => 'open',
                    'evaluation_criteria' => $evaluationCriteria,
                ]);
                $announcementsCount = 1;
            } else { // 'separated_by_element' 또는 'separated_by_group'
                $rfpElements = RfpElement::where('rfp_id', $rfp->id)->get();
                $processedElementTypes = [];

                foreach ($rfpElements as $element) {
                    $groupTitle = null;
                    $elementsInGroup = collect([$element]);

                    if ($rfp->issue_type === 'separated_by_group' && $element->parent_rfp_element_id === null) {
                        $groupTitle = $element->element_type . ' 등 (부분 묶음 발주)';
                        $elementsInGroup = RfpElement::where('rfp_id', $rfp->id)
                                                    ->where(function($query) use ($element) {
                                                        $query->where('parent_rfp_element_id', $element->id)
                                                              ->orWhere('id', $element->id);
                                                    })->get();
                        if (array_key_exists($element->element_type, $processedElementTypes) && $processedElementTypes[$element->element_type]) {
                            continue;
                        }
                        $processedElementTypes[$element->element_type] = true;
                        foreach($elementsInGroup as $groupedElement) {
                            $processedElementTypes[$groupedElement->element_type] = true;
                        }

                    } elseif ($rfp->issue_type === 'separated_by_element') {
                        if (array_key_exists($element->element_type, $processedElementTypes) && $processedElementTypes[$element->element_type]) {
                            continue;
                        }
                        $processedElementTypes[$element->element_type] = true;
                    } else {
                        if ($element->parent_rfp_element_id !== null) {
                            continue;
                        }
                    }

                    $announcementTitle = $rfp->project->project_name . ' - ' . ($groupTitle ?? $element->element_type) . ' 용역 입찰';
                    
                    $announcementDescriptionLines = [
                        $rfp->rfp_description,
                        "",
                        "[필수 용역]",
                    ];
                    foreach($elementsInGroup as $el) {
                        $detailsString = $el->details ? json_encode($el->details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '상세정보 없음';
                        $announcementDescriptionLines[] = "- " . $el->element_type . ": " . $detailsString;
                    }
                    $announcementDescriptionLines[] = "";
                    $announcementDescriptionLines[] = "[발주 타입]: " . ($rfp->issue_type === 'integrated' ? '통합' : ($rfp->issue_type === 'separated_by_element' ? '요소별 분리' : '부분 묶음'));

                    $finalAnnouncementDescription = implode("\n", $announcementDescriptionLines);
                    // 중요: \n을 \\n으로 직접 치환 (jq 오류 방지용)
                    $finalAnnouncementDescription = str_replace(["\r\n", "\r", "\n"], "\\n", $finalAnnouncementDescription); // <--- 다시 추가된 줄


                    $currentGroupAllocatedBudget = 0;
                    foreach($elementsInGroup as $el) {
                        $currentGroupAllocatedBudget += $el->allocated_budget ?? 0;
                    }

                    // **** 중복된 Announcement::create 블록 삭제됨 ****
                    Announcement::create([
                        'rfp_id' => $rfp->id,
                        'rfp_element_id' => ($rfp->issue_type === 'separated_by_group') ? $element->id : $element->id,
                        'agency_id' => $rfp->agency_id,
                        'title' => $announcementTitle,
                        'description' => $finalAnnouncementDescription,
                        'estimated_price' => $currentGroupAllocatedBudget > 0 ? $currentGroupAllocatedBudget : $estimatedPrice,
                        'closing_at' => $closingAt,
                        'channel_type' => $channelType,
                        'contact_info_private' => $contactInfoPrivate,
                        'published_at' => now(),
                        'status' => 'open',
                        'evaluation_criteria' => $evaluationCriteria,
                    ]);
                    $announcementsCount++;
                }
            }

            // 4. RFP 상태 변경 (공고 발행 완료)
            $rfp->current_status = 'published';
            $rfp->published_at = now(); // 공고 발행 일시 기록
            $rfp->save();

            DB::commit();
            return response()->json([
                'message' => "RFP가 성공적으로 공고로 발행되었습니다. (총 {$announcementsCount}개 공고 생성)",
                'rfp_status' => $rfp->current_status,
                'announcements_count' => $announcementsCount,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'RFP 공고 발행 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * 공고 목록 조회 (GET /api/announcements)
     * 용역사가 자신의 전문 분야에 맞는 공고를 찾을 때 사용
     *
     * @OA\Get(
     *     path="/api/announcements",
     *     tags={"Announcement Management"},
     *     summary="공고 목록 조회",
     *     description="공개된 입찰 공고 목록을 조회합니다. 용역사는 자신의 전문 분야와 승인된 대행사의 공고를 볼 수 있습니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="공고 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="입찰 공고 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="announcements", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="estimated_price", type="number"),
     *                         @OA\Property(property="closing_at", type="string", format="date-time"),
     *                         @OA\Property(property="channel_type", type="string"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="rfp", type="object"),
     *                         @OA\Property(property="agency", type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="소속된 용역사 정보를 찾을 수 없습니다.")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. 용역사는 자신의 전문 분야에 맞는 공고를 조회
        if ($user->user_type === 'vendor_member') {
            // eager loading으로 vendor 정보와 specialties를 한 번에 가져오기
            $vendor = $user->vendor_members()->with('vendor')->first()->vendor ?? null;
            if (!$vendor) {
                return response()->json(['message' => '소속된 용역사 정보를 찾을 수 없습니다.'], 403);
            }
            $vendorSpecialties = json_decode($vendor->specialties ?? '[]', true);

            $query = Announcement::where('status', 'open')
                                 ->where('closing_at', '>', now());

            $query->where(function($q) use ($vendor, $vendorSpecialties) {
                $q->where('channel_type', 'public');

                $approvedAgencies = $vendor->approvedAgencies()->pluck('agency_id');
                if ($approvedAgencies->isNotEmpty()) {
                    $q->orWhere(function($q2) use ($approvedAgencies) {
                        $q2->where('channel_type', 'agency_private')
                           ->whereIn('agency_id', $approvedAgencies);
                    });
                }
            });

            if (!empty($vendorSpecialties)) {
                $query->whereHas('rfpElement', function ($q) use ($vendorSpecialties) {
                    $q->whereIn('element_type', $vendorSpecialties);
                });
            }
            
            $announcements = $query->with('rfp.project', 'rfpElement', 'agency')
                                   ->orderBy('published_at', 'desc')
                                   ->paginate(10);

            return response()->json([
                'message' => '입찰 공고 목록을 성공적으로 불러왔습니다.',
                'announcements' => $announcements,
            ], 200);

        }
        // 2. 관리자 또는 대행사 멤버는 모든 공고를 조회 (또는 자신의 대행사 공고만)
        elseif ($user->user_type === 'admin' || $user->user_type === 'agency_member') {
            $query = Announcement::query();

            if ($user->user_type === 'agency_member') {
                $agencyId = $user->agency_members()->first()->agency_id ?? null;
                if ($agencyId) {
                    $query->where('agency_id', $agencyId);
                } else {
                    return response()->json(['message' => '소속된 대행사 정보를 찾을 수 없습니다.'], 403);
                }
            }
            
            $announcements = $query->with('rfp.project', 'rfpElement', 'agency')
                                   ->orderBy('published_at', 'desc')
                                   ->paginate(10);
            
            return response()->json([
                'message' => '공고 목록을 성공적으로 불러왔습니다.',
                'announcements' => $announcements,
            ], 200);
        }

        return response()->json(['message' => '접근 권한이 없습니다.'], 403);
    }

    /**
     * 특정 공고 상세 조회 (GET /api/announcements/{announcement})
     *
     * @param  \App\Models\Announcement  $announcement (모델 바인딩)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Announcement $announcement)
    {
        $user = Auth::user();

        // 1. 공고 접근 권한 확인
        if ($announcement->channel_type === 'agency_private') {
            $hasAccess = false;
            if ($user->user_type === 'agency_member' && ($user->agency_members()->first()->agency_id ?? null) === $announcement->agency_id) {
                $hasAccess = true;
            }
            elseif ($user->user_type === 'vendor_member') {
                // eager loading으로 vendor 정보와 approvedAgencies를 한 번에 가져오기
                $vendor = $user->vendor_members()->with('vendor.approvedAgencies')->first()->vendor ?? null;
                if ($vendor && $vendor->approvedAgencies->contains('id', $announcement->agency_id)) {
                    $hasAccess = true;
                }
            }
            elseif ($user->user_type === 'admin') {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                return response()->json(['message' => '이 공고에 접근할 권한이 없습니다.'], 403);
            }
        }
        else { // 공용 공고인 경우
             if ($user->user_type === 'vendor_member' || $user->user_type === 'admin') {
                // 용역사나 관리자는 공용 공고에 접근 가능
             } elseif ($user->user_type === 'agency_member' && ($user->agency_members()->first()->agency_id ?? null) === $announcement->agency_id) {
                // 해당 공고를 올린 대행사 멤버는 접근 가능
             } else {
                 return response()->json(['message' => '이 공고에 접근할 권한이 없습니다.'], 403);
             }
        }

        // 관련 RFP, 프로젝트, RFP 요소, 대행사 정보 로드
        $announcement->load('rfp.project', 'rfpElement', 'agency');

        return response()->json([
            'message' => '공고 상세 정보를 성공적으로 불러왔습니다.',
            'announcement' => $announcement,
        ], 200);
    }
}
