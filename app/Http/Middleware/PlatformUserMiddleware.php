<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformUserMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures only platform users (superadmin, admin, dev, manager)
     * can access platform management routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated as admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access platform management.');
        }

        $user = Auth::guard('admin')->user();
        
        // Check if user has platform roles
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager', 'shop_manager'];
        
        // Check user roles from roles table
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        
        if (!$hasPlatformRole) {
            // If user doesn't have platform role, redirect to tenant dashboard
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access platform management.');
        }

        // Set platform mode in session
        session(['is_platform_mode' => true]);
        
        return $next($request);
    }
}
