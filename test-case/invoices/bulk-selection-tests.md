# Bulk Selection Tests - Invoice Module

## 📋 Test Overview
Test cases for bulk selection functionality including individual checkboxes, select-all checkbox, and bulk actions visibility.

**Priority**: P1 (High) - Recently fixed critical functionality
**Test URL**: http://yukimart.local/admin/invoices
**Prerequisites**: Login as yukimart@gmail.com / 123456

---

## 🧪 Test Cases

### **TC-BS-001: Individual Row Checkbox Selection**
**Objective**: Verify individual row checkboxes work correctly

**Steps**:
1. Navigate to invoice listing page
2. Wait for data to load completely
3. Click on the first row checkbox
4. Verify checkbox is checked
5. Verify bulk actions button appears
6. Verify count shows "1 hóa đơn"
7. Click on a second row checkbox
8. Verify both checkboxes are checked
9. Verify count updates to "2 hóa đơn"

**Expected Results**:
- ✅ Individual checkboxes respond to clicks
- ✅ Bulk actions button appears when items selected
- ✅ Count updates correctly
- ✅ Multiple selections work properly

**Status**: ✅ PASS
**Notes**: Fixed - event delegation now works for dynamic content

---

### **TC-BS-002: Select All Checkbox Functionality**
**Objective**: Verify select-all checkbox works correctly

**Steps**:
1. Navigate to invoice listing page
2. Wait for data to load completely
3. Click on the select-all checkbox in header
4. Verify all visible row checkboxes are checked
5. Verify bulk actions button shows "10 hóa đơn" (or current page size)
6. Click select-all checkbox again to uncheck
7. Verify all checkboxes are unchecked
8. Verify bulk actions button disappears

**Expected Results**:
- ✅ Select-all checkbox checks all visible rows
- ✅ Select-all checkbox unchecks all rows
- ✅ Bulk actions visibility updates correctly
- ✅ Count reflects total selected items

**Status**: ✅ PASS
**Notes**: Working correctly with proper event handling

---

### **TC-BS-003: Mixed State Behavior**
**Objective**: Verify select-all checkbox shows mixed state correctly

**Steps**:
1. Navigate to invoice listing page
2. Wait for data to load completely
3. Click on 2-3 individual row checkboxes (not all)
4. Observe select-all checkbox state
5. Verify it shows mixed/indeterminate state
6. Click select-all checkbox once
7. Verify all rows become checked
8. Click select-all checkbox again
9. Verify all rows become unchecked

**Expected Results**:
- ✅ Select-all shows mixed state when some rows selected
- ✅ First click on mixed state selects all
- ✅ Second click deselects all
- ✅ Visual indication of mixed state is clear

**Status**: ✅ PASS
**Notes**: Mixed state working as expected

---

### **TC-BS-004: Bulk Actions Button Visibility**
**Objective**: Verify bulk actions button appears/disappears correctly

**Steps**:
1. Navigate to invoice listing page
2. Verify bulk actions button is not visible initially
3. Select one row checkbox
4. Verify bulk actions button appears
5. Verify button text shows correct count
6. Select more rows
7. Verify count updates in button text
8. Uncheck all rows
9. Verify bulk actions button disappears

**Expected Results**:
- ✅ Button hidden when no items selected
- ✅ Button appears when items selected
- ✅ Count updates dynamically
- ✅ Button disappears when all items deselected

**Status**: ✅ PASS
**Notes**: Visibility logic working correctly

---

### **TC-BS-005: Pagination and Selection State**
**Objective**: Verify selection state behavior across pagination

**Steps**:
1. Navigate to invoice listing page
2. Select 2-3 rows on page 1
3. Navigate to page 2
4. Verify no rows are selected on page 2
5. Select 1-2 rows on page 2
6. Navigate back to page 1
7. Verify previous selections are cleared
8. Verify bulk actions reflect current page only

**Expected Results**:
- ✅ Selection state resets when changing pages
- ✅ Bulk actions only apply to current page
- ✅ No cross-page selection persistence
- ✅ Clear visual indication of current state

**Status**: ⏳ PENDING
**Notes**: Need to test pagination behavior

---

### **TC-BS-006: Filter and Selection State**
**Objective**: Verify selection state when applying filters

**Steps**:
1. Navigate to invoice listing page
2. Select 2-3 rows
3. Apply a filter (e.g., status filter)
4. Verify selection state is cleared
5. Verify bulk actions button disappears
6. Select rows in filtered view
7. Clear filter
8. Verify selection state is cleared again

**Expected Results**:
- ✅ Selections cleared when filters applied
- ✅ Selections cleared when filters removed
- ✅ Bulk actions reset appropriately
- ✅ No stale selection state

**Status**: ⏳ PENDING
**Notes**: Need to test filter interaction

---

### **TC-BS-007: Performance with Large Selections**
**Objective**: Verify performance when selecting many items

**Steps**:
1. Navigate to invoice listing page
2. Use select-all to select all visible items (10)
3. Measure response time
4. Verify UI remains responsive
5. Perform bulk action if available
6. Verify operation completes successfully

**Expected Results**:
- ✅ Select-all completes in <500ms
- ✅ UI remains responsive during selection
- ✅ Bulk operations work with full selection
- ✅ No performance degradation

**Status**: ⏳ PENDING
**Notes**: Need to test performance metrics

---

### **TC-BS-008: Keyboard Accessibility**
**Objective**: Verify keyboard navigation works for checkboxes

**Steps**:
1. Navigate to invoice listing page
2. Use Tab key to navigate to checkboxes
3. Use Space key to toggle checkboxes
4. Verify keyboard navigation works for select-all
5. Verify bulk actions are keyboard accessible

**Expected Results**:
- ✅ Checkboxes are keyboard accessible
- ✅ Space key toggles checkbox state
- ✅ Tab navigation works correctly
- ✅ Bulk actions accessible via keyboard

**Status**: ⏳ PENDING
**Notes**: Need to test accessibility features

---

## 📊 Test Summary

| Test Case | Status | Priority | Notes |
|-----------|--------|----------|-------|
| TC-BS-001 | ✅ PASS | P1 | Individual selection working |
| TC-BS-002 | ✅ PASS | P1 | Select-all working |
| TC-BS-003 | ✅ PASS | P1 | Mixed state working |
| TC-BS-004 | ✅ PASS | P1 | Button visibility working |
| TC-BS-005 | ⏳ PENDING | P2 | Pagination interaction |
| TC-BS-006 | ⏳ PENDING | P2 | Filter interaction |
| TC-BS-007 | ⏳ PENDING | P3 | Performance testing |
| TC-BS-008 | ⏳ PENDING | P3 | Accessibility testing |

## 🐛 Known Issues
- None currently identified

## 🔧 Technical Notes
- Fixed event delegation issue for dynamic content
- jQuery event handlers properly bound with namespacing
- Bulk actions visibility logic working correctly
- Mixed state implementation functioning as expected

## 🎯 Next Steps
1. Complete pending test cases
2. Test pagination interaction
3. Test filter interaction
4. Performance and accessibility testing
