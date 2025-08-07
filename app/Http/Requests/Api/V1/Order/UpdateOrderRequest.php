<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateOrderRequest extends FormRequest
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
        $orderId = $this->route('order')->id;
        
        return [
            'order_code' => ['sometimes', 'string', 'max:50', 'unique:orders,order_code,' . $orderId],
            'customer_id' => ['sometimes', 'nullable', 'exists:customers,id'],
            'branch_id' => ['sometimes', 'nullable', 'exists:branch_shops,id'],
            'sold_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'status' => ['sometimes', 'in:processing,completed,cancelled,failed,returned,confirmed'],
            'delivery_status' => ['sometimes', 'in:pending,picking,delivering,delivered,returning,returned'],
            'note' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'other_amount' => ['sometimes', 'nullable', 'numeric'],
            
            // Order items (optional for updates)
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
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
            'order_code.unique' => 'Order code must be unique',
            'customer_id.exists' => 'Selected customer does not exist',
            'branch_id.exists' => 'Selected branch does not exist',
            'sold_by.exists' => 'Selected seller does not exist',
            'status.in' => 'Invalid order status',
            'delivery_status.in' => 'Invalid delivery status',
            'due_date.date' => 'Due date must be a valid date',
            'due_date.after_or_equal' => 'Due date must be today or later',
            'discount_amount.min' => 'Discount amount must be greater than or equal to 0',
            'other_amount.numeric' => 'Other amount must be a number',
            
            // Items validation messages
            'items.array' => 'Order items must be an array',
            'items.min' => 'Order must have at least one item',
            'items.*.product_id.required_with' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required_with' => 'Quantity is required for each item',
            'items.*.quantity.integer' => 'Quantity must be an integer',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'items.*.unit_price.numeric' => 'Unit price must be a number',
            'items.*.unit_price.min' => 'Unit price must be greater than or equal to 0',
            'items.*.discount.numeric' => 'Discount must be a number',
            'items.*.discount.min' => 'Discount must be greater than or equal to 0',
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
            $order = $this->route('order');

            // Check if order can be updated
            if (in_array($order->status, ['completed', 'cancelled'])) {
                $validator->errors()->add('status', 'Cannot update completed or cancelled orders');
            }

            // Validate status transitions
            if ($this->has('status')) {
                $currentStatus = $order->status;
                $newStatus = $this->status;

                // Define allowed status transitions
                $allowedTransitions = [
                    'processing' => ['completed', 'cancelled', 'confirmed'],
                    'confirmed' => ['completed', 'cancelled'],
                    'completed' => [], // Cannot change from completed
                    'cancelled' => [], // Cannot change from cancelled
                    'failed' => ['processing', 'cancelled'],
                    'returned' => ['processing'],
                ];

                if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
                    $validator->errors()->add('status', "Cannot change status from {$currentStatus} to {$newStatus}");
                }
            }

            // Validate order items if provided
            if ($this->has('items') && is_array($this->items)) {
                foreach ($this->items as $index => $item) {
                    if (isset($item['product_id'])) {
                        $product = \App\Models\Product::find($item['product_id']);
                        if ($product) {
                            // Check if product is published
                            if ($product->product_status !== 'publish') {
                                $validator->errors()->add(
                                    "items.{$index}.product_id",
                                    "Product is not available for sale"
                                );
                            }

                            // Check stock availability (considering current order's reserved stock)
                            if (isset($item['quantity'])) {
                                $currentOrderItem = $order->orderItems()->where('product_id', $item['product_id'])->first();
                                $currentReserved = $currentOrderItem ? $currentOrderItem->quantity : 0;
                                $availableStock = $product->getAvailableQuantity() + $currentReserved;

                                if ($item['quantity'] > $availableStock) {
                                    $validator->errors()->add(
                                        "items.{$index}.quantity",
                                        "Insufficient stock. Available: {$availableStock}"
                                    );
                                }
                            }

                            // Validate discount
                            if (isset($item['discount']) && isset($item['unit_price']) && isset($item['quantity'])) {
                                $lineTotal = $item['unit_price'] * $item['quantity'];
                                if ($item['discount'] > $lineTotal) {
                                    $validator->errors()->add(
                                        "items.{$index}.discount",
                                        "Discount cannot exceed line total"
                                    );
                                }
                            }
                        }
                    }
                }
            }

            // Validate total discount if provided
            if ($this->has('discount_amount') && $this->has('items')) {
                $totalAmount = 0;
                foreach ($this->items as $item) {
                    if (isset($item['unit_price']) && isset($item['quantity'])) {
                        $totalAmount += $item['unit_price'] * $item['quantity'];
                    }
                }
                
                if ($this->discount_amount > $totalAmount) {
                    $validator->errors()->add('discount_amount', 'Order discount cannot exceed total amount');
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
