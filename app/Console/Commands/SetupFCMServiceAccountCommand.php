<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupFCMServiceAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcm:setup-service-account {--file= : Path to service account JSON file}';

    /**
     * The console command description.
     */
    protected $description = 'Setup FCM Service Account JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Setting up FCM Service Account...');
        $this->newLine();

        $filePath = $this->option('file');

        if (!$filePath) {
            $filePath = $this->ask('Enter path to Service Account JSON file');
        }

        if (!$filePath) {
            $this->error('No file path provided');
            return 1;
        }

        // Check if file exists
        if (!file_exists($filePath)) {
            $this->error('File not found: ' . $filePath);
            return 1;
        }

        // Validate JSON
        try {
            $content = file_get_contents($filePath);
            $serviceAccount = json_decode($content, true);

            if (!$serviceAccount) {
                throw new \Exception('Invalid JSON format');
            }

            // Validate required fields
            $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($serviceAccount[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                throw new \Exception('Missing required fields: ' . implode(', ', $missingFields));
            }

            // Check if it's a service account
            if ($serviceAccount['type'] !== 'service_account') {
                throw new \Exception('File is not a service account JSON');
            }

            $this->line('✅ Service Account JSON is valid');
            $this->line('   📧 Email: ' . $serviceAccount['client_email']);
            $this->line('   🆔 Project ID: ' . $serviceAccount['project_id']);

        } catch (\Exception $e) {
            $this->error('Invalid Service Account JSON: ' . $e->getMessage());
            return 1;
        }

        // Create firebase directory if it doesn't exist
        $firebaseDir = storage_path('app/firebase');
        if (!File::exists($firebaseDir)) {
            File::makeDirectory($firebaseDir, 0755, true);
            $this->line('📁 Created firebase directory: ' . $firebaseDir);
        }

        // Copy file to storage
        $destinationPath = storage_path('app/firebase/service-account.json');
        
        if (File::exists($destinationPath)) {
            if (!$this->confirm('Service Account file already exists. Overwrite?')) {
                $this->info('Setup cancelled');
                return 0;
            }
        }

        File::copy($filePath, $destinationPath);
        File::chmod($destinationPath, 0600); // Secure permissions

        $this->line('✅ Service Account file copied to: ' . $destinationPath);

        // Update .env file
        $this->updateEnvFile($serviceAccount);

        // Test configuration
        $this->testConfiguration();

        $this->newLine();
        $this->info('🎉 FCM Service Account setup completed!');
        
        return 0;
    }

    /**
     * Update .env file with Service Account configuration.
     */
    protected function updateEnvFile($serviceAccount)
    {
        $this->info('📝 Updating .env configuration...');

        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            $this->warn('.env file not found, skipping automatic update');
            return;
        }

        $envContent = File::get($envPath);
        $updates = [];

        // Service Account Path
        if (!str_contains($envContent, 'FCM_SERVICE_ACCOUNT_PATH=')) {
            $updates[] = 'FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json';
        }

        // Project ID
        if (!str_contains($envContent, 'FCM_PROJECT_ID=')) {
            $updates[] = 'FCM_PROJECT_ID=' . $serviceAccount['project_id'];
        }

        if (!empty($updates)) {
            $envContent .= "\n\n# FCM Service Account Configuration\n";
            $envContent .= implode("\n", $updates) . "\n";
            
            File::put($envPath, $envContent);
            $this->line('✅ Updated .env file with Service Account configuration');
        } else {
            $this->line('ℹ️  .env file already contains FCM configuration');
        }
    }

    /**
     * Test FCM configuration.
     */
    protected function testConfiguration()
    {
        $this->info('🧪 Testing FCM configuration...');

        try {
            $fcmService = app(\App\Services\FCMService::class);
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
                $this->warn('⚠️  ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Configuration test failed: ' . $e->getMessage());
        }
    }
}
