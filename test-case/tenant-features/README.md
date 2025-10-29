# YukiMart Tenant Features Testing Suite

This testing suite provides comprehensive automated testing for YukiMart's multi-tenant system, focusing on core business features across different tenants.

## Overview

The test suite validates the following features across multiple tenants:
- **Products Management** - CRUD operations, search, variants
- **Orders Management** - Order creation, status updates, quick orders
- **Invoices Management** - Invoice generation, payment tracking
- **Returns Management** - Return processing, refunds
- **Payments Management** - Payment recording, receipts

## Test Environment

### Prerequisites
- Node.js (v16 or higher)
- YukiMart local development environment running
- Two configured tenants:
  - TechMart Store (tenant1.yukimart.local)
  - Fashion Boutique (tenant2.yukimart.local)

### URLs
- **Base**: http://yukimart.local
- **Tenant 1**: http://tenant1.yukimart.local
- **Tenant 2**: http://tenant2.yukimart.local

## Quick Start

### 1. Setup
```bash
# Navigate to test directory
cd test-case/tenant-features

# Make run script executable
chmod +x run-tests.sh

# Run complete test suite
./run-tests.sh
```

### 2. Manual Setup (if needed)
```bash
# Install dependencies
npm install

# Setup Playwright
npm run setup

# Run specific tests
npm test
```

## Test Structure

### Files
- `tenant-feature-test.js` - Main test script
- `run-tests.sh` - Test runner script
- `package.json` - Dependencies and scripts
- `report.md` - Detailed test documentation
- `README.md` - This file

### Test Categories

#### 1. Authentication Tests
- Login functionality for different user roles
- Session management
- Permission validation

#### 2. Products Tests
- Product listing and pagination
- Product creation and editing
- Search and filtering
- Image upload functionality
- Variant management

#### 3. Orders Tests
- Order listing and filtering
- Order creation workflow
- Quick order interface
- Status management
- Customer integration

#### 4. Invoices Tests
- Invoice listing and search
- Invoice creation
- Invoice generation from orders
- Payment history tracking
- Print/PDF functionality

#### 5. Returns Tests
- Return order listing
- Return creation from invoices
- Return status workflow
- Refund processing
- Inventory adjustments

#### 6. Payments Tests
- Payment record management
- Payment creation and editing
- Receipt generation
- Bank account integration
- Balance calculations

## User Roles Tested

### TechMart Store
- **Owner**: owner@techmart.local / 123456
- **Admin**: admin@techmart.local / 123456
- **Manager**: manager@techmart.local / 123456
- **Staff**: staff@techmart.local / 123456

### Fashion Boutique
- **Owner**: owner@fashion.local / 123456
- **Admin**: admin@fashion.local / 123456
- **Manager**: manager@fashion.local / 123456
- **Staff**: staff@fashion.local / 123456

## Expected Results

### Success Indicators
- ✅ All pages load without errors
- ✅ Data is properly tenant-scoped
- ✅ CRUD operations work correctly
- ✅ Search and filtering functions
- ✅ File uploads work properly
- ✅ Permissions are enforced

### Common Issues to Watch For
- ❌ 404 Not Found errors (route conflicts)
- ❌ 500 Internal Server errors (code issues)
- ❌ Cross-tenant data leakage
- ❌ Permission bypass vulnerabilities
- ❌ AJAX endpoint failures
- ❌ File upload problems

## Test Output

### Generated Files
- `results/test-output.log` - Complete test execution log
- `results/tenant-test-errors.json` - Detailed error report
- `results/test-summary-YYYYMMDD-HHMMSS.md` - Summary report

### Error Report Structure
```json
{
  "timestamp": "2025-01-11T10:30:00.000Z",
  "results": {
    "techmart": {
      "products": {
        "list": true,
        "create": false,
        "errors": ["Product create form not found"]
      },
      "orders": { ... },
      "invoices": { ... },
      "returns": { ... },
      "payments": { ... }
    },
    "fashion": { ... }
  },
  "errors": [
    {
      "tenant": "techmart",
      "feature": "products",
      "error": "Navigation failed",
      "timestamp": "2025-01-11T10:30:15.000Z"
    }
  ]
}
```

## Troubleshooting

### Common Setup Issues

#### 1. URLs Not Accessible
```bash
# Check if local server is running
curl -I http://yukimart.local

# Verify DNS configuration
ping tenant1.yukimart.local
ping tenant2.yukimart.local
```

#### 2. Playwright Installation Issues
```bash
# Reinstall Playwright
npm uninstall playwright
npm install playwright
npx playwright install chromium
```

#### 3. Permission Errors
```bash
# Make script executable
chmod +x run-tests.sh

# Check file permissions
ls -la *.js *.sh
```

### Test Failures

#### 1. Login Failures
- Verify user credentials in database
- Check authentication middleware
- Confirm tenant context resolution

#### 2. Route Not Found (404)
- Check route definitions in admin.php
- Verify controller namespaces
- Confirm middleware application

#### 3. Data Not Loading
- Check AJAX endpoints
- Verify database connections
- Confirm tenant scoping

#### 4. Permission Denied
- Verify user roles and permissions
- Check middleware authorization
- Confirm tenant user relationships

## Customization

### Adding New Tests
```javascript
// Add to tenant-feature-test.js
async testNewFeature(page, tenant) {
    console.log(`🆕 Testing New Feature for ${TENANTS[tenant].name}...`);
    
    const results = {
        functionality1: false,
        functionality2: false,
        errors: []
    };
    
    try {
        // Test implementation
        await page.goto(`${TENANTS[tenant].url}/admin/new-feature`);
        // ... test logic
        
    } catch (error) {
        results.errors.push(error.message);
    }
    
    return results;
}
```

### Modifying Test Data
```javascript
// Update TENANTS configuration
const TENANTS = {
    tenant1: {
        url: 'http://your-tenant1.local',
        name: 'Your Tenant 1',
        credentials: {
            owner: { email: 'owner@tenant1.local', password: 'password' }
        }
    }
};
```

## Integration with CI/CD

### GitHub Actions Example
```yaml
name: Tenant Features Test
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
        with:
          node-version: '16'
      - run: cd test-case/tenant-features && npm install
      - run: cd test-case/tenant-features && npm run setup
      - run: cd test-case/tenant-features && npm test
```

## Support

For issues or questions:
1. Check the generated error reports
2. Review the troubleshooting section
3. Examine the detailed test logs
4. Contact the development team

## Contributing

To contribute to the test suite:
1. Fork the repository
2. Create a feature branch
3. Add your tests
4. Update documentation
5. Submit a pull request

---

**Last Updated**: 2025-01-11
**Version**: 1.0.0
**Maintainer**: YukiMart Development Team
