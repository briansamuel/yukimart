<?php

/**
 * Test Configuration Loading
 * Simple test to validate .env.postman configuration
 */

echo "🧪 Testing Configuration Loading\n";
echo "===============================\n\n";

$envFile = __DIR__ . '/../.env.postman';

if (!file_exists($envFile)) {
    echo "❌ .env.postman file not found\n";
    exit(1);
}

echo "📋 Loading configuration from .env.postman...\n";

$config = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
        list($key, $value) = explode('=', $line, 2);
        $config[trim($key)] = trim($value);
    }
}

echo "✅ Configuration loaded successfully:\n\n";

foreach ($config as $key => $value) {
    if (str_contains($key, 'API_KEY')) {
        echo "   $key = " . substr($value, 0, 8) . "...\n";
    } else {
        echo "   $key = $value\n";
    }
}

echo "\n🔍 Validation:\n";

// Validate required fields
$required = ['POSTMAN_API_KEY', 'POSTMAN_WORKSPACE_ID'];
$valid = true;

foreach ($required as $field) {
    if (empty($config[$field]) || $config[$field] === 'your_' . strtolower(str_replace('POSTMAN_', '', $field)) . '_here') {
        echo "❌ $field is not configured\n";
        $valid = false;
    } else {
        echo "✅ $field is configured\n";
    }
}

if (isset($config['POSTMAN_COLLECTION_ID']) && $config['POSTMAN_COLLECTION_ID'] !== 'your_collection_id_here') {
    echo "✅ POSTMAN_COLLECTION_ID is configured (update mode)\n";
} else {
    echo "⚠️ POSTMAN_COLLECTION_ID not configured (create mode)\n";
}

echo "\n📊 Summary:\n";
echo "- Configuration file: " . ($valid ? "✅ Valid" : "❌ Invalid") . "\n";
echo "- Mode: " . (isset($config['POSTMAN_COLLECTION_ID']) && $config['POSTMAN_COLLECTION_ID'] !== 'your_collection_id_here' ? "Update existing collection" : "Create new collection") . "\n";
echo "- Ready for sync: " . ($valid ? "✅ Yes" : "❌ No") . "\n\n";

if (!$valid) {
    echo "🔧 Next steps:\n";
    echo "1. Get Postman API Key from: https://web.postman.co/settings/me/api-keys\n";
    echo "2. Update POSTMAN_API_KEY in .env.postman\n";
    echo "3. Run: php scripts/get-postman-info.php\n\n";
} else {
    echo "🚀 Ready to test sync!\n";
    echo "Run: php scripts/sync-to-postman.php\n\n";
}
