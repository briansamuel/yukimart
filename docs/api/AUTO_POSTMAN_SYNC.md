# YukiMart API - Auto Postman Sync

## 🚀 **AUTOMATIC POSTMAN SYNC SETUP**

Thay vì phải manual upload file collection, bạn có thể setup automatic sync trực tiếp lên Postman workspace sử dụng Postman API.

## 📋 **QUICK START**

### **Step 1: Run Setup Script**
```bash
docker exec -it php83 sh -c "cd /var/www/html/yukimart && php scripts/setup-postman-sync.php"
```

### **Step 2: Follow Interactive Setup**
Script sẽ guide bạn qua toàn bộ process:
- ✅ Create .env.postman configuration file
- ✅ Guide lấy Postman API Key
- ✅ Guide lấy Workspace ID
- ✅ Guide lấy Collection ID (optional)
- ✅ Test configuration

### **Step 3: Sync Collection**
```bash
docker exec -it php83 sh -c "cd /var/www/html/yukimart && php scripts/sync-to-postman.php"
```

## 🔧 **DETAILED SETUP**

### **1. Get Postman API Key**

#### **📝 Steps:**
1. Go to: https://web.postman.co/settings/me/api-keys
2. Click **"Generate API Key"**
3. Name: `YukiMart API Sync`
4. Copy the generated key
5. Add to `.env.postman`:
   ```
   POSTMAN_API_KEY=your_api_key_here
   ```

#### **🔐 Security:**
- Keep API key secure
- Never commit to version control
- Use environment-specific keys

### **2. Get Workspace ID**

#### **Method 1 - From URL:**
1. Go to your Postman workspace
2. Copy ID from URL: `https://web.postman.co/workspace/YOUR_WORKSPACE_ID`
3. Add to `.env.postman`:
   ```
   POSTMAN_WORKSPACE_ID=your_workspace_id
   ```

#### **Method 2 - Use Helper Script:**
```bash
php scripts/get-postman-info.php
```
This will list all your workspaces với IDs.

### **3. Get Collection ID (Optional)**

#### **For Updating Existing Collection:**
1. Go to collection trong Postman
2. Click **"..."** menu → **"View documentation"**
3. Copy ID from URL
4. Add to `.env.postman`:
   ```
   POSTMAN_COLLECTION_ID=your_collection_id
   ```

#### **For Creating New Collection:**
Leave `POSTMAN_COLLECTION_ID` empty or remove the line.

## 📁 **CONFIGURATION FILE**

### **.env.postman Example:**
```bash
# Postman API Configuration
POSTMAN_API_KEY=PMAK-64f1e4c8d9a7b2e3f4g5h6i7j8k9l0m1
POSTMAN_WORKSPACE_ID=8ff7000b-f06f-4622-a1ba-e8391d656905
POSTMAN_COLLECTION_ID=4968736-2d9e5298-7e18-4904-bfca-b71bcb7cddb6
POSTMAN_COLLECTION_NAME=YukiMart API v1 - Complete với Examples

# API Configuration
API_BASE_URL=http://yukimart.local/api/v1
TEST_USER_EMAIL=yukimart@gmail.com
TEST_USER_PASSWORD=123456
```

## 🛠️ **AVAILABLE SCRIPTS**

### **1. Setup Script**
```bash
php scripts/setup-postman-sync.php
```
**Purpose:** Interactive setup cho Postman sync configuration
**Features:**
- ✅ Create .env.postman file
- ✅ Guide API key setup
- ✅ Guide workspace/collection ID setup
- ✅ Test configuration
- ✅ Show next steps

### **2. Sync Script**
```bash
php scripts/sync-to-postman.php
```
**Purpose:** Sync collection lên Postman workspace
**Features:**
- ✅ Auto-detect update vs create
- ✅ Upload collection với examples
- ✅ Update existing collection
- ✅ Save collection ID for future updates
- ✅ Show sync summary với links

### **3. Info Script**
```bash
php scripts/get-postman-info.php
```
**Purpose:** Get workspace và collection information
**Features:**
- ✅ List all workspaces
- ✅ List collections trong workspace
- ✅ Show IDs và URLs
- ✅ User information

## 🔄 **SYNC WORKFLOW**

### **Daily Development Workflow:**
1. **Make API changes** trong Laravel
2. **Update collection examples** (if needed)
3. **Run sync script:**
   ```bash
   php scripts/sync-to-postman.php
   ```
4. **Collection automatically updates** trong Postman workspace
5. **Team sees latest changes** immediately

### **Automated Sync (Optional):**
Add to your CI/CD pipeline:
```yaml
# GitHub Actions example
- name: Sync to Postman
  run: |
    docker exec php83 php scripts/sync-to-postman.php
  env:
    POSTMAN_API_KEY: ${{ secrets.POSTMAN_API_KEY }}
```

## 📊 **SYNC FEATURES**

### **✅ What Gets Synced:**
- ✅ **Complete collection structure** (folders, requests)
- ✅ **All response examples** (16+ examples)
- ✅ **Environment variables** (base_url, api_token, etc.)
- ✅ **Request headers** và authentication
- ✅ **Vietnamese business data** trong examples
- ✅ **Real API responses** captured

### **✅ Sync Modes:**
- **Update Mode:** Updates existing collection
- **Create Mode:** Creates new collection
- **Auto-detect:** Script automatically chooses mode

### **✅ Error Handling:**
- ✅ **API key validation**
- ✅ **Workspace access verification**
- ✅ **Collection existence check**
- ✅ **Network error handling**
- ✅ **Detailed error messages**

## 🎯 **BENEFITS**

### **🚀 Development Speed:**
- **No manual uploads** required
- **Instant sync** to team workspace
- **Always up-to-date** collection
- **Automated workflow** integration

### **👥 Team Collaboration:**
- **Shared workspace** automatically updated
- **Consistent collection** across team
- **Real-time changes** visible to all
- **Version control** through API

### **🔧 Maintenance:**
- **Single source of truth** (your codebase)
- **Automated updates** on API changes
- **No manual collection management**
- **CI/CD integration** ready

## 🔗 **USEFUL LINKS**

### **Postman Resources:**
- **API Keys:** https://web.postman.co/settings/me/api-keys
- **Workspaces:** https://web.postman.co/workspaces
- **API Documentation:** https://learning.postman.com/docs/developer/intro-api/

### **Your Workspace:**
- **Workspace URL:** https://web.postman.co/workspace/8ff7000b-f06f-4622-a1ba-e8391d656905
- **Collection URL:** https://web.postman.co/workspace/8ff7000b-f06f-4622-a1ba-e8391d656905/request/4968736-2d9e5298-7e18-4904-bfca-b71bcb7cddb6

## 🚨 **TROUBLESHOOTING**

### **Common Issues:**

#### **❌ "Missing Postman API Key"**
**Solution:**
1. Check `.env.postman` file exists
2. Verify `POSTMAN_API_KEY=your_key` is set
3. Ensure no spaces around `=`

#### **❌ "API connection failed"**
**Solution:**
1. Verify API key is correct
2. Check internet connection
3. Ensure API key has proper permissions

#### **❌ "Collection not found"**
**Solution:**
1. Verify `POSTMAN_COLLECTION_ID` is correct
2. Check collection exists trong workspace
3. Remove collection ID to create new one

#### **❌ "Workspace access denied"**
**Solution:**
1. Verify `POSTMAN_WORKSPACE_ID` is correct
2. Ensure you have access to workspace
3. Check workspace visibility settings

### **Debug Mode:**
Add debug output to scripts:
```php
// Add to any script for debugging
echo "Debug: API Key = " . substr($apiKey, 0, 8) . "...\n";
echo "Debug: Workspace ID = " . $workspaceId . "\n";
```

## 🎉 **SUCCESS INDICATORS**

### **✅ Setup Success:**
- `.env.postman` file created
- API key validated
- Workspace access confirmed
- Test connection successful

### **✅ Sync Success:**
- Collection uploaded/updated
- Examples visible trong Postman
- Team can access updated collection
- Sync summary displayed

### **✅ Usage Success:**
- Daily sync workflow established
- Team using updated collection
- Flutter development accelerated
- No manual uploads needed

**🎯 Automatic Postman sync enables seamless API development workflow với zero manual intervention!**

---

**🏗️ Auto Postman sync setup guide created by YukiMart Development Team**
**📅 Guide Date**: August 6, 2025
**🔄 Sync Status**: Ready for automatic collection updates
**📱 Team Benefit**: Seamless collaboration với always up-to-date API collection**
