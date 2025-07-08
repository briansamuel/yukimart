# Tạo Khách Hàng Mới Trong Quá Trình Tạo Đơn Hàng

## Tổng quan

Tính năng này cho phép người dùng tạo khách hàng mới ngay trong quá trình tạo đơn hàng mà không cần phải rời khỏi trang hiện tại. Điều này giúp tăng hiệu quả công việc và cải thiện trải nghiệm người dùng.

## ✅ **Tính năng đã thực hiện**

### 1. **Form Tạo Khách Hàng Mới**
- **Tích hợp trong trang tạo đơn hàng** với giao diện thân thiện
- **Validation real-time** cho tất cả các trường dữ liệu
- **Kiểm tra trùng lặp** số điện thoại tự động
- **Multi-language support** hoàn chỉnh

### 2. **API Endpoints Mới**
- **POST `/admin/order/create-customer`** - Tạo khách hàng mới
- **GET `/admin/order/check-phone`** - Kiểm tra số điện thoại tồn tại
- **Enhanced customer search** với dữ liệu phong phú

### 3. **Enhanced User Experience**
- **Seamless integration** với dropdown khách hàng
- **Smart duplicate detection** với gợi ý chọn khách hàng có sẵn
- **Loading states** và error handling toàn diện
- **Keyboard shortcuts** cho thao tác nhanh

## 🎯 **Cách sử dụng**

### **Bước 1: Truy cập trang tạo đơn hàng**
```
/admin/order/add
```

### **Bước 2: Chọn "Khách hàng mới"**
1. Click vào dropdown "Khách hàng"
2. Chọn option "Khách hàng mới" hoặc click nút "+" bên cạnh
3. Form tạo khách hàng mới sẽ hiển thị

### **Bước 3: Điền thông tin khách hàng**
- **Tên khách hàng** (bắt buộc): Ít nhất 2 ký tự
- **Số điện thoại** (bắt buộc): 10-15 số, tự động kiểm tra trùng lặp
- **Email** (tùy chọn): Validation format email
- **Địa chỉ** (tùy chọn): Địa chỉ chi tiết
- **Loại khách hàng**: Cá nhân/Doanh nghiệp/VIP

### **Bước 4: Lưu khách hàng**
1. Click "Tạo khách hàng"
2. Hệ thống sẽ:
   - Validate dữ liệu
   - Kiểm tra trùng lặp số điện thoại
   - Tạo khách hàng mới
   - Tự động chọn khách hàng vừa tạo
   - Ẩn form và tiếp tục tạo đơn hàng

## 🔧 **Chi tiết kỹ thuật**

### **Backend Implementation**

#### **OrderService Enhancements**
```php
/**
 * Create new customer during order creation.
 */
public function createNewCustomer($customerData)
{
    // Validate required fields
    if (empty($customerData['name']) || empty($customerData['phone'])) {
        return [
            'success' => false,
            'message' => 'Tên và số điện thoại khách hàng là bắt buộc'
        ];
    }

    // Check if phone number already exists
    $existingCustomer = $this->customer->where('phone', $customerData['phone'])->first();
    if ($existingCustomer) {
        return [
            'success' => false,
            'message' => 'Số điện thoại đã tồn tại trong hệ thống',
            'existing_customer' => [
                'id' => $existingCustomer->id,
                'name' => $existingCustomer->name,
                'phone' => $existingCustomer->phone,
                'email' => $existingCustomer->email
            ]
        ];
    }

    // Create new customer
    $customer = $this->customer->create([
        'name' => trim($customerData['name']),
        'phone' => trim($customerData['phone']),
        'email' => !empty($customerData['email']) ? trim($customerData['email']) : null,
        'address' => !empty($customerData['address']) ? trim($customerData['address']) : null,
        'customer_type' => $customerData['customer_type'] ?? 'individual',
        'status' => 'active'
    ]);

    return [
        'success' => true,
        'message' => 'Khách hàng mới đã được tạo thành công',
        'data' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'display_text' => $customer->name . ' - ' . $customer->phone
        ]
    ];
}

/**
 * Validate customer data for order creation.
 */
public function validateCustomerData($customerData)
{
    $errors = [];

    // Validate name
    if (empty($customerData['name']) || strlen(trim($customerData['name'])) < 2) {
        $errors['name'] = 'Tên khách hàng phải có ít nhất 2 ký tự';
    }

    // Validate phone
    if (empty($customerData['phone'])) {
        $errors['phone'] = 'Số điện thoại là bắt buộc';
    } elseif (!preg_match('/^[0-9+\-\s\(\)]{10,15}$/', $customerData['phone'])) {
        $errors['phone'] = 'Số điện thoại không hợp lệ';
    }

    // Validate email if provided
    if (!empty($customerData['email']) && !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
```

#### **Controller Methods**
```php
/**
 * Create new customer during order creation.
 */
public function createNewCustomer()
{
    $customerData = $this->request->all();
    
    // Validate customer data
    $validation = $this->orderService->validateCustomerData($customerData);
    if (!$validation['valid']) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu khách hàng không hợp lệ',
            'errors' => $validation['errors']
        ], 422);
    }

    $result = $this->orderService->createNewCustomer($customerData);
    return response()->json($result);
}

/**
 * Check if phone number exists.
 */
public function checkPhoneExists()
{
    $phone = $this->request->get('phone');
    $customer = \App\Models\Customer::where('phone', $phone)->first();
    
    if ($customer) {
        return response()->json([
            'success' => true,
            'exists' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email
            ]
        ]);
    }

    return response()->json([
        'success' => true,
        'exists' => false
    ]);
}
```

### **Frontend Implementation**

#### **Enhanced Customer Dropdown**
```html
<select name="customer_id" id="customer_id" class="form-select form-select-solid me-3">
    <option value="">{{ __('orders.select_customer') }}</option>
    <option value="new_customer">{{ __('orders.new_customer') }}</option>
</select>
<button type="button" class="btn btn-light-primary" id="btn_add_new_customer">
    <i class="fas fa-plus"></i>
</button>
```

#### **New Customer Form**
```html
<div id="new_customer_form" class="row g-9 mt-5" style="display: none;">
    <div class="col-12">
        <div class="card card-bordered">
            <div class="card-header">
                <h3 class="card-title">{{ __('orders.new_customer_info') }}</h3>
            </div>
            <div class="card-body">
                <div class="row g-6">
                    <div class="col-md-6 fv-row">
                        <label class="required fs-6 fw-bold mb-2">{{ __('orders.customer_name') }}</label>
                        <input type="text" class="form-control" id="new_customer_name" />
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="required fs-6 fw-bold mb-2">{{ __('orders.customer_phone') }}</label>
                        <input type="text" class="form-control" id="new_customer_phone" />
                    </div>
                    <!-- More fields... -->
                </div>
            </div>
        </div>
    </div>
</div>
```

#### **JavaScript Functions**
```javascript
// Create new customer
var createNewCustomer = function() {
    if (!validateNewCustomerForm()) {
        return;
    }

    var customerData = {
        name: $('#new_customer_name').val().trim(),
        phone: $('#new_customer_phone').val().trim(),
        email: $('#new_customer_email').val().trim(),
        address: $('#new_customer_address').val().trim(),
        customer_type: $('#new_customer_type').val()
    };

    // Check if phone exists first
    checkPhoneExists(customerData.phone, function(exists, existingCustomer) {
        if (exists) {
            // Show option to select existing customer
            Swal.fire({
                title: 'Số điện thoại đã tồn tại',
                text: `Khách hàng "${existingCustomer.name}" đã sử dụng số điện thoại này.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Chọn khách hàng này',
                cancelButtonText: 'Hủy'
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Select existing customer
                    var option = new Option(
                        existingCustomer.name + ' - ' + existingCustomer.phone,
                        existingCustomer.id,
                        true,
                        true
                    );
                    customerSelect.append(option).trigger('change');
                    hideNewCustomerForm();
                }
            });
            return;
        }

        // Proceed with creating new customer
        $.ajax({
            url: '/admin/order/create-customer',
            type: 'POST',
            data: customerData,
            success: function(response) {
                if (response.success) {
                    // Add new customer to select
                    var option = new Option(
                        response.data.display_text,
                        response.data.id,
                        true,
                        true
                    );
                    customerSelect.append(option).trigger('change');
                    hideNewCustomerForm();
                    
                    Swal.fire({
                        text: response.message,
                        icon: 'success'
                    });
                }
            }
        });
    });
};
```

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:new-customer-feature
```

### **Manual Testing Steps**

1. **Truy cập trang tạo đơn hàng:**
   - Vào `/admin/order/add`
   - Kiểm tra dropdown khách hàng có option "Khách hàng mới"

2. **Test tạo khách hàng mới:**
   - Chọn "Khách hàng mới"
   - Điền thông tin hợp lệ
   - Click "Tạo khách hàng"
   - Kiểm tra khách hàng được tạo và chọn tự động

3. **Test validation:**
   - Thử bỏ trống tên và số điện thoại
   - Nhập email không hợp lệ
   - Kiểm tra thông báo lỗi hiển thị đúng

4. **Test duplicate detection:**
   - Nhập số điện thoại đã tồn tại
   - Kiểm tra thông báo và option chọn khách hàng có sẵn

## 📁 **Files Created/Modified**

### **Backend:**
- `app/Services/OrderService.php` - Added customer creation methods
- `app/Http/Controllers/Admin/CMS/OrderController.php` - New endpoints
- `routes/admin.php` - New routes for customer creation
- `app/Console/Commands/TestNewCustomerFeature.php` - Test command

### **Frontend:**
- `resources/views/admin/orders/add.blade.php` - Enhanced UI
- `public/admin/assets/js/custom/apps/orders/list/add.js` - New customer logic

### **Language Files:**
- `lang/vi/orders.php` - Vietnamese translations
- `lang/en/orders.php` - English translations

### **Documentation:**
- `docs/NEW_CUSTOMER_IN_ORDER_CREATION.md` - This documentation

## 🎉 **Benefits**

### **1. Improved Workflow:**
- ⚡ **Không cần rời trang** để tạo khách hàng mới
- 🔄 **Seamless integration** với quy trình tạo đơn hàng
- ⏱️ **Tiết kiệm thời gian** đáng kể

### **2. Enhanced User Experience:**
- 🎨 **Giao diện thân thiện** và intuitive
- ✅ **Real-time validation** với feedback tức thì
- 🚨 **Smart duplicate detection** tránh tạo trùng lặp

### **3. Data Quality:**
- 📊 **Validation toàn diện** đảm bảo chất lượng dữ liệu
- 🔍 **Duplicate prevention** tự động
- 📝 **Consistent data format** across the system

## 🚀 **Future Enhancements**

1. **Advanced Features:**
   - Import khách hàng từ file Excel
   - Tích hợp với CRM systems
   - Customer history tracking

2. **UI/UX Improvements:**
   - Auto-complete địa chỉ
   - Customer avatar upload
   - Quick customer templates

3. **Integration:**
   - SMS verification cho số điện thoại
   - Email verification
   - Social media integration

## 🎯 **Conclusion**

Tính năng tạo khách hàng mới trong quá trình tạo đơn hàng đã được triển khai thành công với:
- ✅ **Complete functionality** cho tạo khách hàng mới
- ✅ **Seamless integration** với existing order flow
- ✅ **Comprehensive validation** và error handling
- ✅ **Multi-language support** hoàn chỉnh
- ✅ **Professional UI/UX** design
- ✅ **Robust testing** tools

Hệ thống hiện tại đã sẵn sàng cho production và mang lại trải nghiệm người dùng tuyệt vời!
