#!/bin/bash

echo "🚀 Setting up Product Variants System..."

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate

# Run seeders
echo "🌱 Running attribute seeders..."
php artisan db:seed --class=ProductAttributeSeeder

echo "✅ Product Variants System setup completed!"
echo ""
echo "📋 Summary:"
echo "- Created product_attributes table"
echo "- Created product_attribute_values table" 
echo "- Created product_variants table"
echo "- Created product_variant_attributes table"
echo "- Updated products table with variant support"
echo "- Updated inventories table with variant support"
echo "- Seeded default attributes (Hương vị, Kích thước, Màu sắc)"
echo ""
echo "🎯 Next steps:"
echo "1. Go to Admin > Products > Add Product"
echo "2. Select 'Variable' as product type"
echo "3. Choose attributes and create variants automatically"
echo "4. Test the variant management interface"
echo ""
echo "🔗 Available endpoints:"
echo "- GET /admin/products/attributes - Get available attributes"
echo "- POST /admin/products/{id}/variants - Create variants"
echo "- GET /admin/products/{id}/variants - Get product variants"
echo "- PUT /admin/products/{productId}/variants/{variantId} - Update variant"
echo "- DELETE /admin/products/{productId}/variants/{variantId} - Delete variant"
