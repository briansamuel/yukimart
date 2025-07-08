# Inventory Factories Update Summary

## Overview
Updated the InventoryFactory to set default quantity to 0 and created a comprehensive InventoryTransactionFactory for generating realistic inventory transaction data.

## Changes Made

### 1. Updated InventoryFactory
**File**: `database/factories/InventoryFactory.php`

#### Key Change:
```php
// Before
'quantity' => $this->faker->numberBetween(0, 500),

// After  
'quantity' => 0, // Default quantity is 0
```

#### Rationale:
- **Clean Start**: New products start with zero inventory
- **Realistic Workflow**: Inventory is added through transactions
- **Audit Trail**: All stock changes are tracked via transactions
- **Business Logic**: Matches real-world inventory management

#### Existing State Methods Still Available:
- `inStock()` - Sets quantity 50-500
- `lowStock()` - Sets quantity 1-10  
- `outOfStock()` - Sets quantity 0
- `quantity($amount)` - Sets specific quantity
- `quantityRange($min, $max)` - Sets quantity range
- `highStock()` - Sets quantity 200-1000
- `mediumStock()` - Sets quantity 50-199

### 2. Created InventoryTransactionFactory
**File**: `database/factories/InventoryTransactionFactory.php`

#### Features:
- **Complete Transaction Types**: All 9 transaction types supported
- **Realistic Data**: Proper quantity calculations and business logic
- **Flexible States**: Methods for each transaction type
- **Vietnamese Pricing**: Unit costs in VND (10K - 500K range)
- **Audit Trail**: Proper before/after quantity tracking
- **Location Support**: From/to location tracking

#### Transaction Types Supported:

1. **Purchase** - `purchase()`
   - Positive quantity change (10-100 units)
   - Supplier purchase scenarios
   - Warehouse destination

2. **Sale** - `sale()`
   - Negative quantity change (1-10 units)
   - Customer order references
   - Store/warehouse source

3. **Adjustment** - `adjustment()`
   - Positive or negative change (-50 to +50)
   - Inventory count corrections
   - System adjustments

4. **Return** - `return()`
   - Positive quantity change (1-10 units)
   - Customer return scenarios
   - Return area destination

5. **Damage** - `damage()`
   - Negative quantity change (1-5 units)
   - Damage reason tracking
   - Loss documentation

6. **Transfer** - `transfer()`
   - Zero net quantity change
   - Location-to-location movement
   - Warehouse transfers

7. **Reservation** - `reservation()`
   - Negative quantity change (1-10 units)
   - Order reservation tracking
   - Stock allocation

8. **Release** - `release()`
   - Positive quantity change (1-10 units)
   - Released reservations
   - Stock deallocation

9. **Initial** - `initial()`
   - Positive quantity change (10-500 units)
   - Initial stock setup
   - Starting inventory

#### State Methods:

**Transaction Type States:**
```php
InventoryTransaction::factory()->purchase()->create();
InventoryTransaction::factory()->sale()->create();
InventoryTransaction::factory()->adjustment()->create();
InventoryTransaction::factory()->return()->create();
InventoryTransaction::factory()->damage()->create();
InventoryTransaction::factory()->transfer()->create();
InventoryTransaction::factory()->reservation()->create();
InventoryTransaction::factory()->release()->create();
InventoryTransaction::factory()->initial()->create();
```

**Customization Methods:**
```php
// Set specific quantity change
->quantityChange(50)

// Set specific unit cost
->unitCost(100000)

// Link to specific product
->forProduct($product)

// Add custom notes
->withNotes('Custom transaction note')

// Link to reference model (Order, Purchase, etc.)
->withReference($order)

// Set specific locations
->withLocations('Warehouse A', 'Store Front')
```

#### Sample Data Generated:

**Purchase Transaction:**
```php
[
    'transaction_type' => 'purchase',
    'quantity_before' => 50,
    'quantity_change' => 25,
    'quantity_after' => 75,
    'unit_cost' => 150000,
    'total_value' => 3750000,
    'notes' => 'Purchase from supplier - ABC Company',
    'location_to' => 'Warehouse A'
]
```

**Sale Transaction:**
```php
[
    'transaction_type' => 'sale',
    'quantity_before' => 75,
    'quantity_change' => -3,
    'quantity_after' => 72,
    'unit_cost' => 150000,
    'total_value' => 450000,
    'notes' => 'Sale to customer - Order #123456',
    'location_from' => 'Store Front'
]
```

## Usage Examples

### Basic Transaction Creation:
```php
// Create random transaction
$transaction = InventoryTransaction::factory()->create();

// Create purchase transaction
$purchase = InventoryTransaction::factory()->purchase()->create();

// Create sale for specific product
$sale = InventoryTransaction::factory()
    ->sale()
    ->forProduct($product)
    ->create();
```

### Complex Transaction Scenarios:
```php
// Initial stock setup
$initial = InventoryTransaction::factory()
    ->initial()
    ->forProduct($product)
    ->quantityChange(100)
    ->unitCost(50000)
    ->withNotes('Initial inventory setup')
    ->create();

// Customer return with reference
$return = InventoryTransaction::factory()
    ->return()
    ->forProduct($product)
    ->withReference($order)
    ->withNotes('Customer return - defective item')
    ->create();

// Warehouse transfer
$transfer = InventoryTransaction::factory()
    ->transfer()
    ->forProduct($product)
    ->withLocations('Warehouse A', 'Warehouse B')
    ->create();
```

### Batch Transaction Creation:
```php
// Create 50 random transactions
InventoryTransaction::factory()->count(50)->create();

// Create 10 purchase transactions
InventoryTransaction::factory()->count(10)->purchase()->create();

// Create transactions for specific product
InventoryTransaction::factory()
    ->count(20)
    ->forProduct($product)
    ->create();
```

## Integration with Inventory System

### Workflow Example:
```php
// 1. Create product with zero inventory
$product = Product::factory()->create();
$inventory = Inventory::factory()->forProduct($product)->create(); // quantity = 0

// 2. Add initial stock via transaction
$initialTransaction = InventoryTransaction::factory()
    ->initial()
    ->forProduct($product)
    ->quantityChange(100)
    ->create();

// 3. Update inventory quantity
$inventory->update(['quantity' => 100]);

// 4. Create sale transaction
$saleTransaction = InventoryTransaction::factory()
    ->sale()
    ->forProduct($product)
    ->quantityChange(-5)
    ->create();

// 5. Update inventory quantity
$inventory->update(['quantity' => 95]);
```

## Benefits

### 1. **Realistic Test Data**
- Proper quantity calculations
- Business-appropriate transaction types
- Vietnamese market pricing

### 2. **Complete Audit Trail**
- Before/after quantities tracked
- Transaction types properly categorized
- Reference linking supported

### 3. **Flexible Testing**
- All transaction scenarios covered
- Easy customization via state methods
- Batch creation capabilities

### 4. **Business Logic Validation**
- Proper stock increase/decrease logic
- Location tracking for transfers
- Reference model linking

## Testing Scenarios

### Stock Movement Testing:
```php
// Test stock increase scenarios
$purchase = InventoryTransaction::factory()->purchase()->create();
$return = InventoryTransaction::factory()->return()->create();
$adjustment = InventoryTransaction::factory()->adjustment()->quantityChange(10)->create();

// Test stock decrease scenarios  
$sale = InventoryTransaction::factory()->sale()->create();
$damage = InventoryTransaction::factory()->damage()->create();
$reservation = InventoryTransaction::factory()->reservation()->create();
```

### Business Process Testing:
```php
// Test complete purchase-to-sale cycle
$product = Product::factory()->create();

// Initial stock
InventoryTransaction::factory()->initial()->forProduct($product)->create();

// Customer orders (reservations)
InventoryTransaction::factory()->count(5)->reservation()->forProduct($product)->create();

// Sales fulfillment
InventoryTransaction::factory()->count(3)->sale()->forProduct($product)->create();

// Returns processing
InventoryTransaction::factory()->count(1)->return()->forProduct($product)->create();
```

## Next Steps

1. **Update ProductSeeder**: Use transactions to set initial inventory
2. **Create TransactionSeeder**: Generate transaction history for products
3. **Test Integration**: Verify transactions update inventory correctly
4. **Add Validation**: Ensure quantity calculations are accurate

The factory system now provides a complete foundation for testing all aspects of the inventory management system with realistic, properly structured transaction data.
