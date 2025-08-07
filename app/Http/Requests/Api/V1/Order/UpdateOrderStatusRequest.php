<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['sometimes', 'in:processing,completed,cancelled,failed,returned,confirmed'],
            'delivery_status' => ['sometimes', 'in:pending,picking,delivering,delivered,returning,returned'],
            'payment_status' => ['sometimes', 'in:unpaid,partial,paid,overpaid,refunded'],
            'note' => ['sometimes', 'nullable', 'string'],
            'status_reason' => ['sometimes', 'nullable', 'string', 'max:500'],
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
            'status.in' => 'Invalid order status',
            'delivery_status.in' => 'Invalid delivery status',
            'payment_status.in' => 'Invalid payment status',
            'status_reason.max' => 'Status reason cannot exceed 500 characters',
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

                // Require reason for cancellation
                if ($newStatus === 'cancelled' && empty($this->status_reason)) {
                    $validator->errors()->add('status_reason', 'Reason is required when cancelling an order');
                }
            }

            // Validate delivery status transitions
            if ($this->has('delivery_status')) {
                $currentDeliveryStatus = $order->delivery_status;
                $newDeliveryStatus = $this->delivery_status;

                // Define allowed delivery status transitions
                $allowedDeliveryTransitions = [
                    'pending' => ['picking', 'cancelled'],
                    'picking' => ['delivering', 'cancelled'],
                    'delivering' => ['delivered', 'returning'],
                    'delivered' => ['returning'],
                    'returning' => ['returned'],
                    'returned' => [],
                ];

                if (!in_array($newDeliveryStatus, $allowedDeliveryTransitions[$currentDeliveryStatus] ?? [])) {
                    $validator->errors()->add('delivery_status', "Cannot change delivery status from {$currentDeliveryStatus} to {$newDeliveryStatus}");
                }
            }

            // Validate payment status
            if ($this->has('payment_status')) {
                $newPaymentStatus = $this->payment_status;
                
                // Payment status should generally be calculated automatically
                // Only allow manual override in specific cases
                if (!in_array($newPaymentStatus, ['unpaid', 'partial', 'paid', 'overpaid', 'refunded'])) {
                    $validator->errors()->add('payment_status', 'Invalid payment status');
                }
            }

            // Business logic validations
            if ($this->has('status') && $this->status === 'completed') {
                // Check if all items are available in stock
                foreach ($order->orderItems as $item) {
                    if (!$item->product->canOrder($item->quantity)) {
                        $validator->errors()->add('status', "Cannot complete order: insufficient stock for {$item->product->product_name}");
                    }
                }
            }

            // Validate delivery status consistency with order status
            if ($this->has('delivery_status') && $this->has('status')) {
                $orderStatus = $this->status;
                $deliveryStatus = $this->delivery_status;

                // Cancelled orders should have cancelled delivery
                if ($orderStatus === 'cancelled' && $deliveryStatus !== 'cancelled') {
                    $validator->errors()->add('delivery_status', 'Cancelled orders must have cancelled delivery status');
                }

                // Completed orders should have delivered status
                if ($orderStatus === 'completed' && !in_array($deliveryStatus, ['delivered', 'returned'])) {
                    $validator->errors()->add('delivery_status', 'Completed orders should have delivered or returned delivery status');
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
