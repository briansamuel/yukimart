<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\FCMService;

class SetupFCMCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcm:setup {--force : Force overwrite existing configuration}';

    /**
     * The console command description.
     */
    protected $description = 'Setup Firebase Cloud Messaging (FCM) configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Setting up Firebase Cloud Messaging (FCM)...');
        $this->newLine();

        // Check if migration exists
        $this->checkMigration();

        // Check configuration
        $this->checkConfiguration();

        // Test FCM connection
        $this->testFCMConnection();

        // Show setup instructions
        $this->showSetupInstructions();

        $this->newLine();
        $this->info('✅ FCM setup completed!');
    }

    /**
     * Check if FCM migration exists and run it.
     */
    protected function checkMigration()
    {
        $this->info('1. Checking FCM database migration...');

        $migrationFile = 'database/migrations/2025_08_08_150000_create_fcm_tokens_table.php';
        
        if (File::exists($migrationFile)) {
            $this->line('   ✅ FCM migration file exists');
            
            if ($this->confirm('   Run FCM migration now?', true)) {
                $this->call('migrate', ['--path' => 'database/migrations/2025_08_08_150000_create_fcm_tokens_table.php']);
            }
        } else {
            $this->error('   ❌ FCM migration file not found');
            $this->line('   Please ensure the migration file exists at: ' . $migrationFile);
        }

        $this->newLine();
    }

    /**
     * Check FCM configuration.
     */
    protected function checkConfiguration()
    {
        $this->info('2. Checking FCM configuration...');

        $serviceAccountPath = config('services.fcm.service_account_path');
        $projectId = config('services.fcm.project_id');
        $vapidKey = config('services.fcm.vapid_key');
        $serverKey = config('services.fcm.server_key'); // Legacy

        // Check Service Account (Recommended)
        if ($serviceAccountPath && file_exists($serviceAccountPath)) {
            $this->line('   ✅ Service Account File: ' . $serviceAccountPath);

            // Validate Service Account JSON
            try {
                $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
                if (isset($serviceAccount['client_email']) && isset($serviceAccount['private_key'])) {
                    $this->line('   ✅ Service Account Valid: ' . $serviceAccount['client_email']);
                } else {
                    $this->error('   ❌ Service Account file is invalid');
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Service Account file error: ' . $e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  Service Account not configured or file not found');
            $this->line('   📁 Expected path: ' . $serviceAccountPath);
        }

        // Check Legacy Server Key
        if ($serverKey) {
            $this->warn('   ⚠️  FCM Server Key (DEPRECATED): ' . substr($serverKey, 0, 20) . '...');
            $this->line('   💡 Consider migrating to Service Account');
        }

        if ($projectId) {
            $this->line('   ✅ FCM Project ID: ' . $projectId);
        } else {
            $this->warn('   ⚠️  FCM Project ID not configured');
        }

        if ($vapidKey) {
            $this->line('   ✅ FCM VAPID Key: ' . substr($vapidKey, 0, 20) . '...');
        } else {
            $this->warn('   ⚠️  FCM VAPID Key not configured (needed for web push)');
        }

        $this->newLine();
    }

    /**
     * Test FCM connection.
     */
    protected function testFCMConnection()
    {
        $this->info('3. Testing FCM connection...');

        try {
            $fcmService = app(FCMService::class);
            $result = $fcmService->testConfiguration();

            if ($result['success']) {
                $this->line('   ✅ ' . $result['message']);
            } else {
                $this->warn('   ⚠️  ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('   ❌ FCM test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Show setup instructions.
     */
    protected function showSetupInstructions()
    {
        $this->info('4. Setup Instructions:');
        $this->newLine();

        $this->line('📋 To complete FCM setup, add these to your .env file:');
        $this->newLine();

        $this->line('# Firebase Cloud Messaging Configuration (Service Account)');
        $this->line('# Project: saas-techcura');
        $this->line('FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json');
        $this->line('FCM_PROJECT_ID=saas-techcura');
        $this->line('FCM_SENDER_ID=185186239234');
        $this->line('FCM_API_KEY=AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc');
        $this->line('FCM_AUTH_DOMAIN=saas-techcura.firebaseapp.com');
        $this->line('FCM_STORAGE_BUCKET=saas-techcura.firebasestorage.app');
        $this->line('FCM_APP_ID=1:185186239234:web:9717b33e89ce7c71fd381b');
        $this->line('FCM_MEASUREMENT_ID=G-PWKCFCL5ZQ');
        $this->line('FCM_VAPID_KEY=your_vapid_key_here');
        $this->newLine();

        $this->line('🔗 Get Service Account from Firebase Console:');
        $this->line('   1. Go to https://console.firebase.google.com/');
        $this->line('   2. Select your project (saas-techcura)');
        $this->line('   3. Go to Project Settings > Service Accounts');
        $this->line('   4. Click "Generate new private key"');
        $this->line('   5. Download the JSON file');
        $this->line('   6. Place it in storage/app/firebase/service-account.json');
        $this->line('   7. For VAPID Key: Go to Cloud Messaging > Web Push certificates');
        $this->newLine();

        $this->line('📱 API Endpoints available:');
        $this->line('   POST /api/v1/fcm/register-token     - Register device token');
        $this->line('   POST /api/v1/fcm/unregister-token   - Unregister device token');
        $this->line('   GET  /api/v1/fcm/tokens             - Get user tokens');
        $this->line('   POST /api/v1/fcm/test-notification  - Send test notification');
        $this->line('   GET  /api/v1/fcm/statistics         - Get FCM statistics (Admin)');
        $this->line('   POST /api/v1/fcm/send-notification  - Send notification (Admin)');
        $this->line('   GET  /api/v1/fcm/test-config        - Test configuration (Admin)');
        $this->newLine();

        $this->line('🔄 Queue Setup (for background processing):');
        $this->line('   php artisan queue:work --queue=notifications');
        $this->newLine();

        $this->line('📚 Usage Examples:');
        $this->line('   # Register token');
        $this->line('   curl -X POST /api/v1/fcm/register-token \\');
        $this->line('     -H "Authorization: Bearer YOUR_TOKEN" \\');
        $this->line('     -H "Content-Type: application/json" \\');
        $this->line('     -d \'{"token":"fcm_device_token","device_type":"android"}\'');
        $this->newLine();

        $this->line('   # Send test notification');
        $this->line('   curl -X POST /api/v1/fcm/test-notification \\');
        $this->line('     -H "Authorization: Bearer YOUR_TOKEN" \\');
        $this->line('     -H "Content-Type: application/json" \\');
        $this->line('     -d \'{"title":"Test","message":"Hello from YukiMart!"}\'');
    }
}
