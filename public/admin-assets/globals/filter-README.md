# Global Filter System (KTGlobalFilter)

Module tái sử dụng để quản lý các bộ lọc dữ liệu trên các trang danh sách.

## Tính năng

- ✅ Bộ lọc thời gian với dropdown panel và các tùy chọn có sẵn
- ✅ Bộ lọc trạng thái với checkbox
- ✅ Bộ lọc người tạo với Select2
- ✅ Bộ lọc người bán với Select2  
- ✅ Bộ lọc kênh bán hàng
- ✅ Bộ lọc phương thức thanh toán với Select2
- ✅ Callback tự động khi thay đổi bộ lọc
- ✅ Hỗ trợ tùy chọn bật/tắt từng loại filter
- ✅ Tương thích với nhiều form trên cùng một trang

## Cài đặt

### 1. Include file JavaScript

Thêm file `filter.js` trước các file JS riêng của trang:

```html
@section('scripts')
<script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
<script src="{{ asset('admin-assets/js/your-page.js') }}"></script>
@endsection
```

### 2. HTML Structure

#### Bộ lọc thời gian:
```html
<!-- Time Filter Trigger -->
<button type="button" id="time_filter_trigger" class="btn btn-sm btn-light">
    <i class="fas fa-calendar-alt"></i>
    <span>Tháng này</span>
    <i class="fas fa-chevron-down ms-2"></i>
</button>

<!-- Time Options Panel -->
<div id="time_options_panel" class="time-options-panel">
    <div class="panel-content">
        <div class="panel-header">
            <h6 class="mb-0">Chọn khoảng thời gian</h6>
            <button type="button" id="close_time_panel" class="btn btn-sm btn-icon">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-body">
            <div class="time-options-grid">
                <button type="button" class="btn btn-light-primary time-option" data-value="today">Hôm nay</button>
                <button type="button" class="btn btn-light-primary time-option" data-value="yesterday">Hôm qua</button>
                <button type="button" class="btn btn-light-primary time-option" data-value="this_week">Tuần này</button>
                <button type="button" class="btn btn-light-primary time-option" data-value="last_week">Tuần trước</button>
                <button type="button" class="btn btn-light-primary time-option" data-value="this_month">Tháng này</button>
                <button type="button" class="btn btn-light-primary time-option" data-value="last_month">Tháng trước</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range -->
<div class="form-check">
    <input class="form-check-input" type="radio" name="time_filter_display" id="time_custom" value="custom">
    <label class="form-check-label" for="time_custom">Tùy chỉnh</label>
</div>
<div id="custom_date_range" style="display: none;">
    <input type="date" id="date_from" name="date_from" class="form-control">
    <input type="date" id="date_to" name="date_to" class="form-control">
</div>
```

#### Bộ lọc trạng thái:
```html
<div class="filter-status">
    <h6>Trạng thái</h6>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="status[]" value="paid" id="status_paid">
        <label class="form-check-label" for="status_paid">Đã thanh toán</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="status[]" value="unpaid" id="status_unpaid">
        <label class="form-check-label" for="status_unpaid">Chưa thanh toán</label>
    </div>
</div>
```

#### Bộ lọc Select2:
```html
<!-- Creators Filter -->
<select name="created_by" class="form-select" data-control="select2">
    <option value="">Tất cả người tạo</option>
    <option value="1">User 1</option>
    <option value="2">User 2</option>
</select>

<!-- Sellers Filter -->
<select name="seller_id" class="form-select" data-control="select2">
    <option value="">Tất cả người bán</option>
    <option value="1">Seller 1</option>
    <option value="2">Seller 2</option>
</select>

<!-- Payment Methods Filter -->
<select name="payment_method" class="form-select" data-control="select2">
    <option value="">Tất cả phương thức</option>
    <option value="cash">Tiền mặt</option>
    <option value="card">Thẻ</option>
    <option value="transfer">Chuyển khoản</option>
</select>
```

### 3. CSS Styling

Thêm CSS cho time panel:

```css
.time-options-panel {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    width: 400px;
    margin-top: 5px;
    display: none;
}

.time-options-panel.show {
    display: block;
    animation: slideDown 0.3s ease;
}

.time-options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.time-option {
    text-align: left;
    border: 1px solid #e4e6ef;
}

.time-option.active {
    background-color: #009ef7 !important;
    color: white !important;
}
```

## Cách sử dụng

### Cơ bản - Khởi tạo tất cả bộ lọc:

```javascript
var loadData = function() {
    // Logic tải dữ liệu của bạn
    console.log('Loading data with filters...');
    
    // Ví dụ AJAX call
    $.ajax({
        url: '/admin/your-endpoint/ajax',
        data: getFilterData(),
        success: function(response) {
            renderData(response.data);
        }
    });
};

// Khởi tạo tất cả bộ lọc
KTGlobalFilter.initAllFilters('#your_filter_form', loadData);
```

### Nâng cao - Chọn bộ lọc cụ thể:

```javascript
// Chỉ khởi tạo một số bộ lọc
KTGlobalFilter.initAllFilters('#your_filter_form', loadData, {
    timeFilter: true,        // Bật bộ lọc thời gian
    statusFilter: true,      // Bật bộ lọc trạng thái
    creatorsFilter: false,   // Tắt bộ lọc người tạo
    sellersFilter: true,     // Bật bộ lọc người bán
    saleChannelsFilter: false, // Tắt bộ lọc kênh bán
    paymentMethodsFilter: true // Bật bộ lọc phương thức thanh toán
});
```

### Khởi tạo từng bộ lọc riêng lẻ:

```javascript
// Thiết lập callback trước
KTGlobalFilter.setLoadDataCallback(loadData);

// Khởi tạo từng bộ lọc
KTGlobalFilter.initTimeFilter('#your_filter_form');
KTGlobalFilter.initFilterStatus('#your_filter_form');
KTGlobalFilter.initFilterCreators('#your_filter_form');
KTGlobalFilter.initFilterSellers('#your_filter_form');
KTGlobalFilter.initFilterPaymentMethods('#your_filter_form');
```

### Lấy dữ liệu filter:

```javascript
var getFilterData = function() {
    var data = {};
    
    // Lấy dữ liệu từ form filter
    $('#your_filter_form').find('input, select').each(function() {
        var $input = $(this);
        var name = $input.attr('name');
        
        if (name) {
            if ($input.is(':checkbox')) {
                if (name.endsWith('[]')) {
                    if (!data[name]) data[name] = [];
                    if ($input.is(':checked')) {
                        data[name].push($input.val());
                    }
                } else if ($input.is(':checked')) {
                    data[name] = $input.val();
                }
            } else if ($input.is(':radio')) {
                if ($input.is(':checked')) {
                    data[name] = $input.val();
                }
            } else {
                var value = $input.val();
                if (value) data[name] = value;
            }
        }
    });
    
    return data;
};
```

## API Reference

### KTGlobalFilter.initAllFilters(formSelector, loadCallback, options)

Khởi tạo tất cả bộ lọc cho một form.

**Parameters:**
- `formSelector` (string): CSS selector cho form filter
- `loadCallback` (function): Callback function khi cần tải lại dữ liệu
- `options` (object): Tùy chọn bật/tắt từng loại filter

**Options:**
- `timeFilter` (boolean): Bộ lọc thời gian (default: true)
- `statusFilter` (boolean): Bộ lọc trạng thái (default: true)
- `creatorsFilter` (boolean): Bộ lọc người tạo (default: true)
- `sellersFilter` (boolean): Bộ lọc người bán (default: true)
- `saleChannelsFilter` (boolean): Bộ lọc kênh bán (default: true)
- `paymentMethodsFilter` (boolean): Bộ lọc phương thức thanh toán (default: true)

### Các method riêng lẻ:

- `KTGlobalFilter.initTimeFilter(formSelector)` - Khởi tạo bộ lọc thời gian
- `KTGlobalFilter.initFilterStatus(formSelector)` - Khởi tạo bộ lọc trạng thái
- `KTGlobalFilter.initFilterCreators(formSelector)` - Khởi tạo bộ lọc người tạo
- `KTGlobalFilter.initFilterSellers(formSelector)` - Khởi tạo bộ lọc người bán
- `KTGlobalFilter.initFilterSaleChannels(formSelector)` - Khởi tạo bộ lọc kênh bán
- `KTGlobalFilter.initFilterPaymentMethods(formSelector)` - Khởi tạo bộ lọc phương thức thanh toán

### Utility methods:

- `KTGlobalFilter.setLoadDataCallback(callback)` - Thiết lập callback function
- `KTGlobalFilter.callLoadDataCallback()` - Gọi callback function
- `KTGlobalFilter.showTimePanel()` - Hiển thị time panel
- `KTGlobalFilter.hideTimePanel()` - Ẩn time panel
- `KTGlobalFilter.isInitialized()` - Kiểm tra trạng thái khởi tạo

## Ví dụ thực tế

### Invoice List:
```javascript
var initFilters = function() {
    KTGlobalFilter.initAllFilters('#kt_invoice_filter_form', loadInvoices, {
        timeFilter: true,
        statusFilter: true,
        creatorsFilter: true,
        sellersFilter: true,
        saleChannelsFilter: true,
        paymentMethodsFilter: true
    });
};
```

### Payment List:
```javascript
var initFilters = function() {
    KTGlobalFilter.initAllFilters('#kt_payment_filter_form', loadPayments, {
        timeFilter: true,
        statusFilter: false,  // Payments không có status filter
        creatorsFilter: true,
        sellersFilter: false, // Payments không có seller filter
        saleChannelsFilter: false,
        paymentMethodsFilter: true
    });
};
```

## Lưu ý

1. **Thứ tự include:** Luôn include `filter.js` trước file JS của trang
2. **Form selector:** Sử dụng selector duy nhất cho mỗi form filter
3. **Callback function:** Đảm bảo callback function xử lý việc tải lại dữ liệu
4. **HTML structure:** Tuân thủ đúng cấu trúc HTML và naming convention
5. **CSS styling:** Thêm CSS cần thiết cho time panel và các filter

## Troubleshooting

### Module không hoạt động
- Kiểm tra `filter.js` đã được include chưa
- Kiểm tra console có lỗi JavaScript không
- Đảm bảo form selector đúng với HTML

### Callback không được gọi
- Kiểm tra callback function đã được set chưa
- Đảm bảo callback function là một function hợp lệ
- Kiểm tra logic trong callback function

### Time panel không hiển thị
- Kiểm tra CSS cho `.time-options-panel`
- Đảm bảo z-index đủ cao
- Kiểm tra HTML structure của time panel
