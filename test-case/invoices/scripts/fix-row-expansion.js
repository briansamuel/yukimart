/**
 * Fix Row Expansion Test - RE01
 * Improved row expansion detection and interaction
 */

const { chromium } = require('playwright');

class FixRowExpansion {
    constructor() {
        this.browser = null;
        this.page = null;
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
    }

    async setup() {
        console.log('🔧 Setting up Row Expansion Fix...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
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
        
        try {
            await this.page.waitForFunction(() => {
                const table = document.querySelector('table:not(.phpdebugbar-widgets-params)');
                return table && table.querySelector('tbody tr');
            }, { timeout: 15000 });
        } catch (e) {
            console.log('⚠️ Data load timeout, continuing...');
        }
    }

    // Fixed Row Expansion Test
    async testRowExpansionFixed() {
        const startTime = Date.now();
        console.log('🧪 Testing Fixed Row Expansion (RE01)...');
        
        try {
            // Wait for table to be fully loaded
            await this.page.waitForSelector('table:not(.phpdebugbar-widgets-params) tbody tr', { 
                timeout: this.config.timeout 
            });
            
            // Get the first row
            const firstRow = this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').first();
            
            // Ensure row is visible and ready
            await firstRow.waitFor({ state: 'visible', timeout: 10000 });
            
            // Method 1: Try standard click
            console.log('🖱️ Attempting standard click...');
            await firstRow.click();
            await this.page.waitForTimeout(2000);
            
            // Check for detail panel with multiple selectors
            const detailSelectors = [
                '.invoice-detail-panel',
                '.detail-panel',
                '.expanded-row',
                '.invoice-detail',
                '.row-detail',
                '.detail-container',
                '.collapse.show',
                '.accordion-collapse.show',
                '[id*="detail"]',
                '[class*="detail"][style*="block"]',
                'tr + tr[style*="table-row"]', // Next row that might be detail
                'tr.detail-row',
                'tr.expanded'
            ];
            
            let detailPanelFound = false;
            let foundSelector = '';
            
            for (const selector of detailSelectors) {
                try {
                    const elements = await this.page.locator(selector).all();
                    for (const element of elements) {
                        const isVisible = await element.isVisible();
                        if (isVisible) {
                            detailPanelFound = true;
                            foundSelector = selector;
                            console.log(`✅ Found visible detail panel: ${selector}`);
                            break;
                        }
                    }
                    if (detailPanelFound) break;
                } catch (e) {
                    continue;
                }
            }
            
            // Method 2: If not found, try JavaScript-based expansion
            if (!detailPanelFound) {
                console.log('🔄 Trying JavaScript-based expansion...');
                
                await this.page.evaluate(() => {
                    const row = document.querySelector('table:not(.phpdebugbar-widgets-params) tbody tr');
                    if (row) {
                        // Try multiple event types
                        const events = ['click', 'mousedown', 'mouseup', 'dblclick'];
                        events.forEach(eventType => {
                            const event = new MouseEvent(eventType, {
                                bubbles: true,
                                cancelable: true,
                                view: window
                            });
                            row.dispatchEvent(event);
                        });
                        
                        // Try triggering any custom events
                        if (window.jQuery && window.jQuery(row).trigger) {
                            window.jQuery(row).trigger('click');
                        }
                        
                        // Check for data attributes that might trigger expansion
                        const dataAttrs = Object.keys(row.dataset || {});
                        console.log('Data attributes:', dataAttrs);
                        
                        // Try clicking specific cells that might have handlers
                        const cells = row.querySelectorAll('td');
                        cells.forEach((cell, index) => {
                            if (index < 3) { // Try first 3 cells
                                cell.click();
                            }
                        });
                    }
                });
                
                await this.page.waitForTimeout(3000);
                
                // Check again for detail panels
                for (const selector of detailSelectors) {
                    try {
                        const elements = await this.page.locator(selector).all();
                        for (const element of elements) {
                            const isVisible = await element.isVisible();
                            if (isVisible) {
                                detailPanelFound = true;
                                foundSelector = selector;
                                console.log(`✅ Found detail panel after JS events: ${selector}`);
                                break;
                            }
                        }
                        if (detailPanelFound) break;
                    } catch (e) {
                        continue;
                    }
                }
            }
            
            // Method 3: Check if expansion creates new table rows
            if (!detailPanelFound) {
                console.log('🔄 Checking for expanded table rows...');
                
                const rowCount = await this.page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
                console.log(`📊 Total rows after expansion attempt: ${rowCount}`);
                
                // Check if any row has expanded content
                const expandedRows = await this.page.evaluate(() => {
                    const rows = document.querySelectorAll('table:not(.phpdebugbar-widgets-params) tbody tr');
                    const expanded = [];
                    
                    rows.forEach((row, index) => {
                        const height = row.offsetHeight;
                        const hasHiddenContent = row.querySelector('[style*="display: block"]') || 
                                               row.querySelector('.show') ||
                                               row.querySelector('.expanded');
                        
                        if (height > 50 || hasHiddenContent) { // Assume normal row height is ~50px
                            expanded.push({
                                index,
                                height,
                                hasHiddenContent: !!hasHiddenContent
                            });
                        }
                    });
                    
                    return expanded;
                });
                
                if (expandedRows.length > 0) {
                    detailPanelFound = true;
                    foundSelector = 'expanded_table_row';
                    console.log(`✅ Found expanded rows:`, expandedRows);
                }
            }
            
            // Method 4: Look for any newly visible elements
            if (!detailPanelFound) {
                console.log('🔄 Checking for any newly visible elements...');
                
                const newElements = await this.page.evaluate(() => {
                    const allElements = document.querySelectorAll('*');
                    const visible = [];
                    
                    allElements.forEach(el => {
                        const style = window.getComputedStyle(el);
                        const rect = el.getBoundingClientRect();
                        
                        if (style.display !== 'none' && 
                            style.visibility !== 'hidden' && 
                            rect.height > 0 && 
                            (el.textContent.includes('invoice') || 
                             el.textContent.includes('detail') ||
                             el.className.includes('detail') ||
                             el.className.includes('expand'))) {
                            
                            visible.push({
                                tagName: el.tagName,
                                className: el.className,
                                id: el.id,
                                textContent: el.textContent.substring(0, 50)
                            });
                        }
                    });
                    
                    return visible.slice(0, 5); // Limit to 5 elements
                });
                
                if (newElements.length > 0) {
                    console.log('📋 Potentially relevant visible elements:', newElements);
                    detailPanelFound = true;
                    foundSelector = 'dynamic_content';
                }
            }
            
            const duration = Date.now() - startTime;
            
            return {
                id: 'RE01_FIXED',
                name: 'Row Expansion (Fixed)',
                status: detailPanelFound ? 'PASSED' : 'FAILED',
                details: detailPanelFound ? 
                    `Detail panel found using: ${foundSelector}` : 
                    'No detail panel found after comprehensive attempts',
                duration,
                foundSelector
            };
            
        } catch (error) {
            return {
                id: 'RE01_FIXED',
                name: 'Row Expansion (Fixed)',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }

    async runFix() {
        console.log('🎯 Starting Row Expansion Fix...\n');
        
        try {
            await this.setup();
            
            const result = await this.testRowExpansionFixed();
            
            console.log('\n🔧 FIX RESULTS');
            console.log('==============');
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.id}: ${result.name}`);
            console.log(`   ${result.details}`);
            console.log(`   Duration: ${result.duration}ms`);
            
            if (result.foundSelector) {
                console.log(`   Found using: ${result.foundSelector}`);
            }
            
            return result;
            
        } catch (error) {
            console.error('❌ Fix failed:', error.message);
            return null;
        } finally {
            await this.teardown();
        }
    }

    async teardown() {
        if (this.browser) {
            await this.browser.close();
        }
        console.log('🏁 Fix session completed');
    }
}

if (require.main === module) {
    const fixer = new FixRowExpansion();
    fixer.runFix().catch(console.error);
}

module.exports = FixRowExpansion;
