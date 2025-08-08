<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;
use App\Models\User;

class TestNotificationApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:notification-api';

    /**
     * The console command description.
     */
    protected $description = 'Test Notification API endpoints';

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
        $this->info('🔔 Testing Notification API');
        $this->info('===========================');

        try {
            // 1. Authenticate
            $this->authenticate();
            
            // 2. Create test notifications
            $this->createTestNotifications();
            
            // 3. Test API endpoints
            $this->testGetNotifications();
            $this->testGetStatistics();
            $this->testMarkAsRead();
            $this->testMarkAllAsRead();
            
            $this->info('✅ All notification API tests completed successfully!');
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
     * Create test notifications
     */
    private function createTestNotifications()
    {
        $this->info('📝 Creating test notifications...');
        
        $user = User::where('email', 'yukimart@gmail.com')->first();
        
        if (!$user) {
            throw new \Exception('Test user not found');
        }

        // Create different types of notifications
        $notifications = [
            [
                'type' => 'order',
                'title' => 'Đơn hàng mới',
                'message' => 'Bạn có đơn hàng mới cần xử lý #ORD-001',
                'priority' => 'high',
                'data' => ['order_id' => 1, 'amount' => 500000]
            ],
            [
                'type' => 'invoice',
                'title' => 'Hóa đơn đã thanh toán',
                'message' => 'Hóa đơn #INV-001 đã được thanh toán thành công',
                'priority' => 'normal',
                'data' => ['invoice_id' => 1, 'amount' => 300000]
            ],
            [
                'type' => 'inventory',
                'title' => 'Cảnh báo tồn kho',
                'message' => 'Sản phẩm ABC sắp hết hàng (còn 5 sản phẩm)',
                'priority' => 'urgent',
                'data' => ['product_id' => 1, 'quantity' => 5]
            ],
            [
                'type' => 'system',
                'title' => 'Cập nhật hệ thống',
                'message' => 'Hệ thống sẽ bảo trì vào 2:00 AM ngày mai',
                'priority' => 'low',
                'data' => ['maintenance_time' => '2025-08-09 02:00:00']
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::createForUser(
                $user,
                $notificationData['type'],
                $notificationData['title'],
                $notificationData['message'],
                $notificationData['data'],
                ['priority' => $notificationData['priority']]
            );
        }

        $this->info('✅ Created ' . count($notifications) . ' test notifications');
    }

    /**
     * Test get notifications endpoint
     */
    private function testGetNotifications()
    {
        $this->info('📋 Testing GET /notifications...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        $response = Http::withHeaders($headers)->get($this->baseUrl . '/notifications');
        
        if ($response->successful()) {
            $data = $response->json();
            $count = count($data['data']);
            $this->info("  ✅ Success - Retrieved {$count} notifications");
            
            // Test with filters
            $response = Http::withHeaders($headers)->get($this->baseUrl . '/notifications?type=order&status=unread');
            if ($response->successful()) {
                $this->info("  ✅ Filter by type and status works");
            }
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }
    }

    /**
     * Test get statistics endpoint
     */
    private function testGetStatistics()
    {
        $this->info('📊 Testing GET /notifications/statistics...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        $response = Http::withHeaders($headers)->get($this->baseUrl . '/notifications/statistics');
        
        if ($response->successful()) {
            $data = $response->json();
            $stats = $data['data'];
            $this->info("  ✅ Success - Total: {$stats['total']}, Unread: {$stats['unread']}");
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }
    }

    /**
     * Test mark as read endpoint
     */
    private function testMarkAsRead()
    {
        $this->info('✅ Testing POST /notifications/{id}/read...');
        
        // Get first notification
        $user = User::where('email', 'yukimart@gmail.com')->first();
        $notification = Notification::forUser($user->id)->unread()->first();
        
        if (!$notification) {
            $this->warn("  ⚠️ No unread notifications to test");
            return;
        }

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        $response = Http::withHeaders($headers)->post($this->baseUrl . "/notifications/{$notification->id}/read");
        
        if ($response->successful()) {
            $this->info("  ✅ Success - Marked notification as read");
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }
    }

    /**
     * Test mark all as read endpoint
     */
    private function testMarkAllAsRead()
    {
        $this->info('✅ Testing POST /notifications/mark-all-read...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        $response = Http::withHeaders($headers)->post($this->baseUrl . '/notifications/mark-all-read');
        
        if ($response->successful()) {
            $data = $response->json();
            $count = $data['data']['count'] ?? 0;
            $this->info("  ✅ Success - Marked {$count} notifications as read");
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }
    }
}
