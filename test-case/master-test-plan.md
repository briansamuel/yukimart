# YukiMart Master Test Plan

## 📋 Tổng Quan

Kế hoạch test toàn diện cho hệ thống YukiMart bao gồm tất cả các module chính với focus vào Playwright testing.

## 🎯 Test Scope

### Core Modules
1. **QuickOrder** - Đặt hàng nhanh, Hóa đơn nhanh, Trả hàng nhanh
2. **Orders** - Quản lý đơn hàng
3. **Invoices** - Quản lý hóa đơn  
4. **Returns** - Quản lý trả hàng
5. **Payments** - Phiếu thu/chi

### Test Types
- **Playwright Tests** (70%) - UI/Integration testing
- **Unit Tests** (30%) - Logic/API testing

## 🔄 Test Workflow Logic

```
1. QuickOrder (Core) → 2. Orders → 3. Invoices → 4. Returns → 5. Payments
     ↓                    ↓           ↓            ↓           ↓
   Creates              Manages     Converts     References   Tracks
   Orders/Invoices      Orders      to Invoices  Invoices     Payments
```

## 📊 Test Categories

### 1. Functional Tests
- **CRUD Operations** - Create, Read, Update, Delete
- **Business Logic** - Calculations, validations, workflows
- **Integration** - Module interactions, data consistency

### 2. UI/UX Tests  
- **Interface** - Layout, responsiveness, accessibility
- **User Interactions** - Clicks, forms, navigation
- **Visual** - Styling, animations, feedback

### 3. Performance Tests
- **Load Testing** - Large datasets, concurrent users
- **Response Time** - API calls, page loads
- **Memory Usage** - Browser performance

### 4. Security Tests
- **Authentication** - Login, permissions
- **Authorization** - Role-based access
- **Data Validation** - Input sanitization

## 🎨 Test Data Strategy

### Using Existing Data
- **Products**: Sử dụng products có sẵn trong DB
- **Customers**: Khách hàng hiện tại + "Khách lẻ"
- **Branch Shops**: Chi nhánh hiện tại của user
- **Bank Accounts**: Tài khoản có sẵn

### Test Scenarios
- **Happy Path** - Normal user flows
- **Edge Cases** - Boundary conditions, empty states
- **Error Cases** - Invalid inputs, network failures

## 🛠️ Test Environment

### Setup
- **URL**: http://yukimart.local
- **Login**: yukimart@gmail.com / 123456
- **Browser**: Chrome (Playwright)
- **Database**: MySQL with existing data

### Tools
- **Playwright** - E2E testing
- **PHPUnit** - Unit testing
- **Laravel Testing** - Feature testing

## 📈 Success Criteria

### Coverage Targets
- **Functional Coverage**: 95%
- **UI Coverage**: 90%
- **API Coverage**: 85%
- **Edge Cases**: 80%

### Performance Targets
- **Page Load**: < 3 seconds
- **API Response**: < 1 second
- **UI Interactions**: < 500ms

### Quality Gates
- **Zero Critical Bugs**
- **< 5 Minor Issues**
- **All Core Workflows Working**
- **Cross-browser Compatibility**

## 📋 Test Execution Plan

### Phase 1: Foundation (Days 1-2)
1. Setup test environment
2. Create base test utilities
3. Implement core QuickOrder tests

### Phase 2: Core Modules (Days 3-5)
1. Orders module tests
2. Invoices module tests  
3. Returns module tests
4. Payments module tests

### Phase 3: Integration (Days 6-7)
1. Cross-module integration tests
2. End-to-end workflows
3. Performance testing

### Phase 4: Quality Assurance (Days 8-9)
1. Bug fixes and retesting
2. Test report generation
3. Documentation updates

## 📊 Reporting

### Test Reports
- **Daily Progress Reports**
- **Module Test Reports**
- **Bug Reports with Screenshots**
- **Performance Metrics**
- **Final Test Summary**

### Deliverables
- **Test Cases Documentation**
- **Automated Test Scripts**
- **Bug Reports**
- **Performance Reports**
- **Recommendations**

## 🚀 Getting Started

1. **Review existing test structure** in `test-case/` directory
2. **Start with QuickOrder module** (most complex)
3. **Follow test case templates** from existing modules
4. **Use Playwright for UI testing**
5. **Document all findings**

## 📁 Directory Structure

```
test-case/
├── master-test-plan.md (this file)
├── quick-order/
│   ├── index.md
│   ├── tab-management-tests.md
│   ├── order-creation-tests.md
│   ├── invoice-creation-tests.md
│   └── return-order-tests.md
├── orders/
│   ├── index.md
│   ├── crud-tests.md
│   ├── filter-tests.md
│   └── export-tests.md
├── invoices/
│   ├── index.md
│   ├── bulk-selection-tests.md
│   ├── detail-panel-tests.md
│   └── payment-integration-tests.md
├── returns/
│   ├── index.md
│   ├── creation-tests.md
│   └── workflow-tests.md
├── payments/
│   ├── index.md
│   ├── receipt-tests.md
│   └── disbursement-tests.md
└── reports/
    ├── daily-reports/
    ├── module-reports/
    └── final-report.md
```

## 🔗 Next Steps

1. **Complete QuickOrder test cases** (in progress)
2. **Create Orders module test cases**
3. **Implement Playwright test scripts**
4. **Execute tests and document results**
5. **Generate comprehensive reports**
