/**
 * Simple YukiMart Tenant Test
 * 
 * Basic test to check if URLs are accessible and routes work
 */

const https = require('https');
const http = require('http');

const TENANTS = {
    base: 'http://yukimart.local',
    tenant1: 'http://tenant1.yukimart.local', 
    tenant2: 'http://tenant2.yukimart.local'
};

const ROUTES_TO_TEST = [
    '/admin/login',
    '/admin/dashboard',
    '/admin/products',
    '/admin/orders',
    '/admin/invoices',
    '/admin/returns',
    '/admin/payments'
];

class SimpleTester {
    constructor() {
        this.results = {};
        this.errors = [];
    }

    async testUrl(url) {
        return new Promise((resolve) => {
            const client = url.startsWith('https') ? https : http;
            
            const req = client.get(url, (res) => {
                resolve({
                    url: url,
                    status: res.statusCode,
                    success: res.statusCode < 400
                });
            });

            req.on('error', (error) => {
                resolve({
                    url: url,
                    status: 'ERROR',
                    success: false,
                    error: error.message
                });
            });

            req.setTimeout(5000, () => {
                req.destroy();
                resolve({
                    url: url,
                    status: 'TIMEOUT',
                    success: false,
                    error: 'Request timeout'
                });
            });
        });
    }

    async testTenant(tenantName, baseUrl) {
        console.log(`\n🏪 Testing ${tenantName} (${baseUrl})...`);
        
        const results = [];
        
        for (const route of ROUTES_TO_TEST) {
            const fullUrl = baseUrl + route;
            const result = await this.testUrl(fullUrl);
            
            const status = result.success ? '✅' : '❌';
            const statusCode = result.status;
            
            console.log(`  ${status} ${route} (${statusCode})`);
            
            results.push(result);
            
            if (!result.success) {
                this.errors.push({
                    tenant: tenantName,
                    route: route,
                    error: result.error || `HTTP ${result.status}`,
                    timestamp: new Date().toISOString()
                });
            }
        }
        
        return results;
    }

    async runTests() {
        console.log('🚀 Starting Simple YukiMart Tenant Tests...\n');
        
        for (const [tenantName, baseUrl] of Object.entries(TENANTS)) {
            this.results[tenantName] = await this.testTenant(tenantName, baseUrl);
        }
        
        this.generateReport();
    }

    generateReport() {
        console.log('\n📊 TEST RESULTS SUMMARY');
        console.log('='.repeat(50));
        
        let totalTests = 0;
        let totalPassed = 0;
        
        for (const [tenantName, results] of Object.entries(this.results)) {
            const passed = results.filter(r => r.success).length;
            const total = results.length;
            
            totalTests += total;
            totalPassed += passed;
            
            console.log(`\n🏪 ${tenantName.toUpperCase()}:`);
            console.log(`  ✅ Passed: ${passed}/${total}`);
            console.log(`  ❌ Failed: ${total - passed}/${total}`);
            
            if (passed === total) {
                console.log(`  🎉 All tests passed!`);
            } else {
                console.log(`  ⚠️  Some tests failed`);
            }
        }
        
        console.log('\n' + '='.repeat(50));
        console.log(`📈 OVERALL: ${totalPassed}/${totalTests} tests passed`);
        
        if (this.errors.length > 0) {
            console.log(`\n❌ ERRORS (${this.errors.length}):`);
            this.errors.forEach((error, index) => {
                console.log(`  ${index + 1}. ${error.tenant}${error.route}: ${error.error}`);
            });
        }
        
        // Save results to file
        const fs = require('fs');
        const reportData = {
            timestamp: new Date().toISOString(),
            summary: {
                total_tests: totalTests,
                passed: totalPassed,
                failed: totalTests - totalPassed,
                success_rate: Math.round((totalPassed / totalTests) * 100)
            },
            results: this.results,
            errors: this.errors
        };
        
        fs.writeFileSync('simple-test-results.json', JSON.stringify(reportData, null, 2));
        console.log('\n📁 Detailed results saved to simple-test-results.json');
        
        // Generate recommendations
        this.generateRecommendations();
    }

    generateRecommendations() {
        console.log('\n💡 RECOMMENDATIONS:');
        
        if (this.errors.length === 0) {
            console.log('  🎉 All tests passed! Your tenant system is working correctly.');
            return;
        }
        
        const errorsByType = {};
        this.errors.forEach(error => {
            const type = error.error.includes('ENOTFOUND') ? 'DNS' :
                        error.error.includes('ECONNREFUSED') ? 'CONNECTION' :
                        error.error.includes('404') ? 'ROUTE' :
                        error.error.includes('500') ? 'SERVER' : 'OTHER';
            
            if (!errorsByType[type]) errorsByType[type] = [];
            errorsByType[type].push(error);
        });
        
        if (errorsByType.DNS) {
            console.log('  🌐 DNS Issues:');
            console.log('    - Check your hosts file or DNS configuration');
            console.log('    - Ensure tenant subdomains are properly configured');
        }
        
        if (errorsByType.CONNECTION) {
            console.log('  🔌 Connection Issues:');
            console.log('    - Make sure your local server is running');
            console.log('    - Check if the correct ports are open');
        }
        
        if (errorsByType.ROUTE) {
            console.log('  🛣️  Route Issues:');
            console.log('    - Verify route definitions in admin.php');
            console.log('    - Check controller namespaces and methods');
        }
        
        if (errorsByType.SERVER) {
            console.log('  🖥️  Server Issues:');
            console.log('    - Check application logs for errors');
            console.log('    - Verify database connections');
            console.log('    - Check middleware configuration');
        }
        
        console.log('\n🔧 NEXT STEPS:');
        console.log('  1. Fix the issues identified above');
        console.log('  2. Re-run this test to verify fixes');
        console.log('  3. Run the full Playwright test suite');
        console.log('  4. Check the detailed error report');
    }
}

// Run the tests
(async () => {
    const tester = new SimpleTester();
    await tester.runTests();
})().catch(console.error);
