/**
 * Invoice Detail Panel Sticky Position Console Test
 * 
 * Instructions:
 * 1. Navigate to http://yukimart.local/admin/invoices
 * 2. Open browser console (F12)
 * 3. Copy and paste this entire script into the console
 * 4. Press Enter to run the tests
 * 5. Follow the prompts and observe the results
 */

(function() {
    'use strict';
    
    console.log('🧪 Invoice Detail Panel Sticky Position Test Suite');
    console.log('================================================');
    
    let testResults = [];
    let currentTest = 0;
    
    function logTest(message, status = 'info') {
        const icons = {
            'pass': '✅',
            'fail': '❌',
            'warn': '⚠️',
            'info': 'ℹ️'
        };
        
        const result = `${icons[status]} ${message}`;
        console.log(result);
        testResults.push({ message, status, timestamp: new Date() });
        return status === 'pass';
    }
    
    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // Test 1: Check if required elements exist
    function testElementsExist() {
        logTest('Test 1: Checking required elements...', 'info');
        
        const table = document.getElementById('kt_invoices_table');
        const container = document.getElementById('kt_invoices_table_container');
        const rows = document.querySelectorAll('#kt_invoices_table tbody tr');
        
        if (!table) {
            return logTest('Invoice table not found', 'fail');
        }
        
        if (!container) {
            return logTest('Table container not found', 'fail');
        }
        
        if (rows.length === 0) {
            return logTest('No invoice rows found', 'fail');
        }
        
        logTest(`Found table with ${rows.length} rows`, 'pass');
        return true;
    }
    
    // Test 2: Check CSS properties
    function testCSSProperties() {
        logTest('Test 2: Checking CSS properties...', 'info');
        
        // Check table container
        const container = document.getElementById('kt_invoices_table_container');
        const containerStyles = window.getComputedStyle(container);
        
        if (containerStyles.overflowX !== 'auto') {
            logTest(`Table container overflow-x should be 'auto', got '${containerStyles.overflowX}'`, 'warn');
        } else {
            logTest('Table container has correct overflow-x: auto', 'pass');
        }
        
        if (containerStyles.position !== 'relative') {
            logTest(`Table container position should be 'relative', got '${containerStyles.position}'`, 'warn');
        } else {
            logTest('Table container has correct position: relative', 'pass');
        }
        
        return true;
    }
    
    // Test 3: Expand detail panel
    async function testPanelExpansion() {
        logTest('Test 3: Testing panel expansion...', 'info');
        
        const firstRow = document.querySelector('#kt_invoices_table tbody tr:first-child');
        if (!firstRow) {
            return logTest('No invoice row found to click', 'fail');
        }
        
        // Click the row
        firstRow.click();
        logTest('Clicked first invoice row', 'info');
        
        // Wait for panel to load
        await delay(2000);
        
        const detailPanel = document.querySelector('.invoice-detail-panel');
        if (!detailPanel) {
            return logTest('Detail panel not found after expansion', 'fail');
        }
        
        logTest('Detail panel successfully expanded', 'pass');
        return detailPanel;
    }
    
    // Test 4: Check sticky positioning properties
    function testStickyProperties(detailPanel) {
        logTest('Test 4: Checking sticky positioning properties...', 'info');
        
        const panelStyles = window.getComputedStyle(detailPanel);
        const containerElement = detailPanel.closest('.invoice-detail-container');
        const rowElement = detailPanel.closest('.invoice-detail-row');
        
        // Check panel properties
        if (panelStyles.position !== 'sticky') {
            logTest(`Panel position should be 'sticky', got '${panelStyles.position}'`, 'fail');
            return false;
        }
        logTest('Panel has position: sticky', 'pass');
        
        if (panelStyles.left !== '0px') {
            logTest(`Panel left should be '0px', got '${panelStyles.left}'`, 'fail');
            return false;
        }
        logTest('Panel has left: 0px', 'pass');
        
        const zIndex = parseInt(panelStyles.zIndex);
        if (zIndex < 90) {
            logTest(`Panel z-index should be >= 90, got '${zIndex}'`, 'warn');
        } else {
            logTest(`Panel has adequate z-index: ${zIndex}`, 'pass');
        }
        
        // Check container properties if exists
        if (containerElement) {
            const containerStyles = window.getComputedStyle(containerElement);
            if (containerStyles.position === 'sticky') {
                logTest('Container has sticky positioning', 'pass');
            } else {
                logTest(`Container position should be 'sticky', got '${containerStyles.position}'`, 'warn');
            }
        }
        
        return true;
    }
    
    // Test 5: Test horizontal scroll behavior
    async function testScrollBehavior(detailPanel) {
        logTest('Test 5: Testing horizontal scroll behavior...', 'info');
        
        const container = document.getElementById('kt_invoices_table_container');
        
        // Check if horizontal scroll is possible
        const canScroll = container.scrollWidth > container.clientWidth;
        if (!canScroll) {
            logTest('Table does not need horizontal scroll at current viewport size', 'warn');
            logTest('Try resizing browser window to be narrower (< 800px width)', 'info');
            return true;
        }
        
        // Get initial panel position
        const initialRect = detailPanel.getBoundingClientRect();
        const initialScrollLeft = container.scrollLeft;
        
        logTest(`Initial panel position: x=${Math.round(initialRect.x)}, y=${Math.round(initialRect.y)}`, 'info');
        logTest(`Initial scroll position: ${initialScrollLeft}`, 'info');
        
        // Scroll horizontally
        const scrollAmount = 200;
        container.scrollLeft = scrollAmount;
        
        await delay(500); // Wait for scroll to complete
        
        // Get new panel position
        const newRect = detailPanel.getBoundingClientRect();
        const newScrollLeft = container.scrollLeft;
        
        logTest(`New panel position: x=${Math.round(newRect.x)}, y=${Math.round(newRect.y)}`, 'info');
        logTest(`New scroll position: ${newScrollLeft}`, 'info');
        
        // Check if table actually scrolled
        if (newScrollLeft <= initialScrollLeft) {
            logTest('Table did not scroll. May not have enough content for horizontal scroll.', 'warn');
            return true;
        }
        
        // Check if panel stayed in place (sticky behavior)
        const xDifference = Math.abs(newRect.x - initialRect.x);
        const yDifference = Math.abs(newRect.y - initialRect.y);
        
        if (xDifference > 10) {
            logTest(`Panel moved horizontally by ${xDifference}px - sticky positioning may not be working`, 'fail');
            return false;
        }
        
        if (yDifference > 5) {
            logTest(`Panel moved vertically by ${yDifference}px - unexpected movement`, 'warn');
        }
        
        logTest('Panel maintained position during horizontal scroll', 'pass');
        
        // Scroll back to original position
        container.scrollLeft = initialScrollLeft;
        await delay(300);
        
        return true;
    }
    
    // Test 6: Test extreme scroll positions
    async function testExtremeScroll(detailPanel) {
        logTest('Test 6: Testing extreme scroll positions...', 'info');
        
        const container = document.getElementById('kt_invoices_table_container');
        
        if (container.scrollWidth <= container.clientWidth) {
            logTest('Skipping extreme scroll test - no horizontal scroll available', 'warn');
            return true;
        }
        
        // Scroll to maximum right
        const maxScrollLeft = container.scrollWidth - container.clientWidth;
        container.scrollLeft = maxScrollLeft;
        
        await delay(500);
        
        // Check if panel is still visible
        const rect = detailPanel.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        
        if (rect.right < 0 || rect.left > viewportWidth) {
            logTest('Panel is not visible at maximum scroll position', 'fail');
            return false;
        }
        
        logTest('Panel remains visible at maximum scroll position', 'pass');
        
        // Scroll back to left
        container.scrollLeft = 0;
        await delay(300);
        
        return true;
    }
    
    // Test 7: Test multiple panels
    async function testMultiplePanels() {
        logTest('Test 7: Testing multiple panels...', 'info');
        
        const rows = document.querySelectorAll('#kt_invoices_table tbody tr:not(.invoice-detail-row)');
        if (rows.length < 2) {
            logTest('Not enough rows to test multiple panels', 'warn');
            return true;
        }
        
        // Click second row (skip first which should already be expanded)
        const secondRow = rows[1];
        secondRow.click();
        
        await delay(2000);
        
        const panels = document.querySelectorAll('.invoice-detail-panel');
        logTest(`Found ${panels.length} detail panels`, 'info');
        
        if (panels.length > 1) {
            logTest('Multiple panels can be opened simultaneously', 'pass');
            
            // Test scroll behavior with multiple panels
            const container = document.getElementById('kt_invoices_table_container');
            if (container.scrollWidth > container.clientWidth) {
                container.scrollLeft = 100;
                await delay(500);
                
                let allPanelsSticky = true;
                panels.forEach((panel, index) => {
                    const rect = panel.getBoundingClientRect();
                    if (rect.left > 50) { // Allow some tolerance
                        logTest(`Panel ${index + 1} may not be sticky (left: ${rect.left})`, 'warn');
                        allPanelsSticky = false;
                    }
                });
                
                if (allPanelsSticky) {
                    logTest('All panels maintain sticky positioning', 'pass');
                }
                
                container.scrollLeft = 0;
            }
        }
        
        return true;
    }
    
    // Main test runner
    async function runAllTests() {
        logTest('Starting Invoice Detail Panel Sticky Position Tests', 'info');
        logTest('Current viewport size: ' + window.innerWidth + 'x' + window.innerHeight, 'info');
        
        if (window.innerWidth > 1000) {
            logTest('Consider resizing browser to < 800px width for better horizontal scroll testing', 'warn');
        }
        
        try {
            // Test 1: Check elements
            if (!testElementsExist()) {
                logTest('Basic elements test failed. Cannot continue.', 'fail');
                return;
            }
            
            // Test 2: Check CSS
            testCSSProperties();
            
            // Test 3: Expand panel
            const detailPanel = await testPanelExpansion();
            if (!detailPanel) {
                logTest('Panel expansion failed. Cannot continue with positioning tests.', 'fail');
                return;
            }
            
            // Test 4: Check sticky properties
            if (!testStickyProperties(detailPanel)) {
                logTest('Sticky properties test failed.', 'fail');
            }
            
            // Test 5: Test scroll behavior
            await testScrollBehavior(detailPanel);
            
            // Test 6: Test extreme scroll
            await testExtremeScroll(detailPanel);
            
            // Test 7: Test multiple panels
            await testMultiplePanels();
            
        } catch (error) {
            logTest(`Test suite error: ${error.message}`, 'fail');
            console.error(error);
        }
        
        // Summary
        const passed = testResults.filter(r => r.status === 'pass').length;
        const failed = testResults.filter(r => r.status === 'fail').length;
        const warnings = testResults.filter(r => r.status === 'warn').length;
        
        console.log('\n📊 Test Summary:');
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`⚠️ Warnings: ${warnings}`);
        console.log(`📝 Total: ${testResults.length}`);
        
        if (failed === 0) {
            logTest('🎉 All tests passed! Sticky positioning is working correctly.', 'pass');
        } else {
            logTest('❌ Some tests failed. Check the results above for details.', 'fail');
        }
        
        // Provide manual testing instructions
        console.log('\n📋 Manual Testing Instructions:');
        console.log('1. Resize browser window to < 800px width');
        console.log('2. Click on invoice rows to expand detail panels');
        console.log('3. Scroll table horizontally using scrollbar or Shift+MouseWheel');
        console.log('4. Verify panels stay fixed at left edge of viewport');
        console.log('5. Test with multiple panels open simultaneously');
    }
    
    // Auto-run tests
    runAllTests();
    
})();
