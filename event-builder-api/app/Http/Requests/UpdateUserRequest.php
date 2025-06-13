<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 인증된 사용자만 접근 가능하도록 미들웨어에서 처리
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'onboarded' => ['nullable', 'boolean'], // onboarded 필드 추가
            'skip_onboarding' => ['nullable', 'boolean'], // skip_onboarding 필드 추가
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '이름은 필수 입력 항목입니다.',
            'name.string' => '이름은 문자열이어야 합니다.',
            'name.max' => '이름은 255자를 초과할 수 없습니다.',
            'email.required' => '이메일은 필수 입력 항목입니다.',
            'email.string' => '이메일은 문자열이어야 합니다.',
            'email.email' => '유효한 이메일 주소를 입력해주세요.',
            'email.max' => '이메일은 255자를 초과할 수 없습니다.',
            'email.unique' => '이미 사용 중인 이메일 주소입니다.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        ];
    }
}