<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|string|max:255',
            'branch_shop_id' => 'required|exists:branch_shops,id',
            'invoice_type' => 'required|in:sale,service,other',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_terms' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'terms_conditions' => 'nullable|string|max:2000',
            'reference_number' => 'nullable|string|max:100',
            
            // Invoice items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_sku' => 'nullable|string|max:100',
            'items.*.product_description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1|max:999999',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0|max:999999999.99',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_name.required_if' => 'Tên khách hàng là bắt buộc khi không chọn khách hàng có sẵn',
            'branch_shop_id.required' => 'Chi nhánh là bắt buộc',
            'branch_shop_id.exists' => 'Chi nhánh không tồn tại',
            'invoice_type.required' => 'Loại hóa đơn là bắt buộc',
            'invoice_type.in' => 'Loại hóa đơn không hợp lệ',
            'invoice_date.required' => 'Ngày hóa đơn là bắt buộc',
            'invoice_date.date' => 'Ngày hóa đơn không đúng định dạng',
            'due_date.date' => 'Ngày đến hạn không đúng định dạng',
            'due_date.after_or_equal' => 'Ngày đến hạn phải sau hoặc bằng ngày hóa đơn',
            
            'items.required' => 'Danh sách sản phẩm là bắt buộc',
            'items.array' => 'Danh sách sản phẩm phải là mảng',
            'items.min' => 'Phải có ít nhất 1 sản phẩm',
            'items.*.product_name.required' => 'Tên sản phẩm là bắt buộc',
            'items.*.quantity.required' => 'Số lượng là bắt buộc',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.unit.required' => 'Đơn vị tính là bắt buộc',
            'items.*.unit_price.required' => 'Đơn giá là bắt buộc',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0',
            'items.*.discount_rate.numeric' => 'Tỷ lệ giảm giá phải là số',
            'items.*.discount_rate.max' => 'Tỷ lệ giảm giá không được vượt quá 100%',
            'items.*.tax_rate.numeric' => 'Tỷ lệ thuế phải là số',
            'items.*.tax_rate.max' => 'Tỷ lệ thuế không được vượt quá 100%',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set customer_id to null if it's 0 (walk-in customer)
        if ($this->customer_id === 0 || $this->customer_id === '0') {
            $this->merge(['customer_id' => null]);
        }
        
        // Set default customer name for walk-in customers
        if (!$this->customer_id && !$this->customer_name) {
            $this->merge(['customer_name' => 'Khách lẻ']);
        }
    }
}
