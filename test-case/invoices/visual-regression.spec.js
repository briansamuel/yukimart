const { test, expect } = require('@playwright/test');

// Helper function to login
async function login(page) {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
}

test.describe('Invoice Detail Panel - Visual Regression Tests', () => {
    test.beforeEach(async ({ page }) => {
        // Set consistent viewport
        await page.setViewportSize({ width: 1200, height: 800 });
        
        // Login
        await login(page);
        
        // Navigate to invoices page
        await page.goto('/admin/invoices');
        await page.waitForLoadState('networkidle');
        
        // Wait for table to load
        await page.waitForSelector('#kt_invoices_table tbody tr', { timeout: 15000 });
        await page.waitForTimeout(2000);
    });

    test('invoice list page initial state', async ({ page }) => {
        // Take full page screenshot
        await expect(page).toHaveScreenshot('invoice-list-initial.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Take screenshot of just the table area
        await expect(page.locator('#kt_invoices_table_container')).toHaveScreenshot('invoice-table-initial.png');
    });

    test('detail panel expansion visual test', async ({ page }) => {
        // Click first row to expand
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        
        // Wait for panel to appear
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Take screenshot of expanded panel
        await expect(page).toHaveScreenshot('invoice-detail-panel-expanded.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Take screenshot of just the detail panel
        await expect(page.locator('.invoice-detail-panel')).toHaveScreenshot('detail-panel-content.png');
    });

    test('horizontal scroll visual behavior', async ({ page }) => {
        // Set smaller viewport to force scrolling
        await page.setViewportSize({ width: 800, height: 600 });
        await page.waitForTimeout(500);
        
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Take screenshot before scroll
        await expect(page).toHaveScreenshot('before-horizontal-scroll.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Scroll table horizontally
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = 200;
        });
        await page.waitForTimeout(500);
        
        // Take screenshot after scroll
        await expect(page).toHaveScreenshot('after-horizontal-scroll.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Scroll to maximum right
        await page.locator('#kt_invoices_table_container').evaluate(el => {
            el.scrollLeft = el.scrollWidth - el.clientWidth;
        });
        await page.waitForTimeout(500);
        
        // Take screenshot at maximum scroll
        await expect(page).toHaveScreenshot('maximum-horizontal-scroll.png', {
            fullPage: true,
            animations: 'disabled'
        });
    });

    test('multiple panels visual test', async ({ page }) => {
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
            await expect(page).toHaveScreenshot('multiple-panels-expanded.png', {
                fullPage: true,
                animations: 'disabled'
            });
            
            // Test scrolling with multiple panels
            await page.setViewportSize({ width: 800, height: 600 });
            await page.waitForTimeout(500);
            
            await page.locator('#kt_invoices_table_container').evaluate(el => {
                el.scrollLeft = 150;
            });
            await page.waitForTimeout(500);
            
            await expect(page).toHaveScreenshot('multiple-panels-scrolled.png', {
                fullPage: true,
                animations: 'disabled'
            });
        }
    });

    test('responsive design visual test', async ({ page }) => {
        // Expand detail panel first
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
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
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            await page.waitForTimeout(1000);
            
            await expect(page).toHaveScreenshot(`responsive-${viewport.name}.png`, {
                fullPage: true,
                animations: 'disabled'
            });
        }
    });

    test('tab switching visual test', async ({ page }) => {
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Take screenshot of default tab
        await expect(page.locator('.invoice-detail-panel')).toHaveScreenshot('detail-panel-default-tab.png');
        
        // Check if payment history tab exists
        const paymentTab = page.locator('a[href*="kt_invoice_payment"]');
        const tabExists = await paymentTab.count() > 0;
        
        if (tabExists) {
            await paymentTab.click();
            await page.waitForTimeout(1000);
            
            // Take screenshot of payment history tab
            await expect(page.locator('.invoice-detail-panel')).toHaveScreenshot('detail-panel-payment-tab.png');
        }
    });

    test('column visibility interaction visual test', async ({ page }) => {
        // Take screenshot of initial state
        await expect(page).toHaveScreenshot('before-column-visibility.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Click column visibility trigger if it exists
        const columnTrigger = page.locator('#column_visibility_trigger');
        const triggerExists = await columnTrigger.count() > 0;
        
        if (triggerExists) {
            await columnTrigger.click();
            await page.waitForTimeout(500);
            
            // Take screenshot with column visibility panel open
            await expect(page).toHaveScreenshot('column-visibility-panel-open.png', {
                fullPage: true,
                animations: 'disabled'
            });
            
            // Close panel
            await page.click('body');
            await page.waitForTimeout(500);
        }
        
        // Expand detail panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Take screenshot with detail panel and test column visibility interaction
        await expect(page).toHaveScreenshot('detail-panel-with-column-visibility.png', {
            fullPage: true,
            animations: 'disabled'
        });
    });

    test('loading states visual test', async ({ page }) => {
        // Intercept the detail panel AJAX request to simulate loading
        await page.route('**/admin/invoices/*/detail-panel', async route => {
            // Delay the response to capture loading state
            await page.waitForTimeout(2000);
            await route.continue();
        });
        
        // Click to expand panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        
        // Take screenshot of loading state
        await page.waitForSelector('.invoice-detail-row', { timeout: 5000 });
        await page.waitForTimeout(500);
        
        await expect(page).toHaveScreenshot('detail-panel-loading-state.png', {
            fullPage: true,
            animations: 'disabled'
        });
        
        // Wait for content to load
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        await page.waitForTimeout(1000);
        
        // Take screenshot of loaded state
        await expect(page).toHaveScreenshot('detail-panel-loaded-state.png', {
            fullPage: true,
            animations: 'disabled'
        });
    });

    test('error state visual test', async ({ page }) => {
        // Intercept the detail panel AJAX request to simulate error
        await page.route('**/admin/invoices/*/detail-panel', async route => {
            await route.abort('failed');
        });
        
        // Click to expand panel
        const firstRow = page.locator('#kt_invoices_table tbody tr').first();
        await firstRow.click();
        
        // Wait for error state
        await page.waitForTimeout(3000);
        
        // Take screenshot of error state
        await expect(page).toHaveScreenshot('detail-panel-error-state.png', {
            fullPage: true,
            animations: 'disabled'
        });
    });
});
