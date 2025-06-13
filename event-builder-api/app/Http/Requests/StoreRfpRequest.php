<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRfpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 인증된 사용자는 RFP 생성 가능
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'event_date' => 'nullable|date|after_or_equal:today',
            'expected_attendees' => 'nullable|integer|min:1',
            'total_budget' => 'nullable|integer|min:0',
            'is_total_budget_undecided' => 'boolean',
            'description' => 'nullable|string',
            'selections' => 'required|array|min:1',
            'selections.*.feature_id' => 'required|integer|exists:features,id',
            'selections.*.details' => 'nullable|array',
            'selections.*.allocated_budget' => 'nullable|integer|min:0',
            'selections.*.is_budget_undecided' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'RFP 제목은 필수입니다.',
            'title.max' => 'RFP 제목은 255자를 초과할 수 없습니다.',
            'event_date.date' => '올바른 날짜 형식이 아닙니다.',
            'event_date.after_or_equal' => '행사 날짜는 오늘 이후여야 합니다.',
            'expected_attendees.integer' => '예상 참가자 수는 정수여야 합니다.',
            'expected_attendees.min' => '예상 참가자 수는 1명 이상이어야 합니다.',
            'total_budget.integer' => '총 예산은 정수여야 합니다.',
            'total_budget.min' => '총 예산은 0 이상이어야 합니다.',
            'is_total_budget_undecided.boolean' => '총 예산 미정 여부는 참/거짓이어야 합니다.',
            'description.string' => '설명은 문자열이어야 합니다.',
            'selections.required' => '최소 하나 이상의 요소를 선택해야 합니다.',
            'selections.array' => '선택 항목은 배열 형태여야 합니다.',
            'selections.min' => '최소 하나 이상의 요소를 선택해야 합니다.',
            'selections.*.feature_id.required' => '각 선택 항목에는 feature_id가 필요합니다.',
            'selections.*.feature_id.integer' => 'feature_id는 정수여야 합니다.',
            'selections.*.feature_id.exists' => '존재하지 않는 feature입니다.',
            'selections.*.details.array' => '상세 정보는 배열 형태여야 합니다.',
            'selections.*.allocated_budget.integer' => '할당된 예산은 정수여야 합니다.',
            'selections.*.allocated_budget.min' => '할당된 예산은 0 이상이어야 합니다.',
            'selections.*.is_budget_undecided.boolean' => '예산 미정 여부는 참/거짓이어야 합니다.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => '제목',
            'event_date' => '행사 날짜',
            'expected_attendees' => '예상 참가자 수',
            'total_budget' => '총 예산',
            'is_total_budget_undecided' => '총 예산 미정 여부',
            'description' => '설명',
            'selections' => '선택 항목',
            'selections.*.feature_id' => 'Feature ID',
            'selections.*.details' => '상세 정보',
            'selections.*.allocated_budget' => '할당된 예산',
            'selections.*.is_budget_undecided' => '예산 미정 여부',
        ];
    }
}
