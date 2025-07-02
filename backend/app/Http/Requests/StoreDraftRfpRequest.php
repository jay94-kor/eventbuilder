<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDraftRfpRequest extends FormRequest
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
            // Project 기본 정보 유효성 검사 (임시저장이므로 대부분 nullable)
            'project_name' => 'nullable|string|max:255',
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
            'preparation_start_datetime' => 'nullable|date',
            '철수_end_datetime' => 'nullable|date|after_or_equal:preparation_start_datetime',
            'client_name' => 'nullable|string|max:255',
            'client_contact_person' => 'nullable|string|max:255',
            'client_contact_number' => 'nullable|string|max:20',
            'is_indoor' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'budget_including_vat' => 'nullable|numeric|min:0',

            // RFP 관련 정보 유효성 검사
            'issue_type' => 'nullable|in:integrated,separated_by_element,separated_by_group',
            'rfp_description' => 'nullable|string',
            'closing_at' => 'nullable|date',

            // RFP Elements 유효성 검사
            'elements' => 'nullable|array',
            'elements.*.element_type' => 'required_if:elements,array|string|max:255',
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
            'end_datetime.after_or_equal' => '종료일시는 시작일시보다 늦어야 합니다.',
            'issue_type.in' => '발주 유형은 통합발주, 요소별 분리발주, 그룹별 분리발주 중 하나여야 합니다.',
            'elements.*.element_type.required_if' => '요소가 있는 경우 요소 타입은 필수입니다.',
        ];
    }
}