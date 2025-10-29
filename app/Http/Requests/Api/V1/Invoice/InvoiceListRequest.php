<?php

namespace App\Http\Requests\Api\V1\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceListRequest extends FormRequest
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
            'status' => ['sometimes', 'array'],
            'status.*' => ['string', 'in:draft,processing,completed,cancelled'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'customer_phone' => ['sometimes', 'string', 'max:20'],
            'branch_shop_id' => ['sometimes', 'integer', 'exists:branch_shops,id'],
            'sales_channel' => ['sometimes', 'string', 'in:offline,online,marketplace,social_media,phone_order'],
            'sold_by' => ['sometimes', 'integer', 'exists:users,id'],
            'created_by' => ['sometimes', 'integer', 'exists:users,id'],

            // Date filtering
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,invoice_number,invoice_date,due_date,total_amount,status,created_at,updated_at'],
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
            'status.*.in' => 'Invalid status. Allowed values: draft, processing, completed, cancelled.',
            'customer_phone.max' => 'Customer phone number cannot exceed 20 characters.',
            'sales_channel.in' => 'Invalid sales channel. Allowed values: offline, online, marketplace, social_media, phone_order.',
            'date_to.after_or_equal' => 'End date must be after or equal to start date.',
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
            'customer_phone' => 'customer phone number',
            'date_from' => 'start date',
            'date_to' => 'end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
