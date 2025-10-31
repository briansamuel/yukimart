#!/bin/bash

# YukiMart Tenant Features Test Runner
# This script sets up and runs comprehensive tests for tenant features

echo "🚀 YukiMart Tenant Features Test Runner"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test configuration
BASE_URL="http://yukimart.local"
TENANT1_URL="http://tenant1.yukimart.local"
TENANT2_URL="http://tenant2.yukimart.local"

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to check if URL is accessible
check_url() {
    local url=$1
    local name=$2
    
    print_status $BLUE "🔍 Checking $name ($url)..."
    
    if curl -s --head "$url" | head -n 1 | grep -q "200 OK"; then
        print_status $GREEN "  ✅ $name is accessible"
        return 0
    else
        print_status $RED "  ❌ $name is not accessible"
        return 1
    fi
}

# Function to test specific routes
test_routes() {
    local base_url=$1
    local tenant_name=$2
    
    print_status $BLUE "🧪 Testing routes for $tenant_name..."
    
    local routes=(
        "/admin/login"
        "/admin/dashboard"
        "/admin/products"
        "/admin/products/add"
        "/admin/orders"
        "/admin/orders/add"
        "/admin/quick-order"
        "/admin/invoices"
        "/admin/invoices/create"
        "/admin/returns"
        "/admin/returns/create"
        "/admin/payments"
        "/admin/customers"
        "/admin/suppliers"
    )
    
    local success_count=0
    local total_count=${#routes[@]}
    
    for route in "${routes[@]}"; do
        local full_url="${base_url}${route}"
        
        # Test route accessibility (expect redirect to login for protected routes)
        local status_code=$(curl -s -o /dev/null -w "%{http_code}" "$full_url")
        
        if [[ "$status_code" == "200" || "$status_code" == "302" ]]; then
            print_status $GREEN "    ✅ $route ($status_code)"
            ((success_count++))
        else
            print_status $RED "    ❌ $route ($status_code)"
        fi
    done
    
    print_status $YELLOW "  📊 Routes test: $success_count/$total_count passed"
    
    if [ $success_count -eq $total_count ]; then
        return 0
    else
        return 1
    fi
}

# Function to setup test environment
setup_test_env() {
    print_status $BLUE "🔧 Setting up test environment..."
    
    # Check if we're in the right directory
    if [ ! -f "package.json" ]; then
        print_status $RED "❌ package.json not found. Please run from test-case/tenant-features directory"
        exit 1
    fi
    
    # Install dependencies if needed
    if [ ! -d "node_modules" ]; then
        print_status $YELLOW "📦 Installing dependencies..."
        npm install
        
        if [ $? -eq 0 ]; then
            print_status $GREEN "  ✅ Dependencies installed"
        else
            print_status $RED "  ❌ Failed to install dependencies"
            exit 1
        fi
    fi
    
    # Setup Playwright if needed
    if [ ! -d "node_modules/playwright" ]; then
        print_status $YELLOW "🎭 Setting up Playwright..."
        npx playwright install chromium
        
        if [ $? -eq 0 ]; then
            print_status $GREEN "  ✅ Playwright setup complete"
        else
            print_status $RED "  ❌ Failed to setup Playwright"
            exit 1
        fi
    fi
}

# Function to run Playwright tests
run_playwright_tests() {
    print_status $BLUE "🎭 Running Playwright tests..."
    
    # Create results directory
    mkdir -p results
    
    # Run the main test script
    node tenant-feature-test.js > results/test-output.log 2>&1
    
    local exit_code=$?
    
    if [ $exit_code -eq 0 ]; then
        print_status $GREEN "  ✅ Playwright tests completed successfully"
    else
        print_status $RED "  ❌ Playwright tests failed (exit code: $exit_code)"
    fi
    
    # Display results if available
    if [ -f "tenant-test-errors.json" ]; then
        print_status $BLUE "📊 Test results summary:"
        
        # Extract key information from results
        local techmart_errors=$(jq '.results.techmart | if type == "object" then [.products.errors[], .orders.errors[], .invoices.errors[], .returns.errors[], .payments.errors[]] | length else 0 end' tenant-test-errors.json 2>/dev/null || echo "0")
        local fashion_errors=$(jq '.results.fashion | if type == "object" then [.products.errors[], .orders.errors[], .invoices.errors[], .returns.errors[], .payments.errors[]] | length else 0 end' tenant-test-errors.json 2>/dev/null || echo "0")
        
        print_status $YELLOW "  TechMart errors: $techmart_errors"
        print_status $YELLOW "  Fashion errors: $fashion_errors"
        
        # Move results to results directory
        mv tenant-test-errors.json results/
        
        print_status $BLUE "  📁 Detailed results saved to results/tenant-test-errors.json"
    fi
    
    return $exit_code
}

# Function to generate summary report
generate_report() {
    print_status $BLUE "📝 Generating test report..."
    
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local report_file="results/test-summary-$(date '+%Y%m%d-%H%M%S').md"
    
    cat > "$report_file" << EOF
# YukiMart Tenant Features Test Summary

**Test Date**: $timestamp
**Test Environment**: Local Development
**Base URL**: $BASE_URL

## Test Results

### Environment Check
- Base URL: $BASE_URL
- Tenant 1: $TENANT1_URL  
- Tenant 2: $TENANT2_URL

### Route Testing
Routes tested for both tenants:
- Login page
- Dashboard
- Products (list, create)
- Orders (list, create, quick order)
- Invoices (list, create)
- Returns (list, create)
- Payments (list)
- Customers
- Suppliers

### Playwright Testing
Automated browser testing performed for:
- User authentication
- Page navigation
- Form interactions
- Data loading
- Error handling

## Files Generated
- \`results/test-output.log\` - Full test execution log
- \`results/tenant-test-errors.json\` - Detailed error report
- \`$report_file\` - This summary report

## Next Steps
1. Review detailed error report
2. Fix identified issues
3. Re-run tests to verify fixes
4. Update documentation

---
Generated by YukiMart Test Runner
EOF

    print_status $GREEN "  ✅ Report saved to $report_file"
}

# Main execution
main() {
    print_status $BLUE "Starting YukiMart tenant features testing..."
    
    # Step 1: Setup test environment
    setup_test_env
    
    # Step 2: Check URL accessibility
    print_status $BLUE "\n🌐 Checking URL accessibility..."
    
    local urls_ok=true
    
    check_url "$BASE_URL" "Base URL" || urls_ok=false
    check_url "$TENANT1_URL" "TechMart Tenant" || urls_ok=false
    check_url "$TENANT2_URL" "Fashion Tenant" || urls_ok=false
    
    if [ "$urls_ok" = false ]; then
        print_status $RED "❌ Some URLs are not accessible. Please check your local environment."
        print_status $YELLOW "💡 Make sure your local server is running and DNS is configured correctly."
        exit 1
    fi
    
    # Step 3: Test routes
    print_status $BLUE "\n🛣️  Testing routes..."
    
    local routes_ok=true
    
    test_routes "$TENANT1_URL" "TechMart" || routes_ok=false
    test_routes "$TENANT2_URL" "Fashion" || routes_ok=false
    
    # Step 4: Run Playwright tests
    print_status $BLUE "\n🎭 Running automated browser tests..."
    
    run_playwright_tests
    local playwright_result=$?
    
    # Step 5: Generate report
    print_status $BLUE "\n📊 Generating final report..."
    
    generate_report
    
    # Final summary
    print_status $BLUE "\n🏁 Test execution completed!"
    
    if [ "$urls_ok" = true ] && [ "$routes_ok" = true ] && [ $playwright_result -eq 0 ]; then
        print_status $GREEN "✅ All tests passed successfully!"
        exit 0
    else
        print_status $RED "❌ Some tests failed. Please check the results."
        exit 1
    fi
}

# Check if script is being run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
