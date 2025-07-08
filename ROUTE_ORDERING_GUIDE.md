# 🛣️ Route Ordering Guide

## 🚨 **Critical Rule: Specific Routes BEFORE Parameterized Routes**

Laravel matches routes in the order they are defined. More specific routes must come before more general ones.

---

## ❌ **WRONG Order (Causes Conflicts)**

```php
// BAD: Parameterized route first
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/attributes', [ProductController::class, 'getAttributes']); // NEVER REACHED!
```

**Problem:** `products/attributes` will be matched by `products/{id}` with `id = "attributes"`

---

## ✅ **CORRECT Order**

```php
// GOOD: Specific routes first
Route::get('/products/attributes', [ProductController::class, 'getAttributes']);
Route::get('/products/ajax/get-list', [ProductController::class, 'ajaxGetList']);
Route::get('/products/{id}', [ProductController::class, 'show']); // Last
```

---

## 📋 **Route Ordering Checklist**

### **1. Static/Literal Routes First**
```php
Route::get('/products/attributes', ...);
Route::get('/products/categories', ...);
Route::get('/products/export', ...);
Route::get('/products/import', ...);
```

### **2. AJAX/API Routes**
```php
Route::get('/products/ajax/get-list', ...);
Route::post('/products/ajax/search', ...);
```

### **3. Action Routes (specific paths)**
```php
Route::post('/products/bulk-delete', ...);
Route::post('/products/bulk-update', ...);
```

### **4. Parameterized Routes Last**
```php
Route::get('/products/{id}', ...);
Route::post('/products/{id}/duplicate', ...);
Route::get('/products/{id}/variants', ...);
```

---

## 🎯 **Current Fixed Order in admin.php**

```php
// ✅ CORRECT ORDER
Route::prefix('admin')->group(function () {
    
    // 1. Product Attribute Routes (specific paths)
    Route::get('/products/attributes', ...);
    Route::post('/products/attributes', ...);
    Route::get('/products/attributes/{attributeId}/values', ...);
    
    // 2. AJAX Routes (specific paths)
    Route::get('/products/ajax/get-list', ...);
    
    // 3. Parameterized Routes (general patterns)
    Route::get('/products/{id}', ...);
    Route::post('/products/{id}/duplicate', ...);
    Route::get('/products/{id}/variants', ...);
});
```

---

## 🔍 **How to Check for Conflicts**

### **Manual Check:**
1. Look for routes with same prefix
2. Check if specific paths come after `{parameter}` routes
3. Verify route order in `routes/admin.php`

### **Automated Check:**
```bash
php artisan route:check-conflicts --prefix=admin
```

---

## 🚨 **Common Conflict Patterns**

### **1. Resource vs Custom Routes**
```php
// ❌ WRONG
Route::resource('products', ProductController::class);
Route::get('/products/export', [ProductController::class, 'export']); // CONFLICT!

// ✅ CORRECT  
Route::get('/products/export', [ProductController::class, 'export']);
Route::resource('products', ProductController::class);
```

### **2. Nested Parameters**
```php
// ❌ WRONG
Route::get('/products/{id}/variants/{variantId}', ...);
Route::get('/products/{id}/variants/create', ...); // CONFLICT!

// ✅ CORRECT
Route::get('/products/{id}/variants/create', ...);
Route::get('/products/{id}/variants/{variantId}', ...);
```

### **3. API vs Web Routes**
```php
// ❌ WRONG
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/api/search', [ProductController::class, 'apiSearch']); // CONFLICT!

// ✅ CORRECT
Route::get('/products/api/search', [ProductController::class, 'apiSearch']);
Route::get('/products/{id}', [ProductController::class, 'show']);
```

---

## 📝 **Best Practices for New Routes**

### **1. Before Adding New Routes:**
- [ ] Check existing routes in the same prefix
- [ ] Identify if your route has parameters
- [ ] Find the correct position (specific before general)

### **2. Route Naming Convention:**
```php
// Use descriptive, specific paths
Route::get('/products/attributes', ...);        // ✅ Good
Route::get('/products/attrs', ...);             // ❌ Unclear
Route::get('/products/get-attributes', ...);    // ❌ Redundant
```

### **3. Group Related Routes:**
```php
Route::prefix('products')->group(function () {
    // Attribute management
    Route::get('/attributes', ...);
    Route::post('/attributes', ...);
    
    // Variant management  
    Route::get('/{id}/variants', ...);
    Route::post('/{id}/variants', ...);
});
```

---

## 🛠️ **Testing Route Order**

### **1. Test Specific Routes:**
```bash
curl -X GET "http://localhost/admin/products/attributes"
# Should hit getAttributes(), not show() with id="attributes"
```

### **2. Test Parameterized Routes:**
```bash
curl -X GET "http://localhost/admin/products/123"
# Should hit show() with id=123
```

### **3. Use Route Debugger:**
```bash
php artisan route:list --path=admin/products
```

---

## 🚀 **Quick Fix Checklist**

When you encounter route conflicts:

1. **Identify the conflict:**
   - [ ] Which routes are conflicting?
   - [ ] Which one has parameters?

2. **Reorder routes:**
   - [ ] Move specific routes up
   - [ ] Move parameterized routes down

3. **Test the fix:**
   - [ ] Test specific route works
   - [ ] Test parameterized route works
   - [ ] Run automated conflict check

4. **Document the change:**
   - [ ] Add comments explaining order
   - [ ] Update this guide if needed

---

## 💡 **Remember**

> **"Specific routes first, general routes last"**

This simple rule prevents 99% of route conflicts. When in doubt, put your new route as high as possible in the file, then move it down until it works correctly.

---

## 🔧 **Tools**

- **Check conflicts:** `php artisan route:check-conflicts`
- **List routes:** `php artisan route:list`
- **Test routes:** Use Postman/curl to verify routing

**Always test your routes after adding new ones!** 🧪
