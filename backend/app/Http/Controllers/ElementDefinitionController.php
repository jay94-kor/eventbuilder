<?php

namespace App\Http\Controllers;

use App\Models\ElementDefinition; // ElementDefinition 모델 추가
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 인증된 사용자 확인용
use Illuminate\Validation\Rule; // Rule 클래스 추가

class ElementDefinitionController extends Controller
{
    /**
     * 모든 RFP 요소 정의 목록을 조회 (GET /api/element-definitions)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 이 API는 RFP 생성 시 모든 사용자가 접근 가능하게 할 수 있습니다.
        // 필요하다면 특정 user_type (예: admin 또는 agency_member)만 접근 가능하도록 권한 검사를 추가할 수 있습니다.
        // if (Auth::user()->user_type !== 'admin' && Auth::user()->user_type !== 'agency_member') {
        //     return response()->json(['message' => '접근 권한이 없습니다.'], 403);
        // }

        $elements = ElementDefinition::orderBy('display_name')->get(); // display_name 기준으로 정렬하여 반환

        return response()->json([
            'message' => 'RFP 요소 정의 목록을 성공적으로 불러왔습니다.',
            'elements' => $elements,
        ], 200);
    }

    /**
     * 새로운 RFP 요소 정의 생성 (POST /api/element-definitions)
     * (관리자 전용)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->user_type !== 'admin') {
            return response()->json(['message' => 'RFP 요소 정의를 생성할 권한이 없습니다.'], 403);
        }

        $request->validate([
            'element_type' => ['required', 'string', 'max:255', 'unique:element_definitions,element_type'],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'input_schema' => 'nullable|json', // JSON 형식인지 검증
            'default_details_template' => 'nullable|json',
            'recommended_elements' => 'nullable|json', // JSON 배열 형태인지 검증
        ]);

        try {
            $elementDefinition = ElementDefinition::create([
                'element_type' => $request->input('element_type'),
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description'),
                'input_schema' => json_decode($request->input('input_schema') ?? '{}'), // JSON 문자열을 객체로 변환
                'default_details_template' => json_decode($request->input('default_details_template') ?? '{}'),
                'recommended_elements' => json_decode($request->input('recommended_elements') ?? '[]'),
            ]);

            return response()->json([
                'message' => 'RFP 요소 정의가 성공적으로 생성되었습니다.',
                'elementDefinition' => $elementDefinition,
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'RFP 요소 정의 생성 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 특정 RFP 요소 정의 수정 (PUT /api/element-definitions/{elementDefinition})
     * (관리자 전용)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ElementDefinition  $elementDefinition
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, ElementDefinition $elementDefinition)
    {
        $user = Auth::user();
        if ($user->user_type !== 'admin') {
            return response()->json(['message' => 'RFP 요소 정의를 수정할 권한이 없습니다.'], 403);
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'input_schema' => 'nullable|json',
            'default_details_template' => 'nullable|json',
            'recommended_elements' => 'nullable|json',
        ]);

        try {
            $elementDefinition->update([
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description'),
                'input_schema' => json_decode($request->input('input_schema') ?? '{}'),
                'default_details_template' => json_decode($request->input('default_details_template') ?? '{}'),
                'recommended_elements' => json_decode($request->input('recommended_elements') ?? '[]'),
            ]);

            return response()->json([
                'message' => 'RFP 요소 정의가 성공적으로 수정되었습니다.',
                'elementDefinition' => $elementDefinition,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'RFP 요소 정의 수정 중 오류가 발생했습니다.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 특정 RFP 요소 정의 삭제 (DELETE /api/element-definitions/{elementDefinition})
     * (관리자 전용)
     *
     * @param  \App\Models\ElementDefinition  $elementDefinition
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ElementDefinition $elementDefinition)
    {
        $user = Auth::user();
        if ($user->user_type !== 'admin') {
            return response()->json(['message' => 'RFP 요소 정의를 삭제할 권한이 없습니다.'], 403);
        }

        // 주의: 이 요소가 사용 중인 RFP_Elements가 있다면 삭제되지 않거나 오류 발생.
        // 참조 무결성 제약 조건이 DB에 설정되어 있음.
        // 또는 CASCADE DELETE 설정이나 소프트 삭제(soft delete) 고려.
        try {
            $elementDefinition->delete();
            return response()->json([
                'message' => 'RFP 요소 정의가 성공적으로 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'RFP 요소 정의 삭제 중 오류가 발생했습니다. (사용 중인 RFP가 있을 수 있습니다.)',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
