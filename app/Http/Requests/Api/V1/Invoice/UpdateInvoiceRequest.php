<?php

namespace App\Http\Requests\Api\V1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateInvoiceRequest extends FormRequest
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
            'customer_id' => ['sometimes', 'nullable', 'exists:customers,id'],
            'sold_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'invoice_date' => ['sometimes', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:invoice_date'],
            'payment_method' => ['sometimes', 'in:cash,card,transfer,check,points,other'],
            'sale_channel' => ['sometimes', 'in:direct,store,online,phone,social,other'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'discount_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tax_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'shipping_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:draft,processing,complete,cancelled,undeliverable'],
            
            // Invoice items (optional for updates)
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id' => ['sometimes', 'exists:invoice_items,id'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['sometimes', 'nullable', 'string', 'max:500'],
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
            'sold_by.exists' => 'Selected seller does not exist',
            'due_date.after_or_equal' => 'Due date must be after or equal to invoice date',
            'payment_method.in' => 'Invalid payment method',
            'sale_channel.in' => 'Invalid sale channel',
            'discount_rate.max' => 'Discount rate cannot exceed 100%',
            'tax_rate.max' => 'Tax rate cannot exceed 100%',
            'items.min' => 'At least one item is required',
            'items.*.id.exists' => 'Invoice item does not exist',
            'items.*.product_id.required_with' => 'Product is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required_with' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'items.*.unit_price.required_with' => 'Unit price is required for each item',
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
            // Validate discount and tax calculations
            if ($this->discount_rate && $this->discount_amount) {
                $validator->errors()->add('discount', 'Cannot specify both discount rate and amount');
            }

            if ($this->tax_rate && $this->tax_amount) {
                $validator->errors()->add('tax', 'Cannot specify both tax rate and amount');
            }

            // Validate that invoice items belong to this invoice
            if ($this->items) {
                $invoice = $this->route('invoice');
                foreach ($this->items as $index => $item) {
                    if (isset($item['id'])) {
                        $invoiceItem = \App\Models\InvoiceItem::find($item['id']);
                        if ($invoiceItem && $invoiceItem->invoice_id !== $invoice->id) {
                            $validator->errors()->add("items.{$index}.id", 'Invoice item does not belong to this invoice');
                        }
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
