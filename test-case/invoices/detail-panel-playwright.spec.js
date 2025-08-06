const { test, expect } = require('@playwright/test');

test.describe('Invoice Detail Panel Tests', () => {
    test.beforeEach(async ({ page }) => {
        // Login
        await page.goto('http://yukimart.local/admin/login');
        await page.fill('input[name="email"]', 'yukimart@gmail.com');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        
        // Navigate to invoices page
        await page.goto('http://yukimart.local/admin/invoices');
        await page.waitForLoadState('networkidle');
    });

    test('Detail panel has fixed width and proper layout', async ({ page }) => {
        // Click on first invoice row to expand detail panel
        await page.click('tbody tr:first-child');
        
        // Wait for detail panel to load
        await page.waitForSelector('.invoice-detail-panel', { timeout: 10000 });
        
        // Check fixed width
        const panel = page.locator('.invoice-detail-panel');
        await expect(panel).toHaveCSS('width', '600px');
        await expect(panel).toHaveCSS('min-width', '600px');
        await expect(panel).toHaveCSS('max-width', '600px');
        
        // Verify customer header elements
        await expect(page.locator('.invoice-detail-panel h3')).toBeVisible();
        await expect(page.locator('.invoice-detail-panel .badge')).toBeVisible();
    });

    test('Tab navigation works correctly', async ({ page }) => {
        // Expand detail panel
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Verify default tab is active
        const infoTab = page.locator('a[href*="kt_invoice_info"]');
        const paymentTab = page.locator('a[href*="kt_invoice_payment"]');
        
        await expect(infoTab).toHaveClass(/active/);
        
        // Click payment history tab
        await paymentTab.click();
        await page.waitForTimeout(500); // Wait for tab transition
        
        // Verify tab switched
        await expect(paymentTab).toHaveClass(/active/);
        await expect(infoTab).not.toHaveClass(/active/);
        
        // Switch back to info tab
        await infoTab.click();
        await page.waitForTimeout(500);
        
        await expect(infoTab).toHaveClass(/active/);
        await expect(paymentTab).not.toHaveClass(/active/);
    });

    test('Information tab displays correct content', async ({ page }) => {
        // Expand detail panel
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Verify information fields
        await expect(page.locator('text=Người tạo:')).toBeVisible();
        await expect(page.locator('text=Người bán:')).toBeVisible();
        await expect(page.locator('text=Ngày bán:')).toBeVisible();
        await expect(page.locator('text=Kênh bán:')).toBeVisible();
        await expect(page.locator('text=Bảng giá:')).toBeVisible();
        
        // Verify product table
        await expect(page.locator('th:has-text("Mã hàng")')).toBeVisible();
        await expect(page.locator('th:has-text("Tên hàng")')).toBeVisible();
        await expect(page.locator('th:has-text("Số lượng")')).toBeVisible();
        await expect(page.locator('th:has-text("Đơn giá")')).toBeVisible();
        await expect(page.locator('th:has-text("Giảm giá")')).toBeVisible();
        await expect(page.locator('th:has-text("Giá bán")')).toBeVisible();
        await expect(page.locator('th:has-text("Thành tiền")')).toBeVisible();
        
        // Verify summary section
        await expect(page.locator('text=Tổng tiền hàng')).toBeVisible();
        await expect(page.locator('text=Giảm giá hóa đơn')).toBeVisible();
        await expect(page.locator('text=Khách cần trả')).toBeVisible();
        await expect(page.locator('text=Khách đã trả')).toBeVisible();
    });

    test('Payment history tab displays correctly', async ({ page }) => {
        // Expand detail panel
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Click payment history tab
        await page.click('a[href*="kt_invoice_payment"]');
        await page.waitForTimeout(500);
        
        // Check if payment history exists or empty state is shown
        const hasPayments = await page.locator('.payment-history-item').count() > 0;
        
        if (hasPayments) {
            // Verify payment history items
            await expect(page.locator('.payment-history-item').first()).toBeVisible();
            
            // Check payment method icons
            const paymentIcons = page.locator('.payment-history-item .symbol i');
            await expect(paymentIcons.first()).toBeVisible();
            
            // Verify payment amounts are displayed
            await expect(page.locator('.payment-history-item .text-success')).toBeVisible();
        } else {
            // Verify empty state
            await expect(page.locator('text=Chưa có lịch sử thanh toán')).toBeVisible();
            await expect(page.locator('text=Hóa đơn này chưa có giao dịch thanh toán nào')).toBeVisible();
        }
    });

    test('Action buttons are displayed and positioned correctly', async ({ page }) => {
        // Expand detail panel
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Verify action buttons
        await expect(page.locator('button:has-text("Hủy")')).toBeVisible();
        await expect(page.locator('button:has-text("Trả hàng")')).toBeVisible();
        
        // Check button positioning (should be centered)
        const buttonContainer = page.locator('.d-flex.justify-content-center.gap-3');
        await expect(buttonContainer).toBeVisible();
    });

    test('Panel maintains fixed width during table scroll', async ({ page }) => {
        // Expand detail panel
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Get initial panel position and width
        const panel = page.locator('.invoice-detail-panel');
        const initialBox = await panel.boundingBox();
        
        // Scroll table horizontally if possible
        await page.evaluate(() => {
            const table = document.querySelector('.table-responsive');
            if (table) {
                table.scrollLeft = 100;
            }
        });
        
        await page.waitForTimeout(500);
        
        // Verify panel width and position unchanged
        const afterScrollBox = await panel.boundingBox();
        expect(afterScrollBox.width).toBe(initialBox.width);
        expect(afterScrollBox.x).toBe(initialBox.x);
    });

    test('Panel is responsive on different screen sizes', async ({ page }) => {
        // Test desktop size (1400px+)
        await page.setViewportSize({ width: 1500, height: 900 });
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        let panel = page.locator('.invoice-detail-panel');
        await expect(panel).toHaveCSS('width', '600px');
        
        // Close panel
        await page.click('tbody tr:first-child');
        await page.waitForTimeout(500);
        
        // Test medium desktop size (1200-1400px)
        await page.setViewportSize({ width: 1300, height: 900 });
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        panel = page.locator('.invoice-detail-panel');
        await expect(panel).toHaveCSS('width', '500px');
        
        // Close panel
        await page.click('tbody tr:first-child');
        await page.waitForTimeout(500);
        
        // Test small desktop size (992-1200px)
        await page.setViewportSize({ width: 1100, height: 900 });
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        panel = page.locator('.invoice-detail-panel');
        await expect(panel).toHaveCSS('width', '450px');
    });

    test('Payment method icons display correctly', async ({ page }) => {
        // This test requires invoices with different payment methods
        // We'll check if payment icons are displayed when payments exist
        
        await page.click('tbody tr:first-child');
        await page.waitForSelector('.invoice-detail-panel');
        
        // Switch to payment history tab
        await page.click('a[href*="kt_invoice_payment"]');
        await page.waitForTimeout(500);
        
        // Check if payment items exist
        const paymentItems = await page.locator('.payment-history-item').count();
        
        if (paymentItems > 0) {
            // Verify payment method icons are present
            const icons = page.locator('.payment-history-item .symbol i');
            await expect(icons.first()).toBeVisible();
            
            // Check that icon has appropriate FontAwesome class
            const iconClass = await icons.first().getAttribute('class');
            expect(iconClass).toMatch(/fa-/);
        }
    });

    test('Panel loading performance', async ({ page }) => {
        const startTime = Date.now();
        
        // Click to expand panel
        await page.click('tbody tr:first-child');
        
        // Wait for panel to be fully loaded
        await page.waitForSelector('.invoice-detail-panel');
        await page.waitForSelector('.tab-content');
        
        const endTime = Date.now();
        const loadTime = endTime - startTime;
        
        // Panel should load within 3 seconds
        expect(loadTime).toBeLessThan(3000);
        
        // Verify all essential elements are loaded
        await expect(page.locator('.nav-tabs')).toBeVisible();
        await expect(page.locator('.tab-pane.active')).toBeVisible();
    });
});
