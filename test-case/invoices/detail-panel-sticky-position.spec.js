const { test, expect } = require('@playwright/test');

// Helper function to login
async function login(page) {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
}

test.describe('Invoice Detail Panel Sticky Position', () => {
    test.beforeEach(async ({ page }) => {
        // Login
        await login(page);

        // Navigate to invoices page
        await page.goto('/admin/invoices');
        await page.waitForLoadState('networkidle');

        // Wait for table to load
        await page.waitForSelector('#kt_invoices_table tbody tr', { timeout: 15000 });
    });

    test('Detail panel has sticky positioning properties', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Check sticky positioning properties
        const panelStyles = await page.locator('.invoice-detail-panel').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                position: styles.position,
                left: styles.left,
                zIndex: styles.zIndex,
                background: styles.backgroundColor
            };
        });
        
        console.log('Panel sticky styles:', panelStyles);
        
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
                zIndex: styles.zIndex,
                background: styles.backgroundColor
            };
        });
        
        console.log('Container sticky styles:', containerStyles);
        
        // Verify container sticky positioning
        expect(containerStyles.position).toBe('sticky');
        expect(containerStyles.left).toBe('0px');
        expect(parseInt(containerStyles.zIndex)).toBeGreaterThan(90);
    });

    test('Detail panel stays fixed during horizontal table scroll', async ({ page }) => {
        // Set viewport to ensure horizontal scrolling is needed
        await page.setViewportSize({ width: 800, height: 600 });
        
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Get initial panel position
        const initialPanelPosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Initial panel position:', initialPanelPosition);
        
        // Check if table container has horizontal scroll
        const hasHorizontalScroll = await page.locator('#kt_invoices_table_container').evaluate(el => {
            return el.scrollWidth > el.clientWidth;
        });
        
        console.log('Table has horizontal scroll:', hasHorizontalScroll);
        
        if (hasHorizontalScroll) {
            // Scroll table horizontally
            await page.locator('#kt_invoices_table_container').evaluate(el => {
                el.scrollLeft = 200; // Scroll 200px to the right
            });
            
            // Wait for scroll to complete
            await page.waitForTimeout(500);
            
            // Get panel position after scroll
            const afterScrollPanelPosition = await page.locator('.invoice-detail-panel').boundingBox();
            console.log('Panel position after scroll:', afterScrollPanelPosition);
            
            // Panel should stay in the same position (sticky behavior)
            expect(Math.abs(afterScrollPanelPosition.x - initialPanelPosition.x)).toBeLessThan(5);
            expect(Math.abs(afterScrollPanelPosition.y - initialPanelPosition.y)).toBeLessThan(5);
            
            // Verify table has actually scrolled
            const scrollLeft = await page.locator('#kt_invoices_table_container').evaluate(el => el.scrollLeft);
            console.log('Table scroll position:', scrollLeft);
            expect(scrollLeft).toBeGreaterThan(100);
        } else {
            console.log('Table does not need horizontal scroll at current viewport size');
            // Force a smaller viewport to ensure scrolling
            await page.setViewportSize({ width: 600, height: 600 });
            await page.waitForTimeout(500);
            
            // Try scrolling again
            await page.locator('#kt_invoices_table_container').evaluate(el => {
                el.scrollLeft = 200;
            });
            
            await page.waitForTimeout(500);
            
            const finalPanelPosition = await page.locator('.invoice-detail-panel').boundingBox();
            console.log('Final panel position:', finalPanelPosition);
            
            // Panel should still be in viewport
            expect(finalPanelPosition.x).toBeGreaterThanOrEqual(0);
        }
    });

    test('Detail panel remains visible during extreme horizontal scroll', async ({ page }) => {
        // Set small viewport to force horizontal scrolling
        await page.setViewportSize({ width: 600, height: 600 });
        
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Scroll table to maximum right
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = el.scrollWidth - el.clientWidth;
        });
        
        await page.waitForTimeout(500);
        
        // Check if panel is still visible
        const panelVisible = await page.locator('.invoice-detail-panel').isVisible();
        expect(panelVisible).toBe(true);
        
        // Check panel position is still at left edge
        const panelPosition = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Panel position at max scroll:', panelPosition);
        
        // Panel should be at or near the left edge of viewport
        expect(panelPosition.x).toBeLessThan(50); // Allow some margin for browser differences
        
        // Scroll back to left
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = 0;
        });
        
        await page.waitForTimeout(500);
        
        // Panel should still be visible and positioned correctly
        const panelAfterScrollBack = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Panel position after scroll back:', panelAfterScrollBack);
        
        expect(panelAfterScrollBack.x).toBeLessThan(50);
    });

    test('Multiple detail panels maintain sticky positioning', async ({ page }) => {
        // Set viewport for horizontal scrolling
        await page.setViewportSize({ width: 800, height: 600 });
        
        // Expand first detail panel
        await page.click('#kt_invoices_table tbody tr:nth-child(1)');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Expand second detail panel (skip the detail row)
        await page.click('#kt_invoices_table tbody tr:nth-child(3)');
        await page.waitForTimeout(1000);
        
        // Check how many panels are open
        const panelCount = await page.locator('.invoice-detail-panel').count();
        console.log('Number of open panels:', panelCount);
        
        if (panelCount > 1) {
            // Get initial positions of all panels
            const initialPositions = [];
            for (let i = 0; i < panelCount; i++) {
                const position = await page.locator('.invoice-detail-panel').nth(i).boundingBox();
                initialPositions.push(position);
            }
            
            console.log('Initial positions of all panels:', initialPositions);
            
            // Scroll table horizontally
            await page.locator('#kt_invoices_table_container').evaluate(el => {
                el.scrollLeft = 150;
            });
            
            await page.waitForTimeout(500);
            
            // Check positions after scroll
            for (let i = 0; i < panelCount; i++) {
                const newPosition = await page.locator('.invoice-detail-panel').nth(i).boundingBox();
                console.log(`Panel ${i + 1} position after scroll:`, newPosition);
                
                // Each panel should maintain its position (sticky behavior)
                expect(Math.abs(newPosition.x - initialPositions[i].x)).toBeLessThan(5);
            }
        }
    });

    test('Detail panel sticky positioning works with tab switching', async ({ page }) => {
        // Set viewport for horizontal scrolling
        await page.setViewportSize({ width: 800, height: 600 });
        
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Scroll table horizontally
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = 100;
        });
        
        await page.waitForTimeout(500);
        
        // Get panel position before tab switch
        const positionBeforeTab = await page.locator('.invoice-detail-panel').boundingBox();
        console.log('Panel position before tab switch:', positionBeforeTab);
        
        // Switch to payment history tab if available
        const paymentTab = page.locator('a[href*="kt_invoice_payment"]');
        if (await paymentTab.count() > 0) {
            await paymentTab.click();
            await page.waitForTimeout(500);
            
            // Check panel position after tab switch
            const positionAfterTab = await page.locator('.invoice-detail-panel').boundingBox();
            console.log('Panel position after tab switch:', positionAfterTab);
            
            // Panel should maintain its sticky position
            expect(Math.abs(positionAfterTab.x - positionBeforeTab.x)).toBeLessThan(5);
            expect(Math.abs(positionAfterTab.y - positionBeforeTab.y)).toBeLessThan(5);
        }
    });

    test('Detail panel z-index hierarchy is correct', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Check z-index values
        const zIndexValues = await page.evaluate(() => {
            const panel = document.querySelector('.invoice-detail-panel');
            const container = document.querySelector('.invoice-detail-container');
            const row = document.querySelector('.invoice-detail-row td');
            
            return {
                panel: window.getComputedStyle(panel).zIndex,
                container: window.getComputedStyle(container).zIndex,
                row: window.getComputedStyle(row).zIndex
            };
        });
        
        console.log('Z-index values:', zIndexValues);
        
        // Verify z-index hierarchy: container > panel > row
        expect(parseInt(zIndexValues.container)).toBeGreaterThan(parseInt(zIndexValues.panel));
        expect(parseInt(zIndexValues.panel)).toBeGreaterThan(parseInt(zIndexValues.row));
        
        // All should be above 90 to ensure they stay on top
        expect(parseInt(zIndexValues.container)).toBeGreaterThan(90);
        expect(parseInt(zIndexValues.panel)).toBeGreaterThan(90);
        expect(parseInt(zIndexValues.row)).toBeGreaterThan(90);
    });

    test('Table container scroll behavior is preserved', async ({ page }) => {
        // Set viewport to ensure scrolling
        await page.setViewportSize({ width: 800, height: 600 });
        
        // Check table container scroll properties
        const scrollProperties = await page.locator('#kt_invoices_table_container').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                overflowX: styles.overflowX,
                position: styles.position,
                scrollWidth: el.scrollWidth,
                clientWidth: el.clientWidth,
                canScroll: el.scrollWidth > el.clientWidth
            };
        });
        
        console.log('Table container scroll properties:', scrollProperties);
        
        // Verify container can scroll horizontally
        expect(scrollProperties.overflowX).toBe('auto');
        expect(scrollProperties.position).toBe('relative');
        
        if (scrollProperties.canScroll) {
            // Test actual scrolling
            await page.locator('#kt_invoices_table_container').evaluate(el => {
                el.scrollLeft = 50;
            });
            
            const scrollLeft = await page.locator('#kt_invoices_table_container').evaluate(el => el.scrollLeft);
            expect(scrollLeft).toBeGreaterThan(0);
            
            console.log('Table successfully scrolled to position:', scrollLeft);
        }
    });
});
