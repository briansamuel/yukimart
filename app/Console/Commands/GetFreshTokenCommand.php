<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetFreshTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'auth:fresh-token';

    /**
     * The console command description.
     */
    protected $description = 'Get a fresh authentication token and test APIs';

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
        $this->info('🔑 Getting Fresh Authentication Token');
        $this->info('====================================');

        try {
            // 1. Login and get fresh token
            $token = $this->getFreshToken();
            
            if (!$token) {
                $this->error('❌ Failed to get token');
                return 1;
            }
            
            // 2. Test with fresh token
            $this->testWithFreshToken($token);
            
            // 3. Show token for manual testing
            $this->info('🔑 Fresh Token for Manual Testing:');
            $this->line($token);
            $this->info('');
            $this->info('📋 Example Usage:');
            $this->line("curl -H 'Authorization: Bearer {$token}' -H 'Accept: application/json' '{$this->baseUrl}/products'");
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get fresh token
     */
    private function getFreshToken()
    {
        $this->info('🔐 Logging in...');
        
        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        $response = Http::post($this->baseUrl . '/auth/login', $credentials);
        
        if ($response->successful()) {
            $data = $response->json();
            $token = $data['data']['access_token'] ?? null;
            
            if ($token) {
                $this->info('✅ Fresh token obtained');
                $this->info('📏 Token length: ' . strlen($token));
                return $token;
            } else {
                $this->error('❌ No token in response');
                return null;
            }
        } else {
            $this->error('❌ Login failed: ' . $response->body());
            return null;
        }
    }

    /**
     * Test with fresh token
     */
    private function testWithFreshToken($token)
    {
        $this->info('🧪 Testing APIs with Fresh Token...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];

        $endpoints = [
            'Health' => '/health',
            'Dashboard Stats' => '/dashboard/stats',
            'Products' => '/products',
            'Customers' => '/customers',
            'Notifications' => '/notifications'
        ];

        foreach ($endpoints as $name => $endpoint) {
            $response = Http::withHeaders($headers)->get($this->baseUrl . $endpoint);
            
            if ($response->successful()) {
                $this->info("  ✅ {$name}: Success ({$response->status()})");
            } else {
                $this->error("  ❌ {$name}: Failed ({$response->status()})");
                if ($response->status() === 401) {
                    $this->error("    🔒 Unauthorized - Token issue");
                }
            }
        }
    }
}
