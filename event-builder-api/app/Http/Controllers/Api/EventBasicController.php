<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventBasic;
use Illuminate\Http\Request;
use Carbon\Carbon; // Carbon 클래스 추가
use App\Http\Requests\StoreEventBasicRequest;
use App\Http\Requests\UpdateEventBasicRequest;
use Illuminate\Http\JsonResponse;

class EventBasicController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/event-basics",
     *     summary="모든 이벤트 기본 정보 조회",
     *     tags={"EventBasic"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EventBasic")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $eventBasics = EventBasic::all();
        return response()->json($eventBasics);
    }

    /**
     * @OA\Post(
     *     path="/api/event-basics",
     *     summary="새로운 이벤트 기본 정보 생성",
     *     tags={"EventBasic"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreEventBasicRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="성공적으로 생성됨",
     *         @OA\JsonContent(ref="#/components/schemas/EventBasic")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="잘못된 요청"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 오류"
     *     )
     * )
     */
    public function store(StoreEventBasicRequest $request): JsonResponse
    {
        $data = $request->validated();

        // 날짜 불확실성 처리 및 자동 계산
        $this->processEventDates($data);

        $eventBasic = EventBasic::create($data);
        return response()->json($eventBasic, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/event-basics/{id}",
     *     summary="특정 이벤트 기본 정보 조회",
     *     tags={"EventBasic"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="이벤트 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(ref="#/components/schemas/EventBasic")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="이벤트를 찾을 수 없음"
     *     )
     * )
     */
    public function show(EventBasic $eventBasic): JsonResponse
    {
        return response()->json($eventBasic);
    }

    /**
     * @OA\Put(
     *     path="/api/event-basics/{id}",
     *     summary="특정 이벤트 기본 정보 업데이트",
     *     tags={"EventBasic"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="이벤트 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEventBasicRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(ref="#/components/schemas/EventBasic")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="잘못된 요청"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="이벤트를 찾을 수 없음"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="유효성 검사 오류"
     *     )
     * )
     */
    public function update(UpdateEventBasicRequest $request, EventBasic $eventBasic): JsonResponse
    {
        $data = $request->validated();

        // 날짜 불확실성 처리 및 자동 계산
        $this->processEventDates($data);

        $eventBasic->update($data);
        return response()->json($eventBasic);
    }

    /**
     * @OA\Delete(
     *     path="/api/event-basics/{id}",
     *     summary="특정 이벤트 기본 정보 삭제",
     *     tags={"EventBasic"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="이벤트 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="성공적으로 삭제됨"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="인증되지 않음"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="이벤트를 찾을 수 없음"
     *     )
     * )
     */
    public function destroy(EventBasic $eventBasic): JsonResponse
    {
        $eventBasic->delete();
        return response()->json(null, 204);
    }
    /**
     * 날짜 관련 필드 (기간, 세팅/철수)를 처리합니다.
     * @param array $data
     * @return void
     */
    private function processEventDates(array &$data): void
    {
        // event_duration_days 자동 계산
        if (isset($data['event_start_date_range_min']) && isset($data['event_end_date_range_max'])) {
            try {
                $startDate = \Carbon\Carbon::parse($data['event_start_date_range_min']);
                $endDate = \Carbon\Carbon::parse($data['event_end_date_range_max']);
                $data['event_duration_days'] = $startDate->diffInDays($endDate) + 1;
            } catch (\Exception $e) {
                $data['event_duration_days'] = null; // 날짜 파싱 오류 시 null
            }
        } else {
            $data['event_duration_days'] = null;
        }

        // setup_start_date, teardown_end_date 자동 산출 (최소/최대 날짜 기준)
        if (isset($data['event_start_date_range_min']) && !isset($data['setup_start_date'])) {
            try {
                $startDate = \Carbon\Carbon::parse($data['event_start_date_range_min']);
                // 예시: 행사 시작일 3일 전으로 설정
                $data['setup_start_date'] = $startDate->subDays(3)->toDateString();
            } catch (\Exception $e) {
                $data['setup_start_date'] = null;
            }
        }

        if (isset($data['event_end_date_range_max']) && !isset($data['teardown_end_date'])) {
            try {
                $endDate = \Carbon\Carbon::parse($data['event_end_date_range_max']);
                // 예시: 행사 종료일 1일 후로 설정
                $data['teardown_end_date'] = $endDate->addDays(1)->toDateString();
            } catch (\Exception $e) {
                $data['teardown_end_date'] = null;
            }
        }
    }
}
