<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;
        
        return [
            'product_name' => ['sometimes', 'string', 'max:255'],
            'product_slug' => ['sometimes', 'string', 'max:255', 'unique:products,product_slug,' . $productId],
            'product_description' => ['sometimes', 'string'],
            'product_content' => ['sometimes', 'nullable', 'string'],
            'product_thumbnail' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_status' => ['sometimes', 'in:draft,pending,publish,trash'],
            'product_type' => ['sometimes', 'in:simple,variable,grouped,external'],
            'sku' => ['sometimes', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'barcode' => ['sometimes', 'nullable', 'string', 'max:100', 'unique:products,barcode,' . $productId],
            'category_id' => ['sometimes', 'nullable', 'exists:product_categories,id'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'regular_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'reorder_point' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'weight' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'points' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_feature' => ['sometimes', 'nullable', 'boolean'],
            
            // Dimensions
            'length' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'width' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'height' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            
            // Supplier information
            'supplier_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            
            // Meta data
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'meta_keywords' => ['sometimes', 'nullable', 'string', 'max:500'],
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
            'product_name.max' => 'Product name cannot exceed 255 characters',
            'product_slug.unique' => 'Product slug must be unique',
            'product_status.in' => 'Invalid product status',
            'product_type.in' => 'Invalid product type',
            'sku.unique' => 'SKU must be unique',
            'barcode.unique' => 'Barcode must be unique',
            'category_id.exists' => 'Selected category does not exist',
            'cost_price.min' => 'Cost price must be greater than or equal to 0',
            'sale_price.min' => 'Sale price must be greater than or equal to 0',
            'regular_price.min' => 'Regular price must be greater than or equal to 0',
            'reorder_point.min' => 'Reorder point must be greater than or equal to 0',
            'weight.min' => 'Weight must be greater than or equal to 0',
            'points.min' => 'Points must be greater than or equal to 0',
            'length.min' => 'Length must be greater than or equal to 0',
            'width.min' => 'Width must be greater than or equal to 0',
            'height.min' => 'Height must be greater than or equal to 0',
            'supplier_cost.min' => 'Supplier cost must be greater than or equal to 0',
            'lead_time_days.min' => 'Lead time days must be greater than or equal to 0',
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
            // Validate that sale price is not less than cost price
            if ($this->has('sale_price') && $this->has('cost_price') && 
                $this->sale_price < $this->cost_price) {
                $validator->errors()->add('sale_price', 'Sale price should not be less than cost price');
            }

            // Validate that regular price is not less than sale price
            if ($this->has('regular_price') && $this->has('sale_price') && 
                $this->regular_price && $this->regular_price < $this->sale_price) {
                $validator->errors()->add('regular_price', 'Regular price should not be less than sale price');
            }

            // Update slug if product name is changed
            if ($this->has('product_name') && !$this->has('product_slug')) {
                $this->merge([
                    'product_slug' => \Str::slug($this->product_name)
                ]);
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
