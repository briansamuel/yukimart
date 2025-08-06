# Time Filter Fix Verification Test

## Mục đích
Kiểm tra xem các bug về time filter đã được sửa chưa:
- Bug T01: Lọc theo "Hôm nay" 
- Bug T02: Lọc theo "Hôm qua"
- Bug T03: Lọc theo "Tuần này" 
- Bug T04: Lọc theo "Tuần trước"
- Bug T05: Lọc theo "7 ngày qua"
- Bug T12: Lọc theo "30 ngày qua"

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| TF01 | Kiểm tra hidden input được tạo | 1. Mở trang payments<br>2. Kiểm tra DOM có hidden input name="time_filter" | Hidden input tồn tại với value mặc định "this_month" |
| TF02 | Kiểm tra click "Hôm nay" | 1. Click trigger time filter<br>2. Click "Hôm nay"<br>3. Kiểm tra hidden input value | Hidden input value = "today" |
| TF03 | Kiểm tra AJAX request "Hôm nay" | 1. Click "Hôm nay"<br>2. Kiểm tra network request | Request có parameter time_filter=today |
| TF04 | Kiểm tra pagination info "Hôm nay" | 1. Click "Hôm nay"<br>2. Kiểm tra pagination info | Pagination info cập nhật đúng số kết quả |
| TF05 | Kiểm tra data "Hôm nay" | 1. Click "Hôm nay"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data ngày hôm nay (11/1/2025) |
| TF06 | Kiểm tra click "Hôm qua" | 1. Click "Hôm qua"<br>2. Kiểm tra hidden input và request | Hidden input = "yesterday", request đúng |
| TF07 | Kiểm tra data "Hôm qua" | 1. Click "Hôm qua"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data ngày hôm qua (10/1/2025) |
| TF08 | Kiểm tra click "Tuần này" | 1. Click "Tuần này"<br>2. Kiểm tra hidden input và request | Hidden input = "this_week", request đúng |
| TF09 | Kiểm tra data "Tuần này" | 1. Click "Tuần này"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data tuần này (6/1-12/1/2025) |
| TF10 | Kiểm tra click "Tuần trước" | 1. Click "Tuần trước"<br>2. Kiểm tra hidden input và request | Hidden input = "last_week", request đúng |
| TF11 | Kiểm tra data "Tuần trước" | 1. Click "Tuần trước"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data tuần trước (30/12/2024-5/1/2025) |
| TF12 | Kiểm tra click "7 ngày qua" | 1. Click "7 ngày qua"<br>2. Kiểm tra hidden input và request | Hidden input = "7_days", request đúng |
| TF13 | Kiểm tra data "7 ngày qua" | 1. Click "7 ngày qua"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data 7 ngày qua (5/1-11/1/2025) |
| TF14 | Kiểm tra click "30 ngày qua" | 1. Click "30 ngày qua"<br>2. Kiểm tra hidden input và request | Hidden input = "30_days", request đúng |
| TF15 | Kiểm tra data "30 ngày qua" | 1. Click "30 ngày qua"<br>2. Kiểm tra data hiển thị | Chỉ hiển thị data 30 ngày qua |

## Automated Test Script

Sử dụng script test tại: `public/tests/payment-time-filter-fix-test.js`

### Cách chạy:
1. Mở http://yukimart.local/admin/payments
2. Mở Developer Console (F12)
3. Load script: 
```javascript
// Copy và paste nội dung file payment-time-filter-fix-test.js
// Hoặc load từ file:
fetch('/tests/payment-time-filter-fix-test.js')
  .then(response => response.text())
  .then(script => eval(script));
```

### Expected Results:
- All tests should pass (✅)
- Hidden input should be created with correct values
- AJAX requests should include time_filter parameter
- Pagination info should update correctly
- Data should be filtered correctly

## Manual Verification Steps

### Step 1: Kiểm tra Backend Logic
```bash
# Check logs for time filter processing
tail -f storage/logs/laravel.log | grep "Time filter applied"
```

### Step 2: Kiểm tra Frontend Logic
1. Mở Network tab trong DevTools
2. Click các time filter options
3. Kiểm tra AJAX requests có parameter `time_filter` đúng không

### Step 3: Kiểm tra Data Accuracy
1. So sánh kết quả với database thực tế
2. Kiểm tra pagination info có khớp với số lượng records không

## Success Criteria

✅ **Fix thành công khi:**
1. Hidden input được tạo và cập nhật đúng
2. AJAX requests có parameter time_filter
3. Backend xử lý time_filter đúng logic
4. Pagination info cập nhật chính xác
5. Data hiển thị đúng khoảng thời gian được chọn
6. Tất cả time filter options hoạt động (không chỉ monthly filters)

❌ **Fix chưa thành công khi:**
1. Hidden input không được tạo hoặc không cập nhật
2. AJAX requests thiếu parameter time_filter
3. Backend không xử lý time_filter
4. Pagination info không cập nhật
5. Data hiển thị sai khoảng thời gian
6. Một số time filter options vẫn bị bug
