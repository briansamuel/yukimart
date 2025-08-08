<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FCMService;
use App\Models\FCMToken;
use App\Models\User;

class TestFCMTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcm:test-token {token} {--user-id=1 : User ID to associate with token}';

    /**
     * The console command description.
     */
    protected $description = 'Test FCM functionality with a specific token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = $this->argument('token');
        $userId = $this->option('user-id');

        $this->info('🔥 Testing FCM with token: ' . substr($token, 0, 30) . '...');
        $this->newLine();

        // Test 1: Configuration
        $this->testConfiguration();

        // Test 2: Register Token
        $this->testRegisterToken($token, $userId);

        // Test 3: Send Test Notification
        $this->testSendNotification($token);

        // Test 4: Send Custom Notification
        $this->testCustomNotification($token);

        // Test 5: Check Token Status
        $this->testTokenStatus($token);

        $this->newLine();
        $this->info('🎉 FCM testing completed!');
    }

    /**
     * Test FCM configuration.
     */
    protected function testConfiguration()
    {
        $this->info('⚙️  Test 1: FCM Configuration');
        $this->line('------------------------------');

        try {
            $fcmService = app(FCMService::class);
            $result = $fcmService->testConfiguration();

            if ($result['success']) {
                $this->line('✅ ' . $result['message']);
                
                if (isset($result['details'])) {
                    $details = $result['details'];
                    $this->line('   📧 Service Account: ' . $details['service_account_email']);
                    $this->line('   🆔 Project ID: ' . $details['project_id']);
                    $this->line('   🔗 API Version: ' . $details['api_version']);
                }
            } else {
                $this->error('❌ ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Configuration test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test token registration.
     */
    protected function testRegisterToken($token, $userId)
    {
        $this->info('📱 Test 2: Register FCM Token');
        $this->line('------------------------------');

        try {
            // Find or create user
            $user = User::find($userId);
            if (!$user) {
                $this->error("❌ User with ID $userId not found");
                $this->newLine();
                return;
            }

            // Register token
            $fcmToken = FCMToken::updateOrCreate(
                ['token' => $token],
                [
                    'user_id' => $user->id,
                    'device_type' => 'android',
                    'device_id' => 'test-device-' . time(),
                    'device_name' => 'Test Device via Command',
                    'app_version' => '1.0.0',
                    'platform_version' => 'Android 12',
                    'is_active' => true,
                    'metadata' => [
                        'test_mode' => true,
                        'registered_via' => 'artisan_command',
                        'timestamp' => now()->toISOString()
                    ]
                ]
            );

            $this->line('✅ Token registered successfully');
            $this->line('   👤 User: ' . $user->name . ' (ID: ' . $user->id . ')');
            $this->line('   📱 Device: ' . $fcmToken->device_name);
            $this->line('   🆔 Token ID: ' . $fcmToken->id);

        } catch (\Exception $e) {
            $this->error('❌ Token registration failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test sending notification to specific token.
     */
    protected function testSendNotification($token)
    {
        $this->info('🔔 Test 3: Send Test Notification');
        $this->line('----------------------------------');

        try {
            $fcmService = app(FCMService::class);

            $notification = [
                'id' => 'test-' . time(),
                'title' => 'YukiMart FCM Test',
                'message' => 'This is a test notification from FCM Service Account!',
                'type' => 'test',
                'priority' => 'normal',
                'icon' => '/favicon.ico'
            ];

            $data = [
                'test_mode' => 'true',
                'sent_via' => 'artisan_command',
                'timestamp' => now()->toISOString()
            ];

            $result = $fcmService->sendToTokens([$token], $notification, $data);

            if ($result['success']) {
                $this->line('✅ Test notification sent successfully');
                $this->line('   📊 Sent: ' . $result['sent_count']);
                $this->line('   ❌ Failed: ' . $result['failed_count']);
            } else {
                $this->error('❌ Test notification failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Send notification failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test sending custom notification.
     */
    protected function testCustomNotification($token)
    {
        $this->info('🎯 Test 4: Send Custom Notification');
        $this->line('------------------------------------');

        try {
            $fcmService = app(FCMService::class);

            $notification = [
                'id' => 'custom-test-' . time(),
                'title' => 'Custom FCM Notification',
                'message' => 'This is a custom notification with high priority and action button!',
                'type' => 'custom',
                'priority' => 'high',
                'icon' => '/favicon.ico',
                'action_url' => 'https://yukimart.local/dashboard',
                'action_text' => 'Open Dashboard'
            ];

            $data = [
                'custom_data' => 'test_value',
                'notification_id' => $notification['id'],
                'action_required' => 'true'
            ];

            $result = $fcmService->sendToTokens([$token], $notification, $data);

            if ($result['success']) {
                $this->line('✅ Custom notification sent successfully');
                $this->line('   📊 Sent: ' . $result['sent_count']);
                $this->line('   ❌ Failed: ' . $result['failed_count']);
                
                if (isset($result['results'])) {
                    foreach ($result['results'] as $tokenResult) {
                        if ($tokenResult['success']) {
                            $this->line('   ✅ Token: ' . substr($tokenResult['token'], 0, 20) . '...');
                        } else {
                            $this->line('   ❌ Token: ' . substr($tokenResult['token'], 0, 20) . '... - ' . $tokenResult['error']);
                        }
                    }
                }
            } else {
                $this->error('❌ Custom notification failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Send custom notification failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test token status and information.
     */
    protected function testTokenStatus($token)
    {
        $this->info('📋 Test 5: Check Token Status');
        $this->line('------------------------------');

        try {
            $fcmToken = FCMToken::where('token', $token)->first();

            if ($fcmToken) {
                $this->line('✅ Token found in database');
                $this->line('   🆔 ID: ' . $fcmToken->id);
                $this->line('   👤 User: ' . $fcmToken->user->name . ' (ID: ' . $fcmToken->user_id . ')');
                $this->line('   📱 Device: ' . $fcmToken->device_name);
                $this->line('   📋 Type: ' . $fcmToken->device_type);
                $this->line('   ✅ Active: ' . ($fcmToken->is_active ? 'Yes' : 'No'));
                $this->line('   📅 Registered: ' . $fcmToken->created_at->format('Y-m-d H:i:s'));
                $this->line('   🔄 Updated: ' . $fcmToken->updated_at->format('Y-m-d H:i:s'));
                
                if ($fcmToken->metadata) {
                    $this->line('   📊 Metadata: ' . json_encode($fcmToken->metadata, JSON_PRETTY_PRINT));
                }
            } else {
                $this->warn('⚠️  Token not found in database');
                $this->line('   💡 Register the token first using the register test');
            }

            // Check all tokens for user
            if (isset($fcmToken)) {
                $userTokens = FCMToken::where('user_id', $fcmToken->user_id)
                    ->where('is_active', true)
                    ->count();
                
                $this->line('   📱 User has ' . $userTokens . ' active tokens');
            }

        } catch (\Exception $e) {
            $this->error('❌ Token status check failed: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
