console.log('Test script loaded successfully');

// Simple test function
var TestCustomers = function() {
    return {
        init: function() {
            console.log('TestCustomers initialized');
        }
    };
}();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    TestCustomers.init();
});
