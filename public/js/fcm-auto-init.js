/**
 * FCM Auto-Initialization Script
 * Automatically detects FCM support and initializes appropriate system
 */

(function() {
    'use strict';

    // Configuration
    const FCM_CONFIG = {
        apiKey: "AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc",
        authDomain: "saas-techcura.firebaseapp.com",
        projectId: "saas-techcura",
        storageBucket: "saas-techcura.firebasestorage.app",
        messagingSenderId: "185186239234",
        appId: "1:185186239234:web:9717b33e89ce7c71fd381b",
        measurementId: "G-PWKCFCL5ZQ"
    };

    const VAPID_KEY = 'YOUR_VAPID_KEY_HERE'; // Replace with actual VAPID key

    // Check browser support
    function checkFCMSupport() {
        const issues = [];

        // Check secure context
        if (!window.isSecureContext) {
            issues.push('HTTPS required');
        }

        // Check service workers
        if (!('serviceWorker' in navigator)) {
            issues.push('Service Workers not supported');
        }

        // Check notifications
        if (!('Notification' in window)) {
            issues.push('Notifications not supported');
        }

        // Check Firebase SDK
        if (typeof firebase === 'undefined') {
            issues.push('Firebase SDK not loaded');
        }

        return {
            supported: issues.length === 0,
            issues: issues
        };
    }

    // Initialize FCM
    async function initializeFCM() {
        try {
            console.log('Initializing FCM...');

            // Initialize Firebase
            if (!firebase.apps.length) {
                firebase.initializeApp(FCM_CONFIG);
            }

            // Check messaging support
            let messagingSupported = true;
            try {
                messagingSupported = firebase.messaging.isSupported();
            } catch (e) {
                console.warn('Cannot check messaging support:', e.message);
                messagingSupported = false;
            }

            if (!messagingSupported) {
                throw new Error('Firebase Messaging not supported');
            }

            const messaging = firebase.messaging();

            // Register service worker
            if ('serviceWorker' in navigator) {
                await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('Service Worker registered');
            }

            // Request permission
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.warn('Notification permission not granted');
                return false;
            }

            // Get token
            const token = await messaging.getToken({ vapidKey: VAPID_KEY });
            if (token) {
                console.log('FCM Token:', token.substring(0, 20) + '...');
                
                // Register token with backend
                await registerTokenWithBackend(token);
                
                // Listen for messages
                messaging.onMessage((payload) => {
                    console.log('FCM message received:', payload);
                    showNotification(payload);
                });

                // Listen for token refresh
                messaging.onTokenRefresh(async () => {
                    console.log('FCM token refreshed');
                    const newToken = await messaging.getToken({ vapidKey: VAPID_KEY });
                    if (newToken) {
                        await registerTokenWithBackend(newToken);
                    }
                });

                console.log('FCM initialized successfully');
                return true;
            } else {
                console.warn('No FCM token available');
                return false;
            }

        } catch (error) {
            console.error('FCM initialization failed:', error);
            return false;
        }
    }

    // Initialize fallback
    async function initializeFallback() {
        try {
            console.log('Initializing FCM Fallback...');

            // Check basic notification support
            if (!('Notification' in window)) {
                console.warn('Notifications not supported');
                return false;
            }

            // Request permission
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.warn('Notification permission not granted');
                return false;
            }

            // Register for polling
            await registerForPolling();

            // Start polling for notifications
            startNotificationPolling();

            console.log('FCM Fallback initialized successfully');
            return true;

        } catch (error) {
            console.error('Fallback initialization failed:', error);
            return false;
        }
    }

    // Register token with backend
    async function registerTokenWithBackend(token) {
        try {
            const authToken = getAuthToken();
            if (!authToken) {
                console.warn('No auth token available');
                return false;
            }

            const response = await fetch('/api/v1/fcm/register-token', {
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
                    app_version: '1.0.0'
                })
            });

            if (response.ok) {
                console.log('FCM token registered with backend');
                return true;
            } else {
                console.warn('Failed to register FCM token with backend');
                return false;
            }

        } catch (error) {
            console.error('Error registering token:', error);
            return false;
        }
    }

    // Register for polling-based notifications
    async function registerForPolling() {
        try {
            const authToken = getAuthToken();
            if (!authToken) {
                return false;
            }

            const response = await fetch('/api/v1/notifications/register-polling', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_type: 'web_fallback',
                    device_name: navigator.userAgent,
                    supports_fcm: false
                })
            });

            return response.ok;

        } catch (error) {
            console.error('Error registering for polling:', error);
            return false;
        }
    }

    // Start polling for notifications
    function startNotificationPolling() {
        // Poll every 30 seconds
        setInterval(async () => {
            try {
                const authToken = getAuthToken();
                if (!authToken) return;

                const lastCheck = localStorage.getItem('last_notification_check') || new Date(0).toISOString();
                
                const response = await fetch(`/api/v1/notifications?since=${lastCheck}&unread=true`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    const notifications = result.data || [];

                    notifications.forEach(notification => {
                        showNotification({
                            notification: {
                                title: notification.title,
                                body: notification.message
                            },
                            data: notification.data
                        });
                    });

                    localStorage.setItem('last_notification_check', new Date().toISOString());
                }

            } catch (error) {
                console.error('Error polling notifications:', error);
            }
        }, 30000);
    }

    // Show notification
    function showNotification(payload) {
        const title = payload.notification?.title || 'YukiMart Notification';
        const options = {
            body: payload.notification?.body || 'You have a new notification',
            icon: payload.notification?.icon || '/favicon.ico',
            badge: '/favicon.ico',
            tag: payload.data?.notification_id || 'yukimart-notification'
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

            // Auto close after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }

    // Get auth token
    function getAuthToken() {
        return localStorage.getItem('auth_token') || 
               sessionStorage.getItem('auth_token') ||
               document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    }

    // Main initialization
    async function initialize() {
        // Only initialize if user is logged in
        if (!getAuthToken()) {
            console.log('No auth token found, skipping FCM initialization');
            return;
        }

        console.log('Starting FCM auto-initialization...');

        // Check FCM support
        const support = checkFCMSupport();
        
        if (support.supported) {
            console.log('FCM supported, initializing...');
            const fcmSuccess = await initializeFCM();
            
            if (!fcmSuccess) {
                console.log('FCM failed, falling back to polling...');
                await initializeFallback();
            }
        } else {
            console.log('FCM not supported:', support.issues.join(', '));
            console.log('Using fallback mode...');
            await initializeFallback();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Export for manual initialization
    window.YukiMartFCM = {
        initialize,
        checkSupport: checkFCMSupport,
        initializeFCM,
        initializeFallback
    };

})();
