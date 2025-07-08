# 🔧 Debug Column Mapping Issue

## 🚨 Lỗi: "Missing required field mappings: Tên sản phẩm, SKU, Giá khuyến mãi"

### ✅ Đã thêm debug code:

1. **loadAvailableFields** - Debug API response
2. **validateColumnMapping** - Debug validation logic  
3. **autoMapColumns** - Debug auto-mapping process
4. **setDefaultFields** - Fallback fields nếu API fail

## 🔍 Debug Steps:

### 1. **Upload file và check console:**
```javascript
// Expected logs:
Loading available fields...
Fields response: {success: true, data: {...}}
Available fields loaded: X fields

// Or fallback:
Setting default fields as fallback
Default fields set: 10 fields
```

### 2. **Chuyển sang step 2 và check auto-mapping:**
```javascript
// Expected logs:
Auto-mapping columns: {headers: [...], availableFields: [...]}
Mapping header "Product Name" (normalized: "product_name")
  Checking field "product_name" (tên sản phẩm): {labelMatch: true, keyMatch: true, customMatch: false}
  ✅ Mapped "Product Name" → "product_name"
Final column mapping: {0: "product_name", 1: "sku", ...}
```

### 3. **Click "Tiếp" và check validation:**
```javascript
// Expected logs:
Validating column mapping: {columnMapping: {...}, availableFields: {...}}
Required fields: ["product_name", "sku", "sale_price"]
Mapped fields: ["product_name", "sku", "sale_price"]
Column mapping validation passed
```

## 🛠️ Possible Issues & Solutions:

### **Issue 1: API endpoint không hoạt động**
**Check console for:**
```
HTTP 404: Not Found
HTTP 500: Internal Server Error
```

**Solution:** 
- Check route: `/admin/products/import/fields`
- Check controller method: `getFields()`
- Check service method: `getAvailableFields()`

### **Issue 2: Translation keys missing**
**Check console for:**
```
Fields response: {success: true, data: {product_name: {label: "product.product_name"}}}
```

**Solution:** Ensure translation keys exist in `resources/lang/vi/product.php`

### **Issue 3: Auto-mapping không work**
**Check console for:**
```
❌ No mapping found for "Product Name"
❌ No mapping found for "SKU"
```

**Solution:** Headers trong Excel không match với expected patterns

### **Issue 4: Manual mapping không save**
**Check console for:**
```
Column mapping changed: {columnIndex: 0, fieldKey: "product_name"}
Updated column mapping: {0: "product_name"}
```

**Solution:** Event handlers không được bind đúng

## 🎯 Manual Tests:

### **Test 1: Check API endpoint**
```javascript
// Run in console:
fetch('/admin/products/import/fields')
  .then(r => r.json())
  .then(d => console.log('API Response:', d));
```

### **Test 2: Check available fields**
```javascript
// Run in console after upload:
console.log('Available fields:', window.productImport.availableFields);
console.log('Required fields:', Object.keys(window.productImport.availableFields).filter(k => window.productImport.availableFields[k].required));
```

### **Test 3: Manual mapping**
```javascript
// Run in console on step 2:
window.productImport.columnMapping = {
  0: 'product_name',
  1: 'sku', 
  2: 'sale_price'
};
console.log('Manual mapping set:', window.productImport.columnMapping);
```

### **Test 4: Force validation**
```javascript
// Run in console:
const isValid = window.productImport.validateColumnMapping();
console.log('Validation result:', isValid);
```

## 🚀 Quick Fixes:

### **Fix 1: Force set required mappings**
```javascript
// Add to setupColumnMapping():
// Auto-set first 3 columns to required fields
this.columnMapping = {
  0: 'product_name',
  1: 'sku',
  2: 'sale_price'
};
```

### **Fix 2: Skip validation temporarily**
```javascript
// Modify validateCurrentStep():
case 1: // Column mapping step
  return true; // Skip validation temporarily
```

### **Fix 3: Manual field selection**
```html
<!-- User manually selects fields in dropdowns -->
<select data-column-index="0">
  <option value="product_name">Tên sản phẩm (Required)</option>
</select>
```

## 📋 Expected Excel Headers:

### **Vietnamese Headers:**
- Tên sản phẩm → product_name
- SKU → sku  
- Giá bán → sale_price
- Mô tả → product_description
- Mã vạch → barcode

### **English Headers:**
- Product Name → product_name
- SKU → sku
- Sale Price → sale_price  
- Description → product_description
- Barcode → barcode

## 🔧 Debug Commands:

### **Check current state:**
```javascript
console.log('File data:', window.productImport.fileData);
console.log('Available fields:', window.productImport.availableFields);
console.log('Column mapping:', window.productImport.columnMapping);
```

### **Force API reload:**
```javascript
window.productImport.loadAvailableFields().then(() => {
  console.log('Fields reloaded:', window.productImport.availableFields);
});
```

### **Test validation:**
```javascript
const validation = window.productImport.validateColumnMapping();
console.log('Validation passed:', validation);
```

**Hãy upload file, chuyển sang step 2 và check console logs để xem vấn đề cụ thể!** 🔍
