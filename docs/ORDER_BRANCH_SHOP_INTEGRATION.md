# Order Branch Shop Integration Guide

## 📋 **Overview**

This feature integrates branch shops into the order creation process, allowing users to select a specific branch shop when creating orders. The system automatically handles delivery fees, branch-specific settings, and provides comprehensive branch shop management.

## ✅ **Implementation Details**

### **Branch Shop Selection in Orders**
When creating an order, users can:
1. Select from active branch shops
2. View branch shop details (type, delivery availability)
3. Automatically calculate delivery fees
4. Track orders by branch shop

### **Automatic Delivery Fee Calculation**
The system automatically:
- Sets shipping fee based on selected branch shop
- Updates order totals when branch shop changes
- Handles branches with/without delivery service

## 🔧 **Technical Implementation**

### **1. Database Structure**

#### **Orders Table Enhancement**
```sql
-- Added branch_shop_id column to orders table
ALTER TABLE orders ADD COLUMN branch_shop_id BIGINT UNSIGNED NULL;
ALTER TABLE orders ADD FOREIGN KEY (branch_shop_id) REFERENCES branch_shops(id);
```

#### **BranchShop Model Features**
- **Shop Types**: flagship, standard, mini, kiosk
- **Delivery Settings**: has_delivery, delivery_radius, delivery_fee
- **Location Data**: address, coordinates, working hours
- **Status Management**: active, inactive, maintenance

### **2. Order Form Enhancement**

#### **Branch Shop Selection Field**
```html
<div class="col-md-6 fv-row">
    <label class="required fs-6 fw-bold mb-2">Chi nhánh</label>
    <select name="branch_shop_id" class="form-select form-select-solid" data-control="select2">
        <option value="">Chọn chi nhánh</option>
        @foreach(\App\Models\BranchShop::active()->orderBy('sort_order')->get() as $branch)
            <option value="{{ $branch->id }}" 
                data-delivery="{{ $branch->has_delivery ? 'true' : 'false' }}"
                data-delivery-fee="{{ $branch->delivery_fee }}"
                data-address="{{ $branch->full_address }}">
                {{ $branch->name }} - {{ $branch->shop_type_label }}
            </option>
        @endforeach
    </select>
</div>
```

#### **JavaScript Integration**
```javascript
// Initialize branch shop select
var initBranchShopSelect = function() {
    $('select[name="branch_shop_id"]').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var hasDelivery = selectedOption.data('delivery') === 'true';
        var deliveryFee = selectedOption.data('delivery-fee') || 0;
        
        // Update shipping fee field
        var shippingFeeInput = $('input[name="shipping_fee"]');
        if (hasDelivery && deliveryFee > 0) {
            shippingFeeInput.val(deliveryFee);
        } else {
            shippingFeeInput.val(0);
        }
        
        // Update order totals
        calculateOrderTotal();
    });
};
```

### **3. OrderService Enhancement**

#### **Order Creation with Branch Shop**
```php
// Create order with branch shop
$order = $this->order->create([
    'order_code' => $orderCode,
    'customer_id' => $customer->id,
    'branch_shop_id' => $data['branch_shop_id'] ?? null,
    'branch_id' => $data['branch_id'] ?? $data['branch_shop_id'] ?? null, // Backward compatibility
    'shipping_fee' => $data['shipping_fee'] ?? 0,
    // ... other fields
]);
```

#### **Notification Enhancement**
```php
'data' => [
    'order_id' => $order->id,
    'order_code' => $order->order_code,
    'customer_name' => $order->customer->name,
    'branch_shop_name' => $order->branchShop ? $order->branchShop->name : null,
    'branch_shop_id' => $order->branch_shop_id,
    'channel' => $order->channel,
    // ... other fields
],
```

### **4. Model Relationships**

#### **Order Model**
```php
/**
 * Relationship with branch shop.
 */
public function branchShop()
{
    return $this->belongsTo(BranchShop::class, 'branch_shop_id');
}

/**
 * Scope for orders by branch shop.
 */
public function scopeByBranchShop($query, $branchShopId)
{
    return $query->where('branch_shop_id', $branchShopId);
}
```

#### **BranchShop Model**
```php
/**
 * Get orders from this branch shop
 */
public function orders()
{
    return $this->hasMany(Order::class, 'branch_shop_id');
}

/**
 * Scope a query to only include active branch shops
 */
public function scopeActive($query)
{
    return $query->where('status', 'active');
}
```

## 📊 **Branch Shop Data Structure**

### **Complete Branch Shop Record**
```php
[
    'id' => 1,
    'name' => 'YukiMart Quận 1',
    'code' => 'YM-Q1-001',
    'shop_type' => 'flagship',
    'address' => '123 Nguyễn Huệ',
    'ward' => 'Phường Bến Nghé',
    'district' => 'Quận 1',
    'province' => 'TP. Hồ Chí Minh',
    'phone' => '028-3822-1234',
    'email' => 'quan1@yukimart.vn',
    'manager_id' => 1,
    'status' => 'active',
    'area' => 250.50,
    'staff_count' => 15,
    'opening_time' => '07:00',
    'closing_time' => '22:00',
    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
    'has_delivery' => true,
    'delivery_radius' => 5.0,
    'delivery_fee' => 25000,
    'latitude' => 10.7769,
    'longitude' => 106.7009,
    'sort_order' => 1,
    'description' => 'Cửa hàng chính tại trung tâm Quận 1',
]
```

### **Shop Types and Features**
```php
'flagship' => [
    'label' => 'Cửa hàng chính',
    'features' => ['full_service', 'delivery', 'large_inventory'],
    'typical_area' => '300-500 m²',
    'staff_count' => '20-30 people'
],
'standard' => [
    'label' => 'Cửa hàng tiêu chuẩn',
    'features' => ['standard_service', 'delivery', 'medium_inventory'],
    'typical_area' => '150-300 m²',
    'staff_count' => '10-20 people'
],
'mini' => [
    'label' => 'Cửa hàng mini',
    'features' => ['basic_service', 'limited_delivery', 'small_inventory'],
    'typical_area' => '50-150 m²',
    'staff_count' => '5-10 people'
],
'kiosk' => [
    'label' => 'Quầy hàng',
    'features' => ['minimal_service', 'no_delivery', 'very_small_inventory'],
    'typical_area' => '10-50 m²',
    'staff_count' => '2-5 people'
]
```

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:order-branch-shop
```

### **Test Scenarios**
The test command covers:

1. **Prerequisites Check**: Ensures branch shops exist
2. **Branch Shop Selection**: Tests active branch shop listing
3. **Order Creation**: Tests order creation with branch shop
4. **Relationship Loading**: Verifies branchShop relationship
5. **Statistics**: Tests order statistics by branch shop
6. **Filtering**: Tests branch shop filtering and scopes

### **Manual Testing Steps**
1. **Visit Order Creation Page**: `/admin/orders/add`
2. **Select Branch Shop**: Choose from dropdown
3. **Verify Delivery Fee**: Check automatic fee calculation
4. **Create Order**: Complete order creation process
5. **Check Notification**: Verify branch shop info in notification
6. **View Order Details**: Confirm branch shop relationship

## 📈 **Benefits**

### **1. Enhanced Order Management**
- Clear branch shop assignment for each order
- Automatic delivery fee calculation
- Branch-specific order tracking

### **2. Better Business Intelligence**
- Sales performance by branch shop
- Delivery service utilization
- Geographic sales distribution

### **3. Improved User Experience**
- Intuitive branch shop selection
- Automatic fee calculation
- Clear delivery service indication

### **4. Operational Efficiency**
- Streamlined order processing
- Accurate delivery cost calculation
- Branch-specific inventory management

## 🔍 **Analytics and Reporting**

### **Branch Shop Performance Queries**
```php
// Orders by branch shop
$branchOrders = BranchShop::withCount('orders')->get();

// Revenue by branch shop
$branchRevenue = BranchShop::with(['orders' => function($query) {
    $query->selectRaw('branch_shop_id, SUM(final_amount) as total_revenue')
          ->groupBy('branch_shop_id');
}])->get();

// Delivery utilization
$deliveryStats = BranchShop::withDelivery()
    ->withCount(['orders' => function($query) {
        $query->where('shipping_fee', '>', 0);
    }])->get();
```

### **Order Filtering by Branch Shop**
```php
// Filter orders by specific branch shop
$orders = Order::byBranchShop($branchShopId)->get();

// Filter by shop type
$flagshipOrders = Order::whereHas('branchShop', function($query) {
    $query->ofType('flagship');
})->get();

// Filter by delivery availability
$deliveryOrders = Order::whereHas('branchShop', function($query) {
    $query->withDelivery();
})->where('shipping_fee', '>', 0)->get();
```

## ⚠️ **Important Considerations**

### **1. Data Validation**
- Ensure branch shop is active when creating orders
- Validate delivery fee against branch shop settings
- Check branch shop working hours for order timing

### **2. Backward Compatibility**
- Maintain support for existing `branch_id` field
- Handle orders without branch shop assignment
- Graceful fallback for missing branch shop data

### **3. Performance Optimization**
- Index on `branch_shop_id` in orders table
- Eager load branchShop relationship when needed
- Cache active branch shops for form population

### **4. Business Logic**
- Respect branch shop delivery radius
- Handle branch shop status changes
- Manage inventory across multiple branch shops

## 🚀 **Future Enhancements**

### **Potential Improvements**
1. **Inventory Integration**: Branch-specific inventory management
2. **Delivery Zones**: Advanced delivery zone mapping
3. **Staff Assignment**: Assign specific staff to orders
4. **Branch Analytics**: Comprehensive branch performance dashboard
5. **Multi-Branch Orders**: Support for orders spanning multiple branches
6. **Delivery Optimization**: Route optimization for delivery orders
7. **Branch Notifications**: Branch-specific notification system
8. **Mobile Integration**: Branch shop selection in mobile apps

### **Advanced Features**
```php
// Future configuration possibilities
'branch_shops' => [
    'auto_assign_nearest' => true,
    'delivery_zone_validation' => true,
    'inventory_sync' => true,
    'staff_notification' => true,
    'performance_tracking' => true
]
```

## 📞 **Support**

### **Troubleshooting**
- **Branch shop not showing**: Check if branch shop is active
- **Delivery fee not updating**: Verify JavaScript initialization
- **Order creation fails**: Check branch shop validation rules
- **Missing relationship**: Ensure proper model loading

### **Debugging**
```php
// Debug branch shop selection
$branchShop = BranchShop::find($branchShopId);
Log::debug('Branch shop selected', [
    'id' => $branchShop->id,
    'name' => $branchShop->name,
    'has_delivery' => $branchShop->has_delivery,
    'delivery_fee' => $branchShop->delivery_fee
]);

// Debug order creation
Log::info('Order created with branch shop', [
    'order_id' => $order->id,
    'branch_shop_id' => $order->branch_shop_id,
    'shipping_fee' => $order->shipping_fee
]);
```

---

**The branch shop integration provides comprehensive order management with location-specific features and automatic delivery fee calculation! 🚀**
