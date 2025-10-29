# YukiMart Dashboard APIs - Postman Collection

## 📊 Overview

This Postman collection contains all YukiMart Dashboard API endpoints with real examples and comprehensive documentation.

## 🚀 Quick Start

### 1. Import Collection
- Download `YukiMart-Dashboard-APIs.postman_collection.json`
- Import into Postman: File → Import → Select file

### 2. Set Environment Variables
Create a new environment with these variables:
```
base_url: http://yukimart.local
access_token: (will be set after login)
```

### 3. Authentication
1. Run the **Login** request in the Authentication folder
2. Copy the `access_token` from response
3. Set it in your environment variables

## 📋 API Endpoints

### 🔐 Authentication
- **POST** `/api/v1/auth/login` - Login to get access token

### 📊 Dashboard Statistics
- **GET** `/admin/dashboard/stats` - Get dashboard statistics with period filter
  - Parameters: `period` (today, yesterday, month, last_month, year)
  - Returns: Overall stats + period-specific metrics

### 📈 Revenue Chart
- **GET** `/admin/dashboard/revenue-chart` - Get revenue chart data
  - Parameters: `period` (today, yesterday, month, last_month, year)
  - Returns: Chart data with categories, data points, and series name
  - Data granularity:
    - **Today/Yesterday**: Hourly data (3-hour intervals)
    - **Month/Last Month**: Daily data
    - **Year**: Monthly data

### 🏆 Top Products
- **GET** `/admin/dashboard/top-products` - Get top products (invoice-based)
  - Parameters: 
    - `type` (revenue, quantity)
    - `period` (today, yesterday, month, last_month, year)
    - `limit` (number of products)
  - Returns: Unified response with both revenue and quantity data

## 📝 Response Examples

### Dashboard Stats Response
```json
{
    "status": "success",
    "message": "Statistics retrieved successfully",
    "data": {
        "total_orders": 22,
        "total_invoices": 0,
        "total_products": 0,
        "total_customers": 8,
        "period_revenue": 57754017,
        "period_orders": 22,
        "period_customers": 7,
        "period_avg_order_value": 2625183.5,
        "formatted_period_revenue": "57.754.017₫"
    },
    "meta": {
        "period": "month",
        "period_name": "tháng này",
        "date_range": {
            "start": "2025-08-01 00:00:00",
            "end": "2025-08-31 23:59:59"
        }
    }
}
```

### Revenue Chart Response
```json
{
    "status": "success",
    "message": "Revenue chart data retrieved successfully",
    "data": {
        "revenue_chart": {
            "categories": ["01/08", "02/08", "03/08", "04/08", "05/08", "06/08", "07/08"],
            "data": [20.754836, 8.042129, 6.057981, 5.838266, 1.879843, 9.267306, 5.913656],
            "series_name": "Doanh thu tháng này (triệu VNĐ)"
        }
    },
    "meta": {
        "period": "month",
        "period_name": "tháng này"
    }
}
```

### Top Products Response
```json
{
    "status": "success",
    "message": "Top products retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Product Name",
            "sku": "SKU123",
            "total_revenue": 2500000.0,
            "image": "http://yukimart.local/storage/products/image.jpg",
            "sold_quantity": 15
        }
    ],
    "meta": {
        "type": "revenue",
        "period": "month",
        "period_name": "tháng này",
        "limit": "10"
    }
}
```

## 🔧 Period Parameters

| Period | Description | Date Range |
|--------|-------------|------------|
| `today` | Current day | 00:00:00 - 23:59:59 today |
| `yesterday` | Previous day | 00:00:00 - 23:59:59 yesterday |
| `month` | Current month | 1st - last day of current month |
| `last_month` | Previous month | 1st - last day of previous month |
| `year` | Current year | Jan 1 - Dec 31 of current year |

## 💡 Usage Tips

### 1. Authentication
- Always include `Authorization: Bearer {{access_token}}` header
- Token expires after some time, re-login if you get 401 errors

### 2. Period Filtering
- Default period is `month` for most endpoints
- Use `today` for real-time monitoring
- Use `year` for long-term analysis

### 3. Chart Data
- Revenue values are in millions (triệu VNĐ) for chart readability
- Categories format varies by period (hours, days, months)
- Series names are in Vietnamese

### 4. Top Products
- Based on invoice data (confirmed sales only)
- Revenue type: sorted by line_total (includes tax, discount)
- Quantity type: sorted by quantity sold
- Always returns both revenue and quantity fields

## 🐛 Troubleshooting

### Empty Data Responses
- Normal if no invoices/products in database
- APIs return empty arrays with proper meta information
- Structure remains consistent for frontend integration

### Authentication Issues
- Check if token is valid and not expired
- Ensure `Authorization` header is properly formatted
- Re-login to get fresh token

### Period Filter Issues
- Verify period parameter spelling
- Check date ranges in meta response
- Ensure data exists for the requested period

## 📚 Additional Resources

- **API Documentation**: Available in code comments
- **Database Schema**: Check migration files
- **Frontend Integration**: Use meta information for period names
- **Error Handling**: All endpoints return consistent error format

## 🔄 Updates

This collection is automatically updated when new dashboard endpoints are added. Check the modification date and re-import if needed.

---

**Last Updated**: August 2025  
**Version**: 1.0  
**Maintainer**: YukiMart Development Team
