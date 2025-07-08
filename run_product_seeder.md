# Product Seeder - Import 20 Products

## Overview
This seeder creates 20 realistic products with their corresponding inventory records, organized into three categories: Electronics, Fashion, and Home & Garden.

## What Gets Created

### Electronics Products (8 items):
1. **iPhone 15 Pro Max** - Apple - Stock: 50 units
2. **Samsung Galaxy S24 Ultra** - Samsung - Stock: 35 units  
3. **MacBook Pro M3** - Apple - Stock: 15 units
4. **Sony WH-1000XM5 Headphones** - Sony - Stock: 80 units
5. **iPad Air M2** - Apple - Stock: 25 units
6. **LG OLED 55" Smart TV** - LG - Stock: 12 units
7. **Canon EOS R6 Mark II** - Canon - Stock: 8 units
8. **Xiaomi Mi Band 8** - Xiaomi - Stock: 200 units

### Fashion Products (7 items):
1. **Nike Air Max 270** - Nike - Stock: 45 units
2. **Adidas Ultraboost 22** - Adidas - Stock: 38 units
3. **Uniqlo Heattech T-Shirt** - Uniqlo - Stock: 150 units
4. **Zara Slim Fit Jeans** - Zara - Stock: 60 units
5. **H&M Cotton Hoodie** - H&M - Stock: 75 units
6. **Levi's 501 Original Jeans** - Levi's - Stock: 40 units
7. **Champion Reverse Weave Sweatshirt** - Champion - Stock: 30 units

### Home & Garden Products (5 items):
1. **IKEA MALM Bed Frame** - IKEA - Stock: 20 units
2. **Dyson V15 Detect Vacuum** - Dyson - Stock: 8 units
3. **Philips Air Fryer XXL** - Philips - Stock: 25 units
4. **Muji Aroma Diffuser** - Muji - Stock: 50 units
5. **Xiaomi Robot Vacuum S10** - Xiaomi - Stock: 12 units

## Product Features

Each product includes:
- **Complete Product Information**: Name, description, content, SKU
- **Pricing**: Realistic cost and sale prices in VND
- **Inventory Management**: Stock quantity, reorder points, tracking enabled
- **Supplier Information**: Supplier name, SKU, cost, lead times
- **Physical Attributes**: Dimensions (length, width, height), weight
- **Business Rules**: Min/max order quantities, backorder settings
- **Status**: Published and ready for sale
- **Categories**: Electronics (simple), Fashion (variable), Home (simple)

## Running the Seeder

### Option 1: Run ProductSeeder Only
```bash
php artisan db:seed --class=ProductSeeder
```

### Option 2: Run All Seeders (includes ProductSeeder)
```bash
php artisan db:seed
```

### Option 3: Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

## Prerequisites

Before running the seeder, ensure:

1. **Database is set up** and accessible
2. **Migrations are run**:
   ```bash
   php artisan migrate
   ```
3. **Required tables exist**:
   - `products` table
   - `inventories` table  
   - `users` table

## Sample Output

When you run the seeder, you'll see:
```
Creating 20 products with inventory...
Creating electronics products...
Creating fashion products...
Creating home & garden products...
Successfully created 20 products with inventory records!

Summary:
- Total Products: 20
- Total Inventory: 1,023 units
- Low Stock Products: 3
```

## Database Records Created

### Products Table:
- 20 product records with complete information
- Realistic Vietnamese pricing (VND)
- Proper product slugs and SKUs
- Inventory tracking enabled
- Supplier information included

### Inventories Table:
- 20 inventory records (one per product)
- Varied stock levels (some high, some low)
- Realistic quantities based on product type

### Stock Levels Distribution:
- **High Stock**: Electronics accessories, fashion basics
- **Medium Stock**: Popular items, seasonal products  
- **Low Stock**: Premium items, specialized products
- **Some items below reorder point** for testing alerts

## Testing the Results

After seeding, verify:

1. **Products List**: Visit `/admin/products` - should show 20 products
2. **Stock Quantities**: Each product should display inventory from inventories table
3. **Stock Status**: Should show "In Stock", "Low Stock" based on reorder points
4. **Product Details**: Click edit to see complete product information
5. **Inventory Relationship**: Stock quantities come from inventories table

## Customization

To modify the seeder:

1. **Edit ProductSeeder.php** to change products or quantities
2. **Add more categories** by extending the arrays
3. **Adjust stock levels** to test different scenarios
4. **Change pricing** to match your market

## Cleanup

To remove seeded data:
```bash
# Delete all products and their inventory
php artisan tinker
>>> App\Models\Product::truncate();
>>> App\Models\Inventory::truncate();
```

Or run fresh migration:
```bash
php artisan migrate:fresh
```

## Integration with Inventory System

The seeded products work perfectly with the inventory management system:
- **Stock tracking** via inventories relationship
- **Low stock alerts** for products below reorder point
- **Inventory transactions** can be created for stock movements
- **Stock operations** (add/remove) update inventories table
- **Business rules** enforced (min/max quantities, backorders)

This provides a solid foundation for testing and demonstrating the complete inventory management functionality.
