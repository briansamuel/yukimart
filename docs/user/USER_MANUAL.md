# 📚 YUKIMART MULTI-TENANT SYSTEM - USER MANUAL

## **Hướng dẫn sử dụng hệ thống YukiMart**

**Version**: 1.0  
**Date**: 2025-08-11  
**Language**: Tiếng Việt  

---

## 🎯 **TỔNG QUAN HỆ THỐNG**

YukiMart là hệ thống quản lý bán hàng đa tenant (multi-tenant) cho phép nhiều cửa hàng hoạt động độc lập trên cùng một nền tảng. Mỗi tenant có:

- **Subdomain riêng**: `ten-cua-hang.yukimart.com`
- **Dữ liệu riêng biệt**: Sản phẩm, khách hàng, đơn hàng
- **Quản lý độc lập**: Người dùng, chi nhánh, kho hàng
- **Giao diện tùy chỉnh**: Branding và theme riêng

### **5 Tenant Demo Hiện Có:**

| Tenant | Subdomain | Lĩnh vực | Sản phẩm |
|--------|-----------|----------|----------|
| **TechMart Store** | `tenant1.yukimart.local` | Công nghệ | 50 sản phẩm tech |
| **Fashion Boutique** | `tenant2.yukimart.local` | Thời trang | 50 sản phẩm fashion |
| **Food & Beverage Co** | `tenant3.yukimart.local` | F&B | 50 sản phẩm F&B |
| **HelloMart Store** | `hellomart.yukimart.local` | Tổng hợp | 50 sản phẩm tổng hợp |
| **BiboMart Store** | `bibomart.yukimart.local` | Tiện lợi | 50 sản phẩm tiện lợi |

---

## 🔐 **ĐĂNG NHẬP HỆ THỐNG**

### **Bước 1: Truy cập Subdomain**
1. Mở trình duyệt web
2. Nhập địa chỉ: `http://ten-tenant.yukimart.local/admin/login`
3. Ví dụ: `http://tenant1.yukimart.local/admin/login`

### **Bước 2: Đăng nhập**
1. Nhập **Email** và **Password**
2. Chọn **"Ghi nhớ đăng nhập"** (tùy chọn)
3. Click **"Đăng nhập"**

### **Tài khoản Demo (Password: 123456):**

#### **TechMart Store:**
- **Owner**: `owner@techmart.local` / 123456
- **Admin**: `admin@techmart.local` / 123456
- **Manager**: `manager@techmart.local` / 123456
- **Staff**: `staff@techmart.local` / 123456

#### **Fashion Boutique:**
- **Owner**: `owner@fashion.local` / 123456
- **Admin**: `admin@fashion.local` / 123456
- **Manager**: `manager@fashion.local` / 123456
- **Staff**: `staff@fashion.local` / 123456

#### **Food & Beverage Co:**
- **Owner**: `owner@foodbev.local` / 123456
- **Admin**: `admin@foodbev.local` / 123456
- **Manager**: `manager@foodbev.local` / 123456
- **Staff**: `staff@foodbev.local` / 123456

#### **HelloMart Store:**
- **Owner**: `owner@hellomart.local` / 123456
- **Admin**: `admin@hellomart.local` / 123456
- **Manager**: `manager@hellomart.local` / 123456
- **Staff**: `staff@hellomart.local` / 123456

#### **BiboMart Store:**
- **Owner**: `owner@bibomart.local` / 123456
- **Admin**: `admin@bibomart.local` / 123456
- **Manager**: `manager@bibomart.local` / 123456
- **Staff**: `staff@bibomart.local` / 123456

---

## 🏠 **DASHBOARD - BẢNG ĐIỀU KHIỂN**

Sau khi đăng nhập thành công, bạn sẽ được chuyển đến Dashboard với các thông tin:

### **Thông tin Người dùng:**
- **Họ tên**: Tên đầy đủ của người dùng
- **Email**: Địa chỉ email đăng nhập
- **Vai trò**: Owner, Admin, Manager, Staff
- **Trạng thái**: Active/Inactive
- **Ngày tham gia**: Ngày được thêm vào hệ thống

### **Thông tin Cửa hàng:**
- **Tên cửa hàng**: Tên tenant
- **Mã cửa hàng**: Slug identifier
- **Subdomain**: Địa chỉ truy cập
- **Trạng thái**: Active/Inactive
- **Ngày tạo**: Ngày khởi tạo tenant

### **Thống kê Tổng quan:**
- **👥 Người dùng**: Số lượng users trong tenant
- **🏬 Chi nhánh**: Số lượng branch shops
- **📦 Sản phẩm**: Số lượng products
- **🛒 Đơn hàng**: Số đơn hàng hôm nay

### **Thao tác Nhanh:**
- ➕ **Thêm sản phẩm mới**
- 🛒 **Tạo đơn hàng**
- 👥 **Quản lý khách hàng**
- 📊 **Xem báo cáo**
- ⚙️ **Cài đặt hệ thống**
- ℹ️ **Kiểm tra trạng thái**

---

## 👥 **PHÂN QUYỀN NGƯỜI DÙNG**

### **Owner (Chủ sở hữu):**
- ✅ **Toàn quyền**: Truy cập tất cả chức năng
- ✅ **Quản lý người dùng**: Thêm, sửa, xóa users
- ✅ **Cài đặt hệ thống**: Cấu hình tenant
- ✅ **Báo cáo tài chính**: Xem tất cả báo cáo
- ✅ **Quản lý chi nhánh**: Thêm, sửa chi nhánh

### **Admin (Quản trị viên):**
- ✅ **Quản lý sản phẩm**: CRUD products
- ✅ **Quản lý đơn hàng**: Xử lý orders
- ✅ **Quản lý khách hàng**: CRUD customers
- ✅ **Quản lý kho**: Inventory management
- ✅ **Báo cáo**: Xem báo cáo kinh doanh
- ❌ **Cài đặt hệ thống**: Không có quyền

### **Manager (Quản lý):**
- ✅ **Quản lý chi nhánh**: Chi nhánh được phân công
- ✅ **Quản lý nhân viên**: Staff trong chi nhánh
- ✅ **Xử lý đơn hàng**: Orders của chi nhánh
- ✅ **Báo cáo chi nhánh**: Reports của chi nhánh
- ❌ **Cài đặt toàn hệ thống**: Không có quyền

### **Staff (Nhân viên):**
- ✅ **Tạo đơn hàng**: POS operations
- ✅ **Xem sản phẩm**: Product catalog
- ✅ **Quản lý khách hàng**: Customer service
- ❌ **Báo cáo**: Không có quyền
- ❌ **Cài đặt**: Không có quyền

---

## 🛍️ **QUẢN LÝ SẢN PHẨM**

### **Danh sách Sản phẩm theo Tenant:**

#### **TechMart Store (50 sản phẩm):**
- **Điện thoại**: iPhone 15 Pro Max, Samsung Galaxy S24 Ultra
- **Laptop**: MacBook Pro M3, Dell XPS 13 Plus
- **Phụ kiện**: AirPods Pro, Apple Watch Series 9
- **Gaming**: Gaming Mouse, Gaming Keyboard
- **Smart Home**: Smart Speaker, Smart Camera

#### **Fashion Boutique (50 sản phẩm):**
- **Thời trang nữ**: Váy maxi, Áo sơ mi, Quần jeans
- **Thời trang nam**: Áo polo, Quần kaki, Áo khoác
- **Giày dép**: Giày cao gót, Giày thể thao
- **Phụ kiện**: Túi xách, Đồng hồ, Trang sức

#### **Food & Beverage Co (50 sản phẩm):**
- **Đồ uống**: Cà phê Arabica, Trà xanh Thái Nguyên
- **Thực phẩm**: Gạo ST25, Mì gói, Nước mắm
- **Gia vị**: Dầu ăn, Đường, Muối biển
- **Snack**: Bánh quy, Kẹo, Nước ngọt

#### **HelloMart Store (50 sản phẩm):**
- **Gia dụng**: Nồi cơm điện, Bộ dao thớt
- **Văn phòng phẩm**: Bút bi, Sổ tay, Pin AA
- **Điện gia dụng**: Đèn pin LED, Ổ cắm điện
- **Dệt may**: Khăn tắm, Dép tổ ong

#### **BiboMart Store (50 sản phẩm):**
- **Đồ ăn vặt**: Bánh quy Oreo, Kẹo Mentos
- **Đồ uống**: Coca Cola, Nước suối, Cà phê hòa tan
- **Tiện lợi**: Bánh mì sandwich, Mì tôm
- **Năng lượng**: Nước tăng lực, Kẹo cao su

### **Thông tin Sản phẩm:**
- **Tên sản phẩm**: Tên đầy đủ
- **SKU**: Mã sản phẩm unique
- **Barcode**: Mã vạch
- **Giá bán**: Sale price
- **Giá vốn**: Cost price
- **Mô tả**: Description chi tiết
- **Trọng lượng**: Weight (nếu có)
- **Trạng thái**: Active/Inactive

---

## 🏬 **QUẢN LÝ CHI NHÁNH**

### **Danh sách Chi nhánh theo Tenant:**

#### **TechMart Store (4 chi nhánh):**
- **TECHMART01**: Chi nhánh 1 - Quận 1, TP.HCM
- **TECHMART02**: Chi nhánh 2 - Quận 2, TP.HCM
- **TECHMART03**: Chi nhánh 3 - Quận 3, TP.HCM
- **TECHMART04**: Chi nhánh 4 - Quận 4, TP.HCM

#### **Fashion Boutique (3 chi nhánh):**
- **FASHION01**: Chi nhánh 1 - Quận 1, TP.HCM
- **FASHION02**: Chi nhánh 2 - Quận 2, TP.HCM
- **FASHION03**: Chi nhánh 3 - Quận 3, TP.HCM

#### **Food & Beverage Co (3 chi nhánh):**
- **FOODBEV01**: Chi nhánh 1 - Quận 1, TP.HCM
- **FOODBEV02**: Chi nhánh 2 - Quận 2, TP.HCM
- **FOODBEV03**: Chi nhánh 3 - Quận 3, TP.HCM

#### **HelloMart Store (4 chi nhánh):**
- **HELLOMART01**: Chi nhánh 1 - Quận 1, TP.HCM
- **HELLOMART02**: Chi nhánh 2 - Quận 2, TP.HCM
- **HELLOMART03**: Chi nhánh 3 - Quận 3, TP.HCM
- **HELLOMART04**: Chi nhánh 4 - Quận 4, TP.HCM

#### **BiboMart Store (2 chi nhánh):**
- **BIBOMART01**: Chi nhánh 1 - Quận 1, TP.HCM
- **BIBOMART02**: Chi nhánh 2 - Quận 2, TP.HCM

### **Thông tin Chi nhánh:**
- **Tên chi nhánh**: Tên đầy đủ
- **Mã chi nhánh**: Code unique
- **Địa chỉ**: Địa chỉ đầy đủ
- **Tỉnh/Thành**: Province
- **Quận/Huyện**: District
- **Phường/Xã**: Ward
- **Điện thoại**: Phone number
- **Email**: Email liên hệ
- **Giờ mở cửa**: 08:00-22:00
- **Số nhân viên**: 5-15 người
- **Trạng thái**: Active

---

## 🔧 **TÍNH NĂNG HỆ THỐNG**

### **✅ Đã Triển khai:**
- **Multi-Tenant Architecture**: Hoàn toàn độc lập
- **Subdomain Routing**: Professional URLs
- **User Management**: Role-based access
- **Product Management**: 250+ products
- **Branch Management**: 16 branches
- **Inventory Tracking**: Theo dõi tồn kho
- **Authentication**: Secure login system
- **Dashboard**: Real-time statistics

### **🚧 Đang Phát triển:**
- **Order Management**: Quản lý đơn hàng
- **Customer Management**: Quản lý khách hàng
- **Payment Integration**: Tích hợp thanh toán
- **Reporting System**: Báo cáo chi tiết
- **Mobile App**: Ứng dụng di động

### **📋 Kế hoạch Tương lai:**
- **Multi-language**: Đa ngôn ngữ
- **Multi-currency**: Đa tiền tệ
- **Loyalty Program**: Chương trình khách hàng thân thiết
- **Marketplace Integration**: Tích hợp sàn TMDT
- **Advanced Analytics**: Phân tích nâng cao

---

## 🆘 **HỖ TRỢ & LIÊN HỆ**

### **Thông tin Liên hệ:**
- **Email hỗ trợ**: support@yukimart.com
- **Hotline**: +84 28 1234 5678
- **Giờ hỗ trợ**: Thứ 2 - Thứ 6: 8:00 - 18:00 (GMT+7)
- **Website**: https://yukimart.com

### **Tài liệu Kỹ thuật:**
- **API Documentation**: docs/api/API_DOCUMENTATION.md
- **Admin Guide**: docs/admin/ADMIN_GUIDE.md
- **Developer Guide**: docs/developer/DEVELOPER_GUIDE.md

### **Báo lỗi & Góp ý:**
- **Bug Report**: bugs@yukimart.com
- **Feature Request**: features@yukimart.com
- **Security Issues**: security@yukimart.com

---

---

## 🔍 **TROUBLESHOOTING - XỬ LÝ SỰ CỐ**

### **Lỗi Đăng nhập:**
**Vấn đề**: Không thể đăng nhập
**Giải pháp**:
1. Kiểm tra email và password
2. Đảm bảo đang truy cập đúng subdomain
3. Xóa cache trình duyệt
4. Thử trình duyệt khác

**Vấn đề**: Trang login không hiển thị
**Giải pháp**:
1. Kiểm tra URL: `/admin/login`
2. Kiểm tra kết nối internet
3. Liên hệ admin nếu vẫn lỗi

### **Lỗi Hiển thị:**
**Vấn đề**: Dashboard không load
**Giải pháp**:
1. Refresh trang (F5)
2. Kiểm tra quyền truy cập
3. Đăng xuất và đăng nhập lại

**Vấn đề**: Dữ liệu không cập nhật
**Giải pháp**:
1. Refresh trang
2. Kiểm tra kết nối mạng
3. Liên hệ support

### **Lỗi Phân quyền:**
**Vấn đề**: Không có quyền truy cập
**Giải pháp**:
1. Kiểm tra role của user
2. Liên hệ Owner/Admin
3. Đảm bảo account đang active

---

## 📖 **FAQ - CÂU HỎI THƯỜNG GẶP**

### **Q: Làm sao để thay đổi password?**
A: Hiện tại chưa có chức năng đổi password trong demo. Liên hệ admin để được hỗ trợ.

### **Q: Có thể thêm sản phẩm mới không?**
A: Chức năng thêm sản phẩm đang được phát triển. Hiện tại có 250+ sản phẩm demo.

### **Q: Làm sao để xem báo cáo?**
A: Chức năng báo cáo đang được phát triển. Dashboard hiện có thống kê cơ bản.

### **Q: Có thể tạo đơn hàng không?**
A: Chức năng tạo đơn hàng đang được phát triển trong phiên bản tiếp theo.

### **Q: Dữ liệu có bị trộn lẫn giữa các tenant không?**
A: Không. Hệ thống đảm bảo hoàn toàn tách biệt dữ liệu giữa các tenant.

### **Q: Có thể truy cập từ mobile không?**
A: Có. Giao diện responsive, hoạt động tốt trên mobile browser.

---

**📞 Cần hỗ trợ? Liên hệ ngay với team YukiMart!**
**🎉 Cảm ơn bạn đã sử dụng YukiMart Multi-Tenant System!**
