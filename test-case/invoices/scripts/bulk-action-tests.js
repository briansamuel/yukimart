/**
 * Bulk Action Tests - Automated Playwright Testing
 * Tests bulk selection and bulk operations functionality
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;

class BulkActionTests {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.config = {
            baseUrl: 'http://yukimart.local',
            loginUrl: 'http://yukimart.local/login',
            invoicesUrl: 'http://yukimart.local/admin/invoices',
            credentials: {
                email: 'yukimart@gmail.com',
                password: '123456'
            },
            headless: true,
            timeout: 20000
        };
        this.sessionFile = './session.json';
    }

    async setup() {
        console.log('🚀 Setting up Bulk Action Tests...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        // Block unnecessary resources
        await context.route('**/*.{png,jpg,jpeg,gif,svg,woff,woff2}', route => route.abort());
        
        this.page = await context.newPage();
        
        await this.ensureLogin();
        await this.waitForPageLoad();
        
        console.log('✅ Setup completed');
    }

    async ensureLogin() {
        await this.page.goto(this.config.invoicesUrl);
        
        if (this.page.url().includes('/login')) {
            await this.page.goto(this.config.loginUrl);
            await this.page.fill('input[name="email"]', this.config.credentials.email);
            await this.page.fill('input[name="password"]', this.config.credentials.password);
            
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle' }),
                this.page.click('button[type="submit"]')
            ]);
        }
    }

    async waitForPageLoad() {
        await this.page.goto(this.config.invoicesUrl);
        await this.page.waitForSelector('table', { timeout: this.config.timeout });
        await this.page.waitForFunction(() => {
            const table = document.querySelector('table:not(.phpdebugbar-widgets-params)');
            return table && table.querySelector('tbody tr');
        }, { timeout: this.config.timeout });
    }

    // BA01: Test Select All Checkbox
    async testSelectAllCheckbox() {
        const startTime = Date.now();
        console.log('🧪 Testing BA01: Select All Checkbox');
        
        try {
            // Find select all checkbox in header
            const selectAllCheckbox = this.page.locator('thead input[type="checkbox"], th input[type="checkbox"]').first();
            
            if (await selectAllCheckbox.isVisible()) {
                // Click select all
                await selectAllCheckbox.check();
                await this.page.waitForTimeout(1000);
                
                // Check if individual checkboxes are selected
                const individualCheckboxes = await this.page.locator('tbody input[type="checkbox"]').count();
                const checkedCheckboxes = await this.page.locator('tbody input[type="checkbox"]:checked').count();
                
                const allSelected = individualCheckboxes > 0 && checkedCheckboxes === individualCheckboxes;
                
                this.results.push({
                    id: 'BA01',
                    name: 'Select All Checkbox',
                    status: allSelected ? 'PASSED' : 'FAILED',
                    details: `Selected ${checkedCheckboxes}/${individualCheckboxes} checkboxes`,
                    duration: Date.now() - startTime
                });
            } else {
                this.results.push({
                    id: 'BA01',
                    name: 'Select All Checkbox',
                    status: 'FAILED',
                    details: 'Select all checkbox not found',
                    duration: Date.now() - startTime
                });
            }
        } catch (error) {
            this.results.push({
                id: 'BA01',
                name: 'Select All Checkbox',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // BA02: Test Individual Checkbox Selection
    async testIndividualCheckboxSelection() {
        const startTime = Date.now();
        console.log('🧪 Testing BA02: Individual Checkbox Selection');
        
        try {
            // Uncheck select all first
            const selectAllCheckbox = this.page.locator('thead input[type="checkbox"]').first();
            if (await selectAllCheckbox.isVisible() && await selectAllCheckbox.isChecked()) {
                await selectAllCheckbox.uncheck();
                await this.page.waitForTimeout(500);
            }
            
            // Select individual checkboxes
            const individualCheckboxes = await this.page.locator('tbody input[type="checkbox"]').all();
            let selectedCount = 0;
            
            for (let i = 0; i < Math.min(3, individualCheckboxes.length); i++) {
                await individualCheckboxes[i].check();
                selectedCount++;
                await this.page.waitForTimeout(200);
            }
            
            // Verify selection
            const checkedCount = await this.page.locator('tbody input[type="checkbox"]:checked').count();
            
            this.results.push({
                id: 'BA02',
                name: 'Individual Checkbox Selection',
                status: checkedCount === selectedCount ? 'PASSED' : 'FAILED',
                details: `Selected ${checkedCount} individual checkboxes`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'BA02',
                name: 'Individual Checkbox Selection',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // BA03: Test Bulk Action Button Visibility
    async testBulkActionButtonVisibility() {
        const startTime = Date.now();
        console.log('🧪 Testing BA03: Bulk Action Button Visibility');
        
        try {
            // Ensure some checkboxes are selected
            const firstCheckbox = this.page.locator('tbody input[type="checkbox"]').first();
            await firstCheckbox.check();
            await this.page.waitForTimeout(1000);
            
            // Look for bulk action buttons
            const bulkActionSelectors = [
                'button:has-text("Bulk")',
                'button:has-text("Hàng loạt")',
                '.bulk-action-btn',
                '.btn-bulk',
                'button:has-text("Actions")',
                'button:has-text("Thao tác")'
            ];
            
            let bulkButtonFound = false;
            for (const selector of bulkActionSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    bulkButtonFound = true;
                    break;
                }
            }
            
            this.results.push({
                id: 'BA03',
                name: 'Bulk Action Button Visibility',
                status: bulkButtonFound ? 'PASSED' : 'FAILED',
                details: `Bulk action button ${bulkButtonFound ? 'found' : 'not found'} when items selected`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'BA03',
                name: 'Bulk Action Button Visibility',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // BA04: Test Bulk Status Update
    async testBulkStatusUpdate() {
        const startTime = Date.now();
        console.log('🧪 Testing BA04: Bulk Status Update');
        
        try {
            // Select multiple items
            const checkboxes = await this.page.locator('tbody input[type="checkbox"]').all();
            for (let i = 0; i < Math.min(2, checkboxes.length); i++) {
                await checkboxes[i].check();
                await this.page.waitForTimeout(200);
            }
            
            // Look for bulk action dropdown or button
            const bulkActionSelectors = [
                'button:has-text("Bulk")',
                'button:has-text("Hàng loạt")',
                '.bulk-action-btn',
                'select.bulk-action'
            ];
            
            let actionPerformed = false;
            for (const selector of bulkActionSelectors) {
                const element = this.page.locator(selector).first();
                if (await element.isVisible()) {
                    await element.click();
                    await this.page.waitForTimeout(1000);
                    
                    // Look for status update options
                    const statusOptions = [
                        'text="Update Status"',
                        'text="Cập nhật trạng thái"',
                        'text="Processing"',
                        'text="Đang xử lý"'
                    ];
                    
                    for (const option of statusOptions) {
                        const optionElement = this.page.locator(option).first();
                        if (await optionElement.isVisible()) {
                            await optionElement.click();
                            actionPerformed = true;
                            break;
                        }
                    }
                    break;
                }
            }
            
            this.results.push({
                id: 'BA04',
                name: 'Bulk Status Update',
                status: actionPerformed ? 'PASSED' : 'FAILED',
                details: `Bulk status update ${actionPerformed ? 'performed' : 'not available'}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'BA04',
                name: 'Bulk Status Update',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // BA05: Test Bulk Export
    async testBulkExport() {
        const startTime = Date.now();
        console.log('🧪 Testing BA05: Bulk Export');
        
        try {
            // Select items for export
            const checkboxes = await this.page.locator('tbody input[type="checkbox"]').all();
            for (let i = 0; i < Math.min(3, checkboxes.length); i++) {
                await checkboxes[i].check();
                await this.page.waitForTimeout(200);
            }
            
            // Look for export button
            const exportSelectors = [
                'button:has-text("Export")',
                'button:has-text("Xuất")',
                'button:has-text("Excel")',
                '.btn-export',
                '.export-btn'
            ];
            
            let exportFound = false;
            for (const selector of exportSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    exportFound = true;
                    // Don't actually click to avoid download
                    break;
                }
            }
            
            this.results.push({
                id: 'BA05',
                name: 'Bulk Export',
                status: exportFound ? 'PASSED' : 'FAILED',
                details: `Export functionality ${exportFound ? 'available' : 'not found'}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'BA05',
                name: 'Bulk Export',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // BA06: Test Deselect All
    async testDeselectAll() {
        const startTime = Date.now();
        console.log('🧪 Testing BA06: Deselect All');
        
        try {
            // First select all
            const selectAllCheckbox = this.page.locator('thead input[type="checkbox"]').first();
            if (await selectAllCheckbox.isVisible()) {
                await selectAllCheckbox.check();
                await this.page.waitForTimeout(1000);
                
                // Then deselect all
                await selectAllCheckbox.uncheck();
                await this.page.waitForTimeout(1000);
                
                // Verify all are deselected
                const checkedCount = await this.page.locator('tbody input[type="checkbox"]:checked').count();
                
                this.results.push({
                    id: 'BA06',
                    name: 'Deselect All',
                    status: checkedCount === 0 ? 'PASSED' : 'FAILED',
                    details: `${checkedCount} checkboxes remain selected after deselect all`,
                    duration: Date.now() - startTime
                });
            } else {
                this.results.push({
                    id: 'BA06',
                    name: 'Deselect All',
                    status: 'FAILED',
                    details: 'Select all checkbox not found',
                    duration: Date.now() - startTime
                });
            }
        } catch (error) {
            this.results.push({
                id: 'BA06',
                name: 'Deselect All',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Bulk Action Tests...\n');
        
        await this.setup();
        
        await this.testSelectAllCheckbox();
        await this.testIndividualCheckboxSelection();
        await this.testBulkActionButtonVisibility();
        await this.testBulkStatusUpdate();
        await this.testBulkExport();
        await this.testDeselectAll();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 BULK ACTION TESTS REPORT');
        console.log('============================');
        
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
        console.log('🏁 Bulk Action Tests completed');
    }
}

if (require.main === module) {
    const tests = new BulkActionTests();
    tests.runAllTests().catch(console.error);
}

module.exports = BulkActionTests;
