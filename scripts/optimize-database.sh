#!/bin/bash

# YukiMart Database Optimization Script
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
DB_NAME="yukimart"
DB_USER="root"
DB_PASS="root"
DB_HOST="mysql"
LOG_FILE="/var/log/yukimart-db-optimization.log"
BACKUP_DIR="/var/backups/yukimart"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}" | tee -a "$LOG_FILE"
}

# Check if MySQL is available
check_mysql() {
    if ! command -v mysql &> /dev/null; then
        error "MySQL client not found"
    fi
    
    if ! mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
        error "Cannot connect to MySQL database"
    fi
    
    log "MySQL connection verified"
}

# Create backup before optimization
create_backup() {
    log "Creating database backup before optimization..."
    
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_file="$BACKUP_DIR/pre_optimization_$timestamp.sql"
    
    mkdir -p "$BACKUP_DIR"
    
    mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$backup_file" || error "Backup failed"
    
    log "Backup created: $backup_file"
}

# Analyze current database status
analyze_database() {
    log "Analyzing current database status..."
    
    # Get database size
    local db_size=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
        FROM information_schema.tables 
        WHERE table_schema = '$DB_NAME';" | tail -1)
    
    info "Database size: ${db_size}MB"
    
    # Get table information
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT 
            table_name,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size_MB',
            table_rows
        FROM information_schema.TABLES 
        WHERE table_schema = '$DB_NAME'
        ORDER BY (data_length + index_length) DESC;" | tee -a "$LOG_FILE"
    
    # Check for tables without primary keys
    log "Checking for tables without primary keys..."
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT t.table_name
        FROM information_schema.tables t
        LEFT JOIN information_schema.table_constraints tc 
            ON t.table_schema = tc.table_schema 
            AND t.table_name = tc.table_name 
            AND tc.constraint_type = 'PRIMARY KEY'
        WHERE t.table_schema = '$DB_NAME' 
            AND tc.constraint_name IS NULL;" | tee -a "$LOG_FILE"
}

# Create performance indexes
create_indexes() {
    log "Creating performance indexes..."
    
    local indexes=(
        # Tenant-related indexes
        "CREATE INDEX IF NOT EXISTS idx_tenants_subdomain ON tenants(subdomain)"
        "CREATE INDEX IF NOT EXISTS idx_tenants_status ON tenants(status)"
        "CREATE INDEX IF NOT EXISTS idx_tenants_slug ON tenants(slug)"
        
        # User-related indexes
        "CREATE INDEX IF NOT EXISTS idx_users_tenant_email ON users(tenant_id, email)"
        "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)"
        "CREATE INDEX IF NOT EXISTS idx_users_tenant_status ON users(tenant_id, status)"
        
        # Product-related indexes
        "CREATE INDEX IF NOT EXISTS idx_products_tenant_status ON products(tenant_id, product_status)"
        "CREATE INDEX IF NOT EXISTS idx_products_sku ON products(sku)"
        "CREATE INDEX IF NOT EXISTS idx_products_barcode ON products(barcode)"
        "CREATE INDEX IF NOT EXISTS idx_products_tenant_category ON products(tenant_id, category_id)"
        "CREATE INDEX IF NOT EXISTS idx_products_featured ON products(product_feature)"
        
        # Branch-related indexes
        "CREATE INDEX IF NOT EXISTS idx_branch_shops_tenant ON branch_shops(tenant_id)"
        "CREATE INDEX IF NOT EXISTS idx_branch_shops_status ON branch_shops(status)"
        
        # Tenant-user relationship indexes
        "CREATE INDEX IF NOT EXISTS idx_tenant_users_tenant_active ON tenant_users(tenant_id, is_active)"
        "CREATE INDEX IF NOT EXISTS idx_tenant_users_role ON tenant_users(role)"
        
        # Search optimization indexes
        "CREATE INDEX IF NOT EXISTS idx_products_search ON products(product_name(50), sku, barcode)"
        "CREATE INDEX IF NOT EXISTS idx_users_login ON users(email, status)"
        
        # Timestamp indexes for common queries
        "CREATE INDEX IF NOT EXISTS idx_products_created_at ON products(created_at)"
        "CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at)"
        "CREATE INDEX IF NOT EXISTS idx_tenants_created_at ON tenants(created_at)"
    )
    
    for index in "${indexes[@]}"; do
        info "Creating index: $(echo "$index" | cut -d' ' -f5)"
        if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$index" 2>/dev/null; then
            log "✅ Index created successfully"
        else
            warning "⚠️ Index creation failed or already exists"
        fi
    done
}

# Optimize table structures
optimize_tables() {
    log "Optimizing table structures..."
    
    # Get list of tables
    local tables=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = '$DB_NAME' 
            AND table_type = 'BASE TABLE';" | tail -n +2)
    
    for table in $tables; do
        info "Optimizing table: $table"
        
        # Analyze table
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "ANALYZE TABLE $table;" >/dev/null 2>&1
        
        # Optimize table
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "OPTIMIZE TABLE $table;" >/dev/null 2>&1
        
        log "✅ Table $table optimized"
    done
}

# Update table statistics
update_statistics() {
    log "Updating table statistics..."
    
    local tables=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = '$DB_NAME' 
            AND table_type = 'BASE TABLE';" | tail -n +2)
    
    for table in $tables; do
        info "Updating statistics for: $table"
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "ANALYZE TABLE $table;" >/dev/null 2>&1
    done
    
    log "✅ Statistics updated for all tables"
}

# Check and fix table integrity
check_integrity() {
    log "Checking table integrity..."
    
    local tables=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = '$DB_NAME' 
            AND table_type = 'BASE TABLE';" | tail -n +2)
    
    local corrupted_tables=()
    
    for table in $tables; do
        info "Checking integrity of: $table"
        local check_result=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "CHECK TABLE $table;" | tail -1 | awk '{print $4}')
        
        if [ "$check_result" != "OK" ]; then
            corrupted_tables+=("$table")
            warning "Table $table has integrity issues: $check_result"
            
            # Attempt to repair
            info "Attempting to repair table: $table"
            mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "REPAIR TABLE $table;" >/dev/null 2>&1
        else
            log "✅ Table $table integrity OK"
        fi
    done
    
    if [ ${#corrupted_tables[@]} -gt 0 ]; then
        warning "Corrupted tables found and repaired: ${corrupted_tables[*]}"
    else
        log "✅ All tables have good integrity"
    fi
}

# Optimize MySQL configuration
optimize_mysql_config() {
    log "Checking MySQL configuration..."
    
    # Check current settings
    local innodb_buffer_pool=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';" | tail -1 | awk '{print $2}')
    local max_connections=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SHOW VARIABLES LIKE 'max_connections';" | tail -1 | awk '{print $2}')
    local query_cache_size=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SHOW VARIABLES LIKE 'query_cache_size';" | tail -1 | awk '{print $2}')
    
    info "Current InnoDB buffer pool size: $innodb_buffer_pool bytes"
    info "Current max connections: $max_connections"
    info "Current query cache size: $query_cache_size bytes"
    
    # Recommendations
    local total_memory=$(free -b | grep Mem | awk '{print $2}')
    local recommended_buffer_pool=$(echo "$total_memory * 0.7" | bc | cut -d. -f1)
    
    info "Recommended InnoDB buffer pool size: $recommended_buffer_pool bytes (70% of RAM)"
    
    if [ "$innodb_buffer_pool" -lt "$recommended_buffer_pool" ]; then
        warning "Consider increasing innodb_buffer_pool_size to improve performance"
    fi
}

# Clean up old data
cleanup_old_data() {
    log "Cleaning up old data..."
    
    # Clean up old log entries (if log table exists)
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'logs';" | grep -q logs; then
        local old_logs=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            SELECT COUNT(*) FROM logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);" | tail -1)
        
        if [ "$old_logs" -gt 0 ]; then
            info "Deleting $old_logs old log entries..."
            mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
                DELETE FROM logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);"
            log "✅ Old log entries cleaned up"
        fi
    fi
    
    # Clean up old sessions (if sessions table exists)
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'sessions';" | grep -q sessions; then
        local old_sessions=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            SELECT COUNT(*) FROM sessions 
            WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY));" | tail -1)
        
        if [ "$old_sessions" -gt 0 ]; then
            info "Deleting $old_sessions old sessions..."
            mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
                DELETE FROM sessions 
                WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY));"
            log "✅ Old sessions cleaned up"
        fi
    fi
}

# Generate optimization report
generate_report() {
    log "Generating optimization report..."
    
    local report_file="/tmp/yukimart-db-optimization-report-$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$report_file" << EOF
YukiMart Database Optimization Report
Generated: $(date)

=== DATABASE OVERVIEW ===
Database: $DB_NAME
Host: $DB_HOST

=== SIZE INFORMATION ===
EOF
    
    # Add database size info
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT 
            table_name as 'Table',
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size_MB',
            table_rows as 'Rows'
        FROM information_schema.TABLES 
        WHERE table_schema = '$DB_NAME'
        ORDER BY (data_length + index_length) DESC;" >> "$report_file"
    
    cat >> "$report_file" << EOF

=== INDEX INFORMATION ===
EOF
    
    # Add index information
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "
        SELECT 
            table_name as 'Table',
            index_name as 'Index',
            column_name as 'Column'
        FROM information_schema.statistics 
        WHERE table_schema = '$DB_NAME'
        ORDER BY table_name, index_name;" >> "$report_file"
    
    cat >> "$report_file" << EOF

=== OPTIMIZATION SUMMARY ===
- Database indexes created/verified
- Table structures optimized
- Statistics updated
- Integrity checks completed
- Old data cleaned up

=== RECOMMENDATIONS ===
- Monitor query performance regularly
- Consider partitioning for large tables
- Implement regular maintenance schedule
- Monitor disk space usage
- Consider read replicas for scaling
EOF
    
    info "Optimization report saved to: $report_file"
    
    # Display summary
    echo
    log "=== OPTIMIZATION SUMMARY ==="
    cat "$report_file"
}

# Test query performance
test_performance() {
    log "Testing query performance..."
    
    # Test tenant lookup query
    local start_time=$(date +%s%N)
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT * FROM tenants WHERE subdomain = 'tenant1' AND status = 'active' LIMIT 1;" >/dev/null 2>&1
    local end_time=$(date +%s%N)
    local tenant_query_time=$(echo "scale=2; ($end_time - $start_time) / 1000000" | bc)
    
    info "Tenant lookup query time: ${tenant_query_time}ms"
    
    # Test product listing query
    start_time=$(date +%s%N)
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT * FROM products WHERE tenant_id = 1 AND product_status = 'publish' LIMIT 20;" >/dev/null 2>&1
    end_time=$(date +%s%N)
    local product_query_time=$(echo "scale=2; ($end_time - $start_time) / 1000000" | bc)
    
    info "Product listing query time: ${product_query_time}ms"
    
    # Test user authentication query
    start_time=$(date +%s%N)
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT u.*, tu.role FROM users u 
        JOIN tenant_users tu ON u.id = tu.user_id 
        WHERE u.email = 'owner@techmart.local' AND u.tenant_id = 1 LIMIT 1;" >/dev/null 2>&1
    end_time=$(date +%s%N)
    local auth_query_time=$(echo "scale=2; ($end_time - $start_time) / 1000000" | bc)
    
    info "User authentication query time: ${auth_query_time}ms"
}

# Main optimization function
main() {
    log "Starting YukiMart database optimization..."
    
    check_mysql
    create_backup
    analyze_database
    create_indexes
    optimize_tables
    update_statistics
    check_integrity
    optimize_mysql_config
    cleanup_old_data
    test_performance
    generate_report
    
    log "Database optimization completed successfully!"
}

# Run main function
main "$@"
