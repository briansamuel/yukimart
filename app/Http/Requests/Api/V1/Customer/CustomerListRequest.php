<?php

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerListRequest extends FormRequest
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
            'customer_type' => ['sometimes', 'array'],
            'customer_type.*' => ['string', 'in:individual,business'],
            'customer_group' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'array'],
            'status.*' => ['string', 'in:active,inactive'],
            'branch_shop_id' => ['sometimes', 'integer', 'exists:branch_shops,id'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],
            'search_fields' => ['sometimes', 'array'],
            'search_fields.*' => ['string', 'in:name,phone,email,customer_code,address'],

            // Date filtering
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
            'birthday_from' => ['sometimes', 'date'],
            'birthday_to' => ['sometimes', 'date', 'after_or_equal:birthday_from'],

            // Points filtering
            'min_points' => ['sometimes', 'integer', 'min:0'],
            'max_points' => ['sometimes', 'integer', 'min:0', 'gte:min_points'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,name,phone,email,customer_code,customer_type,status,points,created_at,updated_at'],
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
            'customer_type.*.in' => 'Invalid customer type. Allowed values: individual, business.',
            'status.*.in' => 'Invalid status. Allowed values: active, inactive.',
            'created_to.after_or_equal' => 'End date must be after or equal to start date.',
            'birthday_to.after_or_equal' => 'Birthday end date must be after or equal to birthday start date.',
            'max_points.gte' => 'Maximum points must be greater than or equal to minimum points.',
            'search_fields.*.in' => 'Invalid search field. Allowed values: name, phone, email, customer_code, address.',
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
            'min_points' => 'minimum points',
            'max_points' => 'maximum points',
            'created_from' => 'created start date',
            'created_to' => 'created end date',
            'birthday_from' => 'birthday start date',
            'birthday_to' => 'birthday end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
