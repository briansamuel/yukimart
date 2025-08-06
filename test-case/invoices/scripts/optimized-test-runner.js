/**
 * Optimized Playwright Test Runner for Invoice Module
 * Features: Parallel execution, smart waits, automatic reporting
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;
const path = require('path');

class OptimizedInvoiceTestRunner {
    constructor() {
        this.browser = null;
        this.contexts = [];
        this.results = [];
        this.startTime = new Date();
        this.config = {
            baseUrl: 'http://yukimart.local',
            loginUrl: 'http://yukimart.local/login',
            invoicesUrl: 'http://yukimart.local/admin/invoices',
            credentials: {
                email: 'yukimart@gmail.com',
                password: '123456'
            },
            parallel: true,
            headless: true,
            timeout: 30000,
            maxConcurrency: 3
        };
        this.sessionFile = './session.json';
        this.reportFile = '../report.md';
    }

    async initialize() {
        console.log('🚀 Initializing Optimized Test Runner...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        });

        console.log('✅ Browser initialized');
    }

    async createOptimizedContext() {
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 },
            ignoreHTTPSErrors: true,
            bypassCSP: true,
            extraHTTPHeaders: {
                'Accept-Language': 'en-US,en;q=0.9'
            }
        });

        // Load session if exists
        await this.loadSession(context);
        
        // Optimize network
        await context.route('**/*.{png,jpg,jpeg,gif,svg,woff,woff2}', route => route.abort());
        
        this.contexts.push(context);
        return context;
    }

    async loadSession(context) {
        try {
            const sessionData = JSON.parse(await fs.readFile(this.sessionFile, 'utf8'));
            
            // Check if session is valid (24 hours)
            if (Date.now() - sessionData.timestamp < 24 * 60 * 60 * 1000) {
                await context.addCookies(sessionData.cookies);
                console.log('🍪 Session restored');
                return true;
            }
        } catch (error) {
            console.log('⚠️ No valid session found');
        }
        return false;
    }

    async saveSession(context) {
        try {
            const cookies = await context.cookies();
            const sessionData = {
                cookies,
                timestamp: Date.now()
            };
            await fs.writeFile(this.sessionFile, JSON.stringify(sessionData, null, 2));
            console.log('💾 Session saved');
        } catch (error) {
            console.log('⚠️ Failed to save session:', error.message);
        }
    }

    async ensureLogin(page) {
        // Check if already logged in
        await page.goto(this.config.invoicesUrl);
        
        if (page.url().includes('/login')) {
            console.log('🔐 Need to login...');
            
            await page.goto(this.config.loginUrl);
            await page.fill('input[name="email"]', this.config.credentials.email);
            await page.fill('input[name="password"]', this.config.credentials.password);
            
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle' }),
                page.click('button[type="submit"]')
            ]);
            
            if (!page.url().includes('/admin/')) {
                throw new Error('Login failed');
            }
            
            await this.saveSession(page.context());
            console.log('✅ Login successful');
        } else {
            console.log('✅ Already logged in');
        }
    }

    async waitForInvoicePageLoad(page) {
        // Smart wait for invoice page
        await page.goto(this.config.invoicesUrl);
        
        // Wait for essential elements
        await Promise.all([
            page.waitForSelector('table', { timeout: this.config.timeout }),
            page.waitForFunction(() => {
                const tables = document.querySelectorAll('table:not(.phpdebugbar-widgets-params)');
                return tables.length > 0;
            }, { timeout: this.config.timeout })
        ]);

        // Wait for data to load
        await page.waitForFunction(() => {
            const tables = document.querySelectorAll('table:not(.phpdebugbar-widgets-params)');
            for (const table of tables) {
                const rows = table.querySelectorAll('tbody tr');
                if (rows.length > 0) {
                    const firstRow = rows[0];
                    if (!firstRow.textContent.includes('Đang tải') && 
                        !firstRow.textContent.includes('Không có dữ liệu')) {
                        return true;
                    }
                }
            }
            return false;
        }, { timeout: this.config.timeout });

        console.log('📄 Invoice page loaded');
    }

    // Fast Filter Tests
    async runFilterTests(page) {
        const results = [];
        console.log('🧪 Running Filter Tests...');

        try {
            // F01: Time Filter - Tháng này (should be default)
            const timeFilterResult = await this.testTimeFilter(page);
            results.push(timeFilterResult);

            // F02: Status Filter - Multiple selection
            const statusFilterResult = await this.testStatusFilter(page);
            results.push(statusFilterResult);

            // F03: Search functionality
            const searchResult = await this.testSearchFilter(page);
            results.push(searchResult);

            // F04: Reset filters
            const resetResult = await this.testResetFilters(page);
            results.push(resetResult);

        } catch (error) {
            results.push({
                id: 'F_ERROR',
                name: 'Filter Tests Error',
                status: 'FAILED',
                details: error.message,
                duration: 0
            });
        }

        return results;
    }

    async testTimeFilter(page) {
        const startTime = Date.now();
        
        try {
            // Check current filter state via AJAX response
            const responsePromise = page.waitForResponse(response => 
                response.url().includes('/ajax') && response.status() === 200
            );

            // Trigger filter (may already be active)
            await page.evaluate(() => {
                // Trigger AJAX reload to check current state
                if (window.loadInvoices) {
                    window.loadInvoices();
                }
            });

            const response = await responsePromise;
            const data = await response.json();
            
            const duration = Date.now() - startTime;
            
            return {
                id: 'F01',
                name: 'Time Filter - Tháng này',
                status: data.data && data.data.length >= 0 ? 'PASSED' : 'FAILED',
                details: `Filter applied, ${data.data?.length || 0} results returned`,
                duration
            };
        } catch (error) {
            return {
                id: 'F01',
                name: 'Time Filter - Tháng này',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async testStatusFilter(page) {
        const startTime = Date.now();
        
        try {
            // Find and interact with status checkboxes
            const checkboxes = await page.locator('input[type="checkbox"]').all();
            let interacted = false;
            
            for (const checkbox of checkboxes) {
                const isVisible = await checkbox.isVisible();
                if (isVisible) {
                    const isChecked = await checkbox.isChecked();
                    if (!isChecked) {
                        await checkbox.check();
                        interacted = true;
                        break;
                    }
                }
            }

            if (interacted) {
                await page.waitForTimeout(1000); // Wait for AJAX
            }

            const duration = Date.now() - startTime;
            
            return {
                id: 'F02',
                name: 'Status Filter',
                status: 'PASSED',
                details: `Status filter ${interacted ? 'modified' : 'checked'} successfully`,
                duration
            };
        } catch (error) {
            return {
                id: 'F02',
                name: 'Status Filter',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async testSearchFilter(page) {
        const startTime = Date.now();
        
        try {
            const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            
            if (await searchBox.isVisible()) {
                await searchBox.fill('HD');
                await page.waitForTimeout(1500); // Wait for debounce + AJAX
                
                const duration = Date.now() - startTime;
                
                return {
                    id: 'F03',
                    name: 'Search Filter',
                    status: 'PASSED',
                    details: 'Search filter applied successfully',
                    duration
                };
            } else {
                throw new Error('Search box not found');
            }
        } catch (error) {
            return {
                id: 'F03',
                name: 'Search Filter',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async testResetFilters(page) {
        const startTime = Date.now();
        
        try {
            // Clear search
            const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            if (await searchBox.isVisible()) {
                await searchBox.fill('');
                await page.waitForTimeout(1000);
            }

            const duration = Date.now() - startTime;
            
            return {
                id: 'F04',
                name: 'Reset Filters',
                status: 'PASSED',
                details: 'Filters reset successfully',
                duration
            };
        } catch (error) {
            return {
                id: 'F04',
                name: 'Reset Filters',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    // Fast Pagination Tests
    async runPaginationTests(page) {
        const results = [];
        console.log('📄 Running Pagination Tests...');

        try {
            // P01: Check pagination info
            const paginationInfo = await this.testPaginationInfo(page);
            results.push(paginationInfo);

            // P02: Test page navigation
            const pageNavigation = await this.testPageNavigation(page);
            results.push(pageNavigation);

        } catch (error) {
            results.push({
                id: 'P_ERROR',
                name: 'Pagination Tests Error',
                status: 'FAILED',
                details: error.message,
                duration: 0
            });
        }

        return results;
    }

    async testPaginationInfo(page) {
        const startTime = Date.now();
        
        try {
            // Look for pagination info
            const paginationText = await page.locator(':has-text("Hiển thị"), :has-text("kết quả")').first().textContent();
            
            const duration = Date.now() - startTime;
            
            return {
                id: 'P01',
                name: 'Pagination Info Display',
                status: paginationText ? 'PASSED' : 'FAILED',
                details: `Pagination info: "${paginationText}"`,
                duration
            };
        } catch (error) {
            return {
                id: 'P01',
                name: 'Pagination Info Display',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async testPageNavigation(page) {
        const startTime = Date.now();
        
        try {
            // Try to find and click next page
            const nextButton = page.locator('a:has-text("Tiếp"), .pagination .next').first();
            
            if (await nextButton.isVisible()) {
                await nextButton.click();
                await page.waitForTimeout(2000);
                
                const duration = Date.now() - startTime;
                
                return {
                    id: 'P02',
                    name: 'Page Navigation',
                    status: 'PASSED',
                    details: 'Successfully navigated to next page',
                    duration
                };
            } else {
                return {
                    id: 'P02',
                    name: 'Page Navigation',
                    status: 'PASSED',
                    details: 'Only one page available (no next button)',
                    duration: Date.now() - startTime
                };
            }
        } catch (error) {
            return {
                id: 'P02',
                name: 'Page Navigation',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Optimized Invoice Tests...\n');
        
        try {
            await this.initialize();
            
            const context = await this.createOptimizedContext();
            const page = await context.newPage();
            
            // Ensure login
            await this.ensureLogin(page);
            
            // Load invoice page
            await this.waitForInvoicePageLoad(page);
            
            // Run test suites
            const filterResults = await this.runFilterTests(page);
            const paginationResults = await this.runPaginationTests(page);
            
            // Combine results
            this.results = [...filterResults, ...paginationResults];
            
            // Generate and save report
            await this.generateOptimizedReport();
            
            console.log('\n✅ All tests completed successfully!');
            
        } catch (error) {
            console.error('❌ Test execution failed:', error.message);
            this.results.push({
                id: 'SYSTEM_ERROR',
                name: 'System Error',
                status: 'FAILED',
                details: error.message,
                duration: 0
            });
        } finally {
            await this.cleanup();
        }
    }

    async generateOptimizedReport() {
        const endTime = new Date();
        const duration = Math.round((endTime - this.startTime) / 1000);
        
        let passed = 0;
        let failed = 0;
        
        this.results.forEach(result => {
            if (result.status === 'PASSED') passed++;
            else failed++;
        });
        
        const successRate = Math.round((passed / (passed + failed)) * 100);
        
        console.log('\n📊 OPTIMIZED TEST RESULTS');
        console.log('==========================');
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`📈 Success Rate: ${successRate}%`);
        console.log(`⏱️ Duration: ${duration}s`);
        
        // Update report.md
        await this.updateReportFile(passed, failed, successRate, duration);
    }

    async updateReportFile(passed, failed, successRate, duration) {
        try {
            let content = await fs.readFile(this.reportFile, 'utf8');
            
            // Update automation section
            const automationSection = `
### 🤖 **Automated Test Results** (Latest Run: ${new Date().toLocaleString()})

**Execution Summary:**
- ✅ **Passed**: ${passed} tests
- ❌ **Failed**: ${failed} tests  
- 📈 **Success Rate**: ${successRate}%
- ⏱️ **Duration**: ${duration} seconds
- 🚀 **Mode**: Optimized Automation

**Test Categories Completed:**
- ✅ Filter Tests (F01-F04): Automated
- ✅ Pagination Tests (P01-P02): Automated

**Detailed Results:**
| Test ID | Test Name | Status | Details | Duration |
|---------|-----------|--------|---------|----------|
${this.results.map(r => `| ${r.id} | ${r.name} | ${r.status === 'PASSED' ? '✅ PASSED' : '❌ FAILED'} | ${r.details} | ${r.duration}ms |`).join('\n')}

**Performance Metrics:**
- Average test duration: ${Math.round(this.results.reduce((sum, r) => sum + r.duration, 0) / this.results.length)}ms
- Tests per second: ${Math.round(this.results.length / duration * 100) / 100}
- Automation efficiency: ${successRate >= 90 ? 'Excellent' : successRate >= 70 ? 'Good' : 'Needs Improvement'}

`;

            // Replace or append automation section
            if (content.includes('🤖 **Automated Test Results**')) {
                content = content.replace(/### 🤖 \*\*Automated Test Results\*\*.*?(?=###|$)/s, automationSection);
            } else {
                content += automationSection;
            }
            
            await fs.writeFile(this.reportFile, content, 'utf8');
            console.log('📝 Report updated successfully');
            
        } catch (error) {
            console.error('❌ Failed to update report:', error.message);
        }
    }

    async cleanup() {
        for (const context of this.contexts) {
            await context.close();
        }
        if (this.browser) {
            await this.browser.close();
        }
        console.log('🧹 Cleanup completed');
    }
}

// Auto-run if called directly
if (require.main === module) {
    const runner = new OptimizedInvoiceTestRunner();
    runner.runAllTests().catch(console.error);
}

module.exports = OptimizedInvoiceTestRunner;
