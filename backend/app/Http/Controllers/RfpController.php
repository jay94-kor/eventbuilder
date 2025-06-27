<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 트랜잭션 사용을 위해 DB 퍼사드 추가
use Illuminate\Support\Facades\Auth; // 현재 인증된 사용자 정보 가져오기 위해 Auth 퍼사드 추가
use App\Models\Project; // Project 모델 사용
use App\Models\Rfp;     // Rfp 모델 사용
use App\Models\RfpElement; // RfpElement 모델 사용

class RfpController extends Controller
{
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
     *             @OA\Property(property="main_agency_contact_user_id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *             @OA\Property(property="sub_agency_contact_user_id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *             @OA\Property(property="is_client_name_public", type="boolean", example=true),
     *             @OA\Property(property="is_budget_public", type="boolean", example=false),
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. 요청 데이터 유효성 검사
        $validatedData = $request->validate([
            // Project 기본 정보 유효성 검사
            'project_name' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'preparation_start_datetime' => 'nullable|date',
            '철수_end_datetime' => 'nullable|date|after_or_equal:preparation_start_datetime',
            'client_name' => 'nullable|string|max:255',
            'client_contact_person' => 'nullable|string|max:255',
            'client_contact_number' => 'nullable|string|max:20',
            'main_agency_contact_user_id' => 'nullable|string|exists:users,id', // 추가: 주담당자 ID
            'sub_agency_contact_user_id' => 'nullable|string|exists:users,id',  // 추가: 부담당자 ID
            'is_client_name_public' => 'nullable|boolean',     // 추가: 클라이언트명 공개 여부
            'is_budget_public' => 'nullable|boolean',          // 추가: 예산 공개 여부
            'is_indoor' => 'required|boolean',
            'location' => 'required|string|max:255',
            'budget_including_vat' => 'nullable|numeric|min:0',

            // RFP 관련 정보 유효성 검사
            'issue_type' => 'required|in:integrated,separated_by_element,separated_by_group',
            'rfp_description' => 'nullable|string',
            'closing_at' => 'required|date|after:now',

            // RFP 요소 (rfp_elements) 유효성 검사
            'elements' => 'required|array|min:1', // 최소 하나의 요소는 있어야 함
            'elements.*.element_type' => 'required|string|exists:element_definitions,element_type', // element_type은 정의된 것만 허용
            'elements.*.details' => 'nullable|array', // JSONB 데이터는 배열 형태로 받음
            'elements.*.allocated_budget' => 'nullable|numeric|min:0',
            'elements.*.prepayment_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.prepayment_due_date' => 'nullable|date|after_or_equal:today',
            'elements.*.balance_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.balance_due_date' => 'nullable|date|after_or_equal:today',
            // 'elements.*.parent_rfp_element_id'는 내부 로직으로 처리하므로 유효성 검사에서 제외
        ]);

        // 현재 인증된 사용자 정보 (사내 행사 담당자)
        $user = Auth::user();

        // 담당자 유효성 검사
        if (isset($validatedData['main_agency_contact_user_id'])) {
            $mainContact = \App\Models\User::find($validatedData['main_agency_contact_user_id']);
            if (!$mainContact || $mainContact->user_type !== 'agency_member') {
                return response()->json(['message' => '주담당자는 대행사 멤버여야 합니다.'], 422);
            }
        }

        if (isset($validatedData['sub_agency_contact_user_id'])) {
            $subContact = \App\Models\User::find($validatedData['sub_agency_contact_user_id']);
            if (!$subContact || $subContact->user_type !== 'agency_member') {
                return response()->json(['message' => '부담당자는 대행사 멤버여야 합니다.'], 422);
            }
        }

        // 데이터베이스 트랜잭션 시작
        // 프로젝트, RFP, RFP 요소 생성이 모두 성공해야 하므로 트랜잭션으로 묶습니다.
        DB::beginTransaction();

        try {
            // 2. Project 생성
            $project = Project::create([
                'project_name' => $validatedData['project_name'],
                'start_datetime' => $validatedData['start_datetime'],
                'end_datetime' => $validatedData['end_datetime'],
                'preparation_start_datetime' => $validatedData['preparation_start_datetime'] ?? null,
                '철수_end_datetime' => $validatedData['철수_end_datetime'] ?? null,
                'client_name' => $validatedData['client_name'] ?? null,
                'client_contact_person' => $validatedData['client_contact_person'] ?? null,
                'client_contact_number' => $validatedData['client_contact_number'] ?? null,
                'main_agency_contact_user_id' => $validatedData['main_agency_contact_user_id'] ?? $user->id, // 지정된 주담당자 또는 현재 사용자
                'sub_agency_contact_user_id' => $validatedData['sub_agency_contact_user_id'] ?? null, // 지정된 부담당자
                'is_indoor' => $validatedData['is_indoor'],
                'location' => $validatedData['location'],
                'budget_including_vat' => $validatedData['budget_including_vat'] ?? null,
                'agency_id' => $user->agency_members->first()->agency_id ?? null, // 현재 사용자의 소속 대행사 ID (복수 소속 시 처리 로직 필요)
            ]);

            // 3. RFP 생성 (클라이언트 정보 공개/비공개 설정 포함)
            $rfp = Rfp::create([
                'project_id' => $project->id,
                'current_status' => 'draft', // RFP 생성 시 초기 상태는 'draft' (초안)
                'created_by_user_id' => $user->id,
                'agency_id' => $project->agency_id,
                'issue_type' => $validatedData['issue_type'],
                'rfp_description' => $validatedData['rfp_description'] ?? null,
                'closing_at' => $validatedData['closing_at'],
                'is_client_name_public' => $validatedData['is_client_name_public'] ?? true,  // 기본값: 공개
                'is_budget_public' => $validatedData['is_budget_public'] ?? false,         // 기본값: 비공개
                // published_at은 결재 승인 후 공고 시점에 설정
            ]);

            // 4. RFP 요소 (RfpElement) 생성
            $elementMap = []; // parent_rfp_element_id 처리를 위한 맵
            foreach ($validatedData['elements'] as $elementData) {
                // 'parent_rfp_element_id'는 입력에서 직접 받지 않고, issue_type에 따라 내부 로직으로 설정합니다.
                // 지금은 단순화를 위해 NULL로 두거나, 나중에 group_id 등을 처리할 때 활용합니다.
                $rfpElement = RfpElement::create([
                    'rfp_id' => $rfp->id,
                    'element_type' => $elementData['element_type'],
                    'details' => $elementData['details'] ?? [], // JSONB 필드
                    'allocated_budget' => $elementData['allocated_budget'] ?? null,
                    'prepayment_ratio' => $elementData['prepayment_ratio'] ?? null,
                    'prepayment_due_date' => $elementData['prepayment_due_date'] ?? null,
                    'balance_ratio' => $elementData['balance_ratio'] ?? null,
                    'balance_due_date' => $elementData['balance_due_date'] ?? null,
                    // 'parent_rfp_element_id'는 'separated_by_group' 발주 시에만 의미 있음.
                    // 현재는 단순화를 위해 기본적으로 NULL로 처리.
                ]);
                $elementMap[$rfpElement->id] = $rfpElement; // 나중에 parent_rfp_element_id 설정 시 사용 가능
            }

            // 트랜잭션 커밋
            DB::commit();

            return response()->json([
                'message' => 'RFP가 성공적으로 생성되었습니다.',
                'rfp' => $rfp->load('project', 'elements'), // 생성된 RFP 정보와 관련 프로젝트, 요소들을 함께 반환
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // 예외 발생 시 트랜잭션 롤백
            DB::rollBack();
            return response()->json([
                'message' => 'RFP 생성 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null, // 디버그 모드에서만 스택 트레이스 표시
            ], 500); // 500 Internal Server Error
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
     * RFP 수정 (PUT /api/rfps/{rfp})
     *
     * @OA\Put(
     *     path="/api/rfps/{rfp}",
     *     tags={"RFP Management"},
     *     summary="RFP 수정",
     *     description="RFP가 '초안', '결재 대기', '반려' 상태일 때만 수정 가능합니다.",
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
     *             @OA\Property(property="project_name", type="string", example="2024 신년 행사 (수정)"),
     *             @OA\Property(property="start_datetime", type="string", format="date-time"),
     *             @OA\Property(property="end_datetime", type="string", format="date-time"),
     *             @OA\Property(property="rfp_description", type="string", example="수정된 RFP 설명"),
     *             @OA\Property(property="closing_at", type="string", format="date-time"),
     *             @OA\Property(property="elements", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="string", description="기존 요소 ID (수정 시)"),
     *                     @OA\Property(property="element_type", type="string"),
     *                     @OA\Property(property="details", type="object"),
     *                     @OA\Property(property="allocated_budget", type="number")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RFP 수정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP가 성공적으로 수정되었습니다."),
     *             @OA\Property(property="rfp", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=409, description="수정 불가능한 상태"),
     *     @OA\Response(response=422, description="유효성 검사 실패")
     * )
     */
    public function update(Request $request, Rfp $rfp)
    {
        $user = Auth::user();

        // 권한 확인
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => 'RFP 수정 권한이 없습니다.'], 403);
        }

        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $rfp->agency_id) {
                return response()->json(['message' => '이 RFP에 대한 권한이 없습니다.'], 403);
            }
        }

        // 상태 확인: 초안, 결재 대기, 반려 상태에서만 수정 가능
        $editableStatuses = ['draft', 'approval_pending', 'rejected'];
        if (!in_array($rfp->current_status, $editableStatuses)) {
            return response()->json([
                'message' => "RFP가 '{$rfp->current_status}' 상태일 때는 수정할 수 없습니다. 수정 가능한 상태: " . implode(', ', $editableStatuses)
            ], 409);
        }

        // 유효성 검사
        $validatedData = $request->validate([
            'project_name' => 'sometimes|string|max:255',
            'start_datetime' => 'sometimes|date',
            'end_datetime' => 'sometimes|date|after_or_equal:start_datetime',
            'preparation_start_datetime' => 'nullable|date',
            '철수_end_datetime' => 'nullable|date|after_or_equal:preparation_start_datetime',
            'client_name' => 'nullable|string|max:255',
            'client_contact_person' => 'nullable|string|max:255',
            'client_contact_number' => 'nullable|string|max:20',
            'main_agency_contact_user_id' => 'nullable|string|exists:users,id',
            'sub_agency_contact_user_id' => 'nullable|string|exists:users,id',
            'is_client_name_public' => 'nullable|boolean',
            'is_budget_public' => 'nullable|boolean',
            'is_indoor' => 'sometimes|boolean',
            'location' => 'sometimes|string|max:255',
            'budget_including_vat' => 'nullable|numeric|min:0',
            'issue_type' => 'sometimes|in:integrated,separated_by_element,separated_by_group',
            'rfp_description' => 'nullable|string',
            'closing_at' => 'sometimes|date|after:now',
            'elements' => 'sometimes|array|min:1',
            'elements.*.id' => 'nullable|string|exists:rfp_elements,id',
            'elements.*.element_type' => 'required|string|exists:element_definitions,element_type',
            'elements.*.details' => 'nullable|array',
            'elements.*.allocated_budget' => 'nullable|numeric|min:0',
            'elements.*.prepayment_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.prepayment_due_date' => 'nullable|date|after_or_equal:today',
            'elements.*.balance_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.balance_due_date' => 'nullable|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
            // 프로젝트 정보 업데이트
            if (isset($validatedData['project_name']) || isset($validatedData['start_datetime']) || 
                isset($validatedData['end_datetime']) || isset($validatedData['location']) || 
                isset($validatedData['budget_including_vat'])) {
                
                $projectUpdateData = [];
                $projectFields = ['project_name', 'start_datetime', 'end_datetime', 'preparation_start_datetime', 
                                '철수_end_datetime', 'client_name', 'client_contact_person', 'client_contact_number',
                                'main_agency_contact_user_id', 'sub_agency_contact_user_id', 'is_indoor', 
                                'location', 'budget_including_vat'];
                
                foreach ($projectFields as $field) {
                    if (isset($validatedData[$field])) {
                        $projectUpdateData[$field] = $validatedData[$field];
                    }
                }
                
                if (!empty($projectUpdateData)) {
                    $rfp->project->update($projectUpdateData);
                }
            }

            // RFP 정보 업데이트
            $rfpUpdateData = [];
            $rfpFields = ['issue_type', 'rfp_description', 'closing_at', 'is_client_name_public', 'is_budget_public'];
            
            foreach ($rfpFields as $field) {
                if (isset($validatedData[$field])) {
                    $rfpUpdateData[$field] = $validatedData[$field];
                }
            }
            
            // 수정 시 상태를 초안으로 되돌림 (재결재 필요)
            if ($rfp->current_status === 'rejected' || $rfp->current_status === 'approval_pending') {
                $rfpUpdateData['current_status'] = 'draft';
            }
            
            if (!empty($rfpUpdateData)) {
                $rfp->update($rfpUpdateData);
            }

            // RFP 요소 업데이트
            if (isset($validatedData['elements'])) {
                // 기존 요소들 삭제 (새로 생성)
                $rfp->elements()->delete();
                
                // 새 요소들 생성
                foreach ($validatedData['elements'] as $elementData) {
                    RfpElement::create([
                        'rfp_id' => $rfp->id,
                        'element_type' => $elementData['element_type'],
                        'details' => $elementData['details'] ?? [],
                        'allocated_budget' => $elementData['allocated_budget'] ?? null,
                        'prepayment_ratio' => $elementData['prepayment_ratio'] ?? null,
                        'prepayment_due_date' => $elementData['prepayment_due_date'] ?? null,
                        'balance_ratio' => $elementData['balance_ratio'] ?? null,
                        'balance_due_date' => $elementData['balance_due_date'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $rfp->load('project', 'elements');

            return response()->json([
                'message' => 'RFP가 성공적으로 수정되었습니다.',
                'rfp' => $rfp,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'RFP 수정 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * RFP 재결재 요청 (POST /api/rfps/{rfp}/resubmit)
     *
     * @OA\Post(
     *     path="/api/rfps/{rfp}/resubmit",
     *     tags={"RFP Management"},
     *     summary="RFP 재결재 요청",
     *     description="수정된 RFP를 다시 결재 요청합니다.",
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
     *         description="재결재 요청 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="RFP 재결재 요청이 성공적으로 제출되었습니다."),
     *             @OA\Property(property="rfp_status", type="string", example="approval_pending")
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=409, description="재결재 요청 불가능한 상태")
     * )
     */
    public function resubmit(Rfp $rfp)
    {
        $user = Auth::user();

        // 권한 확인
        if ($user->user_type !== 'admin' && $user->user_type !== 'agency_member') {
            return response()->json(['message' => 'RFP 재결재 요청 권한이 없습니다.'], 403);
        }

        if ($user->user_type === 'agency_member') {
            $userAgencyId = $user->agency_members->first()->agency_id ?? null;
            if ($userAgencyId !== $rfp->agency_id) {
                return response()->json(['message' => '이 RFP에 대한 권한이 없습니다.'], 403);
            }
        }

        // 상태 확인: 초안 또는 반려 상태에서만 재결재 요청 가능
        $resubmittableStatuses = ['draft', 'rejected'];
        if (!in_array($rfp->current_status, $resubmittableStatuses)) {
            return response()->json([
                'message' => "RFP가 '{$rfp->current_status}' 상태일 때는 재결재 요청할 수 없습니다."
            ], 409);
        }

        $rfp->update([
            'current_status' => 'approval_pending',
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'RFP 재결재 요청이 성공적으로 제출되었습니다.',
            'rfp_status' => $rfp->current_status
        ]);
    }
}
