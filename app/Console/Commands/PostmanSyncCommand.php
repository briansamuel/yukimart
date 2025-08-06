<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class PostmanSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:sync 
                            {--collection= : Collection file to sync (default: enhanced)}
                            {--force : Force sync without confirmation}
                            {--create : Create new collection instead of updating}
                            {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync YukiMart API collection to Postman workspace';

    private $baseUrl = 'https://api.getpostman.com';
    private $apiKey;
    private $workspaceId;
    private $collectionId;
    private $config = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 YukiMart API - Postman Sync');
        $this->info('==============================');
        $this->newLine();

        try {
            // 1. Load configuration
            $this->loadConfiguration();
            
            // 2. Validate configuration
            $this->validateConfiguration();
            
            // 3. Load collection file
            $collection = $this->loadCollectionFile();
            
            // 4. Show sync preview
            $this->showSyncPreview($collection);
            
            // 5. Confirm sync (unless forced or dry-run)
            if (!$this->option('force') && !$this->option('dry-run')) {
                if (!$this->confirm('Do you want to proceed with the sync?')) {
                    $this->warn('Sync cancelled by user.');
                    return 0;
                }
            }
            
            // 6. Perform sync (unless dry-run)
            if ($this->option('dry-run')) {
                $this->info('🧪 Dry run completed. No changes made to Postman.');
                return 0;
            }
            
            $result = $this->performSync($collection);
            
            // 7. Show results
            $this->showSyncResults($result);
            
            return 0;
            
        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function loadConfiguration()
    {
        $this->info('📋 Loading configuration...');
        
        // Load from .env.postman file
        $envFile = base_path('.env.postman');
        if (!file_exists($envFile)) {
            throw new Exception('.env.postman file not found. Run setup first.');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $this->config[trim($key)] = trim($value);
            }
        }
        
        $this->apiKey = $this->config['POSTMAN_API_KEY'] ?? null;
        $this->workspaceId = $this->config['POSTMAN_WORKSPACE_ID'] ?? null;
        $this->collectionId = $this->config['POSTMAN_COLLECTION_ID'] ?? null;
        
        $this->line('   - API Key: ' . substr($this->apiKey, 0, 8) . '...');
        $this->line('   - Workspace ID: ' . $this->workspaceId);
        $this->line('   - Collection ID: ' . ($this->collectionId ?: 'Will create new'));
        $this->newLine();
    }

    private function validateConfiguration()
    {
        $this->info('🔍 Validating configuration...');
        
        if (!$this->apiKey) {
            throw new Exception('POSTMAN_API_KEY not configured');
        }
        
        if (!$this->workspaceId) {
            throw new Exception('POSTMAN_WORKSPACE_ID not configured');
        }
        
        // Test API connection
        $response = $this->makePostmanRequest('GET', '/me');
        if (!$response || !isset($response['user'])) {
            throw new Exception('Invalid API key or connection failed');
        }
        
        $this->line('   - API Connection: ✅ Success');
        $this->line('   - User: ' . $response['user']['username']);
        $this->newLine();
    }

    private function loadCollectionFile()
    {
        $this->info('📦 Loading collection file...');
        
        $collectionType = $this->option('collection') ?: 'enhanced';
        $collectionFiles = [
            'enhanced' => 'yukimart-api-enhanced.json',
            'fixed' => 'yukimart-api-fixed-examples.json',
            'complete' => 'yukimart-api-complete-examples.json'
        ];
        
        $fileName = $collectionFiles[$collectionType] ?? $collectionType;
        $collectionPath = storage_path('testing/postman/' . $fileName);
        
        if (!file_exists($collectionPath)) {
            throw new Exception("Collection file not found: $fileName");
        }
        
        $collection = json_decode(file_get_contents($collectionPath), true);
        if (!$collection) {
            throw new Exception('Invalid collection file format');
        }
        
        $this->line('   - File: ' . $fileName);
        $this->line('   - Name: ' . $collection['info']['name']);
        $this->line('   - Folders: ' . count($collection['item']));
        $this->newLine();
        
        return $collection;
    }

    private function showSyncPreview($collection)
    {
        $this->info('👀 Sync Preview:');
        $this->info('================');
        
        $mode = $this->option('create') || !$this->collectionId ? 'Create' : 'Update';
        $this->line('   - Mode: ' . $mode . ' collection');
        $this->line('   - Target: ' . ($this->collectionId ?: 'New collection'));
        $this->line('   - Workspace: ' . $this->workspaceId);
        
        // Count examples
        $totalExamples = 0;
        foreach ($collection['item'] as $folder) {
            foreach ($folder['item'] as $request) {
                if (isset($request['response'])) {
                    $totalExamples += count($request['response']);
                }
            }
        }
        
        $this->line('   - Examples: ' . $totalExamples . ' response examples');
        $this->newLine();
    }

    private function performSync($collection)
    {
        $this->info('🔄 Syncing to Postman...');
        
        if ($this->option('create') || !$this->collectionId) {
            $this->line('   Creating new collection...');
            $result = $this->createCollection($collection);
        } else {
            $this->line('   Updating existing collection...');
            $result = $this->updateCollection($collection);
        }
        
        if (!$result || !isset($result['collection'])) {
            throw new Exception('Sync failed - no collection returned');
        }
        
        // Save collection ID if created new
        if (!$this->collectionId && isset($result['collection']['id'])) {
            $this->saveCollectionId($result['collection']['id']);
        }
        
        return $result['collection'];
    }

    private function createCollection($collection)
    {
        $payload = ['collection' => $collection];
        return $this->makePostmanRequest('POST', '/collections', $payload);
    }

    private function updateCollection($collection)
    {
        $payload = ['collection' => $collection];
        return $this->makePostmanRequest('PUT', "/collections/{$this->collectionId}", $payload);
    }

    private function saveCollectionId($collectionId)
    {
        $envFile = base_path('.env.postman');
        $content = file_get_contents($envFile);
        
        if (strpos($content, 'POSTMAN_COLLECTION_ID=') !== false) {
            $content = preg_replace('/POSTMAN_COLLECTION_ID=.*/', "POSTMAN_COLLECTION_ID=$collectionId", $content);
        } else {
            $content .= "\nPOSTMAN_COLLECTION_ID=$collectionId\n";
        }
        
        file_put_contents($envFile, $content);
        $this->line('   - Collection ID saved to .env.postman');
    }

    private function showSyncResults($collection)
    {
        $this->newLine();
        $this->info('🎉 Sync Completed Successfully!');
        $this->info('==============================');
        
        $this->line('📊 Results:');
        $this->line('   - Collection: ' . ($collection['info']['name'] ?? $collection['name'] ?? 'Unknown'));
        $this->line('   - ID: ' . ($collection['id'] ?? $collection['uid'] ?? 'Unknown'));
        $this->line('   - Workspace: ' . $this->workspaceId);
        $this->line('   - Status: ✅ Success');
        $this->line('   - Sync Time: ' . date('Y-m-d H:i:s'));
        
        $this->newLine();
        $this->line('🔗 Access Links:');
        $this->line('   - Workspace: https://web.postman.co/workspace/' . $this->workspaceId);
        $collectionId = $collection['id'] ?? $collection['uid'] ?? $this->collectionId;
        $this->line('   - Collection: https://web.postman.co/workspace/' . $this->workspaceId . '/collection/' . $collectionId);
        
        $this->newLine();
        $this->line('📱 Next Steps:');
        $this->line('   1. Open Postman workspace to verify sync');
        $this->line('   2. Check Examples tab trong mỗi request');
        $this->line('   3. Test API endpoints với examples');
        $this->line('   4. Use for Flutter development');
        
        $this->newLine();
        $this->info('🎯 Collection is now live in your Postman workspace!');
    }

    private function makePostmanRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        $headers = [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode >= 400) {
            $errorResponse = json_decode($response, true);
            $errorMessage = $errorResponse['error']['message'] ?? "HTTP $httpCode error";
            throw new Exception("Postman API Error: $errorMessage");
        }
        
        return json_decode($response, true);
    }
}
