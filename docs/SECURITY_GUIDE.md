# YukiMart Security Guide

## 🔒 **SECURITY BEST PRACTICES**

### **⚠️ NEVER COMMIT THESE TO GIT:**

#### **🚫 API Keys và Secrets:**
- Postman API Keys (`PMAK-*`)
- Database passwords
- JWT secrets
- Third-party API keys (Shopee, payment gateways)
- Encryption keys
- OAuth client secrets

#### **🚫 Environment Files:**
- `.env` (production environment)
- `.env.production`
- `.env.local`
- `.env.testing`
- `.env.postman`

#### **🚫 Configuration Files với Sensitive Data:**
- Files containing hardcoded passwords
- Files containing API keys
- Database connection strings với credentials
- SSL certificates và private keys

## ✅ **SAFE PRACTICES**

### **1. Environment Variables:**
```bash
# ✅ GOOD - Use environment variables
POSTMAN_API_KEY=your_api_key_here
DB_PASSWORD=your_password_here

# ❌ BAD - Never hardcode in files
$apiKey = 'PMAK-your-actual-api-key-here';
```

### **2. Configuration Files:**
```php
// ✅ GOOD - Use env() function
'api_key' => env('POSTMAN_API_KEY'),
'password' => env('DB_PASSWORD'),

// ❌ BAD - Never hardcode
'api_key' => 'PMAK-your-actual-api-key-here',
'password' => 'mypassword123',
```

### **3. Documentation:**
```markdown
<!-- ✅ GOOD - Use placeholders -->
POSTMAN_API_KEY=your_postman_api_key_here
COLLECTION_ID=your_collection_id_here

<!-- ❌ BAD - Never include real values -->
POSTMAN_API_KEY=PMAK-your-actual-api-key-here
```

## 🛡️ **SECURITY CHECKLIST**

### **Before Committing:**
- [ ] Check for hardcoded API keys
- [ ] Check for hardcoded passwords
- [ ] Check for database credentials
- [ ] Check for JWT secrets
- [ ] Check for third-party API keys
- [ ] Verify .env files are in .gitignore
- [ ] Verify sensitive files are in .gitignore

### **Environment Setup:**
- [ ] Create `.env.example` với placeholder values
- [ ] Add all sensitive variables to `.env`
- [ ] Ensure `.env` is in `.gitignore`
- [ ] Use `env()` function in all config files
- [ ] Never commit `.env.production` or similar files

### **API Key Management:**
- [ ] Store API keys in environment variables only
- [ ] Use descriptive placeholder names in documentation
- [ ] Rotate API keys regularly
- [ ] Use different keys for different environments
- [ ] Monitor API key usage

## 🚨 **IF YOU ACCIDENTALLY COMMIT SECRETS**

### **1. Immediate Actions:**
```bash
# Remove the secret from files
git add .
git commit -m "🔒 Security: Remove hardcoded secrets"

# If already pushed, contact GitHub support or:
# - Rotate the compromised keys immediately
# - Update all systems using the old keys
# - Monitor for unauthorized usage
```

### **2. GitHub Push Protection:**
If GitHub blocks your push:
1. **Remove the secret** from the file
2. **Replace with placeholder** or environment variable
3. **Commit the fix**
4. **Push again**

### **3. Key Rotation:**
- **Postman API Keys**: Generate new key in Postman settings
- **Database Passwords**: Update in hosting provider
- **JWT Secrets**: Generate new secret key
- **Third-party APIs**: Regenerate in respective platforms

## 📋 **ENVIRONMENT VARIABLE TEMPLATE**

### **Required Environment Variables:**
```bash
# Application
APP_NAME=YukiMart
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Postman Integration (Optional)
POSTMAN_API_KEY=your_postman_api_key_here
POSTMAN_WORKSPACE_ID=your_workspace_id_here
POSTMAN_COLLECTION_ID=your_collection_id_here
POSTMAN_AUTO_SYNC=false

# API Configuration
API_BASE_URL=https://your-domain.com/api/v1
TEST_USER_EMAIL=test@example.com
TEST_USER_PASSWORD=secure_test_password

# Third-party APIs (if used)
SHOPEE_PARTNER_ID=your_shopee_partner_id
SHOPEE_PARTNER_KEY=your_shopee_partner_key
PAYMENT_GATEWAY_KEY=your_payment_key
```

## 🔧 **SECURITY TOOLS**

### **1. Git Hooks:**
Create `.git/hooks/pre-commit`:
```bash
#!/bin/bash
# Check for potential secrets before commit
if git diff --cached --name-only | xargs grep -l "PMAK-\|password.*=.*[^env(]"; then
    echo "❌ Potential secret detected! Please review your changes."
    exit 1
fi
```

### **2. IDE Extensions:**
- **GitGuardian** - Secret detection
- **SonarLint** - Security analysis
- **Snyk** - Vulnerability scanning

### **3. GitHub Security Features:**
- **Secret Scanning** - Automatic detection
- **Push Protection** - Blocks commits với secrets
- **Dependabot** - Dependency vulnerability alerts

## 📞 **INCIDENT RESPONSE**

### **If Secrets Are Compromised:**
1. **Immediate**: Rotate all affected keys/passwords
2. **Assess**: Check logs for unauthorized access
3. **Update**: Update all systems với new credentials
4. **Monitor**: Watch for suspicious activity
5. **Document**: Record incident và lessons learned

### **Contact Information:**
- **GitHub Support**: For repository security issues
- **Hosting Provider**: For server security concerns
- **API Providers**: For key rotation và security

## 🎯 **SECURITY CULTURE**

### **Team Guidelines:**
- **Never share** API keys via chat/email
- **Always use** environment variables
- **Regular review** of committed code
- **Security training** for all developers
- **Incident reporting** without blame

### **Code Review Checklist:**
- [ ] No hardcoded secrets
- [ ] Environment variables used properly
- [ ] Sensitive data properly handled
- [ ] Security best practices followed
- [ ] Documentation updated appropriately

---

## 🚀 **REMEMBER**

**Security is everyone's responsibility!**

- **Think before you commit**
- **Use environment variables**
- **Never hardcode secrets**
- **Review your changes**
- **Report incidents quickly**

**When in doubt, ask the team!**
