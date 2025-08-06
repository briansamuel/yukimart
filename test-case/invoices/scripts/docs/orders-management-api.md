# Orders Management System - API Documentation

## Overview

The Orders Management System provides comprehensive APIs for managing orders, including bulk operations, filtering, and Excel export functionality.

## Base URL
```
http://yukimart.local/admin/orders
```

## Authentication
All API endpoints require authentication. Include session cookies or authentication headers as configured in your Laravel application.

## API Endpoints

### 1. Get Orders Data (AJAX)

**Endpoint:** `GET /admin/orders/ajax`

**Description:** Retrieve orders data with filtering, pagination, and search capabilities.

**Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 10)
- `search` (string, optional): Search term for order code, customer name
- `status` (string, optional): Order status filter (comma-separated: processing,completed,cancelled,undeliverable)
- `payment_status` (string, optional): Payment status filter (paid,unpaid,partial)
- `delivery_status` (string, optional): Delivery status filter (pending,picking,shipping,delivered,returned,cancelled)
- `time_filter_display` (string, optional): Time filter (today,yesterday,this_week,last_week,this_month,last_month,all_time)
- `date_from` (date, optional): Start date (YYYY-MM-DD)
- `date_to` (date, optional): End date (YYYY-MM-DD)
- `created_by` (integer, optional): Filter by creator user ID
- `sold_by` (integer, optional): Filter by seller user ID
- `channel` (string, optional): Sales channel filter

**Example Request:**
```bash
GET /admin/orders/ajax?page=1&per_page=10&status=processing,completed&time_filter_display=today
```

**Example Response:**
```json
{
  "draw": 1,
  "recordsTotal": 1811,
  "recordsFiltered": 8,
  "data": [
    {
      "id": 1811,
      "order_code": "HD054",
      "customer_name": "Lê Văn C",
      "customer_phone": "0987654321",
      "total_amount": "1200000.00",
      "amount_paid": "0.00",
      "status": "completed",
      "payment_status": "unpaid",
      "delivery_status": "pending",
      "channel": "other",
      "created_at": "2025-07-23T06:32:50.000000Z",
      "updated_at": "2025-07-23T06:34:18.000000Z"
    }
  ],
  "success": true
}
```

### 2. Bulk Status Update

**Endpoint:** `POST /admin/orders/bulk-status-update`

**Description:** Update status for multiple orders simultaneously.

**Request Body:**
```json
{
  "order_ids": [1811, 1810, 1809],
  "order_status": "completed",
  "payment_status": "paid",
  "delivery_status": "delivered"
}
```

**Parameters:**
- `order_ids` (array, required): Array of order IDs to update
- `order_status` (string, optional): New order status (processing,completed,cancelled,undeliverable)
- `payment_status` (string, optional): New payment status (paid,unpaid,partial)
- `delivery_status` (string, optional): New delivery status (pending,picking,shipping,delivered,returned,cancelled)

**Example Request:**
```bash
curl -X POST http://yukimart.local/admin/orders/bulk-status-update \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "order_ids": [1811, 1810, 1809],
    "order_status": "completed",
    "payment_status": "paid"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Đã cập nhật trạng thái thành công cho 3 đơn hàng",
  "updated_count": 3,
  "errors": []
}
```

### 3. Bulk Excel Export

**Endpoint:** `POST /admin/orders/bulk-export`

**Description:** Export selected orders to Excel file.

**Request Body:**
```json
{
  "order_ids": [1811, 1810, 1809]
}
```

**Parameters:**
- `order_ids` (array, required): Array of order IDs to export

**Example Request:**
```bash
curl -X POST http://yukimart.local/admin/orders/bulk-export \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "order_ids": [1811, 1810, 1809]
  }'
```

**Response:** Excel file download with filename format: `orders_export_YYYY-MM-DD_HH-MM-SS.xlsx`

## Error Handling

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": ["Detailed error messages"]
}
```

### Common Error Codes
- `400 Bad Request`: Invalid parameters or request format
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

## Status Values

### Order Status
- `processing`: Đang xử lý
- `completed`: Hoàn thành
- `cancelled`: Đã hủy
- `undeliverable`: Không giao được

### Payment Status
- `paid`: Đã thanh toán
- `unpaid`: Chưa thanh toán
- `partial`: Thanh toán một phần

### Delivery Status
- `pending`: Chờ xử lý
- `picking`: Lấy hàng
- `shipping`: Giao hàng
- `delivered`: Giao thành công
- `returned`: Chuyển hoàn
- `cancelled`: Đã hủy
