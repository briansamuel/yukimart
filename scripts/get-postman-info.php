<?php

/**
 * Get Postman Workspace and Collection Information
 * Helper script to find workspace and collection IDs
 */

require_once __DIR__ . '/../vendor/autoload.php';

class PostmanInfoService
{
    private $apiKey;
    private $baseUrl = 'https://api.getpostman.com';

    public function __construct()
    {
        echo "🔍 Getting Postman Workspace Information\n";
        echo "========================================\n\n";
        
        $this->loadConfig();
    }

    public function getInfo()
    {
        try {
            // 1. Get user info
            $this->getUserInfo();
            
            // 2. List workspaces
            $this->listWorkspaces();
            
            // 3. List collections (if workspace ID provided)
            $this->listCollections();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function loadConfig()
    {
        echo "📋 Loading Postman API Key...\n";
        
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
        
        if (!$this->apiKey) {
            echo "⚠️ No API Key found. Please:\n";
            echo "1. Copy .env.postman.example to .env.postman\n";
            echo "2. Add your Postman API Key\n";
            echo "3. Get API Key from: https://web.postman.co/settings/me/api-keys\n\n";
            throw new Exception("Missing Postman API Key");
        }
        
        echo "✅ API Key loaded: " . substr($this->apiKey, 0, 8) . "...\n\n";
    }

    private function getUserInfo()
    {
        echo "👤 Getting user information...\n";
        
        $response = $this->makePostmanRequest('GET', '/me');
        
        if ($response && isset($response['user'])) {
            $user = $response['user'];
            echo "✅ User: " . $user['username'] . " (" . $user['fullName'] . ")\n";
            echo "   Email: " . $user['email'] . "\n";
            echo "   Team: " . ($user['teamName'] ?? 'Personal') . "\n\n";
        }
    }

    private function listWorkspaces()
    {
        echo "🏢 Listing workspaces...\n";
        
        $response = $this->makePostmanRequest('GET', '/workspaces');
        
        if ($response && isset($response['workspaces'])) {
            echo "✅ Found " . count($response['workspaces']) . " workspaces:\n\n";
            
            foreach ($response['workspaces'] as $workspace) {
                echo "📁 " . $workspace['name'] . "\n";
                echo "   ID: " . $workspace['id'] . "\n";
                echo "   Type: " . $workspace['type'] . "\n";
                echo "   Visibility: " . $workspace['visibility'] . "\n";
                echo "   URL: https://web.postman.co/workspace/" . $workspace['id'] . "\n\n";
            }
            
            echo "💡 Copy the Workspace ID you want to use to .env.postman\n\n";
        }
    }

    private function listCollections()
    {
        $workspaceId = $_ENV['POSTMAN_WORKSPACE_ID'] ?? null;
        
        if (!$workspaceId) {
            echo "⚠️ No Workspace ID provided. Add POSTMAN_WORKSPACE_ID to .env.postman to see collections.\n\n";
            return;
        }
        
        echo "📚 Listing collections in workspace...\n";
        
        $response = $this->makePostmanRequest('GET', "/collections?workspace=$workspaceId");
        
        if ($response && isset($response['collections'])) {
            echo "✅ Found " . count($response['collections']) . " collections:\n\n";
            
            foreach ($response['collections'] as $collection) {
                echo "📋 " . $collection['name'] . "\n";
                echo "   ID: " . $collection['id'] . "\n";
                echo "   UID: " . $collection['uid'] . "\n";
                echo "   Owner: " . $collection['owner'] . "\n";
                echo "   Created: " . $collection['createdAt'] . "\n";
                echo "   Updated: " . $collection['updatedAt'] . "\n";
                echo "   URL: https://web.postman.co/workspace/$workspaceId/collection/" . $collection['id'] . "\n\n";
            }
            
            echo "💡 Copy the Collection ID you want to update to .env.postman\n";
            echo "   Or leave empty to create a new collection\n\n";
        }
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
            CURLOPT_TIMEOUT => 30
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

// Check if running from command line
if (php_sapi_name() === 'cli') {
    $infoService = new PostmanInfoService();
    $infoService->getInfo();
} else {
    echo "This script should be run from command line.\n";
}
