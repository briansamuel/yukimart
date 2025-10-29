#!/bin/bash

# YukiMart User Setup Script
# This script creates platform users and updates tenant users passwords

echo "🚀 YukiMart User Setup Script"
echo "=============================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_status $BLUE "📋 This script will:"
print_status $YELLOW "   - Create 5 platform users (superadmin, admin, dev, manager, support)"
print_status $YELLOW "   - Update all tenant users passwords to: 123456"
print_status $YELLOW "   - Set up proper roles and permissions"
echo ""

# Ask for confirmation
read -p "Do you want to continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_status $YELLOW "❌ Operation cancelled"
    exit 0
fi

echo ""
print_status $BLUE "🔧 Starting user setup process..."

# Run the user management seeder
print_status $BLUE "📦 Running user management seeder..."
php artisan db:seed --class=UserManagementSeeder

if [ $? -eq 0 ]; then
    print_status $GREEN "✅ User setup completed successfully!"
else
    print_status $RED "❌ Error occurred during user setup"
    exit 1
fi

echo ""
print_status $GREEN "🎉 Setup Complete!"
echo ""
print_status $BLUE "🔐 Login Credentials:"
print_status $BLUE "====================="
echo ""
print_status $GREEN "🏢 Platform Users (Access: /admin/login):"
echo "   📧 superadmin@yukimart.local | 🔑 123456 | 👑 Super Administrator"
echo "   📧 admin@yukimart.local      | 🔑 123456 | 🛡️  Platform Administrator"
echo "   📧 dev@yukimart.local        | 🔑 123456 | 💻 Development Manager"
echo "   📧 manager@yukimart.local    | 🔑 123456 | 📊 Platform Manager"
echo "   📧 support@yukimart.local    | 🔑 123456 | 🎧 System Support"
echo ""
print_status $GREEN "🏪 Tenant Users (Access: /admin/login):"
echo "   📧 All tenant users now have password: 🔑 123456"
echo "   📧 Examples:"
echo "      - owner@techmart.local   | 🔑 123456 | 👑 TechMart Owner"
echo "      - admin@techmart.local   | 🔑 123456 | 🛡️  TechMart Admin"
echo "      - owner@fashion.local    | 🔑 123456 | 👑 Fashion Owner"
echo "      - admin@fashion.local    | 🔑 123456 | 🛡️  Fashion Admin"
echo ""
print_status $BLUE "🌐 Access URLs:"
echo "   🏢 Platform: http://yukimart.local/admin/login"
echo "   🏪 TechMart: http://tenant1.yukimart.local/admin/login"
echo "   🏪 Fashion:  http://tenant2.yukimart.local/admin/login"
echo ""
print_status $YELLOW "💡 Tips:"
echo "   - Platform users can switch between tenants"
echo "   - Tenant users can only access their own tenant"
echo "   - Use superadmin for full system access"
echo "   - Change passwords after first login for security"
echo ""
print_status $GREEN "🚀 Ready to login and test the system!"
