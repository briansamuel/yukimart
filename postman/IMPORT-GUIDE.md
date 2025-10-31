# 📦 YukiMart Dashboard APIs - Postman Import Guide

## 🚀 Quick Import Steps

### 1. Download Files
Download these files from the `postman/` directory:
- `YukiMart-Dashboard-APIs.postman_collection.json` - Main collection
- `YukiMart-Environment.postman_environment.json` - Environment variables

### 2. Import into Postman

#### Import Collection:
1. Open Postman
2. Click **Import** button (top left)
3. Select **YukiMart-Dashboard-APIs.postman_collection.json**
4. Click **Import**

#### Import Environment:
1. Click **Import** button again
2. Select **YukiMart-Environment.postman_environment.json**
3. Click **Import**
4. Select **YukiMart Environment** from environment dropdown (top right)

### 3. Authentication Setup

#### Method 1: Manual Token
1. Run **Authentication → Login** request
2. Copy `access_token` from response
3. Set it in environment variable `access_token`

#### Method 2: Auto-Update (Recommended)
Add this script to **Authentication → Login** request's **Tests** tab:
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.status === 'success' && response.data.access_token) {
        pm.environment.set('access_token', response.data.access_token);
        console.log('Access token updated automatically');
    }
}
```

## 📊 Available Endpoints

### 🔐 Authentication
- **POST** `/api/v1/auth/login` - Get access token

### 📈 Dashboard Statistics  
- **GET** `/admin/dashboard/stats` - Dashboard statistics with period filter
  - Parameters: `period` (today, yesterday, month, last_month, year)

### 📊 Revenue Charts
- **GET** `/admin/dashboard/revenue-chart` - Revenue chart data
  - Parameters: `period` (today, yesterday, month, last_month, year)

### 🏆 Top Products
- **GET** `/admin/dashboard/top-products` - Top products (invoice-based)
  - Parameters: `type` (revenue, quantity), `period`, `limit`

## 🔧 Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `base_url` | Application base URL | `http://yukimart.local` |
| `access_token` | Authentication token | `1\|abc123...` |
| `api_base_url` | API base URL | `{{base_url}}/api/v1` |
| `admin_base_url` | Admin base URL | `{{base_url}}/admin` |
| `login_email` | Login email | `yukimart@gmail.com` |
| `login_password` | Login password | `123456` |

## 📝 Real Data Examples

### Dashboard Stats Response
```json
{
    "status": "success",
    "data": {
        "total_orders": 22,
        "total_invoices": 1853,
        "total_products": 3782,
        "total_customers": 434,
        "period_revenue": 57754017,
        "period_invoice_amount": 16723300,
        "formatted_period_revenue": "57.754.017₫"
    },
    "meta": {
        "period": "month",
        "period_name": "tháng này"
    }
}
```

### Revenue Chart Response
```json
{
    "status": "success",
    "data": {
        "revenue_chart": {
            "categories": ["01/08", "02/08", "03/08", ...],
            "data": [20.754836, 8.042129, 6.057981, ...],
            "series_name": "Doanh thu tháng này (triệu VNĐ)"
        }
    }
}
```

### Top Products Response
```json
{
    "status": "success",
    "data": [
        {
            "id": 105,
            "name": "Miếng Dán Nóng Giảm Đau Kowa Vantelin Kowa Pat EX",
            "sku": "4987067329908",
            "total_revenue": 2420000,
            "image": "https://cdn-images.kiotviet.vn/...",
            "sold_quantity": 4
        }
    ]
}
```

## 🎯 Testing Workflow

### 1. Authentication Test
1. Run **Authentication → Login**
2. Verify `access_token` is set in environment
3. Check token format: `1|abc123...`

### 2. Dashboard Stats Test
1. Run **Dashboard Stats - Month**
2. Verify response structure
3. Check period-specific data
4. Test different periods (today, yesterday, year)

### 3. Revenue Chart Test
1. Run **Revenue Chart - Month**
2. Verify chart data format
3. Check categories and data arrays
4. Test different periods for different granularity

### 4. Top Products Test
1. Run **Top Products - Revenue**
2. Verify product data structure
3. Test both revenue and quantity types
4. Check image URLs and product details

## 🐛 Troubleshooting

### Authentication Issues
- **401 Unauthorized**: Token expired, re-login
- **Token format**: Should start with `1|`
- **Environment**: Ensure correct environment selected

### Empty Responses
- **Normal behavior**: Some endpoints return empty arrays if no data
- **Structure preserved**: Meta information always included
- **Database dependent**: Results depend on available invoice data

### Network Issues
- **Base URL**: Verify `http://yukimart.local` is accessible
- **CORS**: Admin endpoints require proper authentication
- **Timeout**: Increase request timeout if needed

## 📚 Additional Features

### Pre-request Scripts
Collection includes automatic token refresh and environment setup.

### Response Tests
Built-in tests verify response structure and data types.

### Documentation
Each request includes detailed descriptions and parameter explanations.

### Examples
Real response examples from production data included.

## 🔄 Updates

### Version History
- **v1.0** (Aug 2025): Initial release with dashboard endpoints
- Real data examples from YukiMart production
- Complete authentication flow
- Comprehensive error handling

### Future Updates
- Additional dashboard endpoints
- Enhanced filtering options
- Real-time data streaming
- Advanced analytics endpoints

---

**Need Help?** Check the main README.md for detailed API documentation.

**Last Updated**: August 2025  
**Collection Version**: 1.0
