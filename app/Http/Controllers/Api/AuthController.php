<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API Authentication Controller
 * 
 * Handles API authentication including login, registration, token management,
 * and tenant context for multi-tenant system.
 */
class AuthController extends Controller
{
    /**
     * Login user and return API token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'tenant_subdomain' => 'nullable|string|exists:tenants,subdomain',
            'device_name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rate limiting
        $key = 'api_login:' . $request->ip();
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        
        if ($limiter->tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.'
            ], 429);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $limiter->hit($key, 900); // 15 minutes lockout
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not active'
                ], 401);
            }

            // Handle tenant context
            $tenant = null;
            $tenantUser = null;
            
            if ($request->tenant_subdomain) {
                $tenant = Tenant::where('subdomain', $request->tenant_subdomain)
                               ->where('status', 'active')
                               ->first();
                
                if (!$tenant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid tenant'
                    ], 400);
                }

                // Check if user has access to this tenant
                $tenantUser = TenantUser::where('tenant_id', $tenant->id)
                                      ->where('user_id', $user->id)
                                      ->where('is_active', true)
                                      ->first();

                // Platform users can access any tenant
                $platformRoles = ['admin', 'superadmin', 'dev', 'manager'];
                $userRoles = $user->roles()->pluck('name')->toArray();
                $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));

                if (!$hasPlatformRole && !$tenantUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this tenant'
                    ], 403);
                }
            }

            // Create API token
            $deviceName = $request->device_name ?? 'API Client';
            $token = $user->createToken($deviceName, ['*'])->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful login
            \Log::info('API Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'tenant_id' => $tenant->id ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $response = [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'status' => $user->status,
                        'last_login_at' => $user->last_login_at
                    ]
                ]
            ];

            // Add tenant information if available
            if ($tenant) {
                $response['data']['tenant'] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'status' => $tenant->status
                ];

                if ($tenantUser) {
                    $response['data']['tenant_role'] = $tenantUser->role;
                    $response['data']['tenant_permissions'] = json_decode($tenantUser->permissions, true);
                }
            }

            // Add user roles
            $response['data']['user']['roles'] = $user->roles()->pluck('name')->toArray();

            return response()->json($response, 200);

        } catch (\Exception $e) {
            \Log::error('API Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            \Log::info('API Logout successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('API Logout failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * Get authenticated user information
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('roles');

            // Get tenant information if available
            $tenantId = $request->header('X-Tenant-ID');
            $tenant = null;
            $tenantUser = null;

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    $tenantUser = TenantUser::where('tenant_id', $tenant->id)
                                          ->where('user_id', $user->id)
                                          ->first();
                }
            }

            $response = [
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'avatar' => $user->avatar,
                        'status' => $user->status,
                        'last_login_at' => $user->last_login_at,
                        'roles' => $user->roles->pluck('name')->toArray()
                    ]
                ]
            ];

            if ($tenant && $tenantUser) {
                $response['data']['tenant'] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'role' => $tenantUser->role,
                    'permissions' => json_decode($tenantUser->permissions, true),
                    'is_active' => $tenantUser->is_active
                ];
            }

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user information'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $data = $validator->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    \Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'avatar' => $user->avatar
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Revoke all tokens except current
            $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $deviceName = $request->header('User-Agent', 'API Client');
            $token = $user->createToken($deviceName, ['*'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token'
            ], 500);
        }
    }
}
