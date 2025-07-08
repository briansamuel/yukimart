# 🔧 Debug Stepper Navigation Issue

## 🚨 Vấn đề: Không thể click "Tiếp" sau upload file

### ✅ Đã thêm debug code vào JavaScript:

1. **Console logs** trong các method quan trọng
2. **Step validation** với detailed logging  
3. **Button state** tracking
4. **Stepper initialization** verification

## 🔍 Cách debug:

### 1. **Mở Browser Developer Tools:**
- **F12** → **Console tab**
- **Upload file** và xem console logs

### 2. **Kiểm tra logs sau upload:**
```javascript
// Expected logs:
File uploaded successfully: {fileData: true, headers: X, rows: Y}
Next step enabled: {buttonFound: true, isDisabled: false}
```

### 3. **Test click nút "Tiếp":**
```javascript
// Expected logs:
Next button clicked
Moving from step: 0
Validating step: 0 {fileData: true, ...}
```

### 4. **Nếu vẫn không work, test manual:**
```javascript
// Run in console after upload:
window.productImport.stepper.goNext();
```

## 🛠️ Possible Issues & Solutions:

### **Issue 1: Stepper not initialized**
**Check console for:**
```
Stepper element not found: #kt_import_stepper
```

**Solution:** Verify HTML has correct ID

### **Issue 2: Step index mismatch**
**Check console for:**
```
Validating step: X {fileData: false}
```

**Solution:** Step index might be 1-based instead of 0-based

### **Issue 3: Button selector wrong**
**Check console for:**
```
Next step enabled: {buttonFound: false}
```

**Solution:** Button selector `[data-kt-stepper-action="next"]` might be wrong

### **Issue 4: KTStepper library issue**
**Check console for:**
```
KTStepper is not defined
```

**Solution:** Ensure Keen UI library is loaded

## 🔧 Manual Tests:

### **Test 1: Check button exists**
```javascript
// Run in console:
console.log('Next button:', $('[data-kt-stepper-action="next"]').length);
```

### **Test 2: Check stepper object**
```javascript
// Run in console:
console.log('Stepper:', window.productImport.stepper);
console.log('Current step:', window.productImport.stepper?.getCurrentStepIndex());
```

### **Test 3: Check file data**
```javascript
// Run in console after upload:
console.log('File data:', window.productImport.fileData);
```

### **Test 4: Force next step**
```javascript
// Run in console:
window.productImport.stepper.goNext();
```

## 🎯 Quick Fixes:

### **Fix 1: Force enable button**
```javascript
// Add to enableNextStep():
$('[data-kt-stepper-action="next"]').prop('disabled', false).show();
```

### **Fix 2: Alternative button selector**
```javascript
// Try different selectors:
$('.btn[data-kt-stepper-action="next"]')
$('button:contains("Tiếp")')
$('button:contains("Next")')
```

### **Fix 3: Manual step change**
```javascript
// Add after file upload:
setTimeout(() => {
    if (this.stepper) {
        this.stepper.goNext();
    }
}, 1000);
```

## 📋 Expected Console Output:

### **After page load:**
```
Stepper initialized: {element: div#kt_import_stepper, stepper: KTStepper, currentStep: 0}
```

### **After file upload:**
```
File uploaded successfully: {fileData: true, headers: 5, rows: 100}
Next step enabled: {buttonFound: true, isDisabled: false, hasDisabledClass: false}
```

### **After clicking "Tiếp":**
```
Next button clicked
Moving from step: 0
Validating step: 0 {fileData: true, columnMapping: 0, validationResults: false}
```

## 🚀 Alternative Solution:

Nếu stepper vẫn không work, có thể implement custom navigation:

```javascript
// Custom next step function
goToNextStep() {
    const steps = document.querySelectorAll('[data-kt-stepper-element="content"]');
    const currentStep = document.querySelector('[data-kt-stepper-element="content"].current');
    const currentIndex = Array.from(steps).indexOf(currentStep);
    
    if (currentIndex < steps.length - 1) {
        currentStep.classList.remove('current');
        steps[currentIndex + 1].classList.add('current');
    }
}
```

**Hãy upload file và check console logs để xem vấn đề cụ thể!** 🔍
