<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\V1\UserResource;
use App\Http\Requests\Api\V1\LoginRequest;

class AuthController extends Controller
{
    /**
     * Login user and create tokens (access + refresh)
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 401);
            }

            $user = Auth::user();
            $deviceName = $request->input('device_name', 'YukiMart API');

            // Load relationships for complete user data
            $user->load(['branchShops', 'roles']);

            // Create simple access token
            $accessToken = $user->createToken($deviceName . '_access')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400 // 1 day in seconds
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            // Load relationships for complete user data
            $user->load(['branchShops', 'roles']);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => new UserResource($user)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke all tokens for device)
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $currentToken = $user->currentAccessToken();

            // Get device name to delete both access and refresh tokens
            $deviceName = str_replace(['_access', '_refresh'], '', $currentToken->name);

            // Delete all tokens for this device (both access and refresh)
            $user->tokens()->where('name', 'like', $deviceName . '%')->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
