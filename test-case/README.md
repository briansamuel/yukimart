# YukiMart Test Cases

Bộ test cases tự động cho hệ thống YukiMart sử dụng Playwright.

## 📋 Tổng Quan

Bộ test cases này bao gồm các test tự động cho:

### 🧾 **Invoice System Tests**
- **Payment Creation Tests** (`invoices/payment-creation.test.js`)
  - Kiểm tra tạo payment tự động khi tạo invoice
  - Xác minh dữ liệu payment khớp với invoice
  - Kiểm tra relationship giữa invoice và payment

- **Detail Panel Actions Tests** (`invoices/detail-panel-actions.test.js`)
  - Kiểm tra vị trí Actions Button trong tab "Thông tin"
  - Test chức năng nút "Lưu", "Hủy", "Trả hàng"
  - Xác minh button arrangement và styling

### 💰 **Payment System Tests**
- **Print Functionality Tests** (`payments/print-functionality.test.js`)
  - Kiểm tra nút "In" trong detail panel
  - Test chức năng in phiếu thu/chi
  - Xác minh accessibility và usability

## 🚀 Cài Đặt

### 1. Cài đặt dependencies
```bash
cd test-case
npm install
```

### 2. Cài đặt browsers cho Playwright
```bash
npm run install-browsers
```

## 🧪 Chạy Tests

### Chạy tất cả tests
```bash
npm test
```

### Chạy tests theo module
```bash
# Chạy tất cả invoice tests
npm run test:invoices

# Chạy tất cả payment tests
npm run test:payments
```

### Chạy test cụ thể
```bash
# Test payment creation
npm run test:payment-creation

# Test detail panel actions
npm run test:detail-panel-actions

# Test print functionality
npm run test:print-functionality
```

### Chạy tests với UI (headed mode)
```bash
npm run test:headed
```

### Debug tests
```bash
npm run test:debug
```

## 📁 Cấu Trúc Thư Mục

```
test-case/
├── invoices/
│   ├── payment-creation.test.js      # Test tạo payment tự động
│   ├── detail-panel-actions.test.js  # Test actions button
│   └── report.md                     # Báo cáo test invoices
├── payments/
│   ├── print-functionality.test.js   # Test chức năng in
│   └── report.md                     # Báo cáo test payments
├── package.json                      # Dependencies và scripts
├── playwright.config.js              # Cấu hình Playwright
└── README.md                         # Hướng dẫn này
```

## 🎯 Test Cases Chi Tiết

### Invoice Payment Creation Tests
- ✅ **PC01**: Automatic payment creation when invoice has amount_paid > 0
- ✅ **PC02**: Payment data consistency with invoice
- ✅ **PC03**: Payment relationship functionality

### Invoice Detail Panel Actions Tests
- ✅ **DPA01**: Actions buttons position in Thông tin tab
- ✅ **DPA02**: Button arrangement and styling
- ✅ **DPA03**: Lưu button functionality
- ✅ **DPA04**: Trả hàng button functionality
- ✅ **DPA05**: Hủy button functionality

### Payment Print Functionality Tests
- ✅ **PF01**: Print button display in detail panel
- ✅ **PF02**: Payment detail panel content verification
- ✅ **PF03**: Print button functionality test
- ✅ **PF04**: Print functionality for different payment types
- ✅ **PF05**: Print button accessibility and usability
- ✅ **PF06**: Print button position in detail panel

## 🔧 Cấu Hình

### Test Environment
- **Base URL**: `http://yukimart.local`
- **Browser**: Chromium (default), Firefox, WebKit
- **Login**: yukimart@gmail.com / 123456
- **Timeout**: 30 seconds per action

### Test Data
Tests sử dụng dữ liệu thực từ database:
- **Invoice**: INV-20250709-1736
- **Payment**: TT1821-1
- **Amount**: 1.801.800 ₫

## 📊 Báo Cáo

Sau khi chạy tests, báo cáo HTML sẽ được tạo tự động tại:
```
test-results/
└── playwright-report/
    └── index.html
```

Mở file này trong browser để xem báo cáo chi tiết với:
- ✅ Test results
- 📸 Screenshots
- 🎥 Videos (nếu có lỗi)
- 📋 Traces

## 🐛 Troubleshooting

### Lỗi thường gặp:

1. **Browser not found**
   ```bash
   npm run install-browsers
   ```

2. **Connection refused**
   - Kiểm tra YukiMart server đang chạy
   - Xác minh URL: http://yukimart.local

3. **Login failed**
   - Kiểm tra credentials: yukimart@gmail.com / 123456
   - Xác minh user tồn tại trong database

4. **Element not found**
   - Kiểm tra UI có thay đổi không
   - Cập nhật selectors trong test files

## 🎯 Kết Quả Mong Đợi

Tất cả tests được thiết kế để:
- ✅ **PASS** với dữ liệu hiện tại
- 🔄 **Tự động retry** nếu có lỗi tạm thời
- 📸 **Capture screenshots** khi có lỗi
- 📝 **Ghi log chi tiết** cho debugging

## 📞 Hỗ Trợ

Nếu gặp vấn đề với tests:
1. Kiểm tra file `report.md` trong từng module
2. Xem logs trong terminal
3. Kiểm tra screenshots trong test-results/
4. Liên hệ team development để hỗ trợ
