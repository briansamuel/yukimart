<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderListRequest extends FormRequest
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
            'status.*' => ['string', 'in:draft,pending,processing,completed,cancelled'],
            'payment_status' => ['sometimes', 'array'],
            'payment_status.*' => ['string', 'in:unpaid,partial,paid,refunded'],
            'delivery_status' => ['sometimes', 'array'],
            'delivery_status.*' => ['string', 'in:pending,processing,shipped,delivered,failed'],
            'order_type' => ['sometimes', 'array'],
            'order_type.*' => ['string', 'in:sale,return,exchange,service'],
            'priority' => ['sometimes', 'array'],
            'priority.*' => ['string', 'in:low,normal,high,urgent'],

            // Relationships
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'branch_shop_id' => ['sometimes', 'integer', 'exists:branch_shops,id'],
            'created_by' => ['sometimes', 'integer', 'exists:users,id'],
            'sold_by' => ['sometimes', 'integer', 'exists:users,id'],

            // Amount filtering
            'min_amount' => ['sometimes', 'numeric', 'min:0'],
            'max_amount' => ['sometimes', 'numeric', 'min:0', 'gte:min_amount'],

            // Date filtering
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'order_date_from' => ['sometimes', 'date'],
            'order_date_to' => ['sometimes', 'date', 'after_or_equal:order_date_from'],
            'delivery_date_from' => ['sometimes', 'date'],
            'delivery_date_to' => ['sometimes', 'date', 'after_or_equal:delivery_date_from'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],
            'search_fields' => ['sometimes', 'array'],
            'search_fields.*' => ['string', 'in:order_number,customer_name,customer_phone,reference_number'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,order_number,order_date,delivery_date,final_amount,status,payment_status,delivery_status,created_at,updated_at'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],

            // API optimization
            'include' => ['sometimes', 'string'],
            'fields' => ['sometimes', 'string'],

            // Advanced filters
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
            'updated_from' => ['sometimes', 'date'],
            'updated_to' => ['sometimes', 'date', 'after_or_equal:updated_from'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'per_page.max' => 'Maximum 100 items per page allowed.',
            'status.*.in' => 'Invalid order status. Allowed values: draft, pending, processing, completed, cancelled.',
            'payment_status.*.in' => 'Invalid payment status. Allowed values: unpaid, partial, paid, refunded.',
            'delivery_status.*.in' => 'Invalid delivery status. Allowed values: pending, processing, shipped, delivered, failed.',
            'order_type.*.in' => 'Invalid order type. Allowed values: sale, return, exchange, service.',
            'priority.*.in' => 'Invalid priority. Allowed values: low, normal, high, urgent.',
            'max_amount.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
            'date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'order_date_to.after_or_equal' => 'Order end date must be after or equal to order start date.',
            'delivery_date_to.after_or_equal' => 'Delivery end date must be after or equal to delivery start date.',
            'created_to.after_or_equal' => 'End date must be after or equal to start date.',
            'updated_to.after_or_equal' => 'End date must be after or equal to start date.',
            'search_fields.*.in' => 'Invalid search field. Allowed values: order_number, customer_name, customer_phone, reference_number.',
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
            'date_from' => 'start date',
            'date_to' => 'end date',
            'order_date_from' => 'order start date',
            'order_date_to' => 'order end date',
            'delivery_date_from' => 'delivery start date',
            'delivery_date_to' => 'delivery end date',
            'created_from' => 'created start date',
            'created_to' => 'created end date',
            'updated_from' => 'updated start date',
            'updated_to' => 'updated end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
