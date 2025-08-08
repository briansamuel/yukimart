<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get token from Authorization header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Please provide a valid token.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Find the token in database
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token.',
                'error_code' => 'INVALID_TOKEN'
            ], 401);
        }

        // Check if token is expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired.',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        }

        // Get the user
        $user = $accessToken->tokenable;
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'error_code' => 'USER_NOT_FOUND'
            ], 401);
        }

        // Set the authenticated user
        auth()->setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
