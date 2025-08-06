-- Create test products for Quick Order testing
INSERT INTO products (
    product_name, 
    product_slug, 
    product_description, 
    product_content, 
    sku, 
    barcode, 
    cost_price, 
    sale_price, 
    product_status, 
    product_type, 
    brand, 
    weight, 
    points, 
    reorder_point, 
    product_feature, 
    created_by_user, 
    updated_by_user, 
    created_at, 
    updated_at
) VALUES 
-- Test Product 1: Laptop
(
    'Laptop Dell Inspiron 15', 
    'laptop-dell-inspiron-15', 
    'Laptop Dell Inspiron 15 inch với hiệu năng cao', 
    'Laptop Dell Inspiron 15 inch với hiệu năng cao, phù hợp cho công việc và giải trí', 
    'LAPTOP001', 
    '1234567890123', 
    15000000.00, 
    18000000.00, 
    'publish', 
    'simple', 
    'Dell', 
    2500, 
    180, 
    5, 
    1, 
    1, 
    1, 
    NOW(), 
    NOW()
),
-- Test Product 2: Mouse
(
    'Chuột Logitech MX Master 3', 
    'chuot-logitech-mx-master-3', 
    'Chuột không dây Logitech MX Master 3 cao cấp', 
    'Chuột không dây Logitech MX Master 3 với thiết kế ergonomic và pin lâu', 
    'MOUSE001', 
    '2345678901234', 
    1500000.00, 
    2200000.00, 
    'publish', 
    'simple', 
    'Logitech', 
    150, 
    22, 
    10, 
    0, 
    1, 
    1, 
    NOW(), 
    NOW()
),
-- Test Product 3: Keyboard
(
    'Bàn phím cơ Keychron K2', 
    'ban-phim-co-keychron-k2', 
    'Bàn phím cơ Keychron K2 wireless với switch Blue', 
    'Bàn phím cơ Keychron K2 wireless với switch Blue, kết nối Bluetooth và USB-C', 
    'KEYBOARD001', 
    '3456789012345', 
    2000000.00, 
    2800000.00, 
    'publish', 
    'simple', 
    'Keychron', 
    800, 
    28, 
    8, 
    1, 
    1, 
    1, 
    NOW(), 
    NOW()
),
-- Test Product 4: Monitor
(
    'Màn hình Samsung 24 inch', 
    'man-hinh-samsung-24-inch', 
    'Màn hình Samsung 24 inch Full HD IPS', 
    'Màn hình Samsung 24 inch Full HD IPS với độ phân giải 1920x1080', 
    'MONITOR001', 
    '4567890123456', 
    3500000.00, 
    4200000.00, 
    'publish', 
    'simple', 
    'Samsung', 
    4000, 
    42, 
    3, 
    0, 
    1, 
    1, 
    NOW(), 
    NOW()
),
-- Test Product 5: Phone
(
    'iPhone 15 Pro Max', 
    'iphone-15-pro-max', 
    'iPhone 15 Pro Max 256GB Titanium Natural', 
    'iPhone 15 Pro Max 256GB Titanium Natural với chip A17 Pro và camera 48MP', 
    'PHONE001', 
    '5678901234567', 
    28000000.00, 
    32000000.00, 
    'publish', 
    'simple', 
    'Apple', 
    221, 
    320, 
    2, 
    1, 
    1, 
    1, 
    NOW(), 
    NOW()
);

-- Create inventory records for test products
INSERT INTO inventories (product_id, warehouse_id, quantity, created_at, updated_at)
SELECT 
    p.id, 
    1, -- warehouse_id = 1 (default warehouse)
    CASE 
        WHEN p.sku = 'LAPTOP001' THEN 10
        WHEN p.sku = 'MOUSE001' THEN 25
        WHEN p.sku = 'KEYBOARD001' THEN 15
        WHEN p.sku = 'MONITOR001' THEN 8
        WHEN p.sku = 'PHONE001' THEN 5
    END as quantity,
    NOW(),
    NOW()
FROM products p 
WHERE p.sku IN ('LAPTOP001', 'MOUSE001', 'KEYBOARD001', 'MONITOR001', 'PHONE001');
