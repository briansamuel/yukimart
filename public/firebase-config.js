// Firebase Configuration for YukiMart
// Project: saas-techcura

const firebaseConfig = {
  apiKey: "AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc",
  authDomain: "saas-techcura.firebaseapp.com",
  projectId: "saas-techcura",
  storageBucket: "saas-techcura.firebasestorage.app",
  messagingSenderId: "185186239234",
  appId: "1:185186239234:web:9717b33e89ce7c71fd381b",
  measurementId: "G-PWKCFCL5ZQ"
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = firebaseConfig;
} else if (typeof window !== 'undefined') {
  window.firebaseConfig = firebaseConfig;
}
