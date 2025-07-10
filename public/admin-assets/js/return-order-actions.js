/**
 * Return Order Actions JavaScript
 * Handle all return order action buttons functionality
 */

class ReturnOrderActions {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Use event delegation for dynamically loaded content
        $(document).on('click', '[id^="btn-save-return-"]', (e) => {
            const returnOrderId = this.extractReturnOrderId(e.target.id);
            this.handleSave(returnOrderId);
        });

        $(document).on('click', '[id^="btn-cancel-return-"]', (e) => {
            const returnOrderId = this.extractReturnOrderId(e.target.id);
            this.handleCancel(returnOrderId);
        });

        $(document).on('click', '[id^="btn-copy-return-"]', (e) => {
            const returnOrderId = this.extractReturnOrderId(e.target.id);
            this.handleCopy(returnOrderId);
        });

        $(document).on('click', '[id^="btn-export-return-"]', (e) => {
            const returnOrderId = this.extractReturnOrderId(e.target.id);
            this.handleExport(returnOrderId);
        });

        $(document).on('click', '[id^="btn-print-return-"]', (e) => {
            const returnOrderId = this.extractReturnOrderId(e.target.id);
            this.handlePrint(returnOrderId);
        });

        // Handle receiver change
        $(document).on('change', 'select[data-control="select2"]', (e) => {
            const $select = $(e.target);
            if ($select.closest('.col-md-4').find('label').text().includes('Người nhận trả')) {
                this.handleReceiverChange($select);
            }
        });

        // Handle date change
        $(document).on('change', 'input[type="datetime-local"]', (e) => {
            const $input = $(e.target);
            if ($input.closest('.col-md-4').find('label').text().includes('Ngày trả')) {
                this.handleDateChange($input);
            }
        });
    }

    extractReturnOrderId(buttonId) {
        return buttonId.split('-').pop();
    }

    /**
     * Handle Save button click
     */
    handleSave(returnOrderId) {
        const $panel = $(`#kt_return_order_info_${returnOrderId}`);
        const receiverId = $panel.find('select[data-control="select2"]').val();
        const returnDate = $panel.find('input[type="datetime-local"]').val();

        // Show loading
        const $btn = $(`#btn-save-return-${returnOrderId}`);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...').prop('disabled', true);

        // Prepare data
        const data = {
            receiver_id: receiverId,
            return_date: returnDate,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Send AJAX request
        $.ajax({
            url: `/admin/return-orders/${returnOrderId}`,
            method: 'PUT',
            data: data,
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', 'Cập nhật thành công!');
                    // Optionally reload the table
                    if (typeof window.returnOrderTable !== 'undefined') {
                        window.returnOrderTable.ajax.reload(null, false);
                    }
                } else {
                    this.showNotification('error', response.message || 'Có lỗi xảy ra');
                }
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra khi lưu';
                this.showNotification('error', message);
            },
            complete: () => {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Handle Cancel button click
     */
    handleCancel(returnOrderId) {
        Swal.fire({
            title: 'Xác nhận hủy?',
            text: 'Bạn có chắc chắn muốn hủy đơn trả hàng này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Có, hủy đơn!',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.isConfirmed) {
                this.cancelReturnOrder(returnOrderId);
            }
        });
    }

    cancelReturnOrder(returnOrderId) {
        $.ajax({
            url: `/admin/return-orders/${returnOrderId}/cancel`,
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', 'Hủy đơn trả hàng thành công!');
                    // Reload table
                    if (typeof window.returnOrderTable !== 'undefined') {
                        window.returnOrderTable.ajax.reload(null, false);
                    }
                } else {
                    this.showNotification('error', response.message || 'Có lỗi xảy ra');
                }
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra khi hủy đơn';
                this.showNotification('error', message);
            }
        });
    }

    /**
     * Handle Copy button click
     */
    handleCopy(returnOrderId) {
        $.ajax({
            url: `/admin/return-orders/${returnOrderId}/copy`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', 'Sao chép đơn trả hàng thành công!');
                    // Redirect to new return order
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                } else {
                    this.showNotification('error', response.message || 'Có lỗi xảy ra');
                }
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra khi sao chép';
                this.showNotification('error', message);
            }
        });
    }

    /**
     * Handle Export button click
     */
    handleExport(returnOrderId) {
        // Create a temporary link to download
        const downloadUrl = `/admin/return-orders/${returnOrderId}/export`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `return-order-${returnOrderId}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showNotification('info', 'Đang tải xuống file...');
    }

    /**
     * Handle Print button click
     */
    handlePrint(returnOrderId) {
        const printUrl = `/admin/return-orders/${returnOrderId}/print`;
        const printWindow = window.open(printUrl, '_blank', 'width=800,height=600');
        
        if (printWindow) {
            printWindow.onload = function() {
                printWindow.print();
            };
        } else {
            this.showNotification('error', 'Không thể mở cửa sổ in. Vui lòng kiểm tra popup blocker.');
        }
    }

    /**
     * Handle receiver change
     */
    handleReceiverChange($select) {
        const returnOrderId = this.extractReturnOrderIdFromPanel($select);
        const receiverId = $select.val();
        
        // Mark as changed for save
        $select.addClass('changed');
        this.showUnsavedChanges(returnOrderId);
    }

    /**
     * Handle date change
     */
    handleDateChange($input) {
        const returnOrderId = this.extractReturnOrderIdFromPanel($input);
        
        // Mark as changed for save
        $input.addClass('changed');
        this.showUnsavedChanges(returnOrderId);
    }

    extractReturnOrderIdFromPanel($element) {
        const $panel = $element.closest('[id^="kt_return_order_info_"]');
        return $panel.attr('id').split('_').pop();
    }

    showUnsavedChanges(returnOrderId) {
        const $saveBtn = $(`#btn-save-return-${returnOrderId}`);
        $saveBtn.removeClass('btn-primary').addClass('btn-warning');
        $saveBtn.find('i').removeClass('fa-save').addClass('fa-exclamation-triangle');
    }

    /**
     * Show notification
     */
    showNotification(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
                title: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.returnOrderActions = new ReturnOrderActions();
});
