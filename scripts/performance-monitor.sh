#!/bin/bash

# YukiMart Performance Monitoring Script
# Version: 1.0
# Date: 2025-08-11

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/yukimart"
LOG_FILE="/var/log/yukimart-performance.log"
ALERT_EMAIL="admin@yukimart.com"
THRESHOLD_RESPONSE_TIME=1000  # milliseconds
THRESHOLD_MEMORY_USAGE=80     # percentage
THRESHOLD_DB_CONNECTIONS=150  # connections
THRESHOLD_DISK_USAGE=85       # percentage

# Tenant URLs for testing
TENANT_URLS=(
    "http://tenant1.yukimart.local"
    "http://tenant2.yukimart.local"
    "http://tenant3.yukimart.local"
    "http://hellomart.yukimart.local"
    "http://bibomart.yukimart.local"
)

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}" | tee -a "$LOG_FILE"
}

# Check system requirements
check_requirements() {
    local missing_tools=()
    
    # Check required tools
    for tool in curl mysql redis-cli bc; do
        if ! command -v "$tool" &> /dev/null; then
            missing_tools+=("$tool")
        fi
    done
    
    if [ ${#missing_tools[@]} -ne 0 ]; then
        error "Missing required tools: ${missing_tools[*]}"
        exit 1
    fi
}

# Test response time for all tenants
test_response_times() {
    log "Testing response times for all tenants..."
    
    local total_response_time=0
    local tenant_count=0
    local slow_tenants=()
    
    for url in "${TENANT_URLS[@]}"; do
        local response_time=$(curl -o /dev/null -s -w '%{time_total}' "$url" 2>/dev/null || echo "999")
        local response_time_ms=$(echo "$response_time * 1000" | bc -l | cut -d. -f1)
        
        tenant_count=$((tenant_count + 1))
        total_response_time=$(echo "$total_response_time + $response_time" | bc -l)
        
        if [ "$response_time_ms" -gt "$THRESHOLD_RESPONSE_TIME" ]; then
            slow_tenants+=("$url: ${response_time_ms}ms")
            warning "Slow response from $url: ${response_time_ms}ms"
        else
            info "Response time for $url: ${response_time_ms}ms"
        fi
    done
    
    local avg_response_time=$(echo "scale=3; $total_response_time / $tenant_count" | bc -l)
    local avg_response_time_ms=$(echo "$avg_response_time * 1000" | bc -l | cut -d. -f1)
    
    log "Average response time: ${avg_response_time_ms}ms"
    
    if [ ${#slow_tenants[@]} -gt 0 ]; then
        warning "Slow tenants detected:"
        printf '%s\n' "${slow_tenants[@]}" | tee -a "$LOG_FILE"
        return 1
    fi
    
    return 0
}

# Check system resources
check_system_resources() {
    log "Checking system resources..."
    
    local alerts=()
    
    # Check memory usage
    local memory_usage=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
    local memory_usage_int=$(echo "$memory_usage" | cut -d. -f1)
    
    if [ "$memory_usage_int" -gt "$THRESHOLD_MEMORY_USAGE" ]; then
        alerts+=("High memory usage: ${memory_usage}%")
        warning "High memory usage: ${memory_usage}%"
    else
        info "Memory usage: ${memory_usage}%"
    fi
    
    # Check disk usage
    local disk_usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
    
    if [ "$disk_usage" -gt "$THRESHOLD_DISK_USAGE" ]; then
        alerts+=("High disk usage: ${disk_usage}%")
        warning "High disk usage: ${disk_usage}%"
    else
        info "Disk usage: ${disk_usage}%"
    fi
    
    # Check CPU load
    local cpu_load=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    local cpu_cores=$(nproc)
    local cpu_usage=$(echo "scale=1; $cpu_load / $cpu_cores * 100" | bc -l)
    local cpu_usage_int=$(echo "$cpu_usage" | cut -d. -f1)
    
    if [ "$cpu_usage_int" -gt 80 ]; then
        alerts+=("High CPU usage: ${cpu_usage}%")
        warning "High CPU usage: ${cpu_usage}%"
    else
        info "CPU usage: ${cpu_usage}%"
    fi
    
    if [ ${#alerts[@]} -gt 0 ]; then
        return 1
    fi
    
    return 0
}

# Check database performance
check_database_performance() {
    log "Checking database performance..."
    
    local alerts=()
    
    # Check database connections
    local db_connections=$(mysql -e "SHOW STATUS LIKE 'Threads_connected';" 2>/dev/null | tail -1 | awk '{print $2}' || echo "0")
    
    if [ "$db_connections" -gt "$THRESHOLD_DB_CONNECTIONS" ]; then
        alerts+=("High database connections: $db_connections")
        warning "High database connections: $db_connections"
    else
        info "Database connections: $db_connections"
    fi
    
    # Check slow queries
    local slow_queries=$(mysql -e "SHOW STATUS LIKE 'Slow_queries';" 2>/dev/null | tail -1 | awk '{print $2}' || echo "0")
    info "Slow queries: $slow_queries"
    
    # Check database size
    local db_size=$(mysql -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema='yukimart';" 2>/dev/null | tail -1 || echo "0")
    info "Database size: ${db_size}MB"
    
    # Test database query performance
    local query_start=$(date +%s%N)
    mysql -e "SELECT COUNT(*) FROM products;" >/dev/null 2>&1 || true
    local query_end=$(date +%s%N)
    local query_time=$(echo "scale=2; ($query_end - $query_start) / 1000000" | bc -l)
    
    info "Sample query time: ${query_time}ms"
    
    if [ ${#alerts[@]} -gt 0 ]; then
        return 1
    fi
    
    return 0
}

# Check Redis performance
check_redis_performance() {
    log "Checking Redis performance..."
    
    local alerts=()
    
    # Check Redis connection
    if ! redis-cli ping >/dev/null 2>&1; then
        alerts+=("Redis connection failed")
        error "Redis connection failed"
        return 1
    fi
    
    # Check Redis memory usage
    local redis_memory=$(redis-cli info memory | grep used_memory_human | cut -d: -f2 | tr -d '\r')
    info "Redis memory usage: $redis_memory"
    
    # Check Redis connected clients
    local redis_clients=$(redis-cli info clients | grep connected_clients | cut -d: -f2 | tr -d '\r')
    info "Redis connected clients: $redis_clients"
    
    # Test Redis performance
    local redis_start=$(date +%s%N)
    redis-cli set test_key "test_value" >/dev/null 2>&1
    redis-cli get test_key >/dev/null 2>&1
    redis-cli del test_key >/dev/null 2>&1
    local redis_end=$(date +%s%N)
    local redis_time=$(echo "scale=2; ($redis_end - $redis_start) / 1000000" | bc -l)
    
    info "Redis operation time: ${redis_time}ms"
    
    return 0
}

# Check application health
check_application_health() {
    log "Checking application health..."
    
    local alerts=()
    
    # Check Laravel application
    if [ -f "$PROJECT_DIR/artisan" ]; then
        cd "$PROJECT_DIR"
        
        # Check if application is up
        if ! php artisan --version >/dev/null 2>&1; then
            alerts+=("Laravel application not responding")
            error "Laravel application not responding"
        else
            info "Laravel application is running"
        fi
        
        # Check storage permissions
        if [ ! -w "$PROJECT_DIR/storage/logs" ]; then
            alerts+=("Storage directory not writable")
            warning "Storage directory not writable"
        fi
        
        # Check cache status
        if [ -f "$PROJECT_DIR/bootstrap/cache/config.php" ]; then
            info "Configuration cached"
        else
            warning "Configuration not cached"
        fi
        
        # Check log file size
        local log_size=$(du -sh "$PROJECT_DIR/storage/logs/laravel.log" 2>/dev/null | cut -f1 || echo "0")
        info "Laravel log size: $log_size"
        
    else
        alerts+=("Laravel application not found")
        error "Laravel application not found at $PROJECT_DIR"
    fi
    
    if [ ${#alerts[@]} -gt 0 ]; then
        return 1
    fi
    
    return 0
}

# Test API endpoints
test_api_endpoints() {
    log "Testing API endpoints..."
    
    local failed_endpoints=()
    
    for url in "${TENANT_URLS[@]}"; do
        local api_url="${url}/api/tenant/info"
        local http_code=$(curl -o /dev/null -s -w '%{http_code}' "$api_url" 2>/dev/null || echo "000")
        
        if [ "$http_code" = "200" ]; then
            info "API endpoint OK: $api_url"
        else
            failed_endpoints+=("$api_url (HTTP $http_code)")
            warning "API endpoint failed: $api_url (HTTP $http_code)"
        fi
    done
    
    if [ ${#failed_endpoints[@]} -gt 0 ]; then
        warning "Failed API endpoints:"
        printf '%s\n' "${failed_endpoints[@]}" | tee -a "$LOG_FILE"
        return 1
    fi
    
    return 0
}

# Generate performance report
generate_report() {
    log "Generating performance report..."
    
    local report_file="/tmp/yukimart-performance-report-$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$report_file" << EOF
YukiMart Performance Report
Generated: $(date)

=== SYSTEM OVERVIEW ===
Hostname: $(hostname)
Uptime: $(uptime)
Load Average: $(uptime | awk -F'load average:' '{print $2}')

=== RESOURCE USAGE ===
Memory: $(free -h | grep Mem | awk '{print $3 "/" $2}')
Disk: $(df -h / | tail -1 | awk '{print $3 "/" $2 " (" $5 " used)"}')
CPU Cores: $(nproc)

=== DATABASE STATUS ===
Connections: $(mysql -e "SHOW STATUS LIKE 'Threads_connected';" 2>/dev/null | tail -1 | awk '{print $2}' || echo "N/A")
Slow Queries: $(mysql -e "SHOW STATUS LIKE 'Slow_queries';" 2>/dev/null | tail -1 | awk '{print $2}' || echo "N/A")
Database Size: $(mysql -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Size' FROM information_schema.tables WHERE table_schema='yukimart';" 2>/dev/null | tail -1 || echo "N/A")MB

=== REDIS STATUS ===
Memory Usage: $(redis-cli info memory 2>/dev/null | grep used_memory_human | cut -d: -f2 | tr -d '\r' || echo "N/A")
Connected Clients: $(redis-cli info clients 2>/dev/null | grep connected_clients | cut -d: -f2 | tr -d '\r' || echo "N/A")

=== TENANT RESPONSE TIMES ===
EOF

    for url in "${TENANT_URLS[@]}"; do
        local response_time=$(curl -o /dev/null -s -w '%{time_total}' "$url" 2>/dev/null || echo "999")
        local response_time_ms=$(echo "$response_time * 1000" | bc -l | cut -d. -f1)
        echo "$url: ${response_time_ms}ms" >> "$report_file"
    done
    
    cat >> "$report_file" << EOF

=== RECOMMENDATIONS ===
EOF

    # Add recommendations based on findings
    local memory_usage=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
    if [ "$memory_usage" -gt 80 ]; then
        echo "- Consider increasing server memory (current usage: ${memory_usage}%)" >> "$report_file"
    fi
    
    local disk_usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
    if [ "$disk_usage" -gt 80 ]; then
        echo "- Consider cleaning up disk space (current usage: ${disk_usage}%)" >> "$report_file"
    fi
    
    echo "- Regular monitoring recommended" >> "$report_file"
    echo "- Consider implementing automated alerts" >> "$report_file"
    
    info "Performance report saved to: $report_file"
    
    # Display summary
    echo
    log "=== PERFORMANCE SUMMARY ==="
    cat "$report_file"
}

# Send alert email
send_alert() {
    local subject="$1"
    local message="$2"
    
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" "$ALERT_EMAIL"
        info "Alert email sent to $ALERT_EMAIL"
    else
        warning "Mail command not available, cannot send alert email"
    fi
}

# Main monitoring function
main() {
    log "Starting YukiMart performance monitoring..."
    
    check_requirements
    
    local issues=()
    
    # Run all checks
    if ! test_response_times; then
        issues+=("Response time issues detected")
    fi
    
    if ! check_system_resources; then
        issues+=("System resource issues detected")
    fi
    
    if ! check_database_performance; then
        issues+=("Database performance issues detected")
    fi
    
    if ! check_redis_performance; then
        issues+=("Redis performance issues detected")
    fi
    
    if ! check_application_health; then
        issues+=("Application health issues detected")
    fi
    
    if ! test_api_endpoints; then
        issues+=("API endpoint issues detected")
    fi
    
    # Generate report
    generate_report
    
    # Send alerts if issues found
    if [ ${#issues[@]} -gt 0 ]; then
        local alert_message="YukiMart performance issues detected:\n\n"
        printf '%s\n' "${issues[@]}" | while read -r issue; do
            alert_message="${alert_message}- $issue\n"
        done
        alert_message="${alert_message}\nPlease check the system immediately."
        
        send_alert "YukiMart Performance Alert" "$alert_message"
        
        error "Performance monitoring completed with ${#issues[@]} issues"
        exit 1
    else
        log "Performance monitoring completed successfully - all systems healthy"
        exit 0
    fi
}

# Run main function
main "$@"
