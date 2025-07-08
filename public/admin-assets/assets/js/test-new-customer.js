// Test script for new customer functionality
console.log('🧪 Testing New Customer JavaScript Functions...');

// Test 1: Check if KTOrderAdd is defined
if (typeof KTOrderAdd !== 'undefined') {
    console.log('✅ KTOrderAdd object is defined');
    
    // Test 2: Check if required methods exist
    const requiredMethods = [
        'showNewCustomerForm',
        'hideNewCustomerForm', 
        'createNewCustomer'
    ];
    
    requiredMethods.forEach(method => {
        if (typeof KTOrderAdd[method] === 'function') {
            console.log(`✅ ${method} method exists`);
        } else {
            console.error(`❌ ${method} method is missing`);
        }
    });
    
} else {
    console.error('❌ KTOrderAdd object is not defined');
}

// Test 3: Check if jQuery is available
if (typeof $ !== 'undefined') {
    console.log('✅ jQuery is available');
    
    // Test 4: Check if required DOM elements exist when page loads
    $(document).ready(function() {
        const requiredElements = [
            '#customer_id',
            '#btn_add_new_customer',
            '#new_customer_form',
            '#new_customer_name',
            '#new_customer_phone',
            '#new_customer_email',
            '#btn_save_new_customer'
        ];
        
        requiredElements.forEach(selector => {
            if ($(selector).length > 0) {
                console.log(`✅ Element ${selector} exists`);
            } else {
                console.warn(`⚠️  Element ${selector} not found`);
            }
        });
    });
    
} else {
    console.error('❌ jQuery is not available');
}

// Test 5: Check if SweetAlert2 is available
if (typeof Swal !== 'undefined') {
    console.log('✅ SweetAlert2 is available');
} else {
    console.warn('⚠️  SweetAlert2 is not available');
}

// Test 6: Test API endpoints availability
const testApiEndpoints = function() {
    console.log('🔗 Testing API endpoints...');
    
    // Test create customer endpoint
    $.ajax({
        url: '/admin/order/create-customer',
        type: 'POST',
        data: { test: true },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('✅ Create customer endpoint is accessible');
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                console.log('✅ Create customer endpoint is accessible (validation error expected)');
            } else {
                console.warn('⚠️  Create customer endpoint error:', xhr.status);
            }
        }
    });
    
    // Test check phone endpoint
    $.ajax({
        url: '/admin/order/check-phone',
        type: 'GET',
        data: { phone: '0901234567' },
        success: function() {
            console.log('✅ Check phone endpoint is accessible');
        },
        error: function(xhr) {
            console.warn('⚠️  Check phone endpoint error:', xhr.status);
        }
    });
};

// Run API tests when jQuery is ready
$(document).ready(function() {
    setTimeout(testApiEndpoints, 1000);
});

console.log('🎉 New Customer JavaScript Test Completed!');
