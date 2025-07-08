<!DOCTYPE html>
<html>
<head>
    <title>Order Routes Debug</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .result { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Order Routes Debug Page</h1>
    
    <div class="test-section">
        <h2>1. Route Information</h2>
        <button onclick="testRouteInfo()">Check Route Registration</button>
        <div id="route-info-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>2. Direct API Tests</h2>
        <button onclick="testCustomersAPI()">Test Customers API</button>
        <button onclick="testProductsAPI()">Test Products API</button>
        <button onclick="testInitialDataAPI()">Test Initial Data API</button>
        <div id="api-test-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>3. Service Method Tests</h2>
        <button onclick="testServiceMethods()">Test Service Methods</button>
        <div id="service-test-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>4. Direct Controller Tests</h2>
        <button onclick="testDirectCustomers()">Test Direct Customers</button>
        <button onclick="testDirectProducts()">Test Direct Products</button>
        <div id="direct-test-result" class="result"></div>
    </div>

    <script>
        // Set up CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function showResult(elementId, content, type = 'info') {
            const element = document.getElementById(elementId);
            element.innerHTML = `<pre class="${type}">${content}</pre>`;
        }
        
        function testRouteInfo() {
            fetch('/admin/debug-order-routes')
                .then(response => response.json())
                .then(data => {
                    showResult('route-info-result', JSON.stringify(data, null, 2), 'success');
                })
                .catch(error => {
                    showResult('route-info-result', 'Error: ' + error.message, 'error');
                });
        }
        
        function testCustomersAPI() {
            fetch('/admin/order/customers', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 302) {
                        showResult('api-test-result', 'Status: 302 Found (Redirect)\nThis indicates authentication redirect or route conflict', 'warning');
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        showResult('api-test-result', 'Customers API Response:\n' + JSON.stringify(data, null, 2), 'success');
                    }
                })
                .catch(error => {
                    showResult('api-test-result', 'Customers API Error: ' + error.message, 'error');
                });
        }
        
        function testProductsAPI() {
            fetch('/admin/order/products', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 302) {
                        showResult('api-test-result', 'Status: 302 Found (Redirect)\nThis indicates authentication redirect or route conflict', 'warning');
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        showResult('api-test-result', 'Products API Response:\n' + JSON.stringify(data, null, 2), 'success');
                    }
                })
                .catch(error => {
                    showResult('api-test-result', 'Products API Error: ' + error.message, 'error');
                });
        }
        
        function testInitialDataAPI() {
            fetch('/admin/order/initial-data', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 302) {
                        showResult('api-test-result', 'Status: 302 Found (Redirect)\nThis indicates authentication redirect or route conflict', 'warning');
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        showResult('api-test-result', 'Initial Data API Response:\n' + JSON.stringify(data, null, 2), 'success');
                    }
                })
                .catch(error => {
                    showResult('api-test-result', 'Initial Data API Error: ' + error.message, 'error');
                });
        }
        
        function testServiceMethods() {
            fetch('/admin/test-order-api')
                .then(response => response.json())
                .then(data => {
                    showResult('service-test-result', 'Service Methods Test:\n' + JSON.stringify(data, null, 2), 'success');
                })
                .catch(error => {
                    showResult('service-test-result', 'Service Methods Error: ' + error.message, 'error');
                });
        }
        
        function testDirectCustomers() {
            fetch('/admin/direct-test-customers')
                .then(response => response.json())
                .then(data => {
                    showResult('direct-test-result', 'Direct Customers Test:\n' + JSON.stringify(data, null, 2), 'success');
                })
                .catch(error => {
                    showResult('direct-test-result', 'Direct Customers Error: ' + error.message, 'error');
                });
        }
        
        function testDirectProducts() {
            fetch('/admin/direct-test-products')
                .then(response => response.json())
                .then(data => {
                    showResult('direct-test-result', 'Direct Products Test:\n' + JSON.stringify(data, null, 2), 'success');
                })
                .catch(error => {
                    showResult('direct-test-result', 'Direct Products Error: ' + error.message, 'error');
                });
        }
        
        // Auto-run route info test on page load
        document.addEventListener('DOMContentLoaded', function() {
            testRouteInfo();
        });
    </script>
</body>
</html>
