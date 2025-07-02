<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 추후 Policy로 권한 체크
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subscription_end_date' => 'nullable|date',
            'subscription_status' => 'nullable|in:active,inactive,suspended',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subscription_end_date.date' => '구독 만료일은 유효한 날짜여야 합니다.',
            'subscription_status.in' => '유효하지 않은 구독 상태입니다.',
            'name.max' => '대행사명은 255자를 초과할 수 없습니다.',
            'address.max' => '주소는 500자를 초과할 수 없습니다.',
        ];
    }
}