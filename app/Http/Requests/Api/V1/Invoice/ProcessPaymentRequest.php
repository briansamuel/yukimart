<?php

namespace App\Http\Requests\Api\V1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,transfer,check,points,other'],
            'payment_date' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount must be greater than 0',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'payment_date.date' => 'Invalid payment date format',
            'bank_account_id.exists' => 'Selected bank account does not exist',
            'exchange_rate.min' => 'Exchange rate must be greater than 0',
            'currency.size' => 'Currency code must be 3 characters',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $invoice = $this->route('invoice');
            
            // Check if invoice exists and is not already fully paid
            if ($invoice) {
                $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
                
                if ($remainingAmount <= 0) {
                    $validator->errors()->add('amount', 'Invoice is already fully paid');
                } elseif ($this->amount > $remainingAmount) {
                    $validator->errors()->add('amount', "Payment amount cannot exceed remaining balance of {$remainingAmount}");
                }
            }

            // Validate bank account requirement for certain payment methods
            if (in_array($this->payment_method, ['transfer', 'check']) && !$this->bank_account_id) {
                $validator->errors()->add('bank_account_id', 'Bank account is required for this payment method');
            }

            // Validate currency and exchange rate
            if ($this->currency && !$this->exchange_rate) {
                $validator->errors()->add('exchange_rate', 'Exchange rate is required when currency is specified');
            }

            if ($this->exchange_rate && !$this->currency) {
                $validator->errors()->add('currency', 'Currency is required when exchange rate is specified');
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR',
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'version' => 'v1',
                    'request_id' => request()->header('X-Request-ID', uniqid())
                ]
            ], 422)
        );
    }
}
