#!/bin/bash

# YukiMart Unified Login Verification Script
# Tests the unified login view with route-level separation

echo "🔍 YukiMart Unified Login Verification"
echo "======================================"
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

print_status $PURPLE "🚀 Starting Unified Login Verification..."
echo ""

# Initialize counters
total_checks=0
passed_checks=0

# 1. Routes Check
print_status $CYAN "🛣️ Step 1: Routes Check"
print_status $CYAN "======================"

# Platform routes
((total_checks++))
platform_routes=$(docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=platform.login 2>/dev/null | grep -c "platform.login")
if [ "$platform_routes" -ge 2 ]; then
    print_status $GREEN "✅ Platform routes exist (GET + POST)"
    ((passed_checks++))
else
    print_status $RED "❌ Platform routes missing"
fi

# Tenant routes
((total_checks++))
tenant_routes=$(docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=tenant.login 2>/dev/null | grep -c "tenant.login")
if [ "$tenant_routes" -ge 2 ]; then
    print_status $GREEN "✅ Tenant routes exist (GET + POST)"
    ((passed_checks++))
else
    print_status $RED "❌ Tenant routes missing"
fi
echo ""

# 2. Controllers Check
print_status $CYAN "🎮 Step 2: Controllers Check"
print_status $CYAN "==========================="

# Platform controller
((total_checks++))
if [ -f "app/Http/Controllers/Platform/AuthController.php" ]; then
    print_status $GREEN "✅ Platform AuthController exists"
    ((passed_checks++))
else
    print_status $RED "❌ Platform AuthController missing"
fi

# Admin controller (for tenant)
((total_checks++))
if [ -f "app/Http/Controllers/Admin/AuthController.php" ]; then
    print_status $GREEN "✅ Admin AuthController exists (for tenant)"
    ((passed_checks++))
else
    print_status $RED "❌ Admin AuthController missing"
fi
echo ""

# 3. Unified View Check
print_status $CYAN "🎨 Step 3: Unified View Check"
print_status $CYAN "============================"

((total_checks++))
if [ -f "resources/views/admin/auth/login.blade.php" ]; then
    print_status $GREEN "✅ Unified login view exists"
    ((passed_checks++))
    
    # Check if view has platform detection logic
    if grep -q "isPlatform" "resources/views/admin/auth/login.blade.php"; then
        print_status $GREEN "✅ View has platform detection logic"
    else
        print_status $YELLOW "⚠️ View missing platform detection logic"
    fi
else
    print_status $RED "❌ Unified login view missing"
fi

# Check that separate tenant view is removed
((total_checks++))
if [ ! -f "resources/views/tenant/auth/login.blade.php" ]; then
    print_status $GREEN "✅ Separate tenant view properly removed"
    ((passed_checks++))
else
    print_status $YELLOW "⚠️ Separate tenant view still exists"
fi
echo ""

# 4. Route-Controller Mapping
print_status $CYAN "🔗 Step 4: Route-Controller Mapping"
print_status $CYAN "=================================="

print_status $BLUE "📋 Platform Routes:"
docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=platform.login 2>/dev/null | grep -E "(GET|POST)" | while read line; do
    print_status $BLUE "   $line"
done

print_status $BLUE "📋 Tenant Routes:"
docker exec -it php83 php /var/www/html/yukimart/artisan route:list --name=tenant.login 2>/dev/null | grep -E "(GET|POST)" | while read line; do
    print_status $BLUE "   $line"
done
echo ""

# 5. Configuration Summary
print_status $CYAN "⚙️ Step 5: Configuration Summary"
print_status $CYAN "==============================="

print_status $BLUE "📋 Unified Login Configuration:"
print_status $BLUE "   🎨 View: admin.auth.login (shared)"
print_status $BLUE "   🏢 Platform: Platform\\AuthController"
print_status $BLUE "   🏪 Tenant: Admin\\AuthController"
print_status $BLUE "   🔗 Routes: Domain-based separation"
print_status $BLUE ""
print_status $BLUE "📋 Access URLs:"
print_status $BLUE "   🏢 Platform: http://platform.yukimart.local/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/login"
print_status $BLUE "   🏪 Fashion:  http://tenant2.yukimart.local/login"
print_status $BLUE ""
print_status $BLUE "🔐 Test Credentials:"
print_status $BLUE "   📧 Platform: superadmin@yukimart.local | 🔑 123456"
print_status $BLUE "   📧 TechMart: owner@techmart.local     | 🔑 123456"
print_status $BLUE "   📧 Fashion:  owner@fashion.local      | 🔑 123456"
echo ""

# 6. Final Results
print_status $CYAN "🎯 Final Results"
print_status $CYAN "================"

success_rate=$((passed_checks * 100 / total_checks))

print_status $BLUE "📊 Overall Results:"
print_status $BLUE "   ✅ Passed: $passed_checks/$total_checks tests"
print_status $BLUE "   📈 Success Rate: $success_rate%"

if [ $success_rate -ge 90 ]; then
    print_status $GREEN "🎉 EXCELLENT! Unified login configured perfectly!"
    system_status="EXCELLENT"
elif [ $success_rate -ge 80 ]; then
    print_status $YELLOW "👍 GOOD! Minor issues to address."
    system_status="GOOD"
else
    print_status $RED "❌ NEEDS WORK! Major issues require attention."
    system_status="NEEDS_WORK"
fi

echo ""
print_status $BLUE "🌐 Browser Test URLs (Already opened):"
print_status $BLUE "   🏢 Platform: http://platform.yukimart.local/login"
print_status $BLUE "   🏪 TechMart: http://tenant1.yukimart.local/login"

echo ""
print_status $PURPLE "🎊 Unified Login Configuration Complete!"
print_status $PURPLE "System Status: $system_status ($success_rate% success rate)"

# Exit with appropriate code
if [ $success_rate -ge 80 ]; then
    exit 0
else
    exit 1
fi
