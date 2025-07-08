# Stock Status Info Function Guide

## 📋 **Overview**

The `getStockStatusInfo()` function provides comprehensive stock status information for products, including visual indicators, alerts, and reorder suggestions. This function is now available in the Product model and can be used throughout the application.

## ✅ **Implementation Details**

### **Function Signature**
```php
public function getStockStatusInfo($stockQuantity = null, $reorderPoint = null)
```

### **Parameters**
- `$stockQuantity` (optional): Current stock quantity. If null, uses `$this->stock_quantity`
- `$reorderPoint` (optional): Reorder point threshold. If null, uses `$this->reorder_point`

### **Return Value**
Returns an associative array with comprehensive stock information:

```php
[
    'status' => 'in_stock|medium_stock|low_stock|out_of_stock',
    'label' => 'Translated status label',
    'class' => 'success|info|warning|danger',
    'icon' => 'check-circle|information-5|warning-2|cross-circle',
    'color' => '#50CD89|#7239EA|#FFC700|#F1416C',
    'quantity' => 50,
    'reorder_point' => 10,
    'percentage' => 75.0,
    'urgency' => 'normal|medium|high|critical',
    'days_until_out_of_stock' => 30.5,
    'reorder_suggestion' => [
        'should_reorder' => false,
        'suggested_quantity' => 0,
        'message' => 'Stock sufficient'
    ],
    'badge_html' => '<span class="badge...">...</span>',
    'progress_html' => '<div class="progress...">...</div>',
    'alert_message' => 'Stock level is good.'
]
```

## 🎯 **Stock Status Categories**

### **1. Out of Stock** 🔴
- **Condition:** `stockQuantity <= 0`
- **Status:** `out_of_stock`
- **Class:** `danger`
- **Icon:** `cross-circle`
- **Urgency:** `critical`
- **Action:** Immediate restocking required

### **2. Low Stock** 🟡
- **Condition:** `stockQuantity <= reorderPoint`
- **Status:** `low_stock`
- **Class:** `warning`
- **Icon:** `warning-2`
- **Urgency:** `high`
- **Action:** Reorder recommended

### **3. Medium Stock** 🔵
- **Condition:** `stockQuantity <= (reorderPoint * 2)`
- **Status:** `medium_stock`
- **Class:** `info`
- **Icon:** `information-5`
- **Urgency:** `medium`
- **Action:** Monitor closely

### **4. Good Stock** 🟢
- **Condition:** `stockQuantity > (reorderPoint * 2)`
- **Status:** `in_stock`
- **Class:** `success`
- **Icon:** `check-circle`
- **Urgency:** `normal`
- **Action:** No action needed

## 🔧 **Usage Examples**

### **1. Basic Usage**
```php
$product = Product::find(1);
$stockInfo = $product->getStockStatusInfo();

echo $stockInfo['label']; // "Good Stock"
echo $stockInfo['class']; // "success"
echo $stockInfo['urgency']; // "normal"
```

### **2. Using Accessor**
```php
$product = Product::find(1);
$stockStatus = $product->stock_status; // Uses accessor

echo $stockStatus['status']; // "in_stock"
echo $stockStatus['percentage']; // 85.5
```

### **3. Custom Parameters**
```php
$product = Product::find(1);
$stockInfo = $product->getStockStatusInfo(25, 10);

// Test with custom stock quantity (25) and reorder point (10)
echo $stockInfo['status']; // "medium_stock"
```

### **4. In Blade Templates**
```blade
@php
    $stockInfo = $product->getStockStatusInfo();
@endphp

<!-- Status Badge -->
{!! $stockInfo['badge_html'] !!}

<!-- Progress Bar -->
{!! $stockInfo['progress_html'] !!}

<!-- Alert Message -->
<div class="alert alert-{{ $stockInfo['class'] }}">
    {{ $stockInfo['alert_message'] }}
</div>

<!-- Reorder Suggestion -->
@if($stockInfo['reorder_suggestion']['should_reorder'])
    <div class="alert alert-warning">
        <strong>Reorder Needed:</strong> 
        {{ $stockInfo['reorder_suggestion']['message'] }}
    </div>
@endif
```

### **5. In Order Items Display**
```blade
@foreach($order->orderItems as $item)
    @php
        $stockInfo = $item->product->getStockStatusInfo();
    @endphp
    
    <tr>
        <td>{{ $item->product->product_name }}</td>
        <td>{{ $item->quantity }}</td>
        <td>
            <span class="badge badge-light-{{ $stockInfo['class'] }}">
                <i class="ki-duotone ki-{{ $stockInfo['icon'] }} fs-7 me-1"></i>
                {{ $stockInfo['label'] }} ({{ $stockInfo['quantity'] }})
            </span>
        </td>
    </tr>
@endforeach
```

## 📊 **Advanced Features**

### **1. Sales Velocity Calculation**
The function calculates days until out of stock based on sales velocity:
```php
// Analyzes last 30 days of sales transactions
$daysUntilOutOfStock = $stockInfo['days_until_out_of_stock'];

if ($daysUntilOutOfStock <= 7) {
    // Critical: Will run out in a week
} elseif ($daysUntilOutOfStock <= 30) {
    // Warning: Will run out in a month
}
```

### **2. Reorder Suggestions**
Intelligent reorder suggestions based on sales patterns:
```php
$reorderInfo = $stockInfo['reorder_suggestion'];

if ($reorderInfo['should_reorder']) {
    $suggestedQty = $reorderInfo['suggested_quantity'];
    $message = $reorderInfo['message'];
    
    // Display reorder recommendation
    echo "Suggest ordering {$suggestedQty} units";
}
```

### **3. Stock Level Percentage**
Visual representation of stock level:
```php
$percentage = $stockInfo['percentage']; // 0-100%

// Use for progress bars, charts, etc.
echo "<div class='progress'>
    <div class='progress-bar' style='width: {$percentage}%'></div>
</div>";
```

## 🎨 **UI Components**

### **1. Status Badge**
```html
<span class="badge badge-light-success d-inline-flex align-items-center">
    <i class="ki-duotone ki-check-circle fs-7 me-1">
        <span class="path1"></span>
        <span class="path2"></span>
    </i>
    Good Stock (150)
</span>
```

### **2. Progress Bar**
```html
<div class="progress h-6px w-100">
    <div class="progress-bar bg-success" role="progressbar" 
         style="width: 85.5%; background-color: #50CD89 !important;" 
         aria-valuenow="85.5" aria-valuemin="0" aria-valuemax="100">
    </div>
</div>
```

### **3. Alert Messages**
```html
<!-- Critical Alert -->
<div class="alert alert-danger">
    <i class="ki-duotone ki-warning-2 fs-2tx text-danger me-4"></i>
    Product is out of stock! Immediate restocking required.
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">
    <i class="ki-duotone ki-information fs-2tx text-warning me-4"></i>
    Low stock warning! Only 5 units remaining.
</div>
```

## 🔔 **Notifications**

The function automatically creates notifications for critical stock levels:

```php
// Automatically triggered when stock is critical or high urgency
if (in_array($stockInfo['urgency'], ['critical', 'high'])) {
    // Creates notification with:
    // - Type: 'stock_alert'
    // - Title: Translated alert title
    // - Message: Specific alert message
    // - Data: Product details and stock info
    // - Action URL: Link to product edit page
}
```

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:stock-status-info
```

### **Manual Testing**
```php
// Test different scenarios
$product = new Product();

// Out of stock
$info1 = $product->getStockStatusInfo(0, 10);

// Low stock
$info2 = $product->getStockStatusInfo(5, 10);

// Good stock
$info3 = $product->getStockStatusInfo(50, 10);
```

## 🌐 **Internationalization**

### **Translation Keys Added**
```php
// Vietnamese (resources/lang/vi/product.php)
'medium_stock' => 'Tồn kho vừa phải',
'stock_alert_title' => 'Cảnh báo tồn kho',
'reorder_suggestion' => 'Đề xuất đặt hàng :quantity đơn vị...',
'alert_out_of_stock' => 'Sản phẩm đã hết hàng!...',
// ... more keys

// English (resources/lang/en/product.php)
'medium_stock' => 'Medium Stock',
'stock_alert_title' => 'Stock Alert',
'reorder_suggestion' => 'Suggest ordering :quantity units...',
'alert_out_of_stock' => 'Product is out of stock!...',
// ... more keys
```

## 📈 **Performance Considerations**

### **1. Relationship Loading**
```php
// Efficient: Load inventory relationship
$products = Product::with('inventory')->get();
foreach ($products as $product) {
    $stockInfo = $product->getStockStatusInfo();
}

// Inefficient: N+1 query problem
$products = Product::all();
foreach ($products as $product) {
    $stockInfo = $product->getStockStatusInfo(); // Queries inventory each time
}
```

### **2. Caching**
```php
// Cache stock info for frequently accessed products
$cacheKey = "stock_info_product_{$product->id}";
$stockInfo = Cache::remember($cacheKey, 300, function() use ($product) {
    return $product->getStockStatusInfo();
});
```

## 🔧 **Configuration**

### **Stock Level Thresholds**
You can customize the stock level calculations by modifying the function:

```php
// Current logic:
// - Out of stock: <= 0
// - Low stock: <= reorder_point
// - Medium stock: <= (reorder_point * 2)
// - Good stock: > (reorder_point * 2)

// To customize, modify the conditions in getStockStatusInfo()
```

### **Sales Velocity Period**
```php
// Default: Analyzes last 30 days
// To change, modify the calculateDaysUntilOutOfStock() method
$salesLast30Days = $this->inventoryTransactions()
    ->where('transaction_type', 'sale')
    ->where('created_at', '>=', now()->subDays(30)) // Change this
    ->sum('quantity_change');
```

## 🚀 **Integration Examples**

### **1. Dashboard Widgets**
```php
// Get products needing attention
$criticalProducts = Product::with('inventory')
    ->get()
    ->filter(function($product) {
        $stockInfo = $product->getStockStatusInfo();
        return in_array($stockInfo['urgency'], ['critical', 'high']);
    });
```

### **2. Inventory Reports**
```php
// Generate stock status report
$stockReport = Product::with('inventory')
    ->get()
    ->groupBy(function($product) {
        return $product->getStockStatusInfo()['status'];
    });

$outOfStock = $stockReport['out_of_stock'] ?? collect();
$lowStock = $stockReport['low_stock'] ?? collect();
```

### **3. API Responses**
```php
// Include stock info in API responses
return response()->json([
    'product' => $product,
    'stock_info' => $product->getStockStatusInfo()
]);
```

---

**The `getStockStatusInfo()` function provides a comprehensive solution for stock management with visual indicators, intelligent alerts, and actionable insights! 🚀**
