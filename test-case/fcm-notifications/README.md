# FCM Notifications Test Cases

## 📁 Directory Structure

```
test-case/fcm-notifications/
├── README.md                           # This file
├── fcm-notification-test-report.md     # Comprehensive test report
└── playwright-test-scenarios/          # Playwright test scenarios (future)
```

## 🎯 Test Scope

This directory contains test cases and documentation for the **FCM (Firebase Cloud Messaging) Notification System** implemented in YukiMart.

### **Features Tested:**
- ✅ **Order Creation Notifications** - Real-time notifications when orders are created
- ✅ **Invoice Creation Notifications** - Real-time notifications when invoices are created  
- ✅ **FCM v1 API Integration** - Firebase Cloud Messaging v1 API
- ✅ **Event-Driven Architecture** - Laravel events and listeners
- ✅ **Job Queue Processing** - Background notification processing
- ✅ **Real Device Testing** - Actual Android device notification delivery

## 🧪 Test Environment

**Test Setup:**
- **Environment:** Docker container (php83)
- **URL:** http://yukimart.local/admin/quick-order
- **Test Tool:** Playwright with Chrome browser
- **Test User:** yukimart@gmail.com (Admin)
- **Test Device:** Android device with FCM token

**Prerequisites:**
- Firebase project configured
- Service account credentials
- FCM token registered
- Laravel queue worker running

## 📋 Test Cases

### **TC001: Order Creation Notification**
**Objective:** Verify FCM notification is sent when creating order from QuickOrder  
**Status:** ✅ PASSED  
**Result:** Notification sent successfully with correct content

### **TC002: Invoice Creation Notification**  
**Objective:** Verify FCM notification is sent when creating invoice from QuickOrder  
**Status:** ✅ PASSED  
**Result:** Notification sent successfully with correct content

### **TC003: FCM Token Management**
**Objective:** Verify FCM token registration and validation  
**Status:** ✅ PASSED  
**Result:** Token registered and validated successfully

### **TC004: Event System Integration**
**Objective:** Verify Laravel events trigger FCM notifications  
**Status:** ✅ PASSED  
**Result:** Events triggered correctly for both orders and invoices

## 🚀 How to Run Tests

### **Manual Testing with Playwright:**

1. **Start Docker Environment:**
```bash
docker exec -it php83 /bin/sh
```

2. **Navigate to QuickOrder:**
```
http://yukimart.local/admin/quick-order
```

3. **Test Order Creation:**
- Switch to "Đơn hàng 1" tab
- Add product (search "Dao")
- Click "THANH TOÁN"
- Verify notification in logs

4. **Test Invoice Creation:**
- Switch to "Hóa đơn 2" tab  
- Add product (search "Dao")
- Click "TẠO HÓA ĐƠN"
- Verify notification in logs

### **Check FCM Logs:**
```bash
docker exec -it php83 tail -f /var/www/html/yukimart/storage/logs/laravel.log | grep FCM
```

### **Verify Database Records:**
```sql
-- Check FCM tokens
SELECT * FROM fcm_tokens WHERE is_active = 1;

-- Check notifications
SELECT * FROM notifications WHERE type IN ('order', 'invoice') ORDER BY created_at DESC;

-- Check recent orders/invoices
SELECT * FROM orders ORDER BY created_at DESC LIMIT 5;
SELECT * FROM invoices ORDER BY created_at DESC LIMIT 5;
```

## 📊 Test Results Summary

| Component | Status | Success Rate | Notes |
|-----------|--------|--------------|-------|
| FCM v1 API | ✅ WORKING | 100% | All notifications delivered |
| Order Events | ✅ WORKING | 100% | Events triggered correctly |
| Invoice Events | ✅ WORKING | 100% | Events triggered correctly |
| Job Queue | ✅ WORKING | 100% | Background processing working |
| Database | ✅ WORKING | 100% | All records stored correctly |
| Real Device | ✅ WORKING | 100% | Notifications received on Android |

## 🔧 Troubleshooting

### **Common Issues:**

#### **Issue: No notifications received**
**Solution:**
1. Check FCM token is active: `SELECT * FROM fcm_tokens WHERE is_active = 1`
2. Verify queue worker is running: `php artisan queue:work`
3. Check Firebase service account credentials
4. Verify events are registered in EventServiceProvider

#### **Issue: Database errors**
**Solution:**
1. Check notification table has required columns (color, icon)
2. Verify foreign key constraints
3. Check user permissions

#### **Issue: FCM API errors**
**Solution:**
1. Verify Firebase service account JSON is valid
2. Check FCM token format (should be 142+ characters)
3. Verify project ID matches Firebase console

## 📝 Test Documentation

### **Main Report:**
- **[fcm-notification-test-report.md](fcm-notification-test-report.md)** - Comprehensive test report with detailed results

### **Key Metrics:**
- **Test Duration:** ~2 hours
- **Test Cases:** 4 scenarios
- **Success Rate:** 100%
- **Issues Found:** 4 (all fixed)
- **Production Readiness:** ✅ APPROVED

## 🔮 Future Test Scenarios

### **Planned Tests:**
1. **Order Status Change Notifications** - Test when order status changes to completed
2. **Multiple User Notifications** - Test notifications to multiple admin users
3. **High Volume Testing** - Test performance with many simultaneous notifications
4. **Error Handling** - Test behavior when FCM service is unavailable
5. **Cross-Platform Testing** - Test on iOS devices

### **Performance Tests:**
1. **Load Testing** - Test with 100+ concurrent notifications
2. **Stress Testing** - Test system limits
3. **Endurance Testing** - Test long-running notification processing

## 📞 Support

For questions about FCM notification testing:

1. **Check logs:** `storage/logs/laravel.log`
2. **Review test report:** `fcm-notification-test-report.md`
3. **Verify configuration:** Firebase console and service account
4. **Test manually:** Use QuickOrder interface

---

**Last Updated:** 2025-08-08  
**Test Status:** ✅ ALL TESTS PASSING  
**Production Status:** ✅ READY FOR DEPLOYMENT
