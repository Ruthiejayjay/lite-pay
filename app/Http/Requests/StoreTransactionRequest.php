<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StoreTransactionRequest extends FormRequest
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
            'receiver_account_number' => ['required', 'numeric', 'exists:accounts,account_number'],
            'currency_code' => ['required', 'string', 'exists:currencies,currency_code'],
            'amount' => ['required', 'numeric', 'min:10']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $senderAccounts = Auth::user()->accounts;
            $selectedCurrency = Currency::where('currency_code', $this->currency_code)->first();

            Log::info('Selected Currency:', ['currency' => $selectedCurrency]);
            Log::info('Sender Accounts:', ['accounts' => $senderAccounts]);

            if (!$selectedCurrency) {
                $validator->errors()->add('currency_code', 'Invalid currency code.');
                return;
            }

            $accountWithCurrency = $senderAccounts->where('currency_id', $selectedCurrency->id)->first();

            if (!$accountWithCurrency) {
                $validator->errors()->add('currency_code', 'No associated account found with this currency.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
