/**
 * Quick Order Test Script
 * Run this in browser console to test functionality
 */

// Test configuration
const TEST_CONFIG = {
    DELAY_BETWEEN_TESTS: 1000,
    VERBOSE_LOGGING: true
};

// Test results storage
let testResults = {
    passed: 0,
    failed: 0,
    total: 0,
    details: []
};

/**
 * Test utility functions
 */
function testLog(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const prefix = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
    console.log(`${prefix} [${timestamp}] ${message}`);
    
    if (TEST_CONFIG.VERBOSE_LOGGING) {
        testResults.details.push({
            timestamp,
            message,
            type
        });
    }
}

function testAssert(condition, testName, errorMessage = '') {
    testResults.total++;
    
    if (condition) {
        testResults.passed++;
        testLog(`PASS: ${testName}`, 'success');
        return true;
    } else {
        testResults.failed++;
        testLog(`FAIL: ${testName} - ${errorMessage}`, 'error');
        return false;
    }
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Test 1: Basic DOM Elements
 */
async function testBasicElements() {
    testLog('Testing basic DOM elements...', 'info');
    
    testAssert(
        $('#orderTabTemplate').length > 0,
        'Tab template exists',
        'orderTabTemplate not found in DOM'
    );
    
    testAssert(
        $('#orderTabsContainer').length > 0,
        'Tab container exists',
        'orderTabsContainer not found in DOM'
    );
    
    testAssert(
        $('#orderTabsContent').length > 0,
        'Tab content container exists',
        'orderTabsContent not found in DOM'
    );
    
    testAssert(
        $('#barcodeInput').length > 0,
        'Barcode input exists',
        'barcodeInput not found in DOM'
    );
    
    testAssert(
        typeof addNewTab === 'function',
        'addNewTab function exists',
        'addNewTab function not defined'
    );
    
    testAssert(
        typeof switchTab === 'function',
        'switchTab function exists',
        'switchTab function not defined'
    );
}

/**
 * Test 2: Tab Creation
 */
async function testTabCreation() {
    testLog('Testing tab creation...', 'info');
    
    // Clear existing tabs
    orderTabs = [];
    $('#orderTabsContainer').empty();
    $('#orderTabsContent').empty();
    tabCounter = 0;
    
    // Test order tab creation
    const orderTabId = addNewTab('order');
    testAssert(
        orderTabId && orderTabId.startsWith('tab_'),
        'Order tab created',
        'Failed to create order tab'
    );
    
    await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
    
    // Test invoice tab creation
    const invoiceTabId = addNewTab('invoice');
    testAssert(
        invoiceTabId && invoiceTabId.startsWith('tab_'),
        'Invoice tab created',
        'Failed to create invoice tab'
    );
    
    await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
    
    // Test return tab creation
    const returnTabId = addNewTab('return');
    testAssert(
        returnTabId && returnTabId.startsWith('tab_'),
        'Return tab created',
        'Failed to create return tab'
    );
    
    // Test tab count
    testAssert(
        orderTabs.length === 3,
        'Correct number of tabs created',
        `Expected 3 tabs, got ${orderTabs.length}`
    );
    
    // Test DOM elements created
    testAssert(
        $('.order-tab').length === 3,
        'Tab elements created in DOM',
        `Expected 3 tab elements, got ${$('.order-tab').length}`
    );
    
    testAssert(
        $('[data-tab-id]').length === 3,
        'Tab content elements created in DOM',
        `Expected 3 tab content elements, got ${$('[data-tab-id]').length}`
    );
}

/**
 * Test 3: Tab Switching
 */
async function testTabSwitching() {
    testLog('Testing tab switching...', 'info');
    
    if (orderTabs.length < 3) {
        testLog('Not enough tabs for switching test, skipping...', 'error');
        return;
    }
    
    // Test switching to each tab
    for (let i = 0; i < orderTabs.length; i++) {
        const tab = orderTabs[i];
        switchTab(tab.id);
        
        await delay(500);
        
        testAssert(
            activeTabId === tab.id,
            `Switch to ${tab.type} tab`,
            `Failed to switch to tab ${tab.id}`
        );
        
        testAssert(
            $(`#${tab.id}`).hasClass('active'),
            `${tab.type} tab has active class`,
            `Tab ${tab.id} should have active class`
        );
        
        testAssert(
            $(`#${tab.id}_content`).is(':visible'),
            `${tab.type} tab content is visible`,
            `Tab content ${tab.id}_content should be visible`
        );
    }
}

/**
 * Test 4: Return Tab Specific Elements
 */
async function testReturnTabElements() {
    testLog('Testing return tab specific elements...', 'info');
    
    // Find return tab
    const returnTab = orderTabs.find(tab => tab.type === 'return');
    if (!returnTab) {
        testLog('No return tab found, skipping return tab tests...', 'error');
        return;
    }
    
    // Switch to return tab
    switchTab(returnTab.id);
    await delay(500);
    
    const tabContent = $(`#${returnTab.id}_content`);
    
    // Test return-specific elements are visible
    testAssert(
        tabContent.find(`#${returnTab.id}_returnOrderHeader`).is(':visible'),
        'Return order header is visible',
        'Return order header should be visible in return tab'
    );
    
    testAssert(
        tabContent.find(`#${returnTab.id}_exchangeSearchSection`).is(':visible'),
        'Exchange search section is visible',
        'Exchange search section should be visible in return tab'
    );
    
    testAssert(
        tabContent.find(`#${returnTab.id}_returnSummarySection`).is(':visible'),
        'Return summary section is visible',
        'Return summary section should be visible in return tab'
    );
    
    testAssert(
        tabContent.find(`#${returnTab.id}_regularSummarySection`).is(':hidden'),
        'Regular summary section is hidden',
        'Regular summary section should be hidden in return tab'
    );
    
    // Test button text
    const buttonText = tabContent.find('#createOrderBtn').text().trim();
    testAssert(
        buttonText.includes('TRẢ HÀNG'),
        'Return tab button text is correct',
        `Expected button text to contain "TRẢ HÀNG", got "${buttonText}"`
    );
}

/**
 * Test 5: Order/Invoice Tab Elements
 */
async function testOrderInvoiceTabElements() {
    testLog('Testing order/invoice tab elements...', 'info');
    
    // Test order tab
    const orderTab = orderTabs.find(tab => tab.type === 'order');
    if (orderTab) {
        switchTab(orderTab.id);
        await delay(500);
        
        const tabContent = $(`#${orderTab.id}_content`);
        
        testAssert(
            tabContent.find(`#${orderTab.id}_returnOrderHeader`).is(':hidden'),
            'Return header hidden in order tab',
            'Return header should be hidden in order tab'
        );
        
        testAssert(
            tabContent.find(`#${orderTab.id}_regularSummarySection`).is(':visible'),
            'Regular summary visible in order tab',
            'Regular summary should be visible in order tab'
        );
        
        const buttonText = tabContent.find('#createOrderBtn').text().trim();
        testAssert(
            buttonText.includes('THANH TOÁN'),
            'Order tab button text is correct',
            `Expected button text to contain "THANH TOÁN", got "${buttonText}"`
        );
    }
    
    // Test invoice tab
    const invoiceTab = orderTabs.find(tab => tab.type === 'invoice');
    if (invoiceTab) {
        switchTab(invoiceTab.id);
        await delay(500);
        
        const tabContent = $(`#${invoiceTab.id}_content`);
        
        testAssert(
            tabContent.find(`#${invoiceTab.id}_returnOrderHeader`).is(':hidden'),
            'Return header hidden in invoice tab',
            'Return header should be hidden in invoice tab'
        );
        
        testAssert(
            tabContent.find(`#${invoiceTab.id}_regularSummarySection`).is(':visible'),
            'Regular summary visible in invoice tab',
            'Regular summary should be visible in invoice tab'
        );
        
        const buttonText = tabContent.find('#createOrderBtn').text().trim();
        testAssert(
            buttonText.includes('HÓA ĐƠN'),
            'Invoice tab button text is correct',
            `Expected button text to contain "HÓA ĐƠN", got "${buttonText}"`
        );
    }
}

/**
 * Test 6: Keyboard Shortcuts
 */
async function testKeyboardShortcuts() {
    testLog('Testing keyboard shortcuts...', 'info');
    
    // Test F3 - focus barcode input
    const barcodeInput = $('#barcodeInput');
    barcodeInput.blur(); // Remove focus first
    
    // Simulate F3 key press
    const f3Event = new KeyboardEvent('keydown', { key: 'F3' });
    document.dispatchEvent(f3Event);
    
    await delay(100);
    
    testAssert(
        document.activeElement === barcodeInput[0],
        'F3 focuses barcode input',
        'F3 should focus the barcode input'
    );
}

/**
 * Main test runner
 */
async function runAllTests() {
    testLog('🚀 Starting Quick Order Test Suite...', 'info');
    testLog('='.repeat(50), 'info');
    
    // Reset test results
    testResults = {
        passed: 0,
        failed: 0,
        total: 0,
        details: []
    };
    
    try {
        await testBasicElements();
        await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
        
        await testTabCreation();
        await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
        
        await testTabSwitching();
        await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
        
        await testReturnTabElements();
        await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
        
        await testOrderInvoiceTabElements();
        await delay(TEST_CONFIG.DELAY_BETWEEN_TESTS);
        
        await testKeyboardShortcuts();
        
    } catch (error) {
        testLog(`Test suite error: ${error.message}`, 'error');
    }
    
    // Print results
    testLog('='.repeat(50), 'info');
    testLog(`🏁 Test Suite Complete!`, 'info');
    testLog(`✅ Passed: ${testResults.passed}`, 'success');
    testLog(`❌ Failed: ${testResults.failed}`, 'error');
    testLog(`📊 Total: ${testResults.total}`, 'info');
    testLog(`📈 Success Rate: ${((testResults.passed / testResults.total) * 100).toFixed(1)}%`, 'info');
    
    if (testResults.failed === 0) {
        testLog('🎉 All tests passed! Ready for production!', 'success');
    } else {
        testLog('⚠️ Some tests failed. Please review and fix issues.', 'error');
    }
    
    return testResults;
}

// Auto-run if in browser console
if (typeof window !== 'undefined') {
    console.log('Quick Order Test Script Loaded!');
    console.log('Run runAllTests() to start testing.');
    
    // Expose functions globally
    window.runQuickOrderTests = runAllTests;
    window.testQuickOrderBasics = testBasicElements;
    window.testQuickOrderTabs = testTabCreation;
}
