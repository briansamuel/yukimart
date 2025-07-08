# Translation Setup - resources/lang Directory

## Tổng quan

Từ giờ trở đi, tất cả các translation keys sẽ được quản lý trong thư mục `resources/lang` thay vì `lang`. Điều này đảm bảo tính nhất quán và dễ dàng quản lý.

## 📁 **Cấu trúc thư mục**

```
resources/lang/
├── en/
│   ├── admin.php
│   ├── app.php
│   ├── auth.php
│   ├── branch_shop.php
│   ├── common.php
│   ├── customer.php
│   ├── inventory.php
│   ├── invoice.php
│   ├── menu.php
│   ├── order.php          # ← File chính cho order translations
│   ├── pagination.php
│   ├── passwords.php
│   ├── permission.php
│   ├── product.php
│   ├── reports.php
│   └── validation.php
├── vi/
│   ├── admin.php
│   ├── app.php
│   ├── audit_log.php
│   ├── backup.php
│   ├── branch_shop.php
│   ├── common.php
│   ├── customer.php
│   ├── inventory.php
│   ├── inventory_import_export.php
│   ├── invoice.php
│   ├── menu.php
│   ├── notification.php
│   ├── order.php          # ← File chính cho order translations
│   ├── permission.php
│   ├── product.php
│   ├── product_category.php
│   ├── reports.php
│   ├── user_settings.php
│   └── validation.php
└── vendor/
    └── rfm/
```

## 🎯 **Translation Keys cho New Customer Feature**

### **Customer Information**
```php
// resources/lang/vi/order.php & resources/lang/en/order.php

// Customer Fields
'customer' => 'Khách hàng' / 'Customer',
'customer_information' => 'Thông tin khách hàng' / 'Customer Information',
'select_customer' => 'Chọn khách hàng' / 'Select customer',
'search_customer' => 'Tìm kiếm khách hàng...' / 'Search customer...',

// New Customer Creation
'new_customer' => 'Khách hàng mới' / 'New Customer',
'add_new_customer' => 'Thêm khách hàng mới' / 'Add New Customer',
'new_customer_info' => 'Thông tin khách hàng mới' / 'New Customer Information',
'create_customer' => 'Tạo khách hàng' / 'Create Customer',

// Customer Form Fields
'customer_name' => 'Tên khách hàng' / 'Customer Name',
'customer_phone' => 'Số điện thoại' / 'Phone Number',
'customer_email' => 'Email' / 'Email',
'customer_address' => 'Địa chỉ' / 'Address',
'customer_type' => 'Loại khách hàng' / 'Customer Type',

// Customer Types
'individual' => 'Cá nhân' / 'Individual',
'business' => 'Doanh nghiệp' / 'Business',
'vip' => 'VIP' / 'VIP',

// Placeholders
'enter_customer_name' => 'Nhập tên khách hàng' / 'Enter customer name',
'enter_customer_phone' => 'Nhập số điện thoại' / 'Enter phone number',
'enter_customer_email' => 'Nhập email (tùy chọn)' / 'Enter email (optional)',
'enter_customer_address' => 'Nhập địa chỉ (tùy chọn)' / 'Enter address (optional)',
```

### **Messages & Validation**
```php
// Success/Error Messages
'customer_created_success' => 'Khách hàng mới đã được tạo thành công' / 'New customer has been created successfully',
'customer_creation_error' => 'Có lỗi xảy ra khi tạo khách hàng mới' / 'An error occurred while creating new customer',
'phone_exists' => 'Số điện thoại đã tồn tại trong hệ thống' / 'Phone number already exists in the system',
'phone_exists_customer' => 'Số điện thoại đã được sử dụng bởi khách hàng: :name' / 'Phone number is already used by customer: :name',
'select_existing_customer' => 'Chọn khách hàng này' / 'Select this customer',

// Validation Messages
'customer_name_required' => 'Tên khách hàng phải có ít nhất 2 ký tự' / 'Customer name must be at least 2 characters',
'customer_phone_required' => 'Số điện thoại là bắt buộc' / 'Phone number is required',
'customer_phone_invalid' => 'Số điện thoại không hợp lệ' / 'Phone number is invalid',
'customer_email_invalid' => 'Email không hợp lệ' / 'Email is invalid',
```

### **Order Creation**
```php
// Order Information
'create_order_description' => 'Tạo đơn hàng và cập nhật tồn kho' / 'Create order and update inventory',
'order_information' => 'Thông tin đơn hàng' / 'Order Information',
'order_summary' => 'Tổng kết đơn hàng' / 'Order Summary',
'products' => 'Sản phẩm' / 'Products',

// Sales Channels
'sales_channel' => 'Kênh bán hàng' / 'Sales Channel',
'select_sales_channel' => 'Chọn kênh bán hàng' / 'Select sales channel',
'online' => 'Online' / 'Online',
'offline' => 'Offline' / 'Offline',
'phone' => 'Điện thoại' / 'Phone',

// Product Search
'search_product' => 'Tìm kiếm sản phẩm' / 'Search Product',
'search_product_placeholder' => 'Tìm kiếm sản phẩm để thêm vào đơn hàng...' / 'Search products to add to order...',
'stock' => 'Tồn kho' / 'Stock',
'actions' => 'Thao tác' / 'Actions',
'no_products' => 'Chưa có sản phẩm nào' / 'No products added yet',
'remove_product' => 'Xóa sản phẩm' / 'Remove Product',

// Order Summary
'subtotal' => 'Tạm tính' / 'Subtotal',
'subtotal_description' => 'Tổng tiền sản phẩm' / 'Total product amount',
'discount' => 'Giảm giá' / 'Discount',
'shipping_fee' => 'Phí vận chuyển' / 'Shipping Fee',
'tax' => 'Thuế' / 'Tax',
'total' => 'Tổng cộng' / 'Total',
'total_description' => 'Tổng tiền phải thanh toán' / 'Total amount to pay',
'notes' => 'Ghi chú' / 'Notes',
'order_notes_placeholder' => 'Ghi chú đơn hàng...' / 'Order notes...',
```

## 🔧 **Cách sử dụng trong Blade Templates**

### **Basic Usage**
```blade
{{-- Simple translation --}}
<label>{{ __('order.customer_name') }}</label>

{{-- Translation with placeholder --}}
<input placeholder="{{ __('order.enter_customer_name') }}">

{{-- Translation with parameters --}}
<span>{{ __('order.phone_exists_customer', ['name' => $customer->name]) }}</span>
```

### **Conditional Translations**
```blade
{{-- Check if translation exists --}}
@if(__('order.new_customer') !== 'order.new_customer')
    <option value="new_customer">{{ __('order.new_customer') }}</option>
@endif
```

### **JavaScript Integration**
```blade
<script>
    const translations = {
        customerCreatedSuccess: '{{ __('order.customer_created_success') }}',
        phoneExists: '{{ __('order.phone_exists') }}',
        customerNameRequired: '{{ __('order.customer_name_required') }}',
        customerPhoneRequired: '{{ __('order.customer_phone_required') }}'
    };
</script>
```

## 🧪 **Testing Translations**

### **Test Command**
```bash
php artisan test:translations
```

### **Manual Testing**
1. **Change locale in config:**
   ```php
   // config/app.php
   'locale' => 'vi', // or 'en'
   ```

2. **Test in browser:**
   - Visit `/admin/order/add`
   - Check all text displays correctly
   - Switch language and verify

3. **Test in Tinker:**
   ```bash
   php artisan tinker
   >>> app()->setLocale('vi');
   >>> __('order.new_customer');
   >>> app()->setLocale('en');
   >>> __('order.new_customer');
   ```

## 📝 **Best Practices**

### **1. Naming Convention**
- Use **dot notation**: `order.customer_name`
- Group related keys: `order.customer_*`, `order.product_*`
- Use descriptive names: `customer_name_required` not `name_req`

### **2. File Organization**
- **order.php**: All order-related translations
- **common.php**: Shared translations (cancel, save, etc.)
- **menu.php**: Navigation and menu items

### **3. Parameter Usage**
```php
// Good: Use named parameters
'phone_exists_customer' => 'Phone used by customer: :name',

// Usage
__('order.phone_exists_customer', ['name' => $customer->name])
```

### **4. Fallback Handling**
```blade
{{-- Always provide fallback --}}
{{ __('order.customer_name', [], 'Customer Name') }}

{{-- Or check existence --}}
@if(__('order.customer_name') !== 'order.customer_name')
    {{ __('order.customer_name') }}
@else
    Customer Name
@endif
```

## 🔄 **Migration from lang/ to resources/lang/**

### **Steps to migrate:**
1. **Move files:**
   ```bash
   mv lang/vi/orders.php resources/lang/vi/order.php
   mv lang/en/orders.php resources/lang/en/order.php
   ```

2. **Update references:**
   ```blade
   {{-- Old --}}
   {{ __('orders.new_customer') }}
   
   {{-- New --}}
   {{ __('order.new_customer') }}
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## 🎯 **Key Benefits**

### **1. Consistency**
- ✅ All translations in `resources/lang/`
- ✅ Standardized naming convention
- ✅ Organized by feature/module

### **2. Maintainability**
- ✅ Easy to find and update translations
- ✅ Clear separation of concerns
- ✅ Version control friendly

### **3. Performance**
- ✅ Laravel's built-in caching
- ✅ Efficient key lookup
- ✅ Minimal memory footprint

### **4. Developer Experience**
- ✅ IDE autocompletion support
- ✅ Easy testing and validation
- ✅ Clear documentation

## 🔗 **Related Files**

- **Translation Files**: `resources/lang/{locale}/{module}.php`
- **Config**: `config/app.php` (locale settings)
- **Blade Templates**: Use `{{ __('key') }}` syntax
- **JavaScript**: Pass translations via blade to JS
- **Testing**: `app/Console/Commands/TestTranslations.php`

## 🎉 **Conclusion**

Việc sử dụng `resources/lang/` thay vì `lang/` giúp:
- ✅ **Tổ chức tốt hơn** các file translation
- ✅ **Dễ dàng maintain** và update
- ✅ **Consistent** với Laravel conventions
- ✅ **Scalable** cho các tính năng tương lai

Tất cả translation keys cho tính năng "New Customer in Order Creation" đã được setup hoàn chỉnh và sẵn sàng sử dụng!
