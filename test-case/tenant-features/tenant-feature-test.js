const { chromium } = require('playwright');

/**
 * YukiMart Tenant Feature Testing Script
 * 
 * Tests Orders, Products, Invoices, Returns, Payments functionality
 * across 2 tenants: TechMart and Fashion Boutique
 */

const TENANTS = {
    techmart: {
        url: 'http://tenant1.yukimart.local',
        name: 'TechMart Store',
        credentials: {
            owner: { email: 'owner@techmart.local', password: '123456' },
            admin: { email: 'admin@techmart.local', password: '123456' },
            manager: { email: 'manager@techmart.local', password: '123456' },
            staff: { email: 'staff@techmart.local', password: '123456' }
        }
    },
    fashion: {
        url: 'http://tenant2.yukimart.local',
        name: 'Fashion Boutique',
        credentials: {
            owner: { email: 'owner@fashion.local', password: '123456' },
            admin: { email: 'admin@fashion.local', password: '123456' },
            manager: { email: 'manager@fashion.local', password: '123456' },
            staff: { email: 'staff@fashion.local', password: '123456' }
        }
    }
};

const FEATURES_TO_TEST = [
    'products',
    'orders', 
    'invoices',
    'returns',
    'payments'
];

class TenantFeatureTester {
    constructor() {
        this.browser = null;
        this.results = {
            techmart: {},
            fashion: {}
        };
        this.errors = [];
    }

    async init() {
        this.browser = await chromium.launch({ 
            headless: false,
            slowMo: 1000 
        });
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
        }
    }

    async login(page, tenant, role = 'owner') {
        const credentials = TENANTS[tenant].credentials[role];
        const loginUrl = `${TENANTS[tenant].url}/admin/login`;
        
        console.log(`🔐 Logging into ${TENANTS[tenant].name} as ${role}...`);
        
        try {
            await page.goto(loginUrl, { waitUntil: 'networkidle' });
            
            // Fill login form
            await page.fill('input[name="email"]', credentials.email);
            await page.fill('input[name="password"]', credentials.password);
            
            // Submit login
            await page.click('button[type="submit"]');
            
            // Wait for dashboard
            await page.waitForURL('**/admin/dashboard', { timeout: 10000 });
            
            console.log(`✅ Successfully logged into ${TENANTS[tenant].name}`);
            return true;
            
        } catch (error) {
            console.error(`❌ Login failed for ${TENANTS[tenant].name}: ${error.message}`);
            this.errors.push({
                tenant: tenant,
                feature: 'login',
                role: role,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            return false;
        }
    }

    async testProducts(page, tenant) {
        console.log(`📦 Testing Products for ${TENANTS[tenant].name}...`);
        
        const results = {
            list: false,
            create: false,
            edit: false,
            search: false,
            errors: []
        };

        try {
            // Test Products List
            await page.goto(`${TENANTS[tenant].url}/admin/products`, { waitUntil: 'networkidle' });
            
            // Check if products table exists
            const productsTable = await page.locator('table, .products-list, #products-table').first();
            if (await productsTable.isVisible()) {
                results.list = true;
                console.log(`  ✅ Products list loaded`);
            } else {
                results.errors.push('Products table not found');
                console.log(`  ❌ Products list failed to load`);
            }

            // Test Create Product
            try {
                await page.click('a[href*="products/add"], a[href*="products/create"], .btn-add-product');
                await page.waitForTimeout(2000);
                
                const createForm = await page.locator('form, .product-form').first();
                if (await createForm.isVisible()) {
                    results.create = true;
                    console.log(`  ✅ Product create form loaded`);
                } else {
                    results.errors.push('Product create form not found');
                    console.log(`  ❌ Product create form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Create product navigation failed: ${error.message}`);
                console.log(`  ❌ Create product navigation failed`);
            }

            // Test Search
            try {
                await page.goto(`${TENANTS[tenant].url}/admin/products`, { waitUntil: 'networkidle' });
                
                const searchInput = await page.locator('input[name="search"], input[placeholder*="search"], .search-input').first();
                if (await searchInput.isVisible()) {
                    await searchInput.fill('test');
                    await page.waitForTimeout(1000);
                    results.search = true;
                    console.log(`  ✅ Product search functionality works`);
                } else {
                    results.errors.push('Search input not found');
                    console.log(`  ❌ Product search input not found`);
                }
            } catch (error) {
                results.errors.push(`Search test failed: ${error.message}`);
                console.log(`  ❌ Product search test failed`);
            }

        } catch (error) {
            results.errors.push(`General products test failed: ${error.message}`);
            console.log(`  ❌ Products test failed: ${error.message}`);
        }

        return results;
    }

    async testOrders(page, tenant) {
        console.log(`🛒 Testing Orders for ${TENANTS[tenant].name}...`);
        
        const results = {
            list: false,
            create: false,
            quickOrder: false,
            statusUpdate: false,
            errors: []
        };

        try {
            // Test Orders List
            await page.goto(`${TENANTS[tenant].url}/admin/orders`, { waitUntil: 'networkidle' });
            
            const ordersTable = await page.locator('table, .orders-list, #orders-table').first();
            if (await ordersTable.isVisible()) {
                results.list = true;
                console.log(`  ✅ Orders list loaded`);
            } else {
                results.errors.push('Orders table not found');
                console.log(`  ❌ Orders list failed to load`);
            }

            // Test Create Order
            try {
                await page.click('a[href*="orders/add"], a[href*="orders/create"], .btn-add-order');
                await page.waitForTimeout(2000);
                
                const createForm = await page.locator('form, .order-form').first();
                if (await createForm.isVisible()) {
                    results.create = true;
                    console.log(`  ✅ Order create form loaded`);
                } else {
                    results.errors.push('Order create form not found');
                    console.log(`  ❌ Order create form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Create order navigation failed: ${error.message}`);
                console.log(`  ❌ Create order navigation failed`);
            }

            // Test Quick Order
            try {
                await page.goto(`${TENANTS[tenant].url}/admin/quick-order`, { waitUntil: 'networkidle' });
                
                const quickOrderForm = await page.locator('form, .quick-order-form, #quick-order').first();
                if (await quickOrderForm.isVisible()) {
                    results.quickOrder = true;
                    console.log(`  ✅ Quick order form loaded`);
                } else {
                    results.errors.push('Quick order form not found');
                    console.log(`  ❌ Quick order form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Quick order test failed: ${error.message}`);
                console.log(`  ❌ Quick order test failed`);
            }

        } catch (error) {
            results.errors.push(`General orders test failed: ${error.message}`);
            console.log(`  ❌ Orders test failed: ${error.message}`);
        }

        return results;
    }

    async testInvoices(page, tenant) {
        console.log(`🧾 Testing Invoices for ${TENANTS[tenant].name}...`);
        
        const results = {
            list: false,
            create: false,
            fromOrder: false,
            paymentHistory: false,
            errors: []
        };

        try {
            // Test Invoices List
            await page.goto(`${TENANTS[tenant].url}/admin/invoices`, { waitUntil: 'networkidle' });
            
            const invoicesTable = await page.locator('table, .invoices-list, #invoices-table').first();
            if (await invoicesTable.isVisible()) {
                results.list = true;
                console.log(`  ✅ Invoices list loaded`);
            } else {
                results.errors.push('Invoices table not found');
                console.log(`  ❌ Invoices list failed to load`);
            }

            // Test Create Invoice
            try {
                await page.click('a[href*="invoices/create"], .btn-add-invoice');
                await page.waitForTimeout(2000);
                
                const createForm = await page.locator('form, .invoice-form').first();
                if (await createForm.isVisible()) {
                    results.create = true;
                    console.log(`  ✅ Invoice create form loaded`);
                } else {
                    results.errors.push('Invoice create form not found');
                    console.log(`  ❌ Invoice create form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Create invoice navigation failed: ${error.message}`);
                console.log(`  ❌ Create invoice navigation failed`);
            }

        } catch (error) {
            results.errors.push(`General invoices test failed: ${error.message}`);
            console.log(`  ❌ Invoices test failed: ${error.message}`);
        }

        return results;
    }

    async testReturns(page, tenant) {
        console.log(`↩️ Testing Returns for ${TENANTS[tenant].name}...`);
        
        const results = {
            list: false,
            create: false,
            fromInvoice: false,
            statusUpdate: false,
            errors: []
        };

        try {
            // Test Returns List
            await page.goto(`${TENANTS[tenant].url}/admin/returns`, { waitUntil: 'networkidle' });
            
            const returnsTable = await page.locator('table, .returns-list, #returns-table').first();
            if (await returnsTable.isVisible()) {
                results.list = true;
                console.log(`  ✅ Returns list loaded`);
            } else {
                results.errors.push('Returns table not found');
                console.log(`  ❌ Returns list failed to load`);
            }

            // Test Create Return
            try {
                await page.click('a[href*="returns/create"], .btn-add-return');
                await page.waitForTimeout(2000);
                
                const createForm = await page.locator('form, .return-form').first();
                if (await createForm.isVisible()) {
                    results.create = true;
                    console.log(`  ✅ Return create form loaded`);
                } else {
                    results.errors.push('Return create form not found');
                    console.log(`  ❌ Return create form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Create return navigation failed: ${error.message}`);
                console.log(`  ❌ Create return navigation failed`);
            }

        } catch (error) {
            results.errors.push(`General returns test failed: ${error.message}`);
            console.log(`  ❌ Returns test failed: ${error.message}`);
        }

        return results;
    }

    async testPayments(page, tenant) {
        console.log(`💳 Testing Payments for ${TENANTS[tenant].name}...`);
        
        const results = {
            list: false,
            create: false,
            receipt: false,
            bankAccount: false,
            errors: []
        };

        try {
            // Test Payments List
            await page.goto(`${TENANTS[tenant].url}/admin/payments`, { waitUntil: 'networkidle' });
            
            const paymentsTable = await page.locator('table, .payments-list, #payments-table').first();
            if (await paymentsTable.isVisible()) {
                results.list = true;
                console.log(`  ✅ Payments list loaded`);
            } else {
                results.errors.push('Payments table not found');
                console.log(`  ❌ Payments list failed to load`);
            }

            // Test Create Payment
            try {
                await page.click('a[href*="payments/create"], .btn-add-payment');
                await page.waitForTimeout(2000);
                
                const createForm = await page.locator('form, .payment-form').first();
                if (await createForm.isVisible()) {
                    results.create = true;
                    console.log(`  ✅ Payment create form loaded`);
                } else {
                    results.errors.push('Payment create form not found');
                    console.log(`  ❌ Payment create form failed to load`);
                }
            } catch (error) {
                results.errors.push(`Create payment navigation failed: ${error.message}`);
                console.log(`  ❌ Create payment navigation failed`);
            }

        } catch (error) {
            results.errors.push(`General payments test failed: ${error.message}`);
            console.log(`  ❌ Payments test failed: ${error.message}`);
        }

        return results;
    }

    async testTenant(tenant) {
        console.log(`\n🏪 Testing ${TENANTS[tenant].name}...`);
        
        const context = await this.browser.newContext();
        const page = await context.newPage();
        
        try {
            // Login
            const loginSuccess = await this.login(page, tenant);
            if (!loginSuccess) {
                this.results[tenant] = { error: 'Login failed' };
                return;
            }

            // Test each feature
            this.results[tenant] = {
                products: await this.testProducts(page, tenant),
                orders: await this.testOrders(page, tenant),
                invoices: await this.testInvoices(page, tenant),
                returns: await this.testReturns(page, tenant),
                payments: await this.testPayments(page, tenant)
            };

        } catch (error) {
            console.error(`❌ Error testing ${TENANTS[tenant].name}: ${error.message}`);
            this.results[tenant] = { error: error.message };
        } finally {
            await context.close();
        }
    }

    async runTests() {
        console.log('🚀 Starting YukiMart Tenant Feature Tests...\n');
        
        await this.init();
        
        try {
            // Test both tenants
            for (const tenant of Object.keys(TENANTS)) {
                await this.testTenant(tenant);
            }
            
            // Generate report
            this.generateReport();
            
        } finally {
            await this.close();
        }
    }

    generateReport() {
        console.log('\n📊 TEST RESULTS SUMMARY\n');
        console.log('='.repeat(50));
        
        for (const [tenant, results] of Object.entries(this.results)) {
            console.log(`\n🏪 ${TENANTS[tenant].name}:`);
            
            if (results.error) {
                console.log(`  ❌ FAILED: ${results.error}`);
                continue;
            }
            
            for (const [feature, result] of Object.entries(results)) {
                console.log(`\n  📋 ${feature.toUpperCase()}:`);
                
                if (result.errors && result.errors.length > 0) {
                    console.log(`    ❌ Errors: ${result.errors.length}`);
                    result.errors.forEach(error => {
                        console.log(`      - ${error}`);
                    });
                }
                
                const tests = Object.keys(result).filter(key => key !== 'errors');
                const passed = tests.filter(test => result[test] === true).length;
                const total = tests.length;
                
                console.log(`    ✅ Passed: ${passed}/${total} tests`);
                
                tests.forEach(test => {
                    const status = result[test] ? '✅' : '❌';
                    console.log(`      ${status} ${test}`);
                });
            }
        }
        
        console.log('\n' + '='.repeat(50));
        console.log('📝 Detailed error report saved to tenant-test-errors.json');
        
        // Save detailed error report
        const fs = require('fs');
        fs.writeFileSync('tenant-test-errors.json', JSON.stringify({
            timestamp: new Date().toISOString(),
            results: this.results,
            errors: this.errors
        }, null, 2));
    }
}

// Run tests
(async () => {
    const tester = new TenantFeatureTester();
    await tester.runTests();
})();
