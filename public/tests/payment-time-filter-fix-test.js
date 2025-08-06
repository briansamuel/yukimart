/**
 * Test script to verify payment time filter bug fixes
 * Run this in browser console on http://yukimart.local/admin/payments
 */

console.log('=== PAYMENT TIME FILTER FIX TEST ===');

// Test 1: Check if hidden input is created
function testHiddenInputCreation() {
    console.log('\n1. Testing hidden input creation...');
    
    const hiddenInput = document.getElementById('hidden_time_filter');
    if (hiddenInput) {
        console.log('✅ Hidden input exists');
        console.log('   - Name:', hiddenInput.name);
        console.log('   - Value:', hiddenInput.value);
        return true;
    } else {
        console.log('❌ Hidden input not found');
        return false;
    }
}

// Test 2: Check if time filter options work
function testTimeFilterOptions() {
    console.log('\n2. Testing time filter options...');
    
    const timeOptions = document.querySelectorAll('.time-option');
    console.log('Found', timeOptions.length, 'time options');
    
    if (timeOptions.length > 0) {
        console.log('✅ Time options found');
        timeOptions.forEach((option, index) => {
            console.log(`   - Option ${index + 1}: ${option.textContent} (value: ${option.dataset.value})`);
        });
        return true;
    } else {
        console.log('❌ No time options found');
        return false;
    }
}

// Test 3: Simulate clicking a time filter option
function testTimeFilterClick(filterValue = 'today') {
    console.log(`\n3. Testing time filter click (${filterValue})...`);
    
    const option = document.querySelector(`[data-value="${filterValue}"]`);
    if (option) {
        console.log(`Found option for ${filterValue}, simulating click...`);
        
        // Get initial hidden input value
        const hiddenInput = document.getElementById('hidden_time_filter');
        const initialValue = hiddenInput ? hiddenInput.value : 'not found';
        console.log('Initial hidden input value:', initialValue);
        
        // Simulate click
        option.click();
        
        // Check updated value
        setTimeout(() => {
            const updatedValue = hiddenInput ? hiddenInput.value : 'not found';
            console.log('Updated hidden input value:', updatedValue);
            
            if (updatedValue === filterValue) {
                console.log('✅ Time filter click test passed');
            } else {
                console.log('❌ Time filter click test failed');
            }
        }, 100);
        
        return true;
    } else {
        console.log(`❌ Option for ${filterValue} not found`);
        return false;
    }
}

// Test 4: Check form data collection
function testFormDataCollection() {
    console.log('\n4. Testing form data collection...');
    
    const form = document.getElementById('kt_payment_filter_form');
    if (form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        console.log('Form data collected:', data);
        
        if (data.time_filter) {
            console.log('✅ time_filter found in form data:', data.time_filter);
            return true;
        } else {
            console.log('❌ time_filter not found in form data');
            return false;
        }
    } else {
        console.log('❌ Form not found');
        return false;
    }
}

// Test 5: Check AJAX request data
function testAjaxRequestData() {
    console.log('\n5. Testing AJAX request data...');
    
    // Mock the getFilterData function if it exists
    if (typeof window.KTPaymentsList !== 'undefined' && window.KTPaymentsList.getFilterData) {
        const filterData = window.KTPaymentsList.getFilterData();
        console.log('Filter data from getFilterData():', filterData);
        
        if (filterData.time_filter) {
            console.log('✅ time_filter found in AJAX data:', filterData.time_filter);
            return true;
        } else {
            console.log('❌ time_filter not found in AJAX data');
            return false;
        }
    } else {
        console.log('⚠️ KTPaymentsList.getFilterData not available for testing');
        return null;
    }
}

// Run all tests
function runAllTests() {
    console.log('Starting payment time filter fix tests...\n');
    
    const results = {
        hiddenInput: testHiddenInputCreation(),
        timeOptions: testTimeFilterOptions(),
        formData: testFormDataCollection(),
        ajaxData: testAjaxRequestData()
    };
    
    // Test time filter click after a delay to ensure page is loaded
    setTimeout(() => {
        testTimeFilterClick('today');
        
        // Summary
        console.log('\n=== TEST RESULTS SUMMARY ===');
        Object.entries(results).forEach(([test, result]) => {
            const status = result === true ? '✅ PASS' : result === false ? '❌ FAIL' : '⚠️ SKIP';
            console.log(`${test}: ${status}`);
        });
        
        const passCount = Object.values(results).filter(r => r === true).length;
        const totalCount = Object.values(results).filter(r => r !== null).length;
        console.log(`\nOverall: ${passCount}/${totalCount} tests passed`);
        
        if (passCount === totalCount) {
            console.log('🎉 All tests passed! Time filter fix appears to be working.');
        } else {
            console.log('⚠️ Some tests failed. Please check the implementation.');
        }
    }, 1000);
}

// Auto-run tests when script is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAllTests);
} else {
    runAllTests();
}

// Export functions for manual testing
window.PaymentTimeFilterTest = {
    runAllTests,
    testHiddenInputCreation,
    testTimeFilterOptions,
    testTimeFilterClick,
    testFormDataCollection,
    testAjaxRequestData
};

console.log('Test functions available at window.PaymentTimeFilterTest');
