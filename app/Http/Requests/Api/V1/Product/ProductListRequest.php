<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductListRequest extends FormRequest
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
            'category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'brand_id' => ['sometimes', 'integer', 'exists:brands,id'],
            'supplier_id' => ['sometimes', 'integer', 'exists:suppliers,id'],
            'product_status' => ['sometimes', 'array'],
            'product_status.*' => ['string', 'in:trash,pending,draft,publish'],
            'has_variants' => ['sometimes', 'boolean'],
            'product_feature' => ['sometimes', 'boolean'],

            // Price filtering
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],

            // Stock filtering
            'stock_status' => ['sometimes', 'string', 'in:in_stock,low_stock,out_of_stock,all'],
            'min_stock' => ['sometimes', 'integer', 'min:0'],
            'max_stock' => ['sometimes', 'integer', 'min:0', 'gte:min_stock'],

            // Search
            'search' => ['sometimes', 'string', 'max:255'],
            'search_fields' => ['sometimes', 'array'],
            'search_fields.*' => ['string', 'in:name,sku,barcode,description'],

            // Sorting
            'sort_by' => ['sometimes', 'string', 'in:id,product_name,sku,sale_price,cost_price,created_at,updated_at,stock_quantity'],
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
            'product_status.*.in' => 'Invalid product status. Allowed values: trash, pending, draft, publish.',
            'stock_status.in' => 'Invalid stock status. Allowed values: in_stock, low_stock, out_of_stock, all.',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'max_stock.gte' => 'Maximum stock must be greater than or equal to minimum stock.',
            'created_to.after_or_equal' => 'End date must be after or equal to start date.',
            'updated_to.after_or_equal' => 'End date must be after or equal to start date.',
            'search_fields.*.in' => 'Invalid search field. Allowed values: name, sku, barcode, description.',
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
            'min_price' => 'minimum price',
            'max_price' => 'maximum price',
            'min_stock' => 'minimum stock',
            'max_stock' => 'maximum stock',
            'created_from' => 'created start date',
            'created_to' => 'created end date',
            'updated_from' => 'updated start date',
            'updated_to' => 'updated end date',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }
}
