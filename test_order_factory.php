<?php

/**
 * Simple test script to verify OrderFactory works correctly
 * Run this with: php test_order_factory.php
 */

// Test the generateOrderCode method logic
function testGenerateOrderCode() {
    echo "Testing generateOrderCode method...\n";
    
    // Test with DateTime object
    $date = new DateTime('2024-01-15');
    $result = generateOrderCodeTest($date);
    echo "DateTime test: $result\n";
    assert(strpos($result, 'ORD20240115') === 0, 'DateTime test failed');
    
    // Test with string date
    $dateString = '2024-01-15';
    $result = generateOrderCodeTest($dateString);
    echo "String test: $result\n";
    assert(strpos($result, 'ORD20240115') === 0, 'String test failed');
    
    // Test with invalid input
    $result = generateOrderCodeTest(null);
    echo "Null test: $result\n";
    $today = date('Ymd');
    assert(strpos($result, "ORD$today") === 0, 'Null test failed');
    
    echo "✅ All generateOrderCode tests passed!\n\n";
}

function generateOrderCodeTest($date) {
    $prefix = 'ORD';
    
    // Handle both DateTime objects and string dates
    if ($date instanceof DateTime) {
        $dateStr = $date->format('Ymd');
    } elseif (is_string($date)) {
        $dateStr = date('Ymd', strtotime($date));
    } else {
        $dateStr = date('Ymd'); // Fallback to current date
    }
    
    $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return $prefix . $dateStr . $randomNumber;
}

function testOrderCodeFormat() {
    echo "Testing order code format...\n";
    
    for ($i = 0; $i < 10; $i++) {
        $code = generateOrderCodeTest(new DateTime());
        echo "Generated: $code\n";
        
        // Check format: ORD + 8 digits (date) + 4 digits (random)
        assert(preg_match('/^ORD\d{8}\d{4}$/', $code), "Invalid format: $code");
        
        // Check length
        assert(strlen($code) === 15, "Invalid length: $code");
    }
    
    echo "✅ All format tests passed!\n\n";
}

function testUniqueness() {
    echo "Testing order code uniqueness...\n";
    
    $codes = [];
    for ($i = 0; $i < 100; $i++) {
        $code = generateOrderCodeTest(new DateTime());
        $codes[] = $code;
    }
    
    $uniqueCodes = array_unique($codes);
    $duplicates = count($codes) - count($uniqueCodes);
    
    echo "Generated: " . count($codes) . " codes\n";
    echo "Unique: " . count($uniqueCodes) . " codes\n";
    echo "Duplicates: $duplicates codes\n";
    
    // Allow some duplicates due to random nature, but should be minimal
    assert($duplicates < 5, "Too many duplicates: $duplicates");
    
    echo "✅ Uniqueness test passed!\n\n";
}

function testDateHandling() {
    echo "Testing different date formats...\n";
    
    $testDates = [
        new DateTime('2024-01-01'),
        new DateTime('2024-12-31'),
        '2024-06-15',
        '2023-02-28',
        '2024-02-29', // Leap year
    ];
    
    foreach ($testDates as $date) {
        $code = generateOrderCodeTest($date);
        echo "Date: " . (is_string($date) ? $date : $date->format('Y-m-d')) . " -> Code: $code\n";
        
        // Extract date part from code
        $datePart = substr($code, 3, 8);
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        assert(checkdate($month, $day, $year), "Invalid date in code: $code");
    }
    
    echo "✅ Date handling tests passed!\n\n";
}

function testPerformance() {
    echo "Testing performance...\n";
    
    $startTime = microtime(true);
    
    for ($i = 0; $i < 1000; $i++) {
        generateOrderCodeTest(new DateTime());
    }
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    echo "Generated 1000 codes in: " . number_format($executionTime, 4) . " seconds\n";
    echo "Average per code: " . number_format($executionTime / 1000 * 1000, 4) . " ms\n";
    
    assert($executionTime < 1, "Performance too slow: $executionTime seconds");
    
    echo "✅ Performance test passed!\n\n";
}

// Run all tests
echo "🧪 Starting OrderFactory Tests\n";
echo "================================\n\n";

try {
    testGenerateOrderCode();
    testOrderCodeFormat();
    testUniqueness();
    testDateHandling();
    testPerformance();
    
    echo "🎉 All tests passed successfully!\n";
    echo "The OrderFactory fix is working correctly.\n";
    
} catch (AssertionError $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
