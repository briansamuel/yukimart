// Debug script for new customer functionality
console.log('🔍 Debug script loaded');

$(document).ready(function() {
    console.log('🔍 Document ready');
    
    // Check if required elements exist
    const elementsToCheck = [
        '#customer_id',
        '#btn_add_new_customer', 
        '#new_customer_form',
        '#new_customer_name',
        '#new_customer_phone',
        '#btn_save_new_customer'
    ];
    
    console.log('🔍 Checking DOM elements...');
    elementsToCheck.forEach(selector => {
        const element = $(selector);
        if (element.length > 0) {
            console.log(`✅ ${selector}: Found (${element.length} elements)`);
        } else {
            console.error(`❌ ${selector}: Not found`);
        }
    });
    
    // Check if KTOrderAdd is available
    if (typeof KTOrderAdd !== 'undefined') {
        console.log('✅ KTOrderAdd object is available');
        
        // Check if methods exist
        const methodsToCheck = [
            'showNewCustomerForm',
            'hideNewCustomerForm',
            'createNewCustomer'
        ];
        
        methodsToCheck.forEach(method => {
            if (typeof KTOrderAdd[method] === 'function') {
                console.log(`✅ KTOrderAdd.${method}: Available`);
            } else {
                console.error(`❌ KTOrderAdd.${method}: Not available`);
            }
        });
    } else {
        console.error('❌ KTOrderAdd object is not available');
    }
    
   
    
    // Monitor Select2 events
    $(document).on('select2:select', '#customer_id', function(e) {
        console.log('🔍 Select2 select event triggered:', e.params.data);
    });
    
    // Monitor button clicks
    $(document).on('click', '#btn_add_new_customer', function() {
        console.log('🔍 Add new customer button clicked (document level)');
    });
    
    // Monitor form visibility changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.id === 'new_customer_form' && mutation.attributeName === 'style') {
                const display = mutation.target.style.display;
                console.log(`🔍 Form display changed to: ${display}`);
            }
        });
    });
    
    const formElement = document.getElementById('new_customer_form');
    if (formElement) {
        observer.observe(formElement, { attributes: true, attributeFilter: ['style'] });
        console.log('🔍 Form observer attached');
    }
});

// Global test functions
window.debugShowForm = function() {
    console.log('🔍 Global debugShowForm called');
    $('#new_customer_form').slideDown(300);
};

window.debugHideForm = function() {
    console.log('🔍 Global debugHideForm called');
    $('#new_customer_form').slideUp(300);
};

window.debugCheckKTOrderAdd = function() {
    console.log('🔍 Checking KTOrderAdd:', typeof KTOrderAdd);
    if (typeof KTOrderAdd !== 'undefined') {
        console.log('🔍 KTOrderAdd methods:', Object.keys(KTOrderAdd));
    }
};

console.log('🔍 Debug script setup complete');
console.log('🔍 Available global functions: debugShowForm(), debugHideForm(), debugCheckKTOrderAdd()');
