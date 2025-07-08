# User Settings Branch Shop Integration Guide

## 📋 **Overview**

This feature moves branch shop selection from the order creation form to user settings, allowing each user to set a default branch shop. When creating orders, the system automatically uses the user's default branch shop and filters products based on the associated warehouse inventory.

## ✅ **Implementation Summary**

### **Key Changes Made:**

1. **Removed Branch Shop Selection from Order Form**
   - Branch shop dropdown removed from order creation
   - Replaced with read-only display showing current default branch shop
   - Added "Change" button linking to user settings

2. **Added Branch Shop to User Settings**
   - Branch shop selection in user settings interface
   - Real-time display of branch shop information (address, delivery, warehouse)
   - Persistent storage in user_settings table

3. **Automatic Branch Shop Assignment**
   - Orders automatically use user's default branch shop
   - Validation ensures branch shop is set before order creation
   - Fallback handling for users without default branch shop

4. **Warehouse-Based Product Filtering**
   - Products filtered by branch shop's associated warehouse
   - Only shows products with available inventory in the warehouse
   - Improves inventory accuracy and prevents overselling

## 🔧 **Technical Implementation**

### **1. User Settings Storage**

#### **UserSetting Model**
```php
// User model methods for settings
public function getSetting($key, $default = null)
{
    $setting = \App\Models\UserSetting::where('user_id', $this->id)
                                     ->where('key', $key)
                                     ->first();
    
    return $setting ? $setting->value : $default;
}

public function setSetting($key, $value)
{
    return \App\Models\UserSetting::updateOrCreate(
        ['user_id' => $this->id, 'key' => $key],
        ['value' => $value]
    );
}
```

### **2. Order Form Modification**

#### **Before (Branch Shop Selection)**
```html
<select name="branch_shop_id" class="form-select form-select-solid">
    <option value="">Chọn chi nhánh</option>
    @foreach($branchShops as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</select>
```

#### **After (Read-only Display)**
```html
<div class="form-control form-control-solid d-flex align-items-center">
    <i class="ki-duotone ki-shop fs-2 text-primary me-3"></i>
    <div class="flex-grow-1">
        <div class="fw-bold">{{ $defaultBranchShop->name }}</div>
        <div class="text-muted fs-7">{{ $defaultBranchShop->shop_type_label }}</div>
    </div>
    <div class="ms-auto">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-light-primary">
            Thay đổi
        </a>
    </div>
</div>
<input type="hidden" name="branch_shop_id" value="{{ $defaultBranchShop->id }}">
```

### **3. User Settings Interface**

#### **Branch Shop Selection with Info Display**
```html
<select name="default_branch_shop" class="form-select form-select-solid">
    @foreach($branchShops as $branch)
        <option value="{{ $branch->id }}" 
            data-warehouse="{{ $branch->warehouse->name }}"
            data-address="{{ $branch->full_address }}"
            data-delivery="{{ $branch->has_delivery ? 'Có giao hàng' : 'Không giao hàng' }}">
            {{ $branch->name }} - {{ $branch->shop_type_label }}
            @if($branch->warehouse)
                (Kho: {{ $branch->warehouse->name }})
            @endif
        </option>
    @endforeach
</select>

<!-- Branch Shop Info Display -->
<div id="branch-shop-info" class="mt-4">
    <div class="card card-bordered">
        <div class="card-body p-4">
            <h6 class="card-title">Thông tin chi nhánh</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <i class="ki-duotone ki-geolocation fs-4 text-primary me-2"></i>
                        <span id="branch-address"></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <i class="ki-duotone ki-delivery fs-4 text-success me-2"></i>
                        <span id="branch-delivery"></span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-package fs-4 text-warning me-2"></i>
                        <span id="branch-warehouse"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### **4. OrderService Enhancement**

#### **Automatic Branch Shop Assignment**
```php
public function createOrder(array $data)
{
    // Set branch_shop_id from user settings if not provided
    if (empty($data['branch_shop_id'])) {
        $data['branch_shop_id'] = Auth::user()->getSetting('default_branch_shop');
    }
    
    // Validate that branch shop is set
    if (empty($data['branch_shop_id'])) {
        return [
            'success' => false,
            'message' => 'Vui lòng cài đặt chi nhánh mặc định trong phần cài đặt người dùng.',
            'data' => null
        ];
    }
    
    // Continue with order creation...
}
```

#### **Warehouse-Based Product Filtering**
```php
public function getProductsForOrder($search = '')
{
    // Get user's default branch shop and its warehouse
    $defaultBranchShopId = Auth::user()->getSetting('default_branch_shop');
    $warehouseId = null;
    
    if ($defaultBranchShopId) {
        $branchShop = \App\Models\BranchShop::find($defaultBranchShopId);
        if ($branchShop && $branchShop->warehouse_id) {
            $warehouseId = $branchShop->warehouse_id;
        }
    }

    $query = $this->product->with(['inventory' => function($q) use ($warehouseId) {
        if ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        }
    }]);

    // If warehouse is specified, only show products with inventory
    if ($warehouseId) {
        $query->whereHas('inventory', function($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId)
              ->where('quantity', '>', 0);
        });
    }

    return $query->where('product_status', 'publish')
                ->orderBy('product_name')
                ->limit(100)
                ->get();
}
```

### **5. Database Schema Updates**

#### **Branch Shop Warehouse Relationship**
```sql
-- Add warehouse_id to branch_shops table
ALTER TABLE branch_shops ADD COLUMN warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE branch_shops ADD FOREIGN KEY (warehouse_id) REFERENCES warehouses(id);
```

#### **BranchShop Model Enhancement**
```php
/**
 * Get the warehouse for this branch shop
 */
public function warehouse()
{
    return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id');
}
```

## 📊 **User Experience Flow**

### **1. Initial Setup**
1. User logs in to admin panel
2. Visits User Settings (`/admin/settings`)
3. Selects default branch shop from dropdown
4. Views branch shop information (address, delivery, warehouse)
5. Saves settings

### **2. Order Creation**
1. User visits Order Creation (`/admin/orders/add`)
2. Sees current default branch shop (read-only)
3. Can click "Change" to modify in settings
4. Products are automatically filtered by branch shop's warehouse
5. Order is created with default branch shop

### **3. Settings Management**
1. User can change default branch shop anytime
2. Real-time preview of branch shop information
3. Settings are saved per user
4. Changes apply immediately to new orders

## 🎯 **Benefits**

### **1. Improved User Experience**
- **Streamlined Order Creation**: No need to select branch shop every time
- **Consistent Branch Assignment**: Orders always use user's preferred branch
- **Quick Settings Access**: Easy to change default branch shop

### **2. Better Inventory Management**
- **Warehouse-Specific Products**: Only shows products available in branch's warehouse
- **Accurate Stock Levels**: Prevents overselling from other warehouses
- **Location-Based Inventory**: Matches physical inventory with digital records

### **3. Enhanced Business Operations**
- **User-Branch Association**: Clear assignment of users to specific branches
- **Operational Efficiency**: Reduces errors in branch selection
- **Inventory Accuracy**: Better tracking of warehouse-specific stock

### **4. System Reliability**
- **Validation**: Ensures branch shop is always set before order creation
- **Fallback Handling**: Clear error messages for missing settings
- **Data Integrity**: Maintains consistency between users and branches

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:user-settings-branch-shop
```

### **Test Scenarios**
1. **User Settings Storage**: Test getSetting/setSetting methods
2. **Branch Shop Warehouse Relationship**: Verify warehouse associations
3. **Order Creation**: Test automatic branch shop assignment
4. **Product Filtering**: Verify warehouse-based product filtering
5. **Settings Interface**: Test user settings form functionality

### **Manual Testing Steps**
1. **Set Default Branch Shop**:
   - Visit `/admin/settings`
   - Select a branch shop
   - Verify information display
   - Save settings

2. **Create Order**:
   - Visit `/admin/orders/add`
   - Verify branch shop is pre-selected
   - Check product availability
   - Create order successfully

3. **Change Branch Shop**:
   - Change default branch shop in settings
   - Verify order form updates
   - Check product list changes

## ⚠️ **Important Considerations**

### **1. Migration Requirements**
- Run migration to add `warehouse_id` to `branch_shops` table
- Update existing branch shops with warehouse assignments
- Ensure all users have default branch shop set

### **2. Data Validation**
- Validate branch shop exists and is active
- Ensure warehouse is assigned to branch shop
- Check user has permission to use selected branch shop

### **3. Performance Optimization**
- Cache user settings for frequent access
- Index warehouse_id in branch_shops table
- Optimize product queries with warehouse filtering

### **4. Error Handling**
- Handle missing default branch shop gracefully
- Provide clear error messages for validation failures
- Fallback to system default if user setting is invalid

## 🔮 **Future Enhancements**

### **Potential Improvements**
1. **Multi-Branch Access**: Allow users to access multiple branches
2. **Branch-Specific Permissions**: Role-based branch access control
3. **Automatic Branch Detection**: GPS-based branch selection
4. **Branch Performance Dashboard**: User-specific branch analytics
5. **Inventory Alerts**: Warehouse-specific low stock notifications

### **Advanced Features**
```php
// Future configuration possibilities
'user_branch_settings' => [
    'allow_multiple_branches' => true,
    'auto_detect_location' => true,
    'branch_specific_permissions' => true,
    'inventory_notifications' => true,
    'performance_tracking' => true
]
```

## 📞 **Support**

### **Troubleshooting**
- **Branch shop not set**: Guide user to settings page
- **Products not showing**: Check warehouse assignment
- **Order creation fails**: Verify branch shop validation
- **Settings not saving**: Check user_settings table

### **Configuration**
```php
// User settings configuration
'default_branch_shop' => null,
'theme_mode' => 'light',
'language' => 'vi',
'email_notifications' => true,
'web_notifications' => true,
'items_per_page' => 25,
'date_format' => 'd/m/Y'
```

---

**The user settings branch shop integration provides a seamless, warehouse-aware order creation experience! 🚀**
