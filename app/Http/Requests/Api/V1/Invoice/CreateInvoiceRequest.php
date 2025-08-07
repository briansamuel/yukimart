<?php

namespace App\Http\Requests\Api\V1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateInvoiceRequest extends FormRequest
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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'branch_shop_id' => ['nullable', 'exists:branch_shops,id'],
            'sold_by' => ['nullable', 'exists:users,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'payment_method' => ['required', 'in:cash,card,transfer,check,points,other'],
            'sale_channel' => ['required', 'in:direct,store,online,phone,social,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,processing,complete,cancelled,undeliverable'],
            
            // Invoice items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
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
            'customer_id.exists' => 'Selected customer does not exist',
            'branch_shop_id.exists' => 'Selected branch shop does not exist',
            'sold_by.exists' => 'Selected seller does not exist',
            'invoice_date.required' => 'Invoice date is required',
            'due_date.after_or_equal' => 'Due date must be after or equal to invoice date',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'sale_channel.required' => 'Sale channel is required',
            'sale_channel.in' => 'Invalid sale channel',
            'discount_rate.max' => 'Discount rate cannot exceed 100%',
            'tax_rate.max' => 'Tax rate cannot exceed 100%',
            'items.required' => 'Invoice items are required',
            'items.min' => 'At least one item is required',
            'items.*.product_id.required' => 'Product is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'items.*.unit_price.required' => 'Unit price is required for each item',
            'items.*.unit_price.min' => 'Unit price must be greater than or equal to 0',
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
            // Validate that user has access to the branch
            if ($this->branch_shop_id) {
                $user = auth()->user();
                $userBranches = $user->branchShops->pluck('id');
                
                if ($userBranches->isNotEmpty() && !$userBranches->contains($this->branch_shop_id)) {
                    $validator->errors()->add('branch_shop_id', 'You do not have access to this branch');
                }
            }

            // Validate discount and tax calculations
            if ($this->discount_rate && $this->discount_amount) {
                $validator->errors()->add('discount', 'Cannot specify both discount rate and amount');
            }

            if ($this->tax_rate && $this->tax_amount) {
                $validator->errors()->add('tax', 'Cannot specify both tax rate and amount');
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
