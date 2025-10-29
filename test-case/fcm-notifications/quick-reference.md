# FCM Notifications Quick Reference

## 🚀 Quick Test Commands

### **Test FCM Notification System:**
```bash
# 1. Start environment
docker exec -it php83 /bin/sh

# 2. Check queue worker
php artisan queue:work --daemon

# 3. Monitor FCM logs
tail -f storage/logs/laravel.log | grep -E "(FCM|notification)"

# 4. Test via browser
# Navigate to: http://yukimart.local/admin/quick-order
# Create order/invoice and check logs
```

### **Verify System Status:**
```bash
# Check FCM tokens
php artisan tinker --execute="echo \App\Models\FCMToken::where('is_active', true)->count() . ' active tokens';"

# Check recent notifications  
php artisan tinker --execute="echo \App\Models\Notification::whereIn('type', ['order', 'invoice'])->count() . ' notifications';"

# Test FCM service
php artisan tinker --execute="\App\Services\FCMService::testConnection();"
```

## 📱 Test Data

### **Test Device Token:**
```
c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g
```

### **Test User:**
```
Email: yukimart@gmail.com
Password: 123456
Role: Admin
User ID: 12
```

### **Test Product:**
```
Name: Dao gọt hoa quả đục lỗ Echo 25cm
SKU: 4991203155313
Price: 50,000 VNĐ
Product ID: 67
```

## 🔍 Debug Commands

### **Check FCM Configuration:**
```php
// Check service account
$serviceAccount = storage_path('app/firebase/service-account.json');
echo file_exists($serviceAccount) ? 'Service account exists' : 'Service account missing';

// Check FCM service
$fcm = app(\App\Services\FCMService::class);
echo $fcm->isConfigured() ? 'FCM configured' : 'FCM not configured';
```

### **Manual Notification Test:**
```php
// Send test notification
$user = \App\Models\User::find(12);
$notification = \App\Models\Notification::createWithFCM(
    $user,
    'test',
    'Test Notification',
    'This is a test message',
    ['test_data' => 'test_value'],
    ['priority' => 'high', 'color' => 'info', 'icon' => 'test']
);
```

### **Check Event Registration:**
```php
// Verify events are registered
$events = app('events')->getListeners(\App\Events\OrderCreated::class);
echo count($events) . ' listeners for OrderCreated';

$events = app('events')->getListeners(\App\Events\InvoiceCreated::class);  
echo count($events) . ' listeners for InvoiceCreated';
```

## 📊 Expected Results

### **Successful Order Notification:**
```json
{
  "order_id": 24,
  "order_code": "DH202508080002", 
  "fcm_status": "success_count: 1, failure_count: 0",
  "notification_id": "bae3ccc7-7fc4-489e-af29-a84cf1ba6995",
  "title": "🛒 Đơn hàng mới - DH202508080002",
  "message": "Có đơn hàng mới DH202508080002 từ khách hàng Khách lẻ với tổng tiền 50.000 VNĐ"
}
```

### **Successful Invoice Notification:**
```json
{
  "invoice_id": 1856,
  "invoice_code": "HD-1856",
  "fcm_status": "success_count: 1, failure_count: 0", 
  "notification_id": "5a35a0ba-ae3f-48ff-aae2-6d5698bd9c9d",
  "title": "💰 Hóa đơn mới - HD-1856",
  "message": "Hóa đơn mới HD-1856 của khách hàng Khách lẻ với tổng tiền 50.000 VNĐ"
}
```

### **Log Patterns to Look For:**
```
✅ SUCCESS:
[timestamp] FCM v1 notification sent {"tokens_count":1,"success_count":1,"failure_count":0}
[timestamp] FCM Job: Notification sent successfully
[timestamp] Order/Invoice notification sent

❌ ERRORS:
[timestamp] FCM error: [error message]
[timestamp] Failed to send notification: [error details]
[timestamp] Column 'color' cannot be null
```

## 🛠️ Troubleshooting

### **No Notifications Received:**
1. ✅ Check FCM token is active
2. ✅ Verify queue worker is running  
3. ✅ Check Firebase credentials
4. ✅ Verify events are triggered

### **Database Errors:**
1. ✅ Check notification table schema
2. ✅ Verify required columns exist
3. ✅ Check foreign key constraints

### **FCM API Errors:**
1. ✅ Verify service account JSON
2. ✅ Check token format
3. ✅ Verify project ID

## 📋 Test Checklist

### **Pre-Test Setup:**
- [ ] Docker environment running
- [ ] Database migrated and seeded
- [ ] Queue worker started
- [ ] Firebase credentials configured
- [ ] FCM token registered

### **Test Execution:**
- [ ] Navigate to QuickOrder
- [ ] Test order creation notification
- [ ] Test invoice creation notification
- [ ] Verify logs show success
- [ ] Check database records

### **Post-Test Verification:**
- [ ] FCM notifications delivered
- [ ] Database records created
- [ ] No errors in logs
- [ ] System performance acceptable

## 🔗 Related Files

### **Core Implementation:**
- `app/Events/OrderCreated.php`
- `app/Events/InvoiceCreated.php`
- `app/Listeners/SendOrderNotificationListener.php`
- `app/Listeners/SendInvoiceNotificationListener.php`
- `app/Services/FCMService.php`

### **Configuration:**
- `storage/app/firebase/service-account.json`
- `app/Providers/EventServiceProvider.php`
- `config/queue.php`

### **Test Documentation:**
- `test-case/fcm-notifications/fcm-notification-test-report.md`
- `test-case/fcm-notifications/README.md`

## 🎯 Success Criteria

### **System is working correctly when:**
1. ✅ Orders trigger FCM notifications
2. ✅ Invoices trigger FCM notifications  
3. ✅ Notifications contain correct content
4. ✅ FCM delivery success rate = 100%
5. ✅ No errors in application logs
6. ✅ Database records created properly

---

**Quick Test Status: ✅ ALL SYSTEMS OPERATIONAL**  
**Last Verified:** 2025-08-08 18:52:55
