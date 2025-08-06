const { test, expect } = require('@playwright/test');

// Helper function to login
async function login(page) {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
}

// Helper function to wait for element with retry
async function waitForElementWithRetry(page, selector, timeout = 10000) {
    let attempts = 0;
    const maxAttempts = 3;
    
    while (attempts < maxAttempts) {
        try {
            await page.waitForSelector(selector, { timeout: timeout / maxAttempts });
            return true;
        } catch (error) {
            attempts++;
            if (attempts === maxAttempts) {
                throw error;
            }
            await page.waitForTimeout(1000);
        }
    }
}

test.describe('Invoice Detail Panel UI - Sticky Position Tests', () => {
    test.beforeEach(async ({ page }) => {
        // Set viewport for consistent testing
        await page.setViewportSize({ width: 1200, height: 800 });
        
        // Login
        await login(page);
        
        // Navigate to invoices page
        await page.goto('/admin/invoices');
        await page.waitForLoadState('networkidle');
        
        // Wait for table to load with retry
        await waitForElementWithRetry(page, '#kt_invoices_table tbody tr');
        
        // Wait a bit more for any dynamic content
        await page.waitForTimeout(2000);
    });

    test('should have correct CSS properties for sticky positioning', async ({ page }) => {
        // Take initial screenshot
        await page.screenshot({ path: 'test-results/01-initial-page.png', fullPage: true });
        
        // Click first invoice row to expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        
        // Wait for detail panel to appear
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000); // Wait for animations
        
        // Take screenshot after panel expansion
        await page.screenshot({ path: 'test-results/02-panel-expanded.png', fullPage: true });
        
        // Check CSS properties of detail panel
        const panelStyles = await page.locator('.invoice-detail-panel').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                position: styles.position,
                left: styles.left,
                zIndex: styles.zIndex,
                backgroundColor: styles.backgroundColor
            };
        });
        
        console.log('Panel styles:', panelStyles);
        
        // Verify sticky positioning
        expect(panelStyles.position).toBe('sticky');
        expect(panelStyles.left).toBe('0px');
        expect(parseInt(panelStyles.zIndex)).toBeGreaterThan(90);
        
        // Check container styles
        const containerStyles = await page.locator('.invoice-detail-container').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                position: styles.position,
                left: styles.left,
                zIndex: styles.zIndex
            };
        });
        
        console.log('Container styles:', containerStyles);
        
        expect(containerStyles.position).toBe('sticky');
        expect(containerStyles.left).toBe('0px');
        expect(parseInt(containerStyles.zIndex)).toBeGreaterThan(90);
    });

    test('should maintain position during horizontal scroll', async ({ page }) => {
        // Set smaller viewport to force horizontal scrolling
        await page.setViewportSize({ width: 800, height: 600 });
        await page.waitForTimeout(500);
        
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Get initial panel position
        const initialPosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Initial panel position:', initialPosition);
        
        // Take screenshot before scroll
        await page.screenshot({ path: 'test-results/03-before-scroll.png', fullPage: true });
        
        // Check if table has horizontal scroll
        const tableContainer = page.locator('#kt_invoices_table_container');
        const scrollInfo = await tableContainer.evaluate(el => ({
            scrollWidth: el.scrollWidth,
            clientWidth: el.clientWidth,
            canScroll: el.scrollWidth > el.clientWidth
        }));
        
        console.log('Scroll info:', scrollInfo);
        
        if (scrollInfo.canScroll) {
            // Scroll table horizontally
            await tableContainer.evaluate(el => {
                el.scrollLeft = 200;
            });
            
            await page.waitForTimeout(500);
            
            // Take screenshot after scroll
            await page.screenshot({ path: 'test-results/04-after-scroll.png', fullPage: true });
            
            // Get panel position after scroll
            const afterScrollPosition = await page.locator('.invoice-detail-panel').boundingBox();
            console.log('Panel position after scroll:', afterScrollPosition);
            
            // Panel should maintain its position (sticky behavior)
            expect(Math.abs(afterScrollPosition.x - initialPosition.x)).toBeLessThan(10);
            expect(Math.abs(afterScrollPosition.y - initialPosition.y)).toBeLessThan(10);
            
            // Verify table actually scrolled
            const scrollLeft = await tableContainer.evaluate(el => el.scrollLeft);
            expect(scrollLeft).toBeGreaterThan(100);
            
        } else {
            console.log('Table does not need horizontal scroll at current viewport');
            // Force even smaller viewport
            await page.setViewportSize({ width: 600, height: 500 });
            await page.waitForTimeout(500);
            
            // Try scrolling again
            await tableContainer.evaluate(el => {
                el.scrollLeft = 150;
            });
            
            await page.waitForTimeout(500);
            await page.screenshot({ path: 'test-results/04-after-forced-scroll.png', fullPage: true });
        }
    });

    test('should handle multiple panels with sticky positioning', async ({ page }) => {
        // Expand first panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Try to expand second panel
        const rows = page.locator('#kt_invoices_table tbody tr:not(.invoice-detail-row)');
        const rowCount = await rows.count();
        
        if (rowCount > 1) {
            const secondRow = rows.nth(1);
            await secondRow.click();
            await page.waitForTimeout(2000);
            
            // Take screenshot with multiple panels
            await page.screenshot({ path: 'test-results/05-multiple-panels.png', fullPage: true });
            
            // Check how many panels are open
            const panelCount = await page.locator('.invoice-detail-panel').count();
            console.log('Number of panels:', panelCount);
            
            if (panelCount > 1) {
                // Set smaller viewport for scrolling
                await page.setViewportSize({ width: 800, height: 600 });
                await page.waitForTimeout(500);
                
                // Scroll table
                await page.locator('#kt_invoices_table_container').evaluate(el => {
                    el.scrollLeft = 150;
                });
                
                await page.waitForTimeout(500);
                await page.screenshot({ path: 'test-results/06-multiple-panels-scrolled.png', fullPage: true });
                
                // Check all panels maintain position
                const panels = page.locator('.invoice-detail-panel');
                for (let i = 0; i < panelCount; i++) {
                    const panelPosition = await panels.nth(i).boundingBox();
                    console.log(`Panel ${i + 1} position:`, panelPosition);
                    
                    // Each panel should be near the left edge
                    expect(panelPosition.x).toBeLessThan(100);
                }
            }
        }
    });

    test('should work correctly with tab switching', async ({ page }) => {
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Set smaller viewport and scroll
        await page.setViewportSize({ width: 800, height: 600 });
        await page.waitForTimeout(500);
        
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = 100;
        });
        await page.waitForTimeout(500);
        
        // Get panel position before tab switch
        const positionBefore = await page.locator('.invoice-detail-panel').boundingBox();
        
        // Take screenshot before tab switch
        await page.screenshot({ path: 'test-results/07-before-tab-switch.png', fullPage: true });
        
        // Try to switch tabs if payment history tab exists
        const paymentTab = page.locator('a[href*="kt_invoice_payment"]');
        const tabExists = await paymentTab.count() > 0;
        
        if (tabExists) {
            await paymentTab.click();
            await page.waitForTimeout(1000);
            
            // Take screenshot after tab switch
            await page.screenshot({ path: 'test-results/08-after-tab-switch.png', fullPage: true });
            
            // Check panel position after tab switch
            const positionAfter = await page.locator('.invoice-detail-panel').boundingBox();
            
            // Panel should maintain sticky position
            expect(Math.abs(positionAfter.x - positionBefore.x)).toBeLessThan(10);
            expect(Math.abs(positionAfter.y - positionBefore.y)).toBeLessThan(10);
        } else {
            console.log('Payment history tab not found, skipping tab switch test');
        }
    });

    test('should maintain sticky position at different viewport sizes', async ({ page }) => {
        // Test desktop size
        await page.setViewportSize({ width: 1400, height: 900 });
        await page.waitForTimeout(500);
        
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        await page.screenshot({ path: 'test-results/09-desktop-size.png', fullPage: true });
        
        const desktopPosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Desktop position:', desktopPosition);
        
        // Test tablet size
        await page.setViewportSize({ width: 1024, height: 768 });
        await page.waitForTimeout(1000);
        
        await page.screenshot({ path: 'test-results/10-tablet-size.png', fullPage: true });
        
        const tabletPosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Tablet position:', tabletPosition);
        
        // Test mobile size
        await page.setViewportSize({ width: 768, height: 600 });
        await page.waitForTimeout(1000);
        
        await page.screenshot({ path: 'test-results/11-mobile-size.png', fullPage: true });
        
        const mobilePosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Mobile position:', mobilePosition);
        
        // Panel should be visible at all sizes
        expect(desktopPosition.width).toBeGreaterThan(0);
        expect(tabletPosition.width).toBeGreaterThan(0);
        expect(mobilePosition.width).toBeGreaterThan(0);
    });

    test('should have proper z-index hierarchy', async ({ page }) => {
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Check z-index values
        const zIndexValues = await page.evaluate(() => {
            const panel = document.querySelector('.invoice-detail-panel');
            const container = document.querySelector('.invoice-detail-container');
            const row = document.querySelector('.invoice-detail-row td');
            
            return {
                panel: panel ? window.getComputedStyle(panel).zIndex : null,
                container: container ? window.getComputedStyle(container).zIndex : null,
                row: row ? window.getComputedStyle(row).zIndex : null
            };
        });
        
        console.log('Z-index values:', zIndexValues);
        
        // Verify z-index hierarchy
        if (zIndexValues.container && zIndexValues.panel && zIndexValues.row) {
            expect(parseInt(zIndexValues.container)).toBeGreaterThan(parseInt(zIndexValues.panel));
            expect(parseInt(zIndexValues.panel)).toBeGreaterThan(parseInt(zIndexValues.row));
            
            // All should be above 90
            expect(parseInt(zIndexValues.container)).toBeGreaterThan(90);
            expect(parseInt(zIndexValues.panel)).toBeGreaterThan(90);
            expect(parseInt(zIndexValues.row)).toBeGreaterThan(90);
        }
        
        await page.screenshot({ path: 'test-results/12-z-index-test.png', fullPage: true });
    });
});
