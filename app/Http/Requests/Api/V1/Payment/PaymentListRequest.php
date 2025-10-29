<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Allow all authenticated users
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Pagination
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'cursor' => ['sometimes', 'string'],

            // Filtering
            'payment_type' => ['sometimes', 'array'],
            'payment_type.*' => ['string', 'in:receipt,payment'],
            'payment_method' => ['sometimes', 'array'],
            'payment_method.*' => ['string', 'in:cash,card,transfer,check,points,other'],
            'status' => ['sometimes', 'array'],
            'status.*' => ['string', 'in:pending,completed,cancelled'],
            'reference_type' => ['sometimes', 'array'],
            'reference_type.*' => ['string', 'in:invoice,order,return_order,other'],
            'reference_id' => ['sometimes', 'integer'],
            'bank_account_id' => ['sometimes', 'integer', 'exists:bank_accounts,id'],

            // Amount filtering
            'min_amount' => ['sometimes', 'numeric', 'min:0'],
            'max_amount' => ['sometimes', 'numeric', 'min:0', 'gte:min_amount'],

            // Date filtering
            'payment_date_from' => ['sometimes', 'date'],
            'payment_date_to' => ['sometimes', 'date', 'after_or_equal:payment_date_from'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],
            'search_fields' => ['sometimes', 'array'],
            'search_fields.*' => ['string', 'in:payment_code,description,reference_number,notes'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,payment_code,payment_type,payment_method,amount,payment_date,status,created_at,updated_at'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],

            // API optimization
            'include' => ['sometimes', 'string'],
            'fields' => ['sometimes', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'per_page.max' => 'Maximum 100 items per page allowed.',
            'payment_type.*.in' => 'Invalid payment type. Allowed values: receipt, payment.',
            'payment_method.*.in' => 'Invalid payment method. Allowed values: cash, card, transfer, check, points, other.',
            'status.*.in' => 'Invalid status. Allowed values: pending, completed, cancelled.',
            'reference_type.*.in' => 'Invalid reference type. Allowed values: invoice, order, return_order, other.',
            'max_amount.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
            'payment_date_to.after_or_equal' => 'Payment end date must be after or equal to payment start date.',
            'created_to.after_or_equal' => 'End date must be after or equal to start date.',
            'search_fields.*.in' => 'Invalid search field. Allowed values: payment_code, description, reference_number, notes.',
            'sort_by.in' => 'Invalid sort field.',
            'sort_order.in' => 'Sort order must be asc or desc.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'items per page',
            'min_amount' => 'minimum amount',
            'max_amount' => 'maximum amount',
            'payment_date_from' => 'payment start date',
            'payment_date_to' => 'payment end date',
            'created_from' => 'created start date',
            'created_to' => 'created end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
