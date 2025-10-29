# Time Filter Test Cases - Return Orders Module

**Test Date:** 2025-10-27  
**Module:** Return Orders  
**Feature:** Time Filter  
**Tester:** Automated Test with Playwright

---

## 📋 TEST CASES

### **1. Hôm nay (Today)**

**Expected Behavior:**
- Filter: `time_filter=today`
- Date Range: `2025-10-27` to `2025-10-27`
- SQL: `WHERE DATE(created_at) = '2025-10-27'`

**Test Steps:**
1. Click "Hôm nay" radio button
2. Verify AJAX request contains `time_filter=today`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show only records created today

**Expected Results:**
- ✅ Only records with `created_at = 2025-10-27`

---

### **2. Hôm qua (Yesterday)**

**Expected Behavior:**
- Filter: `time_filter=yesterday`
- Date Range: `2025-10-26` to `2025-10-26`
- SQL: `WHERE DATE(created_at) = '2025-10-26'`

**Test Steps:**
1. Click "Hôm qua" radio button
2. Verify AJAX request contains `time_filter=yesterday`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show only records created yesterday

**Expected Results:**
- ✅ Only records with `created_at = 2025-10-26`

---

### **3. Tuần này (This Week)**

**Expected Behavior:**
- Filter: `time_filter=this_week`
- Date Range: `2025-10-20` (Monday) to `2025-10-26` (Sunday)
- SQL: `WHERE created_at BETWEEN '2025-10-20' AND '2025-10-26'`
- **Note:** Carbon's `startOfWeek()` defaults to Monday

**Test Steps:**
1. Click "Tuần này" radio button
2. Verify AJAX request contains `time_filter=this_week`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show only records created this week

**Expected Results:**
- ✅ Only records with `created_at` between 2025-10-20 and 2025-10-26

---

### **4. Tháng này (This Month)**

**Expected Behavior:**
- Filter: `time_filter=this_month`
- Date Range: `2025-10-01` to `2025-10-31`
- SQL: `WHERE MONTH(created_at) = 10 AND YEAR(created_at) = 2025`

**Test Steps:**
1. Click "Tháng này" radio button (default)
2. Verify AJAX request contains `time_filter=this_month`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show only records created this month

**Expected Results:**
- ✅ Only records with `created_at` in October 2025

---

### **5. Quý này (This Quarter) - BUG SUSPECTED**

**Expected Behavior:**
- Filter: `time_filter=this_quarter`
- Current Date: 2025-10-27 (Q4)
- Date Range: `2025-10-01` to `2025-12-31`
- SQL: `WHERE created_at BETWEEN '2025-10-01' AND '2025-12-31'`

**Quarter Breakdown:**
- Q1: January 1 - March 31
- Q2: April 1 - June 30
- Q3: July 1 - September 30
- Q4: October 1 - December 31

**Test Steps:**
1. Click "Quý này" radio button
2. Verify AJAX request contains `time_filter=this_quarter`
3. Verify NO `date_from` or `date_to` in request
4. Check backend logs for actual date range
5. Verify results show only records created in Q4 2025

**Expected Results:**
- ✅ Only records with `created_at` between 2025-10-01 and 2025-12-31

**Potential Bug:**
- ⚠️ User reported "Quý này" is incorrect
- Need to verify Carbon's `startOfQuarter()` and `endOfQuarter()` behavior

---

### **6. Năm này (This Year)**

**Expected Behavior:**
- Filter: `time_filter=this_year`
- Date Range: `2025-01-01` to `2025-12-31`
- SQL: `WHERE YEAR(created_at) = 2025`

**Test Steps:**
1. Click "Năm này" radio button
2. Verify AJAX request contains `time_filter=this_year`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show only records created this year

**Expected Results:**
- ✅ Only records with `created_at` in 2025

---

### **7. Tất cả (All Time)**

**Expected Behavior:**
- Filter: `time_filter=all` or `time_filter=all_time`
- Date Range: No restriction
- SQL: No date filter applied

**Test Steps:**
1. Click "Tất cả" radio button
2. Verify AJAX request contains `time_filter=all` or `time_filter=all_time`
3. Verify NO `date_from` or `date_to` in request
4. Verify results show ALL records regardless of date

**Expected Results:**
- ✅ All records shown (137 total in test data)

---

### **8. Tùy chỉnh (Custom)**

**Expected Behavior:**
- Filter: `time_filter=custom`
- Date Range: User-selected dates
- SQL: `WHERE DATE(created_at) >= 'date_from' AND DATE(created_at) <= 'date_to'`

**Test Steps:**
1. Click "Tùy chỉnh" radio button
2. Select date_from: `2025-10-20`
3. Select date_to: `2025-10-27`
4. Verify AJAX request contains:
   - `time_filter=custom`
   - `date_from=2025-10-20`
   - `date_to=2025-10-27`
5. Verify results show only records in date range

**Expected Results:**
- ✅ Only records with `created_at` between 2025-10-20 and 2025-10-27

---

## 🐛 KNOWN ISSUES

### **Issue #1: Quý này (This Quarter) Bug**

**Status:** 🔴 REPORTED BY USER

**Description:**
User reported that "Quý này" filter is showing incorrect results.

**Investigation Needed:**
1. Check Carbon's `startOfQuarter()` and `endOfQuarter()` methods
2. Verify current quarter calculation (Q4 2025)
3. Test with different dates in different quarters
4. Check if timezone affects quarter calculation

**Code Location:**
```php
// app/Traits/FilterableTrait.php - Line 178-183
case 'this_quarter':
    $query->whereBetween($dateColumn, [
        $now->copy()->startOfQuarter()->toDateString(),
        $now->copy()->endOfQuarter()->toDateString()
    ]);
    break;
```

**Possible Causes:**
1. Carbon's quarter calculation might be off
2. Timezone issues
3. Database date format issues
4. Query logic error

---

## 📊 TEST DATA

**Total Return Orders:** 137  
**Date Range:** 2025-04-04 to 2025-10-27 (90 days)

**Status Breakdown:**
- pending: 54
- approved: 42
- rejected: 21
- completed: 41

---

## ✅ TEST EXECUTION PLAN

### **Phase 1: Manual Testing**
1. Test each time filter manually
2. Record actual results vs expected results
3. Identify discrepancies

### **Phase 2: Automated Testing with Playwright**
1. Create Playwright test script
2. Test all time filters automatically
3. Capture screenshots for each filter
4. Generate test report

### **Phase 3: Bug Fixing**
1. Fix identified bugs (especially "Quý này")
2. Re-test all filters
3. Update documentation

---

## 📝 TEST RESULTS

**Status:** ⏳ PENDING

Will be updated after test execution.


