<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreatePaymentRequest extends FormRequest
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
            'payment_number' => ['nullable', 'string', 'max:50', 'unique:payments'],
            'payment_type' => ['required', 'in:receipt,payment'],
            'reference_type' => ['required', 'in:invoice,return_order,order,manual'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'branch_shop_id' => ['nullable', 'exists:branch_shops,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'collector_id' => ['nullable', 'exists:users,id'],
            
            // Payment details
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,transfer,check,points,other'],
            'status' => ['nullable', 'in:pending,completed,cancelled'],
            
            // Additional information
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            
            // Bank/Card information
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
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
            'payment_number.unique' => 'Payment number must be unique',
            'payment_type.required' => 'Payment type is required',
            'payment_type.in' => 'Payment type must be either receipt or payment',
            'reference_type.required' => 'Reference type is required',
            'reference_type.in' => 'Invalid reference type',
            'reference_id.integer' => 'Reference ID must be an integer',
            'reference_id.min' => 'Reference ID must be greater than 0',
            'customer_id.exists' => 'Selected customer does not exist',
            'branch_shop_id.exists' => 'Selected branch shop does not exist',
            'bank_account_id.exists' => 'Selected bank account does not exist',
            'collector_id.exists' => 'Selected collector does not exist',
            'payment_date.required' => 'Payment date is required',
            'payment_date.date' => 'Payment date must be a valid date',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than or equal to 0',
            'actual_amount.numeric' => 'Actual amount must be a number',
            'actual_amount.min' => 'Actual amount must be greater than or equal to 0',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'status.in' => 'Invalid payment status',
            'description.max' => 'Description cannot exceed 255 characters',
            'bank_name.max' => 'Bank name cannot exceed 255 characters',
            'account_number.max' => 'Account number cannot exceed 50 characters',
            'transaction_reference.max' => 'Transaction reference cannot exceed 255 characters',
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
            // Validate reference relationship
            if ($this->reference_type && $this->reference_id) {
                $referenceExists = false;
                
                switch ($this->reference_type) {
                    case 'invoice':
                        $referenceExists = \App\Models\Invoice::where('id', $this->reference_id)->exists();
                        break;
                    case 'order':
                        $referenceExists = \App\Models\Order::where('id', $this->reference_id)->exists();
                        break;
                    case 'return_order':
                        $referenceExists = \App\Models\ReturnOrder::where('id', $this->reference_id)->exists();
                        break;
                    case 'manual':
                        $referenceExists = true; // Manual payments don't need reference validation
                        break;
                }
                
                if (!$referenceExists && $this->reference_type !== 'manual') {
                    $validator->errors()->add('reference_id', 'Referenced ' . $this->reference_type . ' does not exist');
                }
            }

            // Validate bank account for non-cash payments
            if (in_array($this->payment_method, ['card', 'transfer', 'check']) && empty($this->bank_account_id)) {
                $validator->errors()->add('bank_account_id', 'Bank account is required for ' . $this->payment_method . ' payments');
            }

            // Validate bank information for transfer/card payments
            if (in_array($this->payment_method, ['transfer', 'card'])) {
                if (empty($this->bank_account_id) && empty($this->bank_name)) {
                    $validator->errors()->add('bank_name', 'Bank name is required for ' . $this->payment_method . ' payments when bank account is not specified');
                }
                
                if ($this->payment_method === 'transfer' && empty($this->transaction_reference)) {
                    $validator->errors()->add('transaction_reference', 'Transaction reference is required for transfer payments');
                }
            }

            // Validate actual amount
            if ($this->has('actual_amount') && $this->actual_amount !== null) {
                if ($this->payment_type === 'receipt' && $this->actual_amount > $this->amount * 1.1) {
                    $validator->errors()->add('actual_amount', 'Actual amount cannot exceed 110% of the payment amount');
                }
            }

            // Validate payment date
            if ($this->payment_date && $this->payment_date > now()->addDays(1)->toDateString()) {
                $validator->errors()->add('payment_date', 'Payment date cannot be more than 1 day in the future');
            }

            // Validate amount limits
            if ($this->amount > 10000000000) { // 10 billion VND
                $validator->errors()->add('amount', 'Payment amount cannot exceed 10,000,000,000 VND');
            }

            // Validate customer requirement for certain reference types
            if (in_array($this->reference_type, ['invoice', 'order']) && empty($this->customer_id)) {
                // Try to get customer from reference
                if ($this->reference_id) {
                    $customer = null;
                    if ($this->reference_type === 'invoice') {
                        $invoice = \App\Models\Invoice::find($this->reference_id);
                        $customer = $invoice?->customer_id;
                    } elseif ($this->reference_type === 'order') {
                        $order = \App\Models\Order::find($this->reference_id);
                        $customer = $order?->customer_id;
                    }
                    
                    if ($customer && $customer != 0) {
                        $this->merge(['customer_id' => $customer]);
                    }
                }
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
