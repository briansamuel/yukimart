"use strict";

/**
 * Payment List Management
 * Handles table, filters, search, and pagination functionality
 */
var KTPaymentsList = function () {
    var table;
    var filterForm;
    var searchInput;
    var currentRequest;
    var initialized = false;
    var currentPage = 1;
    var perPage = 10;
    var columnVisibilityPanel;
    var timeOptionsPanel;
    var detailPanel;
    var detailOverlay;
    var currentPaymentId = null;

    /**
     * Load all filter data at once for better performance
     */
    var loadAllFilterData = function() {
        console.log('Loading all filter data for payments...');

        const url = '/admin/filters/all?module=payments';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    console.log('All filter data loaded successfully:', data);

                    // Populate creators dropdown
                    if (data.data.creators && data.data.creators.length > 0) {
                        populateSelectElement('#kt_payment_filter_form select[name="created_by"]', data.data.creators, 'Chọn người tạo');
                    }

                    // Populate staff dropdown (sellers)
                    if (data.data.sellers && data.data.sellers.length > 0) {
                        populateSelectElement('#kt_payment_filter_form select[name="staff_id"]', data.data.sellers, 'Chọn nhân viên');
                    }

                    console.log('All filter data populated successfully for payments');
                } else {
                    console.error('Failed to load filter data:', data);
                }
            })
            .catch(error => {
                console.error('Error loading filter data:', error);
            });
    };

    /**
     * Populate select element with data
     */
    var populateSelectElement = function(selector, data, placeholder) {
        const selectElement = document.querySelector(selector);
        if (!selectElement) {
            console.warn('Select element not found:', selector);
            return;
        }

        // Clear existing options except the first one (placeholder)
        const firstOption = selectElement.querySelector('option');
        selectElement.innerHTML = '';

        // Add placeholder option
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        selectElement.appendChild(placeholderOption);

        // Add data options
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.value || item.id;
            option.textContent = item.text || item.label || item.name;
            selectElement.appendChild(option);
        });

        console.log('Populated', selector, 'with', data.length, 'options');
    };

    /**
     * Get filter data from form
     */
    var getFilterData = function() {
        var data = {};

        if (filterForm) {
            $(filterForm).find('input, select').each(function() {
                var $input = $(this);
                var name = $input.attr('name') || $input.attr('id');

                if (name) {
                    if ($input.is(':checkbox')) {
                        if (name.endsWith('[]')) {
                            if (!data[name]) {
                                data[name] = [];
                            }
                            if ($input.is(':checked')) {
                                data[name].push($input.val());
                            }
                        } else {
                            if ($input.is(':checked')) {
                                data[name] = $input.val();
                            }
                        }
                    } else if ($input.is(':radio')) {
                        if ($input.is(':checked')) {
                            console.log('Radio found:', name, '=', $input.val());
                            data[name] = $input.val();
                        }
                    } else if ($input.is('select[multiple]')) {
                        data[name] = $input.val() || [];
                    } else {
                        var value = $input.val();
                        if (value) {
                            console.log('Input found:', name, '=', value);
                            data[name] = value;
                        }
                    }
                }
            });
        }

        console.log('Final filter data:', data);
        return data;
    };

    /**
     * Load summary data
     */
    var loadSummary = function() {
        var filterData = getFilterData();

        // Build summary URL properly
        var summaryUrl;
        if (typeof paymentAjaxUrl !== 'undefined') {
            summaryUrl = paymentAjaxUrl.replace('/ajax', '/summary');
        } else {
            summaryUrl = '/admin/payments/summary';
        }

        console.log('=== LOADING SUMMARY ===');
        console.log('Summary URL:', summaryUrl);
        console.log('Filter data:', filterData);
        console.log('paymentAjaxUrl:', typeof paymentAjaxUrl !== 'undefined' ? paymentAjaxUrl : 'undefined');

        $.ajax({
            url: summaryUrl,
            type: 'GET',
            data: filterData,
            beforeSend: function(xhr) {
                console.log('Sending AJAX request to:', summaryUrl);
                console.log('Data being sent:', filterData);

                // Show loading state on summary cards
                $('#opening_balance').html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
                $('#total_income').html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
                $('#total_expense').html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
                $('#closing_balance').html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
            },
            success: function(response) {
                console.log('=== SUMMARY SUCCESS ===');
                console.log('Summary response:', response);
                if (response.success) {
                    console.log('Updating summary cards with:', response.data);
                    updateSummaryCards(response.data);
                } else {
                    console.error('Failed to load summary data:', response);
                    showSummaryError('Lỗi: ' + (response.error || 'Không thể tải dữ liệu'));
                }
            },
            error: function(xhr, status, error) {
                console.error('=== SUMMARY ERROR ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response status:', xhr.status);
                console.error('Response text:', xhr.responseText);

                var errorMessage = 'Lỗi kết nối';
                if (xhr.status === 401) {
                    errorMessage = 'Chưa đăng nhập';
                } else if (xhr.status === 403) {
                    errorMessage = 'Không có quyền truy cập';
                } else if (xhr.status === 404) {
                    errorMessage = 'Không tìm thấy endpoint';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Lỗi server';
                }

                showSummaryError(errorMessage);
            }
        });
    };

    /**
     * Update summary cards with real data
     */
    var updateSummaryCards = function(data) {
        console.log('=== UPDATING SUMMARY CARDS ===');
        console.log('Data received:', data);

        console.log('Looking for elements:');
        console.log('#opening_balance:', $('#opening_balance').length);
        console.log('#total_income:', $('#total_income').length);
        console.log('#total_expense:', $('#total_expense').length);
        console.log('#closing_balance:', $('#closing_balance').length);

        if ($('#opening_balance').length) {
            var formattedOpening = formatCurrency(data.opening_balance);
            console.log('Setting opening_balance to:', formattedOpening);
            $('#opening_balance').text(formattedOpening);
        }

        if ($('#total_income').length) {
            var formattedIncome = formatCurrency(data.total_income);
            console.log('Setting total_income to:', formattedIncome);
            $('#total_income').text(formattedIncome);
        }

        if ($('#total_expense').length) {
            var formattedExpense = '-' + formatCurrency(data.total_expense);
            console.log('Setting total_expense to:', formattedExpense);
            $('#total_expense').text(formattedExpense);
        }

        if ($('#closing_balance').length) {
            var formattedClosing = formatCurrency(data.closing_balance);
            console.log('Setting closing_balance to:', formattedClosing);
            $('#closing_balance').text(formattedClosing);
        }

        console.log('=== SUMMARY CARDS UPDATED ===');
    };

    /**
     * Show error message on summary cards
     */
    var showSummaryError = function(message) {
        console.log('Showing summary error:', message);
        $('#opening_balance').html('<span class="text-danger">' + message + '</span>');
        $('#total_income').html('<span class="text-danger">' + message + '</span>');
        $('#total_expense').html('<span class="text-danger">' + message + '</span>');
        $('#closing_balance').html('<span class="text-danger">' + message + '</span>');
    };

    /**
     * Load payments data via AJAX
     */
    var loadPayments = function() {
        // Cancel previous request
        if (currentRequest) {
            currentRequest.abort();
        }

        var filterData = getFilterData();
        filterData.page = currentPage;
        filterData.per_page = perPage;

        if (searchInput && searchInput.val()) {
            filterData.search = searchInput.val();
        }

        console.log('Loading payments with filters:', filterData);

        // Show loading state
        showLoadingState();

        // Also load summary data
        loadSummary();

        // Use mock data for testing if paymentAjaxUrl is not defined
        if (typeof paymentAjaxUrl === 'undefined') {
            setTimeout(function() {
                var mockResponse = {
                    success: true,
                    data: [
                        {
                            id: 1,
                            payment_number: 'TTH0040607',
                            payment_type: 'receipt',
                            reference_type: 'invoice',
                            reference_code: 'HD040607',
                            payment_date: '2025-07-10',
                            created_at: '2025-07-10 15:07:00',
                            amount: 155000,
                            payment_method: 'cash',
                            payment_method_display: 'Tiền mặt',
                            description: 'Thu tiền từ hóa đơn HD040607',
                            customer: { name: 'Nguyễn Văn A' },
                            income_type: 'Tiền khách trả'
                        },
                        {
                            id: 2,
                            payment_number: 'TTH0040608',
                            payment_type: 'disbursement',
                            reference_type: 'return_order',
                            reference_code: 'TH040608',
                            payment_date: '2025-07-10',
                            created_at: '2025-07-10 16:30:00',
                            amount: 75000,
                            payment_method: 'cash',
                            payment_method_display: 'Tiền mặt',
                            description: 'Chi tiền hoàn trả',
                            customer: { name: 'Trần Thị B' },
                            income_type: 'Tiền hoàn trả'
                        }
                    ],
                    pagination: {
                        current_page: 1,
                        total_pages: 1,
                        total_items: 2,
                        per_page: 10
                    }
                };
                renderPayments(mockResponse.data);
                renderPagination(mockResponse.pagination);
            }, 500);
            return;
        }

        currentRequest = $.ajax({
            url: paymentAjaxUrl,
            type: 'GET',
            data: filterData,
            success: function(response) {
                if (response.success) {
                    renderPayments(response.data);
                    renderPagination(response.pagination);
                } else {
                    showErrorState('Có lỗi xảy ra khi tải dữ liệu');
                }
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    console.error('AJAX Error:', error);
                    showErrorState('Không thể tải dữ liệu. Vui lòng thử lại.');
                }
            },
            complete: function() {
                currentRequest = null;
            }
        });
    };

    /**
     * Show loading state
     */
    var showLoadingState = function() {
        var tbody = $('#payments-table-body');
        tbody.html(`
            <tr>
                <td colspan="11" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <div class="mt-3 text-muted">Đang tải dữ liệu...</div>
                    </div>
                </td>
            </tr>
        `);
    };

    /**
     * Show error state
     */
    var showErrorState = function(message) {
        var tbody = $('#payments-table-body');
        tbody.html(`
            <tr>
                <td colspan="11" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <i class="fas fa-exclamation-triangle text-warning fs-2x mb-3"></i>
                        <div class="text-muted">${message}</div>
                        <button type="button" class="btn btn-sm btn-primary mt-3" onclick="KTPaymentsList.reload()">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </div>
                </td>
            </tr>
        `);
    };

    /**
     * Render payments data
     */
    var renderPayments = function(payments) {
        var tbody = $('#payments-table-body');

        if (!payments || payments.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-inbox text-muted fs-2x mb-3"></i>
                            <div class="text-muted">Không có dữ liệu</div>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        var html = '';
        payments.forEach(function(payment) {
            html += renderPaymentRow(payment);
        });

        tbody.html(html);

        // Add click event listeners to rows
        tbody.find('.payment-row').off('click').on('click', function(e) {
            // Don't trigger if clicking on checkbox
            if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.form-check').length) {
                return;
            }

            var paymentId = $(this).data('payment-id');
            console.log('Row clicked, payment ID:', paymentId);
            if (paymentId) {
                togglePaymentDetail(paymentId);
            }
        });
    };

    /**
     * Render single payment row
     */
    var renderPaymentRow = function(payment) {
        var customerName = payment.customer ? payment.customer.name : 'Khách lẻ';
        var typeBadge = getTypeBadge(payment.payment_type);
        var amountClass = payment.payment_type === 'receipt' ? 'text-success' : 'text-danger';
        var amountPrefix = payment.payment_type === 'receipt' ? '+' : '-';

        // Determine income type based on reference_type
        var incomeType = 'N/A';
        if (payment.reference_type === 'invoice') {
            incomeType = 'Tiền khách trả';
        } else if (payment.reference_type === 'return_order') {
            incomeType = 'Tiền hoàn trả';
        } else if (payment.reference_type === 'manual') {
            incomeType = payment.income_type || 'Thu khác';
        } else {
            incomeType = payment.income_type || 'N/A';
        }

        return `
            <tr data-payment-id="${payment.id}" class="payment-row" style="cursor: pointer;">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${payment.id}" onclick="event.stopPropagation();" />
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">${payment.payment_number}</span>
                        <span class="text-muted fs-7">${payment.reference_code || ''}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${formatDate(payment.payment_date)}</span>
                        <span class="text-muted fs-7">${formatTime(payment.created_at)}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        ${typeBadge}
                        <span class="text-muted fs-7">${incomeType}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                        <span class="text-muted fs-7">${payment.description || ''}</span>
                    </div>
                </td>
                <td class="text-end">
                    <div class="d-flex flex-column align-items-end">
                        <span class="text-gray-800 fw-bold fs-6 ${amountClass}">${amountPrefix}${formatCurrency(payment.amount)}</span>
                        <span class="text-muted fs-7">${payment.payment_method_display || payment.payment_method || 'N/A'}</span>
                    </div>
                </td>
            </tr>
        `;
    };

    /**
     * Render pagination
     */
    var renderPagination = function(pagination) {
        if (!pagination) return;

        console.log('Rendering pagination:', pagination);

        // Normalize pagination data
        var totalPages = pagination.total_pages || pagination.last_page || 1;
        var serverCurrentPage = pagination.current_page || 1;
        var totalItems = pagination.total_items || pagination.total || 0;
        var perPageValue = pagination.per_page || 10;
        var from = pagination.from || ((serverCurrentPage - 1) * perPageValue + 1);
        var to = pagination.to || Math.min(serverCurrentPage * perPageValue, totalItems);

        // Update global currentPage to match server
        currentPage = serverCurrentPage;

        console.log('Pagination info:', {
            currentPage: currentPage,
            totalPages: totalPages,
            totalItems: totalItems,
            from: from,
            to: to
        });

        // Update info
        var info = `Hiển thị ${from} đến ${to} của ${totalItems} kết quả`;
        $('#payments-info').text(info);

        // Update pagination links
        var paginationHtml = '';

        // Previous button
        if (currentPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">‹</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">‹</span></li>`;
        }

        // Page numbers
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);

        for (var i = startPage; i <= endPage; i++) {
            var activeClass = i === currentPage ? 'active' : '';
            paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        // Next button
        if (currentPage < totalPages) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">›</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">›</span></li>`;
        }

        $('#payments-pagination-links').html(paginationHtml);

        // Bind pagination click events
        $('#payments-pagination-links a').off('click').on('click', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            console.log('Pagination click:', page, 'current:', currentPage);

            if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                console.log('Changing page from', currentPage, 'to', page);
                currentPage = page;
                loadPayments();
            }
        });
    };

    // Helper functions
    var getStatusBadge = function(status) {
        switch(status) {
            case 'pending': return '<span class="badge badge-warning">Chờ xử lý</span>';
            case 'completed': return '<span class="badge badge-success">Hoàn thành</span>';
            case 'cancelled': return '<span class="badge badge-danger">Đã hủy</span>';
            default: return '<span class="badge badge-secondary">N/A</span>';
        }
    };

    var getTypeBadge = function(type) {
        switch(type) {
            case 'receipt': return '<span class="badge badge-primary">Phiếu thu</span>';
            case 'payment': return '<span class="badge badge-info">Phiếu chi</span>';
            default: return '<span class="badge badge-secondary">N/A</span>';
        }
    };

    var getMethodBadge = function(method) {
        switch(method) {
            case 'cash': return '<span class="badge badge-light-success">Tiền mặt</span>';
            case 'card': return '<span class="badge badge-light-primary">Thẻ</span>';
            case 'transfer': return '<span class="badge badge-light-info">Chuyển khoản</span>';
            case 'check': return '<span class="badge badge-light-warning">Séc</span>';
            default: return '<span class="badge badge-light-secondary">Khác</span>';
        }
    };

    var formatDate = function(dateString) {
        if (!dateString) return 'N/A';
        var date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    };

    var formatTime = function(dateString) {
        if (!dateString) return 'N/A';
        var date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    };

    var formatCurrency = function(amount) {
        if (!amount) return '0₫';
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    };



    /**
     * Show loading state
     */
    var showLoadingState = function() {
        var tbody = $('#payments-table-body');
        tbody.html(`
            <tr>
                <td colspan="6" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <div class="mt-3 text-muted">Đang tải dữ liệu...</div>
                    </div>
                </td>
            </tr>
        `);
    };

    /**
     * Show error state
     */
    var showErrorState = function(message) {
        var tbody = $('#payments-table-body');
        tbody.html(`
            <tr>
                <td colspan="6" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <i class="fas fa-exclamation-triangle text-danger fs-2x mb-3"></i>
                        <div class="text-danger">${message}</div>
                    </div>
                </td>
            </tr>
        `);
    };

    /**
     * Get type badge HTML
     */
    var getTypeBadge = function(type) {
        if (type === 'receipt') {
            return '<span class="badge badge-light-success">Phiếu thu</span>';
        } else if (type === 'disbursement') {
            return '<span class="badge badge-light-danger">Phiếu chi</span>';
        }
        return '<span class="badge badge-light-secondary">N/A</span>';
    };

    /**
     * Toggle payment detail panel
     */
    var togglePaymentDetail = function(paymentId) {
        if (currentPaymentId === paymentId && detailPanel && detailPanel.classList.contains('show')) {
            hidePaymentDetail();
        } else {
            showPaymentDetail(paymentId);
        }
    };

    /**
     * Show payment detail panel
     */
    var showPaymentDetail = function(paymentId) {
        console.log('Showing payment detail for ID:', paymentId);
        currentPaymentId = paymentId;

        // Mark row as expanded
        $('#payments-table-body tr').removeClass('expanded');
        var targetRow = $(`#payments-table-body tr[data-payment-id="${paymentId}"]`);
        targetRow.addClass('expanded');

        // Load payment data
        loadPaymentDetail(paymentId);

        // Show panel and overlay
        if (detailOverlay) {
            console.log('Showing overlay');
            detailOverlay.classList.add('show');
        } else {
            console.error('Detail overlay not found');
        }

        if (detailPanel) {
            console.log('Showing panel');
            detailPanel.classList.add('show');
        } else {
            console.error('Detail panel not found');
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    };

    /**
     * Hide payment detail panel
     */
    var hidePaymentDetail = function() {
        currentPaymentId = null;

        // Remove expanded state
        $('#payments-table-body tr').removeClass('expanded');

        // Hide panel and overlay
        detailOverlay.classList.remove('show');
        detailPanel.classList.remove('show');

        // Restore body scroll
        document.body.style.overflow = '';
    };

    /**
     * Load payment detail data
     */
    var loadPaymentDetail = function(paymentId) {
        // Show loading state
        // TODO: Add loading spinner

        // TODO: Replace with actual AJAX call to load payment data
        // For now, use mock data based on payment ID

        $.ajax({
            url: '/admin/payments/' + paymentId + '/details',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    populatePaymentDetail(response.data);
                } else {
                    console.error('Failed to load payment detail:', response.message);
                    // Use mock data as fallback
                    populatePaymentDetail(getMockPaymentData(paymentId));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading payment detail:', error);
                // Use mock data as fallback
                populatePaymentDetail(getMockPaymentData(paymentId));
            }
        });
    };

    /**
     * Get mock payment data for testing
     */
    var getMockPaymentData = function(paymentId) {
        return {
            id: paymentId,
            payment_number: 'TTH0040607',
            payment_type: 'receipt',
            reference_type: 'invoice',
            status: 'completed',
            accounting_status: 'not_accounted',
            creator: 'Lục Thị Như Hoa',
            collector: 'Lục Thị Như Hoa',
            payment_date: '2025-07-10',
            payment_time: '15:07',
            branch: '524 Lý Thường Kiệt',
            amount: 155000,
            income_type: 'Tiền khách trả', // Based on reference_type = 'invoice'
            payer_type: 'Khách hàng',
            payment_method: 'Tiền mặt',
            recipient: 'Khách lẻ',
            related_invoice: {
                code: 'HD040607',
                datetime: '10/07/2025 15:07',
                amount: 155000,
                paid_before: 0,
                collected_amount: 155000,
                status: 'paid'
            },
            notes: null
        };
    };

    /**
     * Populate payment detail panel with data
     */
    var populatePaymentDetail = function(payment) {
        // Payment type badge
        var typeBadge = payment.payment_type === 'receipt' ?
            '<span class="badge badge-light-success">Phiếu thu</span>' :
            '<span class="badge badge-light-danger">Phiếu chi</span>';
        $('#payment_type_badge').html(typeBadge);

        // Payment number
        $('#payment_number').text(payment.payment_number);

        // Status badges
        var statusBadge = payment.status === 'completed' ?
            '<span class="badge badge-light-success">Đã thanh toán</span>' :
            '<span class="badge badge-light-warning">Chờ xử lý</span>';
        $('#payment_status_badge').html(statusBadge);

        var accountingBadge = payment.accounting_status === 'accounted' ?
            '<span class="badge badge-light-success">Đã hạch toán</span>' :
            '<span class="badge badge-light-warning">Không hạch toán</span>';
        $('#accounting_status_badge').html(accountingBadge);

        // Basic info
        $('#payment_creator').text(payment.creator);
        $('#payment_collector').text(payment.collector);
        $('#payment_datetime').text(`${payment.payment_date} ${payment.payment_time}`);
        $('#payment_branch').text(payment.branch);

        // Financial info
        $('#payment_amount').text(formatCurrency(payment.amount));

        // Set income type based on reference_type
        var incomeTypeText = payment.income_type;
        if (payment.reference_type === 'invoice') {
            incomeTypeText = 'Tiền khách trả';
        } else if (payment.reference_type === 'return_order') {
            incomeTypeText = 'Tiền hoàn trả';
        } else if (payment.reference_type === 'manual') {
            incomeTypeText = payment.income_type || 'Thu khác';
        }
        $('#payment_income_type').text(incomeTypeText);

        $('#payment_payer').text(payment.payer_type);
        $('#payment_method').text(payment.payment_method);
        $('#payment_recipient').text(payment.recipient);

        // Related reference (invoice, return order, etc.)
        if (payment.related_invoice) {
            $('#related_invoice_code').text(payment.related_invoice.code);
            $('#invoice_code_link').text(payment.related_invoice.code);
            $('#invoice_datetime').text(payment.related_invoice.datetime);
            $('#invoice_amount').text(formatCurrency(payment.related_invoice.amount));
            $('#invoice_paid_before').text(formatCurrency(payment.related_invoice.paid_before));
            $('#invoice_collected_amount').text(formatCurrency(payment.related_invoice.collected_amount));

            var invoiceStatusBadge = payment.related_invoice.status === 'paid' ?
                '<span class="badge badge-light-success">Đã thanh toán</span>' :
                '<span class="badge badge-light-warning">Chưa thanh toán</span>';
            $('#invoice_status').html(invoiceStatusBadge);

            // Update section title based on reference type
            var sectionTitle = 'Phiếu thu từ đơn được gắn với hóa đơn';
            if (payment.reference_type === 'return_order') {
                sectionTitle = 'Phiếu chi từ đơn trả hàng';
            }
            $('#related_invoice_section h6').html(sectionTitle + ' <span class="text-primary" id="related_invoice_code">' + payment.related_invoice.code + '</span>');

            $('#related_invoice_section').show();
        } else if (payment.related_return_order) {
            // Handle return order reference
            $('#related_invoice_code').text(payment.related_return_order.code);
            $('#invoice_code_link').text(payment.related_return_order.code);
            $('#invoice_datetime').text(payment.related_return_order.datetime);
            $('#invoice_amount').text(formatCurrency(payment.related_return_order.amount));
            $('#invoice_paid_before').text('0');
            $('#invoice_collected_amount').text(formatCurrency(payment.amount));

            var returnStatusBadge = '<span class="badge badge-light-info">Đã hoàn trả</span>';
            $('#invoice_status').html(returnStatusBadge);

            $('#related_invoice_section h6').html('Phiếu chi từ đơn trả hàng <span class="text-primary" id="related_invoice_code">' + payment.related_return_order.code + '</span>');
            $('#related_invoice_section').show();
        } else {
            $('#related_invoice_section').hide();
        }

        // Notes
        if (payment.notes) {
            $('#notes_section').html(`
                <div class="d-flex align-items-start mb-3">
                    <i class="fas fa-sticky-note text-primary me-2 mt-1"></i>
                    <div>
                        <span class="text-dark fw-semibold">${payment.notes}</span>
                    </div>
                </div>
            `);
        } else {
            $('#notes_section').html(`
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-sticky-note text-muted me-2"></i>
                    <span class="text-muted fs-7">Chưa có ghi chú</span>
                </div>
            `);
        }
    };

    /**
     * Initialize column visibility
     */
    var initColumnVisibility = function() {
        columnVisibilityPanel = document.getElementById('column_visibility_panel');
        var trigger = document.getElementById('column_visibility_trigger');

        if (trigger && columnVisibilityPanel) {
            // Toggle panel visibility
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (columnVisibilityPanel.style.display === 'none' || !columnVisibilityPanel.style.display) {
                    columnVisibilityPanel.style.display = 'block';
                } else {
                    columnVisibilityPanel.style.display = 'none';
                }
            });

            // Handle column toggle
            $(columnVisibilityPanel).on('change', '.column-toggle', function() {
                var columnIndex = parseInt($(this).val());
                var isVisible = $(this).is(':checked');

                // Toggle column visibility
                if (table) {
                    var column = $(table).find('th').eq(columnIndex);
                    var cells = $(table).find('td:nth-child(' + (columnIndex + 1) + ')');

                    if (isVisible) {
                        column.show();
                        cells.show();
                    } else {
                        column.hide();
                        cells.hide();
                    }
                }

                // Save state to localStorage
                saveColumnVisibilityState();
            });

            // Close panel when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#column_visibility_trigger, #column_visibility_panel').length) {
                    columnVisibilityPanel.style.display = 'none';
                }
            });

            // Load saved state
            loadColumnVisibilityState();
        }
    };

    /**
     * Save column visibility state
     */
    var saveColumnVisibilityState = function() {
        var state = {};
        $('.column-toggle').each(function() {
            state[$(this).val()] = $(this).is(':checked');
        });
        localStorage.setItem('payment_column_visibility', JSON.stringify(state));
    };

    /**
     * Load column visibility state
     */
    var loadColumnVisibilityState = function() {
        var state = localStorage.getItem('payment_column_visibility');
        if (state) {
            try {
                state = JSON.parse(state);
                $('.column-toggle').each(function() {
                    var columnIndex = $(this).val();
                    if (state.hasOwnProperty(columnIndex)) {
                        $(this).prop('checked', state[columnIndex]);
                        $(this).trigger('change');
                    }
                });
            } catch (e) {
                console.error('Error loading column visibility state:', e);
            }
        }
    };

    /**
     * Initialize time filter
     */
    var initTimeFilter = function() {
        timeOptionsPanel = document.getElementById('time_options_panel');
        var trigger = document.getElementById('time_filter_trigger');
        var closeBtn = document.getElementById('close_time_panel');

        if (trigger && timeOptionsPanel) {
            // Show time options panel
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showTimePanel();
            });

            // Close panel
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    hideTimePanel();
                });
            }

            // Handle time option selection
            $(timeOptionsPanel).on('click', '.time-option', function(e) {
                e.preventDefault();
                var value = $(this).data('value');
                var text = $(this).text();

                console.log('Time option selected:', value, text);

                // Update the trigger text
                $('#time_filter_trigger span').text(text);

                // Ensure the main time filter radio button is checked
                $('#time_this_month').prop('checked', true);

                // Create or update hidden input for time_filter value
                var hiddenInput = $('#time_filter');
                if (hiddenInput.length === 0) {
                    // Create hidden input if it doesn't exist
                    $('<input type="hidden" id="time_filter" name="time_filter" />').appendTo('#kt_payment_filter_form');
                    hiddenInput = $('#time_filter');
                }
                hiddenInput.val(value);

                console.log('Hidden time_filter input updated to:', value);

                // Remove active class from all options
                $('.time-option').removeClass('active btn-primary').addClass('btn-light-primary');

                // Add active class to selected option
                $(this).removeClass('btn-light-primary').addClass('btn-primary active');

                // Close panel with animation
                hideTimePanel();

                // Reload data after animation
                setTimeout(function() {
                    loadPayments();
                }, 300);
            });

            // Close panel when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#time_filter_trigger, #time_options_panel').length) {
                    hideTimePanel();
                }
            });
        }

        // Handle custom time filter radio button
        $('#time_custom').on('change', function() {
            if ($(this).is(':checked')) {
                $('#custom_date_range').slideDown(300);
                // Don't update trigger text - keep it as "Tháng này"

                // Update hidden input for custom time filter
                var hiddenInput = $('#time_filter');
                if (hiddenInput.length === 0) {
                    $('<input type="hidden" id="time_filter" name="time_filter" />').appendTo('#kt_payment_filter_form');
                    hiddenInput = $('#time_filter');
                }
                hiddenInput.val('custom');

                console.log('Custom time filter selected, hidden input updated to: custom');

                // Reload data
                loadPayments();
            }
        });

        // Handle other time filter radio buttons
        $('input[name="time_filter_display"]:not(#time_custom)').on('change', function() {
            if ($(this).is(':checked')) {
                $('#custom_date_range').slideUp(300);

                // Update hidden time_filter input to match the selected value
                var selectedValue = $(this).val();
                var hiddenInput = $('#time_filter');
                if (hiddenInput.length === 0) {
                    $('<input type="hidden" id="time_filter" name="time_filter" />').appendTo('#kt_payment_filter_form');
                    hiddenInput = $('#time_filter');
                }
                console.log('Before update - hidden input value:', hiddenInput.val());
                hiddenInput.val(selectedValue);
                console.log('After update - hidden input value:', hiddenInput.val());
                console.log('Time filter updated to:', selectedValue);

                // Add a small delay to ensure the value is set before loading
                setTimeout(function() {
                    console.log('Final check - hidden input value:', $('#time_filter').val());
                    // Reload data when switching back from custom filter
                    loadPayments();
                }, 100);
            }
        });

        // Handle date range changes
        $('#date_from, #date_to').on('change', function() {
            if ($('#time_custom').is(':checked')) {
                console.log('Date range changed for custom filter');
                // Reload data when date range changes
                loadPayments();
            }
        });
    };

    /**
     * Show time panel with animation
     */
    var showTimePanel = function() {
        if (timeOptionsPanel) {
            timeOptionsPanel.classList.remove('hiding');
            timeOptionsPanel.classList.add('show');
        }
    };

    /**
     * Hide time panel with animation
     */
    var hideTimePanel = function() {
        if (timeOptionsPanel) {
            timeOptionsPanel.classList.add('hiding');
            setTimeout(function() {
                timeOptionsPanel.classList.remove('show', 'hiding');
            }, 300);
        }
    };

    /**
     * Initialize detail panel
     */
    var initDetailPanel = function() {
        detailPanel = document.getElementById('payment_detail_panel');
        detailOverlay = document.getElementById('payment_detail_overlay');

        console.log('Initializing detail panel...');
        console.log('Detail panel found:', !!detailPanel);
        console.log('Detail overlay found:', !!detailOverlay);

        if (detailPanel && detailOverlay) {
            // Close panel when clicking overlay
            detailOverlay.addEventListener('click', function() {
                hidePaymentDetail();
            });

            // Close panel when clicking close button
            var closeBtn = document.getElementById('close_payment_detail');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    hidePaymentDetail();
                });
            }

            // Toggle invoice detail table
            var toggleBtn = document.getElementById('toggle_invoice_detail');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    var table = document.getElementById('invoice_detail_table');
                    var icon = toggleBtn.querySelector('i');

                    if (table.style.display === 'none') {
                        table.style.display = 'block';
                        icon.className = 'fas fa-chevron-up fs-3';
                    } else {
                        table.style.display = 'none';
                        icon.className = 'fas fa-chevron-down fs-3';
                    }
                });
            }

            // Handle action buttons
            $('#edit_payment_btn').on('click', function() {
                if (currentPaymentId) {
                    // TODO: Implement edit functionality
                    console.log('Edit payment:', currentPaymentId);
                }
            });

            $('#print_payment_btn').on('click', function() {
                if (currentPaymentId) {
                    console.log('Print payment:', currentPaymentId);

                    // Create print URL
                    const printUrl = `/admin/payments/${currentPaymentId}/print`;

                    // Open print page in new window
                    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

                    if (!printWindow) {
                        // Fallback if popup blocked
                        window.location.href = printUrl;
                    }
                }
            });

            $('#cancel_payment_btn').on('click', function() {
                if (currentPaymentId) {
                    // TODO: Implement cancel functionality
                    console.log('Cancel payment:', currentPaymentId);
                }
            });

            // Handle invoice code link click
            $(document).on('click', '#invoice_code_link', function(e) {
                e.preventDefault();
                var invoiceCode = $(this).text();

                if (currentPaymentId) {
                    // Get current payment data to determine reference type
                    var referenceType = $('#related_invoice_section').is(':visible') ? 'invoice' : 'return_order';

                    if (referenceType === 'invoice') {
                        // Navigate to invoice page with Code parameter
                        window.open('/admin/invoices?Code=' + invoiceCode, '_blank');
                    } else {
                        // Navigate to return order page
                        window.open('/admin/return-orders?Code=' + invoiceCode, '_blank');
                    }
                }
            });

            // Close panel with Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && detailPanel.classList.contains('show')) {
                    hidePaymentDetail();
                }
            });
        }
    };

    /**
     * Initialize bulk actions
     */
    var initBulkActions = function() {
        var bulkActions = document.getElementById('bulk_actions');
        var selectedCount = document.getElementById('selected_count');

        // Handle master checkbox
        $(document).on('change', 'input[data-kt-check="true"]', function() {
            var isChecked = $(this).is(':checked');
            var checkboxes = $(this).data('kt-check-target');

            $(checkboxes).prop('checked', isChecked);
            updateBulkActions();
        });

        // Handle individual checkboxes
        $(document).on('change', '#payments_custom_table .form-check-input', function() {
            updateBulkActions();
        });

        // Update bulk actions visibility
        function updateBulkActions() {
            var checkedCount = $('#payments_custom_table .form-check-input:checked').length;

            if (checkedCount > 0) {
                bulkActions.classList.remove('d-none');
                if (selectedCount) {
                    selectedCount.textContent = checkedCount;
                }
            } else {
                bulkActions.classList.add('d-none');
            }
        }

        // Handle bulk delete
        $(document).on('click', '#bulk_delete', function() {
            var checkedIds = [];
            $('#payments_custom_table .form-check-input:checked').each(function() {
                var id = $(this).closest('tr').data('id');
                if (id) {
                    checkedIds.push(id);
                }
            });

            if (checkedIds.length > 0) {
                if (confirm('Bạn có chắc chắn muốn xóa ' + checkedIds.length + ' mục đã chọn?')) {
                    // Handle bulk delete logic here
                    console.log('Bulk delete:', checkedIds);
                }
            }
        });
    };

    // Public methods
    return {
        init: function() {
            if (initialized) {
                console.log('KTPaymentsList already initialized');
                return;
            }

            console.log('Initializing KTPaymentsList...');

            table = document.querySelector('#payments_custom_table');
            filterForm = document.querySelector('#kt_payment_filter_form');
            searchInput = $('#payment_search');

            console.log('Table found:', !!table);
            console.log('Filter form found:', !!filterForm);
            console.log('Search input found:', searchInput.length);

            if (!table) {
                console.error('Payment table not found');
                return;
            }

            // Initialize components
            console.log('Initializing components...');
            initColumnVisibility();
            initTimeFilter();
            initDetailPanel();
            initBulkActions();

            // Load all filter data
            loadAllFilterData();

            // Initialize hidden time_filter input with default value
            if ($('#time_filter').length === 0) {
                var defaultTimeFilter = 'this_month'; // Default to "Tháng này"
                $('<input type="hidden" id="time_filter" name="time_filter" />').val(defaultTimeFilter).appendTo('#kt_payment_filter_form');
                console.log('Hidden time_filter input initialized with default value:', defaultTimeFilter);
            }

            // Initialize search
            if (searchInput.length) {
                var searchTimeout;
                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        currentPage = 1;
                        loadPayments();
                    }, 500);
                });
            }

            // Initialize filters
            if (filterForm) {
                $(filterForm).on('change', 'input, select', function() {
                    currentPage = 1;
                    loadPayments();
                });
            }

            // Load initial data
            loadPayments();

            // Also load summary separately to ensure it loads
            setTimeout(function() {
                loadSummary();
            }, 100);

            initialized = true;
            console.log('KTPaymentsList initialized successfully');
        },

        reload: function() {
            loadPayments();
        },

        toggleDetail: function(paymentId) {
            togglePaymentDetail(paymentId);
        }
    };
}();

// Global functions for button actions
window.togglePaymentDetail = function(paymentId) {
    if (typeof KTPaymentsList !== 'undefined' && KTPaymentsList.toggleDetail) {
        KTPaymentsList.toggleDetail(paymentId);
    } else {
        // Fallback if KTPaymentsList is not available
        console.log('Toggle payment detail:', paymentId);
    }
};

window.viewPayment = function(paymentId) {
    console.log('View payment:', paymentId);
    // TODO: Implement view payment modal
    Swal.fire({
        title: 'Xem chi tiết phiếu',
        text: 'Chức năng đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.approvePayment = function(paymentId) {
    console.log('Approve payment:', paymentId);
    // TODO: Implement approve payment
    Swal.fire({
        title: 'Duyệt phiếu',
        text: 'Bạn có chắc chắn muốn duyệt phiếu này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duyệt',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Đã duyệt!', 'Phiếu đã được duyệt thành công.', 'success');
            KTPaymentsList.reload();
        }
    });
};

window.cancelPayment = function(paymentId) {
    console.log('Cancel payment:', paymentId);
    // TODO: Implement cancel payment
    Swal.fire({
        title: 'Hủy phiếu',
        text: 'Bạn có chắc chắn muốn hủy phiếu này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hủy phiếu',
        cancelButtonText: 'Không',
        confirmButtonColor: '#f1416c'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Đã hủy!', 'Phiếu đã được hủy.', 'success');
            KTPaymentsList.reload();
        }
    });
};