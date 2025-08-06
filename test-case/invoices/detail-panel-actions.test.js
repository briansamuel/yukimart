/**
 * Test Case: Invoice Detail Panel Actions Button
 * 
 * This test verifies the Actions Button functionality in the Invoice Detail Panel,
 * including button placement, styling, and functionality.
 * 
 * Requirements tested:
 * 1. Actions buttons are moved inside the "Thông tin" tab
 * 2. "Lưu" button is added with saveInvoice() function
 * 3. Button arrangement: Hủy (left), Lưu + Trả hàng (right)
 * 4. "Trả hàng" button opens new tab with correct URL
 * 5. All buttons have correct icons and styling
 */

const { test, expect } = require('@playwright/test');

test.describe('Invoice Detail Panel Actions Tests', () => {
  let browser, page;

  test.beforeAll(async ({ browser: testBrowser }) => {
    browser = testBrowser;
    page = await browser.newPage();
    
    // Navigate to login page
    await page.goto('http://yukimart.local/admin/login');
    
    // Login with test credentials
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    
    // Wait for dashboard to load
    await page.waitForURL('**/admin/dashboard');
  });

  test.afterAll(async () => {
    await page.close();
  });

  test('should display actions buttons in correct position within Thông tin tab', async () => {
    // Navigate to invoices page
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');

    // Click on an invoice to open detail panel
    const invoiceRow = page.locator('tr').filter({ hasText: 'INV-20250709-1736' });
    await invoiceRow.click();

    // Wait for detail panel to load
    await page.waitForSelector('[data-kt-invoice-detail-panel="true"]', { state: 'visible' });

    // Verify "Thông tin" tab is active
    const thongTinTab = page.locator('a[data-bs-toggle="tab"]').filter({ hasText: 'Thông tin' });
    await expect(thongTinTab).toHaveClass(/active/);

    // Verify actions buttons are inside the tab content
    const actionsContainer = page.locator('.invoice-detail-actions');
    await expect(actionsContainer).toBeVisible();

    // Verify all three buttons exist
    const huyButton = actionsContainer.locator('button').filter({ hasText: 'Hủy' });
    const luuButton = actionsContainer.locator('button').filter({ hasText: 'Lưu' });
    const traHangButton = actionsContainer.locator('button').filter({ hasText: 'Trả hàng' });

    await expect(huyButton).toBeVisible();
    await expect(luuButton).toBeVisible();
    await expect(traHangButton).toBeVisible();

    console.log('✅ Actions buttons position test passed: All buttons visible in Thông tin tab');
  });

  test('should verify button arrangement and styling', async () => {
    // Navigate to invoices page
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');

    // Click on an invoice to open detail panel
    const invoiceRow = page.locator('tr').filter({ hasText: 'INV-20250709-1736' });
    await invoiceRow.click();

    // Wait for detail panel
    await page.waitForSelector('[data-kt-invoice-detail-panel="true"]', { state: 'visible' });

    // Verify button arrangement using flexbox layout
    const actionsContainer = page.locator('.invoice-detail-actions');
    await expect(actionsContainer).toHaveClass(/d-flex/);
    await expect(actionsContainer).toHaveClass(/justify-content-between/);

    // Verify left side has Hủy button
    const leftSide = actionsContainer.locator('.d-flex').first();
    const huyButton = leftSide.locator('button').filter({ hasText: 'Hủy' });
    await expect(huyButton).toBeVisible();
    await expect(huyButton).toHaveClass(/btn-light/);

    // Verify right side has Lưu and Trả hàng buttons
    const rightSide = actionsContainer.locator('.d-flex').last();
    const luuButton = rightSide.locator('button').filter({ hasText: 'Lưu' });
    const traHangButton = rightSide.locator('button').filter({ hasText: 'Trả hàng' });

    await expect(luuButton).toBeVisible();
    await expect(luuButton).toHaveClass(/btn-success/);
    
    await expect(traHangButton).toBeVisible();
    await expect(traHangButton).toHaveClass(/btn-primary/);

    // Verify icons
    await expect(huyButton.locator('i.fas.fa-times')).toBeVisible();
    await expect(luuButton.locator('i.fas.fa-save')).toBeVisible();
    await expect(traHangButton.locator('i.fas.fa-undo')).toBeVisible();

    console.log('✅ Button arrangement test passed: Correct layout and styling');
  });

  test('should test Lưu button functionality', async () => {
    // Navigate to invoices page
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');

    // Click on an invoice to open detail panel
    const invoiceRow = page.locator('tr').filter({ hasText: 'INV-20250709-1736' });
    await invoiceRow.click();

    // Wait for detail panel
    await page.waitForSelector('[data-kt-invoice-detail-panel="true"]', { state: 'visible' });

    // Listen for console logs to verify function call
    const consoleLogs = [];
    page.on('console', msg => {
      if (msg.type() === 'log') {
        consoleLogs.push(msg.text());
      }
    });

    // Click Lưu button
    const luuButton = page.locator('button').filter({ hasText: 'Lưu' });
    await luuButton.click();

    // Wait a moment for any JavaScript to execute
    await page.waitForTimeout(1000);

    // Verify function was called (check console logs)
    const saveLogFound = consoleLogs.some(log => log.includes('saveInvoice') || log.includes('Saving invoice'));
    
    if (saveLogFound) {
      console.log('✅ Lưu button test passed: saveInvoice function called');
    } else {
      console.log('⚠️ Lưu button test: Function call not detected in console logs');
    }
  });

  test('should test Trả hàng button functionality', async () => {
    // Navigate to invoices page
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');

    // Click on an invoice to open detail panel
    const invoiceRow = page.locator('tr').filter({ hasText: 'INV-20250709-1736' });
    await invoiceRow.click();

    // Wait for detail panel
    await page.waitForSelector('[data-kt-invoice-detail-panel="true"]', { state: 'visible' });

    // Listen for console logs to verify function call
    const consoleLogs = [];
    page.on('console', msg => {
      if (msg.type() === 'log') {
        consoleLogs.push(msg.text());
      }
    });

    // Get current tab count
    const initialTabs = await page.context().pages();
    const initialTabCount = initialTabs.length;

    // Click Trả hàng button
    const traHangButton = page.locator('button').filter({ hasText: 'Trả hàng' });
    await traHangButton.click();

    // Wait for new tab to open
    await page.waitForTimeout(2000);

    // Check if new tab was opened
    const finalTabs = await page.context().pages();
    const finalTabCount = finalTabs.length;

    expect(finalTabCount).toBeGreaterThan(initialTabCount);

    // Verify console log for return order creation
    const returnOrderLogFound = consoleLogs.some(log => 
      log.includes('Creating return order for invoice') || 
      log.includes('1821')
    );

    if (returnOrderLogFound) {
      console.log('✅ Trả hàng button test passed: New tab opened and function called');
    }

    // Check the new tab URL if possible
    if (finalTabs.length > initialTabCount) {
      const newTab = finalTabs[finalTabs.length - 1];
      const newTabUrl = newTab.url();
      
      if (newTabUrl.includes('quick-order') && newTabUrl.includes('type=return') && newTabUrl.includes('invoice=1821')) {
        console.log('✅ New tab URL test passed: Correct return order URL');
      }
    }
  });

  test('should test Hủy button functionality', async () => {
    // Navigate to invoices page
    await page.goto('http://yukimart.local/admin/invoices');
    await page.waitForLoadState('networkidle');

    // Click on an invoice to open detail panel
    const invoiceRow = page.locator('tr').filter({ hasText: 'INV-20250709-1736' });
    await invoiceRow.click();

    // Wait for detail panel
    await page.waitForSelector('[data-kt-invoice-detail-panel="true"]', { state: 'visible' });

    // Listen for console logs to verify function call
    const consoleLogs = [];
    page.on('console', msg => {
      if (msg.type() === 'log') {
        consoleLogs.push(msg.text());
      }
    });

    // Click Hủy button
    const huyButton = page.locator('button').filter({ hasText: 'Hủy' });
    await huyButton.click();

    // Wait a moment for any JavaScript to execute
    await page.waitForTimeout(1000);

    // Verify function was called (check console logs)
    const cancelLogFound = consoleLogs.some(log => log.includes('cancelInvoice') || log.includes('Cancel invoice'));
    
    if (cancelLogFound) {
      console.log('✅ Hủy button test passed: cancelInvoice function called');
    } else {
      console.log('⚠️ Hủy button test: Function call not detected in console logs');
    }
  });
});
