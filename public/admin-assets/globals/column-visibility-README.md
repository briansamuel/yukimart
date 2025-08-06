# Column Visibility Module (KTColumnVisibility)

Module tái sử dụng để quản lý hiển thị cột trong bảng dữ liệu.

## Tính năng

- ✅ Lưu trạng thái hiển thị cột vào localStorage theo từng trang
- ✅ Giao diện panel toggle với checkbox cho từng cột
- ✅ Hỗ trợ callback khi thay đổi trạng thái cột
- ✅ Tương thích với DataTables và bảng HTML thông thường
- ✅ Có thể sử dụng cho nhiều bảng trên cùng một trang
- ✅ API đơn giản và dễ tích hợp

## Cài đặt

### 1. Include file JavaScript

Thêm file `column-visibility.js` trước các file JS riêng của trang:

```html
@section('scripts')
<script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
<script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>
<script src="{{ asset('admin-assets/js/your-page.js') }}"></script>
@endsection
```

### 2. HTML Structure

Tạo button trigger và panel:

```html
<!-- Trigger Button -->
<button id="column_visibility_trigger" class="btn btn-sm btn-light">
    <i class="fas fa-columns"></i> Cột hiển thị
</button>

<!-- Visibility Panel -->
<div id="column_visibility_panel" class="column-visibility-panel">
    <div class="panel-content">
        <div class="panel-header">
            <h6 class="mb-0">Cột hiển thị</h6>
        </div>
        <div class="panel-body">
            <div class="form-check">
                <input class="form-check-input column-toggle" type="checkbox" value="0" id="col_0">
                <label class="form-check-label" for="col_0">Checkbox</label>
            </div>
            <div class="form-check">
                <input class="form-check-input column-toggle" type="checkbox" value="1" id="col_1">
                <label class="form-check-label" for="col_1">Mã hóa đơn</label>
            </div>
            <!-- Thêm checkbox cho các cột khác -->
        </div>
    </div>
</div>
```

### 3. CSS Styling

Thêm CSS cho panel (đã có sẵn trong invoice-list):

```css
.column-visibility-panel {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    width: 500px;
    max-height: 400px;
    overflow-y: auto;
    margin-top: 5px;
    display: none;
}

.column-visibility-panel.show {
    display: block;
}
```

## Cách sử dụng

### Cơ bản

```javascript
var columnVisibility;

var initColumnVisibility = function() {
    // Định nghĩa trạng thái mặc định
    var defaultVisibility = {
        0: true,  // Cột checkbox
        1: true,  // Cột tên
        2: true,  // Cột email
        3: false, // Cột điện thoại (ẩn mặc định)
        4: false, // Cột địa chỉ (ẩn mặc định)
        5: true   // Cột hành động
    };
    
    // Khởi tạo
    columnVisibility = KTColumnVisibility.init({
        storageKey: 'my_table_column_visibility',
        defaultVisibility: defaultVisibility,
        triggerSelector: '#column_visibility_trigger',
        panelSelector: '#column_visibility_panel',
        toggleSelector: '.column-toggle',
        tableSelector: '#my_table'
    });
};
```

### Nâng cao với callback

```javascript
columnVisibility = KTColumnVisibility.init({
    storageKey: 'invoice_column_visibility',
    defaultVisibility: defaultVisibility,
    triggerSelector: '#column_visibility_trigger',
    panelSelector: '#column_visibility_panel',
    toggleSelector: '.column-toggle',
    tableSelector: '#kt_invoices_table',
    onToggle: function(columnIndex, isVisible, columnVisibility) {
        console.log('Cột', columnIndex, 'thay đổi thành:', isVisible);
        
        // Logic tùy chỉnh khi thay đổi cột
        if (columnIndex === 3) { // Cột đặc biệt
            reloadTableData();
        }
    }
});
```

### Sử dụng với nhiều bảng

```javascript
// Bảng 1
var table1Visibility = KTColumnVisibility.init({
    storageKey: 'table1_column_visibility',
    defaultVisibility: table1DefaultVisibility,
    triggerSelector: '#table1_column_visibility_trigger',
    panelSelector: '#table1_column_visibility_panel',
    toggleSelector: '.table1-column-toggle',
    tableSelector: '#table1'
});

// Bảng 2
var table2Visibility = KTColumnVisibility.init({
    storageKey: 'table2_column_visibility',
    defaultVisibility: table2DefaultVisibility,
    triggerSelector: '#table2_column_visibility_trigger',
    panelSelector: '#table2_column_visibility_panel',
    toggleSelector: '.table2-column-toggle',
    tableSelector: '#table2'
});
```

## API Reference

### KTColumnVisibility.init(config)

Khởi tạo column visibility cho một bảng.

**Parameters:**
- `config.storageKey` (string): Key để lưu vào localStorage
- `config.defaultVisibility` (object): Trạng thái mặc định của các cột
- `config.triggerSelector` (string): Selector cho button trigger
- `config.panelSelector` (string): Selector cho panel
- `config.toggleSelector` (string): Selector cho checkbox toggle
- `config.tableSelector` (string): Selector cho bảng
- `config.onToggle` (function): Callback khi thay đổi cột

**Returns:** Object chứa trạng thái hiển thị cột

### KTColumnVisibility.apply(settings, columnVisibility)

Áp dụng trạng thái hiển thị cho bảng.

### KTColumnVisibility.updateHeaders(settings, columnVisibility)

Cập nhật header của bảng theo trạng thái hiển thị.

### KTColumnVisibility.setVisibility(storageKey, settings, columnIndex, isVisible, columnVisibility)

Thiết lập trạng thái hiển thị cho một cột cụ thể.

## Ví dụ thực tế

Xem file `example-usage-column-visibility.js` để có các ví dụ chi tiết về:
- Sử dụng cơ bản
- Tích hợp với DataTables
- Sử dụng với nhiều bảng
- Callback nâng cao

## Lưu ý

1. **Thứ tự include:** Luôn include `column-visibility.js` trước file JS của trang
2. **Storage key:** Sử dụng key duy nhất cho mỗi trang/bảng
3. **Column index:** Index bắt đầu từ 0, tương ứng với thứ tự cột trong bảng
4. **Selector:** Đảm bảo các selector trỏ đúng element trong DOM

## Troubleshooting

### Module không hoạt động
- Kiểm tra `column-visibility.js` đã được include chưa
- Kiểm tra console có lỗi JavaScript không
- Đảm bảo các selector đúng với HTML

### Trạng thái không được lưu
- Kiểm tra localStorage có hoạt động không
- Đảm bảo `storageKey` là duy nhất
- Kiểm tra quyền truy cập localStorage

### Panel không hiển thị
- Kiểm tra CSS cho `.column-visibility-panel`
- Đảm bảo z-index đủ cao
- Kiểm tra trigger selector đúng chưa
