@echo off
setlocal enabledelayedexpansion

REM Invoice Module - Full Automation Script for Windows
REM Runs complete Playwright test suite with optimization

echo 🚀 Starting Invoice Module Full Automation
echo ==========================================

REM Check if Node.js is installed
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js is not installed. Please install Node.js 16+ first.
    pause
    exit /b 1
)

REM Check Node.js version
for /f "tokens=1 delims=v" %%i in ('node -v') do set NODE_VERSION=%%i
for /f "tokens=1 delims=." %%i in ("%NODE_VERSION:~1%") do set MAJOR_VERSION=%%i
if %MAJOR_VERSION% lss 16 (
    echo [ERROR] Node.js version 16+ is required. Current version: %NODE_VERSION%
    pause
    exit /b 1
)

echo [SUCCESS] Node.js version check passed: %NODE_VERSION%

REM Navigate to scripts directory
cd /d "%~dp0"

REM Install dependencies if needed
if not exist "node_modules" (
    echo [INFO] Installing dependencies...
    call npm install
    if !errorlevel! neq 0 (
        echo [ERROR] Failed to install dependencies
        pause
        exit /b 1
    )
    echo [SUCCESS] Dependencies installed
)

REM Install Playwright browsers if needed
if not exist "node_modules\playwright\.local-browsers" (
    echo [INFO] Installing Playwright browsers...
    call npx playwright install chromium
    if !errorlevel! neq 0 (
        echo [ERROR] Failed to install Playwright browsers
        pause
        exit /b 1
    )
    echo [SUCCESS] Playwright browsers installed
)

REM Clean previous results
echo [INFO] Cleaning previous results...
call npm run clean >nul 2>&1

REM Check if yukimart.local is accessible
echo [INFO] Checking yukimart.local accessibility...
curl -s --connect-timeout 5 http://yukimart.local >nul 2>&1
if !errorlevel! neq 0 (
    echo [WARNING] yukimart.local is not accessible. Please ensure the server is running.
    set /p CONTINUE="Continue anyway? (y/N): "
    if /i not "!CONTINUE!"=="y" (
        echo [ERROR] Automation cancelled
        pause
        exit /b 1
    )
)

echo [SUCCESS] Server accessibility check passed

REM Get mode from argument or default to speed
set MODE=%1
if "%MODE%"=="" set MODE=speed

REM Run automation based on mode
if /i "%MODE%"=="speed" goto :speed
if /i "%MODE%"=="fast" goto :speed
if /i "%MODE%"=="optimized" goto :optimized
if /i "%MODE%"=="opt" goto :optimized
if /i "%MODE%"=="generate" goto :generate
if /i "%MODE%"=="gen" goto :generate
if /i "%MODE%"=="all" goto :all
if /i "%MODE%"=="full" goto :all
if /i "%MODE%"=="parallel" goto :parallel
if /i "%MODE%"=="visible" goto :visible
if /i "%MODE%"=="help" goto :help
if /i "%MODE%"=="-h" goto :help
if /i "%MODE%"=="--help" goto :help

echo [ERROR] Unknown mode: %MODE%
echo [INFO] Use '%~nx0 help' to see available modes
pause
exit /b 1

:speed
echo [INFO] Running Speed Automation (Parallel + Headless)...
node auto-runner.js
goto :check_result

:optimized
echo [INFO] Running Optimized Tests...
node optimized-test-runner.js
goto :check_result

:generate
echo [INFO] Generating test cases...
node test-generator.js
goto :check_result

:all
echo [INFO] Running Full Test Suite...
call npm run test:generate
timeout /t 2 /nobreak >nul
call npm run test:speed
goto :check_result

:parallel
echo [INFO] Running Parallel Tests...
call npm run test:parallel
goto :check_result

:visible
echo [INFO] Running Tests with Visible Browser...
call npm run test:visible
goto :check_result

:help
echo Usage: %~nx0 [MODE]
echo.
echo Available modes:
echo   speed     - Fast automation with parallel execution (default)
echo   optimized - Optimized test runner
echo   generate  - Generate test cases from specifications
echo   all       - Run complete test suite
echo   parallel  - Force parallel execution
echo   visible   - Run with visible browser
echo   help      - Show this help message
echo.
echo Examples:
echo   %~nx0 speed     # Run fast automation
echo   %~nx0 all       # Run complete suite
echo   %~nx0 visible   # Debug with visible browser
pause
exit /b 0

:check_result
if %errorlevel% equ 0 (
    echo [SUCCESS] Automation completed successfully!
    
    REM Show report summary
    echo [INFO] Generating report summary...
    echo.
    echo 📊 AUTOMATION SUMMARY
    echo ====================
    
    REM Check if report exists
    if exist "..\report.md" (
        echo 📝 Report updated: ..\report.md
        
        REM Try to extract success rate (simplified for Windows)
        findstr /C:"Success Rate" "..\report.md" >nul 2>&1
        if !errorlevel! equ 0 (
            echo 📈 Check report for latest success rate
        )
        
        echo.
        echo 📋 View full report: type ..\report.md
        echo 🔍 View latest results: npm run report
    ) else (
        echo [WARNING] Report file not found
    )
    
    echo.
    echo [SUCCESS] 🎉 Invoice automation completed!
    
) else (
    echo [ERROR] Automation failed with exit code %errorlevel%
    
    REM Show debugging information
    echo.
    echo 🔧 DEBUGGING INFORMATION
    echo =======================
    echo Mode: %MODE%
    echo Working Directory: %cd%
    node -v 2>nul && echo Node Version: && node -v
    npm -v 2>nul && echo NPM Version: && npm -v
    
    if exist "session.json" (
        echo Session File: Found
    ) else (
        echo Session File: Not found
    )
    
    echo.
    echo [INFO] Check the logs above for error details
    echo [INFO] Try running with 'visible' mode for debugging: %~nx0 visible
)

pause
exit /b %errorlevel%
