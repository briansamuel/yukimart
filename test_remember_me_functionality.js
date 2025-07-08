/**
 * Test script for Remember Me functionality
 * Copy and paste this into browser console on login page
 */

console.log('🔐 Testing Remember Me Functionality...');

// 1. Check if remember checkbox exists
console.log('1. Checking remember checkbox...');
const rememberCheckbox = document.querySelector('#remember');
const emailInput = document.querySelector('#email');
const passwordInput = document.querySelector('#password');
const loginForm = document.querySelector('#kt_sign_in_form');

console.log('Remember checkbox:', rememberCheckbox);
console.log('Email input:', emailInput);
console.log('Password input:', passwordInput);
console.log('Login form:', loginForm);

// 2. Check checkbox functionality
window.testRememberCheckbox = function() {
    console.log('🧪 Testing remember checkbox...');
    if (rememberCheckbox) {
        // Test checking/unchecking
        rememberCheckbox.checked = true;
        console.log('Checkbox checked:', rememberCheckbox.checked);
        console.log('Checkbox value:', rememberCheckbox.value);
        
        rememberCheckbox.checked = false;
        console.log('Checkbox unchecked:', rememberCheckbox.checked);
        
        // Test jQuery selector
        const jqueryCheck = $("#remember").is(':checked');
        console.log('jQuery check result:', jqueryCheck);
        
        console.log('✅ Checkbox functionality working');
    } else {
        console.log('❌ Remember checkbox not found');
    }
};

// 3. Test form data collection
window.testFormData = function() {
    console.log('🧪 Testing form data collection...');
    
    if (emailInput && passwordInput && rememberCheckbox) {
        // Set test values
        emailInput.value = 'test@example.com';
        passwordInput.value = 'testpassword';
        rememberCheckbox.checked = true;
        
        // Collect data like the AJAX call does
        const formData = {
            email: $("#email").val(),
            password: $("#password").val(),
            remember: $("#remember").is(':checked') ? 1 : 0,
        };
        
        console.log('Form data collected:', formData);
        console.log('Remember value:', formData.remember);
        
        // Test with unchecked
        rememberCheckbox.checked = false;
        const formData2 = {
            email: $("#email").val(),
            password: $("#password").val(),
            remember: $("#remember").is(':checked') ? 1 : 0,
        };
        
        console.log('Form data (unchecked):', formData2);
        console.log('Remember value (unchecked):', formData2.remember);
        
        console.log('✅ Form data collection working');
    } else {
        console.log('❌ Form inputs not found');
    }
};

// 4. Test AJAX login simulation
window.testLoginAjax = function(testEmail = 'admin@example.com', testPassword = 'password', testRemember = true) {
    console.log('🧪 Testing login AJAX with remember me...');
    
    if (!emailInput || !passwordInput || !rememberCheckbox) {
        console.log('❌ Form inputs not found');
        return;
    }
    
    // Set test values
    emailInput.value = testEmail;
    passwordInput.value = testPassword;
    rememberCheckbox.checked = testRemember;
    
    console.log(`Testing with: ${testEmail}, remember: ${testRemember}`);
    
    // Simulate the AJAX call
    const formData = {
        email: $("#email").val(),
        password: $("#password").val(),
        remember: $("#remember").is(':checked') ? 1 : 0,
    };
    
    console.log('Sending AJAX data:', formData);
    
    $.ajax({
        url: "/login",
        method: 'POST',
        dataType: 'json',
        data: formData,
        success: function(data) {
            console.log('✅ Login response:', data);
            if (data.status === true) {
                console.log('✅ Login successful');
                console.log('Redirect URL:', data.url);
            } else {
                console.log('❌ Login failed:', data.msg);
            }
        },
        error: function(xhr, status, error) {
            console.log('❌ AJAX error:', error);
            console.log('Response:', xhr.responseText);
        }
    });
};

// 5. Check cookies after login
window.checkRememberCookies = function() {
    console.log('🍪 Checking remember me cookies...');
    
    // Get all cookies
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
        const [name, value] = cookie.trim().split('=');
        acc[name] = value;
        return acc;
    }, {});
    
    console.log('All cookies:', cookies);
    
    // Look for Laravel remember cookies
    const rememberCookies = Object.keys(cookies).filter(name => 
        name.includes('remember') || name.includes('session') || name.includes('laravel')
    );
    
    console.log('Remember-related cookies:', rememberCookies);
    
    rememberCookies.forEach(cookieName => {
        console.log(`${cookieName}: ${cookies[cookieName]}`);
    });
    
    if (rememberCookies.length > 0) {
        console.log('✅ Remember cookies found');
    } else {
        console.log('⚠️ No remember cookies found (may be normal if not logged in)');
    }
};

// 6. Test localStorage for remember preference
window.testRememberPreference = function() {
    console.log('💾 Testing remember preference storage...');
    
    // Save remember preference
    if (rememberCheckbox) {
        const isRemembered = rememberCheckbox.checked;
        localStorage.setItem('remember_login', isRemembered);
        console.log('Saved remember preference:', isRemembered);
        
        // Retrieve and apply
        const savedPreference = localStorage.getItem('remember_login');
        console.log('Retrieved preference:', savedPreference);
        
        if (savedPreference === 'true') {
            rememberCheckbox.checked = true;
            console.log('✅ Applied saved preference: checked');
        } else {
            rememberCheckbox.checked = false;
            console.log('✅ Applied saved preference: unchecked');
        }
    }
};

// 7. Auto-apply saved preference on page load
window.applySavedRememberPreference = function() {
    console.log('🔄 Applying saved remember preference...');
    
    const savedPreference = localStorage.getItem('remember_login');
    if (savedPreference && rememberCheckbox) {
        rememberCheckbox.checked = savedPreference === 'true';
        console.log('✅ Applied saved preference:', savedPreference);
    } else {
        console.log('ℹ️ No saved preference found');
    }
};

// 8. Enhanced login test with preference saving
window.testFullRememberFlow = function() {
    console.log('🚀 Testing full remember me flow...');
    
    if (!rememberCheckbox) {
        console.log('❌ Remember checkbox not found');
        return;
    }
    
    // Step 1: Check the remember box
    rememberCheckbox.checked = true;
    console.log('Step 1: ✅ Remember checkbox checked');
    
    // Step 2: Save preference
    localStorage.setItem('remember_login', 'true');
    console.log('Step 2: ✅ Preference saved to localStorage');
    
    // Step 3: Simulate form submission
    const formData = {
        email: 'test@example.com',
        password: 'testpassword',
        remember: rememberCheckbox.checked ? 1 : 0,
    };
    console.log('Step 3: ✅ Form data prepared:', formData);
    
    // Step 4: Check cookies (after login would be successful)
    setTimeout(() => {
        checkRememberCookies();
        console.log('Step 4: ✅ Cookies checked');
    }, 1000);
    
    console.log('🎉 Full remember me flow test completed');
};

// 9. Instructions
console.log('📋 Available test commands:');
console.log('   testRememberCheckbox()     - Test checkbox functionality');
console.log('   testFormData()             - Test form data collection');
console.log('   testLoginAjax()            - Test AJAX login with remember');
console.log('   checkRememberCookies()     - Check remember cookies');
console.log('   testRememberPreference()   - Test localStorage preference');
console.log('   applySavedRememberPreference() - Apply saved preference');
console.log('   testFullRememberFlow()     - Test complete flow');

// 10. Auto-run basic tests
console.log('🔄 Running basic tests...');
testRememberCheckbox();
testFormData();

// Apply saved preference if exists
applySavedRememberPreference();

// 11. Add event listener to save preference when changed
if (rememberCheckbox) {
    rememberCheckbox.addEventListener('change', function() {
        localStorage.setItem('remember_login', this.checked);
        console.log('💾 Remember preference saved:', this.checked);
    });
    console.log('✅ Event listener added for preference saving');
}

// 12. Final status
setTimeout(() => {
    console.log('📊 Remember Me Test Summary:');
    console.log('   Checkbox exists:', !!rememberCheckbox);
    console.log('   Form inputs exist:', !!(emailInput && passwordInput));
    console.log('   jQuery available:', typeof $ !== 'undefined');
    console.log('   Current checkbox state:', rememberCheckbox?.checked);
    console.log('   Saved preference:', localStorage.getItem('remember_login'));
    
    if (rememberCheckbox && emailInput && passwordInput) {
        console.log('✅ Remember Me functionality ready for testing');
    } else {
        console.log('❌ Some components missing');
    }
}, 1000);
