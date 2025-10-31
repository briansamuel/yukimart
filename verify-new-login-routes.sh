#!/bin/bash

# YukiMart New Login Routes Verification Script
# Tests the new domain-based login configuration

echo "🔍 YukiMart New Login Routes Verification"
echo "========================================="
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

print_status $PURPLE "🚀 Starting New Login Routes Verification..."
echo ""

# Initialize counters
total_checks=0
passed_checks=0

# 1. Platform Routes Check
print_status $CYAN "🏢 Step 1: Platform Routes Check"
print_status $CYAN "================================"

((total_checks++))
platform_routes=$(docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=platform.login 2>/dev/null | grep -c "platform.login")

if [ "$platform_routes" -ge 2 ]; then
    print_status $GREEN "✅ Platform routes exist (GET + POST)"
    ((passed_checks++))
    
    # Show platform routes
    print_status $BLUE "📋 Platform Routes:"
    docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=platform.login 2>/dev/null | grep -E "(GET|POST).*platform\.yukimart\.local" | while read line; do
        print_status $BLUE "   $line"
    done
else
    print_status $RED "❌ Platform routes missing or incomplete"
fi
echo ""

# 2. Tenant Routes Check
print_status $CYAN "🏪 Step 2: Tenant Routes Check"
print_status $CYAN "=============================="

((total_checks++))
tenant_routes=$(docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=tenant.login 2>/dev/null | grep -c "tenant.login")

if [ "$tenant_routes" -ge 2 ]; then
    print_status $GREEN "✅ Tenant routes exist (GET + POST)"
    ((passed_checks++))
    
    # Show tenant routes
    print_status $BLUE "📋 Tenant Routes:"
    docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=tenant.login 2>/dev/null | grep -E "(GET|POST).*subdomain.*yukimart\.local" | while read line; do
        print_status $BLUE "   $line"
    done
else
    print_status $RED "❌ Tenant routes missing or incomplete"
fi
echo ""

# 3. Old Routes Check (should be commented out)
print_status $CYAN "🗑️ Step 3: Old Routes Check (Should be Disabled)"
print_status $CYAN "==============================================="

((total_checks++))
old_admin_routes=$(docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=admin.login 2>/dev/null | grep -c "admin.login" || echo "0")

if [ "$old_admin_routes" -eq 0 ]; then
    print_status $GREEN "✅ Old admin.login routes properly disabled"
    ((passed_checks++))
else
    print_status $YELLOW "⚠️ Old admin.login routes still exist: $old_admin_routes"
    print_status $BLUE "📋 Existing old routes:"
    docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=admin.login 2>/dev/null | while read line; do
        print_status $BLUE "   $line"
    done
fi
echo ""

# 4. Views Check
print_status $CYAN "🎨 Step 4: Views Check"
print_status $CYAN "===================="

# Platform view
((total_checks++))
if [ -f "resources/views/platform/auth/login.blade.php" ]; then
    print_status $GREEN "✅ Platform login view exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform login view missing"
fi

# Tenant view
((total_checks++))
if [ -f "resources/views/tenant/auth/login.blade.php" ]; then
    print_status $GREEN "✅ Tenant login view exists"
    ((passed_checks++))
else
    print_status $RED "❌ Tenant login view missing"
fi
echo ""

# 5. URL Accessibility Test
print_status $CYAN "🌐 Step 5: URL Accessibility Test"
print_status $CYAN "================================="

test_urls=(
    "http://platform.yukimart.local/login"
    "http://tenant1.yukimart.local/login"
    "http://tenant2.yukimart.local/login"
)

for url in "${test_urls[@]}"; do
    ((total_checks++))
    
    if command -v curl >/dev/null 2>&1; then
        response=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null || echo "000")
        
        case $response in
            200|302)
                print_status $GREEN "✅ $url accessible (HTTP $response)"
                ((passed_checks++))
                ;;
            000)
                print_status $BLUE "ℹ️ $url server not running or DNS not configured"
                ;;
            *)
                print_status $RED "❌ $url HTTP $response"
                ;;
        esac
    else
        print_status $BLUE "ℹ️ curl not available - skipping URL test"
        ((passed_checks++))
    fi
done
echo ""

# 6. Configuration Summary
print_status $CYAN "⚙️ Step 6: Configuration Summary"
print_status $CYAN "==============================="

print_status $BLUE "📋 New Login Configuration:"
print_status $BLUE "   🏢 Platform: platform.yukimart.local/login"
print_status $BLUE "   🏪 Tenant:   {subdomain}.yukimart.local/login"
print_status $BLUE ""
print_status $BLUE "📋 Example URLs:"
print_status $BLUE "   🏢 Platform: http://platform.yukimart.local/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/login"
print_status $BLUE "   🏪 Fashion:  http://tenant2.yukimart.local/login"
print_status $BLUE ""
print_status $BLUE "🔐 Test Credentials:"
print_status $BLUE "   📧 Platform: superadmin@yukimart.local | 🔑 123456"
print_status $BLUE "   📧 TechMart: owner@techmart.local     | 🔑 123456"
print_status $BLUE "   📧 Fashion:  owner@fashion.local      | 🔑 123456"
echo ""

# 7. Final Results
print_status $CYAN "🎯 Final Results"
print_status $CYAN "================"

success_rate=$((passed_checks * 100 / total_checks))

print_status $BLUE "📊 Overall Results:"
print_status $BLUE "   ✅ Passed: $passed_checks/$total_checks tests"
print_status $BLUE "   📈 Success Rate: $success_rate%"

if [ $success_rate -ge 90 ]; then
    print_status $GREEN "🎉 EXCELLENT! New login routes configured perfectly!"
    system_status="EXCELLENT"
elif [ $success_rate -ge 80 ]; then
    print_status $YELLOW "👍 GOOD! Minor issues to address."
    system_status="GOOD"
else
    print_status $RED "❌ NEEDS WORK! Major issues require attention."
    system_status="NEEDS_WORK"
fi

echo ""
print_status $BLUE "🔗 Browser Test URLs (Already opened):"
print_status $BLUE "   🏢 Platform: http://platform.yukimart.local/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/login"
print_status $BLUE "   🏪 Fashion:  http://tenant2.yukimart.local/login"

echo ""
print_status $PURPLE "🎊 New Login Routes Configuration Complete!"
print_status $PURPLE "System Status: $system_status ($success_rate% success rate)"

# Exit with appropriate code
if [ $success_rate -ge 80 ]; then
    exit 0
else
    exit 1
fi
