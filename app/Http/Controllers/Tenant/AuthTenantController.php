<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\TenantUser;

class AuthTenantController extends Controller
{
    /**
     * Show the tenant login form
     */
    public function showLoginForm(Request $request)
    {
        Log::info('Tenant showLoginForm called', [
            'controller' => get_class($this),
            'method' => __METHOD__,
            'url' => $request->url(),
            'host' => $request->getHost(),
        ]);

        $tenant = $request->attributes->get('tenant');

        return view('tenant.auth.login', [
            'tenant' => $tenant,
            'isPlatform' => false,
            'pageTitle' => 'Admin Login',
            'siteName' => $tenant->name ?? 'YukiMart'
        ]);
    }

    /**
     * Handle tenant login
     */
    public function login(Request $request)
    {
        Log::info('Tenant login attempt', [
            'email' => $request->input('email'),
            'url' => $request->url(),
            'route' => $request->route()->getName(),
            'controller' => get_class($this),
            'ip' => $request->ip(),
            'is_ajax' => $request->ajax(),
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            $errorMessage = 'Tenant not found. Please check the URL.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 404);
            }

            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        // Find user by email and tenant
        $user = User::where('email', $request->email)
                   ->where('tenant_id', $tenant->id)
                   ->first();

        if (!$user) {
            $errorMessage = 'User not found for this tenant.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            $errorMessage = 'The provided credentials are incorrect.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            $errorMessage = 'Your account is not active.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        // Check tenant-user relationship
        $tenantUser = TenantUser::where('tenant_id', $tenant->id)
                               ->where('user_id', $user->id)
                               ->where('is_active', true)
                               ->first();

        if (!$tenantUser) {
            $errorMessage = 'You do not have access to this tenant.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 403);
            }

            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        // Login the user using tenant guard
        Auth::guard('tenant')->login($user, $request->boolean('remember'));

        // Regenerate session to prevent fixation attacks
        $request->session()->regenerate();

        // Store tenant context in session
        session([
            'current_tenant_id' => $tenant->id,
            'current_tenant_role' => $tenantUser->role,
        ]);

        // Log successful login
        Log::info('Tenant login successful', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'role' => $tenantUser->role,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => '/admin/dashboard',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'role' => $tenantUser->role,
                ]
            ]);
        }

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Handle tenant logout
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('tenant')->user();
        $tenant = $request->attributes->get('tenant');

        // Log logout
        Log::info('Tenant logout', [
            'user_id' => $user ? $user->id : null,
            'tenant_id' => $tenant ? $tenant->id : null,
            'ip' => $request->ip(),
        ]);

        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Show tenant dashboard
     */
    public function dashboard(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $user = Auth::guard('tenant')->user();
        $tenantUser = null;

        if ($user && $tenant) {
            $tenantUser = TenantUser::where('tenant_id', $tenant->id)
                                   ->where('user_id', $user->id)
                                   ->first();
        }

        return view('admin.dashboard', [
            'tenant' => $tenant,
            'user' => $user,
            'tenantUser' => $tenantUser,
            'pageTitle' => 'Dashboard',
        ]);
    }

    /**
     * Check authentication status
     */
    public function checkAuth(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $user = Auth::guard('tenant')->user();

        return response()->json([
            'authenticated' => Auth::guard('tenant')->check(),
            'user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
            ] : null,
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ] : null,
            'session_tenant_id' => session('current_tenant_id'),
            'session_tenant_role' => session('current_tenant_role'),
        ]);
    }
}

