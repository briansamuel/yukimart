<?php

/**
 * Setup Postman Sync Configuration
 * Interactive setup for Postman API integration
 */

require_once __DIR__ . '/../vendor/autoload.php';

class PostmanSetupService
{
    private $envFile;

    public function __construct()
    {
        echo "🚀 YukiMart API - Postman Sync Setup\n";
        echo "====================================\n\n";
        
        $this->envFile = __DIR__ . '/../.env.postman';
    }

    public function setup()
    {
        try {
            echo "This setup will help you configure automatic sync to Postman workspace.\n\n";
            
            // 1. Check if .env.postman exists
            $this->checkEnvFile();
            
            // 2. Guide user to get API key
            $this->guideApiKey();
            
            // 3. Guide user to get workspace ID
            $this->guideWorkspaceId();
            
            // 4. Guide user to get collection ID (optional)
            $this->guideCollectionId();
            
            // 5. Test configuration
            $this->testConfiguration();
            
            // 6. Show next steps
            $this->showNextSteps();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function checkEnvFile()
    {
        echo "📋 Step 1: Checking configuration file...\n";
        
        if (!file_exists($this->envFile)) {
            echo "⚠️ .env.postman file not found. Creating from template...\n";
            
            $templateFile = __DIR__ . '/../.env.postman.example';
            if (file_exists($templateFile)) {
                copy($templateFile, $this->envFile);
                echo "✅ Created .env.postman from template\n\n";
            } else {
                throw new Exception("Template file .env.postman.example not found");
            }
        } else {
            echo "✅ .env.postman file exists\n\n";
        }
    }

    private function guideApiKey()
    {
        echo "🔑 Step 2: Postman API Key\n";
        echo "==========================\n";
        echo "To sync collections automatically, you need a Postman API Key.\n\n";
        
        echo "📝 How to get your Postman API Key:\n";
        echo "1. Go to: https://web.postman.co/settings/me/api-keys\n";
        echo "2. Click 'Generate API Key'\n";
        echo "3. Give it a name (e.g., 'YukiMart API Sync')\n";
        echo "4. Copy the generated key\n";
        echo "5. Paste it in .env.postman file as POSTMAN_API_KEY=your_key_here\n\n";
        
        echo "⚠️ Keep your API key secure and never commit it to version control!\n\n";
        
        $this->waitForUserInput("Press Enter when you've added your API Key to .env.postman...");
    }

    private function guideWorkspaceId()
    {
        echo "🏢 Step 3: Workspace ID\n";
        echo "=======================\n";
        echo "You need to specify which Postman workspace to sync to.\n\n";
        
        echo "📝 How to get your Workspace ID:\n";
        echo "Method 1 - From URL:\n";
        echo "1. Go to your Postman workspace\n";
        echo "2. Copy the ID from URL: https://web.postman.co/workspace/YOUR_WORKSPACE_ID\n\n";
        
        echo "Method 2 - Use our helper script:\n";
        echo "1. Add your API Key to .env.postman first\n";
        echo "2. Run: php scripts/get-postman-info.php\n";
        echo "3. Copy the Workspace ID you want to use\n\n";
        
        echo "4. Add it to .env.postman as POSTMAN_WORKSPACE_ID=your_workspace_id\n\n";
        
        $this->waitForUserInput("Press Enter when you've added your Workspace ID to .env.postman...");
    }

    private function guideCollectionId()
    {
        echo "📚 Step 4: Collection ID (Optional)\n";
        echo "===================================\n";
        echo "If you want to UPDATE an existing collection, provide its ID.\n";
        echo "If you want to CREATE a new collection, leave this empty.\n\n";
        
        echo "📝 How to get Collection ID (if updating existing):\n";
        echo "Method 1 - From Postman:\n";
        echo "1. Go to your collection in Postman\n";
        echo "2. Click the '...' menu → 'View documentation'\n";
        echo "3. Copy the ID from the URL\n\n";
        
        echo "Method 2 - Use our helper script:\n";
        echo "1. Run: php scripts/get-postman-info.php\n";
        echo "2. Copy the Collection ID you want to update\n\n";
        
        echo "3. Add it to .env.postman as POSTMAN_COLLECTION_ID=your_collection_id\n";
        echo "   (Or leave empty to create new collection)\n\n";
        
        $this->waitForUserInput("Press Enter when ready to continue...");
    }

    private function testConfiguration()
    {
        echo "🧪 Step 5: Testing configuration...\n";
        echo "===================================\n";
        
        // Load config
        $config = $this->loadConfig();
        
        if (!$config['api_key']) {
            throw new Exception("API Key not found in .env.postman");
        }
        
        if (!$config['workspace_id']) {
            throw new Exception("Workspace ID not found in .env.postman");
        }
        
        echo "✅ Configuration loaded successfully:\n";
        echo "   - API Key: " . substr($config['api_key'], 0, 8) . "...\n";
        echo "   - Workspace ID: " . $config['workspace_id'] . "\n";
        echo "   - Collection ID: " . ($config['collection_id'] ?: 'Will create new') . "\n\n";
        
        // Test API connection
        echo "🔗 Testing Postman API connection...\n";
        $testResult = $this->testApiConnection($config['api_key']);
        
        if ($testResult) {
            echo "✅ API connection successful!\n";
            echo "   User: " . $testResult['username'] . "\n";
            echo "   Email: " . $testResult['email'] . "\n\n";
        }
    }

    private function showNextSteps()
    {
        echo "🎉 Setup Complete!\n";
        echo "==================\n\n";
        
        echo "📋 Your configuration is ready. Next steps:\n\n";
        
        echo "1. 🔄 Sync collection to Postman:\n";
        echo "   php scripts/sync-to-postman.php\n\n";
        
        echo "2. 🔍 Get workspace/collection info:\n";
        echo "   php scripts/get-postman-info.php\n\n";
        
        echo "3. 📱 Use in your workflow:\n";
        echo "   - Make changes to API\n";
        echo "   - Update collection examples\n";
        echo "   - Run sync script\n";
        echo "   - Collection automatically updates in Postman\n\n";
        
        echo "🔗 Useful Links:\n";
        echo "- Postman API Docs: https://learning.postman.com/docs/developer/intro-api/\n";
        echo "- Your API Keys: https://web.postman.co/settings/me/api-keys\n";
        echo "- Your Workspaces: https://web.postman.co/workspaces\n\n";
        
        echo "🎯 Ready to sync your collection automatically!\n";
    }

    private function loadConfig()
    {
        $config = [
            'api_key' => null,
            'workspace_id' => null,
            'collection_id' => null
        ];
        
        if (file_exists($this->envFile)) {
            $lines = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    if ($key === 'POSTMAN_API_KEY') $config['api_key'] = $value;
                    if ($key === 'POSTMAN_WORKSPACE_ID') $config['workspace_id'] = $value;
                    if ($key === 'POSTMAN_COLLECTION_ID') $config['collection_id'] = $value;
                }
            }
        }
        
        return $config;
    }

    private function testApiConnection($apiKey)
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.getpostman.com/me',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['user'])) {
                return [
                    'username' => $data['user']['username'],
                    'email' => $data['user']['email']
                ];
            }
        }
        
        throw new Exception("API connection failed. Please check your API Key.");
    }

    private function waitForUserInput($message)
    {
        echo $message;
        if (php_sapi_name() === 'cli') {
            fgets(STDIN);
        }
        echo "\n";
    }
}

// Check if running from command line
if (php_sapi_name() === 'cli') {
    $setupService = new PostmanSetupService();
    $setupService->setup();
} else {
    echo "This script should be run from command line.\n";
}
