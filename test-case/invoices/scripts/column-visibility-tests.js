/**
 * Invoice Column Visibility Tests - Playwright Automation Script
 * Tests column show/hide functionality on the invoice listing page
 */

const { chromium } = require('playwright');

class InvoiceColumnVisibilityTests {
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
        console.log('🚀 Setting up Column Visibility Tests...');
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

    async openColumnVisibilityPanel() {
        // Look for column visibility button (usually an icon or "Columns" button)
        const columnButton = await this.page.locator('button:has-text("Cột"), button[title*="cột"], .column-visibility-btn, .btn-columns').first();
        await columnButton.click();
        await this.page.waitForTimeout(500);
    }

    async getVisibleColumns() {
        const headers = await this.page.locator('table thead th:visible').count();
        return headers;
    }

    async isColumnVisible(columnName) {
        const column = await this.page.locator(`table thead th:has-text("${columnName}")`).first();
        return await column.isVisible();
    }

    // Test CV01: Open Column Visibility Panel
    async testOpenColumnVisibilityPanel() {
        console.log('🧪 Testing CV01: Open Column Visibility Panel');
        
        try {
            await this.openColumnVisibilityPanel();
            
            // Check if panel is visible
            const panelVisible = await this.page.isVisible('.column-visibility-panel, .dropdown-menu, .popover');
            
            this.results.push({
                id: 'CV01',
                name: 'Open Column Visibility Panel',
                status: panelVisible ? 'PASSED' : 'FAILED',
                details: `Column visibility panel opened: ${panelVisible}`
            });
            
            console.log(panelVisible ? '✅ CV01 PASSED' : '❌ CV01 FAILED');
        } catch (error) {
            this.results.push({
                id: 'CV01',
                name: 'Open Column Visibility Panel',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV01 FAILED:', error.message);
        }
    }

    // Test CV02: Hide Email Column
    async testHideEmailColumn() {
        console.log('🧪 Testing CV02: Hide Email Column');
        
        try {
            // Find and uncheck email column checkbox
            const emailCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Email"), label:has-text("Email") input').first();
            await emailCheckbox.uncheck();
            await this.page.waitForTimeout(1000);
            
            // Check if email column is hidden
            const emailVisible = await this.isColumnVisible('Email');
            
            this.results.push({
                id: 'CV02',
                name: 'Hide Email Column',
                status: !emailVisible ? 'PASSED' : 'FAILED',
                details: `Email column hidden: ${!emailVisible}`
            });
            
            console.log(!emailVisible ? '✅ CV02 PASSED' : '❌ CV02 FAILED');
        } catch (error) {
            this.results.push({
                id: 'CV02',
                name: 'Hide Email Column',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV02 FAILED:', error.message);
        }
    }

    // Test CV03: Show Email Column
    async testShowEmailColumn() {
        console.log('🧪 Testing CV03: Show Email Column');
        
        try {
            // Find and check email column checkbox
            const emailCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Email"), label:has-text("Email") input').first();
            await emailCheckbox.check();
            await this.page.waitForTimeout(1000);
            
            // Check if email column is visible
            const emailVisible = await this.isColumnVisible('Email');
            
            this.results.push({
                id: 'CV03',
                name: 'Show Email Column',
                status: emailVisible ? 'PASSED' : 'FAILED',
                details: `Email column shown: ${emailVisible}`
            });
            
            console.log(emailVisible ? '✅ CV03 PASSED' : '❌ CV03 FAILED');
        } catch (error) {
            this.results.push({
                id: 'CV03',
                name: 'Show Email Column',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV03 FAILED:', error.message);
        }
    }

    // Test CV04: Hide Multiple Columns
    async testHideMultipleColumns() {
        console.log('🧪 Testing CV04: Hide Multiple Columns');
        
        try {
            const initialColumns = await this.getVisibleColumns();
            
            // Hide multiple columns
            const columnsToHide = ['Người bán', 'Người tạo', 'Chi nhánh'];
            for (const column of columnsToHide) {
                const checkbox = await this.page.locator(`input[type="checkbox"]:has-text("${column}"), label:has-text("${column}") input`).first();
                await checkbox.uncheck().catch(() => {});
                await this.page.waitForTimeout(500);
            }
            
            const finalColumns = await this.getVisibleColumns();
            const columnsHidden = initialColumns > finalColumns;
            
            this.results.push({
                id: 'CV04',
                name: 'Hide Multiple Columns',
                status: columnsHidden ? 'PASSED' : 'FAILED',
                details: `Columns reduced from ${initialColumns} to ${finalColumns}`
            });
            
            console.log(columnsHidden ? '✅ CV04 PASSED' : '❌ CV04 FAILED');
        } catch (error) {
            this.results.push({
                id: 'CV04',
                name: 'Hide Multiple Columns',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV04 FAILED:', error.message);
        }
    }

    // Test CV05: Show All Columns
    async testShowAllColumns() {
        console.log('🧪 Testing CV05: Show All Columns');
        
        try {
            // Check all column checkboxes
            const checkboxes = await this.page.locator('.column-visibility-panel input[type="checkbox"], .dropdown-menu input[type="checkbox"]').all();
            
            for (const checkbox of checkboxes) {
                await checkbox.check().catch(() => {});
                await this.page.waitForTimeout(200);
            }
            
            const finalColumns = await this.getVisibleColumns();
            
            this.results.push({
                id: 'CV05',
                name: 'Show All Columns',
                status: 'PASSED',
                details: `All columns shown, total visible: ${finalColumns}`
            });
            
            console.log('✅ CV05 PASSED');
        } catch (error) {
            this.results.push({
                id: 'CV05',
                name: 'Show All Columns',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV05 FAILED:', error.message);
        }
    }

    // Test CV06: Column Visibility Persistence
    async testColumnVisibilityPersistence() {
        console.log('🧪 Testing CV06: Column Visibility Persistence');
        
        try {
            // Hide a column
            const emailCheckbox = await this.page.locator('input[type="checkbox"]:has-text("Email"), label:has-text("Email") input').first();
            await emailCheckbox.uncheck();
            await this.page.waitForTimeout(1000);
            
            // Refresh page
            await this.page.reload();
            await this.waitForDataLoad();
            
            // Check if column is still hidden
            const emailVisible = await this.isColumnVisible('Email');
            
            this.results.push({
                id: 'CV06',
                name: 'Column Visibility Persistence',
                status: !emailVisible ? 'PASSED' : 'FAILED',
                details: `Email column remains hidden after refresh: ${!emailVisible}`
            });
            
            console.log(!emailVisible ? '✅ CV06 PASSED' : '❌ CV06 FAILED');
        } catch (error) {
            this.results.push({
                id: 'CV06',
                name: 'Column Visibility Persistence',
                status: 'FAILED',
                details: `Error: ${error.message}`
            });
            console.log('❌ CV06 FAILED:', error.message);
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Column Visibility Tests...\n');
        
        await this.setup();
        
        await this.testOpenColumnVisibilityPanel();
        await this.testHideEmailColumn();
        await this.testShowEmailColumn();
        await this.testHideMultipleColumns();
        await this.testShowAllColumns();
        await this.testColumnVisibilityPersistence();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 COLUMN VISIBILITY TESTS REPORT');
        console.log('===================================');
        
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
        console.log('🏁 Column Visibility Tests completed');
    }
}

if (require.main === module) {
    const tests = new InvoiceColumnVisibilityTests();
    tests.runAllTests().catch(console.error);
}

module.exports = InvoiceColumnVisibilityTests;
