# YukiMart - Product Management Documentation

## 📋 Tổng quan quản lý sản phẩm

Hệ thống quản lý sản phẩm YukiMart hỗ trợ quản lý sản phẩm đa dạng với variants, hình ảnh, và tích hợp kho hàng.

## 🏷️ Product Structure

### Cấu trúc sản phẩm
```
Product (Parent)
├── Basic Information
│   ├── Name, Description
│   ├── Category, Brand
│   └── Status, Visibility
├── Product Variants (Children)
│   ├── SKU, Barcode
│   ├── Price, Cost
│   ├── Attributes (Color, Size)
│   └── Images
└── Inventory Management
    ├── Stock Quantity
    ├── Warehouse Location
    └── Stock Alerts
```

### Loại sản phẩm
- **Simple Product**: Sản phẩm đơn giản
- **Variable Product**: Sản phẩm có variants
- **Digital Product**: Sản phẩm số
- **Service Product**: Dịch vụ

## 📦 Product Management

### Mục đích
Quản lý toàn bộ thông tin sản phẩm từ tạo mới đến cập nhật và xóa.

### Tính năng chính
- **Product Creation**: Tạo sản phẩm mới
- **Variant Management**: Quản lý biến thể
- **Image Management**: Quản lý hình ảnh
- **Category Assignment**: Phân loại sản phẩm
- **Bulk Operations**: Thao tác hàng loạt

### Functions chính
- `createProduct()`: Tạo sản phẩm mới
- `updateProduct()`: Cập nhật thông tin sản phẩm
- `deleteProduct()`: Xóa sản phẩm
- `duplicateProduct()`: Nhân bản sản phẩm
- `bulkUpdateProducts()`: Cập nhật hàng loạt

## 🎨 Product Variants

### Mục đích
Quản lý các biến thể của sản phẩm như màu sắc, kích thước, chất liệu.

### Thuộc tính variants
- **Color**: Màu sắc (Đỏ, Xanh, Vàng)
- **Size**: Kích thước (S, M, L, XL)
- **Material**: Chất liệu (Cotton, Polyester)
- **Style**: Kiểu dáng (Slim, Regular, Loose)

### Tính năng chính
- **Attribute Management**: Quản lý thuộc tính
- **Variant Generation**: Tạo variants tự động
- **Individual Pricing**: Giá riêng cho từng variant
- **Stock Management**: Quản lý kho theo variant
- **Image Assignment**: Gán hình ảnh cho variant

### Functions chính
- `createVariant()`: Tạo variant mới
- `generateVariants()`: Tạo variants tự động
- `updateVariantPrice()`: Cập nhật giá variant
- `assignVariantImage()`: Gán hình ảnh variant
- `getVariantStock()`: Lấy tồn kho variant

## 🏷️ Category Management

### Mục đích
Tổ chức sản phẩm theo danh mục phân cấp để dễ dàng quản lý và tìm kiếm.

### Cấu trúc danh mục
```
Electronics
├── Smartphones
│   ├── iPhone
│   ├── Samsung
│   └── Xiaomi
├── Laptops
│   ├── Gaming
│   ├── Business
│   └── Ultrabook
└── Accessories
    ├── Cases
    ├── Chargers
    └── Headphones
```

### Tính năng chính
- **Hierarchical Categories**: Danh mục phân cấp
- **Category Attributes**: Thuộc tính danh mục
- **SEO Optimization**: Tối ưu SEO
- **Category Images**: Hình ảnh danh mục
- **Product Assignment**: Gán sản phẩm vào danh mục

### Functions chính
- `createCategory()`: Tạo danh mục mới
- `updateCategoryHierarchy()`: Cập nhật cấu trúc
- `assignProductToCategory()`: Gán sản phẩm
- `getCategoryProducts()`: Lấy sản phẩm theo danh mục
- `generateCategoryTree()`: Tạo cây danh mục

## 🔍 Product Search

### Mục đích
Tìm kiếm sản phẩm nhanh chóng và chính xác trong POS và admin.

### Phương thức tìm kiếm
- **Text Search**: Tìm theo tên, mô tả
- **SKU Search**: Tìm theo mã SKU
- **Barcode Search**: Tìm theo barcode
- **Category Filter**: Lọc theo danh mục
- **Attribute Filter**: Lọc theo thuộc tính

### Tính năng chính
- **Auto-complete**: Gợi ý tự động
- **Fuzzy Search**: Tìm kiếm mờ
- **Search Filters**: Bộ lọc tìm kiếm
- **Search History**: Lịch sử tìm kiếm
- **Popular Products**: Sản phẩm phổ biến

### Functions chính
- `searchProducts()`: Tìm kiếm sản phẩm
- `searchBySKU()`: Tìm theo SKU
- `searchByBarcode()`: Tìm theo barcode
- `getSearchSuggestions()`: Lấy gợi ý
- `saveSearchHistory()`: Lưu lịch sử tìm kiếm

## 📸 Image Management

### Mục đích
Quản lý hình ảnh sản phẩm với tính năng upload, resize và optimize.

### Loại hình ảnh
- **Primary Image**: Hình ảnh chính
- **Gallery Images**: Thư viện hình ảnh
- **Variant Images**: Hình ảnh variants
- **Thumbnail**: Hình ảnh thu nhỏ

### Tính năng chính
- **Drag & Drop Upload**: Upload kéo thả
- **Image Resize**: Thay đổi kích thước
- **Image Optimization**: Tối ưu hình ảnh
- **Bulk Upload**: Upload hàng loạt
- **Image Gallery**: Thư viện hình ảnh

### Functions chính
- `uploadProductImage()`: Upload hình ảnh
- `resizeImage()`: Thay đổi kích thước
- `optimizeImage()`: Tối ưu hình ảnh
- `deleteProductImage()`: Xóa hình ảnh
- `setImageOrder()`: Sắp xếp thứ tự

## 💰 Pricing Management

### Mục đích
Quản lý giá bán, giá cost và các chính sách giá đặc biệt.

### Loại giá
- **Cost Price**: Giá vốn
- **Selling Price**: Giá bán
- **Discount Price**: Giá giảm
- **Wholesale Price**: Giá sỉ
- **Retail Price**: Giá lẻ

### Tính năng chính
- **Dynamic Pricing**: Giá động
- **Bulk Pricing**: Giá theo số lượng
- **Time-based Pricing**: Giá theo thời gian
- **Customer Group Pricing**: Giá theo nhóm khách hàng
- **Promotional Pricing**: Giá khuyến mãi

### Functions chính
- `updateProductPrice()`: Cập nhật giá
- `calculateDiscount()`: Tính giảm giá
- `applyBulkPricing()`: Áp dụng giá sỉ
- `setPromotionalPrice()`: Đặt giá khuyến mãi
- `getPriceHistory()`: Lấy lịch sử giá

## 📊 Inventory Integration

### Mục đích
Tích hợp quản lý kho hàng với thông tin sản phẩm.

### Thông tin kho hàng
- **Current Stock**: Tồn kho hiện tại
- **Reserved Stock**: Kho đã đặt
- **Available Stock**: Kho có thể bán
- **Reorder Level**: Mức đặt hàng lại
- **Maximum Stock**: Tồn kho tối đa

### Tính năng chính
- **Real-time Stock**: Tồn kho real-time
- **Stock Alerts**: Cảnh báo tồn kho
- **Multi-warehouse**: Đa kho hàng
- **Stock Reservation**: Đặt trước kho
- **Stock History**: Lịch sử xuất nhập

### Functions chính
- `updateProductStock()`: Cập nhật tồn kho
- `reserveStock()`: Đặt trước kho
- `releaseStock()`: Giải phóng kho
- `checkStockAvailability()`: Kiểm tra tồn kho
- `generateStockReport()`: Báo cáo tồn kho

## 📥 Product Import/Export

### Mục đích
Nhập/xuất dữ liệu sản phẩm hàng loạt từ/ra file Excel/CSV.

### Định dạng import
- **Excel Format**: .xlsx, .xls
- **CSV Format**: .csv
- **JSON Format**: .json
- **XML Format**: .xml

### Tính năng chính
- **Column Mapping**: Ánh xạ cột
- **Data Validation**: Kiểm tra dữ liệu
- **Error Reporting**: Báo cáo lỗi
- **Progress Tracking**: Theo dõi tiến độ
- **Rollback Support**: Hỗ trợ rollback

### Functions chính
- `importProducts()`: Nhập sản phẩm
- `exportProducts()`: Xuất sản phẩm
- `validateImportData()`: Kiểm tra dữ liệu
- `mapImportColumns()`: Ánh xạ cột
- `generateImportReport()`: Báo cáo nhập

## 🏷️ Barcode Management

### Mục đích
Quản lý mã vạch cho sản phẩm và variants.

### Loại barcode
- **EAN-13**: Mã vạch 13 số
- **UPC-A**: Mã vạch UPC
- **Code 128**: Mã vạch Code 128
- **QR Code**: Mã QR

### Tính năng chính
- **Auto-generate Barcode**: Tự động tạo barcode
- **Custom Barcode**: Barcode tùy chỉnh
- **Barcode Validation**: Kiểm tra barcode
- **Barcode Printing**: In barcode
- **Barcode Scanning**: Quét barcode

### Functions chính
- `generateBarcode()`: Tạo barcode
- `validateBarcode()`: Kiểm tra barcode
- `printBarcode()`: In barcode
- `scanBarcode()`: Quét barcode
- `updateProductBarcode()`: Cập nhật barcode

## 📈 Product Analytics

### Mục đích
Phân tích hiệu suất sản phẩm và xu hướng bán hàng.

### Chỉ số phân tích
- **Sales Volume**: Khối lượng bán
- **Revenue**: Doanh thu
- **Profit Margin**: Biên lợi nhuận
- **Inventory Turnover**: Vòng quay kho
- **Customer Rating**: Đánh giá khách hàng

### Tính năng chính
- **Sales Analytics**: Phân tích bán hàng
- **Trend Analysis**: Phân tích xu hướng
- **Performance Metrics**: Chỉ số hiệu suất
- **Comparison Reports**: Báo cáo so sánh
- **Predictive Analytics**: Phân tích dự đoán

### Functions chính
- `analyzeProductSales()`: Phân tích bán hàng
- `calculateProfitMargin()`: Tính biên lợi nhuận
- `generateTrendReport()`: Báo cáo xu hướng
- `compareProductPerformance()`: So sánh hiệu suất
- `predictSalesTrend()`: Dự đoán xu hướng

---

**Last Updated**: January 2025  
**Version**: 1.0.0  
**Maintainer**: Development Team
