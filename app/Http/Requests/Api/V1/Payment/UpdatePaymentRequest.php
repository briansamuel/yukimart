<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdatePaymentRequest extends FormRequest
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
        $paymentId = $this->route('payment')->id;
        
        return [
            'payment_number' => ['sometimes', 'string', 'max:50', 'unique:payments,payment_number,' . $paymentId],
            'payment_type' => ['sometimes', 'in:receipt,payment'],
            'reference_type' => ['sometimes', 'in:invoice,return_order,order,manual'],
            'reference_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'customer_id' => ['sometimes', 'nullable', 'exists:customers,id'],
            'branch_shop_id' => ['sometimes', 'nullable', 'exists:branch_shops,id'],
            'bank_account_id' => ['sometimes', 'nullable', 'exists:bank_accounts,id'],
            'collector_id' => ['sometimes', 'nullable', 'exists:users,id'],
            
            // Payment details
            'payment_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'actual_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,card,transfer,check,points,other'],
            'status' => ['sometimes', 'in:pending,completed,cancelled'],
            
            // Additional information
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            
            // Bank/Card information
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'account_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'transaction_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
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
            'payment_type.in' => 'Payment type must be either receipt or payment',
            'reference_type.in' => 'Invalid reference type',
            'reference_id.integer' => 'Reference ID must be an integer',
            'reference_id.min' => 'Reference ID must be greater than 0',
            'customer_id.exists' => 'Selected customer does not exist',
            'branch_shop_id.exists' => 'Selected branch shop does not exist',
            'bank_account_id.exists' => 'Selected bank account does not exist',
            'collector_id.exists' => 'Selected collector does not exist',
            'payment_date.date' => 'Payment date must be a valid date',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than or equal to 0',
            'actual_amount.numeric' => 'Actual amount must be a number',
            'actual_amount.min' => 'Actual amount must be greater than or equal to 0',
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
            $payment = $this->route('payment');

            // Check if payment can be updated
            if ($payment->status === 'completed') {
                $validator->errors()->add('status', 'Cannot update completed payments');
            }

            // Validate reference relationship if provided
            if ($this->has('reference_type') && $this->has('reference_id')) {
                $referenceType = $this->reference_type;
                $referenceId = $this->reference_id;
                
                if ($referenceType && $referenceId) {
                    $referenceExists = false;
                    
                    switch ($referenceType) {
                        case 'invoice':
                            $referenceExists = \App\Models\Invoice::where('id', $referenceId)->exists();
                            break;
                        case 'order':
                            $referenceExists = \App\Models\Order::where('id', $referenceId)->exists();
                            break;
                        case 'return_order':
                            $referenceExists = \App\Models\ReturnOrder::where('id', $referenceId)->exists();
                            break;
                        case 'manual':
                            $referenceExists = true;
                            break;
                    }
                    
                    if (!$referenceExists && $referenceType !== 'manual') {
                        $validator->errors()->add('reference_id', 'Referenced ' . $referenceType . ' does not exist');
                    }
                }
            }

            // Validate bank account for non-cash payments
            $paymentMethod = $this->has('payment_method') ? $this->payment_method : $payment->payment_method;
            $bankAccountId = $this->has('bank_account_id') ? $this->bank_account_id : $payment->bank_account_id;
            
            if (in_array($paymentMethod, ['card', 'transfer', 'check']) && empty($bankAccountId)) {
                $validator->errors()->add('bank_account_id', 'Bank account is required for ' . $paymentMethod . ' payments');
            }

            // Validate bank information for transfer/card payments
            if (in_array($paymentMethod, ['transfer', 'card'])) {
                $bankName = $this->has('bank_name') ? $this->bank_name : $payment->bank_name;
                
                if (empty($bankAccountId) && empty($bankName)) {
                    $validator->errors()->add('bank_name', 'Bank name is required for ' . $paymentMethod . ' payments when bank account is not specified');
                }
                
                if ($paymentMethod === 'transfer') {
                    $transactionRef = $this->has('transaction_reference') ? $this->transaction_reference : $payment->transaction_reference;
                    if (empty($transactionRef)) {
                        $validator->errors()->add('transaction_reference', 'Transaction reference is required for transfer payments');
                    }
                }
            }

            // Validate actual amount
            if ($this->has('actual_amount') && $this->actual_amount !== null) {
                $amount = $this->has('amount') ? $this->amount : $payment->amount;
                $paymentType = $this->has('payment_type') ? $this->payment_type : $payment->payment_type;
                
                if ($paymentType === 'receipt' && $this->actual_amount > $amount * 1.1) {
                    $validator->errors()->add('actual_amount', 'Actual amount cannot exceed 110% of the payment amount');
                }
            }

            // Validate payment date
            if ($this->has('payment_date') && $this->payment_date > now()->addDays(1)->toDateString()) {
                $validator->errors()->add('payment_date', 'Payment date cannot be more than 1 day in the future');
            }

            // Validate amount limits
            if ($this->has('amount') && $this->amount > 10000000000) { // 10 billion VND
                $validator->errors()->add('amount', 'Payment amount cannot exceed 10,000,000,000 VND');
            }

            // Validate status transitions
            if ($this->has('status')) {
                $currentStatus = $payment->status;
                $newStatus = $this->status;

                // Define allowed status transitions
                $allowedTransitions = [
                    'pending' => ['completed', 'cancelled'],
                    'completed' => [], // Cannot change from completed
                    'cancelled' => ['pending'], // Can reactivate cancelled payments
                ];

                if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
                    $validator->errors()->add('status', "Cannot change status from {$currentStatus} to {$newStatus}");
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
