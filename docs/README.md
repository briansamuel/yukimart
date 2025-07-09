# YukiMart - Documentation

Tài liệu hướng dẫn sử dụng và phát triển hệ thống YukiMart - Hệ thống quản lý bán hàng đa kênh.

## 📋 Tổng quan dự án

YukiMart là hệ thống quản lý bán hàng toàn diện được phát triển bằng Laravel, hỗ trợ:
- **POS System**: Bán hàng tại quầy với barcode scanning
- **Multi-channel Sales**: Bán hàng đa kênh (Online, Marketplace, Direct)
- **Inventory Management**: Quản lý kho hàng theo thời gian thực
- **Branch Management**: Quản lý nhiều chi nhánh
- **Customer & Supplier**: Quản lý đối tác kinh doanh
- **Reports & Analytics**: Báo cáo và phân tích doanh số

## 📁 Cấu trúc Documentation

```
docs/
├── README.md                    # Tài liệu chính
├── Architecture.md              # Kiến trúc hệ thống
├── GitWorkflow.md              # Quy trình Git
└── Feature/                    # Tài liệu tính năng
    ├── Work.md                 # Quy trình làm việc
    └── Product.md              # Quản lý sản phẩm
```

## 🏗️ Kiến trúc hệ thống

Xem chi tiết tại: [Architecture.md](./Architecture.md)

- **Backend**: Laravel 10.x với PHP 8.3
- **Frontend**: Blade Templates + Bootstrap 5 + Metronic Theme
- **Database**: MySQL 8.0
- **Cache**: Redis
- **File Storage**: Local + Cloud Storage
- **Real-time**: WebSocket cho notifications

## 🔄 Quy trình phát triển

Xem chi tiết tại: [GitWorkflow.md](./GitWorkflow.md)

- **Branching Strategy**: Feature branches từ main
- **Code Review**: Required trước khi merge
- **Testing**: Automated tests + Manual testing
- **Deployment**: CI/CD pipeline

## 🚀 Tính năng chính

### 📦 [Quản lý sản phẩm](./Feature/Product.md)
- Sản phẩm với variants (màu sắc, kích thước)
- Quản lý SKU, barcode, giá bán
- Upload hình ảnh sản phẩm
- Phân loại theo danh mục

### 💰 [Quy trình làm việc](./Feature/Work.md)
- Quick Order POS system
- Quản lý đơn hàng và hóa đơn
- Xử lý thanh toán đa phương thức
- Quản lý kho hàng tự động

## 🛠️ Công nghệ sử dụng

### Backend
- **Framework**: Laravel 10.x
- **PHP Version**: 8.3
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Redis Queue

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5
- **Theme**: Metronic 8
- **JavaScript**: jQuery + Modern ES6
- **Icons**: FontAwesome + KTIcons

### Development Tools
- **Docker**: Development environment
- **Composer**: PHP dependency management
- **NPM**: Frontend asset management
- **Git**: Version control

## 📚 Hướng dẫn sử dụng

### Cho Developer
1. Đọc [Architecture.md](./Architecture.md) để hiểu kiến trúc
2. Theo [GitWorkflow.md](./GitWorkflow.md) cho quy trình phát triển
3. Xem [Feature/](./Feature/) để hiểu các tính năng

### Cho User
1. Xem [Feature/Work.md](./Feature/Work.md) cho quy trình làm việc
2. Xem [Feature/Product.md](./Feature/Product.md) cho quản lý sản phẩm

## 🧪 Testing & Debug

### Test Files Location
Tất cả file test được lưu trong: `public/tests/`

### Debug Tools
- **Invoice Checkbox Debug**: `public/tests/invoice-checkbox-debug.html`
- **Browser Console**: Sử dụng F12 Developer Tools
- **Laravel Debugbar**: Enabled trong development

## 📞 Hỗ trợ

- **Technical Issues**: Tạo issue trên GitHub
- **Feature Requests**: Liên hệ team phát triển
- **Documentation**: Cập nhật tại `docs/` folder

## 📝 Đóng góp

1. Fork repository
2. Tạo feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -m 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Tạo Pull Request

---

**Project**: YukiMart POS System
**Version**: 1.0.0
**Last Updated**: January 2025
**Maintainer**: Development Team
