<?php

/**
 * Script to install doctrine/dbal package if needed
 * Run this with: php install_doctrine_dbal.php
 */

echo "🔍 Checking if doctrine/dbal is needed...\n";

// Check if composer.json exists
if (!file_exists('composer.json')) {
    echo "❌ composer.json not found. Please run this script from the project root.\n";
    exit(1);
}

// Read composer.json
$composerJson = json_decode(file_get_contents('composer.json'), true);

// Check if doctrine/dbal is already installed
$hasDoctrineDbal = false;
if (isset($composerJson['require']['doctrine/dbal'])) {
    $hasDoctrineDbal = true;
    echo "✅ doctrine/dbal is already in require section\n";
}

if (isset($composerJson['require-dev']['doctrine/dbal'])) {
    $hasDoctrineDbal = true;
    echo "✅ doctrine/dbal is already in require-dev section\n";
}

if ($hasDoctrineDbal) {
    echo "✅ doctrine/dbal is already installed!\n";
    echo "\n💡 If you're still getting errors, try:\n";
    echo "   composer update doctrine/dbal\n";
    echo "   composer dump-autoload\n";
    exit(0);
}

echo "⚠️  doctrine/dbal is not installed.\n";
echo "\n🔧 To fix migration issues, you have two options:\n\n";

echo "Option 1: Install doctrine/dbal (Recommended)\n";
echo "   composer require doctrine/dbal\n\n";

echo "Option 2: Use alternative migrations (Already implemented)\n";
echo "   The migrations have been updated to use raw SQL instead of ->change() methods\n";
echo "   This avoids the need for doctrine/dbal\n\n";

echo "📋 Current migration status:\n";

// Check migration files
$migrationFiles = [
    'database/migrations/2025_06_18_120005_add_user_timestamp_columns.php' => 'Add user timestamp columns',
    'database/migrations/2025_06_18_120009_fix_amount_paid_null_constraint.php' => 'Fix amount_paid null constraint',
];

foreach ($migrationFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ {$description}\n";
    } else {
        echo "   ❌ {$description} - File missing\n";
    }
}

echo "\n🚀 Next steps:\n";
echo "1. Choose one of the options above\n";
echo "2. Run: php artisan migrate\n";
echo "3. If you get errors, check the migration files\n";

echo "\n💡 Tips:\n";
echo "- doctrine/dbal is only needed for column modifications\n";
echo "- Raw SQL migrations work without doctrine/dbal\n";
echo "- You can always install doctrine/dbal later if needed\n";

// Check if we can run composer
$composerPath = 'composer';

// Try to find composer
$possiblePaths = [
    'composer',
    'composer.phar',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
];

$composerFound = false;
foreach ($possiblePaths as $path) {
    $output = [];
    $returnCode = 0;
    exec("$path --version 2>/dev/null", $output, $returnCode);
    if ($returnCode === 0) {
        $composerPath = $path;
        $composerFound = true;
        break;
    }
}

if ($composerFound) {
    echo "\n🎯 Auto-install option:\n";
    echo "Would you like to install doctrine/dbal now? (y/n): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
        echo "\n🔧 Installing doctrine/dbal...\n";
        
        $command = "$composerPath require doctrine/dbal";
        echo "Running: $command\n";
        
        passthru($command, $returnCode);
        
        if ($returnCode === 0) {
            echo "\n✅ doctrine/dbal installed successfully!\n";
            echo "You can now run: php artisan migrate\n";
        } else {
            echo "\n❌ Failed to install doctrine/dbal\n";
            echo "Please install manually: composer require doctrine/dbal\n";
        }
    } else {
        echo "\n👍 Skipping auto-install. You can install manually later.\n";
    }
} else {
    echo "\n⚠️  Composer not found in PATH. Please install doctrine/dbal manually:\n";
    echo "   composer require doctrine/dbal\n";
}

echo "\n🎉 Setup complete!\n";
?>
