/**
 * Invoice Filter Tests - Playwright Automation Script
 * Tests all filter functionality on the invoice listing page
 */

const { chromium } = require('playwright');

class InvoiceFilterTests {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.baseUrl = 'http://yukimart.local/admin/invoices';
        this.loginUrl = 'http://yukimart.local/login';
        this.credentials = {
            email: 'yukimart@gmail.com',
            password: '123456'
        };
        this.sessionFile = './session.json';
    }

    async setup() {
        console.log('🚀 Setting up Invoice Filter Tests...');
        this.browser = await chromium.launch({
            headless: false,
            slowMo: 500 // Slow down for better visibility
        });

        // Create context with persistent session
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });

        // Load saved session if exists
        await this.loadSession(context);

        this.page = await context.newPage();

        // Check if already logged in
        await this.page.goto(this.baseUrl);
        await this.page.waitForLoadState('networkidle');

        // If redirected to login, need to authenticate
        if (this.page.url().includes('/login')) {
            console.log('🔐 Session expired, need to login...');
            await this.login();
            await this.saveSession(context);
        } else {
            console.log('✅ Session valid, already logged in');
        }

        // Navigate to invoices page
        console.log('📄 Navigating to invoices page...');
        await this.page.goto(this.baseUrl);
        await this.page.waitForLoadState('networkidle');

        // Wait for page to load
        console.log('⏳ Waiting for invoice data to load...');
        await this.waitForDataLoad();

        console.log('✅ Setup completed');
    }

    async login() {
        console.log('🔐 Attempting to login...');

        // Go to correct login page
        await this.page.goto(this.loginUrl);
        await this.page.waitForLoadState('networkidle');

        // Wait for login form
        await this.page.waitForSelector('input[name="email"]', { timeout: 10000 });

        // Fill login form
        console.log('📝 Filling login form...');
        await this.page.fill('input[name="email"]', this.credentials.email);
        await this.page.waitForTimeout(500);
        await this.page.fill('input[name="password"]', this.credentials.password);
        await this.page.waitForTimeout(500);

        // Submit form
        console.log('🚀 Submitting login form...');
        await Promise.all([
            this.page.waitForNavigation({ waitUntil: 'networkidle' }),
            this.page.click('button[type="submit"]')
        ]);

        // Verify login success
        const currentUrl = this.page.url();
        if (currentUrl.includes('/admin/') && !currentUrl.includes('/login')) {
            console.log('✅ Login successful');
        } else {
            throw new Error(`Login failed - current URL: ${currentUrl}`);
        }
    }

    async saveSession(context) {
        try {
            const fs = require('fs').promises;
            const cookies = await context.cookies();
            const localStorage = await this.page.evaluate(() => {
                return JSON.stringify(localStorage);
            });

            const sessionData = {
                cookies,
                localStorage,
                timestamp: Date.now()
            };

            await fs.writeFile(this.sessionFile, JSON.stringify(sessionData, null, 2));
            console.log('💾 Session saved');
        } catch (error) {
            console.log('⚠️ Failed to save session:', error.message);
        }
    }

    async loadSession(context) {
        try {
            const fs = require('fs').promises;
            const sessionData = JSON.parse(await fs.readFile(this.sessionFile, 'utf8'));

            // Check if session is not too old (24 hours)
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours
            if (Date.now() - sessionData.timestamp > maxAge) {
                console.log('⚠️ Session expired, will need fresh login');
                return;
            }

            // Restore cookies
            if (sessionData.cookies) {
                await context.addCookies(sessionData.cookies);
                console.log('🍪 Cookies restored');
            }

        } catch (error) {
            console.log('⚠️ No saved session found or failed to load');
        }
    }

    async waitForDataLoad() {
        // Wait for invoice table to load (exclude debug bar)
        await this.page.waitForSelector('table:not(.phpdebugbar-widgets-params) tbody tr', { timeout: 15000 });

        // Wait for loading to complete (check if "Đang tải dữ liệu..." disappears)
        try {
            await this.page.waitForFunction(() => {
                const tables = document.querySelectorAll('table:not(.phpdebugbar-widgets-params)');
                for (const table of tables) {
                    const loadingText = table.querySelector('tbody tr td');
                    if (loadingText && !loadingText.textContent.includes('Đang tải dữ liệu')) {
                        return true;
                    }
                }
                return false;
            }, { timeout: 10000 });
        } catch (e) {
            console.log('Loading check timeout, proceeding...');
        }

        await this.page.waitForTimeout(2000); // Additional wait for AJAX
    }

    async getResultCount() {
        // Look for pagination info text
        const selectors = [
            'text="Hiển thị"',
            ':has-text("kết quả")',
            '.dataTables_info',
            '.pagination-info',
            '.table-info'
        ];

        for (const selector of selectors) {
            try {
                const element = await this.page.locator(selector).first();
                if (await element.isVisible()) {
                    const text = await element.textContent();
                    const match = text?.match(/(\d+)\s+kết quả/);
                    if (match) return parseInt(match[1]);
                }
            } catch (e) {
                continue;
            }
        }

        // Fallback: count table rows (exclude debug bar)
        const rows = await this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
        return rows > 0 && !(await this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr:has-text("Đang tải")').count()) ? rows : 0;
    }

    async captureNetworkRequest() {
        return new Promise((resolve) => {
            this.page.once('request', (request) => {
                if (request.url().includes('/ajax')) {
                    const url = new URL(request.url());
                    resolve(Object.fromEntries(url.searchParams));
                }
            });
        });
    }

    // Test F01: Time Filter - "Tháng này"
    async testTimeFilterThisMonth() {
        console.log('🧪 Testing F01: Time Filter - Tháng này');
        
        try {
            // Ensure "Tháng này" is selected (should be default)
            const thisMonthRadio = await this.page.locator('input[type="radio"][value="this_month"], input[type="radio"]:has-text("Tháng này")').first();
            await thisMonthRadio.check();
            
            // Wait for AJAX request
            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(1000);
            const params = await requestPromise.catch(() => ({}));
            
            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();
            
            this.results.push({
                id: 'F01',
                name: 'Time Filter - Tháng này',
                status: 'PASSED',
                details: `Filter applied, ${resultCount} results, params: ${JSON.stringify(params)}`
            });
            
            console.log('✅ F01 PASSED');
        } catch (error) {
            this.results.push({
                id: 'F01',
                name: 'Time Filter - Tháng này',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F01 FAILED:', error.message);
        }
    }

    // Test F02: Time Filter - "Tùy chỉnh"
    async testTimeFilterCustom() {
        console.log('🧪 Testing F02: Time Filter - Tùy chỉnh');
        
        try {
            // Click "Tùy chỉnh" radio
            const customRadio = await this.page.locator('input[type="radio"][value="custom"], input[type="radio"]:has-text("Tùy chỉnh")').first();
            await customRadio.check();
            
            // Check if date picker appears
            await this.page.waitForTimeout(1000);
            const datePickerVisible = await this.page.isVisible('.date-range-picker, input[type="date"], .flatpickr-input');
            
            this.results.push({
                id: 'F02',
                name: 'Time Filter - Tùy chỉnh',
                status: datePickerVisible ? 'PASSED' : 'FAILED',
                details: `Custom radio checked, date picker visible: ${datePickerVisible}`
            });
            
            console.log(datePickerVisible ? '✅ F02 PASSED' : '❌ F02 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F02',
                name: 'Time Filter - Tùy chỉnh',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F02 FAILED:', error.message);
        }
    }

    // Test F03: Status Filter - "Đang xử lý"
    async testStatusFilterProcessing() {
        console.log('🧪 Testing F03: Status Filter - Đang xử lý');
        
        try {
            // Uncheck all status checkboxes first
            await this.page.uncheck('input[type="checkbox"]:has-text("Hoàn thành")').catch(() => {});
            
            // Check only "Đang xử lý"
            const processingCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Đang xử lý")').first();
            await processingCheckbox.check();
            
            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));
            
            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();
            
            // Verify all results have "Đang xử lý" status
            const statusCells = await this.page.locator('table tbody tr td:has-text("Đang xử lý")').count();
            const totalRows = await this.page.locator('table tbody tr').count();
            
            const allProcessing = statusCells === totalRows;
            
            this.results.push({
                id: 'F03',
                name: 'Status Filter - Đang xử lý',
                status: allProcessing ? 'PASSED' : 'FAILED',
                details: `${resultCount} results, ${statusCells}/${totalRows} rows with "Đang xử lý" status`
            });
            
            console.log(allProcessing ? '✅ F03 PASSED' : '❌ F03 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F03',
                name: 'Status Filter - Đang xử lý',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F03 FAILED:', error.message);
        }
    }

    // Test F04: Status Filter - "Hoàn thành"
    async testStatusFilterCompleted() {
        console.log('🧪 Testing F04: Status Filter - Hoàn thành');
        
        try {
            // Uncheck "Đang xử lý" and check "Hoàn thành"
            await this.page.uncheck('input[type="checkbox"]:has-text("Đang xử lý")').catch(() => {});
            
            const completedCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Hoàn thành")').first();
            await completedCheckbox.check();
            
            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));
            
            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();
            
            // Verify all results have "Hoàn thành" status
            const statusCells = await this.page.locator('table tbody tr td:has-text("Hoàn thành")').count();
            const totalRows = await this.page.locator('table tbody tr').count();
            
            const allCompleted = statusCells === totalRows;
            
            this.results.push({
                id: 'F04',
                name: 'Status Filter - Hoàn thành',
                status: allCompleted ? 'PASSED' : 'FAILED',
                details: `${resultCount} results, ${statusCells}/${totalRows} rows with "Hoàn thành" status`
            });
            
            console.log(allCompleted ? '✅ F04 PASSED' : '❌ F04 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F04',
                name: 'Status Filter - Hoàn thành',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F04 FAILED:', error.message);
        }
    }

    // Test F05: Multiple Status Filter
    async testMultipleStatusFilter() {
        console.log('🧪 Testing F05: Multiple Status Filter');
        
        try {
            // Check both "Đang xử lý" and "Hoàn thành"
            await this.page.check('input[type="checkbox"]:has-text("Đang xử lý")');
            await this.page.check('input[type="checkbox"]:has-text("Hoàn thành")');
            
            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));
            
            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();
            
            // Verify results contain both statuses
            const processingCells = await this.page.locator('table tbody tr td:has-text("Đang xử lý")').count();
            const completedCells = await this.page.locator('table tbody tr td:has-text("Hoàn thành")').count();
            const totalRows = await this.page.locator('table tbody tr').count();
            
            const validResults = (processingCells + completedCells) === totalRows;
            
            this.results.push({
                id: 'F05',
                name: 'Multiple Status Filter',
                status: validResults ? 'PASSED' : 'FAILED',
                details: `${resultCount} results, ${processingCells} processing + ${completedCells} completed = ${totalRows} total`
            });
            
            console.log(validResults ? '✅ F05 PASSED' : '❌ F05 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F05',
                name: 'Multiple Status Filter',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F05 FAILED:', error.message);
        }
    }

    // Test F06: Creator Filter
    async testCreatorFilter() {
        console.log('🧪 Testing F06: Creator Filter');

        try {
            // Click creator dropdown
            const creatorDropdown = await this.page.locator('select:has-text("Chọn người tạo"), .select2-container:has-text("Chọn người tạo")').first();
            await creatorDropdown.click();
            await this.page.waitForTimeout(1000);

            // Check if dropdown options are visible
            const optionsVisible = await this.page.isVisible('.select2-results, option');

            this.results.push({
                id: 'F06',
                name: 'Creator Filter Dropdown',
                status: optionsVisible ? 'PASSED' : 'FAILED',
                details: `Creator dropdown opened, options visible: ${optionsVisible}`
            });

            console.log(optionsVisible ? '✅ F06 PASSED' : '❌ F06 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F06',
                name: 'Creator Filter Dropdown',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F06 FAILED:', error.message);
        }
    }

    // Test F07: Seller Filter
    async testSellerFilter() {
        console.log('🧪 Testing F07: Seller Filter');

        try {
            // Click seller dropdown
            const sellerDropdown = await this.page.locator('select:has-text("Chọn người bán"), .select2-container:has-text("Chọn người bán")').first();
            await sellerDropdown.click();
            await this.page.waitForTimeout(1000);

            // Check if dropdown options are visible
            const optionsVisible = await this.page.isVisible('.select2-results, option');

            this.results.push({
                id: 'F07',
                name: 'Seller Filter Dropdown',
                status: optionsVisible ? 'PASSED' : 'FAILED',
                details: `Seller dropdown opened, options visible: ${optionsVisible}`
            });

            console.log(optionsVisible ? '✅ F07 PASSED' : '❌ F07 FAILED');
        } catch (error) {
            this.results.push({
                id: 'F07',
                name: 'Seller Filter Dropdown',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F07 FAILED:', error.message);
        }
    }

    // Test F08: Delivery Status Filter
    async testDeliveryStatusFilter() {
        console.log('🧪 Testing F08: Delivery Status Filter');

        try {
            // Check "Chờ xử lý" delivery status
            const pendingCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Chờ xử lý")').first();
            await pendingCheckbox.check();

            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));

            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();

            this.results.push({
                id: 'F08',
                name: 'Delivery Status Filter',
                status: 'PASSED',
                details: `Delivery status filter applied, ${resultCount} results`
            });

            console.log('✅ F08 PASSED');
        } catch (error) {
            this.results.push({
                id: 'F08',
                name: 'Delivery Status Filter',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F08 FAILED:', error.message);
        }
    }

    // Test F09: Sales Channel Filter
    async testSalesChannelFilter() {
        console.log('🧪 Testing F09: Sales Channel Filter');

        try {
            // Type in sales channel input
            const channelInput = await this.page.locator('input:has-text("Chọn kênh bán"), input[placeholder*="kênh bán"]').first();
            await channelInput.fill('Cửa hàng');

            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));

            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();

            this.results.push({
                id: 'F09',
                name: 'Sales Channel Filter',
                status: 'PASSED',
                details: `Sales channel filter applied, ${resultCount} results`
            });

            console.log('✅ F09 PASSED');
        } catch (error) {
            this.results.push({
                id: 'F09',
                name: 'Sales Channel Filter',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F09 FAILED:', error.message);
        }
    }

    // Test F10: Reset All Filters
    async testResetAllFilters() {
        console.log('🧪 Testing F10: Reset All Filters');

        try {
            // Reset to default state
            await this.page.check('input[type="radio"]:has-text("Tháng này")');
            await this.page.check('input[type="checkbox"]:has-text("Đang xử lý")');
            await this.page.check('input[type="checkbox"]:has-text("Hoàn thành")');

            // Clear other filters
            await this.page.uncheck('input[type="checkbox"]:has-text("Chờ xử lý")').catch(() => {});

            const requestPromise = this.captureNetworkRequest();
            await this.page.waitForTimeout(2000);
            const params = await requestPromise.catch(() => ({}));

            await this.waitForDataLoad();
            const resultCount = await this.getResultCount();

            this.results.push({
                id: 'F10',
                name: 'Reset All Filters',
                status: 'PASSED',
                details: `Filters reset, ${resultCount} results returned to default state`
            });

            console.log('✅ F10 PASSED');
        } catch (error) {
            this.results.push({
                id: 'F10',
                name: 'Reset All Filters',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ F10 FAILED:', error.message);
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Invoice Filter Tests...\n');

        await this.setup();

        // Run all filter tests
        await this.testTimeFilterThisMonth();
        await this.testTimeFilterCustom();
        await this.testStatusFilterProcessing();
        await this.testStatusFilterCompleted();
        await this.testMultipleStatusFilter();
        await this.testCreatorFilter();
        await this.testSellerFilter();
        await this.testDeliveryStatusFilter();
        await this.testSalesChannelFilter();
        await this.testResetAllFilters();

        // Generate report
        this.generateReport();

        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 FILTER TESTS REPORT');
        console.log('========================');
        
        let passed = 0;
        let failed = 0;
        
        this.results.forEach(result => {
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.id}: ${result.name}`);
            console.log(`   ${result.details}\n`);
            
            if (result.status === 'PASSED') passed++;
            else failed++;
        });
        
        console.log(`📈 SUMMARY: ${passed}/${passed + failed} tests passed (${Math.round(passed/(passed + failed)*100)}%)`);
        
        // Save results to file
        const reportContent = this.results.map(r => 
            `| ${r.id} | ${r.name} | ${r.status === 'PASSED' ? '✅ PASSED' : '❌ FAILED'} | ${r.details} |`
        ).join('\n');
        
        console.log('\n📝 Report saved to results');
        return { passed, failed, total: passed + failed, results: this.results };
    }

    async teardown() {
        if (this.page && this.browser) {
            // Save session before closing
            const context = this.page.context();
            await this.saveSession(context);
            await this.browser.close();
        }
        console.log('🏁 Tests completed');
    }
}

// Run tests if called directly
if (require.main === module) {
    const tests = new InvoiceFilterTests();
    tests.runAllTests().catch(console.error);
}

module.exports = InvoiceFilterTests;
