<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rfp;
use App\Services\RfpService;
use App\Http\Requests\StoreRfpRequest;
use App\Http\Requests\StoreDraftRfpRequest;
use App\Http\Requests\UpdateDraftRfpRequest;
use App\Http\Requests\PublishDraftRfpRequest;
use App\Http\Responses\ApiResponse;

class RfpController extends Controller
{
    protected $rfpService;

    public function __construct(RfpService $rfpService)
    {
        $this->rfpService = $rfpService;
    }
    /**
     * RFP 생성 (POST /api/rfps)
     *
     * @OA\Post(
     *     path="/api/rfps",
     *     tags={"RFP Management"},
     *     summary="RFP 생성",
     *     description="새로운 RFP(Request for Proposal)를 생성합니다. 프로젝트와 RFP 요소들을 함께 생성합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"project_name","start_datetime","end_datetime","is_indoor","location","issue_type","closing_at","elements"},
     *             @OA\Property(property="project_name", type="string", example="2024 신년 행사"),
     *             @OA\Property(property="start_datetime", type="string", format="date-time", example="2024-02-01T09:00:00Z"),
     *             @OA\Property(property="end_datetime", type="string", format="date-time", example="2024-02-01T18:00:00Z"),
     *             @OA\Property(property="preparation_start_datetime", type="string", format="date-time", example="2024-01-30T08:00:00Z"),
     *             @OA\Property(property="철수_end_datetime", type="string", format="date-time", example="2024-02-02T12:00:00Z"),
     *             @OA\Property(property="client_name", type="string", example="ABC 회사"),
     *             @OA\Property(property="client_contact_person", type="string", example="김담당자"),
     *             @OA\Property(property="client_contact_number", type="string", example="010-1234-5678"),
     *             @OA\Property(property="is_indoor", type="boolean", example=true),
     *             @OA\Property(property="location", type="string", example="서울시 강남구 코엑스"),
     *             @OA\Property(property="budget_including_vat", type="number", example=50000000),
     *             @OA\Property(property="issue_type", type="string", enum={"integrated","separated_by_element","separated_by_group"}, example="integrated"),
     *             @OA\Property(property="rfp_description", type="string", example="신년 행사를 위한 종합 이벤트 기획"),
     *             @OA\Property(property="closing_at", type="string", format="date-time", example="2024-01-25T17:00:00Z"),
     *             @OA\Property(property="elements", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="element_type", type="string", example="stage"),
     *                     @OA\Property(property="details", type="object", example={"size": "10m x 8m", "height": "1.2m"}),
     *                     @OA\Property(property="allocated_budget", type="number", example=10000000),
     *                     @OA\Property(property="prepayment_ratio", type="number", example=0.3),
     *                     @OA\Property(property="prepayment_due_date", type="string", format="date", example="2024-01-28"),
     *                     @OA\Property(property="balance_ratio", type="number", example=0.7),
     *                     @OA\Property(property="balance_due_date", type="string", format="date", example="2024-02-05")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="RFP 생성 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 생성되었습니다."),
     *             @OA\Property(property="rfp", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 실패",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="서버 오류",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 생성 중 오류가 발생했습니다.")
     *         )
     *     )
     * )
     *
     * @param  StoreRfpRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRfpRequest $request)
    {
        try {
            $user = Auth::user();
            
            // 권한 체크
            if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
                return ApiResponse::forbidden('RFP 생성 권한이 없습니다.');
            }

            if ($user->user_type === 'agency_member') {
                $agencyId = $user->agency_members->first()->agency_id ?? null;
                if (!$agencyId) {
                    return ApiResponse::forbidden('소속된 대행사 정보를 찾을 수 없습니다.');
                }
            }

            $result = $this->rfpService->createRfp($request->validated());

            return ApiResponse::created('RFP가 성공적으로 생성되었습니다.', [
                'rfp' => $result['rfp'],
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('RFP 생성 중 오류가 발생했습니다.', $e->getMessage());
        }
    }

    /**
     * RFP 목록 조회 (GET /api/rfps)
     *
     * @OA\Get(
     *     path="/api/rfps",
     *     tags={"RFP Management"},
     *     summary="RFP 목록 조회",
     *     description="사용자가 소속된 대행사의 RFP 목록을 조회합니다. 관리자는 모든 RFP를 조회할 수 있습니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="RFP 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="rfps", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                         @OA\Property(property="current_status", type="string", example="draft"),
     *                         @OA\Property(property="issue_type", type="string", example="integrated"),
     *                         @OA\Property(property="closing_at", type="string", format="date-time"),
     *                         @OA\Property(property="project", type="object"),
     *                         @OA\Property(property="elements", type="array", @OA\Items(type="object"))
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
     *             @OA\Property(property="message", type="string", example="소속된 대행사 정보를 찾을 수 없습니다.")
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

        // 사용자의 user_type에 따라 다른 RFP 목록을 제공할 수 있습니다.
        // 현재는 대행사 멤버가 자신의 대행사 RFP만 볼 수 있도록 합니다.
        if ($user->user_type === 'agency_member' || $user->user_type === 'admin') {
            $agencyId = $user->agency_members->first()->agency_id ?? null; // 대행사 ID 가져오기

            if (!$agencyId) {
                return response()->json(['message' => '소속된 대행사 정보를 찾을 수 없습니다.'], 403);
            }

            $rfps = Rfp::where('agency_id', $agencyId)
                       ->with('project', 'elements') // 관련 프로젝트와 요소들을 함께 로드
                       ->orderBy('created_at', 'desc')
                       ->paginate(10); // 페이지네이션 적용

            return response()->json([
                'message' => 'RFP 목록을 성공적으로 불러왔습니다.',
                'rfps' => $rfps,
            ], 200);
        }

        // 용역사(vendor_member)는 자신이 지원 가능한 공고 목록을 봐야 하므로,
        // 여기서는 RFP 목록을 제공하지 않거나, 별도의 API를 통해 제공할 수 있습니다.
        return response()->json(['message' => '접근 권한이 없습니다.'], 403);
    }

    /**
     * 특정 RFP 상세 조회 (GET /api/rfps/{rfp})
     *
     * @OA\Get(
     *     path="/api/rfps/{rfp}",
     *     tags={"RFP Management"},
     *     summary="RFP 상세 조회",
     *     description="특정 RFP의 상세 정보를 조회합니다. 프로젝트 정보와 RFP 요소들을 포함합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RFP 상세 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 상세 정보를 성공적으로 불러왔습니다."),
     *             @OA\Property(property="rfp", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="current_status", type="string"),
     *                 @OA\Property(property="issue_type", type="string"),
     *                 @OA\Property(property="rfp_description", type="string"),
     *                 @OA\Property(property="closing_at", type="string", format="date-time"),
     *                 @OA\Property(property="project", type="object"),
     *                 @OA\Property(property="elements", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="권한 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="이 RFP에 접근할 권한이 없습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RFP를 찾을 수 없음",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP를 찾을 수 없습니다.")
     *         )
     *     )
     * )
     *
     * @param  \App\Models\Rfp  $rfp  (모델 바인딩)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Rfp $rfp)
    {
        $user = Auth::user();

        // RFP의 소유 대행사 멤버만 접근 가능하도록 권한 확인
        // 또는 RFP 생성자나 결재 라인에 있는 사용자만 가능하도록 확장할 수 있습니다.
        if ($user->user_type === 'agency_member' && ($user->agency_members->first()->agency_id ?? null) !== $rfp->agency_id) {
            return response()->json(['message' => '이 RFP에 접근할 권한이 없습니다.'], 403);
        }
        if ($user->user_type === 'vendor_member') {
             // 용역사 멤버는 특정 RFP 상세를 직접 조회하는 대신,
             // 공고 상세 API를 통해 접근하도록 유도하거나 별도의 권한 부여 로직이 필요합니다.
             return response()->json(['message' => '이 RFP에 접근할 권한이 없습니다.'], 403);
        }

        // 관련 프로젝트와 요소들을 함께 로드
        $rfp->load('project', 'elements');

        return response()->json([
            'message' => 'RFP 상세 정보를 성공적으로 불러왔습니다.',
            'rfp' => $rfp,
        ], 200);
    }

    /**
     * RFP 임시저장 (POST /api/rfps/draft)
     *
     * @OA\Post(
     *     path="/api/rfps/draft",
     *     tags={"RFP Management"},
     *     summary="RFP 임시저장",
     *     description="RFP 작성 중 임시저장합니다. 불완전한 데이터도 저장 가능합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="project_name", type="string", example="2024 신년 행사"),
     *             @OA\Property(property="start_datetime", type="string", format="date-time", example="2024-02-01T09:00:00Z"),
     *             @OA\Property(property="end_datetime", type="string", format="date-time", example="2024-02-01T18:00:00Z"),
     *             @OA\Property(property="client_name", type="string", example="ABC 회사"),
     *             @OA\Property(property="location", type="string", example="서울시 강남구 코엑스"),
     *             @OA\Property(property="issue_type", type="string", enum={"integrated","separated_by_element","separated_by_group"}, example="integrated"),
     *             @OA\Property(property="elements", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="임시저장 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP가 임시저장되었습니다."),
     *             @OA\Property(property="rfp", type="object")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDraft(StoreDraftRfpRequest $request)
    {
        try {
            $user = Auth::user();

            // 권한 체크
            if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
                return ApiResponse::forbidden('RFP 생성 권한이 없습니다.');
            }

            if ($user->user_type === 'agency_member') {
                $agencyId = $user->agency_members->first()->agency_id ?? null;
                if (!$agencyId) {
                    return ApiResponse::forbidden('소속된 대행사 정보를 찾을 수 없습니다.');
                }
            }

            $result = $this->rfpService->saveDraft($request->validated());

            return ApiResponse::created('RFP가 임시저장되었습니다.', [
                'rfp' => $result['rfp'],
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('RFP 임시저장 중 오류가 발생했습니다.', $e->getMessage());
        }
    }

    /**
     * RFP 임시저장 수정 (PUT /api/rfps/{rfp}/draft)
     *
     * @OA\Put(
     *     path="/api/rfps/{rfp}/draft",
     *     tags={"RFP Management"},
     *     summary="RFP 임시저장 수정",
     *     description="기존 임시저장된 RFP를 수정합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="임시저장 수정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 임시저장이 수정되었습니다."),
     *             @OA\Property(property="rfp", type="object")
     *         )
     *     )
     * )
     *
     * @param  UpdateDraftRfpRequest  $request
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDraft(UpdateDraftRfpRequest $request, Rfp $rfp)
    {
        try {
            $user = Auth::user();

            // 권한 체크
            if ($rfp->created_by_user_id !== $user->id) {
                return ApiResponse::forbidden('이 RFP를 수정할 권한이 없습니다.');
            }

            // draft 상태인지 확인
            if ($rfp->status !== 'draft') {
                return ApiResponse::error('임시저장 상태의 RFP만 수정할 수 있습니다.');
            }

            $result = $this->rfpService->updateDraft($rfp, $request->validated());

            return ApiResponse::success('RFP 임시저장이 수정되었습니다.', [
                'rfp' => $result['rfp'],
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('RFP 수정 중 오류가 발생했습니다.', $e->getMessage());
        }
    }

    /**
     * RFP 임시저장 발행 (POST /api/rfps/{rfp}/publish)
     *
     * @OA\Post(
     *     path="/api/rfps/{rfp}/publish",
     *     tags={"RFP Management"},
     *     summary="RFP 임시저장 발행",
     *     description="임시저장된 RFP를 검토하고 발행합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="발행 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 발행되었습니다."),
     *             @OA\Property(property="rfp", type="object")
     *         )
     *     )
     * )
     *
     * @param  PublishDraftRfpRequest  $request
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function publishDraft(PublishDraftRfpRequest $request, Rfp $rfp)
    {
        try {
            $user = Auth::user();

            // 권한 체크
            if ($rfp->created_by_user_id !== $user->id) {
                return ApiResponse::forbidden('이 RFP를 발행할 권한이 없습니다.');
            }

            // draft 상태인지 확인
            if ($rfp->status !== 'draft') {
                return ApiResponse::error('임시저장 상태의 RFP만 발행할 수 있습니다.');
            }

            $result = $this->rfpService->publishDraft($rfp, $request->validated());

            return ApiResponse::success('RFP가 성공적으로 발행되었습니다.', [
                'rfp' => $result['rfp'],
            ]);

        } catch (\InvalidArgumentException $e) {
            return ApiResponse::validationError($e->getMessage());
        } catch (\Exception $e) {
            return ApiResponse::serverError('RFP 발행 중 오류가 발생했습니다.', $e->getMessage());
        }
    }

    /**
     * 임시저장 RFP 목록 조회 (GET /api/rfps/drafts)
     *
     * @OA\Get(
     *     path="/api/rfps/drafts",
     *     tags={"RFP Management"},
     *     summary="임시저장 RFP 목록 조회",
     *     description="사용자의 임시저장된 RFP 목록을 조회합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="임시저장 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="임시저장 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="drafts", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrafts(Request $request)
    {
        $user = Auth::user();

        // 권한 체크
        if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
            return response()->json(['message' => 'RFP 조회 권한이 없습니다.'], 403);
        }

        $drafts = Rfp::where('created_by_user_id', $user->id)
                     ->where('current_status', 'draft')
                     ->with('project', 'elements')
                     ->orderBy('updated_at', 'desc')
                     ->get();

        return response()->json([
            'message' => '임시저장 목록을 성공적으로 불러왔습니다.',
            'drafts' => $drafts,
        ], 200);
    }

    /**
     * 특정 임시저장 RFP 조회 (GET /api/rfps/{rfp}/draft)
     *
     * @OA\Get(
     *     path="/api/rfps/{rfp}/draft",
     *     tags={"RFP Management"},
     *     summary="특정 임시저장 RFP 조회",
     *     description="특정 임시저장된 RFP의 상세 정보를 조회합니다.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="rfp",
     *         in="path",
     *         required=true,
     *         description="RFP ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="임시저장 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="임시저장을 성공적으로 불러왔습니다."),
     *             @OA\Property(property="draft", type="object")
     *         )
     *     )
     * )
     *
     * @param  \App\Models\Rfp  $rfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDraft(Rfp $rfp)
    {
        $user = Auth::user();

        // 권한 체크
        if ($rfp->created_by_user_id !== $user->id) {
            return response()->json(['message' => '이 임시저장에 접근할 권한이 없습니다.'], 403);
        }

        // draft 상태인지 확인
        if ($rfp->current_status !== 'draft') {
            return response()->json(['message' => '임시저장 상태가 아닙니다.'], 400);
        }

        $rfp->load(['project.mainAgencyContactUser', 'project.subAgencyContactUser', 'elements.elementDefinition']);

        // RfpElement 데이터를 프론트엔드가 기대하는 rfp_elements 형식으로 변환
        $rfpElements = $rfp->elements->map(function ($element, $index) {
            // 같은 ElementDefinition의 여러 인스턴스를 구분하기 위해 인덱스 기반 ID 생성
            $instanceId = $element->element_definition_id . '-' . ($index + 1);
            
            return [
                'element_definition_id' => $element->element_definition_id,
                'element_id' => $instanceId, // 프론트엔드에서 사용하는 인스턴스 ID
                'element_type' => $element->element_type ?? 'unknown',
                'specifications' => $element->specifications ?? [],
                'special_requirements' => $element->special_requirements,
                'allocated_budget' => $element->allocated_budget,
                'down_payment_ratio' => $element->prepayment_ratio ? ($element->prepayment_ratio * 100) : null, // 비율을 퍼센트로 변환
                'down_payment_date' => $element->prepayment_due_date,
                'final_payment_date' => $element->balance_due_date,
            ];
        });

        // draft 응답에 변환된 rfp_elements 포함
        $draftData = $rfp->toArray();
        $draftData['rfp_elements'] = $rfpElements;

        return response()->json([
            'message' => '임시저장을 성공적으로 불러왔습니다.',
            'draft' => $draftData,
        ], 200);
    }

    /**
     * venue_type을 is_indoor boolean으로 변환하는 헬퍼 메서드
     */
    private function venueTypeToIsIndoor($venueType)
    {
        switch ($venueType) {
            case 'indoor':
                return true;
            case 'outdoor':
                return false;
            case 'both':
            default:
                return true; // 기본값은 실내
        }
    }
}
