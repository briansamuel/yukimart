/**
 * FCM Web Client for YukiMart
 * Handles Firebase Cloud Messaging for web browsers
 */

class FCMWebClient {
    constructor() {
        this.messaging = null;
        this.token = null;
        this.isSupported = false;
        this.apiBaseUrl = '/api/v1';
        
        // Firebase config
        this.firebaseConfig = {
            apiKey: "AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc",
            authDomain: "saas-techcura.firebaseapp.com",
            projectId: "saas-techcura",
            storageBucket: "saas-techcura.firebasestorage.app",
            messagingSenderId: "185186239234",
            appId: "1:185186239234:web:9717b33e89ce7c71fd381b",
            measurementId: "G-PWKCFCL5ZQ"
        };
        
        // VAPID key - Get from Firebase Console > Cloud Messaging > Web Push certificates
        this.vapidKey = 'YOUR_VAPID_KEY_HERE'; // Replace with actual VAPID key
    }

    /**
     * Initialize FCM
     */
    async initialize() {
        try {
            // Check browser support first
            if (!this.checkBrowserSupport()) {
                console.warn('FCM is not supported in this browser/environment');
                return false;
            }

            // Check if Firebase is available
            if (typeof firebase === 'undefined') {
                console.error('Firebase SDK not loaded');
                return false;
            }

            // Initialize Firebase
            if (!firebase.apps.length) {
                firebase.initializeApp(this.firebaseConfig);
            }

            // Check if messaging is supported (with fallback)
            let messagingSupported = true;
            try {
                messagingSupported = firebase.messaging.isSupported();
            } catch (e) {
                console.warn('Cannot check messaging support, assuming supported:', e.message);
                messagingSupported = true;
            }

            if (!messagingSupported) {
                console.warn('FCM messaging is not supported in this browser');
                return false;
            }

            this.messaging = firebase.messaging();
            this.isSupported = true;

            // Register service worker
            await this.registerServiceWorker();

            // Request permission
            await this.requestPermission();

            // Get token
            await this.getToken();

            // Listen for foreground messages
            this.onMessage();

            // Listen for token refresh
            this.onTokenRefresh();

            console.log('FCM initialized successfully');
            return true;

        } catch (error) {
            console.error('FCM initialization failed:', error);
            return false;
        }
    }

    /**
     * Register service worker
     */
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('Service Worker registered:', registration);
                return registration;
            } catch (error) {
                console.error('Service Worker registration failed:', error);
                throw error;
            }
        } else {
            throw new Error('Service Worker not supported');
        }
    }

    /**
     * Request notification permission
     */
    async requestPermission() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('Notification permission granted');
                return true;
            } else {
                console.warn('Notification permission denied');
                return false;
            }
        } catch (error) {
            console.error('Error requesting permission:', error);
            return false;
        }
    }

    /**
     * Get FCM token
     */
    async getToken() {
        try {
            if (!this.messaging) {
                throw new Error('Messaging not initialized');
            }

            const token = await this.messaging.getToken({
                vapidKey: this.vapidKey
            });

            if (token) {
                this.token = token;
                console.log('FCM Token:', token);
                
                // Register token with backend
                await this.registerTokenWithBackend(token);
                
                return token;
            } else {
                console.warn('No registration token available');
                return null;
            }
        } catch (error) {
            console.error('Error getting token:', error);
            return null;
        }
    }

    /**
     * Register token with backend API
     */
    async registerTokenWithBackend(token) {
        try {
            const authToken = this.getAuthToken();
            if (!authToken) {
                console.warn('No auth token available, cannot register FCM token');
                return false;
            }

            const response = await fetch(`${this.apiBaseUrl}/fcm/register-token`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    device_type: 'web',
                    device_name: navigator.userAgent,
                    app_version: '1.0.0',
                    platform_version: navigator.platform,
                    metadata: {
                        browser: this.getBrowserInfo(),
                        screen: {
                            width: screen.width,
                            height: screen.height
                        },
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
                    }
                })
            });

            const result = await response.json();

            if (response.ok) {
                console.log('FCM token registered successfully:', result);
                return true;
            } else {
                console.error('Failed to register FCM token:', result);
                return false;
            }

        } catch (error) {
            console.error('Error registering token with backend:', error);
            return false;
        }
    }

    /**
     * Listen for foreground messages
     */
    onMessage() {
        if (!this.messaging) return;

        this.messaging.onMessage((payload) => {
            console.log('Message received in foreground:', payload);

            // Show custom notification for foreground messages
            this.showForegroundNotification(payload);
        });
    }

    /**
     * Listen for token refresh
     */
    onTokenRefresh() {
        if (!this.messaging) return;

        this.messaging.onTokenRefresh(async () => {
            console.log('Token refreshed');
            await this.getToken();
        });
    }

    /**
     * Show notification for foreground messages
     */
    showForegroundNotification(payload) {
        const title = payload.notification?.title || 'YukiMart Notification';
        const options = {
            body: payload.notification?.body || 'You have a new notification',
            icon: payload.notification?.icon || '/favicon.ico',
            badge: '/favicon.ico',
            tag: payload.data?.notification_id || 'yukimart-notification',
            data: payload.data || {},
            requireInteraction: payload.data?.priority === 'high' || payload.data?.priority === 'urgent'
        };

        if (Notification.permission === 'granted') {
            const notification = new Notification(title, options);
            
            notification.onclick = function() {
                const actionUrl = payload.data?.action_url;
                if (actionUrl) {
                    window.open(actionUrl, '_blank');
                }
                notification.close();
            };
        }
    }

    /**
     * Send test notification
     */
    async sendTestNotification(title = 'Test Notification', message = 'This is a test from YukiMart') {
        try {
            const authToken = this.getAuthToken();
            if (!authToken) {
                throw new Error('No auth token available');
            }

            const response = await fetch(`${this.apiBaseUrl}/fcm/test-notification`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    title: title,
                    message: message
                })
            });

            const result = await response.json();

            if (response.ok) {
                console.log('Test notification sent:', result);
                return result;
            } else {
                console.error('Failed to send test notification:', result);
                return null;
            }

        } catch (error) {
            console.error('Error sending test notification:', error);
            return null;
        }
    }

    /**
     * Get auth token from localStorage or wherever it's stored
     */
    getAuthToken() {
        // Customize this based on how you store auth tokens
        return localStorage.getItem('auth_token') || 
               sessionStorage.getItem('auth_token') ||
               document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    }

    /**
     * Check browser support for FCM
     */
    checkBrowserSupport() {
        // Check if we're in a secure context (HTTPS or localhost)
        if (!window.isSecureContext) {
            console.warn('FCM requires HTTPS or localhost');
            return false;
        }

        // Check if service workers are supported
        if (!('serviceWorker' in navigator)) {
            console.warn('Service Workers not supported');
            return false;
        }

        // Check if notifications are supported
        if (!('Notification' in window)) {
            console.warn('Notifications not supported');
            return false;
        }

        // Check if we're not in a private/incognito window
        try {
            if ('storage' in navigator && 'estimate' in navigator.storage) {
                // This is a basic check for private browsing
                return true;
            }
        } catch (e) {
            console.warn('Storage API not available, might be in private browsing');
        }

        return true;
    }

    /**
     * Get browser information
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
     * Unregister FCM token
     */
    async unregisterToken() {
        try {
            if (!this.token) {
                console.warn('No token to unregister');
                return false;
            }

            const authToken = this.getAuthToken();
            if (!authToken) {
                console.warn('No auth token available');
                return false;
            }

            const response = await fetch(`${this.apiBaseUrl}/fcm/unregister-token`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: this.token
                })
            });

            const result = await response.json();

            if (response.ok) {
                console.log('FCM token unregistered successfully');
                this.token = null;
                return true;
            } else {
                console.error('Failed to unregister FCM token:', result);
                return false;
            }

        } catch (error) {
            console.error('Error unregistering token:', error);
            return false;
        }
    }
}

// Initialize FCM when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.fcmClient = new FCMWebClient();
    
    // Auto-initialize if user is logged in
    if (window.fcmClient.getAuthToken()) {
        window.fcmClient.initialize();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FCMWebClient;
}
