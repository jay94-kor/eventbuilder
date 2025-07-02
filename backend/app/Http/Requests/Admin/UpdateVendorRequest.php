<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
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
            'status' => 'nullable|in:active,inactive,banned',
            'ban_reason' => 'nullable|string|max:500',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => '유효하지 않은 용역사 상태입니다.',
            'ban_reason.max' => '차단 사유는 500자를 초과할 수 없습니다.',
            'name.max' => '용역사명은 255자를 초과할 수 없습니다.',
            'address.max' => '주소는 500자를 초과할 수 없습니다.',
            'description.max' => '설명은 1000자를 초과할 수 없습니다.',
        ];
    }
}