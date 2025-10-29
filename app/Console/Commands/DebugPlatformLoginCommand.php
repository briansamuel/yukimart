<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;

class DebugPlatformLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:debug-platform-login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug platform login flow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Debugging Platform Login Flow");
        $this->info("=".str_repeat("=", 50));

        // 1. Check routes
        $this->info("\n1. Route Analysis:");
        $routes = Route::getRoutes();
        
        $adminLoginRoutes = [];
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'admin/login')) {
                $adminLoginRoutes[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'domain' => $route->getDomain(),
                    'methods' => implode('|', $route->methods())
                ];
            }
        }

        foreach ($adminLoginRoutes as $route) {
            $this->info("   📍 {$route['methods']} {$route['uri']}");
            $this->info("      Name: {$route['name']}");
            $this->info("      Action: {$route['action']}");
            $this->info("      Domain: " . ($route['domain'] ?: 'any'));
            $this->info("");
        }

        // 2. Check which route is actually being used
        $this->info("2. Route Resolution Test:");
        try {
            $url = route('admin.login');
            $this->info("   ✅ admin.login resolves to: {$url}");
        } catch (\Exception $e) {
            $this->error("   ❌ admin.login error: " . $e->getMessage());
        }

        try {
            $url = route('admin.login.post');
            $this->info("   ✅ admin.login.post resolves to: {$url}");
        } catch (\Exception $e) {
            $this->error("   ❌ admin.login.post error: " . $e->getMessage());
        }

        // 3. Test user lookup
        $this->info("\n3. User Lookup Test:");
        $email = 'superadmin@yukimart.local';
        
        // Direct query
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->info("   ✅ User found (any tenant): {$user->full_name}");
            $this->info("      Tenant ID: " . ($user->tenant_id ?? 'NULL'));
            $this->info("      Password hash: " . substr($user->password, 0, 20) . '...');
        } else {
            $this->error("   ❌ User not found (any tenant)");
        }

        // Platform-specific query
        $platformUser = User::whereNull('tenant_id')->where('email', $email)->first();
        if ($platformUser) {
            $this->info("   ✅ Platform user found: {$platformUser->full_name}");
            $this->info("      ID: {$platformUser->id}");
            $this->info("      Tenant ID: " . ($platformUser->tenant_id ?? 'NULL'));
        } else {
            $this->error("   ❌ Platform user not found");
        }

        // 4. Test password verification
        $this->info("\n4. Password Verification:");
        if ($platformUser) {
            $password = '123456';
            $isValid = \Hash::check($password, $platformUser->password);
            $this->info("   🔐 Password check: " . ($isValid ? '✅ Valid' : '❌ Invalid'));
            
            if (!$isValid) {
                // Try to update password
                $this->warn("   ⚠️ Password invalid, updating...");
                $platformUser->password = \Hash::make($password);
                $platformUser->save();
                $this->info("   ✅ Password updated");
            }
        }

        // 5. Test guard configuration
        $this->info("\n5. Guard Configuration:");
        $guards = config('auth.guards');
        $providers = config('auth.providers');
        
        if (isset($guards['platform'])) {
            $this->info("   ✅ Platform guard exists:");
            $this->info("      Driver: " . $guards['platform']['driver']);
            $this->info("      Provider: " . $guards['platform']['provider']);
        } else {
            $this->error("   ❌ Platform guard missing");
        }

        if (isset($providers['platform'])) {
            $this->info("   ✅ Platform provider exists:");
            $this->info("      Driver: " . $providers['platform']['driver']);
            $this->info("      Model: " . $providers['platform']['model']);
        } else {
            $this->error("   ❌ Platform provider missing");
        }

        // 6. Test actual authentication
        $this->info("\n6. Authentication Test:");
        $credentials = ['email' => $email, 'password' => '123456'];
        
        try {
            $guard = Auth::guard('platform');
            $result = $guard->attempt($credentials);
            $this->info("   🔐 Platform guard attempt: " . ($result ? '✅ Success' : '❌ Failed'));
            
            if ($result) {
                $user = $guard->user();
                $this->info("      User: {$user->full_name}");
                $guard->logout();
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Platform guard error: " . $e->getMessage());
        }

        // 7. Check middleware
        $this->info("\n7. Middleware Check:");
        $platformRoute = null;
        foreach ($adminLoginRoutes as $route) {
            if (str_contains($route['action'], 'Platform\\AuthController')) {
                $platformRoute = Route::getRoutes()->getByName($route['name']);
                break;
            }
        }

        if ($platformRoute) {
            $middleware = $platformRoute->middleware();
            $this->info("   📋 Platform route middleware:");
            foreach ($middleware as $mw) {
                $this->info("      - {$mw}");
            }
        }

        return 0;
    }
}
