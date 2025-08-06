| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| SE01 | Kiểm tra quyền truy cập | Truy cập trang với tài khoản không có quyền | Hiển thị thông báo từ chối truy cập |
| SE02 | Kiểm tra XSS | Nhập script vào ô tìm kiếm | Script không được thực thi |
| SE03 | Kiểm tra CSRF | Thực hiện các thao tác POST | Yêu cầu token CSRF hợp lệ |