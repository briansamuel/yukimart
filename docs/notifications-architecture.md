# Kiến trúc Hệ thống Thông báo (Notifications System)

## 1. Tổng quan Kiến trúc

### 1.1 Mục tiêu
- Tạo hệ thống thông báo real-time cho ứng dụng YukiMart
- Hỗ trợ nhiều loại thông báo: đơn hàng, hóa đơn, tồn kho, hệ thống
- Gửi thông báo qua nhiều kênh: web, email, SMS (tùy chọn)
- Quản lý trạng thái đọc/chưa đọc
- Phân quyền thông báo theo vai trò người dùng

### 1.2 Thành phần chính
1. **Database Layer**: Lưu trữ thông báo và cấu hình
2. **Service Layer**: Xử lý logic nghiệp vụ thông báo
3. **Event System**: Trigger thông báo từ các sự kiện hệ thống
4. **Real-time Layer**: WebSocket/Pusher cho thông báo real-time
5. **UI Components**: Giao diện hiển thị thông báo
6. **API Layer**: REST API cho CRUD thông báo

## 2. Thiết kế Database

### 2.1 Bảng `notifications`
```sql
- id (bigint, primary key)
- type (enum: order, invoice, inventory, system, user)
- title (varchar 255) - Tiêu đề thông báo
- message (text) - Nội dung thông báo
- data (json) - Dữ liệu bổ sung (ID đối tượng, links, etc.)
- notifiable_type (varchar) - Loại đối tượng nhận (User, Role, etc.)
- notifiable_id (bigint) - ID đối tượng nhận
- read_at (timestamp, nullable) - Thời gian đọc
- priority (enum: low, normal, high, urgent) - Độ ưu tiên
- channels (json) - Kênh gửi [web, email, sms]
- expires_at (timestamp, nullable) - Thời gian hết hạn
- created_at, updated_at
```

### 2.2 Bảng `notification_settings`
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- notification_type (varchar) - Loại thông báo
- channels (json) - Kênh nhận [web, email, sms]
- is_enabled (boolean) - Bật/tắt
- created_at, updated_at
```

### 2.3 Bảng `notification_templates`
```sql
- id (bigint, primary key)
- type (varchar) - Loại template
- channel (enum: web, email, sms)
- subject (varchar) - Tiêu đề
- content (text) - Nội dung template
- variables (json) - Biến có thể sử dụng
- is_active (boolean)
- created_at, updated_at
```

## 3. Service Layer Architecture

### 3.1 NotificationService
- `send()`: Gửi thông báo
- `markAsRead()`: Đánh dấu đã đọc
- `markAllAsRead()`: Đánh dấu tất cả đã đọc
- `getUnreadCount()`: Đếm thông báo chưa đọc
- `getUserNotifications()`: Lấy thông báo của user
- `deleteExpired()`: Xóa thông báo hết hạn

### 3.2 NotificationChannelService
- `WebChannel`: Thông báo trên web
- `EmailChannel`: Gửi email
- `SMSChannel`: Gửi SMS (tùy chọn)

### 3.3 NotificationTemplateService
- `render()`: Render template với dữ liệu
- `getTemplate()`: Lấy template theo type và channel

## 4. Event-Driven Architecture

### 4.1 Events cần trigger thông báo
- `OrderCreated`: Đơn hàng mới
- `OrderStatusChanged`: Thay đổi trạng thái đơn hàng
- `InvoiceCreated`: Hóa đơn mới
- `InvoiceOverdue`: Hóa đơn quá hạn
- `InventoryLowStock`: Tồn kho thấp
- `UserRegistered`: Người dùng mới
- `SystemMaintenance`: Bảo trì hệ thống

### 4.2 Event Listeners
- Mỗi event có listener tương ứng
- Listener sẽ gọi NotificationService để gửi thông báo
- Có thể cấu hình delay, retry cho từng loại

## 5. Real-time Implementation

### 5.1 WebSocket/Pusher
- Sử dụng Laravel Broadcasting
- Channel riêng cho từng user: `user.{user_id}`
- Channel chung cho admin: `admin.notifications`

### 5.2 Frontend Integration
- JavaScript client để nhận thông báo real-time
- Toast notifications cho thông báo mới
- Badge counter cho số thông báo chưa đọc
- Dropdown list hiển thị thông báo gần đây

## 6. UI/UX Design

### 6.1 Notification Bell Icon
- Hiển thị ở header
- Badge đỏ hiển thị số thông báo chưa đọc
- Click để mở dropdown

### 6.2 Notification Dropdown
- Hiển thị 5-10 thông báo gần đây
- Phân loại theo type với icon khác nhau
- Link "Xem tất cả" đến trang notification

### 6.3 Notification Page
- Danh sách tất cả thông báo
- Filter theo type, trạng thái đọc
- Pagination
- Bulk actions (đánh dấu đã đọc, xóa)

### 6.4 Notification Settings
- Cài đặt nhận thông báo theo type
- Chọn kênh nhận (web, email, SMS)
- Thời gian nhận (24/7, giờ hành chính)

## 7. Performance Considerations

### 7.1 Database Optimization
- Index trên (notifiable_type, notifiable_id, read_at)
- Index trên (created_at, type)
- Partition table theo thời gian nếu cần

### 7.2 Caching Strategy
- Cache unread count per user
- Cache notification settings
- Queue jobs cho gửi email/SMS

### 7.3 Cleanup Strategy
- Job tự động xóa thông báo cũ (>30 ngày)
- Soft delete cho audit trail
- Archive thông báo quan trọng

## 8. Security & Privacy

### 8.1 Authorization
- User chỉ xem được thông báo của mình
- Admin có thể xem thông báo hệ thống
- Role-based notification permissions

### 8.2 Data Protection
- Không lưu thông tin nhạy cảm trong notification
- Encrypt dữ liệu cá nhân nếu cần
- GDPR compliance cho xóa dữ liệu

## 9. Monitoring & Analytics

### 9.1 Metrics cần theo dõi
- Số thông báo gửi/ngày
- Tỷ lệ đọc thông báo
- Thời gian phản hồi real-time
- Error rate cho từng channel

### 9.2 Logging
- Log tất cả thông báo được gửi
- Log errors và retries
- Performance monitoring

## 10. Future Enhancements

### 10.1 Advanced Features
- Rich notifications với hình ảnh, buttons
- Notification scheduling
- A/B testing cho templates
- Machine learning cho personalization

### 10.2 Integration
- Mobile push notifications
- Slack/Teams integration
- Webhook notifications cho third-party

## 11. Implementation Phases

### Phase 1: Core System (Week 1-2)
- Database migrations
- Basic models và services
- Web notifications only

### Phase 2: Real-time (Week 3)
- WebSocket integration
- Frontend JavaScript
- Basic UI components

### Phase 3: Email Integration (Week 4)
- Email templates
- Queue jobs
- User settings

### Phase 4: Advanced Features (Week 5-6)
- SMS integration (optional)
- Analytics dashboard
- Performance optimization

### Phase 5: Polish & Testing (Week 7-8)
- UI/UX improvements
- Comprehensive testing
- Documentation
