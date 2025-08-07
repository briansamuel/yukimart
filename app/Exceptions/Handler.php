<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle API requests with JSON responses
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions with proper RESTful responses
     */
    private function handleApiException($request, Throwable $exception)
    {
        // Authentication Exception (401)
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Please provide a valid token.',
                'error_code' => 'UNAUTHENTICATED',
                'errors' => [
                    'token' => ['The provided token is invalid or has expired.']
                ]
            ], 401);
        }

        // Missing Ability Exception (403) - Sanctum token doesn't have required ability
        if ($exception instanceof MissingAbilityException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient permissions. Token does not have required abilities.',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'errors' => [
                    'abilities' => ['The token does not have the required abilities for this action.']
                ]
            ], 403);
        }

        // Access Denied Exception (403)
        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. You do not have permission to access this resource.',
                'error_code' => 'ACCESS_DENIED'
            ], 403);
        }

        // Not Found Exception (404)
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found.',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        // Method Not Allowed Exception (405)
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Method not allowed for this endpoint.',
                'error_code' => 'METHOD_NOT_ALLOWED'
            ], 405);
        }

        // Validation Exception (422)
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'error_code' => 'VALIDATION_FAILED',
                'errors' => $exception->errors()
            ], 422);
        }

        // Check for HTTP exceptions with status codes
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();

            // Handle 401 Unauthorized
            if ($statusCode === 401) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Please provide a valid token.',
                    'error_code' => 'UNAUTHENTICATED',
                    'errors' => [
                        'token' => ['The provided token is invalid or has expired.']
                    ]
                ], 401);
            }

            // Handle other HTTP status codes
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'An error occurred.',
                'error_code' => 'HTTP_ERROR'
            ], $statusCode);
        }

        // Generic Server Error (500)
        return response()->json([
            'status' => 'error',
            'message' => 'Internal server error.',
            'error_code' => 'SERVER_ERROR'
        ], 500);
    }
}
