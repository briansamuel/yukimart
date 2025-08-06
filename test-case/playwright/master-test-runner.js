/**
 * Master Test Runner for YukiMart System
 * Runs comprehensive Playwright tests for all modules
 */

const { test, expect } = require('@playwright/test');
const fs = require('fs').promises;
const path = require('path');

class YukiMartTestRunner {
    constructor() {
        this.baseURL = 'http://yukimart.local';
        this.credentials = {
            email: 'yukimart@gmail.com',
            password: '123456'
        };
        this.testResults = {
            total: 0,
            passed: 0,
            failed: 0,
            skipped: 0,
            modules: {}
        };
        this.startTime = new Date();
    }

    /**
     * Login helper function
     */
    async login(page) {
        try {
            await page.goto(`${this.baseURL}/admin/login`);

            // Check if already logged in
            if (page.url().includes('/admin/dashboard')) {
                console.log('Already logged in');
                return;
            }

            await page.fill('input[name="email"]', this.credentials.email);
            await page.fill('input[name="password"]', this.credentials.password);
            await page.click('button[type="submit"]');

            // Wait for either dashboard or any admin page
            await page.waitForURL('**/admin/**', { timeout: 10000 });

            // Verify we're logged in by checking for admin elements
            await page.waitForSelector('[class*="sidebar"], [class*="nav"], text="Dashboard"', { timeout: 5000 });

        } catch (error) {
            console.log('Login attempt failed, checking if already logged in...');
            // Try to navigate to dashboard to check if already logged in
            await page.goto(`${this.baseURL}/admin/dashboard`);
            await page.waitForSelector('text="Dashboard"', { timeout: 5000 });
        }
    }

    /**
     * Take screenshot for failed tests
     */
    async takeScreenshot(page, testName) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${testName}-${timestamp}.png`;
        const screenshotPath = path.join('test-results', 'screenshots', filename);
        
        // Ensure directory exists
        await fs.mkdir(path.dirname(screenshotPath), { recursive: true });
        await page.screenshot({ path: screenshotPath, fullPage: true });
        
        return screenshotPath;
    }

    /**
     * Log test result
     */
    logResult(module, testName, status, error = null, screenshot = null) {
        if (!this.testResults.modules[module]) {
            this.testResults.modules[module] = {
                total: 0,
                passed: 0,
                failed: 0,
                tests: []
            };
        }

        this.testResults.modules[module].total++;
        this.testResults.modules[module][status]++;
        this.testResults.total++;
        this.testResults[status]++;

        this.testResults.modules[module].tests.push({
            name: testName,
            status,
            error,
            screenshot,
            timestamp: new Date().toISOString()
        });

        console.log(`[${module}] ${testName}: ${status.toUpperCase()}`);
        if (error) {
            console.log(`  Error: ${error}`);
        }
        if (screenshot) {
            console.log(`  Screenshot: ${screenshot}`);
        }
    }

    /**
     * Generate test report
     */
    async generateReport() {
        const endTime = new Date();
        const duration = endTime - this.startTime;

        const report = {
            summary: {
                startTime: this.startTime.toISOString(),
                endTime: endTime.toISOString(),
                duration: `${Math.round(duration / 1000)}s`,
                total: this.testResults.total,
                passed: this.testResults.passed,
                failed: this.testResults.failed,
                skipped: this.testResults.skipped,
                successRate: `${Math.round((this.testResults.passed / this.testResults.total) * 100)}%`
            },
            modules: this.testResults.modules
        };

        // Save JSON report
        const reportPath = path.join('test-results', 'test-report.json');
        await fs.mkdir(path.dirname(reportPath), { recursive: true });
        await fs.writeFile(reportPath, JSON.stringify(report, null, 2));

        // Generate HTML report
        await this.generateHTMLReport(report);

        console.log('\n=== TEST SUMMARY ===');
        console.log(`Total Tests: ${report.summary.total}`);
        console.log(`Passed: ${report.summary.passed}`);
        console.log(`Failed: ${report.summary.failed}`);
        console.log(`Success Rate: ${report.summary.successRate}`);
        console.log(`Duration: ${report.summary.duration}`);
        console.log(`Report saved to: ${reportPath}`);

        return report;
    }

    /**
     * Generate HTML report
     */
    async generateHTMLReport(report) {
        const html = `
<!DOCTYPE html>
<html>
<head>
    <title>YukiMart Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .summary { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .module { margin-bottom: 30px; }
        .module h3 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .test { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }
        .test.passed { border-left-color: #4CAF50; background: #f1f8e9; }
        .test.failed { border-left-color: #f44336; background: #ffebee; }
        .test.skipped { border-left-color: #ff9800; background: #fff3e0; }
        .error { color: #d32f2f; font-size: 0.9em; margin-top: 5px; }
        .screenshot { margin-top: 5px; }
        .screenshot a { color: #1976d2; text-decoration: none; }
        .stats { display: flex; gap: 20px; }
        .stat { text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; }
        .stat-label { color: #666; }
    </style>
</head>
<body>
    <h1>YukiMart Test Report</h1>
    
    <div class="summary">
        <h2>Test Summary</h2>
        <div class="stats">
            <div class="stat">
                <div class="stat-value">${report.summary.total}</div>
                <div class="stat-label">Total Tests</div>
            </div>
            <div class="stat">
                <div class="stat-value" style="color: #4CAF50">${report.summary.passed}</div>
                <div class="stat-label">Passed</div>
            </div>
            <div class="stat">
                <div class="stat-value" style="color: #f44336">${report.summary.failed}</div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat">
                <div class="stat-value" style="color: #ff9800">${report.summary.skipped}</div>
                <div class="stat-label">Skipped</div>
            </div>
            <div class="stat">
                <div class="stat-value">${report.summary.successRate}</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>
        <p><strong>Duration:</strong> ${report.summary.duration}</p>
        <p><strong>Start Time:</strong> ${report.summary.startTime}</p>
        <p><strong>End Time:</strong> ${report.summary.endTime}</p>
    </div>

    ${Object.entries(report.modules).map(([moduleName, moduleData]) => `
    <div class="module">
        <h3>${moduleName} Module (${moduleData.passed}/${moduleData.total} passed)</h3>
        ${moduleData.tests.map(test => `
        <div class="test ${test.status}">
            <strong>${test.name}</strong>
            <span style="float: right; color: #666;">${test.timestamp}</span>
            ${test.error ? `<div class="error">Error: ${test.error}</div>` : ''}
            ${test.screenshot ? `<div class="screenshot"><a href="${test.screenshot}" target="_blank">View Screenshot</a></div>` : ''}
        </div>
        `).join('')}
    </div>
    `).join('')}

</body>
</html>`;

        const htmlPath = path.join('test-results', 'test-report.html');
        await fs.writeFile(htmlPath, html);
    }

    /**
     * Wait for element with timeout
     */
    async waitForElement(page, selector, timeout = 10000) {
        try {
            await page.waitForSelector(selector, { timeout });
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Check if element exists
     */
    async elementExists(page, selector) {
        try {
            const element = await page.$(selector);
            return element !== null;
        } catch (error) {
            return false;
        }
    }

    /**
     * Get text content safely
     */
    async getTextContent(page, selector) {
        try {
            const element = await page.$(selector);
            return element ? await element.textContent() : '';
        } catch (error) {
            return '';
        }
    }

    /**
     * Click element safely
     */
    async clickElement(page, selector) {
        try {
            await page.click(selector);
            return true;
        } catch (error) {
            console.log(`Failed to click ${selector}: ${error.message}`);
            return false;
        }
    }

    /**
     * Fill input safely
     */
    async fillInput(page, selector, value) {
        try {
            await page.fill(selector, value);
            return true;
        } catch (error) {
            console.log(`Failed to fill ${selector}: ${error.message}`);
            return false;
        }
    }

    /**
     * Wait for page load
     */
    async waitForPageLoad(page, timeout = 10000) {
        try {
            await page.waitForLoadState('networkidle', { timeout });
            return true;
        } catch (error) {
            return false;
        }
    }
}

module.exports = YukiMartTestRunner;
