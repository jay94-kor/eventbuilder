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
                'main_agency_contact_user_id' => $user->id, // 현재 로그인한 사용자를 정 담당자로 설정
                'sub_agency_contact_user_id' => null, // 부 담당자는 나중에 추가하거나, 요청에서 받을 수 있음
                'is_indoor' => $validatedData['is_indoor'],
                'location' => $validatedData['location'],
                'budget_including_vat' => $validatedData['budget_including_vat'] ?? null,
                'agency_id' => $user->agency_members->first()->agency_id ?? null, // 현재 사용자의 소속 대행사 ID (복수 소속 시 처리 로직 필요)
            ]);

            // 3. RFP 생성
            $rfp = Rfp::create([
                'project_id' => $project->id,
                'current_status' => 'draft', // RFP 생성 시 초기 상태는 'draft' (초안)
                'created_by_user_id' => $user->id,
                'agency_id' => $project->agency_id,
                'issue_type' => $validatedData['issue_type'],
                'rfp_description' => $validatedData['rfp_description'] ?? null,
                'closing_at' => $validatedData['closing_at'],
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
}
