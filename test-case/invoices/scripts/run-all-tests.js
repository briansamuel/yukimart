/**
 * Master Test Runner for Invoice Module
 * Runs all test categories and generates comprehensive report
 */

const FilterTests = require('./filter-tests');
const ColumnVisibilityTests = require('./column-visibility-tests');
const RowExpansionTests = require('./row-expansion-tests');
const fs = require('fs').promises;
const path = require('path');

class InvoiceMasterTestRunner {
    constructor() {
        this.allResults = [];
        this.startTime = new Date();
    }

    async runAllTestCategories() {
        console.log('🎯 STARTING COMPREHENSIVE INVOICE TESTS');
        console.log('========================================\n');

        const testCategories = [
            { name: 'Filter Tests', class: FilterTests, id: 'F' },
            { name: 'Column Visibility Tests', class: ColumnVisibilityTests, id: 'CV' },
            { name: 'Row Expansion Tests', class: RowExpansionTests, id: 'RE' }
        ];

        for (const category of testCategories) {
            console.log(`\n🚀 Running ${category.name}...`);
            console.log('='.repeat(50));
            
            try {
                const testInstance = new category.class();
                await testInstance.runAllTests();
                
                // Collect results
                this.allResults.push({
                    category: category.name,
                    id: category.id,
                    results: testInstance.results || []
                });
                
            } catch (error) {
                console.error(`❌ Error running ${category.name}:`, error.message);
                this.allResults.push({
                    category: category.name,
                    id: category.id,
                    results: [{
                        id: `${category.id}00`,
                        name: `${category.name} - Setup Error`,
                        status: 'FAILED',
                        details: error.message
                    }]
                });
            }
        }

        await this.generateComprehensiveReport();
    }

    async generateComprehensiveReport() {
        console.log('\n\n📊 COMPREHENSIVE INVOICE TESTS REPORT');
        console.log('=====================================');
        
        let totalPassed = 0;
        let totalFailed = 0;
        let totalTests = 0;
        
        const reportLines = [];
        reportLines.push('# Invoice Module - Comprehensive Test Report');
        reportLines.push(`Generated: ${new Date().toLocaleString()}`);
        reportLines.push('');
        
        // Summary by category
        reportLines.push('## Summary by Category');
        reportLines.push('| Category | Passed | Failed | Total | Success Rate |');
        reportLines.push('|----------|--------|--------|-------|--------------|');
        
        this.allResults.forEach(category => {
            const passed = category.results.filter(r => r.status === 'PASSED').length;
            const failed = category.results.filter(r => r.status === 'FAILED').length;
            const total = category.results.length;
            const successRate = total > 0 ? Math.round((passed / total) * 100) : 0;
            
            totalPassed += passed;
            totalFailed += failed;
            totalTests += total;
            
            const status = successRate === 100 ? '✅' : successRate >= 80 ? '⚠️' : '❌';
            
            reportLines.push(`| ${status} ${category.category} | ${passed} | ${failed} | ${total} | ${successRate}% |`);
            
            console.log(`${status} ${category.category}: ${passed}/${total} passed (${successRate}%)`);
        });
        
        const overallSuccessRate = totalTests > 0 ? Math.round((totalPassed / totalTests) * 100) : 0;
        reportLines.push(`| **TOTAL** | **${totalPassed}** | **${totalFailed}** | **${totalTests}** | **${overallSuccessRate}%** |`);
        
        console.log(`\n🎯 OVERALL: ${totalPassed}/${totalTests} tests passed (${overallSuccessRate}%)`);
        
        // Detailed results
        reportLines.push('');
        reportLines.push('## Detailed Test Results');
        
        this.allResults.forEach(category => {
            reportLines.push(`\n### ${category.category}`);
            reportLines.push('| Test ID | Test Name | Status | Details |');
            reportLines.push('|---------|-----------|--------|---------|');
            
            category.results.forEach(result => {
                const status = result.status === 'PASSED' ? '✅ PASSED' : '❌ FAILED';
                reportLines.push(`| ${result.id} | ${result.name} | ${status} | ${result.details} |`);
            });
        });
        
        // Failed tests summary
        const failedTests = [];
        this.allResults.forEach(category => {
            category.results.forEach(result => {
                if (result.status === 'FAILED') {
                    failedTests.push({
                        category: category.category,
                        ...result
                    });
                }
            });
        });
        
        if (failedTests.length > 0) {
            reportLines.push('\n## Failed Tests Summary');
            reportLines.push('| Category | Test ID | Test Name | Error Details |');
            reportLines.push('|----------|---------|-----------|---------------|');
            
            failedTests.forEach(test => {
                reportLines.push(`| ${test.category} | ${test.id} | ${test.name} | ${test.details} |`);
            });
        }
        
        // Test execution info
        const endTime = new Date();
        const duration = Math.round((endTime - this.startTime) / 1000);
        
        reportLines.push('\n## Test Execution Info');
        reportLines.push(`- **Start Time**: ${this.startTime.toLocaleString()}`);
        reportLines.push(`- **End Time**: ${endTime.toLocaleString()}`);
        reportLines.push(`- **Duration**: ${duration} seconds`);
        reportLines.push(`- **Total Tests**: ${totalTests}`);
        reportLines.push(`- **Success Rate**: ${overallSuccessRate}%`);
        
        // Save report to file
        const reportContent = reportLines.join('\n');
        const reportPath = path.join(__dirname, '..', 'comprehensive-test-report.md');
        
        try {
            await fs.writeFile(reportPath, reportContent, 'utf8');
            console.log(`\n📝 Comprehensive report saved to: ${reportPath}`);
        } catch (error) {
            console.error('❌ Error saving report:', error.message);
        }
        
        // Update main report.md
        await this.updateMainReport(totalPassed, totalFailed, totalTests, overallSuccessRate);
        
        console.log('\n🏁 All tests completed!');
        
        return {
            totalPassed,
            totalFailed,
            totalTests,
            overallSuccessRate,
            duration,
            failedTests
        };
    }
    
    async updateMainReport(passed, failed, total, successRate) {
        try {
            const mainReportPath = path.join(__dirname, '..', 'report.md');
            let content = await fs.readFile(mainReportPath, 'utf8');
            
            // Update progress section
            const newProgressLine = `### 📈 **Tiến Độ Tổng Thể**: ${passed}/${total}+ tests completed (${successRate}%)`;
            content = content.replace(/### 📈 \*\*Tiến Độ Tổng Thể\*\*:.*/, newProgressLine);
            
            // Add automation results
            const automationSection = `
### 🤖 **Automation Test Results** (Latest Run: ${new Date().toLocaleString()})
- ✅ Filter Tests: Automated via Playwright
- ✅ Column Visibility Tests: Automated via Playwright  
- ✅ Row Expansion Tests: Automated via Playwright
- **Total Automated Tests**: ${total}
- **Success Rate**: ${successRate}%
- **Failed Tests**: ${failed}
`;
            
            if (!content.includes('🤖 **Automation Test Results**')) {
                content += automationSection;
            } else {
                content = content.replace(/### 🤖 \*\*Automation Test Results\*\*.*?(?=###|$)/s, automationSection);
            }
            
            await fs.writeFile(mainReportPath, content, 'utf8');
            console.log('📝 Main report.md updated with automation results');
            
        } catch (error) {
            console.error('❌ Error updating main report:', error.message);
        }
    }
}

// Run all tests if called directly
if (require.main === module) {
    const runner = new InvoiceMasterTestRunner();
    runner.runAllTestCategories().catch(console.error);
}

module.exports = InvoiceMasterTestRunner;
