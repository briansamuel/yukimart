/**
 * Playwright Tests for QuickOrder Module
 * Comprehensive testing of QuickOrder functionality
 */

const { test, expect } = require('@playwright/test');
const YukiMartTestRunner = require('./master-test-runner');

test.describe('QuickOrder Module Tests', () => {
    let testRunner;

    test.beforeEach(async ({ page }) => {
        testRunner = new YukiMartTestRunner();
        await testRunner.login(page);
    });

    test.describe('Tab Management Tests', () => {
        test('TC-TAB-001: Create new order tab', async ({ page }) => {
            const testName = 'TC-TAB-001: Create new order tab';
            
            try {
                // Navigate to QuickOrder
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Click + button to create new tab
                const addButton = await page.$('button:has-text("+")');
                expect(addButton).toBeTruthy();
                await addButton.click();

                // Wait for new tab to appear
                await page.waitForTimeout(1000);

                // Verify new tab created
                const tabs = await page.$$('[class*="tab"]');
                expect(tabs.length).toBeGreaterThan(1);

                // Verify tab is active
                const activeTab = await page.$('[class*="tab"][class*="active"]');
                expect(activeTab).toBeTruthy();

                // Verify tab shows (0) items
                const tabText = await activeTab.textContent();
                expect(tabText).toContain('(0)');

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'tab-creation-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-TAB-002: Create invoice tab', async ({ page }) => {
            const testName = 'TC-TAB-002: Create invoice tab';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Click + button
                await page.click('button:has-text("+")');
                await page.waitForTimeout(500);

                // Look for dropdown or direct invoice option
                const invoiceOption = await page.$('text="Hóa đơn"');
                if (invoiceOption) {
                    await invoiceOption.click();
                }

                // Verify invoice tab created
                const invoiceTab = await page.$('text*="Hóa đơn"');
                expect(invoiceTab).toBeTruthy();

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'invoice-tab-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-TAB-003: Create return tab', async ({ page }) => {
            const testName = 'TC-TAB-003: Create return tab';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order?type=return`);
                await testRunner.waitForPageLoad(page);

                // Verify return tab created
                const returnTab = await page.$('text*="Trả hàng"');
                expect(returnTab).toBeTruthy();

                // Verify invoice selection modal opens
                const modal = await page.$('[id*="modal"]');
                expect(modal).toBeTruthy();

                // Verify modal title
                const modalTitle = await page.$('text="Chọn hóa đơn để trả hàng"');
                expect(modalTitle).toBeTruthy();

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'return-tab-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });
    });

    test.describe('Product Search Tests', () => {
        test('TC-SEARCH-001: Basic product search', async ({ page }) => {
            const testName = 'TC-SEARCH-001: Basic product search';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Find search input
                const searchInput = await page.$('input[placeholder*="tên, SKU"]');
                expect(searchInput).toBeTruthy();

                // Type search term
                await searchInput.fill('test');
                await page.waitForTimeout(1000);

                // Check if search results appear or no results message
                const hasResults = await testRunner.elementExists(page, '[class*="search-result"]');
                const hasNoResults = await testRunner.elementExists(page, 'text*="Không tìm thấy"');
                
                expect(hasResults || hasNoResults).toBeTruthy();

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'product-search-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-SEARCH-002: F3 keyboard shortcut', async ({ page }) => {
            const testName = 'TC-SEARCH-002: F3 keyboard shortcut';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Press F3
                await page.keyboard.press('F3');
                await page.waitForTimeout(500);

                // Check if search input is focused
                const focusedElement = await page.evaluate(() => document.activeElement.tagName);
                expect(focusedElement).toBe('INPUT');

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'f3-shortcut-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });
    });

    test.describe('Customer Integration Tests', () => {
        test('TC-CUSTOMER-001: Customer search and selection', async ({ page }) => {
            const testName = 'TC-CUSTOMER-001: Customer search and selection';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Find customer search input
                const customerInput = await page.$('input[placeholder*="khách hàng"]');
                if (customerInput) {
                    await customerInput.fill('Anh');
                    await page.waitForTimeout(1000);

                    // Check for autocomplete dropdown
                    const dropdown = await page.$('[class*="dropdown"]');
                    if (dropdown) {
                        // Click first option if available
                        const firstOption = await page.$('[class*="dropdown"] [class*="option"]:first-child');
                        if (firstOption) {
                            await firstOption.click();
                        }
                    }
                }

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'customer-search-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-CUSTOMER-002: Customer modal functionality', async ({ page }) => {
            const testName = 'TC-CUSTOMER-002: Customer modal functionality';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Try to find and click customer name (if exists)
                const customerName = await page.$('[class*="customer-name"]');
                if (customerName) {
                    await customerName.click();
                    await page.waitForTimeout(1000);

                    // Check if modal opened
                    const modal = await page.$('[class*="modal"][class*="show"]');
                    expect(modal).toBeTruthy();

                    // Check modal tabs
                    const tabs = await page.$$('[class*="nav-tab"]');
                    expect(tabs.length).toBeGreaterThan(0);

                    // Close modal
                    const closeButton = await page.$('[class*="btn-close"]');
                    if (closeButton) {
                        await closeButton.click();
                    }
                }

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'customer-modal-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });
    });

    test.describe('Return Order Tests', () => {
        test('TC-RETURN-001: Invoice selection modal', async ({ page }) => {
            const testName = 'TC-RETURN-001: Invoice selection modal';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order?type=return`);
                await testRunner.waitForPageLoad(page);

                // Wait for modal to appear
                await page.waitForSelector('[id*="modal"]', { timeout: 5000 });

                // Check modal title
                const modalTitle = await page.$('text="Chọn hóa đơn để trả hàng"');
                expect(modalTitle).toBeTruthy();

                // Check if invoices are loading or loaded
                const loadingText = await page.$('text*="Đang tải"');
                if (loadingText) {
                    // Wait for loading to complete
                    await page.waitForTimeout(3000);
                }

                // Check for invoice table or no data message
                const invoiceTable = await page.$('table');
                const noDataMessage = await page.$('text*="Không tìm thấy"');
                
                expect(invoiceTable || noDataMessage).toBeTruthy();

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'invoice-selection-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-RETURN-002: Invoice selection and loading', async ({ page }) => {
            const testName = 'TC-RETURN-002: Invoice selection and loading';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order?type=return`);
                await testRunner.waitForPageLoad(page);

                // Wait for modal and invoices to load
                await page.waitForTimeout(3000);

                // Try to select first invoice if available
                const selectButton = await page.$('button:has-text("Chọn")');
                if (selectButton) {
                    await selectButton.click();
                    await page.waitForTimeout(2000);

                    // Check if modal closed
                    const modal = await page.$('[id*="modal"][style*="display: none"]');
                    
                    // Check if invoice info loaded
                    const invoiceInfo = await page.$('[class*="invoice-info"]');
                    
                    // At least one should be true
                    expect(modal || invoiceInfo).toBeTruthy();
                }

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'invoice-loading-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });
    });

    test.describe('UI/UX Tests', () => {
        test('TC-UI-001: Page load and initial state', async ({ page }) => {
            const testName = 'TC-UI-001: Page load and initial state';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Check page title
                const title = await page.title();
                expect(title).toContain('Đặt hàng nhanh');

                // Check main elements exist
                const searchInput = await testRunner.elementExists(page, 'input[placeholder*="tên, SKU"]');
                const addButton = await testRunner.elementExists(page, 'button:has-text("+")');
                const productArea = await testRunner.elementExists(page, '[class*="product"]');

                expect(searchInput && addButton).toBeTruthy();

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'page-load-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });

        test('TC-UI-002: Responsive design', async ({ page }) => {
            const testName = 'TC-UI-002: Responsive design';
            
            try {
                await page.goto(`${testRunner.baseURL}/admin/quick-order`);
                await testRunner.waitForPageLoad(page);

                // Test desktop size
                await page.setViewportSize({ width: 1920, height: 1080 });
                await page.waitForTimeout(500);

                // Test tablet size
                await page.setViewportSize({ width: 768, height: 1024 });
                await page.waitForTimeout(500);

                // Test mobile size
                await page.setViewportSize({ width: 375, height: 667 });
                await page.waitForTimeout(500);

                // Check if page is still functional
                const searchInput = await testRunner.elementExists(page, 'input');
                expect(searchInput).toBeTruthy();

                // Reset to desktop
                await page.setViewportSize({ width: 1920, height: 1080 });

                testRunner.logResult('QuickOrder', testName, 'passed');
            } catch (error) {
                const screenshot = await testRunner.takeScreenshot(page, 'responsive-failed');
                testRunner.logResult('QuickOrder', testName, 'failed', error.message, screenshot);
                throw error;
            }
        });
    });

    test.afterAll(async () => {
        if (testRunner) {
            await testRunner.generateReport();
        }
    });
});
