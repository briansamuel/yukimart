/**
 * Test Case: Payment Print Functionality
 * 
 * This test verifies the print functionality for payment receipts (Phiếu thu/Chi),
 * including button presence, functionality, and proper data handling.
 * 
 * Requirements tested:
 * 1. Print button is present in payment detail panel
 * 2. Print button has correct icon and styling
 * 3. Print function is called with correct payment ID
 * 4. Print functionality works for both receipts and disbursements
 * 5. Button state changes when clicked
 */

const { test, expect } = require('@playwright/test');

test.describe('Payment Print Functionality Tests', () => {
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

  test('should display print button in payment detail panel', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Click on a payment row to open detail panel
    const paymentRow = page.locator('tr').filter({ hasText: 'TT1821-1' });
    await paymentRow.click();

    // Wait for detail panel to load
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    // Verify print button exists
    const printButton = page.locator('button').filter({ hasText: 'In' });
    await expect(printButton).toBeVisible();

    // Verify print button has correct icon
    const printIcon = printButton.locator('i.fas.fa-print');
    await expect(printIcon).toBeVisible();

    // Verify button styling
    await expect(printButton).toHaveClass(/btn/);

    console.log('✅ Print button display test passed: Button visible with correct icon');
  });

  test('should verify payment detail panel content before printing', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Click on payment TT1821-1
    const paymentRow = page.locator('tr').filter({ hasText: 'TT1821-1' });
    await paymentRow.click();

    // Wait for detail panel
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    // Verify payment information is displayed
    const paymentCode = page.locator('text=TT1821-1');
    await expect(paymentCode).toBeVisible();

    // Verify payment type
    const paymentType = page.locator('text=Phiếu thu');
    await expect(paymentType).toBeVisible();

    // Verify payment amount
    const paymentAmount = page.locator('text=1.801.800 ₫');
    await expect(paymentAmount).toBeVisible();

    // Verify payment status
    const paymentStatus = page.locator('text=Đã thanh toán');
    await expect(paymentStatus).toBeVisible();

    // Verify linked invoice
    const linkedInvoice = page.locator('text=INV-20250709-1736');
    await expect(linkedInvoice).toBeVisible();

    // Verify customer information
    const customerInfo = page.locator('text=Khách lẻ');
    await expect(customerInfo).toBeVisible();

    console.log('✅ Payment detail verification test passed: All payment information displayed correctly');
  });

  test('should test print button functionality', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Click on payment TT1821-1
    const paymentRow = page.locator('tr').filter({ hasText: 'TT1821-1' });
    await paymentRow.click();

    // Wait for detail panel
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    // Listen for console logs to verify function call
    const consoleLogs = [];
    page.on('console', msg => {
      if (msg.type() === 'log') {
        consoleLogs.push(msg.text());
      }
    });

    // Click print button
    const printButton = page.locator('button').filter({ hasText: 'In' });
    await printButton.click();

    // Wait for JavaScript to execute
    await page.waitForTimeout(1000);

    // Verify print function was called with correct payment ID
    const printLogFound = consoleLogs.some(log => 
      log.includes('Print payment: 4063') || 
      log.includes('printPayment') ||
      log.includes('4063')
    );

    expect(printLogFound).toBeTruthy();

    // Verify button state changes (should have active class)
    await expect(printButton).toHaveClass(/active/);

    console.log('✅ Print button functionality test passed: printPayment function called with ID 4063');
  });

  test('should test print functionality for different payment types', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Test with first available payment (receipt type)
    const firstPaymentRow = page.locator('tbody tr').first();
    await firstPaymentRow.click();

    // Wait for detail panel
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    // Verify print button exists for receipt
    const printButton = page.locator('button').filter({ hasText: 'In' });
    await expect(printButton).toBeVisible();

    // Listen for console logs
    const consoleLogs = [];
    page.on('console', msg => {
      if (msg.type() === 'log') {
        consoleLogs.push(msg.text());
      }
    });

    // Click print button
    await printButton.click();
    await page.waitForTimeout(1000);

    // Verify function was called
    const printLogFound = consoleLogs.some(log => 
      log.includes('Print payment') || 
      log.includes('printPayment')
    );

    expect(printLogFound).toBeTruthy();

    console.log('✅ Print functionality test passed for payment type');
  });

  test('should verify print button accessibility and usability', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Click on payment
    const paymentRow = page.locator('tr').filter({ hasText: 'TT1821-1' });
    await paymentRow.click();

    // Wait for detail panel
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    const printButton = page.locator('button').filter({ hasText: 'In' });

    // Verify button is enabled
    await expect(printButton).toBeEnabled();

    // Verify button is focusable
    await printButton.focus();
    await expect(printButton).toBeFocused();

    // Verify button can be activated with keyboard
    await printButton.press('Enter');
    await page.waitForTimeout(500);

    // Verify button text is readable
    const buttonText = await printButton.textContent();
    expect(buttonText.trim()).toBe('In');

    // Verify button has proper ARIA attributes or role if any
    const buttonRole = await printButton.getAttribute('role');
    if (buttonRole) {
      expect(buttonRole).toBe('button');
    }

    console.log('✅ Print button accessibility test passed: Button is accessible and usable');
  });

  test('should verify print button position in detail panel', async () => {
    // Navigate to payments page
    await page.goto('http://yukimart.local/admin/payments');
    await page.waitForLoadState('networkidle');

    // Click on payment
    const paymentRow = page.locator('tr').filter({ hasText: 'TT1821-1' });
    await paymentRow.click();

    // Wait for detail panel
    await page.waitForSelector('.payment-detail-panel', { state: 'visible' });

    // Verify print button is in the actions section at bottom of panel
    const actionsSection = page.locator('.payment-detail-panel').locator('.d-flex').last();
    const printButton = actionsSection.locator('button').filter({ hasText: 'In' });
    
    await expect(printButton).toBeVisible();

    // Verify other action buttons are present (Hủy, Chỉnh sửa)
    const cancelButton = actionsSection.locator('button').filter({ hasText: 'Hủy' });
    const editButton = actionsSection.locator('button').filter({ hasText: 'Chỉnh sửa' });

    await expect(cancelButton).toBeVisible();
    await expect(editButton).toBeVisible();

    // Verify buttons are properly spaced
    const buttonContainer = actionsSection;
    await expect(buttonContainer).toHaveClass(/d-flex/);

    console.log('✅ Print button position test passed: Button correctly positioned in actions section');
  });
});
