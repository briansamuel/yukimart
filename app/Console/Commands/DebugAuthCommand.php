<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DebugAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'debug:auth';

    /**
     * The console command description.
     */
    protected $description = 'Debug authentication issues';

    private $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api/v1';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Debugging Authentication Issues');
        $this->info('===================================');

        try {
            // 1. Check database and user
            $this->checkDatabase();
            
            // 2. Test login endpoint
            $token = $this->testLogin();
            
            // 3. Test token with API
            if ($token) {
                $this->testTokenWithAPI($token);
            }
            
            // 4. Check configurations
            $this->checkConfigurations();
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Debug failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Check database and user
     */
    private function checkDatabase()
    {
        $this->info('📊 Checking Database...');
        
        // Check if user exists
        $user = User::where('email', 'yukimart@gmail.com')->first();
        if ($user) {
            $this->info("  ✅ User found: {$user->name} (ID: {$user->id})");
        } else {
            $this->error("  ❌ User not found");
            return;
        }
        
        // Check personal_access_tokens table
        try {
            $tokenCount = DB::table('personal_access_tokens')->count();
            $this->info("  ✅ personal_access_tokens table exists with {$tokenCount} tokens");
        } catch (\Exception $e) {
            $this->error("  ❌ personal_access_tokens table issue: " . $e->getMessage());
        }
    }

    /**
     * Test login endpoint
     */
    private function testLogin()
    {
        $this->info('🔐 Testing Login Endpoint...');
        
        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        $response = Http::post($this->baseUrl . '/auth/login', $credentials);
        
        $this->info("  📡 Response Status: " . $response->status());
        $this->info("  📄 Response Headers: " . json_encode($response->headers()));
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info("  ✅ Login successful");
            $this->info("  📋 Response structure: " . json_encode(array_keys($data), JSON_PRETTY_PRINT));
            
            $token = $data['data']['access_token'] ?? null;
            if ($token) {
                $this->info("  🔑 Token received: " . substr($token, 0, 50) . "...");
                $this->info("  📏 Token length: " . strlen($token));
                return $token;
            } else {
                $this->error("  ❌ No access token in response");
                $this->info("  📄 Full response: " . json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            $this->error("  ❌ Login failed");
            $this->error("  📄 Response: " . $response->body());
        }
        
        return null;
    }

    /**
     * Test token with API
     */
    private function testTokenWithAPI($token)
    {
        $this->info('🧪 Testing Token with API...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];

        // Test with health endpoint first
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/health');
        $this->info("  🏥 Health endpoint: " . $response->status());
        
        // Test with dashboard stats
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/dashboard/stats');
        $this->info("  📊 Dashboard stats: " . $response->status());
        if (!$response->successful()) {
            $this->error("  📄 Error: " . $response->body());
        }
        
        // Test with products
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/products');
        $this->info("  📦 Products: " . $response->status());
        if (!$response->successful()) {
            $this->error("  📄 Error: " . $response->body());
        }
    }

    /**
     * Check configurations
     */
    private function checkConfigurations()
    {
        $this->info('⚙️ Checking Configurations...');
        
        // Check JWT config
        $jwtSecret = config('jwt.secret');
        $this->info("  🔐 JWT Secret: " . ($jwtSecret ? 'Set' : 'Not set'));
        
        // Check auth guards
        $guards = config('auth.guards');
        $this->info("  🛡️ Auth Guards: " . json_encode(array_keys($guards)));
        
        // Check API guard
        $apiGuard = config('auth.guards.api');
        $this->info("  🔑 API Guard: " . json_encode($apiGuard));
        
        // Check Sanctum config
        $sanctumExpiration = config('sanctum.expiration');
        $this->info("  ⏰ Sanctum Expiration: " . ($sanctumExpiration ?? 'null (no expiration)'));
        
        // Check middleware
        $middleware = app('router')->getMiddleware();
        $this->info("  🚦 Available Middleware: " . json_encode(array_keys($middleware)));
    }
}
