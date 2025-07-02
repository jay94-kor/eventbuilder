<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
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
            'account_status' => 'required|in:pending,approved,rejected,suspended',
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'account_status.required' => '계정 상태는 필수입니다.',
            'account_status.in' => '유효하지 않은 계정 상태입니다.',
            'admin_notes.max' => '관리자 메모는 1000자를 초과할 수 없습니다.',
        ];
    }
}