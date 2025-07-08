# Supplier List Testing Guide

## 🧪 How to Test Supplier List Page

### **1. Basic Functionality Test**

#### **Step 1: Navigate to Supplier List**
```
URL: /admin/supplier
Expected: Page loads with supplier table
```

#### **Step 2: Check DataTable Loading**
```
Expected Results:
✅ Table shows "Đang xử lý..." loading message
✅ Data loads from AJAX endpoint
✅ Pagination appears at bottom
✅ Search box appears at top
```

#### **Step 3: Verify Data Display**
```
Expected Columns:
✅ Checkbox (for bulk selection)
✅ Mã NCC (Supplier Code)
✅ Tên NCC (Supplier Name)  
✅ Công ty (Company)
✅ Điện thoại (Phone)
✅ Email
✅ Chi nhánh (Branch)
✅ Trạng thái (Status Badge)
✅ Thao tác (Action Buttons)
```

### **2. Search Functionality Test**

#### **Test Search by Name**
```
1. Type "Samsung" in search box
2. Press Enter or wait for auto-search
Expected: Only Samsung suppliers appear
```

#### **Test Search by Code**
```
1. Type "SUP001" in search box
Expected: Only supplier with code SUP001 appears
```

#### **Test Search by Company**
```
1. Type "Công ty" in search box
Expected: All suppliers with "Công ty" in company name appear
```

#### **Test Search by Phone**
```
1. Type "028" in search box
Expected: All suppliers with phone starting with 028 appear
```

#### **Test Search by Email**
```
1. Type "@samsung" in search box
Expected: All suppliers with Samsung email appear
```

### **3. Action Buttons Test**

#### **View Button Test**
```
1. Click eye icon on any supplier row
Expected: Navigate to supplier detail page
URL: /admin/supplier/detail/{id}
```

#### **Edit Button Test**
```
1. Click pencil icon on any supplier row
Expected: Navigate to supplier edit page
URL: /admin/supplier/edit/{id}
```

#### **Delete Button Test**
```
1. Click trash icon on any supplier row
Expected: SweetAlert confirmation dialog appears
2. Click "Có, xóa!" 
Expected: Supplier deleted, table refreshes
3. Click "Không, hủy"
Expected: Dialog closes, no action taken
```

### **4. Bulk Operations Test**

#### **Select Multiple Suppliers**
```
1. Check checkboxes for 2-3 suppliers
Expected: 
✅ Bulk action toolbar appears
✅ Shows "X Đã chọn" count
✅ "Xóa đã chọn" button appears
```

#### **Bulk Delete Test**
```
1. Select multiple suppliers
2. Click "Xóa đã chọn" button
Expected: Confirmation dialog appears
3. Click "Có, xóa!"
Expected: All selected suppliers deleted
```

### **5. Pagination Test**

#### **Navigate Pages**
```
1. If more than 10 suppliers exist:
   - Click "Tiếp" (Next) button
   - Click "Trước" (Previous) button
   - Click page numbers
Expected: Table loads different data sets
```

#### **Change Page Size**
```
1. Change "Hiển thị X nhà cung cấp" dropdown
Expected: Table shows different number of rows per page
```

### **6. Error Handling Test**

#### **Test with No Data**
```
1. Search for non-existent supplier "XXXXXX"
Expected: "Không tìm thấy nhà cung cấp nào" message
```

#### **Test Network Error**
```
1. Disconnect internet
2. Try to delete a supplier
Expected: Error message appears
```

### **7. AJAX Endpoint Test**

#### **Direct API Test**
```javascript
// Test in browser console
$.get('/admin/supplier/ajax', {
    draw: 1,
    start: 0,
    length: 10,
    search: { value: '' }
}, function(data) {
    console.log('Response:', data);
    console.log('Total records:', data.recordsTotal);
    console.log('Data count:', data.data.length);
});
```

#### **Expected Response Format**
```json
{
    "draw": 1,
    "recordsTotal": 25,
    "recordsFiltered": 25,
    "data": [
        {
            "id": 1,
            "code": "SUP001",
            "name": "Samsung Electronics Vietnam",
            "company": "Công ty Điện tử Samsung Việt Nam",
            "phone": "028-3825-1234",
            "email": "contact@samsung.vn",
            "branch": {
                "name": "Chi nhánh chính"
            },
            "status_badge": "<span class=\"badge badge-success\">Hoạt động</span>",
            "action": "<div class=\"d-flex justify-content-end flex-shrink-0\">...</div>"
        }
    ]
}
```

### **8. Database Verification**

#### **Check Suppliers Exist**
```sql
SELECT COUNT(*) FROM suppliers;
-- Should return > 0

SELECT id, code, name, company, status FROM suppliers LIMIT 5;
-- Should show sample data
```

#### **Check Branch Relationships**
```sql
SELECT s.name as supplier, b.name as branch 
FROM suppliers s 
LEFT JOIN branches b ON s.branch_id = b.id 
LIMIT 5;
-- Should show supplier-branch relationships
```

### **9. Performance Test**

#### **Load Time Test**
```
1. Open browser Developer Tools
2. Go to Network tab
3. Navigate to /admin/supplier
4. Check AJAX request to /admin/supplier/ajax

Expected Performance:
✅ Page load: < 2 seconds
✅ AJAX response: < 1 second
✅ Table render: < 500ms
```

#### **Large Dataset Test**
```
1. If you have 100+ suppliers:
   - Test pagination performance
   - Test search performance
   - Test bulk operations

Expected: No significant slowdown
```

### **10. Mobile Responsiveness Test**

#### **Mobile View Test**
```
1. Open browser Developer Tools
2. Switch to mobile view (iPhone/Android)
3. Navigate to supplier list

Expected:
✅ Table is horizontally scrollable
✅ Action buttons are accessible
✅ Search box works properly
✅ Pagination works on mobile
```

### **11. Browser Compatibility Test**

#### **Test in Different Browsers**
```
✅ Chrome (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Edge (latest)

Expected: Consistent behavior across all browsers
```

### **12. Common Issues & Solutions**

#### **Issue: Table doesn't load data**
```
Solutions:
1. Check browser console for JavaScript errors
2. Verify AJAX endpoint returns data: /admin/supplier/ajax
3. Check if suppliers exist in database
4. Verify route permissions
```

#### **Issue: Search doesn't work**
```
Solutions:
1. Check search input has correct data attribute
2. Verify search handler is attached
3. Check AJAX request includes search parameter
```

#### **Issue: Delete doesn't work**
```
Solutions:
1. Check CSRF token is included in request
2. Verify delete route uses DELETE method
3. Check supplier delete permissions
4. Verify SweetAlert2 is loaded
```

#### **Issue: Action buttons don't appear**
```
Solutions:
1. Check action column data is returned from server
2. Verify HTML is properly escaped
3. Check button event handlers are attached
```

### **13. Success Criteria**

#### **✅ All Tests Pass When:**
- [x] Table loads with supplier data
- [x] Search works across all fields
- [x] Pagination works correctly
- [x] Action buttons (View/Edit/Delete) work
- [x] Bulk operations work
- [x] Error handling works properly
- [x] Mobile responsive design works
- [x] Performance is acceptable
- [x] No JavaScript console errors
- [x] Vietnamese localization is correct

### **14. Troubleshooting Commands**

#### **Check Routes**
```bash
php artisan route:list | grep supplier
```

#### **Check Database**
```bash
php artisan tinker
>>> App\Models\Supplier::count()
>>> App\Models\Supplier::with('branch')->first()
```

#### **Clear Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### **Check Logs**
```bash
tail -f storage/logs/laravel.log
```

## 🎯 Final Verification

After completing all tests, the supplier list page should be fully functional with:

1. ✅ **Data Loading**: Suppliers load from database via AJAX
2. ✅ **Search**: Real-time search across multiple fields
3. ✅ **Actions**: View, Edit, Delete operations work
4. ✅ **Bulk Operations**: Multiple supplier selection and deletion
5. ✅ **Pagination**: Navigate through large datasets
6. ✅ **Responsive**: Works on all device sizes
7. ✅ **Localization**: All text in Vietnamese
8. ✅ **Error Handling**: Graceful error messages
9. ✅ **Performance**: Fast loading and smooth interactions

If all tests pass, the supplier list is ready for production use! 🎉
