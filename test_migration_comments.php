<?php

/**
 * Test script to verify migration comment removal
 * Run this with: php test_migration_comments.php
 */

echo "🧪 Testing Migration Comment Removal\n";
echo "====================================\n\n";

$migrationPath = __DIR__ . '/database/migrations';
$files = glob($migrationPath . '/*.php');

$stats = [
    'total_files' => 0,
    'files_with_comments' => 0,
    'files_with_inline_comments' => 0,
    'comment_patterns_found' => 0,
    'inline_comment_lines' => 0
];

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $stats['total_files']++;
    
    echo "📄 Checking: $filename\n";
    
    // Check for ->comment() patterns
    $commentMatches = [];
    preg_match_all('/->comment\([\'"][^\'"]*[\'"]/', $content, $commentMatches);
    
    if (!empty($commentMatches[0])) {
        $stats['files_with_comments']++;
        $stats['comment_patterns_found'] += count($commentMatches[0]);
        echo "   ❌ Found " . count($commentMatches[0]) . " ->comment() patterns\n";
        
        foreach ($commentMatches[0] as $match) {
            echo "      - $match\n";
        }
    } else {
        echo "   ✅ No ->comment() patterns found\n";
    }
    
    // Check for // comments
    $lines = explode("\n", $content);
    $inlineComments = 0;
    
    foreach ($lines as $lineNumber => $line) {
        if (preg_match('/^\s*\/\/\s*[^\/]/', $line)) {
            $inlineComments++;
        }
    }
    
    if ($inlineComments > 0) {
        $stats['files_with_inline_comments']++;
        $stats['inline_comment_lines'] += $inlineComments;
        echo "   📝 Found $inlineComments inline comment lines\n";
    }
    
    echo "\n";
}

echo "📊 Summary:\n";
echo "===========\n";
echo "📁 Total files: {$stats['total_files']}\n";
echo "❌ Files with ->comment(): {$stats['files_with_comments']}\n";
echo "📝 Files with // comments: {$stats['files_with_inline_comments']}\n";
echo "🔍 Total ->comment() patterns: {$stats['comment_patterns_found']}\n";
echo "💬 Total // comment lines: {$stats['inline_comment_lines']}\n\n";

// Check specific files that should be fixed
$expectedFixedFiles = [
    '2025_06_18_000003_create_notifications_table.php',
    '2025_06_18_000004_create_notification_settings_table.php',
    '2025_06_18_000005_create_notification_templates_table.php',
    '2025_06_18_000006_create_languages_table.php',
    '2025_06_18_000007_create_translations_table.php',
    '2025_06_18_120003_add_payment_method_to_orders_table.php'
];

echo "🎯 Checking Expected Fixed Files:\n";
echo "=================================\n";

$allFixed = true;

foreach ($expectedFixedFiles as $expectedFile) {
    $fullPath = $migrationPath . '/' . $expectedFile;
    
    if (!file_exists($fullPath)) {
        echo "⚠️  File not found: $expectedFile\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    $hasComments = preg_match('/->comment\(/', $content);
    $hasInlineComments = preg_match('/^\s*\/\/\s*[^\/]/m', $content);
    
    echo "📄 $expectedFile:\n";
    
    if ($hasComments) {
        echo "   ❌ Still has ->comment() patterns\n";
        $allFixed = false;
    } else {
        echo "   ✅ No ->comment() patterns\n";
    }
    
    if ($hasInlineComments) {
        echo "   ✅ Has // comments\n";
    } else {
        echo "   ⚠️  No // comments found\n";
    }
    
    echo "\n";
}

echo "🎉 Final Result:\n";
echo "================\n";

if ($stats['comment_patterns_found'] === 0) {
    echo "✅ SUCCESS: All ->comment() patterns have been removed!\n";
    echo "📝 Migration files now use // comments instead.\n";
    echo "\n💡 Next steps:\n";
    echo "   1. Run: php artisan migrate\n";
    echo "   2. Verify tables are created correctly\n";
    echo "   3. Check database schema matches expectations\n";
} else {
    echo "❌ INCOMPLETE: Still found {$stats['comment_patterns_found']} ->comment() patterns.\n";
    echo "\n🔧 To fix remaining issues:\n";
    echo "   1. Run: php artisan migration:remove-comments\n";
    echo "   2. Or manually edit the files listed above\n";
    echo "   3. Re-run this test script\n";
}

if ($stats['inline_comment_lines'] > 0) {
    echo "\n📝 Good: Found {$stats['inline_comment_lines']} // comment lines for documentation.\n";
}

// Test a sample migration syntax
echo "\n🧪 Testing Sample Migration Syntax:\n";
echo "===================================\n";

$sampleMigration = '
Schema::create("test_table", function (Blueprint $table) {
    // ID column
    $table->id();
    // Name column
    $table->string("name");
    // Email column
    $table->string("email");
    $table->timestamps();
});
';

echo "Sample migration code:\n";
echo $sampleMigration;

// Check if it's valid PHP syntax
$tempFile = tempnam(sys_get_temp_dir(), 'migration_test');
file_put_contents($tempFile, "<?php\n" . $sampleMigration);

$syntaxCheck = shell_exec("php -l \"$tempFile\" 2>&1");
unlink($tempFile);

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ Sample syntax is valid!\n";
} else {
    echo "❌ Sample syntax has errors: $syntaxCheck\n";
}

echo "\n🎯 Migration comment removal test completed!\n";
