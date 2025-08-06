/**
 * Performance Testing Module
 * Tests page load times, memory usage, network performance
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;

class PerformanceTests {
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
        this.performanceMetrics = {};
    }

    async setup() {
        console.log('🚀 Setting up Performance Tests...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        this.page = await context.newPage();
        
        // Enable performance monitoring
        await this.page.addInitScript(() => {
            window.performanceMetrics = {
                navigationStart: performance.timing.navigationStart,
                loadEventEnd: 0,
                domContentLoaded: 0,
                firstPaint: 0,
                firstContentfulPaint: 0
            };
            
            // Capture performance metrics
            window.addEventListener('load', () => {
                window.performanceMetrics.loadEventEnd = performance.timing.loadEventEnd;
                window.performanceMetrics.domContentLoaded = performance.timing.domContentLoadedEventEnd;
                
                // Get paint metrics
                const paintEntries = performance.getEntriesByType('paint');
                paintEntries.forEach(entry => {
                    if (entry.name === 'first-paint') {
                        window.performanceMetrics.firstPaint = entry.startTime;
                    }
                    if (entry.name === 'first-contentful-paint') {
                        window.performanceMetrics.firstContentfulPaint = entry.startTime;
                    }
                });
            });
        });
        
        await this.ensureLogin();
        console.log('✅ Performance setup completed');
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

    // PF01: Page Load Performance
    async testPageLoadPerformance() {
        const startTime = Date.now();
        console.log('🧪 Testing PF01: Page Load Performance');
        
        try {
            const navigationStart = Date.now();
            
            await this.page.goto(this.config.invoicesUrl, { 
                waitUntil: 'networkidle',
                timeout: this.config.timeout 
            });
            
            const loadTime = Date.now() - navigationStart;
            
            // Get detailed performance metrics
            const metrics = await this.page.evaluate(() => {
                const timing = performance.timing;
                return {
                    navigationStart: timing.navigationStart,
                    domainLookupStart: timing.domainLookupStart,
                    domainLookupEnd: timing.domainLookupEnd,
                    connectStart: timing.connectStart,
                    connectEnd: timing.connectEnd,
                    requestStart: timing.requestStart,
                    responseStart: timing.responseStart,
                    responseEnd: timing.responseEnd,
                    domLoading: timing.domLoading,
                    domContentLoadedEventStart: timing.domContentLoadedEventStart,
                    domContentLoadedEventEnd: timing.domContentLoadedEventEnd,
                    loadEventStart: timing.loadEventStart,
                    loadEventEnd: timing.loadEventEnd
                };
            });
            
            const domContentLoadedTime = metrics.domContentLoadedEventEnd - metrics.navigationStart;
            const fullLoadTime = metrics.loadEventEnd - metrics.navigationStart;
            
            const performance_rating = loadTime < 2000 ? 'Excellent' : 
                                    loadTime < 4000 ? 'Good' : 
                                    loadTime < 6000 ? 'Average' : 'Poor';
            
            this.results.push({
                id: 'PF01',
                name: 'Page Load Performance',
                status: loadTime < 6000 ? 'PASSED' : 'FAILED',
                details: `Load time: ${loadTime}ms, DOM: ${domContentLoadedTime}ms, Full: ${fullLoadTime}ms, Rating: ${performance_rating}`,
                duration: Date.now() - startTime,
                metrics: { loadTime, domContentLoadedTime, fullLoadTime, performance_rating }
            });
        } catch (error) {
            this.results.push({
                id: 'PF01',
                name: 'Page Load Performance',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // PF02: Memory Usage Test
    async testMemoryUsage() {
        const startTime = Date.now();
        console.log('🧪 Testing PF02: Memory Usage');
        
        try {
            // Get initial memory usage
            const initialMemory = await this.page.evaluate(() => {
                if (performance.memory) {
                    return {
                        usedJSHeapSize: performance.memory.usedJSHeapSize,
                        totalJSHeapSize: performance.memory.totalJSHeapSize,
                        jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                    };
                }
                return null;
            });
            
            // Perform some operations to test memory
            await this.page.locator('input[type="search"]').first().fill('test search');
            await this.page.waitForTimeout(2000);
            
            // Apply filters
            const checkboxes = await this.page.locator('input[type="checkbox"]').all();
            if (checkboxes.length > 0) {
                await checkboxes[0].check();
                await this.page.waitForTimeout(1000);
            }
            
            // Get memory after operations
            const finalMemory = await this.page.evaluate(() => {
                if (performance.memory) {
                    return {
                        usedJSHeapSize: performance.memory.usedJSHeapSize,
                        totalJSHeapSize: performance.memory.totalJSHeapSize,
                        jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                    };
                }
                return null;
            });
            
            let memoryIncrease = 0;
            let memoryStatus = 'PASSED';
            
            if (initialMemory && finalMemory) {
                memoryIncrease = finalMemory.usedJSHeapSize - initialMemory.usedJSHeapSize;
                memoryStatus = memoryIncrease < 10 * 1024 * 1024 ? 'PASSED' : 'FAILED'; // 10MB threshold
            }
            
            this.results.push({
                id: 'PF02',
                name: 'Memory Usage',
                status: memoryStatus,
                details: `Memory increase: ${Math.round(memoryIncrease / 1024)}KB, Initial: ${Math.round((initialMemory?.usedJSHeapSize || 0) / 1024)}KB`,
                duration: Date.now() - startTime,
                metrics: { initialMemory, finalMemory, memoryIncrease }
            });
        } catch (error) {
            this.results.push({
                id: 'PF02',
                name: 'Memory Usage',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // PF03: Network Performance
    async testNetworkPerformance() {
        const startTime = Date.now();
        console.log('🧪 Testing PF03: Network Performance');
        
        try {
            const networkRequests = [];
            
            // Monitor network requests
            this.page.on('response', response => {
                networkRequests.push({
                    url: response.url(),
                    status: response.status(),
                    size: response.headers()['content-length'] || 0
                });
            });
            
            // Reload page to capture network requests
            await this.page.reload({ waitUntil: 'networkidle' });
            
            // Analyze network performance
            const totalRequests = networkRequests.length;
            const failedRequests = networkRequests.filter(req => req.status >= 400).length;
            const totalSize = networkRequests.reduce((sum, req) => sum + parseInt(req.size || 0), 0);
            
            const networkScore = failedRequests === 0 && totalRequests > 0 ? 'PASSED' : 'FAILED';
            
            this.results.push({
                id: 'PF03',
                name: 'Network Performance',
                status: networkScore,
                details: `Requests: ${totalRequests}, Failed: ${failedRequests}, Total size: ${Math.round(totalSize / 1024)}KB`,
                duration: Date.now() - startTime,
                metrics: { totalRequests, failedRequests, totalSize }
            });
        } catch (error) {
            this.results.push({
                id: 'PF03',
                name: 'Network Performance',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // PF04: Search Performance
    async testSearchPerformance() {
        const startTime = Date.now();
        console.log('🧪 Testing PF04: Search Performance');
        
        try {
            const searchQueries = ['HD', 'test', 'Nguyễn', '@gmail.com'];
            const searchTimes = [];
            
            for (const query of searchQueries) {
                const searchStart = Date.now();
                
                const searchBox = this.page.locator('input[type="search"]').first();
                await searchBox.fill(query);
                
                // Wait for search results
                await this.page.waitForTimeout(1500);
                
                const searchTime = Date.now() - searchStart;
                searchTimes.push(searchTime);
            }
            
            const avgSearchTime = searchTimes.reduce((sum, time) => sum + time, 0) / searchTimes.length;
            const maxSearchTime = Math.max(...searchTimes);
            
            const searchPerformance = avgSearchTime < 2000 ? 'PASSED' : 'FAILED';
            
            this.results.push({
                id: 'PF04',
                name: 'Search Performance',
                status: searchPerformance,
                details: `Avg search time: ${Math.round(avgSearchTime)}ms, Max: ${maxSearchTime}ms`,
                duration: Date.now() - startTime,
                metrics: { avgSearchTime, maxSearchTime, searchTimes }
            });
        } catch (error) {
            this.results.push({
                id: 'PF04',
                name: 'Search Performance',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // PF05: Filter Performance
    async testFilterPerformance() {
        const startTime = Date.now();
        console.log('🧪 Testing PF05: Filter Performance');
        
        try {
            const filterStart = Date.now();
            
            // Apply multiple filters
            const checkboxes = await this.page.locator('input[type="checkbox"]').all();
            for (let i = 0; i < Math.min(3, checkboxes.length); i++) {
                await checkboxes[i].check();
                await this.page.waitForTimeout(500);
            }
            
            const filterTime = Date.now() - filterStart;
            const filterPerformance = filterTime < 3000 ? 'PASSED' : 'FAILED';
            
            this.results.push({
                id: 'PF05',
                name: 'Filter Performance',
                status: filterPerformance,
                details: `Filter application time: ${filterTime}ms`,
                duration: Date.now() - startTime,
                metrics: { filterTime }
            });
        } catch (error) {
            this.results.push({
                id: 'PF05',
                name: 'Filter Performance',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // PF06: Pagination Performance
    async testPaginationPerformance() {
        const startTime = Date.now();
        console.log('🧪 Testing PF06: Pagination Performance');
        
        try {
            const paginationTimes = [];
            
            // Test next page navigation
            for (let i = 0; i < 3; i++) {
                const navStart = Date.now();
                
                const nextButton = this.page.locator('a:has-text("Tiếp"), .pagination .next').first();
                if (await nextButton.isVisible()) {
                    await nextButton.click();
                    await this.page.waitForTimeout(1500);
                    
                    const navTime = Date.now() - navStart;
                    paginationTimes.push(navTime);
                } else {
                    break;
                }
            }
            
            const avgPaginationTime = paginationTimes.length > 0 ? 
                paginationTimes.reduce((sum, time) => sum + time, 0) / paginationTimes.length : 0;
            
            const paginationPerformance = avgPaginationTime < 2000 ? 'PASSED' : 'FAILED';
            
            this.results.push({
                id: 'PF06',
                name: 'Pagination Performance',
                status: paginationPerformance,
                details: `Avg pagination time: ${Math.round(avgPaginationTime)}ms, Pages tested: ${paginationTimes.length}`,
                duration: Date.now() - startTime,
                metrics: { avgPaginationTime, paginationTimes }
            });
        } catch (error) {
            this.results.push({
                id: 'PF06',
                name: 'Pagination Performance',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Performance Tests...\n');
        
        await this.setup();
        
        await this.testPageLoadPerformance();
        await this.testMemoryUsage();
        await this.testNetworkPerformance();
        await this.testSearchPerformance();
        await this.testFilterPerformance();
        await this.testPaginationPerformance();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n📊 PERFORMANCE TESTS REPORT');
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
        console.log('🏁 Performance Tests completed');
    }
}

if (require.main === module) {
    const tests = new PerformanceTests();
    tests.runAllTests().catch(console.error);
}

module.exports = PerformanceTests;
