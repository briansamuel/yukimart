// Firebase Messaging Service Worker
// For background notifications on web

importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc",
  authDomain: "saas-techcura.firebaseapp.com",
  projectId: "saas-techcura",
  storageBucket: "saas-techcura.firebasestorage.app",
  messagingSenderId: "185186239234",
  appId: "1:185186239234:web:9717b33e89ce7c71fd381b",
  measurementId: "G-PWKCFCL5ZQ"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Retrieve Firebase Messaging object
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  
  // Customize notification here
  const notificationTitle = payload.notification?.title || 'YukiMart Notification';
  const notificationOptions = {
    body: payload.notification?.body || 'You have a new notification',
    icon: payload.notification?.icon || '/favicon.ico',
    badge: '/favicon.ico',
    tag: payload.data?.notification_id || 'yukimart-notification',
    data: payload.data || {},
    actions: payload.data?.action_url ? [
      {
        action: 'open',
        title: payload.data?.action_text || 'Open',
        icon: '/favicon.ico'
      }
    ] : [],
    requireInteraction: payload.data?.priority === 'high' || payload.data?.priority === 'urgent'
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
  console.log('[firebase-messaging-sw.js] Notification click received.');

  event.notification.close();

  // Handle action clicks
  if (event.action === 'open' || !event.action) {
    const actionUrl = event.notification.data?.action_url;
    
    if (actionUrl) {
      // Open the action URL
      event.waitUntil(
        clients.openWindow(actionUrl)
      );
    } else {
      // Open the main app
      event.waitUntil(
        clients.openWindow('/')
      );
    }
  }
});

// Handle notification close
self.addEventListener('notificationclose', function(event) {
  console.log('[firebase-messaging-sw.js] Notification closed.');
  
  // Optional: Track notification close events
  // You can send analytics data here
});
