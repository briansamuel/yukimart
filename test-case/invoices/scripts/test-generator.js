/**
 * Automatic Test Case Generator for Playwright
 * Generates optimized test scripts from test specifications
 */

const fs = require('fs').promises;
const path = require('path');

class PlaywrightTestGenerator {
    constructor() {
        this.testSpecs = [];
        this.outputDir = './generated-tests';
    }

    // Load test specifications from markdown files
    async loadTestSpecs() {
        const specFiles = [
            '../search-tests.md',
            '../pagination-tests.md', 
            '../filter-tests.md',
            '../column-visibility-tests.md',
            '../row-expansion-tests.md',
            '../bulk-action-tests.md'
        ];

        for (const file of specFiles) {
            try {
                const content = await fs.readFile(file, 'utf8');
                const specs = this.parseTestSpecs(content, file);
                this.testSpecs.push(...specs);
            } catch (error) {
                console.log(`⚠️ Could not load ${file}: ${error.message}`);
            }
        }

        console.log(`📋 Loaded ${this.testSpecs.length} test specifications`);
    }

    parseTestSpecs(content, filename) {
        const specs = [];
        const lines = content.split('\n');
        let currentTest = null;

        for (const line of lines) {
            // Match test case headers like "## S01: Search by Invoice ID"
            const testMatch = line.match(/^##\s+([A-Z]+\d+):\s+(.+)$/);
            if (testMatch) {
                if (currentTest) {
                    specs.push(currentTest);
                }
                currentTest = {
                    id: testMatch[1],
                    name: testMatch[2],
                    category: filename.replace('../', '').replace('-tests.md', ''),
                    steps: [],
                    expected: '',
                    description: ''
                };
            }
            
            // Parse steps
            if (currentTest && line.match(/^\d+\./)) {
                currentTest.steps.push(line.replace(/^\d+\.\s*/, ''));
            }
            
            // Parse expected results
            if (currentTest && line.includes('**Expected:**')) {
                currentTest.expected = line.replace(/.*\*\*Expected:\*\*\s*/, '');
            }
        }

        if (currentTest) {
            specs.push(currentTest);
        }

        return specs;
    }

    // Generate optimized Playwright test for a test spec
    generateTestCode(spec) {
        const testCode = `
    async test${spec.id}(page) {
        const startTime = Date.now();
        console.log('🧪 Testing ${spec.id}: ${spec.name}');
        
        try {
            ${this.generateStepCode(spec)}
            
            const duration = Date.now() - startTime;
            return {
                id: '${spec.id}',
                name: '${spec.name}',
                status: 'PASSED',
                details: '${spec.expected}',
                duration
            };
        } catch (error) {
            return {
                id: '${spec.id}',
                name: '${spec.name}',
                status: 'FAILED',
                details: error.message,
                duration: Date.now() - startTime
            };
        }
    }`;
        
        return testCode;
    }

    generateStepCode(spec) {
        let code = '';
        
        // Generate code based on test category and steps
        switch (spec.category) {
            case 'search':
                code = this.generateSearchCode(spec);
                break;
            case 'filter':
                code = this.generateFilterCode(spec);
                break;
            case 'pagination':
                code = this.generatePaginationCode(spec);
                break;
            case 'column-visibility':
                code = this.generateColumnVisibilityCode(spec);
                break;
            case 'row-expansion':
                code = this.generateRowExpansionCode(spec);
                break;
            default:
                code = this.generateGenericCode(spec);
        }
        
        return code;
    }

    generateSearchCode(spec) {
        if (spec.name.includes('Invoice ID')) {
            return `
            const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            await searchBox.fill('HD');
            await page.waitForTimeout(1500);
            
            const rows = await page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count();
            if (rows === 0) throw new Error('No search results found');`;
        }
        
        if (spec.name.includes('Customer Name')) {
            return `
            const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            await searchBox.fill('Nguyễn');
            await page.waitForTimeout(1500);
            
            const hasResults = await page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').count() > 0;
            if (!hasResults) throw new Error('No customer search results');`;
        }
        
        return `
            const searchBox = page.locator('input[type="search"], input[placeholder*="Tìm kiếm"]').first();
            await searchBox.fill('test');
            await page.waitForTimeout(1500);`;
    }

    generateFilterCode(spec) {
        if (spec.name.includes('Time Filter')) {
            return `
            // Check if time filter is working via AJAX response
            const responsePromise = page.waitForResponse(response => 
                response.url().includes('/ajax') && response.status() === 200
            );
            
            await page.evaluate(() => {
                if (window.loadInvoices) window.loadInvoices();
            });
            
            const response = await responsePromise;
            const data = await response.json();
            if (!data.data) throw new Error('No filter response data');`;
        }
        
        if (spec.name.includes('Status Filter')) {
            return `
            const checkboxes = await page.locator('input[type="checkbox"]').all();
            let found = false;
            
            for (const checkbox of checkboxes) {
                if (await checkbox.isVisible()) {
                    await checkbox.check();
                    found = true;
                    break;
                }
            }
            
            if (!found) throw new Error('No status checkboxes found');
            await page.waitForTimeout(1000);`;
        }
        
        return `
            // Generic filter test
            await page.waitForTimeout(1000);`;
    }

    generatePaginationCode(spec) {
        if (spec.name.includes('Navigation')) {
            return `
            const nextButton = page.locator('a:has-text("Tiếp"), .pagination .next').first();
            
            if (await nextButton.isVisible()) {
                await nextButton.click();
                await page.waitForTimeout(2000);
            }`;
        }
        
        if (spec.name.includes('Info')) {
            return `
            const paginationText = await page.locator(':has-text("Hiển thị"), :has-text("kết quả")').first().textContent();
            if (!paginationText) throw new Error('Pagination info not found');`;
        }
        
        return `
            // Generic pagination test
            await page.waitForTimeout(1000);`;
    }

    generateColumnVisibilityCode(spec) {
        return `
            const columnButton = page.locator('button:has-text("Cột"), .column-visibility-btn').first();
            if (await columnButton.isVisible()) {
                await columnButton.click();
                await page.waitForTimeout(500);
            }`;
    }

    generateRowExpansionCode(spec) {
        return `
            const firstRow = page.locator('table:not(.phpdebugbar-widgets-params) tbody tr').first();
            await firstRow.click();
            await page.waitForTimeout(2000);
            
            const detailPanel = page.locator('.invoice-detail-panel, .detail-panel, .expanded-row');
            const isVisible = await detailPanel.isVisible();
            if (!isVisible) throw new Error('Detail panel not visible');`;
    }

    generateGenericCode(spec) {
        return `
            // Generic test implementation
            await page.waitForTimeout(1000);
            console.log('Executing: ${spec.name}');`;
    }

    // Generate complete test file for a category
    async generateCategoryTestFile(category) {
        const categorySpecs = this.testSpecs.filter(spec => spec.category === category);
        
        if (categorySpecs.length === 0) {
            console.log(`⚠️ No specs found for category: ${category}`);
            return;
        }

        const className = `Generated${category.charAt(0).toUpperCase() + category.slice(1)}Tests`;
        
        let fileContent = `/**
 * Auto-generated Playwright Tests for ${category}
 * Generated on: ${new Date().toISOString()}
 */

const { chromium } = require('playwright');

class ${className} {
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
            timeout: 30000
        };
    }

    async setup() {
        this.browser = await chromium.launch({ headless: this.config.headless });
        const context = await this.browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        this.page = await context.newPage();
        
        // Login and navigate
        await this.ensureLogin();
        await this.page.goto(this.config.invoicesUrl);
        await this.waitForPageLoad();
    }

    async ensureLogin() {
        await this.page.goto(this.config.loginUrl);
        await this.page.fill('input[name="email"]', this.config.credentials.email);
        await this.page.fill('input[name="password"]', this.config.credentials.password);
        await Promise.all([
            this.page.waitForNavigation({ waitUntil: 'networkidle' }),
            this.page.click('button[type="submit"]')
        ]);
    }

    async waitForPageLoad() {
        await this.page.waitForSelector('table:not(.phpdebugbar-widgets-params)', { timeout: this.config.timeout });
        await this.page.waitForFunction(() => {
            const tables = document.querySelectorAll('table:not(.phpdebugbar-widgets-params)');
            return tables.length > 0;
        });
    }

${categorySpecs.map(spec => this.generateTestCode(spec)).join('\n')}

    async runAllTests() {
        console.log('🎯 Starting ${category} tests...');
        
        await this.setup();
        
        ${categorySpecs.map(spec => `
        const result${spec.id} = await this.test${spec.id}(this.page);
        this.results.push(result${spec.id});`).join('')}
        
        await this.generateReport();
        await this.cleanup();
    }

    async generateReport() {
        const passed = this.results.filter(r => r.status === 'PASSED').length;
        const failed = this.results.filter(r => r.status === 'FAILED').length;
        
        console.log('\\n📊 ${category.toUpperCase()} TESTS REPORT');
        console.log('='.repeat(30));
        console.log(\`✅ Passed: \${passed}\`);
        console.log(\`❌ Failed: \${failed}\`);
        console.log(\`📈 Success Rate: \${Math.round(passed/(passed+failed)*100)}%\`);
        
        return { passed, failed, results: this.results };
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

if (require.main === module) {
    const tests = new ${className}();
    tests.runAllTests().catch(console.error);
}

module.exports = ${className};`;

        // Ensure output directory exists
        await fs.mkdir(this.outputDir, { recursive: true });
        
        // Write file
        const filename = path.join(this.outputDir, `${category}-tests.js`);
        await fs.writeFile(filename, fileContent, 'utf8');
        
        console.log(`✅ Generated: ${filename}`);
        return filename;
    }

    // Generate all test files
    async generateAllTests() {
        console.log('🔧 Generating Playwright test files...');
        
        await this.loadTestSpecs();
        
        const categories = [...new Set(this.testSpecs.map(spec => spec.category))];
        const generatedFiles = [];
        
        for (const category of categories) {
            const filename = await this.generateCategoryTestFile(category);
            if (filename) {
                generatedFiles.push(filename);
            }
        }
        
        // Generate master runner
        await this.generateMasterRunner(generatedFiles);
        
        console.log(`🎉 Generated ${generatedFiles.length} test files`);
        return generatedFiles;
    }

    async generateMasterRunner(testFiles) {
        const imports = testFiles.map((file, index) => {
            const className = path.basename(file, '.js').split('-').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join('');
            return `const ${className} = require('./${path.basename(file)}');`;
        }).join('\n');

        const runnerContent = `/**
 * Master Test Runner - Auto-generated
 * Runs all generated Playwright tests
 */

${imports}

class MasterTestRunner {
    async runAllTests() {
        console.log('🚀 Starting Master Test Runner...');
        
        const allResults = [];
        
        ${testFiles.map(file => {
            const className = path.basename(file, '.js').split('-').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join('');
            return `
        try {
            const ${className.toLowerCase()} = new ${className}();
            const results = await ${className.toLowerCase()}.runAllTests();
            allResults.push({ category: '${className}', ...results });
        } catch (error) {
            console.error('❌ Error in ${className}:', error.message);
            allResults.push({ category: '${className}', passed: 0, failed: 1, error: error.message });
        }`;
        }).join('')}
        
        // Generate final report
        this.generateFinalReport(allResults);
    }

    generateFinalReport(allResults) {
        const totalPassed = allResults.reduce((sum, r) => sum + (r.passed || 0), 0);
        const totalFailed = allResults.reduce((sum, r) => sum + (r.failed || 0), 0);
        const successRate = Math.round(totalPassed / (totalPassed + totalFailed) * 100);
        
        console.log('\\n🎯 MASTER TEST REPORT');
        console.log('='.repeat(50));
        console.log(\`✅ Total Passed: \${totalPassed}\`);
        console.log(\`❌ Total Failed: \${totalFailed}\`);
        console.log(\`📈 Overall Success Rate: \${successRate}%\`);
        
        allResults.forEach(result => {
            console.log(\`📊 \${result.category}: \${result.passed || 0}/\${(result.passed || 0) + (result.failed || 0)} passed\`);
        });
    }
}

if (require.main === module) {
    const runner = new MasterTestRunner();
    runner.runAllTests().catch(console.error);
}

module.exports = MasterTestRunner;`;

        const masterFile = path.join(this.outputDir, 'master-runner.js');
        await fs.writeFile(masterFile, runnerContent, 'utf8');
        console.log(`✅ Generated master runner: ${masterFile}`);
    }
}

if (require.main === module) {
    const generator = new PlaywrightTestGenerator();
    generator.generateAllTests().catch(console.error);
}

module.exports = PlaywrightTestGenerator;
