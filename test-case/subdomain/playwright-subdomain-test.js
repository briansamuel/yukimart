const { chromium } = require('playwright');

async function testSubdomainLogin() {
    console.log('🌐 Starting Subdomain Login Tests...');
    
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000 // Slow down for demo
    });
    
    const tenants = [
        {
            name: 'TechMart Store',
            subdomain: 'tenant1',
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
            credentials: [
                { email: 'owner@fashion.local', password: '123456', role: 'Owner' },
                { email: 'admin@fashion.local', password: '123456', role: 'Admin' }
            ]
        },
        {
            name: 'HelloMart Store',
            subdomain: 'hellomart',
            credentials: [
                { email: 'owner@hellomart.local', password: '123456', role: 'Owner' },
                { email: 'admin@hellomart.local', password: '123456', role: 'Admin' }
            ]
        },
        {
            name: 'BiboMart Store',
            subdomain: 'bibomart',
            credentials: [
                { email: 'owner@bibomart.local', password: '123456', role: 'Owner' },
                { email: 'admin@bibomart.local', password: '123456', role: 'Admin' }
            ]
        }
    ];

    let testResults = [];

    for (const tenant of tenants) {
        console.log(`\n🏢 Testing ${tenant.name} (${tenant.subdomain}.yukimart.local)`);
        
        for (const credential of tenant.credentials) {
            console.log(`  👤 Testing ${credential.role}: ${credential.email}`);
            
            const context = await browser.newContext();
            const page = await context.newPage();
            
            try {
                // Test subdomain access
                const subdomainUrl = `http://${tenant.subdomain}.yukimart.local`;
                console.log(`    🌐 Navigating to: ${subdomainUrl}`);
                
                await page.goto(subdomainUrl, { waitUntil: 'networkidle' });
                
                // Check if redirected to login
                const currentUrl = page.url();
                console.log(`    📍 Current URL: ${currentUrl}`);
                
                // Navigate to admin login if not already there
                if (!currentUrl.includes('/admin/login')) {
                    const loginUrl = `${subdomainUrl}/admin/login`;
                    console.log(`    🔐 Navigating to login: ${loginUrl}`);
                    await page.goto(loginUrl, { waitUntil: 'networkidle' });
                }
                
                // Wait for login form
                await page.waitForSelector('input[name="email"]', { timeout: 10000 });
                await page.waitForSelector('input[name="password"]', { timeout: 10000 });
                
                // Fill login form
                console.log(`    📝 Filling login form...`);
                await page.fill('input[name="email"]', credential.email);
                await page.fill('input[name="password"]', credential.password);
                
                // Submit form
                console.log(`    🚀 Submitting login form...`);
                await page.click('button[type="submit"]');
                
                // Wait for navigation
                await page.waitForLoadState('networkidle');
                
                // Check if login successful
                const finalUrl = page.url();
                console.log(`    ✅ Final URL: ${finalUrl}`);
                
                let loginSuccess = false;
                let dashboardAccess = false;
                let tenantInfo = null;
                
                if (finalUrl.includes('/admin/dashboard') || finalUrl.includes('/admin')) {
                    loginSuccess = true;
                    console.log(`    ✅ Login successful for ${credential.role}`);
                    
                    // Try to access dashboard
                    try {
                        await page.goto(`${subdomainUrl}/admin/dashboard`, { waitUntil: 'networkidle' });
                        dashboardAccess = true;
                        console.log(`    ✅ Dashboard access successful`);
                        
                        // Check for tenant-specific content
                        const pageContent = await page.content();
                        if (pageContent.includes(tenant.name)) {
                            console.log(`    ✅ Tenant-specific content found`);
                        }
                        
                        // Try to get tenant info from API
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
                        
                    } catch (dashboardError) {
                        console.log(`    ❌ Dashboard access failed: ${dashboardError.message}`);
                    }
                } else {
                    console.log(`    ❌ Login failed for ${credential.role}`);
                    
                    // Check for error messages
                    const errorElements = await page.$$('.alert-danger, .error, .invalid-feedback');
                    if (errorElements.length > 0) {
                        const errorText = await errorElements[0].textContent();
                        console.log(`    ❌ Error message: ${errorText}`);
                    }
                }
                
                testResults.push({
                    tenant: tenant.name,
                    subdomain: tenant.subdomain,
                    credential: credential,
                    loginSuccess,
                    dashboardAccess,
                    tenantInfo,
                    finalUrl
                });
                
                // Take screenshot
                const screenshotPath = `test-case/subdomain/screenshots/${tenant.subdomain}-${credential.role.toLowerCase()}.png`;
                await page.screenshot({ path: screenshotPath, fullPage: true });
                console.log(`    📸 Screenshot saved: ${screenshotPath}`);
                
            } catch (error) {
                console.log(`    ❌ Test failed: ${error.message}`);
                testResults.push({
                    tenant: tenant.name,
                    subdomain: tenant.subdomain,
                    credential: credential,
                    loginSuccess: false,
                    dashboardAccess: false,
                    error: error.message
                });
            }
            
            await context.close();
            
            // Wait between tests
            await new Promise(resolve => setTimeout(resolve, 2000));
        }
    }

    await browser.close();
    
    // Generate test report
    generateTestReport(testResults);
    
    console.log('\n🎉 Subdomain login tests completed!');
}

function generateTestReport(results) {
    console.log('\n📊 TEST REPORT SUMMARY');
    console.log('='.repeat(50));
    
    const successful = results.filter(r => r.loginSuccess);
    const failed = results.filter(r => !r.loginSuccess);
    
    console.log(`✅ Successful logins: ${successful.length}`);
    console.log(`❌ Failed logins: ${failed.length}`);
    console.log(`📊 Success rate: ${((successful.length / results.length) * 100).toFixed(1)}%`);
    
    console.log('\n📋 DETAILED RESULTS:');
    
    const groupedResults = {};
    results.forEach(result => {
        if (!groupedResults[result.tenant]) {
            groupedResults[result.tenant] = [];
        }
        groupedResults[result.tenant].push(result);
    });
    
    Object.keys(groupedResults).forEach(tenantName => {
        console.log(`\n🏢 ${tenantName}:`);
        groupedResults[tenantName].forEach(result => {
            const status = result.loginSuccess ? '✅' : '❌';
            const dashboard = result.dashboardAccess ? '✅ Dashboard' : '❌ Dashboard';
            const api = result.tenantInfo ? '✅ API' : '❌ API';
            
            console.log(`  ${status} ${result.credential.role} (${result.credential.email})`);
            console.log(`     ${dashboard} | ${api}`);
            if (result.tenantInfo) {
                console.log(`     Tenant: ${result.tenantInfo.name} (ID: ${result.tenantInfo.id})`);
            }
            if (result.error) {
                console.log(`     Error: ${result.error}`);
            }
        });
    });
    
    console.log('\n🌐 SUBDOMAIN URLS TESTED:');
    const uniqueSubdomains = [...new Set(results.map(r => r.subdomain))];
    uniqueSubdomains.forEach(subdomain => {
        console.log(`  http://${subdomain}.yukimart.local`);
    });
    
    console.log('\n🎯 RECOMMENDATIONS:');
    if (failed.length > 0) {
        console.log('  - Check failed login credentials');
        console.log('  - Verify subdomain routing configuration');
        console.log('  - Check tenant data in database');
    } else {
        console.log('  - All tests passed! System is ready for production');
        console.log('  - Consider adding more comprehensive tests');
        console.log('  - Test additional user roles and permissions');
    }
}

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

// Run the tests
testSubdomainLogin().catch(console.error);
