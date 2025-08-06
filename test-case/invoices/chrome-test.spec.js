const { test, expect } = require('@playwright/test');

test.describe('Invoice Page Chrome Test', () => {
    test('Open Chrome and test invoice page', async ({ page }) => {
        // Set viewport size
        await page.setViewportSize({ width: 1200, height: 800 });
        
        console.log('🚀 Starting Chrome test for invoice page...');
        
        // Navigate to login page
        console.log('📝 Navigating to login page...');
        await page.goto('http://yukimart.local/admin/login');
        
        // Take screenshot of login page
        await page.screenshot({ path: 'test-results/01-login-page.png', fullPage: true });
        console.log('📸 Login page screenshot saved');
        
        // Fill login form
        console.log('🔐 Filling login credentials...');
        await page.fill('input[name="email"]', 'yukimart@gmail.com');
        await page.fill('input[name="password"]', '123456');
        
        // Take screenshot before login
        await page.screenshot({ path: 'test-results/02-before-login.png', fullPage: true });
        
        // Click login button
        await page.click('button[type="submit"]');
        console.log('🔑 Login button clicked');
        
        // Wait for redirect to dashboard
        await page.waitForURL('**/admin/dashboard', { timeout: 10000 });
        console.log('✅ Successfully logged in and redirected to dashboard');
        
        // Take screenshot of dashboard
        await page.screenshot({ path: 'test-results/03-dashboard.png', fullPage: true });
        
        // Navigate to invoices page
        console.log('📄 Navigating to invoices page...');
        await page.goto('http://yukimart.local/admin/invoices');
        
        // Wait for page to load
        await page.waitForLoadState('networkidle');
        console.log('⏳ Page loaded, waiting for content...');
        
        // Wait for table to appear
        await page.waitForSelector('#kt_invoices_table', { timeout: 15000 });
        console.log('📊 Invoice table found');
        
        // Take screenshot of invoice list
        await page.screenshot({ path: 'test-results/04-invoice-list.png', fullPage: true });
        console.log('📸 Invoice list screenshot saved');
        
        // Check if there are invoice rows
        const rows = await page.locator('#kt_invoices_table tbody tr').count();
        console.log(`📋 Found ${rows} invoice rows`);
        
        if (rows > 0) {
            console.log('🎯 Testing invoice detail panel...');
            
            // Click first invoice row
            await page.locator('#kt_invoices_table tbody tr').first().click();
            console.log('👆 Clicked first invoice row');
            
            // Wait for detail panel to appear
            try {
                await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
                console.log('✅ Detail panel appeared');
                
                // Take screenshot with detail panel
                await page.screenshot({ path: 'test-results/05-detail-panel.png', fullPage: true });
                console.log('📸 Detail panel screenshot saved');
                
                // Test sticky positioning
                console.log('📌 Testing sticky positioning...');
                
                // Set smaller viewport to force horizontal scroll
                await page.setViewportSize({ width: 800, height: 600 });
                await page.waitForTimeout(1000);
                
                // Take screenshot at smaller viewport
                await page.screenshot({ path: 'test-results/06-small-viewport.png', fullPage: true });
                
                // Check if table can scroll horizontally
                const canScroll = await page.locator('#kt_invoices_table_container').evaluate(el => {
                    return el.scrollWidth > el.clientWidth;
                });
                
                console.log(`🔄 Table can scroll horizontally: ${canScroll}`);
                
                if (canScroll) {
                    // Get initial panel position
                    const initialPosition = await page.locator('.invoice-detail-panel').boundingBox();
                    console.log('📍 Initial panel position:', initialPosition);
                    
                    // Scroll table horizontally
                    await page.locator('#kt_invoices_table_container').evaluate(el => {
                        el.scrollLeft = 200;
                    });
                    
                    await page.waitForTimeout(500);
                    
                    // Take screenshot after scroll
                    await page.screenshot({ path: 'test-results/07-after-scroll.png', fullPage: true });
                    console.log('📸 After scroll screenshot saved');
                    
                    // Get panel position after scroll
                    const afterPosition = await page.locator('.invoice-detail-panel').boundingBox();
                    console.log('📍 Panel position after scroll:', afterPosition);
                    
                    // Check if panel stayed in place (sticky behavior)
                    const xDifference = Math.abs(afterPosition.x - initialPosition.x);
                    const yDifference = Math.abs(afterPosition.y - initialPosition.y);
                    
                    console.log(`📏 Position difference: x=${xDifference}px, y=${yDifference}px`);
                    
                    if (xDifference < 10 && yDifference < 10) {
                        console.log('✅ PASS: Panel maintained sticky position during scroll');
                    } else {
                        console.log('❌ FAIL: Panel moved during scroll - sticky positioning may not be working');
                    }
                    
                    // Test extreme scroll
                    await page.locator('#kt_invoices_table_container').evaluate(el => {
                        el.scrollLeft = el.scrollWidth - el.clientWidth;
                    });
                    
                    await page.waitForTimeout(500);
                    await page.screenshot({ path: 'test-results/08-max-scroll.png', fullPage: true });
                    console.log('📸 Maximum scroll screenshot saved');
                    
                } else {
                    console.log('⚠️ Table does not need horizontal scroll at current viewport');
                }
                
                // Test multiple panels
                console.log('🔢 Testing multiple panels...');
                
                // Try to open second panel
                const allRows = await page.locator('#kt_invoices_table tbody tr:not(.invoice-detail-row)').count();
                if (allRows > 1) {
                    await page.locator('#kt_invoices_table tbody tr:not(.invoice-detail-row)').nth(1).click();
                    await page.waitForTimeout(2000);
                    
                    const panelCount = await page.locator('.invoice-detail-panel').count();
                    console.log(`📊 Number of open panels: ${panelCount}`);
                    
                    await page.screenshot({ path: 'test-results/09-multiple-panels.png', fullPage: true });
                    console.log('📸 Multiple panels screenshot saved');
                }
                
                // Test CSS properties
                console.log('🎨 Checking CSS properties...');
                
                const panelStyles = await page.locator('.invoice-detail-panel').evaluate(el => {
                    const styles = window.getComputedStyle(el);
                    return {
                        position: styles.position,
                        left: styles.left,
                        zIndex: styles.zIndex,
                        backgroundColor: styles.backgroundColor
                    };
                });
                
                console.log('🎨 Panel CSS properties:', panelStyles);
                
                // Verify sticky positioning
                if (panelStyles.position === 'sticky') {
                    console.log('✅ PASS: Panel has sticky positioning');
                } else {
                    console.log('❌ FAIL: Panel does not have sticky positioning');
                }
                
                if (panelStyles.left === '0px') {
                    console.log('✅ PASS: Panel has correct left position');
                } else {
                    console.log('❌ FAIL: Panel left position is not 0px');
                }
                
                const zIndex = parseInt(panelStyles.zIndex);
                if (zIndex > 90) {
                    console.log('✅ PASS: Panel has adequate z-index');
                } else {
                    console.log('❌ FAIL: Panel z-index may be too low');
                }
                
            } catch (error) {
                console.log('❌ Detail panel did not appear:', error.message);
                await page.screenshot({ path: 'test-results/05-panel-error.png', fullPage: true });
            }
            
        } else {
            console.log('⚠️ No invoice rows found for testing');
        }
        
        // Final screenshot
        await page.screenshot({ path: 'test-results/10-final-state.png', fullPage: true });
        console.log('📸 Final state screenshot saved');
        
        console.log('🎉 Chrome test completed successfully!');
        console.log('📁 Check test-results folder for screenshots');
    });
    
    test('Test responsive behavior', async ({ page }) => {
        console.log('📱 Testing responsive behavior...');
        
        // Login first
        await page.goto('http://yukimart.local/admin/login');
        await page.fill('input[name="email"]', 'yukimart@gmail.com');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard');
        
        // Go to invoices
        await page.goto('http://yukimart.local/admin/invoices');
        await page.waitForSelector('#kt_invoices_table', { timeout: 15000 });
        
        // Test different viewport sizes
        const viewports = [
            { width: 1400, height: 900, name: 'desktop-large' },
            { width: 1200, height: 800, name: 'desktop-medium' },
            { width: 1024, height: 768, name: 'tablet-landscape' },
            { width: 768, height: 1024, name: 'tablet-portrait' },
            { width: 480, height: 800, name: 'mobile-large' },
            { width: 375, height: 667, name: 'mobile-medium' }
        ];
        
        for (const viewport of viewports) {
            console.log(`📐 Testing ${viewport.name} (${viewport.width}x${viewport.height})`);
            
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            await page.waitForTimeout(1000);
            
            await page.screenshot({ 
                path: `test-results/responsive-${viewport.name}.png`, 
                fullPage: true 
            });
            
            // Try to expand panel at this size
            const rows = await page.locator('#kt_invoices_table tbody tr').count();
            if (rows > 0) {
                await page.locator('#kt_invoices_table tbody tr').first().click();
                await page.waitForTimeout(1000);
                
                const panelExists = await page.locator('.invoice-detail-panel').count() > 0;
                if (panelExists) {
                    await page.screenshot({ 
                        path: `test-results/responsive-${viewport.name}-with-panel.png`, 
                        fullPage: true 
                    });
                    console.log(`✅ Panel works at ${viewport.name}`);
                } else {
                    console.log(`⚠️ Panel not found at ${viewport.name}`);
                }
            }
        }
        
        console.log('📱 Responsive testing completed');
    });
});
