<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SyncPostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:sync
                            {--api-key= : Postman API key}
                            {--collection-id= : Postman collection ID}
                            {--capture-examples : Capture live API examples}
                            {--update-collection : Update existing collection}
                            {--export-only : Only export to local files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Dashboard APIs to Postman collection with real examples';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting Postman Collection Sync...');

        // Check options
        $captureExamples = $this->option('capture-examples');
        $updateCollection = $this->option('update-collection');
        $exportOnly = $this->option('export-only');

        if ($captureExamples) {
            $this->captureApiExamples();
        }

        if ($exportOnly) {
            $this->exportCollectionFiles();
            return Command::SUCCESS;
        }

        if ($updateCollection) {
            $this->updatePostmanCollection();
        } else {
            $this->exportCollectionFiles();
        }

        $this->info('✅ Postman sync completed successfully!');
        return Command::SUCCESS;
    }

    /**
     * Capture live API examples
     */
    protected function captureApiExamples()
    {
        $this->info('📊 Capturing live API examples...');

        // Get authentication token
        $token = $this->getAuthToken();
        if (!$token) {
            $this->error('❌ Failed to get authentication token');
            return;
        }

        $this->info("🔑 Got authentication token: " . substr($token, 0, 20) . "...");

        // Define endpoints to capture
        $endpoints = $this->getDashboardEndpoints();

        $examples = [];
        $captured = 0;

        foreach ($endpoints as $endpoint) {
            $this->line("Capturing: {$endpoint['name']}");

            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$token}"
                    ])
                    ->get($endpoint['url']);

                if ($response->successful()) {
                    $examples[] = [
                        'name' => $endpoint['name'],
                        'url' => $endpoint['url'],
                        'method' => $endpoint['method'],
                        'description' => $endpoint['description'],
                        'response' => $response->json(),
                        'status_code' => $response->status()
                    ];

                    $this->info("  ✅ Captured successfully");
                    $captured++;
                } else {
                    $this->warn("  ⚠️  Failed with status: {$response->status()}");
                }

            } catch (\Exception $e) {
                $this->warn("  ❌ Error: " . $e->getMessage());
            }
        }

        // Save examples
        $examplesPath = storage_path('testing/dashboard-api-examples.json');
        File::ensureDirectoryExists(dirname($examplesPath));
        File::put($examplesPath, json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("📁 Saved {$captured} examples to: {$examplesPath}");
    }

    /**
     * Get authentication token
     */
    protected function getAuthToken()
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post(config('app.url') . '/api/v1/auth/login', [
                    'email' => 'yukimart@gmail.com',
                    'password' => '123456',
                    'device_name' => 'Artisan Postman Sync'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['access_token'] ?? null;
            }

        } catch (\Exception $e) {
            $this->error("Login error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get dashboard endpoints to capture
     */
    protected function getDashboardEndpoints()
    {
        $baseUrl = config('app.url');

        return [
            [
                'name' => 'Dashboard Stats - Today',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=today",
                'method' => 'GET',
                'description' => 'Get today\'s dashboard statistics'
            ],
            [
                'name' => 'Dashboard Stats - Month',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=month",
                'method' => 'GET',
                'description' => 'Get current month dashboard statistics'
            ],
            [
                'name' => 'Dashboard Stats - Year',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=year",
                'method' => 'GET',
                'description' => 'Get current year dashboard statistics'
            ],
            [
                'name' => 'Revenue Chart - Month',
                'url' => "{$baseUrl}/admin/dashboard/revenue-chart?period=month",
                'method' => 'GET',
                'description' => 'Get monthly revenue chart data'
            ],
            [
                'name' => 'Revenue Chart - Today',
                'url' => "{$baseUrl}/admin/dashboard/revenue-chart?period=today",
                'method' => 'GET',
                'description' => 'Get today\'s revenue chart data'
            ],
            [
                'name' => 'Top Products - Revenue',
                'url' => "{$baseUrl}/admin/dashboard/top-products?type=revenue&period=month&limit=10",
                'method' => 'GET',
                'description' => 'Get top products by revenue'
            ],
            [
                'name' => 'Top Products - Quantity',
                'url' => "{$baseUrl}/admin/dashboard/top-products?type=quantity&period=month&limit=10",
                'method' => 'GET',
                'description' => 'Get top products by quantity sold'
            ]
        ];
    }
}
