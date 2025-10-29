/**
 * FCM Fallback for browsers that don't support Firebase Messaging
 * Provides basic notification functionality without FCM
 */

class FCMFallback {
    constructor() {
        this.isSupported = false;
        this.apiBaseUrl = '/api/v1';
        this.notificationPermission = 'default';
    }

    /**
     * Initialize fallback system
     */
    async initialize() {
        console.log('Initializing FCM Fallback...');
        
        // Check basic notification support
        if ('Notification' in window) {
            this.notificationPermission = Notification.permission;
            this.isSupported = true;
            console.log('Basic notifications supported');
            return true;
        } else {
            console.warn('Notifications not supported in this browser');
            return false;
        }
    }

    /**
     * Request notification permission
     */
    async requestPermission() {
        if (!('Notification' in window)) {
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            this.notificationPermission = permission;
            return permission === 'granted';
        } catch (error) {
            console.error('Error requesting permission:', error);
            return false;
        }
    }

    /**
     * Show local notification
     */
    showNotification(title, options = {}) {
        if (this.notificationPermission !== 'granted') {
            console.warn('Notification permission not granted');
            return null;
        }

        const defaultOptions = {
            body: 'You have a new notification',
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'yukimart-notification'
        };

        const notificationOptions = { ...defaultOptions, ...options };
        
        try {
            const notification = new Notification(title, notificationOptions);
            
            // Auto close after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);

            return notification;
        } catch (error) {
            console.error('Error showing notification:', error);
            return null;
        }
    }

    /**
     * Register for polling-based notifications
     */
    async registerForPolling() {
        try {
            const authToken = this.getAuthToken();
            if (!authToken) {
                console.warn('No auth token available for polling registration');
                return false;
            }

            // Register device for polling-based notifications
            const response = await fetch(`${this.apiBaseUrl}/notifications/register-polling`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_type: 'web_fallback',
                    device_name: navigator.userAgent,
                    browser: this.getBrowserInfo(),
                    supports_fcm: false
                })
            });

            if (response.ok) {
                console.log('Registered for polling-based notifications');
                this.startPolling();
                return true;
            } else {
                console.warn('Failed to register for polling');
                return false;
            }

        } catch (error) {
            console.error('Error registering for polling:', error);
            return false;
        }
    }

    /**
     * Start polling for new notifications
     */
    startPolling() {
        // Poll every 30 seconds for new notifications
        setInterval(async () => {
            await this.checkForNewNotifications();
        }, 30000);

        console.log('Started polling for notifications every 30 seconds');
    }

    /**
     * Check for new notifications via API
     */
    async checkForNewNotifications() {
        try {
            const authToken = this.getAuthToken();
            if (!authToken) return;

            const lastCheck = localStorage.getItem('last_notification_check') || new Date(0).toISOString();
            
            const response = await fetch(`${this.apiBaseUrl}/notifications?since=${lastCheck}&unread=true`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                const notifications = result.data || [];

                // Show notifications
                notifications.forEach(notification => {
                    this.showNotification(notification.title, {
                        body: notification.message,
                        data: notification.data,
                        tag: `notification-${notification.id}`
                    });
                });

                // Update last check time
                localStorage.setItem('last_notification_check', new Date().toISOString());

                if (notifications.length > 0) {
                    console.log(`Showed ${notifications.length} new notifications`);
                }
            }

        } catch (error) {
            console.error('Error checking for notifications:', error);
        }
    }

    /**
     * Send test notification (fallback version)
     */
    async sendTestNotification(title = 'Test Notification', message = 'This is a test notification') {
        // Show local notification immediately
        this.showNotification(title, {
            body: message,
            tag: 'test-notification'
        });

        // Also try to send via API for other devices
        try {
            const authToken = this.getAuthToken();
            if (!authToken) {
                return { success: true, message: 'Local notification shown' };
            }

            const response = await fetch(`${this.apiBaseUrl}/notifications`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: 'test',
                    title: title,
                    message: message,
                    channels: ['web'] // Only web channel for fallback
                })
            });

            if (response.ok) {
                return { success: true, message: 'Test notification sent' };
            } else {
                return { success: false, message: 'Failed to send via API, but local notification shown' };
            }

        } catch (error) {
            console.error('Error sending test notification:', error);
            return { success: true, message: 'Local notification shown, API failed' };
        }
    }

    /**
     * Get auth token
     */
    getAuthToken() {
        return localStorage.getItem('auth_token') || 
               sessionStorage.getItem('auth_token') ||
               document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    }

    /**
     * Get browser info
     */
    getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = 'Unknown';
        
        if (ua.includes('Chrome')) browser = 'Chrome';
        else if (ua.includes('Firefox')) browser = 'Firefox';
        else if (ua.includes('Safari')) browser = 'Safari';
        else if (ua.includes('Edge')) browser = 'Edge';
        
        return browser;
    }

    /**
     * Get support status
     */
    getSupportStatus() {
        return {
            notifications: 'Notification' in window,
            serviceWorker: 'serviceWorker' in navigator,
            secureContext: window.isSecureContext,
            fcm: false, // This is the fallback, so FCM is not supported
            fallbackActive: true
        };
    }
}

// Auto-initialize fallback if FCM is not supported
document.addEventListener('DOMContentLoaded', function() {
    // Check if FCM is already working
    if (window.fcmClient && window.fcmClient.isSupported) {
        return; // FCM is working, no need for fallback
    }

    // Initialize fallback
    window.fcmFallback = new FCMFallback();
    window.fcmFallback.initialize().then(success => {
        if (success) {
            console.log('FCM Fallback initialized successfully');
            
            // Auto-register for polling if user is logged in
            if (window.fcmFallback.getAuthToken()) {
                window.fcmFallback.registerForPolling();
            }
        }
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FCMFallback;
}
