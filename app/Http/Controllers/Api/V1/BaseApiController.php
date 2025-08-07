<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseApiController extends Controller
{
    /**
     * API version
     */
    protected string $version = 'v1';

    /**
     * Default pagination limit
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum pagination limit
     */
    protected int $maxPerPage = 100;

    /**
     * Return success response
     */
    protected function successResponse($data = null, string $message = 'Request completed successfully', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $this->getResponseMeta()
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message = 'Request failed', $errors = null, string $errorCode = 'REQUEST_FAILED', int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'meta' => $this->getResponseMeta()
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more' => $paginator->hasMorePages(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ]
            ],
            'meta' => $this->getResponseMeta()
        ];

        return response()->json($response);
    }

    /**
     * Return validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 'VALIDATION_ERROR', 422);
    }

    /**
     * Return not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, 'RESOURCE_NOT_FOUND', 404);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse($message, null, 'AUTHORIZATION_FAILED', 403);
    }

    /**
     * Return server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, null, 'SERVER_ERROR', 500);
    }

    /**
     * Get response metadata
     */
    protected function getResponseMeta(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'version' => $this->version,
            'request_id' => request()->header('X-Request-ID', uniqid()),
        ];
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = (int) $request->get('per_page', $this->defaultPerPage);
        $perPage = min($perPage, $this->maxPerPage);
        $perPage = max($perPage, 1);

        return [
            'per_page' => $perPage,
            'page' => (int) $request->get('page', 1)
        ];
    }

    /**
     * Get search parameters from request
     */
    protected function getSearchParams(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ];
    }

    /**
     * Get filter parameters from request
     */
    protected function getFilterParams(Request $request): array
    {
        return [
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'branch_id' => $request->get('branch_id'),
            'customer_id' => $request->get('customer_id'),
        ];
    }

    /**
     * Log API request
     */
    protected function logApiRequest(Request $request, $response = null): void
    {
        if (config('api.logging.enabled')) {
            $logData = [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => $request->header('X-Request-ID'),
            ];

            if (config('api.logging.log_requests')) {
                $logData['request_data'] = $request->except(['password', 'password_confirmation']);
            }

            if ($response && config('api.logging.log_responses')) {
                $logData['response_status'] = $response->getStatusCode();
                $logData['response_data'] = $response->getData();
            }

            logger()->channel(config('api.logging.channel', 'api'))->info('API Request', $logData);
        }
    }

    /**
     * Handle API exceptions
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        // Log the exception
        logger()->channel(config('api.logging.channel', 'api'))->error('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'request_id' => request()->header('X-Request-ID'),
        ]);

        // Return appropriate response based on exception type
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors(), $e->getMessage());
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Resource not found');
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->errorResponse('Authentication required', null, 'AUTHENTICATION_FAILED', 401);
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->unauthorizedResponse($e->getMessage());
        }

        // For production, don't expose internal errors
        if (app()->environment('production')) {
            return $this->serverErrorResponse('An error occurred while processing your request');
        }

        return $this->serverErrorResponse($e->getMessage());
    }

    /**
     * Validate API version
     */
    protected function validateApiVersion(Request $request): bool
    {
        $acceptHeader = $request->header('Accept', '');
        
        if (str_contains($acceptHeader, 'application/vnd.yukimart.')) {
            preg_match('/application\/vnd\.yukimart\.(\w+)\+json/', $acceptHeader, $matches);
            $requestedVersion = $matches[1] ?? null;
            
            if ($requestedVersion && !in_array($requestedVersion, config('api.version.supported', ['v1']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has permission for resource
     */
    protected function checkPermission(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Check if user has the specific permission
        return $user->hasPermission($permission);
    }

    /**
     * Get current authenticated user
     */
    protected function getCurrentUser()
    {
        return auth()->user();
    }

    /**
     * Get user's branch shops
     */
    protected function getUserBranches()
    {
        $user = $this->getCurrentUser();
        return $user ? $user->branchShops : collect();
    }
}
