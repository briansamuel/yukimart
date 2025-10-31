<?php

namespace App\Http\Requests\Api\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationListRequest extends FormRequest
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
            'type' => ['sometimes', 'array'],
            'type.*' => ['string', 'in:order,invoice,inventory,system,user,customer_birthday,receipt_voucher,payment_voucher'],
            'priority' => ['sometimes', 'array'],
            'priority.*' => ['string', 'in:low,normal,high,urgent'],
            'status' => ['sometimes', 'string', 'in:read,unread,all'],
            'is_dismissible' => ['sometimes', 'boolean'],

            // Date filtering
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
            'expires_from' => ['sometimes', 'date'],
            'expires_to' => ['sometimes', 'date', 'after_or_equal:expires_from'],
            'read_from' => ['sometimes', 'date'],
            'read_to' => ['sometimes', 'date', 'after_or_equal:read_from'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],
            'search_fields' => ['sometimes', 'array'],
            'search_fields.*' => ['string', 'in:title,message,type'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,type,title,priority,created_at,read_at,expires_at'],
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
            'type.*.in' => 'Invalid notification type.',
            'priority.*.in' => 'Invalid priority. Allowed values: low, normal, high, urgent.',
            'status.in' => 'Invalid status. Allowed values: read, unread, all.',
            'created_to.after_or_equal' => 'End date must be after or equal to start date.',
            'expires_to.after_or_equal' => 'Expires end date must be after or equal to expires start date.',
            'read_to.after_or_equal' => 'Read end date must be after or equal to read start date.',
            'search_fields.*.in' => 'Invalid search field. Allowed values: title, message, type.',
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
            'created_from' => 'created start date',
            'created_to' => 'created end date',
            'expires_from' => 'expires start date',
            'expires_to' => 'expires end date',
            'read_from' => 'read start date',
            'read_to' => 'read end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
