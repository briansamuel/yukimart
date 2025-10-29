/**
 * Firebase Access Token Generator for Postman
 * 
 * This script generates Firebase access tokens for FCM v1 API using service account credentials.
 * Use this token in Postman environment variable 'firebase_access_token'.
 * 
 * Prerequisites:
 * 1. Install google-auth-library: npm install google-auth-library
 * 2. Have service-account.json file ready
 * 3. Run: node generate-firebase-token.js
 */

const { GoogleAuth } = require('google-auth-library');
const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
    serviceAccountPath: '../storage/app/firebase/service-account.json',
    scopes: ['https://www.googleapis.com/auth/firebase.messaging'],
    projectId: 'yukimart-pos-system'
};

/**
 * Generate Firebase access token using service account
 */
async function generateFirebaseAccessToken() {
    try {
        console.log('🔐 Generating Firebase access token...');
        
        // Check if service account file exists
        const serviceAccountPath = path.resolve(__dirname, CONFIG.serviceAccountPath);
        if (!fs.existsSync(serviceAccountPath)) {
            throw new Error(`Service account file not found: ${serviceAccountPath}`);
        }
        
        console.log(`📁 Using service account: ${serviceAccountPath}`);
        
        // Initialize Google Auth
        const auth = new GoogleAuth({
            keyFile: serviceAccountPath,
            scopes: CONFIG.scopes
        });
        
        // Get authenticated client
        const client = await auth.getClient();
        console.log('✅ Authentication client created');
        
        // Get access token
        const accessTokenResponse = await client.getAccessToken();
        const accessToken = accessTokenResponse.token;
        
        if (!accessToken) {
            throw new Error('Failed to generate access token');
        }
        
        console.log('🎉 Firebase access token generated successfully!');
        console.log('📋 Copy this token to your Postman environment variable "firebase_access_token":');
        console.log('');
        console.log('=' * 80);
        console.log(accessToken);
        console.log('=' * 80);
        console.log('');
        
        // Get token expiry info
        const tokenInfo = await client.getTokenInfo(accessToken);
        if (tokenInfo.expiry_date) {
            const expiryDate = new Date(tokenInfo.expiry_date * 1000);
            console.log(`⏰ Token expires at: ${expiryDate.toISOString()}`);
            console.log(`⏱️  Valid for: ${Math.round((expiryDate - new Date()) / 1000 / 60)} minutes`);
        }
        
        // Test the token
        console.log('');
        console.log('🧪 Testing token with FCM API...');
        await testFirebaseToken(accessToken);
        
        return accessToken;
        
    } catch (error) {
        console.error('❌ Error generating Firebase access token:', error.message);
        
        if (error.message.includes('ENOENT')) {
            console.log('');
            console.log('💡 Make sure the service account file exists at:');
            console.log(`   ${path.resolve(__dirname, CONFIG.serviceAccountPath)}`);
        }
        
        if (error.message.includes('invalid_grant')) {
            console.log('');
            console.log('💡 Service account credentials may be invalid or expired.');
            console.log('   Download a new service account key from Firebase Console.');
        }
        
        process.exit(1);
    }
}

/**
 * Test Firebase access token by making a test API call
 */
async function testFirebaseToken(accessToken) {
    try {
        const fetch = require('node-fetch');
        
        // Test with a simple FCM API call (validate project)
        const testUrl = `https://fcm.googleapis.com/v1/projects/${CONFIG.projectId}/messages:send`;
        
        const response = await fetch(testUrl, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${accessToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                // Invalid message to test auth only
                message: {}
            })
        });
        
        if (response.status === 400) {
            // 400 means auth is OK but message is invalid (expected)
            console.log('✅ Token is valid and working with FCM API');
        } else if (response.status === 401) {
            console.log('❌ Token authentication failed');
        } else {
            console.log(`ℹ️  FCM API responded with status: ${response.status}`);
        }
        
    } catch (error) {
        console.log('⚠️  Could not test token (this is usually OK):', error.message);
    }
}

/**
 * Generate Postman pre-request script for automatic token generation
 */
function generatePostmanScript() {
    const script = `
// Postman Pre-request Script for Firebase Access Token
// Add this to your collection's Pre-request Script tab

const serviceAccountKey = pm.environment.get("firebase_private_key");
const serviceAccountEmail = pm.environment.get("firebase_service_account_email");
const projectId = pm.environment.get("firebase_project_id");

if (!serviceAccountKey || !serviceAccountEmail || !projectId) {
    console.log("Missing Firebase credentials in environment variables");
    return;
}

// Note: This is a simplified version. For production use, implement proper JWT signing
// For now, use the Node.js script to generate tokens manually

console.log("Use the Node.js script to generate Firebase access token");
console.log("Set the token in firebase_access_token environment variable");
`;
    
    console.log('');
    console.log('📝 Postman Pre-request Script:');
    console.log('   Add this to your collection for automatic token handling:');
    console.log('');
    console.log(script);
}

// Main execution
if (require.main === module) {
    console.log('🚀 YukiMart Firebase Token Generator');
    console.log('=====================================');
    console.log('');
    
    generateFirebaseAccessToken()
        .then(() => {
            generatePostmanScript();
            console.log('');
            console.log('🎯 Next steps:');
            console.log('1. Copy the access token above');
            console.log('2. Paste it into Postman environment variable "firebase_access_token"');
            console.log('3. Test FCM endpoints in your Postman collection');
            console.log('');
            console.log('✨ Happy testing!');
        })
        .catch(error => {
            console.error('Failed to generate token:', error);
        });
}

module.exports = {
    generateFirebaseAccessToken,
    testFirebaseToken
};
