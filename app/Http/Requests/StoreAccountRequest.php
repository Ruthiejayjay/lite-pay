<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_type' => ['required', 'in:savings,checking'],
            'currency_code' => ['required', 'string', 'exists:currencies,currency_code'],
            'balance' => ['numeric', 'min:0'],
            'total_deposits' => ['numeric', 'min:0'],
            'total_withdrawals' => ['numeric', 'min:0'],
        ];
    }
}
