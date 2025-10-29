/**
 * User Creation Tests
 * Test các chức năng tạo tài khoản người dùng
 */

const { test, expect } = require('@playwright/test');

// Test configuration
const BASE_URL = 'http://tenant1.yukimart.local';
const LOGIN_EMAIL = 'yukimart@gmail.com';
const LOGIN_PASSWORD = '123456';

test.describe('User Manager - User Creation Tests', () => {
    
    // Login before each test
    test.beforeEach(async ({ page }) => {
        // Navigate to login page
        await page.goto(`${BASE_URL}/login`);
        
        // Fill login form
        await page.fill('input[name="email"]', LOGIN_EMAIL);
        await page.fill('input[name="password"]', LOGIN_PASSWORD);
        
        // Submit login
        await page.click('button[type="submit"]');
        
        // Wait for redirect to dashboard
        await page.waitForURL(/.*admin.*/);
        
        // Navigate to User Manager page
        await page.goto(`${BASE_URL}/admin/settings/user-manager`);
        
        // Wait for page to load
        await page.waitForSelector('#kt_users_table');
    });

    /**
     * Test Case 1: Modal Display
     */
    test('TC1: Modal hiển thị đúng khi click button Tạo tài khoản', async ({ page }) => {
        // Click button "Tạo tài khoản"
        await page.click('button:has-text("Tạo tài khoản")');
        
        // Wait for modal to appear
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Check modal title
        const modalTitle = await page.textContent('#kt_modal_add_user h1');
        expect(modalTitle).toBe('Tạo tài khoản người dùng');
        
        // Check modal width
        const modalDialog = await page.$('#kt_modal_add_user .modal-dialog');
        const hasCorrectWidth = await modalDialog.evaluate(el => 
            el.classList.contains('mw-800px')
        );
        expect(hasCorrectWidth).toBe(true);
        
        // Check required fields exist
        await expect(page.locator('input[name="full_name"]')).toBeVisible();
        await expect(page.locator('input[name="phone"]')).toBeVisible();
        await expect(page.locator('input[name="username"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
        
        // Check buttons
        await expect(page.locator('button:has-text("Hủy")')).toBeVisible();
        await expect(page.locator('button:has-text("Lưu")')).toBeVisible();
        
        console.log('✅ TC1 PASSED: Modal hiển thị đúng');
    });

    /**
     * Test Case 2: Required Fields Validation
     */
    test('TC2: Validation các trường bắt buộc', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Click Lưu without filling anything
        await page.click('button:has-text("Lưu")');
        
        // Wait a bit for validation
        await page.waitForTimeout(500);
        
        // Check HTML5 validation or custom validation messages
        const fullNameInput = await page.$('input[name="full_name"]');
        const isFullNameInvalid = await fullNameInput.evaluate(el => !el.validity.valid);
        expect(isFullNameInvalid).toBe(true);
        
        console.log('✅ TC2 PASSED: Validation hoạt động');
    });

    /**
     * Test Case 3: Email Format Validation
     */
    test('TC3: Validation format email', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Fill invalid email
        await page.fill('input[name="email"]', 'invalid-email');
        
        // Fill other required fields
        await page.fill('input[name="full_name"]', 'Test User');
        await page.fill('input[name="phone"]', '0901234567');
        await page.fill('input[name="username"]', 'testuser');
        await page.fill('input[name="password"]', '123456');
        await page.fill('input[name="password_confirmation"]', '123456');
        
        // Click Lưu
        await page.click('button:has-text("Lưu")');
        
        // Wait for validation
        await page.waitForTimeout(500);
        
        // Check email validation
        const emailInput = await page.$('input[name="email"]');
        const isEmailInvalid = await emailInput.evaluate(el => !el.validity.valid);
        expect(isEmailInvalid).toBe(true);
        
        console.log('✅ TC3 PASSED: Email validation hoạt động');
    });

    /**
     * Test Case 4: Password Confirmation Validation
     */
    test('TC4: Validation mật khẩu xác nhận', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Fill form with mismatched passwords
        await page.fill('input[name="full_name"]', 'Test User');
        await page.fill('input[name="phone"]', '0901234567');
        await page.fill('input[name="username"]', 'testuser');
        await page.fill('input[name="password"]', '123456');
        await page.fill('input[name="password_confirmation"]', '654321');
        
        // Click Lưu
        await page.click('button:has-text("Lưu")');
        
        // Wait for response
        await page.waitForTimeout(1000);
        
        // Check if modal is still visible (form didn't submit)
        const isModalVisible = await page.isVisible('#kt_modal_add_user');
        expect(isModalVisible).toBe(true);
        
        console.log('✅ TC4 PASSED: Password confirmation validation hoạt động');
    });

    /**
     * Test Case 5: Successful User Creation
     */
    test('TC5: Tạo user thành công', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Generate unique username
        const timestamp = Date.now();
        const username = `testuser${timestamp}`;
        const email = `testuser${timestamp}@example.com`;
        
        // Fill form with valid data
        await page.fill('input[name="full_name"]', 'Test User');
        await page.fill('input[name="phone"]', '0901234567');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="username"]', username);
        await page.fill('input[name="password"]', '123456');
        await page.fill('input[name="password_confirmation"]', '123456');
        
        // Listen for AJAX request
        const responsePromise = page.waitForResponse(
            response => response.url().includes('/admin/settings/user-manager/store')
        );
        
        // Click Lưu
        await page.click('button:has-text("Lưu")');
        
        // Wait for response
        const response = await responsePromise;
        const responseData = await response.json();
        
        // Check response
        expect(response.status()).toBe(200);
        expect(responseData.success).toBe(true);
        
        // Wait for modal to close
        await page.waitForSelector('#kt_modal_add_user', { state: 'hidden' });
        
        // Wait for table to reload
        await page.waitForTimeout(2000);
        
        // Check if new user appears in table
        const tableContent = await page.textContent('#kt_users_table');
        expect(tableContent).toContain('Test User');
        
        console.log('✅ TC5 PASSED: Tạo user thành công');
    });

    /**
     * Test Case 6: Duplicate Username
     */
    test('TC6: Validation username đã tồn tại', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Fill form with existing username
        await page.fill('input[name="full_name"]', 'Test User');
        await page.fill('input[name="phone"]', '0901234567');
        await page.fill('input[name="email"]', 'newemail@example.com');
        await page.fill('input[name="username"]', 'owner'); // Existing username
        await page.fill('input[name="password"]', '123456');
        await page.fill('input[name="password_confirmation"]', '123456');
        
        // Listen for AJAX request
        const responsePromise = page.waitForResponse(
            response => response.url().includes('/admin/settings/user-manager/store')
        );
        
        // Click Lưu
        await page.click('button:has-text("Lưu")');
        
        // Wait for response
        const response = await responsePromise;
        
        // Check response status
        expect(response.status()).toBe(422);
        
        // Modal should still be visible
        const isModalVisible = await page.isVisible('#kt_modal_add_user');
        expect(isModalVisible).toBe(true);
        
        console.log('✅ TC6 PASSED: Duplicate username validation hoạt động');
    });

    /**
     * Test Case 7: Cancel Button
     */
    test('TC7: Button Hủy đóng modal', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Fill some data
        await page.fill('input[name="full_name"]', 'Test User');
        await page.fill('input[name="username"]', 'testuser');
        
        // Click Hủy
        await page.click('button:has-text("Hủy")');
        
        // Wait for modal to close
        await page.waitForSelector('#kt_modal_add_user', { state: 'hidden' });
        
        // Check modal is closed
        const isModalVisible = await page.isVisible('#kt_modal_add_user');
        expect(isModalVisible).toBe(false);
        
        console.log('✅ TC7 PASSED: Button Hủy hoạt động');
    });

    /**
     * Test Case 8: Password Toggle
     */
    test('TC8: Show/Hide password', async ({ page }) => {
        // Open modal
        await page.click('button:has-text("Tạo tài khoản")');
        await page.waitForSelector('#kt_modal_add_user', { state: 'visible' });
        
        // Fill password
        await page.fill('input[name="password"]', '123456');
        
        // Check initial type
        let passwordType = await page.getAttribute('input[name="password"]', 'type');
        expect(passwordType).toBe('password');
        
        // Click eye icon to show password
        await page.click('input[name="password"] ~ button');
        
        // Check type changed to text
        passwordType = await page.getAttribute('input[name="password"]', 'type');
        expect(passwordType).toBe('text');
        
        // Click again to hide
        await page.click('input[name="password"] ~ button');
        
        // Check type changed back to password
        passwordType = await page.getAttribute('input[name="password"]', 'type');
        expect(passwordType).toBe('password');
        
        console.log('✅ TC8 PASSED: Password toggle hoạt động');
    });

});

