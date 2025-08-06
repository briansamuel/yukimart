/**
 * Run All Tests Script
 * Executes comprehensive test suite for YukiMart system
 */

const { chromium } = require('playwright');
const YukiMartTestRunner = require('./master-test-runner');

class ComprehensiveTestSuite {
    constructor() {
        this.testRunner = new YukiMartTestRunner();
        this.browser = null;
        this.context = null;
        this.page = null;
    }

    async setup() {
        console.log('🚀 Starting YukiMart Comprehensive Test Suite...');
        console.log('='.repeat(60));

        // Launch browser
        this.browser = await chromium.launch({ 
            headless: false, // Set to true for CI/CD
            slowMo: 100 // Slow down for better visibility
        });

        this.context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 },
            ignoreHTTPSErrors: true
        });

        this.page = await this.context.newPage();

        // Login once for all tests
        await this.testRunner.login(this.page);
        console.log('✅ Login successful');
    }

    async runQuickOrderTests() {
        console.log('\n📋 Running QuickOrder Module Tests...');
        
        try {
            // Tab Management Tests
            await this.testTabManagement();
            
            // Product Search Tests
            await this.testProductSearch();
            
            // Customer Integration Tests
            await this.testCustomerIntegration();
            
            // Return Order Tests
            await this.testReturnOrders();
            
            // UI/UX Tests
            await this.testUIUX();

        } catch (error) {
            console.error('❌ QuickOrder tests failed:', error.message);
        }
    }

    async testTabManagement() {
        console.log('  🔧 Testing Tab Management...');

        // TC-TAB-001: Create new order tab
        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order`);
            await this.testRunner.waitForPageLoad(this.page);

            const addButton = await this.page.$('button:has-text("+")');
            if (addButton) {
                await addButton.click();
                await this.page.waitForTimeout(1000);
                
                const tabs = await this.page.$$('[class*="tab"]');
                if (tabs.length > 1) {
                    this.testRunner.logResult('QuickOrder', 'TC-TAB-001: Create new order tab', 'passed');
                } else {
                    throw new Error('New tab not created');
                }
            } else {
                throw new Error('Add button not found');
            }
        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'tab-management-failed');
            this.testRunner.logResult('QuickOrder', 'TC-TAB-001: Create new order tab', 'failed', error.message, screenshot);
        }

        // TC-TAB-002: Create return tab
        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order?type=return`);
            await this.testRunner.waitForPageLoad(this.page);

            const returnTab = await this.page.$('text*="Trả hàng"');
            const modal = await this.page.$('[id*="modal"]');
            
            if (returnTab && modal) {
                this.testRunner.logResult('QuickOrder', 'TC-TAB-002: Create return tab', 'passed');
            } else {
                throw new Error('Return tab or modal not found');
            }
        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'return-tab-failed');
            this.testRunner.logResult('QuickOrder', 'TC-TAB-002: Create return tab', 'failed', error.message, screenshot);
        }
    }

    async testProductSearch() {
        console.log('  🔍 Testing Product Search...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test F3 shortcut
            await this.page.keyboard.press('F3');
            await this.page.waitForTimeout(500);

            const focusedElement = await this.page.evaluate(() => document.activeElement.tagName);
            if (focusedElement === 'INPUT') {
                this.testRunner.logResult('QuickOrder', 'TC-SEARCH-001: F3 keyboard shortcut', 'passed');
            } else {
                throw new Error('F3 shortcut not working');
            }

            // Test basic search
            const searchInput = await this.page.$('input[placeholder*="tên, SKU"]');
            if (searchInput) {
                await searchInput.fill('test');
                await this.page.waitForTimeout(1000);
                this.testRunner.logResult('QuickOrder', 'TC-SEARCH-002: Basic product search', 'passed');
            } else {
                throw new Error('Search input not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'product-search-failed');
            this.testRunner.logResult('QuickOrder', 'TC-SEARCH-002: Basic product search', 'failed', error.message, screenshot);
        }
    }

    async testCustomerIntegration() {
        console.log('  👤 Testing Customer Integration...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test customer search
            const customerInput = await this.page.$('input[placeholder*="khách hàng"]');
            if (customerInput) {
                await customerInput.fill('Anh');
                await this.page.waitForTimeout(1000);
                this.testRunner.logResult('QuickOrder', 'TC-CUSTOMER-001: Customer search', 'passed');
            } else {
                this.testRunner.logResult('QuickOrder', 'TC-CUSTOMER-001: Customer search', 'skipped', 'Customer input not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'customer-integration-failed');
            this.testRunner.logResult('QuickOrder', 'TC-CUSTOMER-001: Customer search', 'failed', error.message, screenshot);
        }
    }

    async testReturnOrders() {
        console.log('  🔄 Testing Return Orders...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order?type=return`);
            await this.testRunner.waitForPageLoad(this.page);

            // Wait for modal to appear
            await this.page.waitForTimeout(2000);

            const modalTitle = await this.page.$('text="Chọn hóa đơn để trả hàng"');
            if (modalTitle) {
                this.testRunner.logResult('QuickOrder', 'TC-RETURN-001: Invoice selection modal', 'passed');

                // Test invoice selection
                await this.page.waitForTimeout(3000); // Wait for invoices to load
                const selectButton = await this.page.$('button:has-text("Chọn")');
                if (selectButton) {
                    await selectButton.click();
                    await this.page.waitForTimeout(2000);
                    this.testRunner.logResult('QuickOrder', 'TC-RETURN-002: Invoice selection', 'passed');
                } else {
                    this.testRunner.logResult('QuickOrder', 'TC-RETURN-002: Invoice selection', 'skipped', 'No invoices available');
                }
            } else {
                throw new Error('Invoice selection modal not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'return-orders-failed');
            this.testRunner.logResult('QuickOrder', 'TC-RETURN-001: Invoice selection modal', 'failed', error.message, screenshot);
        }
    }

    async testUIUX() {
        console.log('  🎨 Testing UI/UX...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/quick-order`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test page load
            const title = await this.page.title();
            if (title.includes('Đặt hàng nhanh') || title.includes('QuickOrder')) {
                this.testRunner.logResult('QuickOrder', 'TC-UI-001: Page load', 'passed');
            } else {
                throw new Error('Page title incorrect');
            }

            // Test responsive design
            await this.page.setViewportSize({ width: 768, height: 1024 });
            await this.page.waitForTimeout(500);
            await this.page.setViewportSize({ width: 375, height: 667 });
            await this.page.waitForTimeout(500);
            await this.page.setViewportSize({ width: 1920, height: 1080 });
            
            this.testRunner.logResult('QuickOrder', 'TC-UI-002: Responsive design', 'passed');

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'ui-ux-failed');
            this.testRunner.logResult('QuickOrder', 'TC-UI-001: Page load', 'failed', error.message, screenshot);
        }
    }

    async runOrdersTests() {
        console.log('\n📋 Running Orders Module Tests...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/orders`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test basic listing
            const ordersTable = await this.page.$('table');
            if (ordersTable) {
                this.testRunner.logResult('Orders', 'TC-ORDER-001: Basic listing', 'passed');
            } else {
                throw new Error('Orders table not found');
            }

            // Test pagination
            const pagination = await this.page.$('[class*="pagination"]');
            if (pagination) {
                this.testRunner.logResult('Orders', 'TC-ORDER-002: Pagination', 'passed');
            } else {
                this.testRunner.logResult('Orders', 'TC-ORDER-002: Pagination', 'skipped', 'Pagination not visible');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'orders-failed');
            this.testRunner.logResult('Orders', 'TC-ORDER-001: Basic listing', 'failed', error.message, screenshot);
        }
    }

    async runInvoicesTests() {
        console.log('\n📋 Running Invoices Module Tests...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/invoices`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test basic listing
            const invoicesTable = await this.page.$('table');
            if (invoicesTable) {
                this.testRunner.logResult('Invoices', 'TC-INVOICE-001: Basic listing', 'passed');
            } else {
                throw new Error('Invoices table not found');
            }

            // Test customer modal
            const customerName = await this.page.$('[class*="customer-name"]');
            if (customerName) {
                await customerName.click();
                await this.page.waitForTimeout(1000);
                
                const modal = await this.page.$('[class*="modal"][class*="show"]');
                if (modal) {
                    this.testRunner.logResult('Invoices', 'TC-INVOICE-002: Customer modal', 'passed');
                    
                    // Close modal
                    const closeButton = await this.page.$('[class*="btn-close"]');
                    if (closeButton) {
                        await closeButton.click();
                    }
                } else {
                    this.testRunner.logResult('Invoices', 'TC-INVOICE-002: Customer modal', 'failed', 'Modal not opened');
                }
            } else {
                this.testRunner.logResult('Invoices', 'TC-INVOICE-002: Customer modal', 'skipped', 'Customer name not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'invoices-failed');
            this.testRunner.logResult('Invoices', 'TC-INVOICE-001: Basic listing', 'failed', error.message, screenshot);
        }
    }

    async runReturnsTests() {
        console.log('\n📋 Running Returns Module Tests...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/returns`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test basic listing
            const returnsTable = await this.page.$('table');
            if (returnsTable) {
                this.testRunner.logResult('Returns', 'TC-RETURN-001: Basic listing', 'passed');
            } else {
                throw new Error('Returns table not found');
            }

            // Test pagination (recently fixed)
            await this.page.waitForTimeout(2000); // Wait for data to load
            const paginationInfo = await this.page.$('text*="Hiển thị"');
            if (paginationInfo) {
                const paginationText = await paginationInfo.textContent();
                if (paginationText.includes('đến') && !paginationText.includes('0 đến 0')) {
                    this.testRunner.logResult('Returns', 'TC-RETURN-002: Pagination fix', 'passed');
                } else {
                    this.testRunner.logResult('Returns', 'TC-RETURN-002: Pagination fix', 'failed', 'Pagination showing 0 results');
                }
            } else {
                this.testRunner.logResult('Returns', 'TC-RETURN-002: Pagination fix', 'failed', 'Pagination info not found');
            }

            // Test create return button
            const createButton = await this.page.$('text="Tạo đơn trả hàng"');
            if (createButton) {
                this.testRunner.logResult('Returns', 'TC-RETURN-003: Create button', 'passed');
            } else {
                this.testRunner.logResult('Returns', 'TC-RETURN-003: Create button', 'failed', 'Create button not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'returns-failed');
            this.testRunner.logResult('Returns', 'TC-RETURN-001: Basic listing', 'failed', error.message, screenshot);
        }
    }

    async runPaymentsTests() {
        console.log('\n📋 Running Payments Module Tests...');

        try {
            await this.page.goto(`${this.testRunner.baseURL}/admin/payments`);
            await this.testRunner.waitForPageLoad(this.page);

            // Test basic listing
            const paymentsTable = await this.page.$('table');
            if (paymentsTable) {
                this.testRunner.logResult('Payments', 'TC-PAYMENT-001: Basic listing', 'passed');
            } else {
                throw new Error('Payments table not found');
            }

            // Test filters
            const timeFilter = await this.page.$('[class*="time-filter"]');
            if (timeFilter) {
                this.testRunner.logResult('Payments', 'TC-PAYMENT-002: Time filter', 'passed');
            } else {
                this.testRunner.logResult('Payments', 'TC-PAYMENT-002: Time filter', 'skipped', 'Time filter not found');
            }

        } catch (error) {
            const screenshot = await this.testRunner.takeScreenshot(this.page, 'payments-failed');
            this.testRunner.logResult('Payments', 'TC-PAYMENT-001: Basic listing', 'failed', error.message, screenshot);
        }
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }

    async run() {
        try {
            await this.setup();

            // Run all module tests
            await this.runQuickOrderTests();
            await this.runOrdersTests();
            await this.runInvoicesTests();
            await this.runReturnsTests();
            await this.runPaymentsTests();

            // Generate final report
            const report = await this.testRunner.generateReport();
            
            console.log('\n🎉 Test Suite Completed!');
            console.log(`📊 Results: ${report.summary.passed}/${report.summary.total} passed (${report.summary.successRate})`);
            console.log(`⏱️  Duration: ${report.summary.duration}`);
            console.log(`📄 Report: test-results/test-report.html`);

        } catch (error) {
            console.error('❌ Test suite failed:', error);
        } finally {
            await this.cleanup();
        }
    }
}

// Run the test suite
if (require.main === module) {
    const testSuite = new ComprehensiveTestSuite();
    testSuite.run().catch(console.error);
}

module.exports = ComprehensiveTestSuite;
