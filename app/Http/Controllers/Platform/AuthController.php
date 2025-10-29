<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the platform login form
     */
    public function showLoginForm()
    {
        // Debug logging
        Log::info('Platform showLoginForm called', [
            'controller' => get_class($this),
            'method' => __METHOD__,
            'url' => request()->url(),
            'host' => request()->getHost(),
        ]);

        // Check if already authenticated
        if (Auth::guard('platform')->check()) {
            return redirect()->route('platform.dashboard');
        }

        return view('auth.unified-login', [
            'isPlatform' => true,
            'pageTitle' => 'Platform Login',
            'siteName' => 'YukiMart Platform'
        ]);
    }

    /**
     * Handle platform login (supports both Ajax and regular form)
     */
    public function login(Request $request)
    {
        // Debug logging
        Log::info('Platform login attempt', [
            'email' => $request->input('email'),
            'url' => $request->url(),
            'route' => $request->route()->getName(),
            'controller' => get_class($this),
            'ip' => $request->ip(),
            'is_ajax' => $request->ajax(),
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
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

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt platform authentication
        if (Auth::guard('platform')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::guard('platform')->user();

            // Log successful login
            Log::info('Platform user logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('platform.admin.dashboard'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                    ]
                ]);
            }

            return redirect()->intended(route('platform.admin.dashboard'));
        }

        // Authentication failed
        Log::warning('Platform login failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        throw ValidationException::withMessages([
            'email' => ['Email hoặc mật khẩu không chính xác.'],
        ]);
    }

    /**
     * Handle platform logout
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('platform')->user();
        
        if ($user) {
            \Log::info('Platform user logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }

        Auth::guard('platform')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }

    /**
     * Show platform dashboard
     */
    public function dashboard()
    {
        $user = Auth::guard('platform')->user();
        
        return view('platform.dashboard', compact('user'));
    }
}
