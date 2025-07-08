"use strict";

// Class definition
var KTOrderAdd = function () {
    // Shared variables
    var form;
    var submitButton;
    var cancelButton;
    var validator;
    var orderItems=[];
    var customerData;
    var customerSelect;
    var productSelect;
    var initialDataLoaded = false;
    var loadingCustomers = false;
    var loadingProducts = false;
    var newCustomerMode = false;

    // Private functions

    // Load initial data (recent customers and popular products)
    var loadInitialData = function() {
        if (initialDataLoaded) return;

        console.log('Loading initial order data...');

        $.ajax({
            url: '/admin/order/initial-data',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    console.log('Initial data loaded successfully:', response.data);

                    // Pre-populate customer select with recent customers
                    if (response.data.recent_customers && response.data.recent_customers.length > 0) {
                        response.data.recent_customers.forEach(function(customer) {
                            var option = new Option(customer.text, customer.id, false, false);
                            customerSelect.append(option);
                        });
                        customerSelect.trigger('change');
                    }

                    // Pre-populate product select with popular products
                    if (response.data.popular_products && response.data.popular_products.length > 0) {
                        response.data.popular_products.forEach(function(product) {
                            var option = new Option(product.text, product.id, false, false);
                            $(option).data('price', product.price);
                            $(option).data('stock', product.stock);
                            $(option).data('name', product.name);
                            $(option).data('sku', product.sku);
                            productSelect.append(option);
                        });
                        productSelect.trigger('change');
                    }

                    initialDataLoaded = true;
                } else {
                    console.warn('Failed to load initial data:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading initial data:', error);
            }
        });
    };

    var initForm = function() {
        // Init form validation rules
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'customer_id': {
                        validators: {
                            notEmpty: {
                                message: 'Khách hàng là bắt buộc'
                            }
                        }
                    },

                    'channel': {
                        validators: {
                            notEmpty: {
                                message: 'Kênh bán hàng là bắt buộc'
                            }
                        }
                    },
                    'status': {
                        validators: {
                            notEmpty: {
                                message: 'Trạng thái là bắt buộc'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Validate order items
            if (orderItems.length === 0) {
                Swal.fire({
                    text: "Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
                return;
            }

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // Prepare form data
                        var formData = new FormData(form);

                        // Ensure customer_id is set
                        var customerId = $('#customer_id').val();
                        if (!customerId || customerId === 'new_customer') {
                            Swal.fire({
                                text: "Vui lòng chọn khách hàng hoặc tạo khách hàng mới.",
                                icon: "warning",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });

                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                            return;
                        }

                        // Add order items
                        formData.append('items', JSON.stringify(orderItems));

                        // Ensure customer_id is properly set
                        formData.set('customer_id', customerId);

                        $.ajax({
                            url: form.action,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;

                                if (response.success) {
                                    // Show success message
                                    Swal.fire({
                                        text: response.message || "Đơn hàng đã được tạo thành công!",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(function (result) {
                                        if (result.isConfirmed) {
                                            // Redirect to orders list
                                            window.location.href = "/admin/order";
                                        }
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi tạo đơn hàng.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });

                                    // Show validation errors if any
                                    if (response.errors) {
                                        for (let field in response.errors) {
                                            validator.updateFieldStatus(field, 'Invalid', 'remote');
                                        }
                                    }
                                }
                            },
                            error: function(xhr) {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;

                                let errorMessage = "Có lỗi xảy ra khi tạo đơn hàng.";
                                
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    // Handle validation errors
                                    let errors = xhr.responseJSON.errors;
                                    for (let field in errors) {
                                        validator.updateFieldStatus(field, 'Invalid', 'remote');
                                    }
                                    errorMessage = "Vui lòng kiểm tra lại thông tin đã nhập.";
                                }

                                Swal.fire({
                                    text: errorMessage,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });

        // Handle cancel button
        cancelButton.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                text: "Bạn có chắc chắn muốn hủy?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Có, hủy!",
                cancelButtonText: "Không",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    form.reset(); // Reset form
                    orderItems = []; // Clear order items
                    updateOrderItemsTable(); // Update table
                    window.location.href = "/admin/order"; // Redirect to orders list
                }
            });
        });
    }

    // Initialize customer select
    var initCustomerSelect = function() {
        // Destroy existing Select2 if it exists
        if ($('#customer_id').hasClass('select2-hidden-accessible')) {
            $('#customer_id').select2('destroy');
        }

        customerSelect = $('#customer_id').select2({
            placeholder: 'Tìm kiếm khách hàng...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '/admin/order/customers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    loadingCustomers = true;
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function (data) {
                    loadingCustomers = false;
                    if (data.success && data.data) {
                        return {
                            results: data.data.map(function(customer) {
                                return {
                                    id: customer.id,
                                    text: customer.display_text || (customer.name + ' - ' + customer.phone),
                                    name: customer.name,
                                    phone: customer.phone,
                                    email: customer.email,
                                    address: customer.address,
                                    customer_type: customer.customer_type
                                };
                            })
                        };
                    }
                    return { results: [] };
                },
                cache: true
            },
            templateResult: function(customer) {
                if (customer.loading) return customer.text;

                if (!customer.name) return customer.text;

                var $container = $(
                    '<div class="d-flex align-items-center">' +
                        '<div class="symbol symbol-30px me-3">' +
                            '<div class="symbol-label bg-light-primary text-primary fw-bold">' +
                                customer.name.charAt(0).toUpperCase() +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex flex-column">' +
                            '<span class="fw-bold">' + customer.name + '</span>' +
                            '<small class="text-muted">' + customer.phone + (customer.email ? ' • ' + customer.email : '') + '</small>' +
                        '</div>' +
                    '</div>'
                );

                return $container;
            },
            templateSelection: function(customer) {
                return customer.text || customer.name;
            }
        });

        // Handle customer selection change
        customerSelect.on('select2:select', function (e) {
            const data = e.params.data;
            console.log('Customer selected:', data);
            if (data.id === 'new_customer') {
                console.log('Showing new customer form...');
                showNewCustomerForm();
            } else {
                console.log('Hiding new customer form...');
                hideNewCustomerForm();
            }
        });

        // Handle customer unselect
        customerSelect.on('select2:unselect', function (e) {
            console.log('Customer unselected');
            hideNewCustomerForm();
        });

        // Load initial data after select is initialized
        setTimeout(function() {
            loadInitialData();
        }, 100);
    }



    // Initialize product select
    var initProductSelect = function() {
        productSelect = $('#product_search').select2({
            placeholder: 'Tìm kiếm sản phẩm để thêm vào đơn hàng...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '/admin/order/products',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    loadingProducts = true;
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function (data) {
                    loadingProducts = false;
                    if (data.success && data.data) {
                        return {
                            results: data.data.map(function(product) {
                                return {
                                    id: product.id,
                                    text: product.display_text || (product.name + ' - ' + product.sku + ' (Tồn: ' + (product.stock_quantity || 0) + ')'),
                                    price: product.price,
                                    stock: product.stock_quantity || 0,
                                    stock_status: product.stock_status,
                                    name: product.name,
                                    sku: product.sku,
                                    image: product.image,
                                    formatted_price: product.formatted_price
                                };
                            })
                        };
                    }
                    return { results: [] };
                },
                cache: true
            },
            templateResult: function(product) {
                if (product.loading) return product.text;

                if (!product.name) return product.text;

                var stockBadgeClass = 'badge-light-success';
                if (product.stock_status === 'low_stock') {
                    stockBadgeClass = 'badge-light-warning';
                } else if (product.stock_status === 'out_of_stock') {
                    stockBadgeClass = 'badge-light-danger';
                }

                var $container = $(
                    '<div class="d-flex align-items-center">' +
                        '<div class="symbol symbol-40px me-3">' +
                            (product.image ?
                                '<img src="' + product.image + '" alt="' + product.name + '" class="symbol-label">' :
                                '<div class="symbol-label bg-light-info text-info fw-bold">' + product.name.charAt(0).toUpperCase() + '</div>'
                            ) +
                        '</div>' +
                        '<div class="d-flex flex-column flex-grow-1">' +
                            '<div class="d-flex justify-content-between align-items-center">' +
                                '<span class="fw-bold">' + product.name + '</span>' +
                                '<span class="badge ' + stockBadgeClass + '">' + product.stock + '</span>' +
                            '</div>' +
                            '<div class="d-flex justify-content-between">' +
                                '<small class="text-muted">SKU: ' + product.sku + '</small>' +
                                '<small class="text-primary fw-bold">' + (product.formatted_price || formatCurrency(product.price)) + '</small>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );

                return $container;
            },
            templateSelection: function(product) {
                return product.text || product.name;
            }
        });

        // Handle product selection
        productSelect.on('select2:select', function (e) {
            const data = e.params.data;
            addProductToOrder(data);
            productSelect.val(null).trigger('change'); // Clear selection
        });
    }

    // Show new customer form
    var showNewCustomerForm = function() {
        console.log('showNewCustomerForm called');
        newCustomerMode = true;

        var $form = $('#new_customer_form');
        console.log('Form element found:', $form.length);

        if ($form.length > 0) {
            $form.slideDown(300, function() {
                console.log('Form slide down completed');
                $('#new_customer_name').focus();
            });
        } else {
            console.error('New customer form not found!');
        }
    };

    // Hide new customer form
    var hideNewCustomerForm = function() {
        console.log('hideNewCustomerForm called');
        newCustomerMode = false;

        var $form = $('#new_customer_form');
        if ($form.length > 0) {
            $form.slideUp(300, function() {
                console.log('Form slide up completed');
                clearNewCustomerForm();
            });
        }
    };

    // Clear new customer form
    var clearNewCustomerForm = function() {
        $('#new_customer_name').val('').removeClass('is-invalid');
        $('#new_customer_phone').val('').removeClass('is-invalid');
        $('#new_customer_email').val('').removeClass('is-invalid');
        $('#new_customer_address').val('');
        $('#new_customer_type').val('individual');

        // Clear error messages
        $('.invalid-feedback').text('');
    };

    // Validate new customer form
    var validateNewCustomerForm = function() {
        var isValid = true;
        var name = $('#new_customer_name').val().trim();
        var phone = $('#new_customer_phone').val().trim();
        var email = $('#new_customer_email').val().trim();

        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validate name
        if (!name || name.length < 2) {
            $('#new_customer_name').addClass('is-invalid');
            $('#new_customer_name_error').text('Tên khách hàng phải có ít nhất 2 ký tự');
            isValid = false;
        }

        // Validate phone
        if (!phone) {
            $('#new_customer_phone').addClass('is-invalid');
            $('#new_customer_phone_error').text('Số điện thoại là bắt buộc');
            isValid = false;
        } else if (!/^[0-9+\-\s\(\)]{10,15}$/.test(phone)) {
            $('#new_customer_phone').addClass('is-invalid');
            $('#new_customer_phone_error').text('Số điện thoại không hợp lệ');
            isValid = false;
        }

        // Validate email if provided
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#new_customer_email').addClass('is-invalid');
            $('#new_customer_email_error').text('Email không hợp lệ');
            isValid = false;
        }

        return isValid;
    };

    // Check if phone exists
    var checkPhoneExists = function(phone, callback) {
        $.ajax({
            url: '/admin/order/check-phone',
            type: 'GET',
            data: { phone: phone },
            success: function(response) {
                if (response.success) {
                    callback(response.exists, response.customer || null);
                } else {
                    callback(false, null);
                }
            },
            error: function() {
                callback(false, null);
            }
        });
    };

    // Create new customer
    var createNewCustomer = function() {
        if (!validateNewCustomerForm()) {
            return;
        }

        var customerData = {
            name: $('#new_customer_name').val().trim(),
            phone: $('#new_customer_phone').val().trim(),
            email: $('#new_customer_email').val().trim(),
            address: $('#new_customer_address').val().trim(),
            customer_type: $('#new_customer_type').val()
        };

        // Check if phone exists first
        checkPhoneExists(customerData.phone, function(exists, existingCustomer) {
            if (exists) {
                Swal.fire({
                    title: 'Số điện thoại đã tồn tại',
                    text: `Khách hàng "${existingCustomer.name}" đã sử dụng số điện thoại này. Bạn có muốn chọn khách hàng này không?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Chọn khách hàng này',
                    cancelButtonText: 'Hủy',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-light'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Select existing customer
                        var option = new Option(
                            existingCustomer.name + ' - ' + existingCustomer.phone,
                            existingCustomer.id,
                            true,
                            true
                        );
                        customerSelect.append(option).trigger('change');
                        hideNewCustomerForm();
                    }
                });
                return;
            }

            // Proceed with creating new customer
            var saveButton = $('#btn_save_new_customer');
            saveButton.attr('data-kt-indicator', 'on').prop('disabled', true);

            $.ajax({
                url: '/admin/order/create-customer',
                type: 'POST',
                data: customerData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    saveButton.removeAttr('data-kt-indicator').prop('disabled', false);

                    if (response.success) {
                        // Add new customer to select
                        var option = new Option(
                            response.data.display_text,
                            response.data.id,
                            true,
                            true
                        );
                        customerSelect.append(option).trigger('change');

                        // Hide form and show success message
                        hideNewCustomerForm();

                        Swal.fire({
                            text: response.message,
                            icon: 'success',
                            buttonsStyling: false,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            text: response.message,
                            icon: 'error',
                            buttonsStyling: false,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });

                        // Show field errors if any
                        if (response.errors) {
                            for (let field in response.errors) {
                                $('#new_customer_' + field).addClass('is-invalid');
                                $('#new_customer_' + field + '_error').text(response.errors[field]);
                            }
                        }
                    }
                },
                error: function(xhr) {
                    saveButton.removeAttr('data-kt-indicator').prop('disabled', false);

                    let errorMessage = 'Có lỗi xảy ra khi tạo khách hàng mới';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        text: errorMessage,
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        });
    };

    // Add product to order
    var addProductToOrder = function(product) {
        // Check stock availability
        if (product.stock <= 0) {
            Swal.fire({
                text: "Sản phẩm '" + product.name + "' đã hết hàng!",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return;
        }

        // Check if product already exists
        const existingIndex = orderItems.findIndex(item => item.product_id === product.id);

        if (existingIndex !== -1) {
            // Check if we can increase quantity
            if (orderItems[existingIndex].quantity >= product.stock) {
                Swal.fire({
                    text: "Không thể thêm. Tồn kho chỉ còn " + product.stock + " sản phẩm!",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
                return;
            }
            // Increase quantity
            orderItems[existingIndex].quantity += 1;
            orderItems[existingIndex].total_price = orderItems[existingIndex].quantity * orderItems[existingIndex].unit_price;
        } else {
            // Add new item
            orderItems.push({
                product_id: product.id,
                product_name: product.name || product.text,
                sku: product.sku || '',
                quantity: 1,
                unit_price: product.price || 0,
                total_price: product.price || 0,
                stock: product.stock || 0
            });
        }

        updateOrderItemsTable();
        calculateOrderTotal();
    }

    // Update order items table
    var updateOrderItemsTable = function() {
        const tbody = document.querySelector('#order_items_table tbody');
        
        if (orderItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Chưa có sản phẩm nào</td></tr>';
            return;
        }

        let html = '';
        orderItems.forEach((item, index) => {
            html += `
                <tr>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">${item.product_name}</span>
                            ${item.sku ? `<small class="text-muted">SKU: ${item.sku}</small>` : ''}
                        </div>
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center"
                               value="${item.quantity}" min="1" max="${item.stock}"
                               onchange="updateItemQuantity(${index}, this.value)">
                    </td>
                    <td class="text-end">${formatCurrency(item.unit_price)}</td>
                    <td class="text-end">${formatCurrency(item.total_price)}</td>
                    <td class="text-center">
                        <span class="badge ${item.stock > 10 ? 'badge-light-success' : item.stock > 0 ? 'badge-light-warning' : 'badge-light-danger'}">
                            ${item.stock}
                        </span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeOrderItem(${index})" title="Xóa sản phẩm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Calculate order total
    var calculateOrderTotal = function() {
        const subtotal = orderItems.reduce((sum, item) => parseFloat(sum) + parseFloat(item.total_price), 0);
        console.log(subtotal);
        const discount = parseFloat(document.querySelector('[name="discount_amount"]').value) || 0;
        const shipping = parseFloat(document.querySelector('[name="shipping_fee"]').value) || 0;
        const tax = parseFloat(document.querySelector('[name="tax_amount"]').value) || 0;
        
        const total = subtotal - discount + shipping + tax;

        // Update display
        document.querySelector('#subtotal_display').textContent = formatCurrency(subtotal);
        document.querySelector('#total_display').textContent = formatCurrency(total);
        
        // Update hidden fields
        document.querySelector('[name="subtotal_amount"]').value = subtotal;
        document.querySelector('[name="final_amount"]').value = total;
        document.querySelector('[name="total_quantity"]').value = orderItems.reduce((sum, item) => sum + item.quantity, 0);
    }

    // Format currency
    var formatCurrency = function(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Auto-generate order code
    var generateOrderCode = function() {
        const now = new Date();
        const year = now.getFullYear().toString().slice(-2);
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');

        return `ORD${year}${month}${day}${random}`;
    }

    // Show loading indicator
    var showLoading = function(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message || 'Đang tải dữ liệu...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    };

    // Hide loading indicator
    var hideLoading = function() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    };

    // Initialize new customer form events
    var initNewCustomerEvents = function() {
        console.log('Initializing new customer events...');

        // Add new customer button
        $('#btn_add_new_customer').on('click', function() {
            console.log('Add new customer button clicked');
            // Try both methods to ensure it works
            customerSelect.val('new_customer').trigger('change');
            showNewCustomerForm();
        });

        // Cancel new customer buttons
        $('#btn_cancel_new_customer, #btn_cancel_new_customer_form').on('click', function() {
            console.log('Cancel button clicked');
            customerSelect.val('').trigger('change');
            hideNewCustomerForm();
        });

        // Save new customer button
        $('#btn_save_new_customer').on('click', function() {
            console.log('Save new customer button clicked');
            createNewCustomer();
        });

        // Phone field blur event to check existing
        $('#new_customer_phone').on('blur', function() {
            var phone = $(this).val().trim();
            if (phone && phone.length >= 10) {
                checkPhoneExists(phone, function(exists, customer) {
                    if (exists) {
                        $('#new_customer_phone').addClass('is-invalid');
                        $('#new_customer_phone_error').text(`Số điện thoại đã được sử dụng bởi khách hàng: ${customer.name}`);
                    } else {
                        $('#new_customer_phone').removeClass('is-invalid');
                        $('#new_customer_phone_error').text('');
                    }
                });
            }
        });

        // Enter key handling in new customer form
        $('#new_customer_form input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                createNewCustomer();
            }
        });

        console.log('New customer events initialized');
    };

    // Public methods
    return {
        init: function () {
            // Elements
            form = document.querySelector('#kt_add_order_form');
            submitButton = document.querySelector('[data-kt-order-action="submit"]');
            cancelButton = document.querySelector('[data-kt-order-action="cancel"]');

            if (form) {
                // Show loading while initializing
                showLoading('Đang khởi tạo trang tạo đơn hàng...');

                initForm();
                initCustomerSelect();
                initProductSelect();

                // Generate order code
                const orderCodeField = form.querySelector('[name="order_code"]');
                if (orderCodeField && !orderCodeField.value) {
                    orderCodeField.value = generateOrderCode();
                }

                // Initialize calculation events
                const discountField = form.querySelector('[name="discount_amount"]');
                const shippingField = form.querySelector('[name="shipping_fee"]');
                const taxField = form.querySelector('[name="tax_amount"]');

                [discountField, shippingField, taxField].forEach(field => {
                    if (field) {
                        field.addEventListener('input', calculateOrderTotal);
                    }
                });

                // Initialize new customer form events
                initNewCustomerEvents();

                // Hide loading after initialization
                setTimeout(function() {
                    hideLoading();
                }, 1000);
            }
        },

        // Expose public methods and data
        orderItems: orderItems,
        updateOrderItemsTable: updateOrderItemsTable,
        calculateOrderTotal: calculateOrderTotal,
        loadInitialData: loadInitialData,
        showLoading: showLoading,
        hideLoading: hideLoading,
        showNewCustomerForm: showNewCustomerForm,
        hideNewCustomerForm: hideNewCustomerForm,
        createNewCustomer: createNewCustomer
    };
}();

// Global functions for order items management
window.updateItemQuantity = function(index, quantity) {
    quantity = parseInt(quantity);
    if (quantity > 0 && quantity <= KTOrderAdd.orderItems[index].stock) {
        KTOrderAdd.orderItems[index].quantity = quantity;
        KTOrderAdd.orderItems[index].total_price = quantity * KTOrderAdd.orderItems[index].unit_price;
        KTOrderAdd.updateOrderItemsTable();
        KTOrderAdd.calculateOrderTotal();
    }
};

window.removeOrderItem = function(index) {
    KTOrderAdd.orderItems.splice(index, 1);
    KTOrderAdd.updateOrderItemsTable();
    KTOrderAdd.calculateOrderTotal();
};

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTOrderAdd.init();
});
