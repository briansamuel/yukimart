/**
 * Demo Test Case - Quick verification that test setup works
 * 
 * This is a simple test to verify that:
 * 1. Playwright is configured correctly
 * 2. YukiMart site is accessible
 * 3. Login functionality works
 * 4. Basic navigation works
 */

const { test, expect } = require('@playwright/test');

test.describe('Demo Tests - Setup Verification', () => {
  test('should be able to access YukiMart and login', async ({ page }) => {
    // Navigate to login page
    await page.goto('/admin/login');
    
    // Verify login page loads
    await expect(page).toHaveTitle(/YukiMart/);
    
    // Login with test credentials
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    
    // Wait for dashboard to load
    await page.waitForURL('**/admin/dashboard');
    
    // Verify we're logged in (dashboard should be visible)
    await expect(page.locator('body')).toContainText('Dashboard');
    
    console.log('✅ Demo test passed: Login and navigation working');
  });

  test('should be able to navigate to invoices page', async ({ page }) => {
    // Login first
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
    
    // Navigate to invoices
    await page.goto('/admin/invoices');
    await page.waitForLoadState('networkidle');
    
    // Verify invoices page loads
    await expect(page.locator('body')).toContainText('Hóa đơn');
    
    // Verify table exists
    const table = page.locator('table');
    await expect(table).toBeVisible();
    
    console.log('✅ Demo test passed: Invoices page accessible');
  });

  test('should be able to navigate to payments page', async ({ page }) => {
    // Login first
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'yukimart@gmail.com');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');
    
    // Navigate to payments
    await page.goto('/admin/payments');
    await page.waitForLoadState('networkidle');
    
    // Verify payments page loads
    await expect(page.locator('body')).toContainText('Phiếu thu');
    
    // Verify table exists
    const table = page.locator('table');
    await expect(table).toBeVisible();
    
    console.log('✅ Demo test passed: Payments page accessible');
  });
});
