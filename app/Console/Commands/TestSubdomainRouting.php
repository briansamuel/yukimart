<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class TestSubdomainRouting extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:test-subdomain';

    /**
     * The console command description.
     */
    protected $description = 'Test subdomain routing functionality for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Testing Subdomain Routing...');
        $this->newLine();

        try {
            // Get all active tenants
            $tenants = Tenant::where('status', 'active')->get();
            
            if ($tenants->isEmpty()) {
                $this->error('No active tenants found!');
                return 1;
            }

            $this->info("Found {$tenants->count()} active tenants to test:");
            $this->newLine();

            $results = [];
            
            foreach ($tenants as $tenant) {
                $this->line("🏢 Testing {$tenant->name} ({$tenant->subdomain}.yukimart.local)...");
                
                $result = $this->testTenantSubdomain($tenant);
                $results[] = $result;
                
                if ($result['success']) {
                    $this->info("   ✅ {$tenant->name}: Subdomain routing working");
                } else {
                    $this->error("   ❌ {$tenant->name}: {$result['error']}");
                }
            }

            $this->newLine();
            $this->displaySummary($results);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with exception:');
            $this->error("   {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Test subdomain for a specific tenant
     */
    private function testTenantSubdomain(Tenant $tenant): array
    {
        $subdomain = $tenant->subdomain ?: $tenant->slug;
        $url = "http://{$subdomain}.yukimart.local/api/tenant/info";
        
        try {
            // Test API endpoint
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['tenant']) && $data['tenant']['id'] === $tenant->id) {
                    return [
                        'success' => true,
                        'tenant' => $tenant,
                        'subdomain' => $subdomain,
                        'url' => $url,
                        'response_time' => $response->transferStats?->getTransferTime() ?? 0,
                        'tenant_data' => $data['tenant'],
                    ];
                } else {
                    return [
                        'success' => false,
                        'tenant' => $tenant,
                        'subdomain' => $subdomain,
                        'url' => $url,
                        'error' => 'Tenant ID mismatch in response',
                        'expected_id' => $tenant->id,
                        'actual_id' => $data['tenant']['id'] ?? 'null',
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'tenant' => $tenant,
                    'subdomain' => $subdomain,
                    'url' => $url,
                    'error' => "HTTP {$response->status()}: {$response->body()}",
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'tenant' => $tenant,
                'subdomain' => $subdomain,
                'url' => $url,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Display test summary
     */
    private function displaySummary(array $results): void
    {
        $successful = array_filter($results, fn($r) => $r['success']);
        $failed = array_filter($results, fn($r) => !$r['success']);
        
        $this->info('📊 Test Summary:');
        $this->line("   Total Tenants: " . count($results));
        $this->line("   Successful: " . count($successful));
        $this->line("   Failed: " . count($failed));
        $this->newLine();

        if (!empty($successful)) {
            $this->info('✅ Successful Subdomains:');
            foreach ($successful as $result) {
                $responseTime = isset($result['response_time']) ? number_format($result['response_time'] * 1000, 2) . 'ms' : 'N/A';
                $this->line("   {$result['subdomain']}.yukimart.local - {$result['tenant']->name} ({$responseTime})");
            }
            $this->newLine();
        }

        if (!empty($failed)) {
            $this->error('❌ Failed Subdomains:');
            foreach ($failed as $result) {
                $this->line("   {$result['subdomain']}.yukimart.local - {$result['error']}");
            }
            $this->newLine();
        }

        $this->info('🌐 Subdomain URLs to Test Manually:');
        foreach ($results as $result) {
            $status = $result['success'] ? '✅' : '❌';
            $this->line("   {$status} http://{$result['subdomain']}.yukimart.local/tenant/subdomain-test");
        }
        $this->newLine();

        $this->info('🔑 Login Credentials for Testing:');
        foreach ($results as $result) {
            if ($result['success']) {
                $tenant = $result['tenant'];
                $this->line("   {$tenant->name}:");
                $this->line("     Admin: admin@{$tenant->slug}.local / 123456");
                $this->line("     Manager: manager@{$tenant->slug}.local / 123456");
            }
        }
        $this->newLine();

        if (count($successful) === count($results)) {
            $this->info('🎉 All subdomain routing tests passed!');
        } else {
            $this->warn('⚠️  Some subdomain routing tests failed. Check the errors above.');
        }

        $this->info('💡 Next Steps:');
        $this->line('   1. Test subdomain URLs manually in browser');
        $this->line('   2. Test login functionality on each subdomain');
        $this->line('   3. Verify tenant data isolation');
        $this->line('   4. Test admin panel access via subdomains');
        $this->newLine();
    }
}
