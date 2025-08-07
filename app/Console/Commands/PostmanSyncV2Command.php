<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PostmanCollectionService;
use Exception;

class PostmanSyncV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:sync-v2 
                            {--force : Force sync without confirmation}
                            {--dry-run : Show what would be synced without actually syncing}
                            {--save-only : Only save collection to file without syncing}
                            {--test : Run API tests after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and sync YukiMart API v1 collection to Postman workspace';

    private $postmanService;

    public function __construct(PostmanCollectionService $postmanService)
    {
        parent::__construct();
        $this->postmanService = $postmanService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 YukiMart API v1 - Postman Collection Sync');
        $this->info('==============================================');
        $this->newLine();

        try {
            // 1. Validate configuration
            $this->validateConfiguration();
            
            // 2. Generate collection
            $this->info('📦 Generating comprehensive API collection...');
            $collection = $this->postmanService->generateCollection();
            
            // 3. Show collection preview
            $this->showCollectionPreview($collection);
            
            // 4. Save collection to file
            $filePath = $this->postmanService->saveCollectionToFile($collection);
            $this->info("💾 Collection saved to: {$filePath}");
            
            // 5. Handle save-only option
            if ($this->option('save-only')) {
                $this->info('✅ Collection saved successfully. Skipping Postman sync.');
                return 0;
            }
            
            // 6. Confirm sync (unless forced or dry-run)
            if (!$this->option('force') && !$this->option('dry-run')) {
                if (!$this->confirm('Do you want to sync this collection to Postman?')) {
                    $this->warn('Sync cancelled by user.');
                    return 0;
                }
            }
            
            // 7. Perform sync (unless dry-run)
            if ($this->option('dry-run')) {
                $this->info('🧪 Dry run completed. Collection generated but not synced to Postman.');
                return 0;
            }
            
            $this->info('🔄 Syncing collection to Postman...');
            $result = $this->postmanService->syncToPostman($collection);
            
            // 8. Show results
            $this->showSyncResults($result);
            
            // 9. Run tests if requested
            if ($this->option('test') && $result['success']) {
                $this->runApiTests();
            }
            
            return $result['success'] ? 0 : 1;
            
        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Validate Postman configuration
     */
    private function validateConfiguration()
    {
        $this->info('🔍 Validating Postman configuration...');
        
        $apiKey = config('postman.api_key');
        $workspaceId = config('postman.workspace_id');
        
        if (!$apiKey) {
            throw new Exception('POSTMAN_API_KEY not configured in .env file');
        }
        
        if (!$workspaceId) {
            $this->warn('POSTMAN_WORKSPACE_ID not configured. Collection will be created in default workspace.');
        }
        
        $this->line('   - API Key: ' . substr($apiKey, 0, 8) . '...');
        $this->line('   - Workspace ID: ' . ($workspaceId ?: 'Default'));
        $this->line('   - Collection ID: ' . (config('postman.collection_id') ?: 'Will create new'));
        $this->newLine();
    }

    /**
     * Show collection preview
     */
    private function showCollectionPreview($collection)
    {
        $this->info('👀 Collection Preview:');
        $this->info('======================');
        
        $this->line('   - Name: ' . $collection['info']['name']);
        $this->line('   - Description: ' . $collection['info']['description']);
        $this->line('   - Version: ' . $collection['info']['version']);
        $this->line('   - Folders: ' . count($collection['item']));
        
        // Count total requests
        $totalRequests = 0;
        foreach ($collection['item'] as $folder) {
            $totalRequests += count($folder['item']);
        }
        $this->line('   - Total Requests: ' . $totalRequests);
        
        $this->newLine();
        $this->line('📁 Folders:');
        foreach ($collection['item'] as $folder) {
            $this->line('   - ' . $folder['name'] . ' (' . count($folder['item']) . ' requests)');
        }
        $this->newLine();
    }

    /**
     * Show sync results
     */
    private function showSyncResults($result)
    {
        $this->newLine();
        
        if ($result['success']) {
            $this->info('🎉 Sync Completed Successfully!');
            $this->info('==============================');
            
            $this->line('📊 Results:');
            $this->line('   - Status: ✅ Success');
            $this->line('   - Collection ID: ' . ($result['collection_id'] ?? 'Unknown'));
            $this->line('   - Workspace: ' . config('postman.workspace_id'));
            $this->line('   - Sync Time: ' . date('Y-m-d H:i:s'));
            
            $this->newLine();
            $this->line('🔗 Access Links:');
            if (config('postman.workspace_id')) {
                $this->line('   - Workspace: https://web.postman.co/workspace/' . config('postman.workspace_id'));
                if ($result['collection_id']) {
                    $this->line('   - Collection: https://web.postman.co/workspace/' . config('postman.workspace_id') . '/collection/' . $result['collection_id']);
                }
            }
            
            $this->newLine();
            $this->line('📱 Next Steps:');
            $this->line('   1. Open Postman workspace to verify sync');
            $this->line('   2. Run Login request to get auth token');
            $this->line('   3. Test API endpoints với examples');
            $this->line('   4. Use for Flutter development');
            
            $this->newLine();
            $this->info('🎯 Collection is now live in your Postman workspace!');
            
        } else {
            $this->error('❌ Sync Failed!');
            $this->error('===============');
            
            $this->line('📊 Error Details:');
            $this->line('   - Status: ❌ Failed');
            $this->line('   - Error: ' . $result['error']);
            $this->line('   - Status Code: ' . ($result['status_code'] ?? 'Unknown'));
            
            $this->newLine();
            $this->line('🔧 Troubleshooting:');
            $this->line('   1. Check your POSTMAN_API_KEY in .env file');
            $this->line('   2. Verify API key permissions');
            $this->line('   3. Check workspace access');
            $this->line('   4. Try with --dry-run first');
        }
    }

    /**
     * Run API tests
     */
    private function runApiTests()
    {
        $this->newLine();
        $this->info('🧪 Running API Tests...');
        $this->info('=======================');
        
        try {
            // Test health endpoint
            $this->line('Testing Health Check...');
            $healthResponse = $this->testHealthEndpoint();
            $this->line($healthResponse ? '   ✅ Health Check: OK' : '   ❌ Health Check: Failed');
            
            // Test login endpoint
            $this->line('Testing Authentication...');
            $loginResponse = $this->testLoginEndpoint();
            $this->line($loginResponse ? '   ✅ Login: OK' : '   ❌ Login: Failed');
            
            if ($loginResponse) {
                // Test protected endpoint
                $this->line('Testing Protected Endpoint...');
                $profileResponse = $this->testProfileEndpoint($loginResponse['token']);
                $this->line($profileResponse ? '   ✅ Profile: OK' : '   ❌ Profile: Failed');
            }
            
            $this->newLine();
            $this->info('🎯 API tests completed!');
            
        } catch (Exception $e) {
            $this->error('❌ API tests failed: ' . $e->getMessage());
        }
    }

    /**
     * Test health endpoint
     */
    private function testHealthEndpoint()
    {
        try {
            $response = file_get_contents(config('postman.base_url') . '/health');
            $data = json_decode($response, true);
            return $data && $data['status'] === 'healthy';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test login endpoint
     */
    private function testLoginEndpoint()
    {
        try {
            $postData = json_encode([
                'email' => config('postman.test_credentials.email'),
                'password' => config('postman.test_credentials.password')
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $postData
                ]
            ]);
            
            $response = file_get_contents(config('postman.base_url') . '/auth/login', false, $context);
            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'success' && isset($data['data']['token'])) {
                return ['token' => $data['data']['token']];
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test profile endpoint
     */
    private function testProfileEndpoint($token)
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'Authorization: Bearer ' . $token,
                        'Accept: application/json'
                    ]
                ]
            ]);
            
            $response = file_get_contents(config('postman.base_url') . '/auth/profile', false, $context);
            $data = json_decode($response, true);
            
            return $data && $data['status'] === 'success';
        } catch (Exception $e) {
            return false;
        }
    }
}
