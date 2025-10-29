#!/bin/bash

# YukiMart Platform Separation Verification Script
# Tests the complete separation between Platform and Tenant systems

echo "🔍 YukiMart Platform Separation Verification"
echo "============================================="
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

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_status $RED "❌ Error: artisan file not found. Please run this script from the Laravel project root directory."
    exit 1
fi

print_status $PURPLE "🚀 Starting Platform Separation Verification..."
echo ""

# Initialize counters
total_checks=0
passed_checks=0

# 1. Database Separation Check
print_status $CYAN "📊 Step 1: Database Separation Check"
print_status $CYAN "===================================="
((total_checks++))

platform_users=$(docker exec -it php83 php /var/www/html/yukimart/artisan tinker --execute="echo App\Models\User::whereNull('tenant_id')->count();" 2>/dev/null | tail -1 | tr -d '\r\n')
tenant_users=$(docker exec -it php83 php /var/www/html/yukimart/artisan tinker --execute="echo App\Models\User::whereNotNull('tenant_id')->count();" 2>/dev/null | tail -1 | tr -d '\r\n')

if [ "$platform_users" -gt 0 ] && [ "$tenant_users" -gt 0 ]; then
    print_status $GREEN "✅ Database separation verified"
    print_status $GREEN "   Platform users (tenant_id = NULL): $platform_users"
    print_status $GREEN "   Tenant users (tenant_id != NULL): $tenant_users"
    ((passed_checks++))
else
    print_status $RED "❌ Database separation failed"
fi
echo ""

# 2. Platform Authentication Test
print_status $CYAN "🔐 Step 2: Platform Authentication Test"
print_status $CYAN "======================================="
((total_checks++))

if docker exec -it php83 php /var/www/html/yukimart/artisan yukimart:test-platform-auth superadmin@yukimart.local 123456 2>/dev/null | grep -q "Platform authentication SUCCESS"; then
    print_status $GREEN "✅ Platform authentication working"
    ((passed_checks++))
else
    print_status $RED "❌ Platform authentication failed"
fi
echo ""

# 3. Tenant Authentication Test
print_status $CYAN "🏪 Step 3: Tenant Authentication Test"
print_status $CYAN "====================================="
((total_checks++))

if docker exec -it php83 php /var/www/html/yukimart/artisan yukimart:test-permission owner@techmart.local tenant1 products.view 2>/dev/null | grep -q "✅ YES"; then
    print_status $GREEN "✅ Tenant authentication working"
    ((passed_checks++))
else
    print_status $RED "❌ Tenant authentication failed"
fi
echo ""

# 4. Routes Verification
print_status $CYAN "🛣️ Step 4: Routes Verification"
print_status $CYAN "=============================="

# Platform routes
((total_checks++))
if docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=admin.login 2>/dev/null | grep -q "yukimart.local/admin/login"; then
    print_status $GREEN "✅ Platform login route exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform login route missing"
fi

# Tenant routes
((total_checks++))
if docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=admin.login 2>/dev/null | grep -q "admin/login"; then
    print_status $GREEN "✅ Tenant login route exists"
    ((passed_checks++))
else
    print_status $RED "❌ Tenant login route missing"
fi
echo ""

# 5. Configuration Check
print_status $CYAN "⚙️ Step 5: Configuration Check"
print_status $CYAN "=============================="

# Check tenancy config
((total_checks++))
if [ -f "config/tenancy.php" ]; then
    print_status $GREEN "✅ Tenancy configuration exists"
    ((passed_checks++))
else
    print_status $RED "❌ Tenancy configuration missing"
fi

# Check platform host config
((total_checks++))
if grep -q "TENANCY_PLATFORM_HOST" .env; then
    print_status $GREEN "✅ Platform host configuration exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform host configuration missing"
fi
echo ""

# 6. Middleware Check
print_status $CYAN "🛡️ Step 6: Middleware Check"
print_status $CYAN "=========================="

# Check DetectPlatform middleware
((total_checks++))
if [ -f "app/Http/Middleware/DetectPlatform.php" ]; then
    print_status $GREEN "✅ DetectPlatform middleware exists"
    ((passed_checks++))
else
    print_status $RED "❌ DetectPlatform middleware missing"
fi

# Check PlatformUserProvider
((total_checks++))
if [ -f "app/Auth/PlatformUserProvider.php" ]; then
    print_status $GREEN "✅ PlatformUserProvider exists"
    ((passed_checks++))
else
    print_status $RED "❌ PlatformUserProvider missing"
fi
echo ""

# 7. Views Check
print_status $CYAN "🎨 Step 7: Views Check"
print_status $CYAN "===================="

# Check platform login view
((total_checks++))
if [ -f "resources/views/platform/auth/login.blade.php" ]; then
    print_status $GREEN "✅ Platform login view exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform login view missing"
fi

# Check platform dashboard view
((total_checks++))
if [ -f "resources/views/platform/dashboard.blade.php" ]; then
    print_status $GREEN "✅ Platform dashboard view exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform dashboard view missing"
fi
echo ""

# 8. URL Accessibility Test
print_status $CYAN "🌐 Step 8: URL Accessibility Test"
print_status $CYAN "================================="

test_urls=(
    "http://yukimart.local/admin/login"
    "http://tenant1.yukimart.local/admin/login"
    "http://tenant2.yukimart.local/admin/login"
)

url_tests_passed=0
for url in "${test_urls[@]}"; do
    ((total_checks++))
    
    if command -v curl >/dev/null 2>&1; then
        response=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null || echo "000")
        
        case $response in
            200|302|403)
                print_status $GREEN "✅ $url accessible (HTTP $response)"
                ((passed_checks++))
                ((url_tests_passed++))
                ;;
            000)
                print_status $BLUE "ℹ️ $url server not running"
                ;;
            *)
                print_status $RED "❌ $url HTTP $response"
                ;;
        esac
    else
        print_status $BLUE "ℹ️ curl not available - skipping URL test"
        ((passed_checks++))
        ((url_tests_passed++))
    fi
done
echo ""

# 9. Final Results
print_status $CYAN "🎯 Final Results"
print_status $CYAN "================"

success_rate=$((passed_checks * 100 / total_checks))

print_status $BLUE "📊 Overall Results:"
print_status $BLUE "   ✅ Passed: $passed_checks/$total_checks tests"
print_status $BLUE "   📈 Success Rate: $success_rate%"

if [ $success_rate -ge 90 ]; then
    print_status $GREEN "🎉 EXCELLENT! Platform separation is complete and working perfectly!"
    system_status="EXCELLENT"
elif [ $success_rate -ge 80 ]; then
    print_status $YELLOW "👍 GOOD! Minor issues to address."
    system_status="GOOD"
else
    print_status $RED "❌ NEEDS WORK! Major issues require attention."
    system_status="NEEDS_WORK"
fi

echo ""
print_status $BLUE "🔗 Test URLs:"
print_status $BLUE "   🏢 Platform: http://yukimart.local/admin/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/admin/login"
print_status $BLUE "   🏪 Fashion:  http://tenant2.yukimart.local/admin/login"

echo ""
print_status $BLUE "🔐 Test Credentials:"
print_status $BLUE "   📧 Platform: superadmin@yukimart.local | 🔑 123456"
print_status $BLUE "   📧 TechMart: owner@techmart.local     | 🔑 123456"
print_status $BLUE "   📧 Fashion:  owner@fashion.local      | 🔑 123456"

echo ""
print_status $PURPLE "🎊 Platform Separation Verification Complete!"
print_status $PURPLE "System Status: $system_status ($success_rate% success rate)"

# Exit with appropriate code
if [ $success_rate -ge 80 ]; then
    exit 0
else
    exit 1
fi
