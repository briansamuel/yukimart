<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on guard type
                switch ($guard) {
                    case 'platform':
                        return redirect()->route('platform.admin.dashboard');
                    case 'admin':
                        // Get tenant from request attributes (set by tenant middleware)
                        $tenant = $request->attributes->get('tenant');
                        if ($tenant) {
                            return redirect()->route('tenant.admin.dashboard', ['subdomain' => $tenant->subdomain]);
                        }
                        return redirect(RouteServiceProvider::HOME);
                    case 'web':
                    default:
                        return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }
}
