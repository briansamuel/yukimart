# 📖 Hướng dẫn sử dụng hệ thống Product Variants

## 🎯 Tổng quan

Hệ thống Product Variants cho phép bạn tạo nhiều biến thể cho cùng một sản phẩm với các thuộc tính khác nhau như hương vị, kích thước, màu sắc, v.v. Mỗi biến thể có thể có SKU, giá bán, mã vạch và hình ảnh riêng.

## 🚀 Cài đặt

### 1. Chạy migrations và seeders
```bash
# Chạy script setup
bash setup_variants.sh

# Hoặc chạy thủ công
php artisan migrate
php artisan db:seed --class=ProductAttributeSeeder
```

### 2. Kiểm tra cài đặt
```bash
# Chạy demo test
php test_variants_demo.php
```

## 📝 Hướng dẫn sử dụng

### 1. Tạo sản phẩm có biến thể

#### Bước 1: Tạo sản phẩm mới
1. Vào **Admin > Products > Add Product**
2. Điền thông tin cơ bản (tên, mô tả, giá, v.v.)
3. Chọn **Product Type = "Variable"**

#### Bước 2: Chọn thuộc tính
1. Khi chọn "Variable", giao diện quản lý biến thể sẽ xuất hiện
2. Chọn các thuộc tính muốn sử dụng (Hương vị, Kích thước, Màu sắc)
3. Chọn các giá trị cho mỗi thuộc tính

#### Bước 3: Tạo biến thể tự động
1. Nhấn nút **"Tạo biến thể tự động"**
2. Hệ thống sẽ tạo tất cả các tổ hợp có thể
3. Xem danh sách biến thể được tạo

### 2. Quản lý biến thể hiện có

#### Chỉnh sửa biến thể
1. Vào **Admin > Products > Edit Product**
2. Nếu sản phẩm có biến thể, danh sách sẽ hiển thị
3. Nhấn nút **"Sửa"** để chỉnh sửa giá, SKU
4. Nhấn nút **"Xóa"** để xóa biến thể

#### Cập nhật giá hàng loạt
1. Sử dụng API endpoint để cập nhật nhiều biến thể cùng lúc
2. Hoặc chỉnh sửa từng biến thể riêng lẻ

### 3. Quản lý tồn kho theo biến thể

#### Tồn kho riêng biệt
- Mỗi biến thể có tồn kho riêng
- Có thể quản lý tồn kho theo kho hàng
- Tự động tạo bản ghi inventory khi tạo biến thể

#### Điều chỉnh tồn kho
```php
// Thêm tồn kho cho biến thể
Inventory::addVariantQuantity($variantId, 100, $warehouseId);

// Lấy tồn kho hiện tại
$quantity = Inventory::getVariantQuantity($variantId);

// Cập nhật tồn kho
Inventory::updateVariantQuantity($variantId, 150, $warehouseId);
```

## 🔧 API Endpoints

### 1. Lấy danh sách thuộc tính
```
GET /admin/products/attributes
```

### 2. Tạo biến thể cho sản phẩm
```
POST /admin/products/{id}/variants
Content-Type: application/json

{
    "attributes": {
        "1": [1, 2, 3],  // attribute_id: [value_ids]
        "2": [4, 5]
    }
}
```

### 3. Lấy danh sách biến thể
```
GET /admin/products/{id}/variants
```

### 4. Cập nhật biến thể
```
PUT /admin/products/{productId}/variants/{variantId}
Content-Type: application/json

{
    "sale_price": 75000,
    "sku": "NEW-SKU-001",
    "attributes": {
        "1": 2,  // attribute_id: value_id
        "2": 5
    }
}
```

### 5. Xóa biến thể
```
DELETE /admin/products/{productId}/variants/{variantId}
```

### 6. Cập nhật giá hàng loạt
```
POST /admin/products/{id}/variants/bulk-update-prices
Content-Type: application/json

{
    "variants": {
        "1": {
            "cost_price": 30000,
            "sale_price": 55000,
            "regular_price": 60000
        },
        "2": {
            "cost_price": 35000,
            "sale_price": 65000
        }
    }
}
```

## 💡 Ví dụ thực tế

### Ví dụ 1: Trà sữa với nhiều hương vị và size
```
Sản phẩm: Trà sữa cao cấp
Thuộc tính:
- Hương vị: Nhài, Xoài, Dâu
- Kích thước: Gói nhỏ (+0đ), Gói lớn (+15.000đ)

Biến thể được tạo:
1. Trà sữa cao cấp - Hương Nhài - Gói nhỏ (45.000đ)
2. Trà sữa cao cấp - Hương Nhài - Gói lớn (60.000đ)
3. Trà sữa cao cấp - Hương Xoài - Gói nhỏ (45.000đ)
4. Trà sữa cao cấp - Hương Xoài - Gói lớn (60.000đ)
5. Trà sữa cao cấp - Hương Dâu - Gói nhỏ (45.000đ)
6. Trà sữa cao cấp - Hương Dâu - Gói lớn (60.000đ)
```

### Ví dụ 2: Áo thun với màu sắc và size
```
Sản phẩm: Áo thun basic
Thuộc tính:
- Màu sắc: Đỏ, Xanh, Vàng
- Kích thước: S, M, L, XL

Biến thể: 12 biến thể (3 màu × 4 size)
```

## 🎨 Tùy chỉnh thuộc tính

### Thêm thuộc tính mới
```php
ProductAttribute::create([
    'name' => 'Chất liệu',
    'slug' => 'chat-lieu',
    'type' => 'select',
    'description' => 'Chất liệu sản phẩm',
    'is_required' => false,
    'is_variation' => true,
    'is_visible' => true,
    'sort_order' => 4,
    'status' => 'active'
]);
```

### Thêm giá trị thuộc tính
```php
ProductAttributeValue::create([
    'attribute_id' => $attributeId,
    'value' => 'Cotton 100%',
    'slug' => 'cotton-100',
    'price_adjustment' => 10000, // Tăng giá 10k
    'sort_order' => 1,
    'status' => 'active'
]);
```

## 🔍 Troubleshooting

### Lỗi thường gặp

1. **"Variant not found"**
   - Kiểm tra variant_id có tồn tại
   - Kiểm tra variant có thuộc product đúng không

2. **"No attributes selected"**
   - Đảm bảo đã chọn ít nhất 1 thuộc tính và giá trị
   - Kiểm tra JavaScript console để debug

3. **"Database constraint error"**
   - Chạy lại migrations
   - Kiểm tra foreign key constraints

### Debug JavaScript
```javascript
// Kiểm tra variant manager
console.log(KTProductVariantManager);

// Kiểm tra selected attributes
console.log(KTProductVariantManager.getSelectedAttributes());

// Load variants manually
KTProductVariantManager.loadVariants();
```

## 📈 Hiệu suất

### Tối ưu hóa
- Index được tạo tự động cho các truy vấn thường dùng
- Lazy loading cho relationships
- Cache attribute data nếu cần

### Giới hạn
- Khuyến nghị tối đa 100 biến thể/sản phẩm
- Tối đa 5 thuộc tính/sản phẩm
- Tối đa 20 giá trị/thuộc tính

## 🚀 Mở rộng

Hệ thống đã sẵn sàng cho:
- Tích hợp marketplace (Shopee, Tiki, TikTok Shop)
- Import/Export variants
- Barcode scanning cho variants
- Order management với variants
- Inventory transactions cho variants

## 📞 Hỗ trợ

Nếu gặp vấn đề, hãy kiểm tra:
1. Log files trong `storage/logs/`
2. Browser console cho JavaScript errors
3. Database constraints và relationships
4. API response trong Network tab
