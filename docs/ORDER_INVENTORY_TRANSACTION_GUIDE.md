# Order Inventory Transaction Guide

## 📋 **Overview**

This feature automatically creates inventory transactions with `transaction_type = 'sale'` when an order is successfully created. This ensures proper inventory tracking and maintains accurate stock levels throughout the sales process.

## ✅ **Implementation Details**

### **Automatic Transaction Creation**
When an order is created successfully, the system automatically:
1. Creates inventory transactions for each order item
2. Sets `transaction_type = 'sale'`
3. Updates inventory quantities
4. Maintains transaction history for audit purposes

### **Transaction Flow**
```
Order Creation → Order Items Added → Inventory Transactions Created → Stock Updated
```

## 🔧 **Technical Implementation**

### **1. OrderService Enhancement**
The `createOrder` method in `OrderService` has been enhanced to automatically create inventory transactions:

```php
// In OrderService::createOrder()
// Calculate totals
$order->calculateTotals();

// Create inventory transactions for sale
$this->createSaleInventoryTransactions($order);

DB::commit();
```

### **2. New Method: createSaleInventoryTransactions**
```php
public function createSaleInventoryTransactions($order)
{
    try {
        foreach ($order->orderItems as $item) {
            // Get current stock before transaction
            $product = $item->product;
            $oldQuantity = $product->inventory ? $product->inventory->quantity : 0;
            
            // Create inventory transaction with transaction_type = 'sale'
            InventoryTransaction::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $order->branch_id, // Use branch as warehouse
                'transaction_type' => 'sale',
                'quantity' => -$item->quantity, // Negative for sale (stock decrease)
                'old_quantity' => $oldQuantity,
                'new_quantity' => $oldQuantity - $item->quantity,
                'unit_cost' => $item->unit_price,
                'total_value' => $item->total_price,
                'reference_type' => 'App\\Models\\Order',
                'reference_id' => $order->id,
                'notes' => "Bán hàng - Đơn hàng {$order->order_code}",
                'created_by_user' => Auth::id(),
                'transaction_date' => now()
            ]);

            // Update inventory quantity
            if ($product->inventory) {
                $product->inventory->decrement('quantity', $item->quantity);
            } else {
                // Create inventory record if not exists
                $product->inventory()->create([
                    'quantity' => -$item->quantity,
                    'reserved_quantity' => 0
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Đã tạo giao dịch bán hàng thành công'
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Lỗi khi tạo giao dịch bán hàng: ' . $e->getMessage()
        ];
    }
}
```

### **3. InventoryTransaction Model Enhancement**
Added new transaction type constant:

```php
// New constant added
const TYPE_SALE = 'sale';          // Bán hàng

// Updated getTransactionTypes method
public static function getTransactionTypes()
{
    return [
        self::TYPE_IMPORT => 'Nhập kho',
        self::TYPE_EXPORT => 'Xuất kho',
        self::TYPE_SALE => 'Bán hàng',        // New type
        self::TYPE_TRANSFER => 'Chuyển kho',
        self::TYPE_ADJUSTMENT => 'Điều chỉnh',
        self::TYPE_INITIAL => 'Tồn đầu kỳ',
    ];
}
```

## 📊 **Transaction Data Structure**

### **Sale Transaction Fields**
```php
[
    'product_id' => 123,                    // Product being sold
    'warehouse_id' => 1,                    // Branch ID as warehouse
    'transaction_type' => 'sale',           // Transaction type
    'quantity' => -5,                       // Negative quantity (stock decrease)
    'old_quantity' => 100,                  // Stock before transaction
    'new_quantity' => 95,                   // Stock after transaction
    'unit_cost' => 50000,                   // Unit price from order
    'total_value' => 250000,                // Total value (quantity × unit_cost)
    'reference_type' => 'App\\Models\\Order', // Reference to order model
    'reference_id' => 456,                  // Order ID
    'notes' => 'Bán hàng - Đơn hàng ORD-001', // Descriptive notes
    'created_by_user' => 1,                 // User who created the order
    'transaction_date' => '2024-01-15 10:30:00' // Transaction timestamp
]
```

### **Key Features**
- **Negative Quantity:** Sale transactions use negative quantities to indicate stock decrease
- **Stock Tracking:** Records both old and new quantities for audit trail
- **Order Reference:** Links transaction to the originating order
- **Warehouse Mapping:** Uses branch_id as warehouse_id for multi-location tracking
- **User Tracking:** Records which user created the transaction

## 🔄 **Process Flow**

### **1. Order Creation Process**
```
1. User creates order with items
2. Order validation and creation
3. Order items are added
4. Order totals are calculated
5. → Inventory transactions are created automatically ←
6. Stock quantities are updated
7. Transaction committed to database
8. Success response returned
```

### **2. Transaction Creation for Each Item**
```
For each order item:
1. Get current product stock
2. Create sale transaction record
3. Update inventory quantity
4. Link transaction to order
5. Add descriptive notes
```

### **3. Stock Update Process**
```
1. Read current inventory quantity
2. Calculate new quantity (current - sold)
3. Update inventory table
4. Record transaction with old/new quantities
5. Maintain audit trail
```

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:order-inventory-transaction
```

### **Test Scenarios**
The test command covers:

1. **Prerequisites Check:** Ensures customers and products exist
2. **Order Creation:** Tests automatic transaction creation
3. **Transaction Details:** Verifies transaction data accuracy
4. **Transaction Types:** Confirms 'sale' type is available
5. **Inventory Consistency:** Validates stock calculations

### **Manual Testing**
```php
// Create test order
$orderService = app(OrderService::class);
$result = $orderService->createOrder([
    'customer_id' => 1,
    'branch_id' => 1,
    'items' => json_encode([
        [
            'product_id' => 1,
            'quantity' => 5,
            'unit_price' => 100000
        ]
    ])
]);

// Check if transactions were created
$transactions = InventoryTransaction::where('transaction_type', 'sale')
    ->where('reference_id', $result['data']->id)
    ->get();

echo "Transactions created: " . $transactions->count();
```

## 📈 **Benefits**

### **1. Automatic Inventory Management**
- No manual intervention required
- Consistent stock tracking
- Real-time inventory updates

### **2. Complete Audit Trail**
- Every sale is recorded
- Transaction history maintained
- User accountability

### **3. Multi-Warehouse Support**
- Branch-based inventory tracking
- Warehouse-specific transactions
- Location-aware stock management

### **4. Data Integrity**
- Atomic transactions (all or nothing)
- Consistent data across tables
- Error handling and rollback

## 🔍 **Monitoring and Reports**

### **1. Transaction Queries**
```php
// Get all sale transactions
$saleTransactions = InventoryTransaction::where('transaction_type', 'sale')
    ->with(['product', 'creator'])
    ->orderBy('created_at', 'desc')
    ->get();

// Get transactions for specific order
$orderTransactions = InventoryTransaction::where('reference_type', 'App\\Models\\Order')
    ->where('reference_id', $orderId)
    ->get();

// Get transactions for specific product
$productTransactions = InventoryTransaction::where('product_id', $productId)
    ->where('transaction_type', 'sale')
    ->get();
```

### **2. Stock Movement Reports**
```php
// Calculate total sales for product
$totalSold = InventoryTransaction::where('product_id', $productId)
    ->where('transaction_type', 'sale')
    ->sum('quantity'); // Will be negative, so abs() for positive value

// Get sales by date range
$salesInPeriod = InventoryTransaction::where('transaction_type', 'sale')
    ->whereBetween('transaction_date', [$startDate, $endDate])
    ->sum('total_value');
```

### **3. Inventory Reconciliation**
```php
// Verify stock consistency
$product = Product::with('inventory')->find($productId);
$currentStock = $product->inventory->quantity;

$calculatedStock = InventoryTransaction::where('product_id', $productId)
    ->orderBy('created_at')
    ->get()
    ->reduce(function($stock, $transaction) {
        return $stock + $transaction->quantity;
    }, 0);

$isConsistent = ($currentStock === $calculatedStock);
```

## ⚠️ **Important Considerations**

### **1. Transaction Atomicity**
- All operations are wrapped in database transactions
- If any step fails, entire order creation is rolled back
- Ensures data consistency

### **2. Error Handling**
- Comprehensive exception handling
- Detailed error messages
- Graceful failure recovery

### **3. Performance**
- Bulk operations for multiple items
- Efficient database queries
- Minimal overhead on order creation

### **4. Data Validation**
- Product existence validation
- Stock availability checks
- Quantity validation

## 🚀 **Future Enhancements**

### **Potential Improvements**
1. **Batch Processing:** Optimize for large orders
2. **Stock Reservations:** Reserve stock during order processing
3. **Return Handling:** Automatic return transaction creation
4. **Advanced Reporting:** Real-time inventory dashboards
5. **Integration:** Connect with external inventory systems
6. **Notifications:** Stock level alerts and notifications

### **Configuration Options**
```php
// Future configuration possibilities
'inventory' => [
    'auto_create_transactions' => true,
    'transaction_type_mapping' => [
        'order' => 'sale',
        'return' => 'return',
        'adjustment' => 'adjustment'
    ],
    'stock_validation' => true,
    'negative_stock_allowed' => false
]
```

## 📞 **Support**

### **Troubleshooting**
- **No transactions created:** Check if order creation succeeded
- **Stock not updated:** Verify inventory table relationships
- **Inconsistent data:** Run inventory reconciliation
- **Performance issues:** Check database indexes

### **Debugging**
```php
// Enable transaction logging
DB::enableQueryLog();
$orderService->createOrder($data);
$queries = DB::getQueryLog();
```

---

**The automatic inventory transaction creation ensures accurate stock tracking and provides a complete audit trail for all sales activities! 🚀**
