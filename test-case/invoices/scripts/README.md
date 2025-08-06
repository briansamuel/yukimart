# Invoice Module - Automated Tests

Automated test suite for the Invoice module using Playwright.

## 🚀 Quick Start

### 1. Install Dependencies
```bash
cd test-case/invoices/scripts
npm install
npm run install-playwright
```

### 2. Run All Tests
```bash
npm test
```

### 3. Run Individual Test Categories
```bash
# Filter Tests only
npm run test:filter

# Column Visibility Tests only  
npm run test:columns

# Row Expansion Tests only
npm run test:expansion
```

## 📋 Test Categories

### Filter Tests (F01-F10)
- **F01**: Time Filter - "Tháng này"
- **F02**: Time Filter - "Tùy chỉnh" 
- **F03**: Status Filter - "Đang xử lý"
- **F04**: Status Filter - "Hoàn thành"
- **F05**: Multiple Status Filter
- **F06**: Creator Filter Dropdown
- **F07**: Seller Filter Dropdown
- **F08**: Delivery Status Filter
- **F09**: Sales Channel Filter
- **F10**: Reset All Filters

### Column Visibility Tests (CV01-CV06)
- **CV01**: Open Column Visibility Panel
- **CV02**: Hide Email Column
- **CV03**: Show Email Column
- **CV04**: Hide Multiple Columns
- **CV05**: Show All Columns
- **CV06**: Column Visibility Persistence

### Row Expansion Tests (RE01-RE06)
- **RE01**: Click Row to Expand
- **RE02**: Detail Panel Content
- **RE03**: Switch Between Tabs
- **RE04**: Collapse Row
- **RE05**: Expand Different Row
- **RE06**: Detail Panel Position

## 📊 Reports

After running tests, reports are generated:

1. **Console Output**: Real-time test results
2. **comprehensive-test-report.md**: Detailed markdown report
3. **Updated report.md**: Main project report with automation results

## ⚙️ Configuration

### Credentials
Tests use these default credentials:
- **Email**: yukimart@gmail.com
- **Password**: 123456

### Base URL
- **Invoice Page**: http://yukimart.local/admin/invoices

### Browser Settings
- **Browser**: Chromium
- **Headless**: false (visible browser for debugging)
- **Timeout**: 10 seconds for element waits

## 🔧 Customization

### Modify Test Data
Edit the test files to change:
- Search terms
- Filter values
- Expected results
- Timeout values

### Add New Tests
1. Create new test methods in existing files
2. Add them to the `runAllTests()` method
3. Update the test count in reports

### Change Browser Settings
Modify the `setup()` method in each test file:
```javascript
this.browser = await chromium.launch({ 
    headless: true,  // Run in background
    slowMo: 1000     // Slow down actions
});
```

## 🐛 Troubleshooting

### Common Issues

1. **Login Failed**
   - Check credentials in test files
   - Verify yukimart.local is accessible

2. **Elements Not Found**
   - Check if page structure changed
   - Update selectors in test files

3. **Timeout Errors**
   - Increase timeout values
   - Check network connectivity

4. **Browser Not Found**
   - Run `npm run install-playwright`
   - Check Playwright installation

### Debug Mode
Add these lines for debugging:
```javascript
await this.page.pause(); // Pause execution
await this.page.screenshot({ path: 'debug.png' }); // Take screenshot
```

## 📈 Performance

### Test Execution Times
- **Filter Tests**: ~2-3 minutes
- **Column Visibility Tests**: ~1-2 minutes  
- **Row Expansion Tests**: ~1-2 minutes
- **Total Runtime**: ~5-7 minutes

### Optimization Tips
1. Run tests in headless mode for faster execution
2. Reduce wait times for stable environments
3. Run individual test categories for focused testing
4. Use parallel execution for large test suites

## 🔄 CI/CD Integration

### GitHub Actions Example
```yaml
name: Invoice Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
        with:
          node-version: '16'
      - run: cd test-case/invoices/scripts && npm install
      - run: cd test-case/invoices/scripts && npm test
```

### Docker Integration
```dockerfile
FROM mcr.microsoft.com/playwright:v1.40.0-focal
WORKDIR /app
COPY test-case/invoices/scripts/ .
RUN npm install
CMD ["npm", "test"]
```

## 📝 Contributing

1. Follow existing code patterns
2. Add comprehensive error handling
3. Update documentation for new tests
4. Test on multiple environments
5. Generate meaningful reports

## 🎯 Future Enhancements

- [ ] Bulk Action Tests automation
- [ ] Export Tests automation  
- [ ] Responsive Tests automation
- [ ] Performance testing integration
- [ ] Visual regression testing
- [ ] API endpoint testing
- [ ] Database state validation
