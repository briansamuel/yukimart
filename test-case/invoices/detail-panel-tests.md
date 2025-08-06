# Invoice Detail Panel Tests

## Test Overview
Test the enhanced invoice detail panel with payment history tab and improved UI layout.

## Test Environment
- URL: http://yukimart.local/admin/invoices
- Login: yukimart@gmail.com / 123456

## Test Categories

### 1. Detail Panel Layout Tests

#### Test 1.1: Fixed Width Panel
**Objective**: Verify detail panel has fixed width and doesn't get affected by table scroll

**Steps**:
1. Navigate to invoice list page
2. Click on any invoice row to expand detail panel
3. Scroll the main table horizontally
4. Verify detail panel maintains fixed width (600px)
5. Verify detail panel doesn't move with table scroll

**Expected Results**:
- Detail panel width remains constant at 600px
- Panel position is not affected by table horizontal scroll
- Panel content remains fully visible and accessible

#### Test 1.2: Customer Header Display
**Objective**: Verify customer information display in panel header

**Steps**:
1. Expand detail panel for invoice with customer
2. Verify customer name display with external link icon
3. Verify invoice number display below customer name
4. Verify status badge display
5. Test with "Khách lẻ" (walk-in customer) invoice

**Expected Results**:
- Customer name displayed prominently with icon
- Invoice number shown below customer name
- Status badge correctly styled and positioned
- "Khách lẻ" displayed for walk-in customers

### 2. Tab Navigation Tests

#### Test 2.1: Tab Switching
**Objective**: Verify tab navigation works correctly

**Steps**:
1. Expand invoice detail panel
2. Verify "Thông tin" tab is active by default
3. Click "Lịch sử thanh toán" tab
4. Verify tab switches correctly
5. Switch back to "Thông tin" tab

**Expected Results**:
- Default tab "Thông tin" is active on panel open
- Tab switching works smoothly
- Only one tab is active at a time
- Tab content changes correctly

#### Test 2.2: Tab Content Display
**Objective**: Verify correct content display in each tab

**Steps**:
1. Open "Thông tin" tab
2. Verify invoice information fields display
3. Verify product table display
4. Verify summary calculations
5. Switch to "Lịch sử thanh toán" tab
6. Verify payment history or empty state

**Expected Results**:
- "Thông tin" tab shows complete invoice details
- Product table with all columns visible
- Summary shows correct calculations
- Payment history tab shows payments or empty state

### 3. Payment History Tab Tests

#### Test 3.1: Payment History Display
**Objective**: Verify payment history displays correctly

**Steps**:
1. Find invoice with payment history
2. Open detail panel and switch to "Lịch sử thanh toán" tab
3. Verify payment entries display
4. Check payment method icons
5. Verify payment amounts and dates
6. Check payment status display

**Expected Results**:
- Payment entries listed in chronological order (newest first)
- Correct payment method icons (cash, transfer, card)
- Payment amounts formatted correctly
- Payment dates and times displayed
- Payment status shown correctly

#### Test 3.2: Empty Payment History
**Objective**: Verify empty state for invoices without payments

**Steps**:
1. Find invoice without payment history
2. Open detail panel and switch to "Lịch sử thanh toán" tab
3. Verify empty state display
4. Check empty state message and icon

**Expected Results**:
- Empty state with appropriate icon displayed
- Message "Chưa có lịch sử thanh toán" shown
- Descriptive text about no payment transactions

#### Test 3.3: Payment Method Icons
**Objective**: Verify correct icons for different payment methods

**Steps**:
1. Find invoices with different payment methods
2. Open payment history for each
3. Verify icons match payment methods:
   - Cash: money-bill icon with green background
   - Transfer: university icon with blue background
   - Card: credit-card icon with info background
   - Other: wallet icon with warning background

**Expected Results**:
- Icons correctly match payment methods
- Background colors appropriate for each method
- Icons clearly visible and properly sized

### 4. Information Tab Tests

#### Test 4.1: Invoice Information Display
**Objective**: Verify invoice information fields display correctly

**Steps**:
1. Open "Thông tin" tab
2. Verify "Người tạo" field
3. Verify "Người bán" field
4. Verify "Ngày bán" field
5. Verify "Kênh bán" field
6. Verify "Bảng giá" field

**Expected Results**:
- All information fields display correct data
- Date format is dd/mm/yyyy HH:mm
- Channel shows "Bán trực tiếp"
- Price list shows "Sale"

#### Test 4.2: Customer Information Display
**Objective**: Verify customer information section

**Steps**:
1. Open invoice with customer information
2. Verify phone number display
3. Verify email display
4. Verify address display
5. Test with invoice without customer (walk-in)

**Expected Results**:
- Customer fields show correct information
- Fields show "N/A" when data not available
- Customer section hidden for walk-in customers

#### Test 4.3: Product Table Display
**Objective**: Verify product table shows all required columns

**Steps**:
1. Open "Thông tin" tab
2. Verify product table columns:
   - Mã hàng (Product SKU)
   - Tên hàng (Product Name)
   - Số lượng (Quantity)
   - Đơn giá (Unit Price)
   - Giảm giá (Discount)
   - Giá bán (Selling Price)
   - Thành tiền (Total Amount)

**Expected Results**:
- All columns display correctly
- Data properly formatted (numbers with thousand separators)
- Table responsive and readable

#### Test 4.4: Summary Calculations
**Objective**: Verify summary section calculations

**Steps**:
1. Open "Thông tin" tab
2. Verify "Tổng tiền hàng" with item count
3. Verify "Giảm giá hóa đơn"
4. Verify "Khách cần trả"
5. Verify "Khách đã trả"

**Expected Results**:
- Item count shown in parentheses
- All amounts formatted correctly
- Calculations match invoice data
- Colors appropriate (red for discounts, green for paid amounts)

### 5. Action Buttons Tests

#### Test 5.1: Action Button Display
**Objective**: Verify action buttons display and positioning

**Steps**:
1. Open detail panel
2. Verify action buttons at bottom
3. Check button alignment (centered)
4. Verify button styling and spacing

**Expected Results**:
- Buttons centered at bottom of panel
- Proper spacing between buttons
- Consistent button styling

#### Test 5.2: Action Button Functionality
**Objective**: Verify action buttons work correctly

**Steps**:
1. Click "Hủy" button
2. Verify cancel functionality
3. Click "Trả hàng" button
4. Verify return order functionality

**Expected Results**:
- Cancel button triggers appropriate action
- Return order button opens return process
- Buttons respond correctly to clicks

### 6. Responsive Design Tests

#### Test 6.1: Desktop Display
**Objective**: Verify panel display on desktop screens

**Steps**:
1. Test on screen width > 1400px
2. Verify panel width is 600px
3. Test on screen width 1200-1400px
4. Verify panel width is 500px
5. Test on screen width 992-1200px
6. Verify panel width is 450px

**Expected Results**:
- Panel width adjusts based on screen size
- Content remains readable at all sizes
- Layout maintains proper proportions

#### Test 6.2: Mobile Display
**Objective**: Verify panel display on mobile screens

**Steps**:
1. Test on screen width < 992px
2. Verify panel takes full width
3. Check content readability
4. Verify tab navigation works on mobile

**Expected Results**:
- Panel uses full width on mobile
- Content remains accessible
- Tabs work properly on touch devices

### 7. Performance Tests

#### Test 7.1: Panel Loading Speed
**Objective**: Verify detail panel loads quickly

**Steps**:
1. Click to expand detail panel
2. Measure loading time
3. Verify loading indicator appears
4. Check content loads completely

**Expected Results**:
- Panel loads within 2 seconds
- Loading indicator shows during load
- All content loads completely

#### Test 7.2: Multiple Panel Operations
**Objective**: Verify performance with multiple panel operations

**Steps**:
1. Open and close multiple detail panels
2. Switch between tabs multiple times
3. Verify no memory leaks or slowdowns

**Expected Results**:
- Consistent performance across operations
- No noticeable slowdowns
- Smooth animations and transitions

## Test Execution Notes

### Prerequisites
- Ensure test database has invoices with various payment statuses
- Have invoices with and without payment history
- Include invoices with different payment methods
- Test with both customer and walk-in invoices

### Test Data Requirements
- Invoices with completed payments
- Invoices with no payments
- Invoices with multiple payment entries
- Invoices with different payment methods (cash, transfer, card)

### Browser Compatibility
Test on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Success Criteria
- All detail panel features work as expected
- Payment history displays correctly
- Fixed width layout maintained
- Responsive design works properly
- Performance meets requirements
