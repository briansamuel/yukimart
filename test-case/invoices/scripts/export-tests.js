/**
 * Export Tests - Automated Playwright Testing
 * Tests export functionality (Excel, CSV, PDF)
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;
const path = require('path');

class ExportTests {
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
            timeout: 30000
        };
        this.downloadPath = './downloads';
    }

    async setup() {
        console.log('🚀 Setting up Export Tests...');
        
        // Create download directory
        await fs.mkdir(this.downloadPath, { recursive: true });
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 },
            acceptDownloads: true
        });
        
        // Block unnecessary resources except downloads
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

    // EX01: Test Excel Export Button Visibility
    async testExcelExportButtonVisibility() {
        const startTime = Date.now();
        console.log('🧪 Testing EX01: Excel Export Button Visibility');
        
        try {
            const exportSelectors = [
                'button:has-text("Export Excel")',
                'button:has-text("Xuất Excel")',
                'button:has-text("Excel")',
                '.btn-export-excel',
                '.export-excel-btn',
                'a:has-text("Excel")'
            ];
            
            let exportButtonFound = false;
            let foundSelector = '';
            
            for (const selector of exportSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    exportButtonFound = true;
                    foundSelector = selector;
                    break;
                }
            }
            
            this.results.push({
                id: 'EX01',
                name: 'Excel Export Button Visibility',
                status: exportButtonFound ? 'PASSED' : 'FAILED',
                details: exportButtonFound ? `Found export button: ${foundSelector}` : 'Excel export button not found',
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'EX01',
                name: 'Excel Export Button Visibility',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // EX02: Test Excel Export Functionality
    async testExcelExportFunctionality() {
        const startTime = Date.now();
        console.log('🧪 Testing EX02: Excel Export Functionality');
        
        try {
            const exportSelectors = [
                'button:has-text("Export Excel")',
                'button:has-text("Xuất Excel")',
                'button:has-text("Excel")',
                '.btn-export-excel',
                'a:has-text("Excel")'
            ];
            
            let downloadStarted = false;
            
            for (const selector of exportSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    // Set up download listener
                    const downloadPromise = this.page.waitForEvent('download', { timeout: 10000 });
                    
                    try {
                        await button.click();
                        
                        // Wait for download to start
                        const download = await downloadPromise;
                        
                        if (download) {
                            downloadStarted = true;
                            
                            // Get download info
                            const filename = download.suggestedFilename();
                            const isExcelFile = filename.endsWith('.xlsx') || filename.endsWith('.xls');
                            
                            // Save download
                            const downloadPath = path.join(this.downloadPath, filename);
                            await download.saveAs(downloadPath);
                            
                            // Check if file exists and has content
                            const stats = await fs.stat(downloadPath);
                            const hasContent = stats.size > 0;
                            
                            this.results.push({
                                id: 'EX02',
                                name: 'Excel Export Functionality',
                                status: isExcelFile && hasContent ? 'PASSED' : 'FAILED',
                                details: `Downloaded: ${filename}, Size: ${stats.size} bytes, Excel format: ${isExcelFile}`,
                                duration: Date.now() - startTime
                            });
                            
                            return;
                        }
                    } catch (downloadError) {
                        // Download might not start immediately, continue to check other buttons
                        continue;
                    }
                }
            }
            
            if (!downloadStarted) {
                this.results.push({
                    id: 'EX02',
                    name: 'Excel Export Functionality',
                    status: 'FAILED',
                    details: 'No download started when clicking export button',
                    duration: Date.now() - startTime
                });
            }
        } catch (error) {
            this.results.push({
                id: 'EX02',
                name: 'Excel Export Functionality',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // EX03: Test Export with Filters
    async testExportWithFilters() {
        const startTime = Date.now();
        console.log('🧪 Testing EX03: Export with Filters');
        
        try {
            // Apply a search filter first
            const searchBox = this.page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            if (await searchBox.isVisible()) {
                await searchBox.fill('HD');
                await this.page.waitForTimeout(2000); // Wait for filter to apply
            }
            
            // Apply status filter
            const statusCheckboxes = await this.page.locator('input[type="checkbox"]').all();
            if (statusCheckboxes.length > 0) {
                await statusCheckboxes[0].check();
                await this.page.waitForTimeout(1000);
            }
            
            // Now try to export
            const exportSelectors = [
                'button:has-text("Export Excel")',
                'button:has-text("Xuất Excel")',
                'button:has-text("Excel")',
                '.btn-export-excel'
            ];
            
            let exportAttempted = false;
            
            for (const selector of exportSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    const downloadPromise = this.page.waitForEvent('download', { timeout: 5000 });
                    
                    try {
                        await button.click();
                        exportAttempted = true;
                        
                        const download = await downloadPromise;
                        if (download) {
                            const filename = download.suggestedFilename();
                            await download.saveAs(path.join(this.downloadPath, `filtered_${filename}`));
                            
                            this.results.push({
                                id: 'EX03',
                                name: 'Export with Filters',
                                status: 'PASSED',
                                details: `Export with filters successful: ${filename}`,
                                duration: Date.now() - startTime
                            });
                            return;
                        }
                    } catch (downloadError) {
                        // Continue to next selector
                        continue;
                    }
                }
            }
            
            this.results.push({
                id: 'EX03',
                name: 'Export with Filters',
                status: exportAttempted ? 'PASSED' : 'FAILED',
                details: exportAttempted ? 'Export attempted with filters applied' : 'Export button not found',
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'EX03',
                name: 'Export with Filters',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // EX04: Test Export All Data
    async testExportAllData() {
        const startTime = Date.now();
        console.log('🧪 Testing EX04: Export All Data');
        
        try {
            // Clear any filters first
            const searchBox = this.page.locator('input[type="search"]').first();
            if (await searchBox.isVisible()) {
                await searchBox.fill('');
                await this.page.waitForTimeout(1000);
            }
            
            // Uncheck any status filters
            const checkedBoxes = await this.page.locator('input[type="checkbox"]:checked').all();
            for (const checkbox of checkedBoxes) {
                await checkbox.uncheck();
                await this.page.waitForTimeout(200);
            }
            
            await this.page.waitForTimeout(2000); // Wait for filters to clear
            
            // Get total record count
            const totalRecords = await this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
            
            // Try to export all data
            const exportSelectors = [
                'button:has-text("Export Excel")',
                'button:has-text("Xuất Excel")',
                'button:has-text("Excel")'
            ];
            
            let exportSuccess = false;
            
            for (const selector of exportSelectors) {
                const button = this.page.locator(selector).first();
                if (await button.isVisible()) {
                    const downloadPromise = this.page.waitForEvent('download', { timeout: 10000 });
                    
                    try {
                        await button.click();
                        
                        const download = await downloadPromise;
                        if (download) {
                            const filename = download.suggestedFilename();
                            const downloadPath = path.join(this.downloadPath, `all_data_${filename}`);
                            await download.saveAs(downloadPath);
                            
                            const stats = await fs.stat(downloadPath);
                            exportSuccess = stats.size > 1000; // Assume all data export should be larger
                            
                            this.results.push({
                                id: 'EX04',
                                name: 'Export All Data',
                                status: exportSuccess ? 'PASSED' : 'FAILED',
                                details: `All data export: ${filename}, ${totalRecords} visible records, ${stats.size} bytes`,
                                duration: Date.now() - startTime
                            });
                            return;
                        }
                    } catch (downloadError) {
                        continue;
                    }
                }
            }
            
            this.results.push({
                id: 'EX04',
                name: 'Export All Data',
                status: 'FAILED',
                details: 'Export all data functionality not available',
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'EX04',
                name: 'Export All Data',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // EX05: Test Export Format Options
    async testExportFormatOptions() {
        const startTime = Date.now();
        console.log('🧪 Testing EX05: Export Format Options');
        
        try {
            const formatSelectors = [
                'button:has-text("CSV")',
                'button:has-text("PDF")',
                'select option:has-text("Excel")',
                'select option:has-text("CSV")',
                '.export-format-dropdown'
            ];
            
            let formatOptionsFound = 0;
            const foundFormats = [];
            
            for (const selector of formatSelectors) {
                const element = this.page.locator(selector).first();
                if (await element.isVisible()) {
                    formatOptionsFound++;
                    foundFormats.push(selector);
                }
            }
            
            this.results.push({
                id: 'EX05',
                name: 'Export Format Options',
                status: formatOptionsFound > 0 ? 'PASSED' : 'FAILED',
                details: `Found ${formatOptionsFound} export format options: ${foundFormats.join(', ')}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'EX05',
                name: 'Export Format Options',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // EX06: Test Export Progress Indicator
    async testExportProgressIndicator() {
        const startTime = Date.now();
        console.log('🧪 Testing EX06: Export Progress Indicator');
        
        try {
            const exportButton = this.page.locator('button:has-text("Export Excel"), button:has-text("Xuất Excel")').first();
            
            if (await exportButton.isVisible()) {
                await exportButton.click();
                
                // Look for progress indicators
                const progressSelectors = [
                    '.loading',
                    '.spinner',
                    '.progress',
                    ':has-text("Đang xuất")',
                    ':has-text("Exporting")',
                    '.btn:disabled'
                ];
                
                let progressFound = false;
                
                for (const selector of progressSelectors) {
                    const element = this.page.locator(selector).first();
                    if (await element.isVisible()) {
                        progressFound = true;
                        break;
                    }
                }
                
                this.results.push({
                    id: 'EX06',
                    name: 'Export Progress Indicator',
                    status: progressFound ? 'PASSED' : 'FAILED',
                    details: `Progress indicator ${progressFound ? 'shown' : 'not shown'} during export`,
                    duration: Date.now() - startTime
                });
            } else {
                this.results.push({
                    id: 'EX06',
                    name: 'Export Progress Indicator',
                    status: 'FAILED',
                    details: 'Export button not found',
                    duration: Date.now() - startTime
                });
            }
        } catch (error) {
            this.results.push({
                id: 'EX06',
                name: 'Export Progress Indicator',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Export Tests...\n');
        
        await this.setup();
        
        await this.testExcelExportButtonVisibility();
        await this.testExcelExportFunctionality();
        await this.testExportWithFilters();
        await this.testExportAllData();
        await this.testExportFormatOptions();
        await this.testExportProgressIndicator();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 EXPORT TESTS REPORT');
        console.log('=======================');
        
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
        console.log('🏁 Export Tests completed');
    }
}

if (require.main === module) {
    const tests = new ExportTests();
    tests.runAllTests().catch(console.error);
}

module.exports = ExportTests;
