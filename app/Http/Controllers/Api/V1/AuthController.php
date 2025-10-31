<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiOptimizationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Http\Resources\V1\UserResource;
use App\Http\Requests\Api\V1\LoginRequest;

class AuthController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Login user and create tokens with optimization
     */
    public function login(LoginRequest $request)
    {
        $startTime = microtime(true);

        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return $this->errorResponse('Invalid credentials', 401, [
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }

            $user = Auth::user();
            $deviceName = $request->input('device_name', 'YukiMart API');

            // Cache user data for faster subsequent requests (10 minutes)
            $cacheKey = "user_profile_{$user->id}";
            $userData = Cache::remember($cacheKey, 600, function() use ($user) {
                $user->load(['branchShops', 'roles']);
                return new UserResource($user);
            });

            // Create simple access token
            $accessToken = $user->createToken($deviceName . '_access')->plainTextToken;

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Apply field selection if specified
            if (!empty($fields)) {
                $userData = $this->applyFieldSelection($userData, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $userData,
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400 // 1 day in seconds
                ],
                'meta' => [
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 10, 1); // 10 login attempts per minute

            return $response;

        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return $this->errorResponse('Login failed', 500);
        }
    }

    /**
     * Get user profile with caching
     */
    public function profile(Request $request)
    {
        $startTime = microtime(true);

        try {
            $user = Auth::user();

            // Generate cache key for user profile
            $cacheKey = "user_profile_{$user->id}";

            // Try to get cached profile (10 minutes cache)
            $userData = Cache::remember($cacheKey, 600, function() use ($user) {
                $user->load(['branchShops', 'roles']);
                return new UserResource($user);
            });

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Apply field selection if specified
            if (!empty($fields)) {
                $userData = $this->applyFieldSelection($userData, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => $userData
                ],
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Get profile failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve profile', 500);
        }
    }



    /**
     * Logout user with cache invalidation
     */
    public function logout(Request $request)
    {
        $startTime = microtime(true);

        try {
            $user = $request->user();
            $currentToken = $user->currentAccessToken();

            // Get device name to delete both access and refresh tokens
            $deviceName = str_replace(['_access', '_refresh'], '', $currentToken->name);

            // Delete all tokens for this device (both access and refresh)
            $user->tokens()->where('name', 'like', $deviceName . '%')->delete();

            // Invalidate user profile cache
            $this->invalidateUserCaches($user->id);

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Logout successful',
                'meta' => [
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->errorResponse('Logout failed', 500);
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $currentToken = $request->user()->currentAccessToken();

            // Check if current token has refresh ability
            if (!$currentToken->can('refresh')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid refresh token. Please login again.'
                ], 401);
            }

            $deviceName = str_replace('_refresh', '', $currentToken->name);

            // Delete old access tokens for this device
            $user->tokens()->where('name', $deviceName . '_access')->delete();

            // Create new access token (expires in 30 days)
            $accessToken = $user->createToken($deviceName . '_access', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400 // 1 day in seconds
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register new user (if registration is enabled)
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('YukiMart API Token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'user'
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return $this->errorResponse('Registration failed', 500);
        }
    }

    /**
     * Invalidate user-related caches
     */
    private function invalidateUserCaches($userId)
    {
        // Clear user profile cache
        Cache::forget("user_profile_{$userId}");

        // Clear authentication statistics cache
        Cache::forget("auth_statistics_user_{$userId}");

        // Clear any other user-related caches
        $cacheKeys = [
            "user_permissions_{$userId}",
            "user_devices_{$userId}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get authentication statistics with caching
     */
    public function statistics()
    {
        $startTime = microtime(true);

        try {
            $user = Auth::user();

            // Generate cache key for statistics
            $cacheKey = "auth_statistics_user_{$user->id}";

            // Try to get cached statistics (15 minutes cache)
            $stats = Cache::remember($cacheKey, 900, function() use ($user) {
                return $this->calculateAuthStatistics($user->id);
            });

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Authentication statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Authentication statistics failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve authentication statistics', 500);
        }
    }

    /**
     * Calculate authentication statistics for a user
     */
    private function calculateAuthStatistics($userId)
    {
        // Get user's tokens (active sessions)
        $activeTokens = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $userId)
            ->where('tokenable_type', User::class)
            ->whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->count();

        // Get recent login activity (last 30 days)
        $recentLogins = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $userId)
            ->where('tokenable_type', User::class)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Get device information
        $devices = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $userId)
            ->where('tokenable_type', User::class)
            ->whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->get()
            ->map(function($token) {
                return [
                    'device_name' => str_replace(['_access', '_refresh'], '', $token->name),
                    'last_used' => $token->last_used_at,
                    'created_at' => $token->created_at
                ];
            })
            ->unique('device_name')
            ->values();

        return [
            'active_sessions' => $activeTokens,
            'recent_logins_30_days' => $recentLogins,
            'active_devices' => $devices->count(),
            'devices' => $devices,
            'last_login' => $devices->max('created_at'),
            'account_security' => [
                'two_factor_enabled' => false, // Placeholder for future 2FA implementation
                'password_last_changed' => null, // Placeholder for password change tracking
                'suspicious_activity' => false // Placeholder for security monitoring
            ]
        ];
    }
}
