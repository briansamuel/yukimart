<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateProductRequest extends FormRequest
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
            'product_name' => ['required', 'string', 'max:255'],
            'product_slug' => ['nullable', 'string', 'max:255', 'unique:products'],
            'product_description' => ['required', 'string'],
            'product_content' => ['nullable', 'string'],
            'product_thumbnail' => ['nullable', 'string', 'max:255'],
            'product_status' => ['nullable', 'in:draft,pending,publish,trash'],
            'product_type' => ['nullable', 'in:simple,variable,grouped,external'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'product_feature' => ['nullable', 'boolean'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            
            // Dimensions
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            
            // Supplier information
            'supplier_cost' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            
            // Meta data
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
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
            'product_name.required' => 'Product name is required',
            'product_name.max' => 'Product name cannot exceed 255 characters',
            'product_slug.unique' => 'Product slug must be unique',
            'product_description.required' => 'Product description is required',
            'product_status.in' => 'Invalid product status',
            'product_type.in' => 'Invalid product type',
            'sku.unique' => 'SKU must be unique',
            'barcode.unique' => 'Barcode must be unique',
            'category_id.exists' => 'Selected category does not exist',
            'cost_price.required' => 'Cost price is required',
            'cost_price.min' => 'Cost price must be greater than or equal to 0',
            'sale_price.required' => 'Sale price is required',
            'sale_price.min' => 'Sale price must be greater than or equal to 0',
            'regular_price.min' => 'Regular price must be greater than or equal to 0',
            'reorder_point.min' => 'Reorder point must be greater than or equal to 0',
            'weight.min' => 'Weight must be greater than or equal to 0',
            'points.min' => 'Points must be greater than or equal to 0',
            'initial_stock.min' => 'Initial stock must be greater than or equal to 0',
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
            if ($this->sale_price && $this->cost_price && $this->sale_price < $this->cost_price) {
                $validator->errors()->add('sale_price', 'Sale price should not be less than cost price');
            }

            // Validate that regular price is not less than sale price
            if ($this->regular_price && $this->sale_price && $this->regular_price < $this->sale_price) {
                $validator->errors()->add('regular_price', 'Regular price should not be less than sale price');
            }

            // Generate slug if not provided
            if (!$this->product_slug && $this->product_name) {
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
