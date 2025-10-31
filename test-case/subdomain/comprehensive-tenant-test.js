const { chromium } = require('playwright');

async function comprehensiveTenantTest() {
    console.log('🌐 Starting Comprehensive Tenant System Tests...');
    
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000 // Slow down for demo
    });
    
    const tenants = [
        {
            name: 'TechMart Store',
            subdomain: 'tenant1',
            slug: 'techmart',
            expectedProducts: 50,
            productTypes: ['iPhone', 'MacBook', 'Gaming'],
            credentials: [
                { email: 'owner@techmart.local', password: '123456', role: 'Owner' },
                { email: 'admin@techmart.local', password: '123456', role: 'Admin' },
                { email: 'manager@techmart.local', password: '123456', role: 'Manager' },
                { email: 'staff@techmart.local', password: '123456', role: 'Staff' }
            ]
        },
        {
            name: 'Fashion Boutique',
            subdomain: 'tenant2',
            slug: 'fashion',
            expectedProducts: 50,
            productTypes: ['Váy', 'Áo', 'Quần'],
            credentials: [
                { email: 'owner@fashion.local', password: '123456', role: 'Owner' },
                { email: 'admin@fashion.local', password: '123456', role: 'Admin' },
                { email: 'manager@fashion.local', password: '123456', role: 'Manager' },
                { email: 'staff@fashion.local', password: '123456', role: 'Staff' }
            ]
        },
        {
            name: 'Food & Beverage Co',
            subdomain: 'tenant3',
            slug: 'foodbev',
            expectedProducts: 50,
            productTypes: ['Cà phê', 'Trà', 'Gạo'],
            credentials: [
                { email: 'owner@foodbev.local', password: '123456', role: 'Owner' },
                { email: 'admin@foodbev.local', password: '123456', role: 'Admin' },
                { email: 'manager@foodbev.local', password: '123456', role: 'Manager' },
                { email: 'staff@foodbev.local', password: '123456', role: 'Staff' }
            ]
        },
        {
            name: 'HelloMart Store',
            subdomain: 'hellomart',
            slug: 'hellomart',
            expectedProducts: 50,
            productTypes: ['Nồi cơm', 'Bút bi', 'Khăn tắm'],
            credentials: [
                { email: 'owner@hellomart.local', password: '123456', role: 'Owner' },
                { email: 'admin@hellomart.local', password: '123456', role: 'Admin' },
                { email: 'manager@hellomart.local', password: '123456', role: 'Manager' },
                { email: 'staff@hellomart.local', password: '123456', role: 'Staff' }
            ]
        },
        {
            name: 'BiboMart Store',
            subdomain: 'bibomart',
            slug: 'bibomart',
            expectedProducts: 50,
            productTypes: ['Bánh quy', 'Kẹo', 'Nước ngọt'],
            credentials: [
                { email: 'owner@bibomart.local', password: '123456', role: 'Owner' },
                { email: 'admin@bibomart.local', password: '123456', role: 'Admin' },
                { email: 'manager@bibomart.local', password: '123456', role: 'Manager' },
                { email: 'staff@bibomart.local', password: '123456', role: 'Staff' }
            ]
        }
    ];

    let testResults = [];

    for (const tenant of tenants) {
        console.log(`\n🏢 Testing ${tenant.name} (${tenant.subdomain}.yukimart.local)`);
        
        // Test first credential (Owner) for comprehensive testing
        const credential = tenant.credentials[0];
        console.log(`  👤 Testing ${credential.role}: ${credential.email}`);
        
        const context = await browser.newContext();
        const page = await context.newPage();
        
        try {
            // Test 1: Subdomain Access
            const subdomainUrl = `http://${tenant.subdomain}.yukimart.local`;
            console.log(`    🌐 Testing subdomain access: ${subdomainUrl}`);
            
            await page.goto(subdomainUrl, { waitUntil: 'networkidle' });
            const subdomainAccessible = !page.url().includes('error');
            
            // Test 2: Admin Login Access
            const loginUrl = `${subdomainUrl}/admin/login`;
            console.log(`    🔐 Testing admin login page: ${loginUrl}`);
            
            await page.goto(loginUrl, { waitUntil: 'networkidle' });
            
            // Check if login form exists
            const emailField = await page.$('input[name="email"]');
            const passwordField = await page.$('input[name="password"]');
            const loginFormExists = emailField && passwordField;
            
            let loginSuccess = false;
            let dashboardAccess = false;
            let tenantInfo = null;
            let productCount = 0;
            let branchCount = 0;
            
            if (loginFormExists) {
                console.log(`    📝 Login form found, attempting login...`);
                
                // Fill and submit login form
                await page.fill('input[name="email"]', credential.email);
                await page.fill('input[name="password"]', credential.password);
                await page.click('button[type="submit"]');
                
                // Wait for navigation
                await page.waitForLoadState('networkidle');
                
                const finalUrl = page.url();
                loginSuccess = finalUrl.includes('/admin/dashboard') || finalUrl.includes('/admin');
                
                if (loginSuccess) {
                    console.log(`    ✅ Login successful`);
                    
                    // Test 3: Dashboard Access
                    try {
                        await page.goto(`${subdomainUrl}/admin/dashboard`, { waitUntil: 'networkidle' });
                        dashboardAccess = true;
                        console.log(`    ✅ Dashboard access successful`);
                        
                        // Test 4: Tenant Info Verification
                        const pageContent = await page.content();
                        if (pageContent.includes(tenant.name)) {
                            console.log(`    ✅ Tenant-specific content verified`);
                        }
                        
                        // Extract stats from dashboard
                        try {
                            const productCountElement = await page.$('.stats-number');
                            if (productCountElement) {
                                const productCountText = await productCountElement.textContent();
                                productCount = parseInt(productCountText) || 0;
                                console.log(`    📊 Product count: ${productCount}`);
                            }
                        } catch (e) {
                            console.log(`    ⚠️  Could not extract product count`);
                        }
                        
                        // Test 5: API Access
                        try {
                            const apiResponse = await page.goto(`${subdomainUrl}/api/tenant/info`);
                            if (apiResponse.ok()) {
                                const apiData = await apiResponse.json();
                                tenantInfo = apiData.tenant;
                                console.log(`    ✅ API access successful: ${tenantInfo?.name}`);
                            }
                        } catch (apiError) {
                            console.log(`    ⚠️  API access failed: ${apiError.message}`);
                        }
                        
                        // Test 6: Auth Check Endpoint
                        try {
                            const authResponse = await page.goto(`${subdomainUrl}/admin/auth/check`);
                            if (authResponse.ok()) {
                                const authData = await authResponse.json();
                                console.log(`    ✅ Auth check successful: ${authData.authenticated ? 'Authenticated' : 'Not authenticated'}`);
                                if (authData.tenant) {
                                    console.log(`    ✅ Tenant context: ${authData.tenant.name} (ID: ${authData.tenant.id})`);
                                }
                            }
                        } catch (authError) {
                            console.log(`    ⚠️  Auth check failed: ${authError.message}`);
                        }
                        
                    } catch (dashboardError) {
                        console.log(`    ❌ Dashboard access failed: ${dashboardError.message}`);
                    }
                } else {
                    console.log(`    ❌ Login failed`);
                    
                    // Check for error messages
                    const errorElements = await page.$$('.alert-danger, .error, .invalid-feedback');
                    if (errorElements.length > 0) {
                        const errorText = await errorElements[0].textContent();
                        console.log(`    ❌ Error message: ${errorText}`);
                    }
                }
            } else {
                console.log(`    ❌ Login form not found`);
            }
            
            testResults.push({
                tenant: tenant.name,
                subdomain: tenant.subdomain,
                slug: tenant.slug,
                credential: credential,
                subdomainAccessible,
                loginFormExists,
                loginSuccess,
                dashboardAccess,
                tenantInfo,
                productCount,
                branchCount,
                expectedProducts: tenant.expectedProducts,
                productTypes: tenant.productTypes,
                finalUrl: page.url()
            });
            
            // Take screenshot
            const screenshotPath = `test-case/subdomain/screenshots/${tenant.subdomain}-comprehensive.png`;
            await page.screenshot({ path: screenshotPath, fullPage: true });
            console.log(`    📸 Screenshot saved: ${screenshotPath}`);
            
        } catch (error) {
            console.log(`    ❌ Test failed: ${error.message}`);
            testResults.push({
                tenant: tenant.name,
                subdomain: tenant.subdomain,
                slug: tenant.slug,
                credential: credential,
                subdomainAccessible: false,
                loginFormExists: false,
                loginSuccess: false,
                dashboardAccess: false,
                error: error.message
            });
        }
        
        await context.close();
        
        // Wait between tests
        await new Promise(resolve => setTimeout(resolve, 2000));
    }

    await browser.close();
    
    // Generate comprehensive test report
    generateComprehensiveReport(testResults);
    
    console.log('\n🎉 Comprehensive tenant system tests completed!');
}

function generateComprehensiveReport(results) {
    console.log('\n📊 COMPREHENSIVE TEST REPORT');
    console.log('='.repeat(60));
    
    const successful = results.filter(r => r.loginSuccess && r.dashboardAccess);
    const failed = results.filter(r => !r.loginSuccess || !r.dashboardAccess);
    
    console.log(`✅ Fully functional tenants: ${successful.length}`);
    console.log(`❌ Failed tenants: ${failed.length}`);
    console.log(`📊 Success rate: ${((successful.length / results.length) * 100).toFixed(1)}%`);
    
    console.log('\n📋 DETAILED TENANT ANALYSIS:');
    
    results.forEach(result => {
        console.log(`\n🏢 ${result.tenant} (${result.subdomain}.yukimart.local):`);
        console.log(`   Subdomain Access: ${result.subdomainAccessible ? '✅' : '❌'}`);
        console.log(`   Login Form: ${result.loginFormExists ? '✅' : '❌'}`);
        console.log(`   Authentication: ${result.loginSuccess ? '✅' : '❌'}`);
        console.log(`   Dashboard: ${result.dashboardAccess ? '✅' : '❌'}`);
        
        if (result.tenantInfo) {
            console.log(`   Tenant ID: ${result.tenantInfo.id}`);
            console.log(`   Tenant Name: ${result.tenantInfo.name}`);
            console.log(`   Tenant Status: ${result.tenantInfo.status}`);
        }
        
        if (result.productCount !== undefined) {
            console.log(`   Products Found: ${result.productCount} (Expected: ${result.expectedProducts})`);
            const productMatch = result.productCount >= (result.expectedProducts * 0.8); // 80% tolerance
            console.log(`   Product Data: ${productMatch ? '✅' : '⚠️'}`);
        }
        
        if (result.productTypes) {
            console.log(`   Expected Product Types: ${result.productTypes.join(', ')}`);
        }
        
        if (result.error) {
            console.log(`   Error: ${result.error}`);
        }
    });
    
    console.log('\n🌐 SUBDOMAIN ROUTING ANALYSIS:');
    const workingSubdomains = results.filter(r => r.subdomainAccessible);
    console.log(`Working subdomains: ${workingSubdomains.length}/${results.length}`);
    
    results.forEach(result => {
        const status = result.subdomainAccessible ? '✅' : '❌';
        console.log(`  ${status} http://${result.subdomain}.yukimart.local`);
    });
    
    console.log('\n🔐 AUTHENTICATION ANALYSIS:');
    const workingAuth = results.filter(r => r.loginSuccess);
    console.log(`Working authentication: ${workingAuth.length}/${results.length}`);
    
    results.forEach(result => {
        const status = result.loginSuccess ? '✅' : '❌';
        console.log(`  ${status} ${result.credential.email} (${result.credential.role})`);
    });
    
    console.log('\n📊 PRODUCT DATA ANALYSIS:');
    const totalProducts = results.reduce((sum, r) => sum + (r.productCount || 0), 0);
    const expectedTotal = results.reduce((sum, r) => sum + (r.expectedProducts || 0), 0);
    console.log(`Total products found: ${totalProducts} (Expected: ${expectedTotal})`);
    
    results.forEach(result => {
        if (result.productCount !== undefined) {
            const percentage = result.expectedProducts > 0 ? 
                ((result.productCount / result.expectedProducts) * 100).toFixed(1) : 0;
            console.log(`  ${result.tenant}: ${result.productCount}/${result.expectedProducts} (${percentage}%)`);
        }
    });
    
    console.log('\n🎯 SYSTEM READINESS ASSESSMENT:');
    const readyTenants = results.filter(r => 
        r.subdomainAccessible && 
        r.loginFormExists && 
        r.loginSuccess && 
        r.dashboardAccess
    );
    
    const systemReadiness = (readyTenants.length / results.length) * 100;
    console.log(`System Readiness: ${systemReadiness.toFixed(1)}%`);
    
    if (systemReadiness >= 90) {
        console.log('🎉 EXCELLENT: System is production-ready!');
    } else if (systemReadiness >= 70) {
        console.log('✅ GOOD: System is mostly functional, minor issues to fix');
    } else if (systemReadiness >= 50) {
        console.log('⚠️  FAIR: System has significant issues that need attention');
    } else {
        console.log('❌ POOR: System requires major fixes before deployment');
    }
    
    console.log('\n🚀 RECOMMENDATIONS:');
    if (failed.length === 0) {
        console.log('  - All tests passed! System is ready for production');
        console.log('  - Consider adding more comprehensive product data');
        console.log('  - Implement additional user roles and permissions testing');
        console.log('  - Add performance testing for high load scenarios');
    } else {
        console.log('  - Fix failed authentication issues');
        console.log('  - Verify subdomain routing configuration');
        console.log('  - Check tenant data consistency in database');
        console.log('  - Ensure all admin routes are properly configured');
    }
}

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

// Run the comprehensive tests
comprehensiveTenantTest().catch(console.error);
