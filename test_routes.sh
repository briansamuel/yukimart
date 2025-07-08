#!/bin/bash

# Script to test routes and find broken ones
BASE_URL="http://yukimart.local"

# Array of routes to test
routes=(
    "/admin/login"
    "/admin/dashboard"
    "/admin/products"
    "/admin/products/create"
    "/admin/orders"
    "/admin/orders/create"
    "/admin/invoices"
    "/admin/invoices/create"
    "/admin/customers"
    "/admin/customers/create"
    "/admin/suppliers"
    "/admin/suppliers/create"
    "/admin/categories"
    "/admin/categories/create"
    "/admin/branch-shops"
    "/admin/branch-shops/create"
    "/admin/warehouses"
    "/admin/warehouses/create"
    "/admin/users"
    "/admin/users/create"
    "/admin/roles"
    "/admin/roles/create"
    "/admin/quick-order"
    "/admin/notifications"
    "/admin/settings-general"
    "/admin/backup"
    "/admin/gallery"
    "/admin/pages"
    "/admin/news"
    "/admin/contact"
    "/admin/my-profile"
)

echo "Testing routes for broken links..."
echo "=================================="

broken_routes=()
working_routes=()

for route in "${routes[@]}"; do
    echo -n "Testing $route ... "
    
    # Get HTTP status code
    status_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$route")
    
    case $status_code in
        200|302)
            echo "✓ OK ($status_code)"
            working_routes+=("$route")
            ;;
        404)
            echo "✗ NOT FOUND (404)"
            broken_routes+=("$route - 404 Not Found")
            ;;
        500)
            echo "✗ SERVER ERROR (500)"
            broken_routes+=("$route - 500 Server Error")
            ;;
        *)
            echo "✗ ERROR ($status_code)"
            broken_routes+=("$route - $status_code")
            ;;
    esac
done

echo ""
echo "=================================="
echo "SUMMARY:"
echo "Working routes: ${#working_routes[@]}"
echo "Broken routes: ${#broken_routes[@]}"

if [ ${#broken_routes[@]} -gt 0 ]; then
    echo ""
    echo "BROKEN ROUTES:"
    echo "=============="
    for broken in "${broken_routes[@]}"; do
        echo "✗ $broken"
    done
fi

echo ""
echo "Test completed."
