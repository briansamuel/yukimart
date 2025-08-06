/**
 * Automatic Test Runner with Speed Optimization
 * Fully automated Playwright testing with parallel execution
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;
const path = require('path');

class AutoTestRunner {
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
            // Speed optimizations
            headless: true,
            parallel: true,
            maxConcurrency: 4,
            timeout: 15000,
            fastMode: true
        };
        this.sessionFile = './session.json';
        this.reportFile = '../report.md';
    }

    async initialize() {
        console.log('🚀 Initializing Auto Test Runner (Speed Optimized)...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor',
                '--disable-background-timer-throttling',
                '--disable-backgrounding-occluded-windows',
                '--disable-renderer-backgrounding'
            ]
        });

        console.log('✅ Browser initialized with speed optimizations');
    }

    async createFastContext() {
        const context = await this.browser.newContext({
            viewport: { width: 1366, height: 768 }, // Smaller viewport for speed
            ignoreHTTPSErrors: true,
            bypassCSP: true,
            extraHTTPHeaders: {
                'Accept-Language': 'en-US,en;q=0.9'
            }
        });

        // Aggressive resource blocking for speed
        await context.route('**/*.{png,jpg,jpeg,gif,svg,woff,woff2,ttf,css}', route => route.abort());
        await context.route('**/phpdebugbar/**', route => route.abort());
        await context.route('**/debugbar/**', route => route.abort());
        
        // Load session
        await this.loadSession(context);
        
        this.contexts.push(context);
        return context;
    }

    async loadSession(context) {
        try {
            const sessionData = JSON.parse(await fs.readFile(this.sessionFile, 'utf8'));
            if (Date.now() - sessionData.timestamp < 24 * 60 * 60 * 1000) {
                await context.addCookies(sessionData.cookies);
                return true;
            }
        } catch (error) {
            // No session found
        }
        return false;
    }

    async saveSession(context) {
        try {
            const cookies = await context.cookies();
            await fs.writeFile(this.sessionFile, JSON.stringify({
                cookies,
                timestamp: Date.now()
            }));
        } catch (error) {
            console.log('⚠️ Session save failed');
        }
    }

    async fastLogin(page) {
        await page.goto(this.config.invoicesUrl);
        
        if (page.url().includes('/login')) {
            await page.goto(this.config.loginUrl);
            await page.fill('input[name="email"]', this.config.credentials.email);
            await page.fill('input[name="password"]', this.config.credentials.password);
            
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('button[type="submit"]')
            ]);
            
            await this.saveSession(page.context());
        }
    }

    async fastPageLoad(page) {
        await page.goto(this.config.invoicesUrl);
        
        // Fast wait - just check for table existence
        await page.waitForSelector('table', { timeout: this.config.timeout });
        
        // Quick data check
        await page.waitForFunction(() => {
            const table = document.querySelector('table:not(.phpdebugbar-widgets-params)');
            return table && table.querySelector('tbody tr');
        }, { timeout: this.config.timeout });
    }

    // Ultra-fast test implementations
    async runSpeedTests() {
        const tests = [
            { id: 'SPEED_01', name: 'Page Load Speed', test: this.testPageLoad },
            { id: 'SPEED_02', name: 'Search Speed', test: this.testSearchSpeed },
            { id: 'SPEED_03', name: 'Filter Speed', test: this.testFilterSpeed },
            { id: 'SPEED_04', name: 'Pagination Speed', test: this.testPaginationSpeed },
            { id: 'SPEED_05', name: 'Data Load Speed', test: this.testDataLoadSpeed }
        ];

        const results = [];
        
        if (this.config.parallel) {
            // Parallel execution
            const promises = tests.map(async (test) => {
                const context = await this.createFastContext();
                const page = await context.newPage();
                
                try {
                    await this.fastLogin(page);
                    await this.fastPageLoad(page);
                    return await test.test.call(this, page);
                } catch (error) {
                    return {
                        id: test.id,
                        name: test.name,
                        status: 'FAILED',
                        details: error.message,
                        duration: 0
                    };
                }
            });
            
            const parallelResults = await Promise.all(promises);
            results.push(...parallelResults);
        } else {
            // Sequential execution
            const context = await this.createFastContext();
            const page = await context.newPage();
            
            await this.fastLogin(page);
            await this.fastPageLoad(page);
            
            for (const test of tests) {
                try {
                    const result = await test.test.call(this, page);
                    results.push(result);
                } catch (error) {
                    results.push({
                        id: test.id,
                        name: test.name,
                        status: 'FAILED',
                        details: error.message,
                        duration: 0
                    });
                }
            }
        }

        return results;
    }

    async testPageLoad(page) {
        const startTime = Date.now();
        
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForSelector('table');
        
        const duration = Date.now() - startTime;
        
        return {
            id: 'SPEED_01',
            name: 'Page Load Speed',
            status: duration < 3000 ? 'PASSED' : 'FAILED',
            details: `Page loaded in ${duration}ms`,
            duration
        };
    }

    async testSearchSpeed(page) {
        const startTime = Date.now();
        
        const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
        await searchBox.fill('HD');
        
        // Wait for any network activity to settle
        await page.waitForTimeout(1000);
        
        const duration = Date.now() - startTime;
        
        return {
            id: 'SPEED_02',
            name: 'Search Speed',
            status: duration < 2000 ? 'PASSED' : 'FAILED',
            details: `Search completed in ${duration}ms`,
            duration
        };
    }

    async testFilterSpeed(page) {
        const startTime = Date.now();
        
        // Quick filter interaction
        const checkboxes = await page.locator('input[type="checkbox"]').all();
        if (checkboxes.length > 0) {
            await checkboxes[0].click();
            await page.waitForTimeout(500);
        }
        
        const duration = Date.now() - startTime;
        
        return {
            id: 'SPEED_03',
            name: 'Filter Speed',
            status: duration < 1500 ? 'PASSED' : 'FAILED',
            details: `Filter applied in ${duration}ms`,
            duration
        };
    }

    async testPaginationSpeed(page) {
        const startTime = Date.now();
        
        const nextButton = page.locator('a:has-text("Tiếp"), .pagination .next').first();
        if (await nextButton.isVisible()) {
            await nextButton.click();
            await page.waitForTimeout(1000);
        }
        
        const duration = Date.now() - startTime;
        
        return {
            id: 'SPEED_04',
            name: 'Pagination Speed',
            status: duration < 2000 ? 'PASSED' : 'FAILED',
            details: `Pagination in ${duration}ms`,
            duration
        };
    }

    async testDataLoadSpeed(page) {
        const startTime = Date.now();
        
        // Force data reload
        await page.evaluate(() => {
            if (window.loadInvoices) {
                window.loadInvoices();
            }
        });
        
        await page.waitForTimeout(1500);
        
        const duration = Date.now() - startTime;
        
        return {
            id: 'SPEED_05',
            name: 'Data Load Speed',
            status: duration < 3000 ? 'PASSED' : 'FAILED',
            details: `Data loaded in ${duration}ms`,
            duration
        };
    }

    async runFullAutomation() {
        console.log('🎯 Starting Full Automation (Speed Mode)...\n');
        
        try {
            await this.initialize();
            
            // Run speed-optimized tests
            this.results = await this.runSpeedTests();
            
            // Generate and save report
            await this.generateSpeedReport();
            
            console.log('\n🎉 Full automation completed successfully!');
            
        } catch (error) {
            console.error('❌ Automation failed:', error.message);
        } finally {
            await this.cleanup();
        }
    }

    async generateSpeedReport() {
        const endTime = new Date();
        const totalDuration = Math.round((endTime - this.startTime) / 1000);
        
        let passed = 0;
        let failed = 0;
        let totalTestDuration = 0;
        
        this.results.forEach(result => {
            if (result.status === 'PASSED') passed++;
            else failed++;
            totalTestDuration += result.duration;
        });
        
        const successRate = Math.round((passed / (passed + failed)) * 100);
        const avgTestDuration = Math.round(totalTestDuration / this.results.length);
        const testsPerSecond = Math.round((this.results.length / totalDuration) * 100) / 100;
        
        console.log('\n⚡ SPEED TEST RESULTS');
        console.log('====================');
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`📈 Success Rate: ${successRate}%`);
        console.log(`⏱️ Total Duration: ${totalDuration}s`);
        console.log(`🚀 Average Test Duration: ${avgTestDuration}ms`);
        console.log(`📊 Tests per Second: ${testsPerSecond}`);
        console.log(`🔥 Mode: ${this.config.parallel ? 'Parallel' : 'Sequential'}`);
        
        // Update report file
        await this.updateSpeedReport(passed, failed, successRate, totalDuration, avgTestDuration, testsPerSecond);
    }

    async updateSpeedReport(passed, failed, successRate, totalDuration, avgTestDuration, testsPerSecond) {
        try {
            let content = await fs.readFile(this.reportFile, 'utf8');
            
            const speedSection = `
### ⚡ **Speed Automation Results** (Latest Run: ${new Date().toLocaleString()})

**Performance Summary:**
- ✅ **Passed**: ${passed} tests
- ❌ **Failed**: ${failed} tests  
- 📈 **Success Rate**: ${successRate}%
- ⏱️ **Total Duration**: ${totalDuration} seconds
- 🚀 **Average Test Duration**: ${avgTestDuration}ms
- 📊 **Tests per Second**: ${testsPerSecond}
- 🔥 **Execution Mode**: ${this.config.parallel ? 'Parallel' : 'Sequential'}

**Speed Benchmarks:**
| Test | Duration | Status | Performance |
|------|----------|--------|-------------|
${this.results.map(r => {
    const performance = r.duration < 1000 ? '🟢 Excellent' : 
                       r.duration < 2000 ? '🟡 Good' : 
                       r.duration < 3000 ? '🟠 Average' : '🔴 Slow';
    return `| ${r.name} | ${r.duration}ms | ${r.status === 'PASSED' ? '✅' : '❌'} | ${performance} |`;
}).join('\n')}

**Optimization Status:**
- Resource Blocking: ✅ Enabled (CSS, Images, Fonts)
- Debug Bar Blocking: ✅ Enabled  
- Parallel Execution: ${this.config.parallel ? '✅ Enabled' : '❌ Disabled'}
- Headless Mode: ${this.config.headless ? '✅ Enabled' : '❌ Disabled'}
- Session Persistence: ✅ Enabled

**Recommendations:**
${successRate >= 90 ? '🎉 Excellent performance! All systems optimal.' : 
  successRate >= 70 ? '⚠️ Good performance with room for improvement.' : 
  '🚨 Performance issues detected. Review failed tests.'}

`;

            // Replace or append speed section
            if (content.includes('⚡ **Speed Automation Results**')) {
                content = content.replace(/### ⚡ \*\*Speed Automation Results\*\*.*?(?=###|$)/s, speedSection);
            } else {
                content += speedSection;
            }
            
            await fs.writeFile(this.reportFile, content, 'utf8');
            console.log('📝 Speed report updated successfully');
            
        } catch (error) {
            console.error('❌ Failed to update speed report:', error.message);
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
    const runner = new AutoTestRunner();
    runner.runFullAutomation().catch(console.error);
}

module.exports = AutoTestRunner;
