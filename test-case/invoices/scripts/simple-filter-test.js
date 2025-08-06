/**
 * Simple Filter Test - Step by step testing
 */

const { chromium } = require('playwright');

async function simpleFilterTest() {
    console.log('🎯 Starting Simple Filter Test...');
    
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000 
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    
    const page = await context.newPage();
    
    try {
        // Step 1: Login
        console.log('🔐 Step 1: Login...');
        await page.goto('http://yukimart.local/login');
        await page.waitForLoadState('networkidle');
        
        await page.fill('input[name="email"]', 'yukimart@gmail.com');
        await page.fill('input[name="password"]', '123456');
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);
        
        console.log(`✅ Login successful: ${page.url()}`);
        
        // Step 2: Navigate to invoices
        console.log('📄 Step 2: Navigate to invoices...');
        await page.goto('http://yukimart.local/admin/invoices');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        console.log(`✅ Invoices page loaded: ${page.url()}`);
        
        // Step 3: Check page elements
        console.log('🔍 Step 3: Check page elements...');
        
        const searchBox = await page.locator('input[placeholder*="Tìm kiếm"]').count();
        const timeFilter = await page.locator('input[type="radio"]:has-text("Tháng này")').count();
        const statusFilter = await page.locator('input[type="checkbox"]:has-text("Đang xử lý")').count();
        const table = await page.locator('table:not(.phpdebugbar-widgets-params)').count();
        
        console.log(`Search box: ${searchBox}`);
        console.log(`Time filter: ${timeFilter}`);
        console.log(`Status filter: ${statusFilter}`);
        console.log(`Table: ${table}`);
        
        // Step 4: Wait for data to load
        console.log('⏳ Step 4: Wait for data to load...');
        
        // Check if loading
        const loadingElement = page.locator('tbody tr:has-text("Đang tải")');
        const loadingCount = await loadingElement.count();
        
        if (loadingCount > 0) {
            console.log('📊 Data is loading, waiting...');
            await page.waitForFunction(() => {
                const tables = document.querySelectorAll('table:not(.phpdebugbar-widgets-params)');
                for (const table of tables) {
                    const loadingText = table.querySelector('tbody tr td');
                    if (loadingText && !loadingText.textContent.includes('Đang tải')) {
                        return true;
                    }
                }
                return false;
            }, { timeout: 15000 });
        }
        
        // Step 5: Count results
        console.log('📊 Step 5: Count results...');
        
        const rows = await page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
        console.log(`Table rows: ${rows}`);
        
        // Check pagination info
        const paginationSelectors = [
            ':has-text("Hiển thị")',
            ':has-text("kết quả")',
            '.dataTables_info'
        ];
        
        for (const selector of paginationSelectors) {
            try {
                const element = page.locator(selector);
                const count = await element.count();
                if (count > 0) {
                    const text = await element.first().textContent();
                    console.log(`Pagination info (${selector}): "${text}"`);
                    break;
                }
            } catch (e) {
                continue;
            }
        }
        
        // Step 6: Test simple filter
        console.log('🧪 Step 6: Test time filter...');
        
        const thisMonthRadio = page.locator('input[type="radio"]:has-text("Tháng này")');
        const isChecked = await thisMonthRadio.isChecked();
        console.log(`"Tháng này" is checked: ${isChecked}`);
        
        if (!isChecked) {
            await thisMonthRadio.check();
            await page.waitForTimeout(2000);
            console.log('✅ "Tháng này" filter applied');
        }
        
        // Step 7: Test status filter
        console.log('🧪 Step 7: Test status filter...');
        
        const processingCheckbox = page.locator('input[type="checkbox"]:has-text("Đang xử lý")');
        const processingChecked = await processingCheckbox.isChecked();
        console.log(`"Đang xử lý" is checked: ${processingChecked}`);
        
        if (!processingChecked) {
            await processingCheckbox.check();
            await page.waitForTimeout(2000);
            console.log('✅ "Đang xử lý" filter applied');
        }
        
        // Final results
        console.log('📈 Final Results:');
        const finalRows = await page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
        console.log(`Final table rows: ${finalRows}`);
        
        // Take screenshot
        await page.screenshot({ path: 'simple-filter-test-result.png' });
        console.log('📸 Screenshot saved');
        
        console.log('✅ Simple Filter Test completed successfully!');
        
        // Keep browser open for inspection
        await page.waitForTimeout(10000);
        
    } catch (error) {
        console.error('❌ Error:', error.message);
        await page.screenshot({ path: 'simple-filter-test-error.png' });
    }
    
    await browser.close();
}

simpleFilterTest().catch(console.error);
