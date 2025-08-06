<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandlesApiErrors
{
    /**
     * Handle API exceptions and return consistent error responses
     */
    protected function handleApiException(\Exception $e, string $context = '', array $extraData = []): JsonResponse
    {
        // Log the error with context
        $logData = array_merge([
            'context' => $context,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => auth()->id(),
        ], $extraData);

        Log::error("API Error: {$context}", $logData);

        // Handle different exception types
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        }

        // Handle model not found
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy dữ liệu yêu cầu'
            ], 404);
        }

        // Handle authorization errors
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        // Handle database errors
        if ($e instanceof \Illuminate\Database\QueryException) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu. Vui lòng thử lại sau.'
            ], 500);
        }

        // Default error response
        $message = app()->environment('production') 
            ? 'Có lỗi xảy ra. Vui lòng thử lại sau.'
            : $e->getMessage();

        return response()->json([
            'success' => false,
            'message' => $message
        ], 500);
    }

    /**
     * Handle DataTables exceptions
     */
    protected function handleDataTablesException(\Exception $e, array $params = []): JsonResponse
    {
        Log::error('DataTables Error', [
            'error' => $e->getMessage(),
            'params' => $params,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'draw' => $params['draw'] ?? 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => app()->environment('production') 
                ? 'Có lỗi xảy ra khi tải dữ liệu'
                : $e->getMessage()
        ]);
    }

    /**
     * Return success response
     */
    protected function successResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validate request data
     */
    protected function validateRequest(array $data, array $rules, array $messages = []): array
    {
        $validator = validator($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
