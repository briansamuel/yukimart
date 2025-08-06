<?php

/**
 * Test Export Excel Functionality
 * 
 * This file tests the OrdersExport class and bulk export functionality
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exports\OrdersExport;
use Illuminate\Support\Collection;

echo "=== Testing Export Excel Functionality ===\n\n";

// Test 1: OrdersExport class instantiation
echo "1. Testing OrdersExport class instantiation...\n";
try {
    $emptyOrders = new Collection();
    $export = new OrdersExport($emptyOrders);
    echo "✅ OrdersExport class instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ Error instantiating OrdersExport: " . $e->getMessage() . "\n";
}

// Test 2: Test headings method
echo "\n2. Testing headings method...\n";
try {
    $headings = $export->headings();
    echo "✅ Headings method works. Count: " . count($headings) . "\n";
    echo "   First few headings: " . implode(', ', array_slice($headings, 0, 5)) . "\n";
} catch (Exception $e) {
    echo "❌ Error getting headings: " . $e->getMessage() . "\n";
}

// Test 3: Test column widths method
echo "\n3. Testing column widths method...\n";
try {
    $widths = $export->columnWidths();
    echo "✅ Column widths method works. Count: " . count($widths) . "\n";
    echo "   Sample widths: A=" . $widths['A'] . ", B=" . $widths['B'] . ", C=" . $widths['C'] . "\n";
} catch (Exception $e) {
    echo "❌ Error getting column widths: " . $e->getMessage() . "\n";
}

// Test 4: Test status label methods
echo "\n4. Testing status label methods...\n";
try {
    $reflection = new ReflectionClass($export);
    
    // Test getStatusLabel method
    $getStatusLabel = $reflection->getMethod('getStatusLabel');
    $getStatusLabel->setAccessible(true);
    
    $statusTests = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    
    foreach ($statusTests as $status => $expected) {
        $result = $getStatusLabel->invoke($export, $status);
        if ($result === $expected) {
            echo "✅ Status '$status' -> '$result'\n";
        } else {
            echo "❌ Status '$status' expected '$expected' but got '$result'\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing status labels: " . $e->getMessage() . "\n";
}

// Test 5: Test payment status label methods
echo "\n5. Testing payment status label methods...\n";
try {
    $getPaymentStatusLabel = $reflection->getMethod('getPaymentStatusLabel');
    $getPaymentStatusLabel->setAccessible(true);
    
    $paymentStatusTests = [
        'unpaid' => 'Chưa thanh toán',
        'partial' => 'Thanh toán một phần',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền'
    ];
    
    foreach ($paymentStatusTests as $status => $expected) {
        $result = $getPaymentStatusLabel->invoke($export, $status);
        if ($result === $expected) {
            echo "✅ Payment status '$status' -> '$result'\n";
        } else {
            echo "❌ Payment status '$status' expected '$expected' but got '$result'\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing payment status labels: " . $e->getMessage() . "\n";
}

// Test 6: Test delivery status label methods
echo "\n6. Testing delivery status label methods...\n";
try {
    $getDeliveryStatusLabel = $reflection->getMethod('getDeliveryStatusLabel');
    $getDeliveryStatusLabel->setAccessible(true);
    
    $deliveryStatusTests = [
        'pending' => 'Chờ xử lý',
        'preparing' => 'Lấy hàng',
        'shipping' => 'Giao hàng',
        'delivered' => 'Giao thành công',
        'returned' => 'Chuyển hoàn'
    ];
    
    foreach ($deliveryStatusTests as $status => $expected) {
        $result = $getDeliveryStatusLabel->invoke($export, $status);
        if ($result === $expected) {
            echo "✅ Delivery status '$status' -> '$result'\n";
        } else {
            echo "❌ Delivery status '$status' expected '$expected' but got '$result'\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing delivery status labels: " . $e->getMessage() . "\n";
}

// Test 7: Test with mock order data
echo "\n7. Testing with mock order data...\n";
try {
    // Create mock order object
    $mockOrder = (object) [
        'order_code' => 'HD001',
        'customer_name' => 'Nguyễn Văn A',
        'customer' => (object) [
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'address' => '123 Test Street'
        ],
        'final_amount' => 500000,
        'amount_paid' => 300000,
        'status' => 'processing',
        'payment_status' => 'partial',
        'delivery_status' => 'preparing',
        'channel' => 'direct',
        'branchShop' => (object) ['name' => 'Chi nhánh 1'],
        'creator' => (object) ['name' => 'Admin'],
        'seller' => (object) ['name' => 'Seller 1'],
        'created_at' => (object) ['format' => function($format) { return '15/07/2025 10:30'; }],
        'updated_at' => (object) ['format' => function($format) { return '15/07/2025 11:00'; }],
        'notes' => 'Test order'
    ];
    
    $mockOrders = new Collection([$mockOrder]);
    $exportWithData = new OrdersExport($mockOrders);
    
    $mappedData = $exportWithData->map($mockOrder);
    echo "✅ Mock order mapping successful\n";
    echo "   Order code: " . $mappedData[0] . "\n";
    echo "   Customer: " . $mappedData[1] . "\n";
    echo "   Total amount: " . $mappedData[5] . "\n";
    echo "   Status: " . $mappedData[8] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing with mock data: " . $e->getMessage() . "\n";
}

echo "\n=== Export Excel Test Completed ===\n";
