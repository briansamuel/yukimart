/**
 * Debug script to check page structure
 */

const { chromium } = require('playwright');

async function debugPage() {
    console.log('🔍 Debugging invoice page structure...');
    
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    // Login
    await page.goto('http://yukimart.local/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    
    // Go to invoices
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(5000);

    // Check if page loaded correctly
    const title = await page.title();
    console.log(`Page title: ${title}`);

    // Check for common invoice page elements
    const searchBox = await page.locator('input[type="search"], input[placeholder*="tìm"]').count();
    console.log(`Search boxes found: ${searchBox}`);

    const cards = await page.locator('.card, .kt-portlet').count();
    console.log(`Cards found: ${cards}`);
    
    // Check for tables
    console.log('📋 Checking for tables...');
    const tables = await page.locator('table').count();
    console.log(`Found ${tables} tables`);
    
    for (let i = 0; i < tables; i++) {
        const table = page.locator('table').nth(i);
        const id = await table.getAttribute('id');
        const className = await table.getAttribute('class');
        const rows = await table.locator('tbody tr').count();
        
        console.log(`Table ${i + 1}:`);
        console.log(`  ID: ${id}`);
        console.log(`  Class: ${className}`);
        console.log(`  Rows: ${rows}`);
        
        if (rows > 0) {
            const firstRowText = await table.locator('tbody tr').first().textContent();
            console.log(`  First row: ${firstRowText?.substring(0, 100)}...`);
        }
        console.log('');
    }
    
    // Check for pagination info
    console.log('📄 Checking for pagination info...');
    const paginationSelectors = [
        '.dataTables_info',
        '.pagination-info', 
        '.table-info',
        '.info',
        '[class*="info"]'
    ];
    
    for (const selector of paginationSelectors) {
        const element = page.locator(selector);
        const count = await element.count();
        if (count > 0) {
            const text = await element.first().textContent();
            console.log(`${selector}: "${text}"`);
        }
    }
    
    // Take screenshot
    await page.screenshot({ path: 'debug-invoice-page.png' });
    console.log('📸 Screenshot saved as debug-invoice-page.png');
    
    // Keep browser open for manual inspection
    console.log('🔍 Browser kept open for manual inspection. Press Ctrl+C to close.');
    await page.waitForTimeout(60000); // Wait 1 minute
    
    await browser.close();
}

debugPage().catch(console.error);
