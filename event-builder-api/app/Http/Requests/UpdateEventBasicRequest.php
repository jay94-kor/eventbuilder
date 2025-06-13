<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventBasicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 인증된 사용자만 요청 가능하도록 설정
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'event_title' => ['required', 'string', 'max:255'],
            'event_location' => ['required', 'string', 'max:255'],
            'venue_type' => ['required', 'string', 'in:실내,실외,혼합'], // 예시: 실제 enum 또는 validation rule 필요
            'zones' => ['nullable', 'array'],
            'zones.*.name' => ['required_with:zones', 'string', 'max:255'],
            'zones.*.type' => ['required_with:zones', 'string', 'in:실내,실외'], // 예시
            'zones.*.quantity' => ['required_with:zones', 'integer', 'min:1'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'is_total_budget_undecided' => ['boolean'],
            'event_start_date_range_min' => ['nullable', 'date'],
            'event_start_date_range_max' => ['nullable', 'date', 'after_or_equal:event_start_date_range_min'],
            'event_end_date_range_min' => ['nullable', 'date', 'after_or_equal:event_start_date_range_min'],
            'event_end_date_range_max' => ['nullable', 'date', 'after_or_equal:event_end_date_range_min'],
            'event_duration_days' => ['nullable', 'integer', 'min:0'],
            'setup_start_date' => ['nullable', 'date'],
            'teardown_end_date' => ['nullable', 'date', 'after_or_equal:setup_start_date'],
            'project_kickoff_date' => ['required', 'date'],
            'settlement_close_date' => ['required', 'date', 'after_or_equal:project_kickoff_date'],
            'contact_person_name' => ['required', 'string', 'max:255'],
            'contact_person_contact' => ['required', 'string', 'max:255'],
            'admin_person_name' => ['required', 'string', 'max:255'],
            'admin_person_contact' => ['required', 'string', 'max:255'],
        ];
    }
}
