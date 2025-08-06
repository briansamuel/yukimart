<?php

/**
 * Sync YukiMart API Collection to Postman Workspace
 * Automatically uploads collection using Postman API
 */

require_once __DIR__ . '/../vendor/autoload.php';

class PostmanSyncService
{
    private $apiKey;
    private $workspaceId;
    private $collectionId;
    private $collectionName;
    private $baseUrl = 'https://api.getpostman.com';

    public function __construct()
    {
        echo "🚀 Syncing Collection to Postman Workspace\n";
        echo "==========================================\n\n";
        
        $this->loadConfig();
    }

    public function syncCollection()
    {
        try {
            // 1. Load collection file
            $collection = $this->loadCollectionFile();
            
            // 2. Check if collection exists
            $existingCollection = $this->getExistingCollection();
            
            if ($existingCollection) {
                // 3. Update existing collection
                echo "📝 Updating existing collection...\n";
                $result = $this->updateCollection($collection);
            } else {
                // 3. Create new collection
                echo "🆕 Creating new collection...\n";
                $result = $this->createCollection($collection);
            }
            
            if ($result) {
                echo "\n🎉 Collection synced successfully to Postman!\n";
                echo "============================================\n\n";
                $this->printSyncSummary($result);
            } else {
                throw new Exception("Failed to sync collection");
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function loadConfig()
    {
        echo "📋 Loading Postman configuration...\n";
        
        // Try to load from .env.postman file
        $envFile = __DIR__ . '/../.env.postman';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        
        $this->apiKey = $_ENV['POSTMAN_API_KEY'] ?? null;
        $this->workspaceId = $_ENV['POSTMAN_WORKSPACE_ID'] ?? null;
        $this->collectionId = $_ENV['POSTMAN_COLLECTION_ID'] ?? null;
        $this->collectionName = $_ENV['POSTMAN_COLLECTION_NAME'] ?? 'YukiMart API v1 - Complete với Examples';
        
        if (!$this->apiKey || !$this->workspaceId) {
            throw new Exception("Missing Postman API Key or Workspace ID. Please check .env.postman file.");
        }
        
        echo "✅ Configuration loaded successfully\n";
        echo "   - API Key: " . substr($this->apiKey, 0, 8) . "...\n";
        echo "   - Workspace ID: " . $this->workspaceId . "\n";
        echo "   - Collection ID: " . ($this->collectionId ?: 'Will create new') . "\n\n";
    }

    private function loadCollectionFile()
    {
        echo "📂 Loading collection file...\n";
        
        $collectionPath = __DIR__ . '/../storage/testing/postman/yukimart-api-enhanced.json';
        if (!file_exists($collectionPath)) {
            throw new Exception("Collection file not found: $collectionPath");
        }
        
        $collection = json_decode(file_get_contents($collectionPath), true);
        if (!$collection) {
            throw new Exception("Invalid collection file format");
        }
        
        echo "✅ Collection file loaded successfully\n";
        echo "   - Name: " . $collection['info']['name'] . "\n";
        echo "   - Folders: " . count($collection['item']) . "\n\n";
        
        return $collection;
    }

    private function getExistingCollection()
    {
        if (!$this->collectionId) {
            return null;
        }
        
        echo "🔍 Checking existing collection...\n";
        
        $response = $this->makePostmanRequest('GET', "/collections/{$this->collectionId}");
        
        if ($response && isset($response['collection'])) {
            echo "✅ Found existing collection: " . $response['collection']['info']['name'] . "\n\n";
            return $response['collection'];
        }
        
        echo "⚠️ Collection not found, will create new one\n\n";
        return null;
    }

    private function updateCollection($collection)
    {
        echo "📝 Updating collection in Postman...\n";
        
        $payload = [
            'collection' => $collection
        ];
        
        $response = $this->makePostmanRequest('PUT', "/collections/{$this->collectionId}", $payload);
        
        if ($response && isset($response['collection'])) {
            echo "✅ Collection updated successfully\n";
            return $response['collection'];
        }
        
        throw new Exception("Failed to update collection");
    }

    private function createCollection($collection)
    {
        echo "🆕 Creating new collection in Postman...\n";
        
        $payload = [
            'collection' => $collection
        ];
        
        $response = $this->makePostmanRequest('POST', '/collections', $payload);
        
        if ($response && isset($response['collection'])) {
            echo "✅ Collection created successfully\n";
            
            // Save collection ID for future updates
            $this->collectionId = $response['collection']['id'];
            $this->saveCollectionId($response['collection']['id']);
            
            return $response['collection'];
        }
        
        throw new Exception("Failed to create collection");
    }

    private function saveCollectionId($collectionId)
    {
        echo "💾 Saving collection ID for future updates...\n";
        
        $envFile = __DIR__ . '/../.env.postman';
        $content = file_get_contents($envFile);
        
        if (strpos($content, 'POSTMAN_COLLECTION_ID=') !== false) {
            $content = preg_replace('/POSTMAN_COLLECTION_ID=.*/', "POSTMAN_COLLECTION_ID=$collectionId", $content);
        } else {
            $content .= "\nPOSTMAN_COLLECTION_ID=$collectionId\n";
        }
        
        file_put_contents($envFile, $content);
        echo "✅ Collection ID saved to .env.postman\n";
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

    private function printSyncSummary($collection)
    {
        echo "📊 Sync Summary:\n";
        echo "================\n";
        echo "- Collection Name: " . $collection['info']['name'] . "\n";
        echo "- Collection ID: " . $collection['id'] . "\n";
        echo "- Workspace ID: " . $this->workspaceId . "\n";
        echo "- Total Folders: " . count($collection['item']) . "\n";
        echo "- Sync Status: ✅ Success\n";
        echo "- Sync Time: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "🔗 Access Links:\n";
        echo "- Workspace: https://web.postman.co/workspace/{$this->workspaceId}\n";
        echo "- Collection: https://web.postman.co/workspace/{$this->workspaceId}/collection/{$collection['id']}\n\n";
        
        echo "📱 Next Steps:\n";
        echo "1. Open Postman workspace to verify sync\n";
        echo "2. Check Examples tab trong mỗi request\n";
        echo "3. Test API endpoints với examples\n";
        echo "4. Use for Flutter development\n\n";
        
        echo "🎯 Collection is now live in your Postman workspace!\n";
    }
}

// Check if running from command line
if (php_sapi_name() === 'cli') {
    $syncService = new PostmanSyncService();
    $syncService->syncCollection();
} else {
    echo "This script should be run from command line.\n";
}
