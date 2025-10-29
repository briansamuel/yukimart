# YukiMart Postman Artisan Sync Command

## 🚀 Overview

The `postman:sync` artisan command automatically syncs your Laravel application configuration and environment variables to Postman collections, ensuring your API testing environment stays up-to-date with your development environment.

## 📋 Command Syntax

```bash
php artisan postman:sync [options]
```

## 🔧 Available Options

| Option | Description | Default | Example |
|--------|-------------|---------|---------|
| `--type` | Collection type to sync | `all` | `--type=fcm` |
| `--sync-env` | Sync environment variables from .env | `false` | `--sync-env` |
| `--api-key` | Postman API key (future use) | - | `--api-key=PMAK-xxx` |
| `--collection-id` | Postman collection ID (future use) | - | `--collection-id=123` |
| `--capture-examples` | Capture live API examples | `false` | `--capture-examples` |
| `--update-collection` | Update existing collection | `false` | `--update-collection` |
| `--export-only` | Only export to local files | `false` | `--export-only` |

## 🎯 Collection Types

### **1. FCM Collection (`--type=fcm`)**
Syncs Firebase Cloud Messaging related configurations:
- Firebase project ID from config
- FCM service account settings
- Test FCM tokens
- YukiMart base URL
- Admin credentials

### **2. API Collection (`--type=api`)**
Syncs general API configurations:
- Application URLs
- Database settings
- API endpoints
- Authentication settings

### **3. All Collections (`--type=all`)**
Syncs both FCM and API collections.

## 🚀 Usage Examples

### **Basic FCM Sync:**
```bash
php artisan postman:sync --type=fcm
```

### **Sync with Environment Variables:**
```bash
php artisan postman:sync --type=fcm --sync-env
```

### **Sync All Collections:**
```bash
php artisan postman:sync --type=all --sync-env
```

### **Export Only (No Remote Update):**
```bash
php artisan postman:sync --type=fcm --export-only
```

## 📊 What Gets Synced

### **Environment Variables Synced:**

#### **From .env and Config:**
```
yukimart_base_url = APP_URL from .env
yukimart_email = yukimart@gmail.com (default admin)
yukimart_password = 123456 (default admin password)
firebase_project_id = FCM_PROJECT_ID from config
firebase_service_account_email = FCM service account email
app_name = APP_NAME from .env
app_env = APP_ENV from .env
database_name = DB_DATABASE from .env
test_fcm_token = Predefined test token
```

#### **Auto-Generated:**
```
timestamp = Current timestamp (auto-generated)
yukimart_api_token = (filled after login)
firebase_access_token = (generated via script)
```

### **Collection Updates:**
- Collection metadata (`_updated_at`, `_synced_from_env`)
- Request URLs updated with current base URL
- Environment variable references
- Authentication headers

## 📁 Files Updated

### **FCM Collection Files:**
- `postman/YukiMart-FCM-API.postman_collection.json`
- `postman/YukiMart-FCM.postman_environment.json`

### **API Collection Files:**
- `postman/YukiMart-API.postman_collection.json` (if exists)
- `postman/YukiMart-Environment.postman_environment.json` (if exists)

## 🔄 Sync Process

### **1. Environment Variable Sync:**
```
1. Read current .env and config values
2. Load existing Postman environment file
3. Update matching variables
4. Add new variables if not found
5. Save updated environment file
```

### **2. Collection Sync:**
```
1. Load existing Postman collection
2. Update collection metadata
3. Update request URLs with current base URL
4. Update variable references
5. Save updated collection file
```

## ✅ Success Output

```bash
🚀 Starting Postman Collection Sync...
📋 Collection type: fcm
🔄 Syncing environment variables...
✅ Environment variables synced successfully!
📁 Environment saved to: /path/to/YukiMart-FCM.postman_environment.json
🔥 Syncing FCM collection...
✅ FCM collection synced successfully!
📁 Collection saved to: /path/to/YukiMart-FCM-API.postman_collection.json
📤 Collection files updated locally
✅ Postman sync completed successfully!
```

## 🔧 Configuration

### **Required Config Values:**

#### **In `config/services.php`:**
```php
'fcm' => [
    'project_id' => env('FCM_PROJECT_ID', 'yukimart-pos-system'),
    'service_account_email' => env('FCM_SERVICE_ACCOUNT_EMAIL', ''),
    // ... other FCM config
],
```

#### **In `.env`:**
```env
APP_URL=http://yukimart.local
APP_NAME=YukiMart
APP_ENV=local
DB_DATABASE=yukimart
FCM_PROJECT_ID=yukimart-pos-system
FCM_SERVICE_ACCOUNT_EMAIL=firebase-adminsdk-xxx@yukimart-pos-system.iam.gserviceaccount.com
```

## 🧪 Testing After Sync

### **1. Import Updated Collections:**
```
1. Open Postman
2. Re-import updated collection files
3. Select updated environment
4. Verify variables are populated
```

### **2. Test Authentication:**
```
1. Run: Authentication > Login to YukiMart
2. Verify: yukimart_api_token is auto-filled
3. Test: Any authenticated endpoint
```

### **3. Test FCM Endpoints:**
```
1. Run: YukiMart FCM API > Send Test Notification
2. Verify: Notification received on device
3. Check: Logs for successful delivery
```

## 🔍 Troubleshooting

### **Command Fails:**
```bash
# Check if collections exist
ls -la postman/

# Check Laravel config
php artisan config:show services.fcm

# Check environment
php artisan env
```

### **Environment Not Syncing:**
```bash
# Force environment sync
php artisan postman:sync --type=fcm --sync-env

# Check file permissions
chmod 644 postman/*.json
```

### **Collection Not Updating:**
```bash
# Check collection file exists
cat postman/YukiMart-FCM-API.postman_collection.json | head -20

# Force collection update
php artisan postman:sync --type=fcm --update-collection
```

## 📅 Automation

### **Add to Deployment Script:**
```bash
#!/bin/bash
# After environment setup
php artisan postman:sync --type=all --sync-env
```

### **Add to Git Hooks:**
```bash
# .git/hooks/post-merge
#!/bin/bash
php artisan postman:sync --type=fcm --sync-env
```

### **Schedule Regular Sync:**
```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('postman:sync --type=all --sync-env')
             ->daily()
             ->at('02:00');
}
```

## 🎯 Best Practices

### **1. Regular Syncing:**
- Sync after environment changes
- Sync after config updates
- Sync before major testing sessions

### **2. Version Control:**
- Commit updated collection files
- Track environment template files
- Document sync procedures

### **3. Team Collaboration:**
- Share sync commands in README
- Automate sync in CI/CD
- Keep collections up-to-date

---

**🎉 Your Postman collections are now automatically synced with your Laravel environment!**

**Last Updated:** 2025-08-08  
**Command Version:** 1.0.0  
**Compatible with:** Laravel 10+, Postman 10+
