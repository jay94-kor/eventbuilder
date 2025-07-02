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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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

            // RFP Elements 유효성 검사
            'elements' => 'required|array|min:1',
            'elements.*.element_type' => 'required|string|max:255',
            'elements.*.details' => 'nullable|array',
            'elements.*.allocated_budget' => 'nullable|numeric|min:0',
            'elements.*.prepayment_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.prepayment_due_date' => 'nullable|date',
            'elements.*.balance_ratio' => 'nullable|numeric|min:0|max:1',
            'elements.*.balance_due_date' => 'nullable|date',
            'elements.*.quantity' => 'nullable|integer|min:1',
            'elements.*.dynamic_specs' => 'nullable|array',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_name.required' => '프로젝트명은 필수입니다.',
            'start_datetime.required' => '시작일시는 필수입니다.',
            'end_datetime.required' => '종료일시는 필수입니다.',
            'end_datetime.after_or_equal' => '종료일시는 시작일시보다 늦어야 합니다.',
            'is_indoor.required' => '실내/실외 구분은 필수입니다.',
            'location.required' => '장소는 필수입니다.',
            'issue_type.required' => '발주 유형은 필수입니다.',
            'issue_type.in' => '발주 유형은 통합발주, 요소별 분리발주, 그룹별 분리발주 중 하나여야 합니다.',
            'closing_at.required' => '마감일시는 필수입니다.',
            'closing_at.after' => '마감일시는 현재 시간보다 늦어야 합니다.',
            'elements.required' => '요소는 최소 1개 이상 필요합니다.',
            'elements.min' => '요소는 최소 1개 이상 필요합니다.',
            'elements.*.element_type.required' => '요소 타입은 필수입니다.',
        ];
    }
}