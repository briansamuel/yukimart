"use strict";

// Notifications Manager
var KTNotifications = function () {
    // Private variables
    var notificationCount = 0;
    var unreadCount = 0;
    var refreshInterval;

    // Private functions
    var initNotifications = function() {
        loadNotifications();
        loadUnreadNotifications();
        loadOrderNotifications();
        updateNotificationCount();
        
        // Auto refresh every 30 seconds
        refreshInterval = setInterval(function() {
            loadNotifications();
            updateNotificationCount();
        }, 30000);
    };

    // Load all notifications
    var loadNotifications = function() {
        const container = document.getElementById('all_notifications');
        const loading = document.getElementById('notifications_loading');
        const empty = document.getElementById('notifications_empty');
        
        if (!container) return;

        if (loading) loading.style.display = 'flex';
        if (empty) empty.classList.add('d-none');

        fetch('/admin/notifications/recent')
            .then(response => response.json())
            .then(data => {
                if (loading) loading.style.display = 'none';

                if (data.success && data.data.length > 0) {
                    container.innerHTML = '';
                    data.data.forEach(notification => {
                        container.appendChild(createNotificationItem(notification));
                    });
                } else {
                    if (empty) empty.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                if (loading) loading.style.display = 'none';
                if (empty) empty.classList.remove('d-none');
            });
    };

    // Load unread notifications
    var loadUnreadNotifications = function() {
        const container = document.getElementById('order_notifications');
        const spinnerElement = container ? container.querySelector('.spinner-border') : null;
        const loading = spinnerElement ? spinnerElement.parentElement : null;
        const empty = null; // Will create if needed
        
        if (!container) return;

        if (loading) loading.style.display = 'flex';

        fetch('/admin/notifications/recent?unread=1')
            .then(response => response.json())
            .then(data => {
                if (loading) loading.style.display = 'none';

                if (data.success && data.data.length > 0) {
                    container.innerHTML = '';
                    data.data.forEach(notification => {
                        container.appendChild(createNotificationItem(notification, true));
                    });
                    unreadCount = data.data.length;
                } else {
                    container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Không có thông báo chưa đọc</span></div>';
                    unreadCount = 0;
                }
            })
            .catch(error => {
                console.error('Error loading unread notifications:', error);
                if (loading) loading.style.display = 'none';
                container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Lỗi khi tải thông báo</span></div>';
            });
    };

    // Load order notifications
    var loadOrderNotifications = function() {
        const container = document.getElementById('system_notifications');
        const spinnerElement = container ? container.querySelector('.spinner-border') : null;
        const loading = spinnerElement ? spinnerElement.parentElement : null;
        const empty = null; // Will create if needed
        
        if (!container) return;

        if (loading) loading.style.display = 'flex';

        fetch('/admin/notifications/recent?type=order')
            .then(response => response.json())
            .then(data => {
                if (loading) loading.style.display = 'none';

                if (data.success && data.data.length > 0) {
                    container.innerHTML = '';
                    data.data.forEach(notification => {
                        container.appendChild(createOrderNotificationItem(notification));
                    });
                } else {
                    container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Không có thông báo nào</span></div>';
                }
            })
            .catch(error => {
                console.error('Error loading order notifications:', error);
                if (loading) loading.style.display = 'none';
                container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Lỗi khi tải thông báo</span></div>';
            });
    };

    // Create notification item
    var createNotificationItem = function(notification, showMarkAsRead = false) {
        const item = document.createElement('div');
        item.className = 'd-flex flex-stack py-4';
        item.setAttribute('data-notification-id', notification.id);

        const isUnread = notification.read_at === null;
        const iconClass = getNotificationIcon(notification.type);
        const badgeClass = getNotificationBadgeClass(notification.priority || 'normal');
        
        item.innerHTML = `
            <!--begin::Section-->
            <div class="d-flex align-items-center">
                <!--begin::Symbol-->
                <div class="symbol symbol-35px me-4">
                    <span class="symbol-label ${badgeClass}">
                        <i class="${iconClass} fs-2 text-white"></i>
                    </span>
                </div>
                <!--end::Symbol-->
                <!--begin::Title-->
                <div class="mb-0 me-2">
                    <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bold ${isUnread ? 'text-primary' : ''}" onclick="markAsReadAndRedirect('${notification.id}', '${(notification.data && notification.data.action_url) ? notification.data.action_url : '#'}')"
                        ${notification.title}
                    </a>
                    <div class="text-gray-400 fs-7">${formatNotificationMessage(notification)}</div>
                    ${isUnread ? '<span class="badge badge-light-primary badge-sm">Mới</span>' : ''}
                </div>
                <!--end::Title-->
            </div>
            <!--end::Section-->
            <!--begin::Label-->
            <div class="d-flex flex-column align-items-end">
                <span class="badge badge-light fs-8">${formatTimeAgo(notification.created_at)}</span>
                ${showMarkAsRead && isUnread ? `<button class="btn btn-sm btn-light-primary mt-1" onclick="markAsRead('${notification.id}')">Đánh dấu đã đọc</button>` : ''}
            </div>
            <!--end::Label-->
        `;

        return item;
    };

    // Create order notification item
    var createOrderNotificationItem = function(notification) {
        const item = document.createElement('div');
        item.className = 'd-flex flex-stack py-4';
        item.setAttribute('data-notification-id', notification.id);

        const isUnread = notification.read_at === null;
        const statusClass = getOrderStatusClass(notification.type);
        
        item.innerHTML = `
            <!--begin::Section-->
            <div class="d-flex align-items-center me-2">
                <!--begin::Code-->
                <span class="w-70px badge ${statusClass} me-4">${getOrderStatusText(notification.type)}</span>
                <!--end::Code-->
                <!--begin::Title-->
                <a href="#" class="text-gray-800 text-hover-primary fw-semibold ${isUnread ? 'text-primary' : ''}" onclick="markAsReadAndRedirect('${notification.id}', '${(notification.data && notification.data.action_url) ? notification.data.action_url : '#'}')">
                    ${notification.title}
                </a>
                <!--end::Title-->
            </div>
            <!--end::Section-->
            <!--begin::Label-->
            <span class="badge badge-light fs-8">${formatTimeAgo(notification.created_at)}</span>
            <!--end::Label-->
        `;

        return item;
    };

    // Get notification icon
    var getNotificationIcon = function(type) {
        const icons = {
            'order_created': 'ki-duotone ki-basket',
            'order_updated': 'ki-duotone ki-pencil',
            'order_cancelled': 'ki-duotone ki-cross-circle',
            'customer_created': 'ki-duotone ki-profile-user',
            'product_created': 'ki-duotone ki-element-11',
            'inventory_low': 'ki-duotone ki-warning-2',
            'system': 'ki-duotone ki-gear'
        };
        return icons[type] || 'ki-duotone ki-notification-bing';
    };

    // Get notification badge class
    var getNotificationBadgeClass = function(priority) {
        const classes = {
            'high': 'bg-danger',
            'medium': 'bg-warning',
            'normal': 'bg-primary',
            'low': 'bg-info'
        };
        return classes[priority] || 'bg-primary';
    };

    // Get order status class
    var getOrderStatusClass = function(type) {
        const classes = {
            'order_created': 'badge-light-success',
            'order_updated': 'badge-light-warning',
            'order_cancelled': 'badge-light-danger'
        };
        return classes[type] || 'badge-light-primary';
    };

    // Get order status text
    var getOrderStatusText = function(type) {
        const texts = {
            'order_created': 'NEW',
            'order_updated': 'UPD',
            'order_cancelled': 'CAN'
        };
        return texts[type] || 'INFO';
    };

    // Format time ago
    var formatTimeAgo = function(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Vừa xong';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' phút trước';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' giờ trước';
        if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' ngày trước';

        return date.toLocaleDateString('vi-VN');
    };

    // Format notification message with special styling for invoice sales
    var formatNotificationMessage = function(notification) {
        if (notification.type === 'invoice_sale' && notification.data && notification.data.seller_name) {
            const sellerName = notification.data.seller_name;
            const amount = notification.data.formatted_amount || (notification.data.total_amount ?
                new Intl.NumberFormat('vi-VN').format(notification.data.total_amount) + ' VND' : '');

            // Format: {Seller Name} vừa {bán đơn hàng} với giá trị {amount}
            // Seller name in primary color, "bán đơn hàng" in primary with link, amount in bold
            const actionUrl = notification.action_url || '#';
            return `<span class="text-primary fw-bold">${sellerName}</span> vừa <a href="${actionUrl}" class="text-primary text-decoration-none">bán đơn hàng</a> với giá trị <span class="fw-bold text-dark">${amount}</span>`;
        }

        return notification.message;
    };

    // Update notification count
    var updateNotificationCount = function() {
        fetch('/admin/notifications/count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationCount = data.data.total || 0;
                    unreadCount = data.data.unread || 0;
                    
                    // Update badge
                    const badge = document.getElementById('notification_count');
                    const pulse = document.getElementById('notification_pulse');
                    const headerCount = document.getElementById('notification_header_count');
                    badge.style.top='-7px';
                    badge.style.right='-7px';
                    badge.style.width='25px';
                    badge.style.height='25px';
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.style.display = 'inline-flex';
                        pulse.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                        pulse.style.display = 'none';
                    }
                    
                    if (headerCount) {
                        headerCount.textContent = `${notificationCount} thông báo`;
                    }
                }
            })
            .catch(error => console.error('Error updating notification count:', error));
    };

    // Mark as read and redirect
    window.markAsReadAndRedirect = function(notificationId, url) {
        markAsRead(notificationId);
        if (url && url !== '#') {
            window.location.href = url;
        }
    };

    // Mark as read
    window.markAsRead = function(notificationId) {
        fetch(`/admin/notifications/${notificationId}/read`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from unread list
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item && item.closest('#order_notifications')) {
                    item.remove();
                }

                // Update counts
                updateNotificationCount();
                loadUnreadNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    };

    // Mark all as read
    var initMarkAllRead = function() {
        const button = document.getElementById('mark_all_read');
        if (button) {
            button.addEventListener('click', function() {
                fetch('/admin/notifications/mark-all-read', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadNotifications();
                        loadUnreadNotifications();
                        updateNotificationCount();
                    }
                })
                .catch(error => console.error('Error marking all as read:', error));
            });
        }
    };

    // Public methods
    return {
        init: function () {
            initNotifications();
            initMarkAllRead();
        },

        refresh: function() {
            loadNotifications();
            loadUnreadNotifications();
            loadOrderNotifications();
            updateNotificationCount();
        },

        destroy: function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
    }
}();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    KTNotifications.init();
});

// Global access
window.KTNotifications = KTNotifications;
