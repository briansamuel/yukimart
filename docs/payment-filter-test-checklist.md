# Payment Filter Test Cases Checklist

## Test Environment
- **URL**: http://yukimart.local/tests/payment-filter-test.html
- **Backend Endpoint**: `/test-payment-pagination`
- **Summary Endpoint**: `/test-payment-summary-direct`
- **Date**: 2025-07-11
- **Total Records**: 2,425 payments

## Test Categories

### 1. Pagination Tests ✅

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `pagination-page-1` | Test Page 1 | Test pagination page 1 with default per_page | `current_page: 1, per_page: 5, data.length: 5` | ⏳ Pending |
| `pagination-page-2` | Test Page 2 | Test pagination page 2 | `current_page: 2, per_page: 5, data.length: 5` | ⏳ Pending |
| `pagination-per-page-10` | Test Per Page 10 | Test pagination with 10 items per page | `current_page: 1, per_page: 10, data.length: 10` | ⏳ Pending |

**Expected Behavior:**
- ✅ Page numbers should be clickable
- ✅ Current page should be highlighted
- ✅ Previous/Next buttons should work correctly
- ✅ Info text should show correct range (e.g., "Hiển thị 1 đến 5 của 2425 kết quả")

### 2. Time Filter Tests ⏰

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-time-today` | Filter Today | Test time filter for today | `success: true, data: array` | ⏳ Pending |
| `filter-time-this-month` | Filter This Month | Test time filter for this month | `success: true, data: array` | ⏳ Pending |
| `filter-time-last-month` | Filter Last Month | Test time filter for last month | `success: true, data: array` | ⏳ Pending |

**Additional Time Filters to Test:**
- [ ] Yesterday
- [ ] This Week  
- [ ] Last Week
- [ ] 7 Days
- [ ] 30 Days
- [ ] This Quarter
- [ ] Last Quarter
- [ ] This Year
- [ ] Last Year
- [ ] Custom Date Range

### 3. Payment Type Filter Tests 💰

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-payment-type-receipt` | Filter Receipt | Test filter by payment type receipt | `success: true, data[0].payment_type: 'receipt'` | ⏳ Pending |
| `filter-payment-type-payment` | Filter Payment | Test filter by payment type payment | `success: true, data[0].payment_type: 'payment'` | ⏳ Pending |

**Expected Behavior:**
- ✅ Only receipts should show when "Thu" is selected
- ✅ Only payments should show when "Chi" is selected
- ✅ All records should show when "Tất cả" is selected

### 4. Payment Method Filter Tests 💳

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-payment-method-cash` | Filter Cash | Test filter by payment method cash | `success: true, data[0].payment_method: 'cash'` | ⏳ Pending |
| `filter-payment-method-card` | Filter Card | Test filter by payment method card | `success: true, data[0].payment_method: 'card'` | ⏳ Pending |

**Additional Payment Methods to Test:**
- [ ] Transfer (Chuyển khoản)
- [ ] E-wallet (Ví điện tử)
- [ ] Check (Séc)

### 5. Status Filter Tests 📊

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-status-completed` | Filter Completed | Test filter by status completed | `success: true, data[0].status: 'completed'` | ⏳ Pending |

**Additional Statuses to Test:**
- [ ] Pending (Đang xử lý)
- [ ] Cancelled (Đã hủy)
- [ ] Draft (Nháp)

### 6. Branch Shop Filter Tests 🏪

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-branch-shop` | Filter Branch Shop | Test filter by specific branch shop | `success: true, data[0].branch_shop_id: [selected_id]` | ⏳ Pending |

### 7. Creator Filter Tests 👤

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-creator` | Filter Creator | Test filter by payment creator | `success: true, data[0].created_by: [selected_id]` | ⏳ Pending |

### 8. Staff Filter Tests 👥

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-staff` | Filter Staff | Test filter by collector staff | `success: true, data[0].collector_id: [selected_id]` | ⏳ Pending |

### 9. Combined Filter Tests 🔄

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `filter-combined-cash-receipt` | Cash + Receipt | Test combined filter: cash payment method + receipt type | `success: true, payment_method: 'cash', payment_type: 'receipt'` | ⏳ Pending |

**Additional Combined Tests:**
- [ ] Time Filter + Payment Type
- [ ] Time Filter + Payment Method
- [ ] Branch Shop + Payment Type
- [ ] Creator + Time Filter
- [ ] All Filters Combined

### 10. Search Filter Tests 🔍

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `search-payment-number` | Search Payment Number | Test search by payment number | `success: true, data contains matching records` | ⏳ Pending |
| `search-customer-name` | Search Customer Name | Test search by customer name | `success: true, data contains matching records` | ⏳ Pending |

### 11. Summary Calculation Tests 📈

| Test ID | Test Name | Description | Expected Result | Status |
|---------|-----------|-------------|-----------------|--------|
| `summary-this-month` | Summary This Month | Test summary calculation for this month | `opening_balance: number, total_income: string, total_expense: string, closing_balance: number` | ⏳ Pending |

**Summary Validation Points:**
- ✅ Opening Balance = Sum of all receipts/payments before filter start date
- ✅ Total Income = Sum of receipts in filter period
- ✅ Total Expense = Sum of payments in filter period  
- ✅ Closing Balance = Opening Balance + Total Income - Total Expense
- ✅ Currency formatting (Vietnamese format)

## Test Execution Instructions

### Manual Testing Steps:
1. **Open Test Suite**: Navigate to http://yukimart.local/tests/payment-filter-test.html
2. **Run All Tests**: Click "Run All Tests" button
3. **Review Results**: Check each test category for pass/fail status
4. **Manual Verification**: 
   - Login to admin panel: http://yukimart.local/admin/payments
   - Test each filter manually
   - Verify pagination works correctly
   - Check summary cards update with filters

### Automated Testing:
- All tests can be run automatically via the test suite
- Results are logged in real-time
- Failed tests show detailed error messages
- Summary statistics are updated automatically

## Success Criteria

### ✅ All Tests Must Pass:
- [ ] Pagination works correctly (page navigation, per_page options)
- [ ] Time filters return correct date ranges
- [ ] Payment type filters return correct types
- [ ] Payment method filters return correct methods
- [ ] Status filters return correct statuses
- [ ] Branch shop filters return correct shops
- [ ] Creator filters return correct creators
- [ ] Staff filters return correct staff
- [ ] Combined filters work together
- [ ] Search functionality works
- [ ] Summary calculations are accurate

### ✅ Performance Requirements:
- [ ] Each filter response < 2 seconds
- [ ] Pagination response < 1 second
- [ ] Summary calculation < 3 seconds
- [ ] No JavaScript errors in console
- [ ] No 404 or 500 errors

### ✅ UI/UX Requirements:
- [ ] Loading states shown during requests
- [ ] Error messages displayed clearly
- [ ] Filter states persist during pagination
- [ ] Summary cards update automatically
- [ ] Responsive design works on mobile

## Bug Report Template

```
**Bug ID**: [Unique identifier]
**Test Case**: [Test case that failed]
**Expected Result**: [What should happen]
**Actual Result**: [What actually happened]
**Steps to Reproduce**: 
1. [Step 1]
2. [Step 2]
3. [Step 3]
**Environment**: [Browser, OS, etc.]
**Severity**: [Critical/High/Medium/Low]
**Screenshots**: [If applicable]
```

## Test Completion Report

**Date**: ___________
**Tester**: ___________
**Total Tests**: ___________
**Passed**: ___________
**Failed**: ___________
**Pass Rate**: ___________%

**Critical Issues Found**: ___________
**Recommendations**: ___________

---

**Note**: This checklist should be updated as new features are added or requirements change.
