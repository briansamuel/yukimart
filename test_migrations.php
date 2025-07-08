<?php

/**
 * Simple test script to verify migration syntax
 * Run this with: php test_migrations.php
 */

echo "🧪 Testing Migration Files Syntax\n";
echo "==================================\n\n";

$migrationPath = __DIR__ . '/database/migrations';
$files = glob($migrationPath . '/*.php');

$errors = [];
$warnings = [];
$success = 0;

foreach ($files as $file) {
    $filename = basename($file);
    echo "📄 Testing: $filename\n";
    
    try {
        // Check syntax by including the file
        $content = file_get_contents($file);
        
        // Check for problematic patterns
        if (preg_match('/\$table->morphs\([^)]+\)[^;]*->comment\(/', $content)) {
            $warnings[] = "$filename: Contains morphs()->comment() pattern that may cause issues";
        }
        
        // Try to parse the PHP syntax
        $tokens = token_get_all($content);
        
        // Check for basic syntax errors by attempting to eval (dangerous but for testing)
        // Instead, we'll use a safer approach with syntax checking
        $syntaxCheck = shell_exec("php -l \"$file\" 2>&1");
        
        if (strpos($syntaxCheck, 'No syntax errors') !== false) {
            echo "   ✅ Syntax OK\n";
            $success++;
        } else {
            $errors[] = "$filename: $syntaxCheck";
            echo "   ❌ Syntax Error\n";
        }
        
    } catch (Exception $e) {
        $errors[] = "$filename: " . $e->getMessage();
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "📊 Results:\n";
echo "===========\n";
echo "✅ Successful: $success files\n";
echo "⚠️  Warnings: " . count($warnings) . " files\n";
echo "❌ Errors: " . count($errors) . " files\n\n";

if (!empty($warnings)) {
    echo "⚠️  Warnings:\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ Errors:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    echo "\n";
    exit(1);
}

echo "🎉 All migration files passed syntax check!\n";

// Additional checks
echo "\n🔍 Additional Checks:\n";
echo "=====================\n";

// Check for specific fixed files
$fixedFiles = [
    '2025_06_18_000003_create_notifications_table.php',
    '2025_06_18_000007_create_translations_table.php'
];

foreach ($fixedFiles as $fixedFile) {
    $fullPath = $migrationPath . '/' . $fixedFile;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        echo "📄 Checking fixed file: $fixedFile\n";
        
        // Check if morphs()->comment() pattern is gone
        if (!preg_match('/\$table->morphs\([^)]+\)[^;]*->comment\(/', $content)) {
            echo "   ✅ morphs()->comment() pattern fixed\n";
        } else {
            echo "   ❌ morphs()->comment() pattern still exists\n";
        }
        
        // Check if separate columns exist
        if (preg_match('/\$table->string\([\'"][^\'"]+(type|_type)[\'"]\)/', $content) && 
            preg_match('/\$table->unsignedBigInteger\([\'"][^\'"]+(id|_id)[\'"]\)/', $content)) {
            echo "   ✅ Separate morphs columns created\n";
        } else {
            echo "   ❌ Separate morphs columns not found\n";
        }
        
        // Check if indexes exist
        if (preg_match('/\$table->index\(/', $content)) {
            echo "   ✅ Indexes found\n";
        } else {
            echo "   ⚠️  No indexes found\n";
        }
        
        echo "\n";
    }
}

echo "🎯 Test Summary:\n";
echo "================\n";

if (count($errors) === 0) {
    echo "✅ All tests passed! Migration files are ready to run.\n";
    echo "\n💡 Next steps:\n";
    echo "   1. Run: php artisan migrate\n";
    echo "   2. Or run specific migration: php artisan migrate --path=database/migrations/filename.php\n";
    echo "   3. Test with: php artisan migrate:rollback (if needed)\n";
} else {
    echo "❌ Some tests failed. Please fix the errors before running migrations.\n";
    exit(1);
}
