# YukiMart User Setup Script (PowerShell)
# This script creates platform users and updates tenant users passwords

Write-Host "🚀 YukiMart User Setup Script" -ForegroundColor Blue
Write-Host "==============================" -ForegroundColor Blue
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: artisan file not found. Please run this script from the Laravel project root directory." -ForegroundColor Red
    exit 1
}

Write-Host "📋 This script will:" -ForegroundColor Blue
Write-Host "   - Create 5 platform users (superadmin, admin, dev, manager, support)" -ForegroundColor Yellow
Write-Host "   - Update all tenant users passwords to: 123456" -ForegroundColor Yellow
Write-Host "   - Set up proper roles and permissions" -ForegroundColor Yellow
Write-Host ""

# Ask for confirmation
$confirmation = Read-Host "Do you want to continue? (y/N)"
if ($confirmation -ne 'y' -and $confirmation -ne 'Y') {
    Write-Host "❌ Operation cancelled" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "🔧 Starting user setup process..." -ForegroundColor Blue

# Run the user management seeder
Write-Host "📦 Running user management seeder..." -ForegroundColor Blue
$result = & php artisan db:seed --class=UserManagementSeeder

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ User setup completed successfully!" -ForegroundColor Green
} else {
    Write-Host "❌ Error occurred during user setup" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🎉 Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "🔐 Login Credentials:" -ForegroundColor Blue
Write-Host "=====================" -ForegroundColor Blue
Write-Host ""
Write-Host "🏢 Platform Users (Access: /admin/login):" -ForegroundColor Green
Write-Host "   📧 superadmin@yukimart.local | 🔑 123456 | 👑 Super Administrator"
Write-Host "   📧 admin@yukimart.local      | 🔑 123456 | 🛡️  Platform Administrator"
Write-Host "   📧 dev@yukimart.local        | 🔑 123456 | 💻 Development Manager"
Write-Host "   📧 manager@yukimart.local    | 🔑 123456 | 📊 Platform Manager"
Write-Host "   📧 support@yukimart.local    | 🔑 123456 | 🎧 System Support"
Write-Host ""
Write-Host "🏪 Tenant Users (Access: /admin/login):" -ForegroundColor Green
Write-Host "   📧 All tenant users now have password: 🔑 123456"
Write-Host "   📧 Examples:"
Write-Host "      - owner@techmart.local   | 🔑 123456 | 👑 TechMart Owner"
Write-Host "      - admin@techmart.local   | 🔑 123456 | 🛡️  TechMart Admin"
Write-Host "      - owner@fashion.local    | 🔑 123456 | 👑 Fashion Owner"
Write-Host "      - admin@fashion.local    | 🔑 123456 | 🛡️  Fashion Admin"
Write-Host ""
Write-Host "🌐 Access URLs:" -ForegroundColor Blue
Write-Host "   🏢 Platform: http://yukimart.local/admin/login"
Write-Host "   🏪 TechMart: http://tenant1.yukimart.local/admin/login"
Write-Host "   🏪 Fashion:  http://tenant2.yukimart.local/admin/login"
Write-Host ""
Write-Host "💡 Tips:" -ForegroundColor Yellow
Write-Host "   - Platform users can switch between tenants"
Write-Host "   - Tenant users can only access their own tenant"
Write-Host "   - Use superadmin for full system access"
Write-Host "   - Change passwords after first login for security"
Write-Host ""
Write-Host "🚀 Ready to login and test the system!" -ForegroundColor Green
