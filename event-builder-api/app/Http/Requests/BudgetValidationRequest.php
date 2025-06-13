<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetValidationRequest extends FormRequest
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
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'is_total_budget_undecided' => ['boolean'],
            'category_budgets' => ['nullable', 'array'],
            'category_budgets.*.category_id' => ['required', 'exists:feature_categories,id'],
            'category_budgets.*.amount' => ['nullable', 'numeric', 'min:0'],
            'category_budgets.*.is_undecided' => ['boolean'],
            'feature_budgets' => ['nullable', 'array'],
            'feature_budgets.*.feature_id' => ['required', 'exists:features,id'],
            'feature_budgets.*.amount' => ['nullable', 'numeric', 'min:0'],
            'feature_budgets.*.is_undecided' => ['boolean'],
        ];
    }
}
