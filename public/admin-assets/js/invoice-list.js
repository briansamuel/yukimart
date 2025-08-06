"use strict";

// Global column visibility state using KTColumnVisibility
var invoiceColumnVisibility;

/**
 * Invoice List with DataTables
 * Using DataTables for column visibility functionality
 */
var KTInvoicesListSimple = function () {
    var table;
    var dt;
    var searchInput;
    var currentRequest;
    var initialized = false;
    var timeOptionsPanel;
    var filterForm;
    var currentPage = 1;
    var perPage = 10;
    var filterForm;
    var timeOptionsPanel;

    /**
     * Get filter data from form
     */
    var getFilterData = function() {
        console.log('getFilterData() called');
        var data = {
            page: currentPage,
            per_page: perPage
        };

        console.log('filterForm:', filterForm);
        console.log('filterForm exists:', !!filterForm);
        if (filterForm) {
            console.log('filterForm found, collecting data...');
            // Initialize status array
            data.status = [];

            $(filterForm).find('input, select').each(function() {
                var $input = $(this);
                var name = $input.attr('name') || $input.attr('id');

                if (name) {
                    if ($input.is(':checkbox')) {
                        if (name === 'status[]') {
                            // Handle status checkboxes - collect into status array
                            if ($input.is(':checked')) {
                                data.status.push($input.val());
                            }
                        } else if (name.endsWith('[]')) {
                            // Handle other array checkboxes
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
                            data[name] = $input.val();
                        }
                    } else if ($input.is('select[multiple]')) {
                        data[name] = $input.val() || [];
                    } else {
                        var value = $input.val();
                        if (value) {
                            data[name] = value;
                        }
                    }
                }
            });
        }

        // Add search term
        if (searchInput && searchInput.value) {
            data.search_term = searchInput.value;
        }

        // Debug log
        console.log('Filter data collected:', data);
        console.log('Status array length:', data.status ? data.status.length : 'undefined');

        return data;
    };

    /**
     * Load invoices data
     */
    var loadInvoices = function() {
        // Cancel previous request
        if (currentRequest) {
            currentRequest.abort();
        }

        var filterData = getFilterData();
        console.log('Loading invoices with filters:', filterData);

        // Show loading state
        showLoadingState();

        currentRequest = $.ajax({
            url: '/admin/invoices/ajax',
            type: 'GET',
            data: filterData,
            success: function(response) {
                console.log('Invoice data loaded:', response);
                console.log('Response has data property:', !!response.data);
                console.log('Response data type:', typeof response.data);
                console.log('Response data length:', response.data ? response.data.length : 'N/A');

                if (response.data && Array.isArray(response.data)) {
                    console.log('About to call renderInvoices with data:', response.data.length, 'items');
                    try {
                        renderInvoices(response.data);
                        console.log('renderInvoices completed successfully');
                    } catch (error) {
                        console.error('Error in renderInvoices:', error);
                        console.error('Error stack:', error.stack);
                        return;
                    }

                    console.log('About to call renderPagination with response:', response);
                    console.log('renderPagination function exists:', typeof renderPagination);
                    try {
                        console.log('Calling renderPagination now...');
                        renderPagination(response);
                        console.log('renderPagination called successfully');
                    } catch (error) {
                        console.error('Error in renderPagination:', error);
                        console.error('Error stack:', error.stack);
                    }
                } else {
                    console.log('No data in response, showing error state');
                    console.log('Response.data is:', response.data);
                    showErrorState('Có lỗi xảy ra khi tải dữ liệu');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error - Status:', status, 'Error:', error);
                console.error('AJAX Error - Response:', xhr.responseText);
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
        var tbody = $('#invoices-table-body');
        tbody.html(`
            <tr>
                <td colspan="17" class="text-center py-10">
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
        var tbody = $('#invoices-table-body');
        tbody.html(`
            <tr>
                <td colspan="17" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <i class="fas fa-exclamation-triangle text-warning fs-2x mb-3"></i>
                        <div class="text-muted">${message}</div>
                        <button class="btn btn-sm btn-primary mt-3" onclick="KTInvoicesListSimple.reload()">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </div>
                </td>
            </tr>
        `);
    };

    /**
     * Apply column visibility using KTColumnVisibility
     */
    var applyColumnVisibility = function() {
        if (invoiceColumnVisibility && typeof KTColumnVisibility !== 'undefined') {
            KTColumnVisibility.apply({
                tableSelector: '#kt_invoices_table'
            }, invoiceColumnVisibility);
        }
    };

    /**
     * Update detail panel widths to match table container
     */
    var updateDetailPanelWidths = function() {
        var tableContainer = $('#kt_invoices_table_container');
        var containerWidth = tableContainer.width();

        if (containerWidth) {
            $('.invoice-detail-container').css({
                'width': containerWidth + 'px',
                'max-width': containerWidth + 'px'
            });

            // $('.invoice-detail-panel').css({
            //     'width': containerWidth + 'px',
            //     'max-width': containerWidth + 'px'
            // });
        }
    };

    /**
     * Update border spans position and height based on clicked row position
     */
    var updateBorderSpansPosition = function($clickedRow, $detailRow) {
        setTimeout(function() {
            var $container = $('#kt_invoices_table_container');
            var $borderLeft = $container.find('.invoice-detail-border-left');
            var $borderRight = $container.find('.invoice-detail-border-right');

            // Get the position of clicked row relative to container
            var containerOffset = $container.offset();
            var rowOffset = $clickedRow.offset();
            var relativeTop = rowOffset.top - containerOffset.top;

            // Get detail row height
            var detailRowHeight = $detailRow.outerHeight();
            var totalHeight = $clickedRow.outerHeight() + detailRowHeight;

            // Update border spans
            $borderLeft.css({
                'top': relativeTop + 'px',
                'height': totalHeight + 'px',
                'display': 'block'
            });

            $borderRight.css({
                'top': relativeTop + 'px',
                'height': totalHeight + 'px',
                'display': 'block'
            });

            console.log('Border spans updated:', {
                relativeTop: relativeTop,
                totalHeight: totalHeight,
                clickedRowHeight: $clickedRow.outerHeight(),
                detailRowHeight: detailRowHeight
            });
        }, 100); // Small delay to ensure content is rendered
    };

    /**
     * Hide border spans
     */
    var hideBorderSpans = function() {
        var $container = $('#kt_invoices_table_container');
        $container.find('.kt-table-detail-border-left, .kt-table-detail-border-right').css({
            'display': 'none',
            'height': '0px'
        });
    };

    /**
     * Render invoices data
     */
    var renderInvoices = function(invoices) {
        var tbody = $('#invoices-table-body');

        if (!invoices || invoices.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="17" class="text-center py-10">
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
        invoices.forEach(function(invoice) {
            html += renderInvoiceRow(invoice);
        });

        tbody.html(html);

        // Apply column visibility after rendering
        console.log('About to call applyColumnVisibility()');
        try {
            applyColumnVisibility();
            console.log('applyColumnVisibility() called successfully');
        } catch (error) {
            console.error('Error calling applyColumnVisibility():', error);
        }

        // Add click event listeners to rows for expansion using event delegation
        // Remove any existing handlers first
        tbody.off('click.rowExpansion');

        // Debug: Check for existing event handlers
        console.log('Checking existing event handlers on tbody:', $._data(tbody[0], 'events'));
        console.log('About to bind native click event handler to tbody:', tbody[0]);

        // Remove all existing event handlers from tbody and all rows
        $(tbody).off('click');
        $(tbody).find('tr').off('click');
        console.log('Removed all existing click event handlers');

        // Remove cursor pointer style from all rows to prevent default navigation
        $(tbody).find('tr').css('cursor', 'default');
        console.log('Removed cursor pointer from all rows');

        // Use event delegation with high priority and capture phase
        tbody[0].addEventListener('click', function(e) {
            console.log('Native click event captured:', e.target, 'Event type:', e.type);
            console.log('Event currentTarget:', e.currentTarget);
            console.log('Event bubbles:', e.bubbles, 'Event cancelable:', e.cancelable);

            // Find the closest invoice row
            var $row = $(e.target).closest('.invoice-row');
            console.log('Closest invoice row found:', $row.length, $row[0]);

            if (!$row.length) {
                console.log('Not clicking on invoice row');
                return;
            }

            // FIRST: Check if clicking on checkbox or action buttons - DO NOT prevent default for these
            console.log('Event target tagName:', e.target.tagName);
            console.log('Event target type:', e.target.type);
            console.log('Is input checkbox:', $(e.target).is('input[type="checkbox"]'));
            console.log('Closest form-check length:', $(e.target).closest('.form-check').length);

            if ($(e.target).is('input[type="checkbox"]') ||
                $(e.target).closest('.form-check, .btn, .dropdown').length) {
                console.log('✅ CHECKBOX/BUTTON DETECTED - Allowing default behavior and returning early');
                return; // Allow default behavior for checkbox/buttons
            }

            console.log('Row click detected, preventing default and stopping propagation');
            // Only prevent default and stop propagation for actual row clicks
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var invoiceId = $row.data('invoice-id');
            console.log('Row clicked, invoice ID:', invoiceId);
            if (invoiceId) {
                console.log('About to call toggleRowExpansion');
                toggleRowExpansion($row, invoiceId);
            }

            console.log('Returning false to prevent further event handling');
            return false; // Prevent any further event handling
        }, true); // Use capture phase to get priority

        console.log('Native click event handler bound successfully');

        // Update select all checkbox
        updateSelectAllCheckbox();
    };

    /**
     * Render single invoice row
     */
    var renderInvoiceRow = function(invoice) {
        var customerName = invoice.customer_display || 'Khách lẻ';
        var statusBadge = getStatusBadge(invoice.status);
        var paymentMethodDisplay = getPaymentMethodDisplay(invoice.payment_method);
        var salesChannelDisplay = getSalesChannelDisplay(invoice.sales_channel);

        return `
            <tr data-invoice-id="${invoice.id}" class="invoice-row" style="cursor: pointer;">
                <td ${!invoiceColumnVisibility[0] ? 'style="display: none;"' : ''}>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${invoice.id}" />
                    </div>
                </td>
                <td ${!invoiceColumnVisibility[1] ? 'style="display: none;"' : ''}>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">${invoice.invoice_number}</span>
                    </div>
                </td>
                <td ${!invoiceColumnVisibility[2] ? 'style="display: none;"' : ''}>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                    </div>
                </td>
                <td ${!invoiceColumnVisibility[3] ? 'style="display: none;"' : ''} class="text-end">
                    <span class="text-gray-800 fw-bold">${formatCurrency(invoice.total_amount)}</span>
                </td>
                <td ${!invoiceColumnVisibility[4] ? 'style="display: none;"' : ''} class="text-end">
                    <span class="text-gray-800">${formatCurrency(invoice.amount_paid || 0)}</span>
                </td>
                <td ${!invoiceColumnVisibility[5] ? 'style="display: none;"' : ''}>
                    ${statusBadge}
                </td>
                <td ${!invoiceColumnVisibility[6] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${paymentMethodDisplay}</span>
                </td>
                <td ${!invoiceColumnVisibility[7] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${salesChannelDisplay}</span>
                </td>
                <td ${!invoiceColumnVisibility[8] ? 'style="display: none;"' : ''}>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${formatDate(invoice.created_at)}</span>
                        <span class="text-muted fs-7">${formatTime(invoice.created_at)}</span>
                    </div>
                </td>
                <td ${!invoiceColumnVisibility[9] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.seller || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[10] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.creator || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[11] ? 'style="display: none;"' : ''} class="text-end">
                    <span class="text-gray-800">${formatCurrency(invoice.discount || 0)}</span>
                </td>
                <td ${!invoiceColumnVisibility[12] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.email || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[13] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.phone || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[14] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.address || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[15] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.branch_shop || ''}</span>
                </td>
                <td ${!invoiceColumnVisibility[16] ? 'style="display: none;"' : ''}>
                    <span class="text-gray-800">${invoice.notes || ''}</span>
                </td>
            </tr>
        `;
    };

    /**
     * Toggle row expansion to show/hide detail panel
     */
    var toggleRowExpansion = function($row, invoiceId) {
        console.log('Toggling row expansion for invoice:', invoiceId);

        // Close any other open detail rows first
        $('.invoice-detail-row:visible').each(function() {
            var $openRow = $(this);
            $openRow.slideUp(300, function() {
                $openRow.remove();
            });
        });
        $('.invoice-row-active').removeClass('expanded invoice-row-active');

        // Hide border spans when closing other rows
        hideBorderSpans();

        var $nextRow = $row.next('.invoice-detail-row');

        if ($nextRow.length > 0) {
            // Detail row exists, toggle it
            if ($nextRow.is(':visible')) {
                console.log('Hiding detail row');
                $nextRow.slideUp(300, function() {
                    $nextRow.remove();
                    // Hide border spans when detail row is closed
                    hideBorderSpans();
                });
                $row.removeClass('expanded invoice-row-active');
            } else {
                console.log('Showing existing detail row');
                $nextRow.slideDown(300);
                $row.addClass('expanded invoice-row-active');
            }
        } else {
            // Detail row doesn't exist, create and show it
            console.log('Creating new detail row');
            $row.addClass('expanded invoice-row-active');

            // Create placeholder row with width matching table container
            var columnCount = $row.find('td').length;

            // Get the table container width to match detail panel width
            var tableContainer = $('#kt_invoices_table_container');
            var containerWidth = tableContainer.width();

            var $detailRow = $(`
                <tr class="kt-table-detail-row" style="display: none;">
                    <td colspan="${columnCount}" class="p-0">
                        <div class="kt-table-detail-container p-5">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Đang tải...</span>
                                </div>
                                <div class="ms-3 text-muted">Đang tải chi tiết hóa đơn...</div>
                            </div>
                        </div>
                    </td>
                </tr>
            `);

            $row.after($detailRow);
            $detailRow.slideDown(300);

            // Load detail content via AJAX
            loadInvoiceDetail(invoiceId, $detailRow, $row);
        }
    };

    /**
     * Load invoice detail content via AJAX
     */
    var loadInvoiceDetail = function(invoiceId, $detailRow, $clickedRow) {
        console.log('Loading invoice detail for ID:', invoiceId);

        $.ajax({
            url: `/admin/invoices/${invoiceId}/detail-panel`,
            type: 'GET',
            success: function(response) {
                console.log('Invoice detail loaded successfully');

                // Check if response has html property (JSON response) or is HTML directly
                var htmlContent = response.html || response;

                // Update the detail row content
                $detailRow.find('.kt-table-detail-container').html(htmlContent);

                // Get table container width and apply to detail container
                var tableContainer = $('#kt_invoices_table_container');
                var containerWidth = tableContainer.width();

                if (containerWidth) {
                    $detailRow.find('.kt-table-detail-container').css({
                        'width': containerWidth + 'px',
                        'max-width': containerWidth + 'px',
                        'overflow': 'visible',
                        'box-sizing': 'border-box'
                    });

                    // Apply same width to detail panel content
                    // $detailRow.find('.invoice-detail-panel').css({
                    //     'width': containerWidth + 'px',
                    //     'max-width': containerWidth + 'px',
                    //     'overflow': 'visible',
                    //     'box-sizing': 'border-box'
                    // });
                }

                // Update border spans position and height based on clicked row
                updateBorderSpansPosition($clickedRow, $detailRow);

                // Initialize any JavaScript components in the detail panel
                initDetailPanelComponents($detailRow);
            },
            error: function(xhr, status, error) {
                console.error('Error loading invoice detail:', error);

                var errorHtml = `
                    <div class="d-flex flex-column align-items-center text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fs-2x mb-3"></i>
                        <div class="text-muted mb-3">Không thể tải chi tiết hóa đơn</div>
                        <button class="btn btn-sm btn-primary" onclick="loadInvoiceDetail(${invoiceId}, $(this).closest('.invoice-detail-row'))">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </div>
                `;

                $detailRow.find('.invoice-detail-container').html(errorHtml);
            }
        });
    };

    /**
     * Initialize components in detail panel
     */
    var initDetailPanelComponents = function($detailRow) {
        console.log('Initializing detail panel components');

        // Initialize Bootstrap tabs
        $detailRow.find('a[data-bs-toggle="tab"]').off('click.detailTab').on('click.detailTab', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this);
            var target = $this.attr('href');

            // Remove active class from all tabs in this panel
            $this.closest('.nav-tabs').find('.nav-link').removeClass('active');
            $this.addClass('active');

            // Hide all tab panes in this panel
            $this.closest('.card-body').find('.tab-pane').removeClass('show active');

            // Show target tab pane
            $(target).addClass('show active');

            console.log('Detail panel tab clicked:', target);
        });

        // Initialize any other components as needed
        // e.g., tooltips, popovers, etc.
    };

    /**
     * Helper functions for display formatting
     */
    var getStatusBadge = function(status) {
        var badges = {
            'processing' : '<span class="badge badge-warning">Đang xử lý</span>',
            'completed' : '<span class="badge badge-success">Hoàn thành</span>',
            'cancelled' : '<span class="badge badge-danger">Đã huỷ</span>',
            'undeliverable' : '<span class="badge badge-info">Không giao được</span>',
        };
        return badges[status] || '<span class="badge badge-light-secondary">N/A</span>';
    };

    var getPaymentMethodDisplay = function(method) {
        var methods = {
            'cash': 'Tiền mặt',
            'card': 'Thẻ',
            'transfer': 'Chuyển khoản',
            'check': 'Séc',
            'other': 'Khác'
        };
        return methods[method] || 'N/A';
    };

    var getSalesChannelDisplay = function(channel) {
        var channels = {
            'offline': 'Cửa hàng',
            'marketplace': 'Marketplace',
            'online': 'Online'
        };
        return channels[channel] || 'N/A';
    };

    /**
     * Format currency
     */
    var formatCurrency = function(amount) {
        if (!amount) return '0 ₫';
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    };

    /**
     * Format date
     */
    var formatDate = function(dateString) {
        if (!dateString) return '';
        var date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    };

    /**
     * Format time
     */
    var formatTime = function(dateString) {
        if (!dateString) return '';
        var date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    };

    /**
     * Render pagination
     */
    var renderPagination = function(response) {
        console.log('renderPagination called with response:', response);
        console.log('recordsFiltered:', response.recordsFiltered, 'perPage:', perPage, 'currentPage:', currentPage);
        var totalPages = Math.ceil(response.recordsFiltered / perPage);
        var paginationHtml = '';

        if (totalPages > 1) {
            // Previous button
            if (currentPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Trước</a></li>`;
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
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Tiếp</a></li>`;
            }
        }

        $('#kt_invoices_table_pagination').html(paginationHtml);

        // Update pagination info
        var start = (currentPage - 1) * perPage + 1;
        var end = Math.min(currentPage * perPage, response.recordsFiltered);
        $('#kt_invoices_table_info').text(`Hiển thị ${start} đến ${end} của ${response.recordsFiltered} kết quả`);

        // Add click handlers
        $('#kt_invoices_table_pagination .page-link').off('click').on('click', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            if (page && page !== currentPage) {
                currentPage = page;
                loadInvoices();
            }
        });
    };

    /**
     * Update select all checkbox
     */
    var updateSelectAllCheckbox = function() {
        var totalCheckboxes = $('#invoices-table-body input[type="checkbox"]').length;
        var checkedCheckboxes = $('#invoices-table-body input[type="checkbox"]:checked').length;

        var selectAllCheckbox = $('#select-all-invoices');
        if (selectAllCheckbox.length) {
            selectAllCheckbox.prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            selectAllCheckbox.prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        }
    };

    /**
     * Initialize search functionality
     */
    var initSearch = function() {
        searchInput = document.querySelector('#invoice_search');

        if (searchInput) {
            let searchTimeout;

            // Remove existing event listener first
            searchInput.removeEventListener('input', searchInput._searchHandler);

            // Create named function for easier removal
            searchInput._searchHandler = function (e) {
                // Cancel previous request
                if (currentRequest) {
                    currentRequest.abort();
                }

                clearTimeout(searchTimeout);

                // Set new timeout for debouncing
                searchTimeout = setTimeout(function() {
                    currentPage = 1; // Reset to first page
                    loadInvoices();
                }, 500);
            };

            // Add the event listener
            searchInput.addEventListener('input', searchInput._searchHandler);
        }
    };



    /**
     * Get time filter label mapping
     */
    var getTimeFilterLabel = function(value) {
        var labels = {
            'today': 'Hôm nay',
            'yesterday': 'Hôm qua',
            'this_week': 'Tuần này',
            'last_week': 'Tuần trước',
            '7_days': '7 ngày qua',
            'this_month': 'Tháng này',
            'last_month': 'Tháng trước',
            'this_month_lunar': 'Tháng này (âm lịch)',
            'last_month_lunar': 'Tháng trước (âm lịch)',
            '30_days': '30 ngày qua',
            'this_quarter': 'Quý này',
            'last_quarter': 'Quý trước',
            'this_year': 'Năm này',
            'last_year': 'Năm trước',
            'custom': 'Tùy chỉnh'
        };
        return labels[value] || 'Tháng này';
    };

    /**
     * Update hidden time filter input
     */
    var updateHiddenTimeFilter = function(value) {
        var hiddenInput = $('#hidden_time_filter');
        if (hiddenInput.length === 0) {
            $('<input type="hidden" id="hidden_time_filter" name="time_filter" />').appendTo('#kt_invoice_filter_form');
            hiddenInput = $('#hidden_time_filter');
        }
        hiddenInput.val(value);
        console.log('Hidden time_filter input updated to:', value);
    };



    /**
     * Initialize filters using KTGlobalFilter
     */
    var initFilters = function() {
        console.log('Initializing filters using KTGlobalFilter...');

        // Check if KTGlobalFilter is available
        if (typeof KTGlobalFilter === 'undefined') {
            console.error('KTGlobalFilter is not available. Make sure filter.js is loaded before this script.');
            return;
        }

        // Initialize all filters using the global filter system
        KTGlobalFilter.initAllFilters('#kt_invoice_filter_form', loadInvoices, {
            module: 'invoices',        // Specify module for targeted data loading
            loadAllData: true,         // Enable bulk data loading (default: true)
            timeFilter: true,
            statusFilter: true,
            creatorsFilter: true,
            sellersFilter: true,
            saleChannelsFilter: true,
            paymentMethodsFilter: true
        });

        console.log('Filters initialized successfully using KTGlobalFilter');
    };

    /**
     * Bind column toggle events - now handled by KTColumnVisibility
     */
    var bindColumnToggleEvents = function() {
        // This function is now handled by KTColumnVisibility module
        console.log('Column toggle events handled by KTColumnVisibility module');
    };

    /**
     * Initialize column visibility using KTColumnVisibility
     */
    var initColumnVisibility = function() {
        console.log('Initializing column visibility using KTColumnVisibility...');

        // Check if KTColumnVisibility is available
        if (typeof KTColumnVisibility === 'undefined') {
            console.error('KTColumnVisibility is not available. Make sure column-visibility.js is loaded before this script.');
            return;
        }

        // Define default visibility state
        var defaultVisibility = {
            0: true,  // checkbox
            1: true,  // invoice_number
            2: true,  // customer_display
            3: true,  // total_amount
            4: true,  // amount_paid
            5: true,  // payment_status
            6: true,  // payment_method
            7: true,  // sales_channel
            8: true,  // created_at
            9: false, // seller
            10: false, // creator
            11: false, // discount
            12: false, // email
            13: false, // phone
            14: false, // address
            15: false, // branch_shop
            16: false  // notes
        };

        // Initialize column visibility with configuration
        invoiceColumnVisibility = KTColumnVisibility.init({
            storageKey: 'invoice_column_visibility',
            defaultVisibility: defaultVisibility,
            triggerSelector: '#column_visibility_trigger',
            panelSelector: '#column_visibility_panel',
            toggleSelector: '.column-toggle',
            tableSelector: '#kt_invoices_table',
            onToggle: function(columnIndex, isVisible, columnVisibility) {
                console.log('Column visibility changed:', columnIndex, isVisible);
                // Additional callback logic can be added here if needed
            }
        });

        console.log('Column visibility initialized successfully using KTColumnVisibility');
    };

    /**
     * Toggle column visibility without reloading data
     */
    var toggleColumnVisibility = function(columnIndex, isVisible) {
        console.log('Toggling column visibility:', columnIndex, isVisible);
        console.log('DataTables instance (dt):', dt);

        if (dt) {
            // Use DataTables API to show/hide column
            console.log('Using DataTables API to toggle column');
            dt.column(columnIndex).visible(isVisible);
        } else {
            // Fallback to manual DOM manipulation if DataTables not available
            console.log('Using manual DOM manipulation to toggle column');
            var table = $('#kt_invoices_table');
            console.log('Table found:', table.length);

            // Toggle header cell
            var headerCell = table.find('thead tr th').eq(columnIndex);
            console.log('Header cell found:', headerCell.length, 'for column index:', columnIndex);
            if (isVisible) {
                headerCell.show();
                console.log('Showing header cell');
            } else {
                headerCell.hide();
                console.log('Hiding header cell');
            }

            // Toggle data cells in all rows
            var dataRows = table.find('tbody tr');
            console.log('Data rows found:', dataRows.length);
            dataRows.each(function() {
                var dataCell = $(this).find('td').eq(columnIndex);
                if (isVisible) {
                    dataCell.show();
                } else {
                    dataCell.hide();
                }
            });
        }

        console.log(`Toggled column index ${columnIndex} visibility to: ${isVisible}`);
    };

    /**
     * Update table headers using KTColumnVisibility
     */
    var updateTableHeaders = function() {
        if (invoiceColumnVisibility && typeof KTColumnVisibility !== 'undefined') {
            KTColumnVisibility.updateHeaders({
                tableSelector: '#kt_invoices_table'
            }, invoiceColumnVisibility);
        }
    };

    /**
     * Initialize select all checkbox
     */
    var initSelectAllCheckbox = function() {
        // Handle select all checkbox - REMOVED: Moved to bulk actions handler

        // Handle individual row checkbox changes - REMOVED: Moved to bulk actions handler
    };

    /**
     * Initialize DataTables for column visibility
     */
    var initDataTable = function() {
        // Initialize DataTables with minimal config for column visibility only
        dt = $(table).DataTable({
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            autoWidth: false,
            columnDefs: [
                { targets: [9, 10, 11, 12, 13, 14, 15, 16], visible: false } // Hide columns by default
            ]
        });

        console.log('DataTables initialized for column visibility');
    };

    /**
     * Initialize all components
     */
    var init = function () {
        if (initialized) {
            console.log('KTInvoicesListSimple already initialized, skipping...');
            return;
        }

        console.log('Initializing KTInvoicesListSimple...');

        // Disable other invoice list initializations
        if (window.KTInvoicesList && window.KTInvoicesList.init) {
            console.log('Disabling KTInvoicesList auto-initialization...');
            window.KTInvoicesList.init = function() {
                console.log('KTInvoicesList.init() called but disabled by KTInvoicesListSimple');
            };
        }

        table = document.querySelector('#kt_invoices_table');
        if (!table) {
            console.error('Invoice table not found');
            return;
        }

        // Initialize filter form
        filterForm = document.querySelector('#kt_invoice_filter_form');

        console.log('About to initialize filter...');
        try {
            initFilters();
            console.log('Filter initialized successfully');
        } catch (error) {
            console.error('Error initializing filter:', error);
        }

        console.log('About to initialize search...');
        try {
            initSearch();
            console.log('Search initialized successfully');
        } catch (error) {
            console.error('Error initializing search:', error);
        }



        console.log('About to initialize column visibility...');
        try {
            initColumnVisibility();
            console.log('Column visibility initialized successfully');
        } catch (error) {
            console.error('Error initializing column visibility:', error);
        }

        console.log('About to initialize select all checkbox...');
        try {
            initSelectAllCheckbox();
            console.log('Select all checkbox initialized successfully');
        } catch (error) {
            console.error('Error initializing select all checkbox:', error);
        }

        console.log('About to initialize bulk actions...');
        try {
            initBulkActions();
            console.log('Bulk actions initialized successfully');
        } catch (error) {
            console.error('Error initializing bulk actions:', error);
        }

        console.log('About to load initial data...');
        try {
            loadInvoices();
            console.log('Initial data load triggered successfully');
        } catch (error) {
            console.error('Error loading initial data:', error);
        }

        console.log('About to update table headers...');
        // Update table headers based on initial column visibility
        updateTableHeaders();

        console.log('About to apply column visibility...');
        // Apply column visibility to existing table data
        applyColumnVisibility();
     
        console.log('About to load invoices...');
        // Load initial data first
        loadInvoices();

        // DataTables initialization disabled - using custom pagination
        // console.log('Setting timeout for DataTables initialization...');
        // setTimeout(function() {
        //     console.log('Attempting to initialize DataTables...');
        //     console.log('Table element:', table);
        //     console.log('Table exists:', $(table).length);
        //     initDataTable();
        // }, 1000);

        // Add window resize listener to update detail panel widths
        $(window).on('resize.invoiceDetailPanels', function() {
            updateDetailPanelWidths();
        });

        initialized = true;
        console.log('KTInvoicesListSimple initialized successfully');
    };

    /**
     * Initialize bulk actions functionality
     */
    var initBulkActions = function() {
        console.log('Initializing bulk actions...');

        // Handle select all checkbox
        $(document).on('change', '#select-all-invoices', function() {
            var isChecked = $(this).is(':checked');
            console.log('Select all checkbox changed:', isChecked);

            // Check/uncheck all row checkboxes
            $('#invoices-table-body input[type="checkbox"]').prop('checked', isChecked);

            // Update bulk actions visibility
            updateBulkActionsVisibility();
        });

        // Handle individual row checkboxes
        $(document).off('change.bulkActions').on('change.bulkActions', '#invoices-table-body input[type="checkbox"]', function() {
            console.log('Row checkbox changed - bulk actions handler');

            // Update select all checkbox state (same as old handler)
            updateSelectAllCheckbox();

            // Update bulk actions visibility
            updateBulkActionsVisibility();
        });

        // Prevent checkbox clicks from triggering row expansion
        $(document).off('click.checkboxPrevention').on('click.checkboxPrevention', '#invoices-table-body input[type="checkbox"]', function(e) {
            e.stopPropagation();
            console.log('Checkbox click propagation stopped');
        });

        // Bulk action handlers for new dropdown
        $('#bulk-update-delivery').on('click', function(e) {
            e.preventDefault();
            var selectedIds = getSelectedInvoiceIds();
            console.log('Bulk update delivery clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để cập nhật giao hàng.');
                return;
            }

            // TODO: Implement bulk update delivery functionality
            alert('Tính năng cập nhật giao hàng cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
        });

        $('#bulk-update-info').on('click', function(e) {
            e.preventDefault();
            var selectedIds = getSelectedInvoiceIds();
            console.log('Bulk update info clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để cập nhật thông tin chung.');
                return;
            }

            // TODO: Implement bulk update info functionality
            alert('Tính năng cập nhật thông tin chung cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
        });

        $('#bulk-cancel').on('click', function(e) {
            e.preventDefault();
            var selectedIds = getSelectedInvoiceIds();
            console.log('Bulk cancel clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để huỷ.');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn huỷ ' + selectedIds.length + ' hóa đơn đã chọn?')) {
                bulkCancelInvoices(selectedIds);
            }
        });

        console.log('Bulk actions initialized successfully');
    };

    /**
     * Update bulk actions visibility based on selected checkboxes
     */
    var updateBulkActionsVisibility = function() {
        console.log('updateBulkActionsVisibility called');

        var selectedCount = $('#invoices-table-body input[type="checkbox"]:checked').length;
        console.log('Selected count:', selectedCount);

        var bulkDropdown = $('#bulk-actions-dropdown');
        var bulkCountElement = $('#bulk-count');
        var bulkTextElement = $('#bulk-actions-text');

        console.log('Bulk dropdown found:', bulkDropdown.length);
        console.log('Bulk count element found:', bulkCountElement.length);
        console.log('Bulk text element found:', bulkTextElement.length);

        if (selectedCount > 0) {
            console.log('Showing bulk dropdown...');
            bulkDropdown.show();
            bulkCountElement.text(selectedCount);
            bulkTextElement.text('Thao tác (' + selectedCount + ' hóa đơn)');
        } else {
            console.log('Hiding bulk dropdown...');
            bulkDropdown.hide();
        }
    };

    /**
     * Get array of selected invoice IDs
     */
    var getSelectedInvoiceIds = function() {
        var selectedIds = [];
        $('#invoices-table-body input[type="checkbox"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
        return selectedIds;
    };

    /**
     * Bulk cancel invoices
     */
    var bulkCancelInvoices = function(invoiceIds) {
        console.log('Bulk cancelling invoices:', invoiceIds);

        // Show loading state
        var bulkDropdown = $('#bulk-actions-dropdown');
        var originalText = $('#bulk-actions-text').text();
        $('#bulk-actions-text').text('Đang xử lý...');
        bulkDropdown.find('button').prop('disabled', true);

        // Make AJAX request to cancel invoices
        $.ajax({
            url: '/admin/invoices/bulk-cancel',
            method: 'POST',
            data: {
                invoice_ids: invoiceIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Bulk cancel response:', response);

                if (response.success) {
                    // Show success message
                    alert('Đã huỷ thành công ' + response.cancelled_count + ' hóa đơn.');

                    // Reload the page to refresh data
                    window.location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + (response.message || 'Không thể huỷ hóa đơn.'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Bulk cancel error:', error);
                console.error('Response:', xhr.responseText);

                var errorMessage = 'Có lỗi xảy ra khi huỷ hóa đơn.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                alert(errorMessage);
            },
            complete: function() {
                // Reset loading state
                $('#bulk-actions-text').text(originalText);
                bulkDropdown.find('button').prop('disabled', false);
            }
        });
    };

    // Public methods
    return {
        init: function () {
            init();
        },

        reload: function() {
            loadInvoices();
        },

        getColumnVisibility: function() {
            return invoiceColumnVisibility;
        },

        setColumnVisibility: function(column, visible) {
            if (invoiceColumnVisibility && invoiceColumnVisibility.hasOwnProperty(column) && typeof KTColumnVisibility !== 'undefined') {
                KTColumnVisibility.setVisibility(
                    'invoice_column_visibility',
                    {
                        tableSelector: '#kt_invoices_table',
                        toggleSelector: '.column-toggle'
                    },
                    column,
                    visible,
                    invoiceColumnVisibility
                );
                loadInvoices();
            }
        },

        // Expose render functions for use by invoice-manager.js
        renderInvoices: renderInvoices,
        renderInvoiceRow: renderInvoiceRow,
        renderPagination: renderPagination
    };
}();

// Export functions to global scope for use by invoice-manager.js
window.invoiceListRenderFunctions = {
    renderInvoices: function(invoices) {
        if (typeof KTInvoicesListSimple !== 'undefined' && KTInvoicesListSimple.renderInvoices) {
            return KTInvoicesListSimple.renderInvoices(invoices);
        }
        console.warn('KTInvoicesListSimple.renderInvoices not available');
    },

    renderInvoiceRow: function(invoice) {
        if (typeof KTInvoicesListSimple !== 'undefined' && KTInvoicesListSimple.renderInvoiceRow) {
            return KTInvoicesListSimple.renderInvoiceRow(invoice);
        }
        console.warn('KTInvoicesListSimple.renderInvoiceRow not available');
        return '';
    },

    renderPagination: function(response) {
        if (typeof KTInvoicesListSimple !== 'undefined' && KTInvoicesListSimple.renderPagination) {
            return KTInvoicesListSimple.renderPagination(response);
        }
        console.warn('KTInvoicesListSimple.renderPagination not available');
    }
};

// Initialize when document is ready
$(document).ready(function() {
    console.log('Document ready, initializing KTInvoicesListSimple...');
    KTInvoicesListSimple.init();
});
