/**
 * Invoice Row Expansion Tests - Playwright Automation Script
 * Tests row click expansion and detail panel functionality
 */

const { chromium } = require('playwright');

class InvoiceRowExpansionTests {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.baseUrl = 'http://yukimart.local/admin/invoices';
        this.credentials = {
            email: 'yukimart@gmail.com',
            password: '123456'
        };
    }

    async setup() {
        console.log('🚀 Setting up Row Expansion Tests...');
        this.browser = await chromium.launch({ headless: false });
        this.page = await this.browser.newPage();
        
        await this.login();
        await this.page.goto(this.baseUrl);
        await this.page.waitForLoadState('networkidle');
        await this.waitForDataLoad();
        
        console.log('✅ Setup completed');
    }

    async login() {
        await this.page.goto('http://yukimart.local/admin/login');
        await this.page.fill('input[name="email"]', this.credentials.email);
        await this.page.fill('input[name="password"]', this.credentials.password);
        await this.page.click('button[type="submit"]');
        await this.page.waitForLoadState('networkidle');
    }

    async waitForDataLoad() {
        await this.page.waitForSelector('table tbody tr', { timeout: 10000 });
        await this.page.waitForTimeout(2000);
    }

    async getFirstRowInvoiceId() {
        const firstRow = await this.page.locator('table tbody tr').first();
        const invoiceIdCell = await firstRow.locator('td').first();
        return await invoiceIdCell.textContent();
    }

    // Test RE01: Click Row to Expand
    async testClickRowToExpand() {
        console.log('🧪 Testing RE01: Click Row to Expand');
        
        try {
            const invoiceId = await this.getFirstRowInvoiceId();
            
            // Click first row
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check if detail panel appears
            const detailPanelVisible = await this.page.isVisible('.invoice-detail-panel, .detail-panel, .expanded-row');
            
            this.results.push({
                id: 'RE01',
                name: 'Click Row to Expand',
                status: detailPanelVisible ? 'PASSED' : 'FAILED',
                details: `Clicked invoice ${invoiceId}, detail panel visible: ${detailPanelVisible}`
            });
            
            console.log(detailPanelVisible ? '✅ RE01 PASSED' : '❌ RE01 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE01',
                name: 'Click Row to Expand',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE01 FAILED:', error.message);
        }
    }

    // Test RE02: Detail Panel Content
    async testDetailPanelContent() {
        console.log('🧪 Testing RE02: Detail Panel Content');
        
        try {
            // Ensure a row is expanded
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check for detail panel tabs
            const tabs = await this.page.locator('.nav-tabs li, .tab-nav li, .detail-tabs li').count();
            
            // Check for invoice information
            const hasInvoiceInfo = await this.page.isVisible(':has-text("Thông tin hóa đơn"), :has-text("Invoice Information")');
            
            // Check for payment history
            const hasPaymentHistory = await this.page.isVisible(':has-text("Lịch sử thanh toán"), :has-text("Payment History")');
            
            const contentLoaded = tabs > 0 || hasInvoiceInfo || hasPaymentHistory;
            
            this.results.push({
                id: 'RE02',
                name: 'Detail Panel Content',
                status: contentLoaded ? 'PASSED' : 'FAILED',
                details: `Tabs: ${tabs}, Invoice info: ${hasInvoiceInfo}, Payment history: ${hasPaymentHistory}`
            });
            
            console.log(contentLoaded ? '✅ RE02 PASSED' : '❌ RE02 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE02',
                name: 'Detail Panel Content',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE02 FAILED:', error.message);
        }
    }

    // Test RE03: Switch Between Tabs
    async testSwitchBetweenTabs() {
        console.log('🧪 Testing RE03: Switch Between Tabs');
        
        try {
            // Ensure a row is expanded
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Find and click payment history tab
            const paymentTab = await this.page.locator('a:has-text("Lịch sử thanh toán"), .nav-link:has-text("Payment")').first();
            await paymentTab.click();
            await this.page.waitForTimeout(1000);
            
            // Check if payment content is visible
            const paymentContentVisible = await this.page.isVisible('.payment-history, .payment-table, :has-text("Thanh toán")');
            
            // Switch back to invoice info tab
            const infoTab = await this.page.locator('a:has-text("Thông tin hóa đơn"), .nav-link:has-text("Invoice")').first();
            await infoTab.click();
            await this.page.waitForTimeout(1000);
            
            // Check if invoice content is visible
            const invoiceContentVisible = await this.page.isVisible('.invoice-info, .invoice-details, :has-text("Mã hóa đơn")');
            
            const tabSwitchWorking = paymentContentVisible || invoiceContentVisible;
            
            this.results.push({
                id: 'RE03',
                name: 'Switch Between Tabs',
                status: tabSwitchWorking ? 'PASSED' : 'FAILED',
                details: `Payment tab: ${paymentContentVisible}, Invoice tab: ${invoiceContentVisible}`
            });
            
            console.log(tabSwitchWorking ? '✅ RE03 PASSED' : '❌ RE03 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE03',
                name: 'Switch Between Tabs',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE03 FAILED:', error.message);
        }
    }

    // Test RE04: Collapse Row
    async testCollapseRow() {
        console.log('🧪 Testing RE04: Collapse Row');
        
        try {
            // Ensure a row is expanded
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Click the same row again to collapse
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check if detail panel is hidden
            const detailPanelVisible = await this.page.isVisible('.invoice-detail-panel, .detail-panel, .expanded-row');
            
            this.results.push({
                id: 'RE04',
                name: 'Collapse Row',
                status: !detailPanelVisible ? 'PASSED' : 'FAILED',
                details: `Detail panel collapsed: ${!detailPanelVisible}`
            });
            
            console.log(!detailPanelVisible ? '✅ RE04 PASSED' : '❌ RE04 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE04',
                name: 'Collapse Row',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE04 FAILED:', error.message);
        }
    }

    // Test RE05: Expand Different Row
    async testExpandDifferentRow() {
        console.log('🧪 Testing RE05: Expand Different Row');
        
        try {
            // Click first row
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            const firstInvoiceId = await this.getFirstRowInvoiceId();
            
            // Click second row
            const secondRow = await this.page.locator('table tbody tr').nth(1);
            await secondRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check if detail panel is still visible (should show second row's details)
            const detailPanelVisible = await this.page.isVisible('.invoice-detail-panel, .detail-panel, .expanded-row');
            
            this.results.push({
                id: 'RE05',
                name: 'Expand Different Row',
                status: detailPanelVisible ? 'PASSED' : 'FAILED',
                details: `Switched from first to second row, detail panel visible: ${detailPanelVisible}`
            });
            
            console.log(detailPanelVisible ? '✅ RE05 PASSED' : '❌ RE05 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE05',
                name: 'Expand Different Row',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE05 FAILED:', error.message);
        }
    }

    // Test RE06: Detail Panel Position
    async testDetailPanelPosition() {
        console.log('🧪 Testing RE06: Detail Panel Position');
        
        try {
            // Click first row
            const firstRow = await this.page.locator('table tbody tr').first();
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check if detail panel appears below the clicked row
            const detailPanel = await this.page.locator('.invoice-detail-panel, .detail-panel, .expanded-row').first();
            const detailPanelBox = await detailPanel.boundingBox();
            const firstRowBox = await firstRow.boundingBox();
            
            const positionCorrect = detailPanelBox && firstRowBox && detailPanelBox.y > firstRowBox.y;
            
            this.results.push({
                id: 'RE06',
                name: 'Detail Panel Position',
                status: positionCorrect ? 'PASSED' : 'FAILED',
                details: `Detail panel positioned below clicked row: ${positionCorrect}`
            });
            
            console.log(positionCorrect ? '✅ RE06 PASSED' : '❌ RE06 FAILED');
        } catch (error) {
            this.results.push({
                id: 'RE06',
                name: 'Detail Panel Position',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ RE06 FAILED:', error.message);
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Row Expansion Tests...\n');
        
        await this.setup();
        
        await this.testClickRowToExpand();
        await this.testDetailPanelContent();
        await this.testSwitchBetweenTabs();
        await this.testCollapseRow();
        await this.testExpandDifferentRow();
        await this.testDetailPanelPosition();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 ROW EXPANSION TESTS REPORT');
        console.log('==============================');
        
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
        return { passed, failed, total: passed + failed, results: this.results };
    }

    async teardown() {
        if (this.browser) {
            await this.browser.close();
        }
        console.log('🏁 Row Expansion Tests completed');
    }
}

if (require.main === module) {
    const tests = new InvoiceRowExpansionTests();
    tests.runAllTests().catch(console.error);
}

module.exports = InvoiceRowExpansionTests;
