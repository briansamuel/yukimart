/**
 * Simple login test to debug authentication issues
 */

const { chromium } = require('playwright');

async function testLogin() {
    console.log('🔍 Testing login process...');
    
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000 
    });
    const page = await browser.newPage();
    
    try {
        // Set viewport
        await page.setViewportSize({ width: 1920, height: 1080 });
        
        // Go to login page
        console.log('📄 Navigating to login page...');
        await page.goto('http://yukimart.local/login');
        await page.waitForLoadState('networkidle');
        
        console.log(`Current URL: ${page.url()}`);
        console.log(`Page title: ${await page.title()}`);
        
        // Check if already logged in
        if (page.url().includes('/admin/dashboard')) {
            console.log('✅ Already logged in, redirected to dashboard');
            
            // Try to go to invoices
            console.log('📄 Navigating to invoices...');
            await page.goto('http://yukimart.local/admin/invoices');
            await page.waitForLoadState('networkidle');
            
            console.log(`Invoices URL: ${page.url()}`);
            console.log(`Invoices title: ${await page.title()}`);
            
            // Check for invoice elements
            const searchBox = await page.locator('input[placeholder*="Tìm kiếm"]').count();
            const table = await page.locator('table').count();
            
            console.log(`Search boxes found: ${searchBox}`);
            console.log(`Tables found: ${table}`);
            
            // Take screenshot
            await page.screenshot({ path: 'invoices-page.png' });
            console.log('📸 Screenshot saved as invoices-page.png');
            
            console.log('✅ Test completed successfully');
            await page.waitForTimeout(5000); // Keep open for 5 seconds
            
        } else {
            // Need to login
            console.log('🔐 Need to login...');
            
            // Check for login form elements
            const emailInput = await page.locator('input[name="email"]').count();
            const passwordInput = await page.locator('input[name="password"]').count();
            const submitButton = await page.locator('button[type="submit"]').count();
            const loginForm = await page.locator('form').count();

            console.log(`Email input found: ${emailInput}`);
            console.log(`Password input found: ${passwordInput}`);
            console.log(`Submit button found: ${submitButton}`);
            console.log(`Forms found: ${loginForm}`);

            // Check form method and action
            if (loginForm > 0) {
                const formMethod = await page.locator('form').first().getAttribute('method');
                const formAction = await page.locator('form').first().getAttribute('action');
                console.log(`Form method: ${formMethod}`);
                console.log(`Form action: ${formAction}`);
            }
            
            if (emailInput && passwordInput && submitButton) {
                // Take screenshot of login form
                await page.screenshot({ path: 'login-form.png' });
                console.log('📸 Login form screenshot saved');

                // Fill and submit
                console.log('📝 Filling login form...');
                await page.fill('input[name="email"]', 'yukimart@gmail.com');
                await page.waitForTimeout(500);
                await page.fill('input[name="password"]', '123456');
                await page.waitForTimeout(500);

                // Check form values
                const emailValue = await page.inputValue('input[name="email"]');
                const passwordValue = await page.inputValue('input[name="password"]');
                console.log(`Email filled: ${emailValue}`);
                console.log(`Password filled: ${passwordValue ? '***' : 'EMPTY'}`);

                console.log('🚀 Submitting form...');

                // Submit form and wait for navigation
                try {
                    await Promise.all([
                        page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }),
                        page.click('button[type="submit"]')
                    ]);
                } catch (e) {
                    console.log('Navigation timeout, checking current state...');
                    await page.waitForTimeout(2000);
                }

                console.log(`After login URL: ${page.url()}`);

                // Check for error messages
                try {
                    const errorMessages = await page.locator('.alert-danger, .error, .invalid-feedback').count();
                    if (errorMessages > 0) {
                        const errorText = await page.locator('.alert-danger, .error, .invalid-feedback').first().textContent();
                        console.log(`❌ Error message: ${errorText}`);
                    }
                } catch (e) {
                    console.log('No error messages found');
                }

                if (page.url().includes('/admin/dashboard') || (page.url().includes('/admin/') && !page.url().includes('/admin/login'))) {
                    console.log('✅ Login successful!');
                    
                    // Try invoices page
                    console.log('📄 Navigating to invoices...');
                    await page.goto('http://yukimart.local/admin/invoices');
                    await page.waitForLoadState('networkidle');
                    
                    console.log(`Invoices URL: ${page.url()}`);
                    
                    // Take screenshot
                    await page.screenshot({ path: 'invoices-after-login.png' });
                    console.log('📸 Screenshot saved as invoices-after-login.png');
                    
                } else {
                    console.log('❌ Login failed');
                    await page.screenshot({ path: 'login-failed.png' });
                }
            } else {
                console.log('❌ Login form elements not found');
                await page.screenshot({ path: 'login-form-missing.png' });
            }
        }
        
    } catch (error) {
        console.error('❌ Error:', error.message);
        await page.screenshot({ path: 'error-screenshot.png' });
    }
    
    await browser.close();
}

testLogin().catch(console.error);
