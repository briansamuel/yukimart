<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestDashboardStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:dashboard-stats';

    /**
     * The console command description.
     */
    protected $description = 'Test dashboard stats API with different periods';

    private $baseUrl;
    private $authToken;

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
        $this->info('🧪 Testing Dashboard Stats API with Period Filters');
        $this->info('==================================================');

        try {
            // 1. Authenticate
            $this->authenticate();
            
            // 2. Test different periods
            $periods = ['today', 'yesterday', 'month', 'last_month', 'year'];
            
            foreach ($periods as $period) {
                $this->testPeriod($period);
            }
            
            $this->info('✅ All tests completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Authenticate and get token
     */
    private function authenticate()
    {
        $this->info('🔐 Authenticating...');
        
        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        $response = Http::post($this->baseUrl . '/auth/login', $credentials);
        
        if ($response->successful()) {
            $data = $response->json();
            $this->authToken = $data['data']['access_token'] ?? null;
            
            if (!$this->authToken) {
                throw new \Exception('No access token received');
            }
            
            $this->info('✅ Authentication successful');
        } else {
            throw new \Exception('Authentication failed: ' . $response->body());
        }
    }

    /**
     * Test dashboard stats for specific period
     */
    private function testPeriod($period)
    {
        $this->line("📊 Testing period: {$period}");
        
        try {
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authToken
            ];

            $url = $this->baseUrl . '/dashboard/stats?period=' . $period;
            $this->line("  🔗 URL: {$url}");

            $response = Http::withHeaders($headers)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();

                $this->line("  ✅ Success ({$response->status()})");

                // Debug: Show full response structure
                if ($period === 'today') {
                    $this->line("  🔍 Debug - Raw response body:");
                    $this->line("  " . $response->body());
                    $this->line("  🔍 Debug - JSON decoded:");
                    $this->line("  " . json_encode($data, JSON_PRETTY_PRINT));
                }

                $this->line("  📈 Period: " . ($data['data']['period_name'] ?? 'N/A'));
                $this->line("  💰 Revenue: " . number_format($data['data']['period_revenue'] ?? 0) . ' VNĐ');
                $this->line("  📦 Orders: " . ($data['data']['period_orders'] ?? 0));
                $this->line("  📄 Invoices: " . ($data['data']['period_invoices'] ?? 0));
                $this->line("  👥 Customers: " . ($data['data']['period_customers'] ?? 0));
                $this->line("  📅 Date Range: " . ($data['meta']['date_range']['start'] ?? 'N/A') . ' to ' . ($data['meta']['date_range']['end'] ?? 'N/A'));
                $this->line("");
                
            } else {
                $this->line("  ❌ Failed ({$response->status()})");
                $this->line("  Error: " . $response->body());
                $this->line("");
            }
            
        } catch (\Exception $e) {
            $this->line("  ❌ Error: " . $e->getMessage());
            $this->line("");
        }
    }
}
