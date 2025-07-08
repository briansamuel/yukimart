/**
 * Test script for Barcode functionality with Tabs
 * Copy and paste this into browser console on quick order page
 */

console.log('🔍 Testing Barcode Functionality with Tabs...');

// Test functions
window.testBarcodeTabs = {
    
    // 1. Test tab creation and barcode input focus
    testTabCreation: function() {
        console.log('🧪 Testing tab creation and barcode focus...');
        
        // Create new tab
        if (window.quickOrderTabs) {
            const newTabId = window.quickOrderTabs.createNewTab('Test Tab');
            console.log('✅ Created tab:', newTabId);
            
            // Check if barcode input exists and is focused
            setTimeout(() => {
                const barcodeInput = document.querySelector(`#${newTabId}-barcodeInput`);
                if (barcodeInput) {
                    console.log('✅ Barcode input found:', barcodeInput.id);
                    console.log('✅ Is focused:', document.activeElement === barcodeInput);
                } else {
                    console.log('❌ Barcode input not found for tab:', newTabId);
                }
            }, 300);
        } else {
            console.log('❌ quickOrderTabs not found');
        }
    },
    
    // 2. Test barcode input event binding
    testBarcodeEvents: function() {
        console.log('🧪 Testing barcode event binding...');
        
        const activeTabId = window.quickOrderTabs?.getActiveTabId();
        if (!activeTabId) {
            console.log('❌ No active tab found');
            return;
        }
        
        const barcodeInput = document.querySelector(`#${activeTabId}-barcodeInput`);
        if (!barcodeInput) {
            console.log('❌ Barcode input not found for active tab');
            return;
        }
        
        // Test keypress event
        console.log('Testing keypress event...');
        const enterEvent = new KeyboardEvent('keypress', {
            key: 'Enter',
            code: 'Enter',
            which: 13,
            keyCode: 13,
            bubbles: true
        });
        
        barcodeInput.value = 'TEST123';
        barcodeInput.dispatchEvent(enterEvent);
        console.log('✅ Enter keypress event dispatched');
        
        // Test input event
        console.log('Testing input event...');
        const inputEvent = new Event('input', { bubbles: true });
        barcodeInput.value = 'TEST456';
        barcodeInput.dispatchEvent(inputEvent);
        console.log('✅ Input event dispatched');
    },
    
    // 3. Test search button functionality
    testSearchButton: function() {
        console.log('🧪 Testing search button functionality...');
        
        const activeTabId = window.quickOrderTabs?.getActiveTabId();
        if (!activeTabId) {
            console.log('❌ No active tab found');
            return;
        }
        
        const searchBtn = document.querySelector(`#${activeTabId}-searchBarcodeBtn`);
        if (!searchBtn) {
            console.log('❌ Search button not found for active tab');
            return;
        }
        
        // Set test barcode
        const barcodeInput = document.querySelector(`#${activeTabId}-barcodeInput`);
        if (barcodeInput) {
            barcodeInput.value = 'TEST789';
        }
        
        // Click search button
        searchBtn.click();
        console.log('✅ Search button clicked');
    },
    
    // 4. Test QuickOrder instance per tab
    testQuickOrderInstances: function() {
        console.log('🧪 Testing QuickOrder instances per tab...');
        
        if (!window.quickOrderTabs) {
            console.log('❌ quickOrderTabs not found');
            return;
        }
        
        const allTabs = window.quickOrderTabs.getAllTabs();
        console.log(`Found ${allTabs.length} tabs`);
        
        allTabs.forEach((tab, index) => {
            console.log(`Tab ${index + 1}:`, {
                id: tab.id,
                title: tab.title,
                hasQuickOrderInstance: !!tab.quickOrderInstance,
                tabId: tab.quickOrderInstance?.tabId,
                orderItemsCount: tab.quickOrderInstance?.orderItems?.length || 0
            });
        });
    },
    
    // 5. Test tab switching and barcode focus
    testTabSwitching: function() {
        console.log('🧪 Testing tab switching and barcode focus...');
        
        if (!window.quickOrderTabs) {
            console.log('❌ quickOrderTabs not found');
            return;
        }
        
        const allTabs = window.quickOrderTabs.getAllTabs();
        if (allTabs.length < 2) {
            console.log('⚠️ Need at least 2 tabs for this test');
            return;
        }
        
        // Switch to different tabs and check focus
        allTabs.forEach((tab, index) => {
            setTimeout(() => {
                console.log(`Switching to tab: ${tab.title}`);
                window.quickOrderTabs.switchToTab(tab.id);
                
                setTimeout(() => {
                    const barcodeInput = document.querySelector(`#${tab.id}-barcodeInput`);
                    const isFocused = document.activeElement === barcodeInput;
                    console.log(`Tab ${tab.title} - Barcode focused: ${isFocused}`);
                }, 100);
            }, index * 1000);
        });
    },
    
    // 6. Test barcode API call simulation
    testBarcodeAPI: function() {
        console.log('🧪 Testing barcode API call...');
        
        const activeTabId = window.quickOrderTabs?.getActiveTabId();
        if (!activeTabId) {
            console.log('❌ No active tab found');
            return;
        }
        
        const activeTab = window.quickOrderTabs.getActiveTab();
        if (!activeTab?.quickOrderInstance) {
            console.log('❌ No QuickOrder instance found for active tab');
            return;
        }
        
        // Set test barcode
        const barcodeInput = document.querySelector(`#${activeTabId}-barcodeInput`);
        if (barcodeInput) {
            barcodeInput.value = 'TEST999';
            console.log('✅ Set test barcode: TEST999');
        }
        
        // Call searchBarcode method directly
        try {
            activeTab.quickOrderInstance.searchBarcode();
            console.log('✅ searchBarcode() method called');
        } catch (error) {
            console.log('❌ Error calling searchBarcode():', error.message);
        }
    },
    
    // 7. Test selector generation
    testSelectorGeneration: function() {
        console.log('🧪 Testing selector generation...');
        
        const activeTabId = window.quickOrderTabs?.getActiveTabId();
        if (!activeTabId) {
            console.log('❌ No active tab found');
            return;
        }
        
        const activeTab = window.quickOrderTabs.getActiveTab();
        if (!activeTab?.quickOrderInstance) {
            console.log('❌ No QuickOrder instance found for active tab');
            return;
        }
        
        const quickOrder = activeTab.quickOrderInstance;
        
        // Test various selectors
        const selectors = [
            'barcodeInput',
            'searchBarcodeBtn',
            'orderItemsTableBody',
            'customerSelect',
            'createOrderBtn'
        ];
        
        selectors.forEach(selector => {
            const tabSelector = quickOrder.getTabSelector(selector);
            const element = document.querySelector(tabSelector);
            console.log(`${selector}: ${tabSelector} - ${element ? '✅ Found' : '❌ Not found'}`);
        });
    },
    
    // 8. Test complete barcode workflow
    testCompleteWorkflow: function() {
        console.log('🧪 Testing complete barcode workflow...');
        
        const activeTabId = window.quickOrderTabs?.getActiveTabId();
        if (!activeTabId) {
            console.log('❌ No active tab found');
            return;
        }
        
        const barcodeInput = document.querySelector(`#${activeTabId}-barcodeInput`);
        if (!barcodeInput) {
            console.log('❌ Barcode input not found');
            return;
        }
        
        console.log('Step 1: Focus barcode input');
        barcodeInput.focus();
        
        setTimeout(() => {
            console.log('Step 2: Type barcode');
            barcodeInput.value = 'WORKFLOW123';
            
            const inputEvent = new Event('input', { bubbles: true });
            barcodeInput.dispatchEvent(inputEvent);
            
            setTimeout(() => {
                console.log('Step 3: Press Enter');
                const enterEvent = new KeyboardEvent('keypress', {
                    key: 'Enter',
                    code: 'Enter',
                    which: 13,
                    keyCode: 13,
                    bubbles: true
                });
                barcodeInput.dispatchEvent(enterEvent);
                
                console.log('✅ Complete workflow test finished');
            }, 600);
        }, 500);
    },
    
    // 9. Run all tests
    runAllTests: function() {
        console.log('🚀 Running all barcode tabs tests...');
        
        this.testTabCreation();
        
        setTimeout(() => this.testBarcodeEvents(), 1000);
        setTimeout(() => this.testSearchButton(), 2000);
        setTimeout(() => this.testQuickOrderInstances(), 3000);
        setTimeout(() => this.testTabSwitching(), 4000);
        setTimeout(() => this.testSelectorGeneration(), 8000);
        setTimeout(() => this.testBarcodeAPI(), 9000);
        setTimeout(() => this.testCompleteWorkflow(), 10000);
        
        setTimeout(() => {
            console.log('🎉 All tests completed!');
        }, 15000);
    }
};

// Auto-run basic tests
console.log('🔄 Running basic tests...');
testBarcodeTabs.testQuickOrderInstances();
testBarcodeTabs.testSelectorGeneration();

// Instructions
console.log('📋 Available test commands:');
console.log('   testBarcodeTabs.testTabCreation()      - Test tab creation and focus');
console.log('   testBarcodeTabs.testBarcodeEvents()    - Test event binding');
console.log('   testBarcodeTabs.testSearchButton()     - Test search button');
console.log('   testBarcodeTabs.testQuickOrderInstances() - Test QuickOrder instances');
console.log('   testBarcodeTabs.testTabSwitching()     - Test tab switching');
console.log('   testBarcodeTabs.testSelectorGeneration() - Test selector generation');
console.log('   testBarcodeTabs.testBarcodeAPI()       - Test API call');
console.log('   testBarcodeTabs.testCompleteWorkflow() - Test complete workflow');
console.log('   testBarcodeTabs.runAllTests()          - Run all tests');

console.log('✅ Barcode tabs test suite loaded successfully!');
