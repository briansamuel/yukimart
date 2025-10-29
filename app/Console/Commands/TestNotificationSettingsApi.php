<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TestNotificationSettingsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification-settings-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Notification Settings API endpoints and generate Postman collection data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Notification Settings API Endpoints...');

        // Step 1: Get authentication token
        $token = $this->getAuthToken();
        if (!$token) {
            $this->error('❌ Failed to get authentication token');
            return 1;
        }

        $this->info("✅ Authentication successful");

        // Step 2: Test all notification settings endpoints
        $this->testNotificationSettingsEndpoints($token);

        // Step 3: Generate Postman collection data
        $this->generatePostmanData($token);

        $this->info('🎉 Notification Settings API testing completed!');
        return 0;
    }

    /**
     * Get authentication token
     */
    private function getAuthToken()
    {
        $this->info('🔐 Getting authentication token...');

        try {
            $response = Http::post('http://yukimart.local/api/v1/auth/login', [
                'email' => 'yukimart@gmail.com',
                'password' => '123456',
                'device_name' => 'API Test'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['token'] ?? null;
            }

            $this->error('Login failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            $this->error('Login error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Test notification settings endpoints
     */
    private function testNotificationSettingsEndpoints($token)
    {
        $this->info('📋 Testing Notification Settings endpoints...');

        $baseUrl = 'http://yukimart.local/api/v1';
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        // Test endpoints
        $endpoints = [
            [
                'method' => 'GET',
                'url' => '/notification-settings',
                'name' => 'Get Notification Settings',
                'description' => 'Retrieve user notification settings grouped by category'
            ],
            [
                'method' => 'PUT',
                'url' => '/notification-settings',
                'name' => 'Update Notification Settings',
                'description' => 'Update user notification settings',
                'body' => [
                    'settings' => [
                        'order_created' => [
                            'is_enabled' => true,
                            'channels' => ['web', 'email']
                        ],
                        'invoice_paid' => [
                            'is_enabled' => true,
                            'channels' => ['web', 'fcm']
                        ]
                    ]
                ]
            ],
            [
                'method' => 'POST',
                'url' => '/notification-settings/test',
                'name' => 'Test Notification',
                'description' => 'Send test notification',
                'body' => [
                    'type' => 'order_created',
                    'channel' => 'web'
                ]
            ],
            [
                'method' => 'POST',
                'url' => '/notification-settings/reset',
                'name' => 'Reset Settings',
                'description' => 'Reset notification settings to default'
            ],
            [
                'method' => 'GET',
                'url' => '/notification-settings/statistics',
                'name' => 'Get Statistics',
                'description' => 'Get notification settings statistics'
            ]
        ];

        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($baseUrl, $endpoint, $headers);
        }
    }

    /**
     * Test individual endpoint
     */
    private function testEndpoint($baseUrl, $endpoint, $headers)
    {
        $this->info("  🔍 Testing {$endpoint['method']} {$endpoint['url']}");

        try {
            $url = $baseUrl . $endpoint['url'];

            if ($endpoint['method'] === 'GET') {
                $response = Http::withHeaders($headers)->get($url);
            } elseif ($endpoint['method'] === 'POST') {
                $response = Http::withHeaders($headers)->post($url, $endpoint['body'] ?? []);
            } elseif ($endpoint['method'] === 'PUT') {
                $response = Http::withHeaders($headers)->put($url, $endpoint['body'] ?? []);
            }

            if ($response->successful()) {
                $this->info("    ✅ {$endpoint['name']}: {$response->status()}");
                $this->line("    📄 Response: " . substr($response->body(), 0, 100) . '...');
            } else {
                $this->warn("    ⚠️ {$endpoint['name']}: {$response->status()}");
                $this->line("    📄 Error: " . substr($response->body(), 0, 100) . '...');
            }

        } catch (\Exception $e) {
            $this->error("    ❌ {$endpoint['name']}: " . $e->getMessage());
        }
    }

    /**
     * Generate Postman collection data
     */
    private function generatePostmanData($token)
    {
        $this->info('📦 Generating Postman collection data...');

        $collection = [
            'info' => [
                'name' => 'YukiMart Notification Settings API',
                'description' => 'API endpoints for notification settings management',
                'version' => '1.0.0'
            ],
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    ['key' => 'token', 'value' => '{{auth_token}}', 'type' => 'string']
                ]
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => 'http://yukimart.local/api/v1', 'type' => 'string'],
                ['key' => 'auth_token', 'value' => $token, 'type' => 'string']
            ],
            'item' => $this->generatePostmanItems()
        ];

        // Save to file
        $filePath = storage_path('app/postman/notification-settings-api.json');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, json_encode($collection, JSON_PRETTY_PRINT));

        $this->info("✅ Postman collection saved to: {$filePath}");
    }

    /**
     * Generate Postman collection items
     */
    private function generatePostmanItems()
    {
        return [
            [
                'name' => 'Get Notification Settings',
                'request' => [
                    'method' => 'GET',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/notification-settings',
                        'host' => ['{{base_url}}'],
                        'path' => ['notification-settings']
                    ]
                ]
            ],
            [
                'name' => 'Update Notification Settings',
                'request' => [
                    'method' => 'PUT',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Content-Type', 'value' => 'application/json'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                    ],
                    'body' => [
                        'mode' => 'raw',
                        'raw' => json_encode([
                            'settings' => [
                                'order_created' => [
                                    'is_enabled' => true,
                                    'channels' => ['web', 'email']
                                ],
                                'invoice_paid' => [
                                    'is_enabled' => true,
                                    'channels' => ['web', 'fcm']
                                ]
                            ]
                        ])
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/notification-settings',
                        'host' => ['{{base_url}}'],
                        'path' => ['notification-settings']
                    ]
                ]
            ],
            [
                'name' => 'Test Notification',
                'request' => [
                    'method' => 'POST',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Content-Type', 'value' => 'application/json'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                    ],
                    'body' => [
                        'mode' => 'raw',
                        'raw' => json_encode([
                            'type' => 'order_created',
                            'channel' => 'web'
                        ])
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/notification-settings/test',
                        'host' => ['{{base_url}}'],
                        'path' => ['notification-settings', 'test']
                    ]
                ]
            ],
            [
                'name' => 'Reset Settings to Default',
                'request' => [
                    'method' => 'POST',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/notification-settings/reset',
                        'host' => ['{{base_url}}'],
                        'path' => ['notification-settings', 'reset']
                    ]
                ]
            ],
            [
                'name' => 'Get Notification Statistics',
                'request' => [
                    'method' => 'GET',
                    'header' => [
                        ['key' => 'Accept', 'value' => 'application/json'],
                        ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                    ],
                    'url' => [
                        'raw' => '{{base_url}}/notification-settings/statistics',
                        'host' => ['{{base_url}}'],
                        'path' => ['notification-settings', 'statistics']
                    ]
                ]
            ]
        ];
    }
}
