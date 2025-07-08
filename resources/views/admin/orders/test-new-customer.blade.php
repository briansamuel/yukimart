<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test New Customer Feature</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .console-output {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">🧪 Test New Customer Feature</h1>
        
        <!-- Test Section 1: Customer Dropdown -->
        <div class="test-section">
            <h3>1. Customer Dropdown Test</h3>
            <div class="row">
                <div class="col-md-10">
                    <label class="form-label">Customer Selection:</label>
                    <select id="customer_id" class="form-select" data-control="select2">
                        <option value="">Select Customer</option>
                        <option value="new_customer">New Customer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100" id="btn_add_new_customer">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Test Section 2: New Customer Form -->
        <div class="test-section">
            <h3>2. New Customer Form Test</h3>
            <div id="new_customer_form" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">New Customer Information</h5>
                        <button type="button" class="btn btn-sm btn-light" id="btn_cancel_new_customer">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="new_customer_name" placeholder="Enter customer name">
                                <div class="invalid-feedback" id="new_customer_name_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" class="form-control" id="new_customer_phone" placeholder="Enter phone number">
                                <div class="invalid-feedback" id="new_customer_phone_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="new_customer_email" placeholder="Enter email (optional)">
                                <div class="invalid-feedback" id="new_customer_email_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer Type</label>
                                <select class="form-select" id="new_customer_type">
                                    <option value="individual">Individual</option>
                                    <option value="business">Business</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" id="new_customer_address" rows="2" placeholder="Enter address (optional)"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-light me-2" id="btn_cancel_new_customer_form">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btn_save_new_customer">
                                <span class="indicator-label">Create Customer</span>
                                <span class="indicator-progress">Processing...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Section 3: API Test -->
        <div class="test-section">
            <h3>3. API Endpoints Test</h3>
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-info w-100" onclick="testCreateCustomerAPI()">
                        Test Create Customer API
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-info w-100" onclick="testCheckPhoneAPI()">
                        Test Check Phone API
                    </button>
                </div>
            </div>
        </div>

        <!-- Test Section 4: Console Output -->
        <div class="test-section">
            <h3>4. Console Output</h3>
            <div class="console-output" id="console-output">
                Console output will appear here...
            </div>
            <button type="button" class="btn btn-secondary mt-2" onclick="clearConsole()">Clear Console</button>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Console override to capture output
        const originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn
        };

        const consoleOutput = document.getElementById('console-output');

        function addToConsole(message, type = 'log') {
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff6b6b' : type === 'warn' ? '#feca57' : '#0f0';
            consoleOutput.innerHTML += `<div style="color: ${color}">[${timestamp}] ${message}</div>`;
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }

        console.log = function(...args) {
            originalConsole.log(...args);
            addToConsole(args.join(' '), 'log');
        };

        console.error = function(...args) {
            originalConsole.error(...args);
            addToConsole(args.join(' '), 'error');
        };

        console.warn = function(...args) {
            originalConsole.warn(...args);
            addToConsole(args.join(' '), 'warn');
        };

        function clearConsole() {
            consoleOutput.innerHTML = '';
        }

        // Test functions
        function testCreateCustomerAPI() {
            console.log('Testing Create Customer API...');
            $.ajax({
                url: '/admin/order/create-customer',
                type: 'POST',
                data: {
                    name: 'Test Customer',
                    phone: '0901234567',
                    email: 'test@example.com'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('✅ Create Customer API Success:', JSON.stringify(response));
                },
                error: function(xhr) {
                    console.error('❌ Create Customer API Error:', xhr.status, xhr.responseText);
                }
            });
        }

        function testCheckPhoneAPI() {
            console.log('Testing Check Phone API...');
            $.ajax({
                url: '/admin/order/check-phone',
                type: 'GET',
                data: { phone: '0901234567' },
                success: function(response) {
                    console.log('✅ Check Phone API Success:', JSON.stringify(response));
                },
                error: function(xhr) {
                    console.error('❌ Check Phone API Error:', xhr.status, xhr.responseText);
                }
            });
        }

        // Initialize when document ready
        $(document).ready(function() {
            console.log('🚀 Initializing New Customer Test Page...');
            
            // Initialize Select2
            $('#customer_id').select2({
                placeholder: 'Select customer...',
                allowClear: true
            });

            console.log('✅ Test page initialized successfully!');
        });
    </script>

    <!-- Load the test script -->
    <script src="{{ asset('admin-assets/assets/js/test-new-customer.js') }}"></script>
</body>
</html>
