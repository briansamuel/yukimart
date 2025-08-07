<?php

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateCustomerRequest extends FormRequest
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
            'customer_code' => ['nullable', 'string', 'max:50', 'unique:customers'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:customers'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'area' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', 'string', 'max:100'],
            'customer_group' => ['nullable', 'string', 'max:100'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive,blocked'],
            'notes' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'points' => ['nullable', 'integer', 'min:0'],
            'branch_shop_id' => ['nullable', 'exists:branch_shops,id'],
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
            'name.required' => 'Customer name is required',
            'name.max' => 'Customer name cannot exceed 255 characters',
            'customer_code.unique' => 'Customer code must be unique',
            'phone.unique' => 'Phone number must be unique',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email must be unique',
            'email.max' => 'Email cannot exceed 255 characters',
            'facebook.max' => 'Facebook cannot exceed 255 characters',
            'address.max' => 'Address cannot exceed 500 characters',
            'area.max' => 'Area cannot exceed 255 characters',
            'customer_type.max' => 'Customer type cannot exceed 100 characters',
            'customer_group.max' => 'Customer group cannot exceed 100 characters',
            'tax_code.max' => 'Tax code cannot exceed 50 characters',
            'status.in' => 'Status must be one of: active, inactive, blocked',
            'birthday.date' => 'Birthday must be a valid date',
            'birthday.before' => 'Birthday must be before today',
            'points.integer' => 'Points must be an integer',
            'points.min' => 'Points must be greater than or equal to 0',
            'branch_shop_id.exists' => 'Selected branch shop does not exist',
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
            // Validate that at least phone or email is provided
            if (empty($this->phone) && empty($this->email)) {
                $validator->errors()->add('phone', 'Either phone number or email must be provided');
                $validator->errors()->add('email', 'Either phone number or email must be provided');
            }

            // Validate phone format (Vietnamese phone numbers)
            if ($this->phone && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $this->phone)) {
                $validator->errors()->add('phone', 'Phone number format is invalid');
            }

            // Validate tax code format (Vietnamese tax code)
            if ($this->tax_code && !preg_match('/^[0-9]{10,13}$/', $this->tax_code)) {
                $validator->errors()->add('tax_code', 'Tax code must be 10-13 digits');
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
