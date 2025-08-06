/**
 * SweetAlert2 Helper Functions
 * Global helper functions for consistent SweetAlert2 usage across the application
 */

// SweetAlert2 Helper Object
window.SweetAlert = {
    
    /**
     * Success Alert
     * @param {string} title - Alert title
     * @param {string} text - Alert text (optional)
     * @param {function} callback - Callback function (optional)
     */
    success: function(title, text = '', callback = null) {
        Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#009ef7',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    /**
     * Error Alert
     * @param {string} title - Alert title
     * @param {string} text - Alert text (optional)
     * @param {function} callback - Callback function (optional)
     */
    error: function(title, text = '', callback = null) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#f1416c',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    /**
     * Warning Alert
     * @param {string} title - Alert title
     * @param {string} text - Alert text (optional)
     * @param {function} callback - Callback function (optional)
     */
    warning: function(title, text = '', callback = null) {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc700',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    /**
     * Info Alert
     * @param {string} title - Alert title
     * @param {string} text - Alert text (optional)
     * @param {function} callback - Callback function (optional)
     */
    info: function(title, text = '', callback = null) {
        Swal.fire({
            icon: 'info',
            title: title,
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#009ef7',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    /**
     * Confirmation Dialog
     * @param {string} title - Dialog title
     * @param {string} text - Dialog text (optional)
     * @param {function} confirmCallback - Callback when confirmed
     * @param {function} cancelCallback - Callback when cancelled (optional)
     * @param {string} confirmText - Confirm button text (default: 'Có')
     * @param {string} cancelText - Cancel button text (default: 'Không')
     */
    confirm: function(title, text = '', confirmCallback = null, cancelCallback = null, confirmText = 'Có', cancelText = 'Không') {
        Swal.fire({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#009ef7',
            cancelButtonColor: '#f1416c',
            allowOutsideClick: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && confirmCallback) {
                confirmCallback();
            } else if (result.isDismissed && cancelCallback) {
                cancelCallback();
            }
        });
    },

    /**
     * Delete Confirmation Dialog
     * @param {string} itemName - Name of item to delete
     * @param {function} confirmCallback - Callback when confirmed
     * @param {function} cancelCallback - Callback when cancelled (optional)
     */
    confirmDelete: function(itemName = 'mục này', confirmCallback = null, cancelCallback = null) {
        Swal.fire({
            icon: 'warning',
            title: 'Xác nhận xóa',
            text: `Bạn có chắc chắn muốn xóa ${itemName}? Hành động này không thể hoàn tác!`,
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#f1416c',
            cancelButtonColor: '#6c757d',
            allowOutsideClick: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && confirmCallback) {
                confirmCallback();
            } else if (result.isDismissed && cancelCallback) {
                cancelCallback();
            }
        });
    },

    /**
     * Loading Dialog
     * @param {string} title - Loading title (default: 'Đang xử lý...')
     * @param {string} text - Loading text (optional)
     */
    loading: function(title = 'Đang xử lý...', text = '') {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    /**
     * Close any open SweetAlert
     */
    close: function() {
        Swal.close();
    },

    /**
     * Toast Notification (small notification in corner)
     * @param {string} icon - Icon type (success, error, warning, info)
     * @param {string} title - Toast title
     * @param {number} timer - Auto close timer in ms (default: 3000)
     */
    toast: function(icon, title, timer = 3000) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: icon,
            title: title
        });
    }
};

// Backward compatibility - replace native alert and confirm
window.showAlert = function(message, type = 'info') {
    switch(type) {
        case 'success':
            SweetAlert.success('Thành công', message);
            break;
        case 'error':
            SweetAlert.error('Lỗi', message);
            break;
        case 'warning':
            SweetAlert.warning('Cảnh báo', message);
            break;
        default:
            SweetAlert.info('Thông báo', message);
    }
};

window.showConfirm = function(message, callback, title = 'Xác nhận') {
    SweetAlert.confirm(title, message, callback);
};

console.log('✅ SweetAlert2 Helper loaded successfully');
