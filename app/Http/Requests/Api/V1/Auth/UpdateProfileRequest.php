<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id();
        
        return [
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $userId],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:18'],
            'address' => ['sometimes', 'string', 'max:500'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'string', 'max:255'],
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
            'username.unique' => 'Username is already taken',
            'full_name.max' => 'Full name cannot exceed 255 characters',
            'phone.max' => 'Phone number cannot exceed 18 characters',
            'address.max' => 'Address cannot exceed 500 characters',
            'birth_date.before' => 'Birth date must be before today',
            'description.max' => 'Description cannot exceed 1000 characters',
            'avatar.max' => 'Avatar URL cannot exceed 255 characters',
        ];
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
