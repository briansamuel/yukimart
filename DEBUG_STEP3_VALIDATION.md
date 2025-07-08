# 🔧 Debug Step 3 Validation Issues

## ❌ **Vấn đề**: 
1. Ở bước 3 validate dữ liệu báo lỗi không nhận được các cột
2. Nút "Trước" không hoạt động

## ✅ **Đã sửa:**

### **1. Thêm debug logging cho validateImportData:**
```javascript
async validateImportData() {
  console.log('validateImportData called', {
    columnMapping: this.columnMapping,
    mappingKeys: Object.keys(this.columnMapping),
    mappingValues: Object.values(this.columnMapping)
  });

  // Check if we have any column mapping
  if (!this.columnMapping || Object.keys(this.columnMapping).length === 0) {
    console.warn('No column mapping available, cannot validate');
    this.showError('Vui lòng quay lại bước 2 để map các cột trước khi validate.');
    return;
  }
}
```

### **2. Thêm debug logging cho step changes:**
```javascript
async handleStepperChanged(stepper) {
  const currentStepIndex = stepper.getCurrentStepIndex();

  console.log('Step changed to:', currentStepIndex, {
    columnMapping: this.columnMapping,
    mappedFieldsCount: Object.keys(this.columnMapping).length
  });

  switch (currentStepIndex) {
    case 2: // Step 3: Validation & Import
      // Check if we have column mapping before validating
      if (Object.keys(this.columnMapping).length === 0) {
        console.warn('No column mapping found when entering step 3');
        this.showError('Vui lòng quay lại bước 2 để map các cột trước khi validate.');
        return;
      }
      this.validateImportData();
      break;
  }
}
```

### **3. Sửa nút "Trước" (Previous button):**
```javascript
// Previous step button
$(document).on('click', '[data-kt-stepper-action="previous"]', () => {
  console.log('Previous button clicked');
  
  if (this.stepper) {
    this.stepper.goPrevious();
  } else {
    // Fallback navigation if stepper not working
    console.log('Stepper not available, using fallback navigation');
    this.goToPreviousStepManual();
  }
});
```

### **4. Thêm method goToPreviousStepManual:**
```javascript
goToPreviousStepManual() {
  const steps = document.querySelectorAll('[data-kt-stepper-element="content"]');
  const navItems = document.querySelectorAll('[data-kt-stepper-element="nav"]');

  let currentStepIndex = -1;

  // Find current step
  steps.forEach((step, index) => {
    if (step.classList.contains('current')) {
      currentStepIndex = index;
    }
  });

  // Move to previous step
  if (currentStepIndex > 0) {
    // Remove current class from current step
    steps[currentStepIndex].classList.remove('current');
    navItems[currentStepIndex].classList.remove('current');

    // Add current class to previous step
    const prevStepIndex = currentStepIndex - 1;
    steps[prevStepIndex].classList.add('current');
    navItems[prevStepIndex].classList.add('current');

    // Trigger step changed event
    this.handleStepperChanged({ getCurrentStepIndex: () => prevStepIndex });
  }
}
```

### **5. Thêm validation trong ProductImportService:**
```php
public function validateImportData(string $filePath, string $fileExtension, array $columnMapping): array
{
  Log::info('validateImportData called', [
    'filePath' => $filePath,
    'fileExtension' => $fileExtension,
    'columnMapping' => $columnMapping,
    'columnMappingCount' => count($columnMapping)
  ]);

  if (empty($columnMapping)) {
    Log::warning('Column mapping is empty');
    return [
      'total_rows' => 0,
      'valid_rows' => 0,
      'invalid_rows' => 0,
      'errors' => ['Không có cột nào được map. Vui lòng quay lại bước 2 để map các cột.'],
      'warnings' => [],
    ];
  }
  
  // ... rest of validation
}
```

### **6. Thêm debug buttons trong HTML:**
```html
<div class="alert alert-info mb-5">
  <h6>Debug Info:</h6>
  <button onclick="console.log('Column Mapping:', window.productImport.columnMapping)">
    Log Column Mapping
  </button>
  <button onclick="window.productImport.validateImportData()">
    Test Validate
  </button>
  <button onclick="window.productImport.stepper.goPrevious()">
    Test Previous
  </button>
</div>
```

## 🧪 **Test Steps:**

### **Step 1: Check Column Mapping**
1. Upload file và map các cột ở step 2
2. Click "Log Column Mapping" button
3. Check console output:
   ```javascript
   Column Mapping: {0: "product_name", 1: "sku", 2: "sale_price"}
   ```

### **Step 2: Test Step Navigation**
1. Go to step 3
2. Check console logs:
   ```
   Step changed to: 2 {
     columnMapping: {0: "product_name", 1: "sku", 2: "sale_price"},
     mappedFieldsCount: 3
   }
   ```

### **Step 3: Test Validation**
1. Click "Test Validate" button
2. Check console logs:
   ```
   validateImportData called {
     columnMapping: {...},
     mappingKeys: ["0", "1", "2"],
     mappingValues: ["product_name", "sku", "sale_price"]
   }
   ```

### **Step 4: Test Previous Button**
1. Click "Test Previous" button
2. Should navigate back to step 2
3. Check console: `Previous button clicked`

## 🔍 **Possible Issues:**

### **Issue 1: Column Mapping Lost**
**Symptoms**: `columnMapping` is empty in step 3
**Causes**:
- Column mapping not saved properly in step 2
- JavaScript object reset between steps
- Event handlers not working

**Debug**:
```javascript
// Check if mapping is saved when changing in step 2
window.productImport.columnMapping
```

### **Issue 2: API Not Receiving Data**
**Symptoms**: Server logs show empty column_mapping
**Causes**:
- JSON serialization issue
- Request body not sent properly
- CSRF token issue

**Debug**:
```javascript
// Check request payload
console.log('Sending validation request:', requestData);
```

### **Issue 3: Stepper Navigation Issue**
**Symptoms**: Previous button doesn't work
**Causes**:
- Stepper instance not initialized
- Event handler not attached
- CSS/HTML structure issue

**Debug**:
```javascript
// Check stepper instance
console.log('Stepper:', window.productImport.stepper);
```

## 🛠️ **Manual Debug Commands:**

### **In Browser Console:**
```javascript
// Check current state
console.log('Current Step:', window.productImport.stepper?.getCurrentStepIndex());
console.log('Column Mapping:', window.productImport.columnMapping);
console.log('File Data:', window.productImport.fileData);

// Test validation manually
window.productImport.validateImportData();

// Test navigation
window.productImport.stepper.goPrevious();
window.productImport.stepper.goNext();

// Force column mapping
window.productImport.columnMapping = {0: "product_name", 1: "sku", 2: "sale_price"};
```

### **Check Server Logs:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log | grep validateImportData

# Should see:
# validateImportData called {"filePath":"...","columnMapping":{...}}
```

## ✅ **Expected Results:**

### **1. Console Logs:**
```
Step changed to: 2 {columnMapping: {...}, mappedFieldsCount: 3}
validateImportData called {columnMapping: {...}, mappingKeys: [...], mappingValues: [...]}
Sending validation request: {column_mapping: {...}}
Validation response: {success: true, data: {...}}
```

### **2. Server Logs:**
```
validateImportData called {"filePath":"temp/...","columnMapping":{"0":"product_name","1":"sku","2":"sale_price"}}
```

### **3. UI Behavior:**
- Previous button navigates back to step 2
- Validation shows results (valid/invalid rows)
- No errors about missing columns

## 🎯 **Next Steps:**

1. **Use debug buttons** to identify exact issue
2. **Check console logs** for column mapping state
3. **Check server logs** for API requests
4. **Test navigation** with debug buttons
5. **Remove debug buttons** after fixing

**🔧 Debug tools added to identify and fix the column mapping and navigation issues!**
