const { test, expect } = require('@playwright/test');

test.describe('Invoice Detail Panel Width Fix', () => {
    test.beforeEach(async ({ page }) => {
        // Login
        await page.goto('http://yukimart.local/admin/login');
        await page.fill('input[name="email"]', 'yukimart@gmail.com');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        
        // Navigate to invoices page
        await page.goto('http://yukimart.local/admin/invoices');
        await page.waitForLoadState('networkidle');
        
        // Wait for table to load
        await page.waitForSelector('#kt_invoices_table tbody tr', { timeout: 10000 });
    });

    test('Panel width matches table container width', async ({ page }) => {
        // Get table container width
        const containerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        console.log('Table container width:', containerWidth);
        
        // Click on first invoice row to expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        
        // Wait for detail panel to load
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Get panel width
        const panelWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        console.log('Panel width:', panelWidth);
        
        // Verify panel width matches container width (allow 1px tolerance for rounding)
        expect(Math.abs(panelWidth - containerWidth)).toBeLessThanOrEqual(1);
    });

    test('Panel has no scroll bars', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Check panel overflow properties
        const panelOverflow = await page.locator('.invoice-detail-panel').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                overflow: styles.overflow,
                overflowX: styles.overflowX,
                overflowY: styles.overflowY
            };
        });
        
        console.log('Panel overflow styles:', panelOverflow);
        
        // Verify no scroll
        expect(panelOverflow.overflow).toBe('visible');
        expect(panelOverflow.overflowX).toBe('visible');
        expect(panelOverflow.overflowY).toBe('visible');
        
        // Check tab content overflow
        const tabContentOverflow = await page.locator('.invoice-detail-panel .tab-content').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                overflow: styles.overflow,
                overflowX: styles.overflowX,
                overflowY: styles.overflowY
            };
        });
        
        console.log('Tab content overflow styles:', tabContentOverflow);
        
        // Verify tab content has no scroll
        expect(tabContentOverflow.overflow).toBe('visible');
        expect(tabContentOverflow.overflowX).toBe('visible');
        expect(tabContentOverflow.overflowY).toBe('visible');
    });

    test('Panel width constraints are properly applied', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Check CSS width properties
        const widthProperties = await page.locator('.invoice-detail-panel').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                width: styles.width,
                maxWidth: styles.maxWidth,
                minWidth: styles.minWidth,
                boxSizing: styles.boxSizing
            };
        });
        
        console.log('Panel width properties:', widthProperties);
        
        // Verify width constraints
        expect(widthProperties.boxSizing).toBe('border-box');
        
        // Check that max-width and min-width are set to prevent expansion
        const containerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        const panelWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        
        expect(panelWidth).toBeLessThanOrEqual(containerWidth);
    });

    test('Detail row and container have proper width styling', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-row', { timeout: 10000 });
        
        // Check detail row width
        const rowWidth = await page.locator('.invoice-detail-row').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                width: styles.width,
                maxWidth: styles.maxWidth,
                minWidth: styles.minWidth
            };
        });
        
        console.log('Detail row width properties:', rowWidth);
        
        // Check detail container width
        const containerStyles = await page.locator('.invoice-detail-container').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                width: styles.width,
                maxWidth: styles.maxWidth,
                minWidth: styles.minWidth,
                overflow: styles.overflow,
                boxSizing: styles.boxSizing
            };
        });
        
        console.log('Detail container styles:', containerStyles);
        
        // Verify container properties
        expect(containerStyles.overflow).toBe('visible');
        expect(containerStyles.boxSizing).toBe('border-box');
    });

    test('Panel content wraps properly within fixed width', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Get container and panel widths
        const containerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        const panelWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        
        // Check if any content overflows horizontally
        const hasHorizontalOverflow = await page.locator('.invoice-detail-panel').evaluate(el => {
            return el.scrollWidth > el.clientWidth;
        });
        
        console.log('Container width:', containerWidth);
        console.log('Panel width:', panelWidth);
        console.log('Has horizontal overflow:', hasHorizontalOverflow);
        
        // Verify no horizontal overflow
        expect(hasHorizontalOverflow).toBe(false);
        expect(panelWidth).toBeLessThanOrEqual(containerWidth);
    });

    test('Multiple panels maintain proper width', async ({ page }) => {
        // Expand first panel
        await page.click('#kt_invoices_table tbody tr:nth-child(1)');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Expand second panel
        await page.click('#kt_invoices_table tbody tr:nth-child(3)'); // Skip the detail row
        await page.waitForTimeout(1000);
        
        // Check if second panel exists
        const panelCount = await page.locator('.invoice-detail-panel').count();
        console.log('Number of panels:', panelCount);
        
        if (panelCount > 1) {
            // Get container width
            const containerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
            
            // Check all panels
            for (let i = 0; i < panelCount; i++) {
                const panelWidth = await page.locator('.invoice-detail-panel').nth(i).evaluate(el => el.offsetWidth);
                console.log(`Panel ${i + 1} width:`, panelWidth);
                expect(panelWidth).toBeLessThanOrEqual(containerWidth);
            }
        }
    });

    test('Panel maintains width on tab switching', async ({ page }) => {
        // Expand detail panel
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Get initial panel width
        const initialWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        
        // Switch to payment history tab if it exists
        const paymentTab = page.locator('a[href*="kt_invoice_payment"]');
        if (await paymentTab.count() > 0) {
            await paymentTab.click();
            await page.waitForTimeout(500);
            
            // Check panel width after tab switch
            const afterTabWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
            
            console.log('Initial width:', initialWidth);
            console.log('After tab switch width:', afterTabWidth);
            
            // Width should remain the same
            expect(Math.abs(afterTabWidth - initialWidth)).toBeLessThanOrEqual(1);
        }
    });

    test('Panel responsive behavior', async ({ page }) => {
        // Test desktop size
        await page.setViewportSize({ width: 1400, height: 900 });
        await page.click('#kt_invoices_table tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        const desktopWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        const desktopContainerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        
        console.log('Desktop - Panel width:', desktopWidth, 'Container width:', desktopContainerWidth);
        expect(desktopWidth).toBeLessThanOrEqual(desktopContainerWidth);
        
        // Test tablet size
        await page.setViewportSize({ width: 1000, height: 700 });
        await page.waitForTimeout(500);
        
        const tabletWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        const tabletContainerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        
        console.log('Tablet - Panel width:', tabletWidth, 'Container width:', tabletContainerWidth);
        expect(tabletWidth).toBeLessThanOrEqual(tabletContainerWidth);
        
        // Test mobile size
        await page.setViewportSize({ width: 768, height: 600 });
        await page.waitForTimeout(500);
        
        const mobileWidth = await page.locator('.invoice-detail-panel').evaluate(el => el.offsetWidth);
        const mobileContainerWidth = await page.locator('#kt_invoices_table_container').evaluate(el => el.offsetWidth);
        
        console.log('Mobile - Panel width:', mobileWidth, 'Container width:', mobileContainerWidth);
        expect(mobileWidth).toBeLessThanOrEqual(mobileContainerWidth);
    });
});
