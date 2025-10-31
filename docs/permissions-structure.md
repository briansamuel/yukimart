# Cấu trúc Permissions (Phân quyền) - Modal Tạo Vai Trò

> **Tài liệu này ghi lại toàn bộ cấu trúc permissions từ modal "Tạo vai trò" trong hệ thống YukiMart**
>
> **Ngày tạo:** 2025-10-28
>
> **Ngày cập nhật:** 2025-10-28
>
> **Nguồn:** Phân tích từ UI modal "Tạo vai trò" (5 screenshots)

---

## 📋 Mục lục

1. [Thông tin cơ bản](#thông-tin-cơ-bản)
2. [Tổng quan](#1-tổng-quan)
3. [Hàng hóa](#2-hàng-hóa)
4. [Kho hàng](#3-kho-hàng)
5. [Nhập hàng](#4-nhập-hàng)
6. [Đơn hàng](#5-đơn-hàng)
7. [Giao hàng](#6-giao-hàng)
8. [Khách hàng](#7-khách-hàng)
9. [Khuyến mại](#8-khuyến-mại)
10. [Sổ quỹ](#9-sổ-quỹ)
11. [Bán online](#10-bán-online)
12. [Phân tích & Báo cáo](#11-phân-tích--báo-cáo)
13. [Nhân viên](#12-nhân-viên)
14. [Thiết lập](#13-thiết-lập)

---

## Thông tin cơ bản

Modal "Tạo vai trò" bao gồm:
- **Tên vai trò** (name) - Bắt buộc
- **Mô tả** (description) - Không bắt buộc
- **Ctrl+F để tìm phân quyền** - Chức năng tìm kiếm nhanh

---

## 1. Tổng quan

**Mô tả:** Quyền xem tổng quan hệ thống

### Permissions:
- ☐ **Xem tổng quan** (`view_overview`)

**Các module con:**
- Hàng hóa
- Đơn hàng
- Khách hàng
- Sổ quỹ
- Bán online
- Phân tích & Báo cáo
- Nhân viên
- Thiết lập

---

## 2. Hàng hóa

**Mô tả:** Hàng hóa, Thiết lập giá, Bảo hành & Bảo trì

### 2.1. Danh sách hàng hóa

**Permissions:**
- ☐ **Danh sách hàng hóa** (`products_list`)
  - ☐ Xem - Hàng hóa (`products.view`)
  - ☐ Tạo - Hàng hóa (`products.create`)
  - ☐ Chỉnh sửa - Hàng hóa (`products.edit`)
  - ☐ Xóa - Hàng hóa (`products.delete`)

**Khác:**
- ☐ Import (`products.import`)
- ☐ Xem và sửa giá vốn (`products.view_edit_cost`)
- ☐ Xuất file (`products.export`)
- ☐ Xem và sửa giá nhập (`products.view_edit_import_price`)

---

### 2.2. Thiết lập giá

**Permissions:**
- ☐ **Thiết lập giá** (`price_settings`)
  - ☐ Xem - Bảng giá (`price_lists.view`)
  - ☐ Tạo - Bảng giá (`price_lists.create`)
  - ☐ Chỉnh sửa - Bảng giá (`price_lists.edit`)
  - ☐ Xóa - Bảng giá (`price_lists.delete`)

**Khác:**
- ☐ Import (`price_lists.import`)
- ☐ Xuất file (`price_lists.export`)

---

## 3. Kho hàng

**Mô tả:** Chuyển hàng, Sản xuất, Kiểm kho, Xuất hủy

### 3.1. Chuyển hàng

**Permissions:**
- ☐ **Chuyển hàng** (`stock_transfers`)
  - ☐ Xem - Phiếu chuyển (`stock_transfers.view`)
  - ☐ Tạo - Phiếu chuyển (`stock_transfers.create`)
  - ☐ Chỉnh sửa - Phiếu chuyển (`stock_transfers.edit`)
  - ☐ Hủy - Phiếu chuyển (`stock_transfers.cancel`)

**Khác:**
- ☐ Import (`stock_transfers.import`)
- ☐ Sao chép (`stock_transfers.duplicate`)

---

### 3.2. Sản xuất

**Permissions:**
- ☐ **Sản xuất** (`production`)
  - ☐ Xem - Phiếu sản xuất (`production.view`)
  - ☐ Tạo - Phiếu sản xuất (`production.create`)
  - ☐ Chỉnh sửa - Phiếu sản xuất (`production.edit`)
  - ☐ Hủy - Phiếu sản xuất (`production.cancel`)

**Khác:**
- ☐ Xuất file (`production.export`)

---

### 3.3. Kiểm kho

**Permissions:**
- ☐ **Kiểm kho** (`stock_checks`)
  - ☐ Xem - Phiếu kiểm kho (`stock_checks.view`)
  - ☐ Tạo - Phiếu kiểm kho (`stock_checks.create`)
  - ☐ Chỉnh sửa - Phiếu kiểm kho (`stock_checks.edit`)
  - ☐ Hoàn thành phiếu (`stock_checks.complete`)

**Khác:**
- ☐ Xuất file (`stock_checks.export`)

---

### 3.4. Xuất hủy

**Permissions:**
- ☐ **Xuất hủy** (`stock_disposals`)
  - ☐ Xem - Phiếu xuất hủy (`stock_disposals.view`)
  - ☐ Tạo - Phiếu xuất hủy (`stock_disposals.create`)
  - ☐ Chỉnh sửa - Phiếu xuất hủy (`stock_disposals.edit`)
  - ☐ Hủy - Phiếu xuất hủy (`stock_disposals.cancel`)

**Khác:**
- ☐ Xuất file (`stock_disposals.export`)
- ☐ Sao chép (`stock_disposals.duplicate`)

---

## 4. Nhập hàng

**Mô tả:** Nhà cung cấp, Công nợ nhà cung cấp, Đặt hàng nhập, Nhập hàng, Trả hàng nhập

### 4.1. Danh sách nhà cung cấp

**Permissions:**
- ☐ **Danh sách nhà cung cấp** (`suppliers_list`)
  - ☐ Xem - Nhà cung cấp (`suppliers.view`)
  - ☐ Tạo - Nhà cung cấp (`suppliers.create`)
  - ☐ Chỉnh sửa - Nhà cung cấp (`suppliers.edit`)
  - ☐ Xóa - Nhà cung cấp (`suppliers.delete`)

**Khác:**
- ☐ Import (`suppliers.import`)
- ☐ Xem và sửa số điện thoại (`suppliers.view_edit_phone`)
- ☐ Xuất file (`suppliers.export`)
- ☐ Xem và sửa giá nhập (`suppliers.view_edit_import_price`)

---

### 4.2. Công nợ nhà cung cấp

**Permissions:**
- ☐ **Công nợ nhà cung cấp** (`supplier_debts`)
  - ☐ Xem - Phiếu điều chỉnh (`supplier_debts.view`)
  - ☐ Tạo - Phiếu điều chỉnh (`supplier_debts.create`)
  - ☐ Chỉnh sửa - Phiếu điều chỉnh (`supplier_debts.edit`)
  - ☐ Hủy - Phiếu điều chỉnh (`supplier_debts.cancel`)

---

### 4.3. Thanh toán nhà cung cấp

**Permissions:**
- ☐ **Thanh toán nhà cung cấp** (`supplier_payments`)
  - ☐ Tạo - Phiếu thu chi (`supplier_payments.create`)
  - ☐ Chỉnh sửa - Phiếu thu chi (`supplier_payments.edit`)
  - ☐ Hủy - Phiếu thu chi (`supplier_payments.cancel`)

---

### 4.4. Đặt hàng nhập

**Permissions:**
- ☐ **Đặt hàng nhập** (`purchase_orders`)
  - ☐ Xem - Phiếu đặt hàng nhập (`purchase_orders.view`)
  - ☐ Tạo - Phiếu đặt hàng nhập (`purchase_orders.create`)
  - ☐ Chỉnh sửa - Phiếu đặt hàng nhập (`purchase_orders.edit`)
  - ☐ Hủy - Phiếu đặt hàng nhập (`purchase_orders.cancel`)

**Khác:**
- ☐ Xuất file (`purchase_orders.export`)
- ☐ Sao chép (`purchase_orders.duplicate`)
- ☐ In lại (`purchase_orders.reprint`)

---

### 4.5. Nhập hàng

**Permissions:**
- ☐ **Nhập hàng** (`purchases`)
  - ☐ Xem - Phiếu nhập hàng (`purchases.view`)
  - ☐ Tạo - Phiếu nhập hàng (`purchases.create`)
  - ☐ Chỉnh sửa - Phiếu nhập hàng (`purchases.edit`)
  - ☐ Hủy - Phiếu nhập hàng (`purchases.cancel`)
  - ☐ Thông tin phí (?) (`purchases.view_fees`)

**Khác:**
- ☐ Xuất file (`purchases.export`)

---

### 4.6. Chi phí nhập hàng

**Permissions:**
- ☐ **Chi phí nhập hàng** (`purchase_expenses`)
  - ☐ Xem - Chi phí (`purchase_expenses.view`)
  - ☐ Tạo - Chi phí (`purchase_expenses.create`)
  - ☐ Chỉnh sửa - Chi phí (`purchase_expenses.edit`)
  - ☐ Xóa - Chi phí (`purchase_expenses.delete`)

---

### 4.7. Trả hàng nhập

**Permissions:**
- ☐ **Trả hàng nhập** (`purchase_returns`)
  - ☐ Xem - Phiếu trả hàng nhập (`purchase_returns.view`)
  - ☐ Tạo - Phiếu trả hàng nhập (`purchase_returns.create`)
  - ☐ Chỉnh sửa - Phiếu trả hàng nhập (`purchase_returns.edit`)
  - ☐ Hủy - Phiếu trả hàng nhập (`purchase_returns.cancel`)

**Khác:**
- ☐ Xuất file (`purchase_returns.export`)
- ☐ Sao chép (`purchase_returns.duplicate`)

---

## 5. Đơn hàng

**Mô tả:** Đặt hàng, Hóa đơn, Trả hàng, Hoàn tiền

### 5.1. Tạo đơn đặt hàng, hóa đơn

**Permissions:**
- ☐ **Tạo đơn đặt hàng, hóa đơn** (`create_orders_invoices`)
  - ☐ Xem - Nhà cung cấp (`create_orders.view_suppliers`)

---

### 5.2. Đặt hàng

**Permissions:**
- ☐ **Đặt hàng** (`orders`)
  - ☐ Xem - Đơn đặt hàng (`orders.view`)
  - ☐ Tạo - Đơn đặt hàng (`orders.create`)
  - ☐ Chỉnh sửa - Đơn đặt hàng (`orders.edit`)
  - ☐ Hủy - Đơn đặt hàng (`orders.cancel`)

**Khác:**
- ☐ Import (`orders.import`)
- ☐ Sao chép (`orders.duplicate`)
- ☐ Tạo phiếu nhập hàng từ phiếu đặt hàng nhập (`orders.create_purchase_from_order`)
- ☐ In lại (`orders.reprint`)

---

### 5.3. Hóa đơn

**Permissions:**
- ☐ **Hóa đơn** (`invoices`)
  - ☐ Xem - Hóa đơn (`invoices.view`)
  - ☐ Tạo - Hóa đơn (`invoices.create`)
  - ☐ Chỉnh sửa - Hóa đơn (`invoices.edit`)
  - ☐ Hủy - Hóa đơn (`invoices.cancel`)

**Khác:**
- ☐ Xuất file (`invoices.export`)

---

### 5.4. Thu khác

**Permissions:**
- ☐ **Thu khác** (`other_receipts`)
  - ☐ Xem - Thu khác (`other_receipts.view`)
  - ☐ Tạo - Thu khác (`other_receipts.create`)
  - ☐ Chỉnh sửa - Thu khác (`other_receipts.edit`)
  - ☐ Xóa - Thu khác (`other_receipts.delete`)

---

### 5.5. Trả hàng

**Permissions:**
- ☐ **Trả hàng** (`returns`)
  - ☐ Xem - Phiếu trả hàng (`returns.view`)
  - ☐ Tạo - Phiếu trả hàng (`returns.create`)
  - ☐ Chỉnh sửa - Phiếu trả hàng (`returns.edit`)
  - ☐ Hủy - Phiếu trả hàng (`returns.cancel`)

**Khác:**
- ☐ Xuất file (`returns.export`)
- ☐ Sao chép (`returns.duplicate`)

---

### 5.6. Hoàn tiền

**Permissions:**
- ☐ **Hoàn tiền** (`refunds`)
  - ☐ Xem - Đơn hoàn tiền (`refunds.view`)
  - ☐ Tạo - Đơn hoàn tiền (`refunds.create`)
  - ☐ Chỉnh sửa - Đơn hoàn tiền (`refunds.edit`)
  - ☐ Hủy - Đơn hoàn tiền (`refunds.cancel`)

---

## 6. Giao hàng

**Mô tả:** Đối tác giao hàng, Công nợ đối tác giao hàng, Thanh toán đối tác giao hàng

### 6.1. Danh sách đối tác giao hàng

**Permissions:**
- ☐ **Danh sách đối tác giao hàng** (`delivery_partners_list`)
  - ☐ Xem - Đối tác giao hàng (`delivery_partners.view`)
  - ☐ Tạo - Đối tác giao hàng (`delivery_partners.create`)
  - ☐ Chỉnh sửa - Đối tác giao hàng (`delivery_partners.edit`)
  - ☐ Xóa - Đối tác giao hàng (`delivery_partners.delete`)

**Khác:**
- ☐ Import (`delivery_partners.import`)
- ☐ Xuất file (`delivery_partners.export`)

---

### 6.2. Công nợ đối tác giao hàng

**Permissions:**
- ☐ **Công nợ đối tác giao hàng** (`delivery_partner_debts`)
  - ☐ Xem - Phiếu điều chỉnh (`delivery_partner_debts.view`)
  - ☐ Tạo - Phiếu điều chỉnh (`delivery_partner_debts.create`)
  - ☐ Chỉnh sửa - Phiếu điều chỉnh (`delivery_partner_debts.edit`)
  - ☐ Hủy - Phiếu điều chỉnh (`delivery_partner_debts.cancel`)

---

### 6.3. Thanh toán đối tác giao hàng

**Permissions:**
- ☐ **Thanh toán đối tác giao hàng** (`delivery_partner_payments`)
  - ☐ Tạo - Phiếu thu chi (`delivery_partner_payments.create`)
  - ☐ Chỉnh sửa - Phiếu thu chi (`delivery_partner_payments.edit`)
  - ☐ Hủy - Phiếu thu chi (`delivery_partner_payments.cancel`)

---

## 7. Khách hàng

**Mô tả:** Khách hàng, Nhóm khách hàng, Công nợ khách hàng, Tích điểm

### 7.1. Khách hàng

**Permissions:**
- ☐ **Khách hàng** (`customers`)
  - ☐ Xem - Khách hàng (`customers.view`)
  - ☐ Tạo - Khách hàng (`customers.create`)
  - ☐ Chỉnh sửa - Khách hàng (`customers.edit`)
  - ☐ Xóa - Khách hàng (`customers.delete`)

**Khác:**
- ☐ Import (`customers.import`)
- ☐ Xem và sửa số điện thoại (`customers.view_edit_phone`)
- ☐ Xuất file (`customers.export`)
- ☐ Tạo mới và sửa nhóm khách hàng (`customers.manage_groups`)

---

### 7.2. Công nợ khách hàng

**Permissions:**
- ☐ **Công nợ khách hàng** (`customer_debts`)
  - ☐ Xem - Công nợ (`customer_debts.view`)
  - ☐ Tạo - Phiếu điều chỉnh (`customer_debts.create`)
  - ☐ Chỉnh sửa - Phiếu điều chỉnh (`customer_debts.edit`)
  - ☐ Hủy - Phiếu điều chỉnh (`customer_debts.cancel`)

---

### 7.3. Thanh toán khách hàng

**Permissions:**
- ☐ **Thanh toán khách hàng** (`customer_payments`)
  - ☐ Tạo - Phiếu thu chi (`customer_payments.create`)
  - ☐ Chỉnh sửa - Phiếu thu chi (`customer_payments.edit`)
  - ☐ Hủy - Phiếu thu chi (`customer_payments.cancel`)

---

### 7.4. Tích điểm

**Permissions:**
- ☐ **Tích điểm** (`loyalty_points`)
  - ☐ Xem - Lịch sử tích điểm (`loyalty_points.view`)
  - ☐ Khác - Điều chỉnh điểm (`loyalty_points.adjust`)

---

## 8. Khuyến mại

**Mô tả:** Khuyến mại, Voucher, Coupon

### 8.1. Khuyến mại

**Permissions:**
- ☐ **Khuyến mại** (`promotions`)
  - ☐ Xem - Chương trình khuyến mại (`promotions.view`)
  - ☐ Tạo - Chương trình khuyến mại (`promotions.create`)
  - ☐ Chỉnh sửa - Chương trình khuyến mại (`promotions.edit`)
  - ☐ Xóa - Chương trình khuyến mại (`promotions.delete`)

---

### 8.2. Voucher

**Permissions:**
- ☐ **Voucher** (`vouchers`)
  - ☐ Xem - Voucher (`vouchers.view`)
  - ☐ Tạo - Voucher (`vouchers.create`)
  - ☐ Chỉnh sửa - Voucher (`vouchers.edit`)
  - ☐ Xóa - Voucher (`vouchers.delete`)

**Khác:**
- ☐ Phát hành voucher (`vouchers.issue`)

---

### 8.3. Coupon

**Permissions:**
- ☐ **Coupon** (`coupons`)
  - ☐ Xem - Coupon (`coupons.view`)
  - ☐ Tạo - Coupon (`coupons.create`)
  - ☐ Chỉnh sửa - Coupon (`coupons.edit`)
  - ☐ Xóa - Coupon (`coupons.delete`)

---

## 9. Sổ quỹ

**Mô tả:** Sổ quỹ, Tài khoản ngân hàng

### 9.1. Sổ quỹ

**Permissions:**
- ☐ **Sổ quỹ** (`cash_book`)
  - ☐ Xem - Phiếu thu chi (`cash_book.view`)
  - ☐ Tạo - Phiếu thu chi (`cash_book.create`)
  - ☐ Chỉnh sửa - Phiếu thu chi (`cash_book.edit`)
  - ☐ Hủy - Phiếu thu chi (`cash_book.cancel`)

**Khác:**
- ☐ Xuất file (`cash_book.export`)

---

### 9.2. Tài khoản ngân hàng

**Permissions:**
- ☐ **Tài khoản ngân hàng** (`bank_accounts`)
  - ☐ Tạo - Tài khoản ngân hàng (`bank_accounts.create`)
  - ☐ Chỉnh sửa - Tài khoản ngân hàng (`bank_accounts.edit`)
  - ☐ Xóa - Tài khoản ngân hàng (`bank_accounts.delete`)

---

## 10. Bán online

**Mô tả:** Kết nối kênh bán

### 10.1. Kết nối kênh bán

**Permissions:**
- ☐ **Kết nối kênh bán** (`online_channels`)
  - ☐ Xem - Kênh bán (`online_channels.view`)
  - ☐ Tạo - Kênh bán (`online_channels.create`)
  - ☐ Chỉnh sửa - Kênh bán (`online_channels.edit`)
  - ☐ Xóa - Kênh bán (`online_channels.delete`)

---

## 11. Phân tích & Báo cáo

**Mô tả:** Phân tích - Kinh doanh, Hàng hóa, Khách hàng, Hiệu quả | Báo cáo - Cuối ngày, Bán hàng, Đặt hàng, Hàng hóa, Khách hàng, Nhà cung cấp, Nhân viên, Kênh bán hàng, Tài chính

### 11.1. Phân tích

**Sub-modules:** Kinh doanh, Hàng hóa, Khách hàng, Hiệu quả

**Permissions:**
- ☐ **Kinh doanh** (`analytics.business`)
- ☐ **Hàng hóa** (`analytics.products`)
- ☐ **Khách hàng** (`analytics.customers`)
- ☐ **Hiệu quả** (`analytics.performance`)

---

### 11.2. Báo cáo

**Sub-modules:** Cuối ngày, Bán hàng, Đặt hàng, Hàng hóa, Khách hàng, Nhà cung cấp, Nhân viên, Kênh bán hàng, Tài chính

#### 11.2.1. Báo cáo cuối ngày

**Permissions:**
- ☐ **Báo cáo cuối ngày** (`reports.end_of_day`)
  - ☐ Bán hàng (`reports.end_of_day.sales`)
  - ☐ Thu chi (`reports.end_of_day.cash_flow`)
  - ☐ Hàng hóa (`reports.end_of_day.products`)
  - ☐ Tổng hợp (`reports.end_of_day.summary`)

---

#### 11.2.2. Báo cáo bán hàng

**Permissions:**
- ☐ **Báo cáo bán hàng** (`reports.sales`)
  - ☐ Thời gian (`reports.sales.time_based`)
  - ☐ Lợi nhuận (`reports.sales.profit`)
  - ☐ Giảm giá hóa đơn (`reports.sales.discounts`)
  - ☐ Trả hàng (`reports.sales.returns`)
  - ☐ Nhân viên (`reports.sales.by_staff`)
  - ☐ Chi nhanh (`reports.sales.by_branch`)

---

#### 11.2.3. Báo cáo đặt hàng

**Permissions:**
- ☐ **Báo cáo đặt hàng** (`reports.orders`)
  - ☐ Hàng hóa (`reports.orders.products`)
  - ☐ Giao dịch (`reports.orders.transactions`)

---

#### 11.2.4. Báo cáo hàng hóa

**Permissions:**
- ☐ **Báo cáo hàng hóa** (`reports.products`)
  - ☐ Bán hàng (`reports.products.sales`)
  - ☐ Lợi nhuận (`reports.products.profit`)
  - ☐ Giá trị kho (`reports.products.inventory_value`)
  - ☐ Xuất nhập tồn (`reports.products.stock_movement`)
  - ☐ Xuất nhập tồn chi tiết (`reports.products.stock_movement_detail`)
  - ☐ Khách theo hàng bán (`reports.products.customers_by_product`)
  - ☐ Nhà cung cấp theo hàng nhập (`reports.products.suppliers_by_product`)

---

#### 11.2.5. Báo cáo khách hàng

**Permissions:**
- ☐ **Báo cáo khách hàng** (`reports.customers`)
  - ☐ Bán hàng (`reports.customers.sales`)
  - ☐ Lợi nhuận (`reports.customers.profit`)
  - ☐ Công nợ (`reports.customers.debt`)
  - ☐ Hàng bán theo khách (`reports.customers.products_by_customer`)

---

#### 11.2.6. Báo cáo nhà cung cấp

**Permissions:**
- ☐ **Báo cáo nhà cung cấp** (`reports.suppliers`)
  - ☐ Bán hàng (`reports.suppliers.sales`)
  - ☐ Lợi nhuận (`reports.suppliers.profit`)
  - ☐ Công nợ (`reports.suppliers.debt`)
  - ☐ Hàng nhập theo nhà cung cấp (`reports.suppliers.products_by_supplier`)
  - ☐ Thuế VAT nhập hàng (`reports.suppliers.vat`)

---

#### 11.2.7. Báo cáo nhân viên

**Permissions:**
- ☐ **Báo cáo nhân viên** (`reports.staff`)
  - ☐ Bán hàng (`reports.staff.sales`)
  - ☐ Lợi nhuận (`reports.staff.profit`)
  - ☐ Hàng bán theo nhân viên (`reports.staff.products_by_staff`)

---

#### 11.2.8. Báo cáo kênh bán hàng

**Permissions:**
- ☐ **Báo cáo kênh bán hàng** (`reports.sales_channels`)
  - ☐ Bán hàng (`reports.sales_channels.sales`)
  - ☐ Lợi nhuận (`reports.sales_channels.profit`)
  - ☐ Hàng bán theo kênh (`reports.sales_channels.products_by_channel`)

---

#### 11.2.9. Xem báo cáo tài chính

**Permissions:**
- ☐ **Xem báo cáo tài chính** (`reports.financial`)

---

## 12. Nhân viên

**Mô tả:** Thông tin nhân viên, Lịch làm việc & Chấm công, Bảng tính lương & Thanh toán lương

### 12.1. Thông tin nhân viên

**Permissions:**
- ☐ **Thông tin nhân viên** (`staff.info`)
  - ☐ Xem - Nhân viên (`staff.view`)
  - ☐ Tạo - Nhân viên (`staff.create`)
  - ☐ Chỉnh sửa - Nhân viên (`staff.edit`)
  - ☐ Xóa - Nhân viên (`staff.delete`)
  - ☐ Mức lương (`staff.salary`)
  - ☐ Nợ và tạm ứng (`staff.debt_advance`)

**Khác:**
- ☐ Xuất file thông tin nhân viên (`staff.export`)
- ☐ Không được xem chấm công, lương của nhân viên khác (`staff.restrict_view_others`)

---

### 12.2. Lịch làm việc, chấm công

**Permissions:**
- ☐ **Lịch làm việc, chấm công** (`staff.attendance`)
  - ☐ Xem - Lịch làm việc, chấm công (`staff.attendance.view`)
  - ☐ Tạo - Lịch làm việc, chấm công (`staff.attendance.create`)
  - ☐ Chỉnh sửa - Lịch làm việc (`staff.attendance.edit`)
  - ☐ Xóa - Lịch làm việc, chấm công (`staff.attendance.delete`)
  - ☐ Dữ liệu chấm công (`staff.attendance.data`)
  - ☐ Giờ chấm công (`staff.attendance.time`)

**Khác:**
- ☐ Xuất file lịch làm việc (`staff.attendance.export`)
- ☐ Sao chép lịch làm việc (`staff.attendance.duplicate`)

---

### 12.3. Bảng tính lương, thanh toán lương

**Permissions:**
- ☐ **Bảng tính lương, thanh toán lương** (`staff.payroll`)
  - ☐ Xem - Bảng tính lương (`staff.payroll.view`)
  - ☐ Tạo - Bảng tính lương (`staff.payroll.create`)
  - ☐ Chỉnh sửa - Bảng tính lương (`staff.payroll.edit`)
  - ☐ Xóa - Bảng tính lương (`staff.payroll.delete`)
  - ☐ Phiếu chi lương (`staff.payroll.payment_voucher`)

**Khác:**
- ☐ Xuất file bảng tính lương (`staff.payroll.export`)
- ☐ Chốt lương (`staff.payroll.finalize`)

---

### 12.4. Thiết lập ca làm việc, tính công

**Permissions:**
- ☐ **Thiết lập ca làm việc, tính công** (`staff.shift_settings`)
  - ☐ Xem - Bảng hoa hồng (`staff.shift_settings.view_commission`)
  - ☐ Tạo - Mẫu lương (`staff.shift_settings.create_salary_template`)
  - ☐ Chỉnh sửa - Thiết lập tính công (`staff.shift_settings.edit_attendance_settings`)
  - ☐ Xóa - Thiết lập tính công (`staff.shift_settings.delete_attendance_settings`)

---

### 12.5. Thiết lập máy chấm công

**Permissions:**
- ☐ **Thiết lập máy chấm công** (`staff.time_clock`)
  - ☐ Xem - Máy chấm công (`staff.time_clock.view`)
  - ☐ Tạo - Máy chấm công (`staff.time_clock.create`)
  - ☐ Chỉnh sửa - Máy chấm công (`staff.time_clock.edit`)
  - ☐ Xóa - Máy chấm công (`staff.time_clock.delete`)
  - ☐ Tải khoản chấm công (`staff.time_clock.download_data`)

---

## 13. Thiết lập

**Mô tả:** Cửa hàng - Người dùng, Chi nhánh | Khác - Thiết lập chung, Mẫu in, Lịch sử thao tác

### 13.1. Cửa hàng

#### 13.1.1. Danh sách người dùng

**Permissions:**
- ☐ **Danh sách người dùng** (`settings.users`)
  - ☐ Xem - Người dùng (`settings.users.view`)
  - ☐ Tạo - Người dùng (`settings.users.create`)
  - ☐ Chỉnh sửa - Người dùng (`settings.users.edit`)
  - ☐ Xóa - Người dùng (`settings.users.delete`)

**Khác:**
- ☐ Xuất file (`settings.users.export`)

---

#### 13.1.2. Chi nhánh

**Permissions:**
- ☐ **Chi nhánh** (`settings.branches`)
  - ☐ Xem - Chi nhánh (`settings.branches.view`)
  - ☐ Tạo - Chi nhánh (`settings.branches.create`)
  - ☐ Chỉnh sửa - Chi nhánh (`settings.branches.edit`)
  - ☐ Xóa - Chi nhánh (`settings.branches.delete`)

---

### 13.2. Khác

#### 13.2.1. Thiết lập chung

**Permissions:**
- ☐ **Thiết lập chung** (`settings.general`)

---

#### 13.2.2. Mẫu in

**Permissions:**
- ☐ **Mẫu in** (`settings.print_templates`)
  - ☐ Xem - Mẫu in (`settings.print_templates.view`)
  - ☐ Chỉnh sửa - Mẫu in (`settings.print_templates.edit`)
  - ☐ Xóa - Mẫu in (`settings.print_templates.delete`)

---

#### 13.2.3. Lịch sử thao tác

**Permissions:**
- ☐ **Lịch sử thao tác** (`settings.activity_log`)

---

## 📊 Tổng kết

### Số lượng Module chính: **13 modules**

1. Tổng quan
2. Hàng hóa
3. Kho hàng
4. Nhập hàng
5. Đơn hàng
6. Giao hàng
7. Khách hàng
8. Khuyến mại
9. Sổ quỹ
10. Bán online
11. Phân tích & Báo cáo
12. Nhân viên
13. Thiết lập

### Số lượng Sub-modules: **60+ sub-modules**

### Tổng số Permissions ước tính: **300+ permissions**

---

## 🔧 Cấu trúc Database đề xuất

### Bảng `permissions`

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE COMMENT 'Tên permission (vd: products.view)',
    display_name VARCHAR(255) NOT NULL COMMENT 'Tên hiển thị',
    description TEXT NULL COMMENT 'Mô tả',
    module VARCHAR(100) NOT NULL COMMENT 'Module chính (vd: products, orders)',
    sub_module VARCHAR(100) NULL COMMENT 'Sub-module (vd: price_lists, stock_transfers)',
    action VARCHAR(50) NOT NULL COMMENT 'Hành động (view, create, edit, delete, etc)',
    sort_order INT DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_module (module),
    INDEX idx_sub_module (sub_module),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Bảng `role_permissions` (Many-to-Many)

```sql
CREATE TABLE role_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📝 Ghi chú

1. **Naming Convention cho Permissions:**
   - Format: `{module}.{action}` hoặc `{module}.{sub_module}.{action}`
   - Ví dụ: `products.view`, `staff.attendance.create`, `reports.sales.profit`

2. **Actions phổ biến:**
   - `view` - Xem
   - `create` - Tạo
   - `edit` - Chỉnh sửa
   - `delete` - Xóa
   - `cancel` - Hủy
   - `import` - Import
   - `export` - Xuất file
   - `duplicate` - Sao chép
   - `reprint` - In lại
   - `finalize` - Chốt (lương, báo cáo)

3. **Permissions đặc biệt:**
   - `view_edit_cost` - Xem và sửa giá vốn
   - `view_edit_phone` - Xem và sửa số điện thoại
   - `manage_groups` - Quản lý nhóm
   - `restrict_view_others` - Hạn chế xem thông tin người khác
   - `download_data` - Tải dữ liệu từ thiết bị

4. **Modules chính trong Sidebar (9 items):**
   - Tổng quan
   - Hàng hóa
   - Đơn hàng
   - Khách hàng
   - Sổ quỹ
   - Bán online
   - Phân tích & Báo cáo
   - Nhân viên
   - Thiết lập

5. **Modules không có trong Sidebar nhưng vẫn là modules chính:**
   - Kho hàng
   - Nhập hàng
   - Giao hàng
   - Khuyến mại

---

## 🎯 Ưu tiên triển khai

### Phase 1 - Core Modules (Đã implement trong modal)
- ✅ Tổng quan
- ✅ Hàng hóa (Danh sách hàng hóa, Thiết lập giá)
- ✅ Đơn hàng (Đặt hàng)
- ✅ Khách hàng

### Phase 2 - Extended Modules (Cần thêm vào modal)
- ⏳ Kho hàng (Chuyển hàng, Sản xuất, Kiểm kho, Xuất hủy)
- ⏳ Nhập hàng (7 sub-modules)
- ⏳ Sổ quỹ
- ⏳ Bán online

### Phase 3 - Advanced Modules
- ⏳ Phân tích & Báo cáo (9 sub-modules)
- ⏳ Nhân viên (5 sub-modules)
- ⏳ Thiết lập (5 sub-modules)
- ⏳ Giao hàng
- ⏳ Khuyến mại

---

**Tài liệu này sẽ được cập nhật khi có thêm thông tin chi tiết từ UI hoặc yêu cầu mới.**

**Lần cập nhật cuối:** 2025-10-28 - Thêm đầy đủ 13 modules với 60+ sub-modules và 300+ permissions từ 5 screenshots UI.

