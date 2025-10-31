#!/bin/bash

# YukiMart Postman Sync Helper Script
# Usage: ./sync.sh [fcm|api|all] [--env]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
TYPE="fcm"
SYNC_ENV=""
DOCKER_CONTAINER="php83"
PROJECT_PATH="/var/www/html/yukimart"

# Function to print colored output
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to show usage
show_usage() {
    echo "YukiMart Postman Sync Helper"
    echo ""
    echo "Usage: $0 [TYPE] [OPTIONS]"
    echo ""
    echo "TYPE:"
    echo "  fcm     Sync FCM collection only"
    echo "  api     Sync API collection only"
    echo "  all     Sync all collections (default: fcm)"
    echo ""
    echo "OPTIONS:"
    echo "  --env   Also sync environment variables from .env"
    echo "  --help  Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 fcm           # Sync FCM collection only"
    echo "  $0 fcm --env     # Sync FCM collection + environment"
    echo "  $0 all --env     # Sync all collections + environment"
    echo ""
}

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        fcm|api|all)
            TYPE="$1"
            shift
            ;;
        --env)
            SYNC_ENV="--sync-env"
            shift
            ;;
        --help|-h)
            show_usage
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# Check if Docker container is running
print_info "Checking Docker container..."
if ! docker ps | grep -q "$DOCKER_CONTAINER"; then
    print_error "Docker container '$DOCKER_CONTAINER' is not running"
    print_info "Please start the container first: docker start $DOCKER_CONTAINER"
    exit 1
fi

print_success "Docker container '$DOCKER_CONTAINER' is running"

# Check if project directory exists
print_info "Checking project directory..."
if ! docker exec "$DOCKER_CONTAINER" test -d "$PROJECT_PATH"; then
    print_error "Project directory '$PROJECT_PATH' not found in container"
    exit 1
fi

print_success "Project directory found"

# Check if postman directory exists
print_info "Checking postman directory..."
if ! docker exec "$DOCKER_CONTAINER" test -d "$PROJECT_PATH/postman"; then
    print_warning "Postman directory not found, creating..."
    docker exec "$DOCKER_CONTAINER" mkdir -p "$PROJECT_PATH/postman"
fi

print_success "Postman directory ready"

# Build the artisan command
ARTISAN_CMD="cd $PROJECT_PATH && php artisan postman:sync --type=$TYPE $SYNC_ENV"

print_info "Running sync command..."
print_info "Command: $ARTISAN_CMD"
echo ""

# Execute the sync command
if docker exec -it "$DOCKER_CONTAINER" /bin/sh -c "$ARTISAN_CMD"; then
    echo ""
    print_success "Postman sync completed successfully!"
    
    # Show next steps
    echo ""
    print_info "Next steps:"
    echo "1. Open Postman"
    echo "2. Import updated collection files:"
    echo "   - postman/YukiMart-FCM-API.postman_collection.json"
    if [[ "$SYNC_ENV" == "--sync-env" ]]; then
        echo "   - postman/YukiMart-FCM.postman_environment.json"
    fi
    echo "3. Select the updated environment"
    echo "4. Test your API endpoints"
    
    # Show file locations
    echo ""
    print_info "Updated files:"
    docker exec "$DOCKER_CONTAINER" ls -la "$PROJECT_PATH/postman/" | grep -E "\.(json|md)$" || true
    
else
    echo ""
    print_error "Postman sync failed!"
    print_info "Check the error messages above for details"
    exit 1
fi

echo ""
print_success "🎉 All done! Your Postman collections are now synced."
