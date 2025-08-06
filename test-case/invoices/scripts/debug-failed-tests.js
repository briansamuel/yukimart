/**
 * Debug Failed Tests - Fix Row Expansion Issues
 * Specifically targets RE01: Click Row to Expand
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;

class DebugFailedTests {
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
            headless: false, // Use visible browser for debugging
            timeout: 30000
        };
        this.sessionFile = './session.json';
    }

    async setup() {
        console.log('🔧 Setting up Debug Session...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            slowMo: 1000 // Slow down for debugging
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        this.page = await context.newPage();
        
        await this.ensureLogin();
        await this.waitForPageLoad();
        
        console.log('✅ Debug setup completed');
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
        
        try {
            await this.page.waitForFunction(() => {
                const table = document.querySelector('table:not(.phpdebugbar-widgets-params)');
                return table && table.querySelector('tbody tr');
            }, { timeout: 15000 });
        } catch (e) {
            console.log('⚠️ Data load timeout, continuing...');
        }
    }

    // Debug RE01: Click Row to Expand
    async debugRowExpansion() {
        const startTime = Date.now();
        console.log('🔍 Debugging RE01: Click Row to Expand');
        
        try {
            // Take screenshot before action
            await this.page.screenshot({ path: './debug-before-click.png' });
            
            // Find all table rows
            const rows = await this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').all();
            console.log(`📊 Found ${rows.length} table rows`);
            
            if (rows.length === 0) {
                throw new Error('No table rows found');
            }
            
            // Get the first row
            const firstRow = rows[0];
            
            // Check if row is visible and clickable
            const isVisible = await firstRow.isVisible();
            const isEnabled = await firstRow.isEnabled();
            console.log(`🔍 First row - Visible: ${isVisible}, Enabled: ${isEnabled}`);
            
            // Get row details before clicking
            const rowText = await firstRow.textContent();
            console.log(`📝 Row content: ${rowText?.substring(0, 100)}...`);
            
            // Check for existing detail panels
            const existingPanels = await this.page.locator('.invoice-detail-panel, .detail-panel, .expanded-row').count();
            console.log(`📋 Existing detail panels: ${existingPanels}`);
            
            // Click the row
            console.log('🖱️ Clicking first row...');
            await firstRow.click();
            
            // Wait for potential expansion
            await this.page.waitForTimeout(3000);
            
            // Take screenshot after action
            await this.page.screenshot({ path: './debug-after-click.png' });
            
            // Check for detail panels after click
            const detailPanelSelectors = [
                '.invoice-detail-panel',
                '.detail-panel', 
                '.expanded-row',
                '.invoice-detail',
                '.row-detail',
                '.detail-container',
                '[class*="detail"]',
                '[class*="expand"]'
            ];
            
            let panelFound = false;
            let foundSelector = '';
            
            for (const selector of detailPanelSelectors) {
                const count = await this.page.locator(selector).count();
                if (count > 0) {
                    panelFound = true;
                    foundSelector = selector;
                    console.log(`✅ Found detail panel with selector: ${selector} (${count} elements)`);
                    break;
                }
            }
            
            if (!panelFound) {
                console.log('❌ No detail panel found with any selector');
                
                // Check for any new elements that appeared
                const allElements = await this.page.evaluate(() => {
                    const elements = document.querySelectorAll('*');
                    const newElements = [];
                    elements.forEach(el => {
                        if (el.style.display === 'block' || el.classList.contains('show') || el.classList.contains('active')) {
                            newElements.push({
                                tagName: el.tagName,
                                className: el.className,
                                id: el.id
                            });
                        }
                    });
                    return newElements.slice(0, 10); // Limit to first 10
                });
                
                console.log('🔍 Recently shown elements:', allElements);
            }
            
            // Check for JavaScript errors
            const jsErrors = await this.page.evaluate(() => {
                return window.jsErrors || [];
            });
            
            if (jsErrors.length > 0) {
                console.log('⚠️ JavaScript errors detected:', jsErrors);
            }
            
            // Check for AJAX requests
            const networkRequests = [];
            this.page.on('response', response => {
                if (response.url().includes('invoice')) {
                    networkRequests.push({
                        url: response.url(),
                        status: response.status()
                    });
                }
            });
            
            // Try alternative click methods
            if (!panelFound) {
                console.log('🔄 Trying alternative click methods...');
                
                // Method 1: Double click
                await firstRow.dblclick();
                await this.page.waitForTimeout(2000);
                
                // Method 2: Click specific cell
                const firstCell = firstRow.locator('td').first();
                await firstCell.click();
                await this.page.waitForTimeout(2000);
                
                // Method 3: JavaScript click
                await this.page.evaluate(() => {
                    const row = document.querySelector('table:not(.phpdebugbar-widgets-params) tbody tr');
                    if (row) {
                        row.click();
                        // Try triggering custom events
                        row.dispatchEvent(new Event('click', { bubbles: true }));
                        row.dispatchEvent(new Event('mousedown', { bubbles: true }));
                        row.dispatchEvent(new Event('mouseup', { bubbles: true }));
                    }
                });
                await this.page.waitForTimeout(2000);
                
                // Check again for detail panels
                for (const selector of detailPanelSelectors) {
                    const count = await this.page.locator(selector).count();
                    if (count > 0) {
                        panelFound = true;
                        foundSelector = selector;
                        console.log(`✅ Found detail panel after alternative click: ${selector}`);
                        break;
                    }
                }
            }
            
            this.results.push({
                id: 'RE01_DEBUG',
                name: 'Row Expansion Debug',
                status: panelFound ? 'PASSED' : 'FAILED',
                details: panelFound ? 
                    `Detail panel found with selector: ${foundSelector}` : 
                    'No detail panel found after multiple click attempts',
                duration: Date.now() - startTime,
                debug: {
                    rowsFound: rows.length,
                    panelFound,
                    foundSelector,
                    networkRequests: networkRequests.length,
                    jsErrors: jsErrors.length
                }
            });
            
        } catch (error) {
            this.results.push({
                id: 'RE01_DEBUG',
                name: 'Row Expansion Debug',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // Try to find the correct row expansion implementation
    async analyzeRowExpansionImplementation() {
        console.log('🔍 Analyzing Row Expansion Implementation...');
        
        try {
            // Check for JavaScript event handlers
            const eventHandlers = await this.page.evaluate(() => {
                const row = document.querySelector('table:not(.phpdebugbar-widgets-params) tbody tr');
                if (!row) return null;
                
                const handlers = [];
                
                // Check for onclick attribute
                if (row.onclick) handlers.push('onclick');
                
                // Check for event listeners (limited detection)
                const events = ['click', 'mousedown', 'mouseup', 'dblclick'];
                events.forEach(event => {
                    try {
                        const hasListener = row.addEventListener && row.removeEventListener;
                        if (hasListener) handlers.push(`${event}_listener_possible`);
                    } catch (e) {}
                });
                
                return {
                    handlers,
                    className: row.className,
                    id: row.id,
                    dataset: Object.keys(row.dataset || {}),
                    attributes: Array.from(row.attributes).map(attr => attr.name)
                };
            });
            
            console.log('📊 Row event analysis:', eventHandlers);
            
            // Check for DataTables or other table libraries
            const tableLibraries = await this.page.evaluate(() => {
                const libraries = [];
                
                if (window.jQuery && window.jQuery.fn.DataTable) {
                    libraries.push('DataTables');
                }
                
                if (window.bootstrap) {
                    libraries.push('Bootstrap');
                }
                
                // Check for custom table handlers
                const scripts = Array.from(document.scripts);
                const hasTableScript = scripts.some(script => 
                    script.textContent && (
                        script.textContent.includes('table') ||
                        script.textContent.includes('row') ||
                        script.textContent.includes('expand')
                    )
                );
                
                if (hasTableScript) {
                    libraries.push('Custom_Table_Script');
                }
                
                return libraries;
            });
            
            console.log('📚 Detected table libraries:', tableLibraries);
            
            // Look for expansion-related CSS classes
            const expansionClasses = await this.page.evaluate(() => {
                const allClasses = [];
                const elements = document.querySelectorAll('*');
                
                elements.forEach(el => {
                    if (el.className && typeof el.className === 'string') {
                        const classes = el.className.split(' ');
                        classes.forEach(cls => {
                            if (cls.includes('expand') || cls.includes('detail') || cls.includes('collapse')) {
                                allClasses.push(cls);
                            }
                        });
                    }
                });
                
                return [...new Set(allClasses)].slice(0, 20); // Unique classes, limit to 20
            });
            
            console.log('🎨 Expansion-related CSS classes:', expansionClasses);
            
        } catch (error) {
            console.log('❌ Analysis error:', error.message);
        }
    }

    async runDebugSession() {
        console.log('🎯 Starting Debug Session for Failed Tests...\n');
        
        try {
            await this.setup();
            
            // Analyze the implementation first
            await this.analyzeRowExpansionImplementation();
            
            // Debug the specific failed test
            await this.debugRowExpansion();
            
            this.generateDebugReport();
            
            console.log('\n🎉 Debug session completed!');
            console.log('📸 Screenshots saved: debug-before-click.png, debug-after-click.png');
            
        } catch (error) {
            console.error('❌ Debug session failed:', error.message);
        } finally {
            // Keep browser open for manual inspection
            console.log('\n🔍 Browser kept open for manual inspection...');
            console.log('Press Ctrl+C to close when done debugging.');
        }
    }

    generateDebugReport() {
        console.log('\n🔧 DEBUG REPORT');
        console.log('================');
        
        this.results.forEach(result => {
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.id}: ${result.name}`);
            console.log(`   ${result.details}`);
            console.log(`   Duration: ${result.duration}ms`);
            
            if (result.debug) {
                console.log(`   Debug Info:`, result.debug);
            }
            console.log('');
        });
    }

    async teardown() {
        if (this.browser) {
            await this.browser.close();
        }
        console.log('🏁 Debug session ended');
    }
}

if (require.main === module) {
    const debugger = new DebugFailedTests();
    debugger.runDebugSession().catch(console.error);
}

module.exports = DebugFailedTests;
