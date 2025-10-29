# 🔄 YukiMart API Auto-Sync Guide

Hệ thống tự động phát hiện và đồng bộ API endpoints mới với Postman collection và documentation.

## 🚀 **QUICK START**

### **1. Setup Auto-Sync**
```bash
# Interactive setup
php artisan api:setup-auto-sync

# Enable auto-sync
php artisan api:setup-auto-sync --enable

# Check status
php artisan api:setup-auto-sync --status
```

### **2. Manual Sync**
```bash
# Full sync (force update)
php artisan api:auto-sync --force

# Postman only
php artisan api:auto-sync --postman

# Documentation only
php artisan api:auto-sync --docs

# Watch mode (continuous monitoring)
php artisan api:auto-sync --watch
```

### **3. Check Results**
```bash
# View sync logs
tail -f storage/logs/api-auto-sync.log

# Check Postman collection
curl -X GET "https://api.getpostman.com/collections/{{collection_id}}" \
  -H "X-API-Key: {{api_key}}"
```

## ⚙️ **CONFIGURATION**

### **Environment Variables**
```env
# Auto-sync settings
API_AUTO_SYNC_ENABLED=true
API_SYNC_POSTMAN=true
API_SYNC_DOCUMENTATION=true
API_SYNC_ON_ROUTE_CHANGES=true
API_SYNC_SCHEDULED=true

# Postman settings
POSTMAN_AUTO_SYNC=true
POSTMAN_API_KEY=your_api_key
POSTMAN_COLLECTION_ID=your_collection_id
POSTMAN_WORKSPACE_ID=your_workspace_id

# Performance settings
API_CHECK_INTERVAL=300
API_BACKGROUND_SYNC=true
API_CACHE_DURATION=3600
```

### **Config File: `config/api-auto-sync.php`**
```php
return [
    'enabled' => env('API_AUTO_SYNC_ENABLED', true),
    
    'sync_triggers' => [
        'route_changes' => true,
        'controller_changes' => false,
        'scheduled' => true,
    ],
    
    'sync_targets' => [
        'postman' => true,
        'documentation' => true,
        'openapi' => true,
    ],
    
    // ... more configuration
];
```

## 🔍 **HOW IT WORKS**

### **1. Route Discovery**
- Tự động scan tất cả routes `api/v1/*`
- Phân tích controller methods và documentation
- Tạo examples và descriptions tự động
- Nhóm routes theo modules (auth, dashboard, products, etc.)

### **2. Change Detection**
- Generate hash từ route signatures
- So sánh với hash trước đó
- Trigger sync khi có thay đổi
- Cache kết quả để tối ưu performance

### **3. Auto-Sync Process**
```
Route Changes Detected
         ↓
Generate New Collection
         ↓
Update Postman Collection
         ↓
Generate Documentation
         ↓
Update Cache & Logs
```

## 📊 **FEATURES**

### **✅ Auto-Discovery**
- **Route Detection**: Tự động phát hiện routes mới
- **Method Analysis**: Phân tích HTTP methods và parameters
- **Documentation Extraction**: Lấy documentation từ PHPDoc
- **Example Generation**: Tạo examples tự động cho requests

### **✅ Smart Grouping**
- **By Module**: Nhóm theo auth, dashboard, products, etc.
- **By Controller**: Tổ chức theo controller classes
- **Custom Icons**: Icons cho từng nhóm (🔐, 📊, 📦, etc.)
- **Descriptions**: Mô tả tự động cho từng nhóm

### **✅ Postman Integration**
- **Collection Update**: Cập nhật collection tự động
- **Request Examples**: Thêm examples cho mỗi request
- **Environment Variables**: Setup variables tự động
- **Authentication**: Config auth headers tự động

### **✅ Documentation Generation**
- **Multiple Formats**: JSON, Markdown, HTML
- **OpenAPI Spec**: Generate OpenAPI 3.0 specification
- **Route Summary**: Tạo summary file cho developers
- **Change Logs**: Track changes theo thời gian

## 🕐 **SCHEDULING**

### **Automatic Scheduling**
```php
// app/Console/Kernel.php
$schedule->command('api:auto-sync')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

$schedule->command('api:auto-sync --force')
    ->daily()
    ->at('09:00');
```

### **Manual Cron Setup**
```bash
# Add to crontab
0 * * * * cd /path/to/project && php artisan api:auto-sync
0 9 * * * cd /path/to/project && php artisan api:auto-sync --force
```

## 🔧 **ADVANCED USAGE**

### **Custom Route Patterns**
```php
// config/api-auto-sync.php
'discovery' => [
    'route_patterns' => [
        'api/v1/*',
        'api/v2/*',  // Add v2 support
    ],
    'excluded_routes' => [
        'api/v1/docs',
        'api/v1/internal/*',
    ],
],
```

### **Custom Examples**
```php
// In your controller
/**
 * Get user profile
 * 
 * @example {
 *   "method": "GET",
 *   "headers": {"Authorization": "Bearer token"},
 *   "response": {"user": {"id": 1, "name": "John"}}
 * }
 */
public function profile() {
    // ...
}
```

### **Webhook Notifications**
```env
# Slack notifications
API_SYNC_SLACK_WEBHOOK=https://hooks.slack.com/...
API_NOTIFY_ON_CHANGES=true
API_NOTIFY_ON_ERRORS=true
```

## 📝 **MONITORING & DEBUGGING**

### **Log Files**
```bash
# Auto-sync logs
tail -f storage/logs/api-auto-sync.log

# Daily sync logs
tail -f storage/logs/api-auto-sync-daily.log

# Laravel logs
tail -f storage/logs/laravel.log
```

### **Debug Commands**
```bash
# Check route hash
php artisan tinker
>>> app('App\Services\AutoApiDiscoveryService')->discoverRoutes()

# Clear cache
php artisan cache:clear
php artisan route:clear

# Test discovery
php artisan api:auto-sync --dry-run
```

### **Status Monitoring**
```bash
# Check sync status
php artisan api:setup-auto-sync --status

# View scheduled tasks
php artisan schedule:list

# Test Postman connection
curl -X GET "https://api.getpostman.com/me" \
  -H "X-API-Key: {{your_api_key}}"
```

## 🚨 **TROUBLESHOOTING**

### **Common Issues**

#### **❌ "No routes detected"**
```bash
# Check route cache
php artisan route:clear
php artisan route:cache

# Verify route patterns
php artisan route:list --path=api/v1
```

#### **❌ "Postman sync failed"**
```bash
# Check API key
curl -X GET "https://api.getpostman.com/me" \
  -H "X-API-Key: {{your_api_key}}"

# Verify collection ID
curl -X GET "https://api.getpostman.com/collections/{{collection_id}}" \
  -H "X-API-Key: {{your_api_key}}"
```

#### **❌ "Auto-sync not running"**
```bash
# Check scheduler
php artisan schedule:run

# Check queue (if using)
php artisan queue:work

# Manual trigger
php artisan api:auto-sync --force
```

## 🎯 **BEST PRACTICES**

### **1. Development Workflow**
1. **Add new routes** trong controllers
2. **Add PHPDoc comments** với examples
3. **Test routes** manually
4. **Run auto-sync**: `php artisan api:auto-sync --force`
5. **Check Postman** collection
6. **Update Flutter app** với new endpoints

### **2. Documentation Standards**
```php
/**
 * Get dashboard statistics
 * 
 * Retrieve comprehensive dashboard statistics including
 * orders, revenue, products, and customer metrics.
 * 
 * @param Request $request
 * @return JsonResponse
 * 
 * @example GET /api/v1/dashboard/stats?period=month
 * @response {
 *   "status": "success",
 *   "data": {
 *     "total_orders": 150,
 *     "total_revenue": 1500000
 *   }
 * }
 */
```

### **3. Performance Optimization**
- **Cache duration**: Set appropriate cache duration
- **Background sync**: Enable background processing
- **Queue jobs**: Use queues for heavy operations
- **Rate limiting**: Respect Postman API limits

## 📚 **RELATED COMMANDS**

```bash
# Postman specific
php artisan postman:sync-v2 --force
php artisan postman:setup

# Documentation
php artisan api:generate-docs
php artisan api:openapi

# Route management
php artisan route:list
php artisan route:cache
php artisan route:clear
```

---

## 🎉 **SUCCESS INDICATORS**

✅ **Auto-sync is working when:**
- New routes automatically appear in Postman
- Documentation updates automatically
- Logs show successful sync operations
- No manual intervention needed
- Team always has latest API collection

🚀 **Your API development workflow is now fully automated!**
