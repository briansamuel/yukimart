# Running Inventory Management Migrations

## Quick Fix for Column Not Found Error

The error `Column not found: 1054 Unknown column 'stock_quantity'` occurs because the inventory management migrations haven't been run yet. Here's how to fix it:

## Option 1: Run Migrations (Recommended)

Execute these commands in your terminal from the project root:

```bash
# Run the inventory management migrations
php artisan migrate

# Or run specific migrations if needed
php artisan migrate --path=database/migrations/2025_06_17_000001_add_inventory_management_to_products.php
php artisan migrate --path=database/migrations/2025_06_17_000002_create_inventory_transactions_table.php
php artisan migrate --path=database/migrations/2025_06_17_000003_create_inventory_alerts_table.php
```

## Option 2: Temporary Fix (If you can't run migrations immediately)

The ProductService has been updated to gracefully handle missing inventory columns. It will:

1. **Check if inventory columns exist** before querying them
2. **Use basic columns** if inventory fields don't exist yet
3. **Default to 0** for missing stock_quantity values
4. **Still provide stock status** (will show "Out of Stock" for all products until migration is run)

## What the Migrations Add

### 1. Products Table Enhancements
- `stock_quantity` - Current stock on hand
- `reserved_quantity` - Stock reserved for orders
- `track_inventory` - Enable/disable inventory tracking
- `allow_backorder` - Allow orders when out of stock
- `max_order_quantity` - Maximum quantity per order
- `min_order_quantity` - Minimum quantity per order
- `low_stock_alert` - Enable low stock notifications
- `out_of_stock_alert` - Enable out of stock notifications
- `last_stock_update` - Timestamp of last inventory change
- `supplier_name` - Primary supplier
- `supplier_sku` - Supplier's product code
- `supplier_cost` - Cost from supplier
- `lead_time_days` - Delivery lead time
- `length`, `width`, `height` - Physical dimensions
- `volume` - Computed volume
- `total_value` - Computed inventory value

### 2. Inventory Transactions Table
- Complete audit trail of all stock movements
- Transaction types: purchase, sale, adjustment, return, damage, transfer, etc.
- Before/after quantities and values
- Reference tracking to related orders/purchases
- User tracking and timestamps

### 3. Inventory Alerts Table
- Automated alert system for stock issues
- Alert types: low stock, out of stock, overstock, etc.
- Severity levels and resolution tracking

## After Running Migrations

Once migrations are complete:

1. **Products List** will show actual stock quantities
2. **Stock Status** will be calculated correctly
3. **Inventory Management** features will be fully functional
4. **Stock Alerts** will be generated automatically
5. **Transaction Tracking** will begin recording all stock movements

## Verification

After running migrations, verify the fix by:

1. **Check Database**: Confirm new columns exist in `products` table
2. **Test Products List**: Visit `/admin/products` - should load without errors
3. **Check Stock Display**: Products should show stock quantities and status
4. **Test Filtering**: Try filtering by product status
5. **Test Sorting**: Try sorting by different columns

## Migration Files Location

The migration files are located at:
- `database/migrations/2025_06_17_000001_add_inventory_management_to_products.php`
- `database/migrations/2025_06_17_000002_create_inventory_transactions_table.php`
- `database/migrations/2025_06_17_000003_create_inventory_alerts_table.php`

## Rollback (If Needed)

If you need to rollback the migrations:

```bash
# Rollback the last batch of migrations
php artisan migrate:rollback

# Or rollback specific migrations
php artisan migrate:rollback --path=database/migrations/2025_06_17_000003_create_inventory_alerts_table.php
php artisan migrate:rollback --path=database/migrations/2025_06_17_000002_create_inventory_transactions_table.php
php artisan migrate:rollback --path=database/migrations/2025_06_17_000001_add_inventory_management_to_products.php
```

## Support

If you encounter any issues:

1. **Check Laravel Logs**: `storage/logs/laravel.log`
2. **Verify Database Connection**: Ensure database is accessible
3. **Check Migration Status**: `php artisan migrate:status`
4. **Clear Cache**: `php artisan cache:clear` and `php artisan config:clear`

The ProductService is now robust enough to handle both scenarios (with and without inventory columns), so the products list should work in either case.
