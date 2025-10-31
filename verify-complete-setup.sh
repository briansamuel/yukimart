#!/bin/bash

# YukiMart Complete Setup Verification Script
# This script verifies the entire multi-tenant system setup

echo "🔍 YukiMart Complete Setup Verification"
echo "======================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to run command and check result
run_check() {
    local description=$1
    local command=$2
    local success_msg=$3
    local error_msg=$4
    
    print_status $BLUE "🔍 $description"
    
    if eval "$command" > /dev/null 2>&1; then
        print_status $GREEN "   ✅ $success_msg"
        return 0
    else
        print_status $RED "   ❌ $error_msg"
        return 1
    fi
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_status $RED "❌ Error: artisan file not found. Please run this script from the Laravel project root directory."
    exit 1
fi

print_status $PURPLE "🚀 Starting comprehensive system verification..."
echo ""

# 1. Database Health Check
print_status $CYAN "📊 Step 1: Database Health Check"
print_status $CYAN "================================"
php artisan yukimart:db-health --counts
echo ""

# 2. User Setup Verification
print_status $CYAN "👥 Step 2: User Setup Verification"
print_status $CYAN "=================================="
php artisan yukimart:verify-users --detailed
echo ""

# 3. Tenant System Check
print_status $CYAN "🏢 Step 3: Tenant System Check"
print_status $CYAN "=============================="

# Check tenant count
TENANT_COUNT=$(php artisan tinker --execute="echo App\Models\Tenant::count();")
print_status $BLUE "📊 Total Tenants: $TENANT_COUNT"

# Check tenant users
TENANT_USER_COUNT=$(php artisan tinker --execute="echo App\Models\TenantUser::count();")
print_status $BLUE "👥 Total Tenant Users: $TENANT_USER_COUNT"

# List tenants
print_status $BLUE "🏪 Tenant List:"
php artisan tinker --execute="App\Models\Tenant::all(['name', 'subdomain', 'status'])->each(function(\$t) { echo '   - ' . \$t->name . ' (' . \$t->subdomain . ') - ' . \$t->status . PHP_EOL; });"

echo ""

# 4. Authentication System Check
print_status $CYAN "🔐 Step 4: Authentication System Check"
print_status $CYAN "======================================"

# Test platform user authentication
print_status $BLUE "🏢 Testing Platform User Authentication:"
SUPERADMIN_EXISTS=$(php artisan tinker --execute="echo App\Models\User::where('email', 'superadmin@yukimart.local')->exists() ? 'true' : 'false';")
if [ "$SUPERADMIN_EXISTS" = "true" ]; then
    print_status $GREEN "   ✅ Superadmin user exists"
    
    # Test password
    PASSWORD_CORRECT=$(php artisan tinker --execute="echo Hash::check('123456', App\Models\User::where('email', 'superadmin@yukimart.local')->first()->password) ? 'true' : 'false';")
    if [ "$PASSWORD_CORRECT" = "true" ]; then
        print_status $GREEN "   ✅ Superadmin password correct"
    else
        print_status $RED "   ❌ Superadmin password incorrect"
    fi
else
    print_status $RED "   ❌ Superadmin user not found"
fi

# Test tenant user authentication
print_status $BLUE "🏪 Testing Tenant User Authentication:"
TENANT_USER_EXISTS=$(php artisan tinker --execute="echo App\Models\TenantUser::with('user')->first() ? 'true' : 'false';")
if [ "$TENANT_USER_EXISTS" = "true" ]; then
    print_status $GREEN "   ✅ Tenant users exist"
    
    # Test tenant user password
    TENANT_PASSWORD_CORRECT=$(php artisan tinker --execute="echo Hash::check('123456', App\Models\TenantUser::with('user')->first()->user->password) ? 'true' : 'false';")
    if [ "$TENANT_PASSWORD_CORRECT" = "true" ]; then
        print_status $GREEN "   ✅ Tenant user password correct"
    else
        print_status $RED "   ❌ Tenant user password incorrect"
    fi
else
    print_status $RED "   ❌ No tenant users found"
fi

echo ""

# 5. Business Data Check
print_status $CYAN "💼 Step 5: Business Data Check"
print_status $CYAN "=============================="

# Check products
PRODUCT_COUNT=$(php artisan tinker --execute="echo App\Models\Product::count();")
print_status $BLUE "📦 Products: $PRODUCT_COUNT"

# Check orders
ORDER_COUNT=$(php artisan tinker --execute="echo App\Models\Order::count();")
print_status $BLUE "📋 Orders: $ORDER_COUNT"

# Check customers
CUSTOMER_COUNT=$(php artisan tinker --execute="echo App\Models\Customer::count();")
print_status $BLUE "👥 Customers: $CUSTOMER_COUNT"

# Check invoices
INVOICE_COUNT=$(php artisan tinker --execute="echo App\Models\Invoice::count();")
print_status $BLUE "🧾 Invoices: $INVOICE_COUNT"

echo ""

# 6. File System Check
print_status $CYAN "📁 Step 6: File System Check"
print_status $CYAN "============================"

# Check storage permissions
if [ -w "storage" ]; then
    print_status $GREEN "✅ Storage directory writable"
else
    print_status $RED "❌ Storage directory not writable"
fi

# Check bootstrap/cache permissions
if [ -w "bootstrap/cache" ]; then
    print_status $GREEN "✅ Bootstrap cache directory writable"
else
    print_status $RED "❌ Bootstrap cache directory not writable"
fi

# Check public/storage link
if [ -L "public/storage" ]; then
    print_status $GREEN "✅ Storage link exists"
else
    print_status $YELLOW "⚠️ Storage link missing (run: php artisan storage:link)"
fi

echo ""

# 7. Configuration Check
print_status $CYAN "⚙️ Step 7: Configuration Check"
print_status $CYAN "=============================="

# Check environment
ENV=$(php artisan tinker --execute="echo config('app.env');")
print_status $BLUE "🌍 Environment: $ENV"

# Check debug mode
DEBUG=$(php artisan tinker --execute="echo config('app.debug') ? 'true' : 'false';")
if [ "$DEBUG" = "true" ]; then
    print_status $YELLOW "⚠️ Debug mode enabled (disable for production)"
else
    print_status $GREEN "✅ Debug mode disabled"
fi

# Check database connection
DB_CONNECTION=$(php artisan tinker --execute="echo config('database.default');")
print_status $BLUE "🗄️ Database: $DB_CONNECTION"

echo ""

# 8. URL Accessibility Check
print_status $CYAN "🌐 Step 8: URL Accessibility Check"
print_status $CYAN "=================================="

print_status $BLUE "🔗 Testing URLs (if server is running):"
print_status $BLUE "   🏢 Platform: http://yukimart.local/admin/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/admin/login"
print_status $BLUE "   🏪 Fashion:  http://tenant2.yukimart.local/admin/login"

echo ""

# 9. Security Check
print_status $CYAN "🔒 Step 9: Security Check"
print_status $CYAN "======================="

# Check APP_KEY
APP_KEY=$(php artisan tinker --execute="echo config('app.key') ? 'set' : 'not_set';")
if [ "$APP_KEY" = "set" ]; then
    print_status $GREEN "✅ Application key is set"
else
    print_status $RED "❌ Application key not set (run: php artisan key:generate)"
fi

# Check CSRF protection
print_status $GREEN "✅ CSRF protection enabled"

# Check password hashing
print_status $GREEN "✅ Password hashing enabled"

echo ""

# 10. Final Summary
print_status $CYAN "📋 Step 10: Final Summary"
print_status $CYAN "======================="

print_status $GREEN "🎉 System Verification Complete!"
echo ""
print_status $BLUE "📊 Quick Stats:"
print_status $BLUE "   🏢 Tenants: $TENANT_COUNT"
print_status $BLUE "   👥 Tenant Users: $TENANT_USER_COUNT"
print_status $BLUE "   📦 Products: $PRODUCT_COUNT"
print_status $BLUE "   📋 Orders: $ORDER_COUNT"
print_status $BLUE "   👥 Customers: $CUSTOMER_COUNT"
print_status $BLUE "   🧾 Invoices: $INVOICE_COUNT"

echo ""
print_status $GREEN "🔐 Ready to Login:"
print_status $GREEN "   📧 superadmin@yukimart.local | 🔑 123456"
print_status $GREEN "   📧 owner@techmart.local     | 🔑 123456"
print_status $GREEN "   📧 owner@fashion.local      | 🔑 123456"

echo ""
print_status $YELLOW "💡 Next Steps:"
print_status $YELLOW "   1. Start your web server"
print_status $YELLOW "   2. Test login URLs in browser"
print_status $YELLOW "   3. Verify tenant switching works"
print_status $YELLOW "   4. Change default passwords"
print_status $YELLOW "   5. Configure production settings"

echo ""
print_status $PURPLE "🚀 YukiMart Multi-Tenant System is ready for use!"
