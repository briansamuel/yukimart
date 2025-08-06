#!/bin/bash

# Invoice Module - Full Automation Script
# Runs complete Playwright test suite with optimization

echo "🚀 Starting Invoice Module Full Automation"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 16+ first."
    exit 1
fi

# Check Node.js version
NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 16 ]; then
    print_error "Node.js version 16+ is required. Current version: $(node -v)"
    exit 1
fi

print_success "Node.js version check passed: $(node -v)"

# Navigate to scripts directory
cd "$(dirname "$0")"

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    print_status "Installing dependencies..."
    npm install
    if [ $? -ne 0 ]; then
        print_error "Failed to install dependencies"
        exit 1
    fi
    print_success "Dependencies installed"
fi

# Install Playwright browsers if needed
if [ ! -d "node_modules/playwright/.local-browsers" ]; then
    print_status "Installing Playwright browsers..."
    npx playwright install chromium
    if [ $? -ne 0 ]; then
        print_error "Failed to install Playwright browsers"
        exit 1
    fi
    print_success "Playwright browsers installed"
fi

# Clean previous results
print_status "Cleaning previous results..."
npm run clean 2>/dev/null || true

# Check if yukimart.local is accessible
print_status "Checking yukimart.local accessibility..."
if ! curl -s --connect-timeout 5 http://yukimart.local > /dev/null; then
    print_warning "yukimart.local is not accessible. Please ensure the server is running."
    read -p "Continue anyway? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Automation cancelled"
        exit 1
    fi
fi

print_success "Server accessibility check passed"

# Run automation based on argument
MODE=${1:-speed}

case $MODE in
    "speed"|"fast")
        print_status "Running Speed Automation (Parallel + Headless)..."
        node auto-runner.js
        ;;
    "optimized"|"opt")
        print_status "Running Optimized Tests..."
        node optimized-test-runner.js
        ;;
    "generate"|"gen")
        print_status "Generating test cases..."
        node test-generator.js
        ;;
    "all"|"full")
        print_status "Running Full Test Suite..."
        npm run test:generate
        sleep 2
        npm run test:speed
        ;;
    "parallel")
        print_status "Running Parallel Tests..."
        npm run test:parallel
        ;;
    "visible")
        print_status "Running Tests with Visible Browser..."
        npm run test:visible
        ;;
    "help"|"-h"|"--help")
        echo "Usage: $0 [MODE]"
        echo ""
        echo "Available modes:"
        echo "  speed     - Fast automation with parallel execution (default)"
        echo "  optimized - Optimized test runner"
        echo "  generate  - Generate test cases from specifications"
        echo "  all       - Run complete test suite"
        echo "  parallel  - Force parallel execution"
        echo "  visible   - Run with visible browser"
        echo "  help      - Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 speed     # Run fast automation"
        echo "  $0 all       # Run complete suite"
        echo "  $0 visible   # Debug with visible browser"
        exit 0
        ;;
    *)
        print_error "Unknown mode: $MODE"
        print_status "Use '$0 help' to see available modes"
        exit 1
        ;;
esac

# Check exit code
if [ $? -eq 0 ]; then
    print_success "Automation completed successfully!"
    
    # Show report summary
    print_status "Generating report summary..."
    echo ""
    echo "📊 AUTOMATION SUMMARY"
    echo "===================="
    
    # Extract latest results from report
    if [ -f "../report.md" ]; then
        echo "📝 Report updated: ../report.md"
        
        # Try to extract success rate
        SUCCESS_RATE=$(grep -o "Success Rate.*[0-9]\+%" ../report.md | tail -1 | grep -o "[0-9]\+%")
        if [ ! -z "$SUCCESS_RATE" ]; then
            echo "📈 Latest Success Rate: $SUCCESS_RATE"
        fi
        
        # Try to extract duration
        DURATION=$(grep -o "Duration.*[0-9]\+ seconds" ../report.md | tail -1 | grep -o "[0-9]\+ seconds")
        if [ ! -z "$DURATION" ]; then
            echo "⏱️ Latest Duration: $DURATION"
        fi
        
        echo ""
        echo "📋 View full report: cat ../report.md"
        echo "🔍 View latest results: npm run report"
    else
        print_warning "Report file not found"
    fi
    
    echo ""
    print_success "🎉 Invoice automation completed!"
    
else
    print_error "Automation failed with exit code $?"
    
    # Show debugging information
    echo ""
    echo "🔧 DEBUGGING INFORMATION"
    echo "======================="
    echo "Mode: $MODE"
    echo "Working Directory: $(pwd)"
    echo "Node Version: $(node -v)"
    echo "NPM Version: $(npm -v)"
    
    if [ -f "session.json" ]; then
        echo "Session File: Found"
    else
        echo "Session File: Not found"
    fi
    
    echo ""
    print_status "Check the logs above for error details"
    print_status "Try running with 'visible' mode for debugging: $0 visible"
    
    exit 1
fi
