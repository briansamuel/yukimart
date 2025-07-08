/**
 * Test script for Global Functions in Variant Manager
 * Copy and paste this into browser console to test
 */

console.log('🧪 Testing Global Functions for Variant Manager...');

// 1. Check if global functions exist
console.log('1. Checking global functions...');
const globalFunctions = [
    'removeAttributeValue',
    'removeAttributeRow', 
    'removeVariantRow'
];

globalFunctions.forEach(funcName => {
    if (typeof window[funcName] === 'function') {
        console.log(`✅ ${funcName} is available globally`);
    } else {
        console.log(`❌ ${funcName} is NOT available globally`);
    }
});

// 2. Check KTProductVariantManager
console.log('2. Checking KTProductVariantManager...');
if (typeof KTProductVariantManager !== 'undefined') {
    console.log('✅ KTProductVariantManager found');
    
    const methods = [
        'showVariantsContainer',
        'addAttributeRow',
        'getVariantData',
        'submitVariants',
        'hasVariants',
        'loadVariants',
        'getSelectedAttributes'
    ];
    
    methods.forEach(method => {
        if (typeof KTProductVariantManager[method] === 'function') {
            console.log(`✅ KTProductVariantManager.${method} available`);
        } else {
            console.log(`❌ KTProductVariantManager.${method} NOT available`);
        }
    });
} else {
    console.log('❌ KTProductVariantManager not found');
}

// 3. Test functions with mock data
console.log('3. Testing functions with mock data...');

// Mock test for removeAttributeValue
window.testRemoveAttributeValue = function() {
    console.log('🧪 Testing removeAttributeValue...');
    try {
        if (typeof removeAttributeValue === 'function') {
            // This will log but won't actually remove anything without proper DOM
            removeAttributeValue(0, 'test-value');
            console.log('✅ removeAttributeValue executed without errors');
        } else {
            console.log('❌ removeAttributeValue not available');
        }
    } catch (e) {
        console.log('❌ removeAttributeValue error:', e.message);
    }
};

// Mock test for removeAttributeRow
window.testRemoveAttributeRow = function() {
    console.log('🧪 Testing removeAttributeRow...');
    try {
        if (typeof removeAttributeRow === 'function') {
            // This will log but won't actually remove anything without proper DOM
            removeAttributeRow(0);
            console.log('✅ removeAttributeRow executed without errors');
        } else {
            console.log('❌ removeAttributeRow not available');
        }
    } catch (e) {
        console.log('❌ removeAttributeRow error:', e.message);
    }
};

// Mock test for removeVariantRow
window.testRemoveVariantRow = function() {
    console.log('🧪 Testing removeVariantRow...');
    try {
        if (typeof removeVariantRow === 'function') {
            // This will log but won't actually remove anything without proper DOM
            removeVariantRow(0);
            console.log('✅ removeVariantRow executed without errors');
        } else {
            console.log('❌ removeVariantRow not available');
        }
    } catch (e) {
        console.log('❌ removeVariantRow error:', e.message);
    }
};

// 4. Test DOM elements
console.log('4. Checking DOM elements...');
const elements = [
    '#kt_product_variants_container',
    '#add_new_attribute_row_btn',
    '#attribute_rows_container',
    '#variant_details_container',
    '#variant_details_table'
];

elements.forEach(selector => {
    const element = document.querySelector(selector);
    if (element) {
        console.log(`✅ ${selector} found in DOM`);
        console.log(`   - Visible: ${element.offsetParent !== null}`);
        console.log(`   - Display: ${element.style.display}`);
    } else {
        console.log(`❌ ${selector} NOT found in DOM`);
    }
});

// 5. Create test functions for manual testing
console.log('5. Creating manual test functions...');

window.createTestAttributeRow = function() {
    console.log('🔧 Creating test attribute row...');
    const container = document.querySelector('#attribute_rows_container');
    if (container) {
        const rowIndex = container.children.length;
        const rowHTML = `
            <div class="attribute-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <select class="form-select attribute-select" data-row-index="${rowIndex}">
                            <option value="">Chọn thuộc tính</option>
                            <option value="1">Màu sắc</option>
                            <option value="2">Kích thước</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" class="form-control attribute-values-tagify"
                               placeholder="Nhập giá trị và enter"
                               data-row-index="${rowIndex}" disabled>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-light-danger remove-attribute-row"
                                data-row-index="${rowIndex}" onclick="removeAttributeRow(${rowIndex})">
                            <i class="fa fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHTML);
        console.log(`✅ Test attribute row ${rowIndex} created`);
        return rowIndex;
    } else {
        console.log('❌ Attribute rows container not found');
        return null;
    }
};

window.createTestVariantRow = function() {
    console.log('🔧 Creating test variant row...');
    const tableBody = document.querySelector('#variant_details_table tbody');
    if (tableBody) {
        const variantIndex = tableBody.children.length;
        const rowHTML = `
            <tr data-variant-index="${variantIndex}">
                <td>Test Variant ${variantIndex + 1}</td>
                <td><input type="text" class="form-control" value="TEST-${variantIndex + 1}" data-field="sku"></td>
                <td><input type="number" class="form-control" value="100000" data-field="sale_price"></td>
                <td><input type="number" class="form-control" value="10" data-field="stock_quantity"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-light-danger remove-variant"
                            data-variant-index="${variantIndex}" onclick="removeVariantRow(${variantIndex})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', rowHTML);
        console.log(`✅ Test variant row ${variantIndex} created`);
        return variantIndex;
    } else {
        console.log('❌ Variant table body not found');
        return null;
    }
};

// 6. Test complete workflow
window.testCompleteWorkflow = function() {
    console.log('🚀 Testing complete workflow...');
    
    // Step 1: Show container
    if (typeof KTProductVariantManager !== 'undefined' && KTProductVariantManager.showVariantsContainer) {
        KTProductVariantManager.showVariantsContainer();
        console.log('✅ Step 1: Container shown');
    }
    
    // Step 2: Add attribute row
    setTimeout(() => {
        if (typeof KTProductVariantManager !== 'undefined' && KTProductVariantManager.addAttributeRow) {
            KTProductVariantManager.addAttributeRow();
            console.log('✅ Step 2: Attribute row added');
        } else {
            const rowIndex = createTestAttributeRow();
            if (rowIndex !== null) {
                console.log('✅ Step 2: Test attribute row created');
            }
        }
        
        // Step 3: Test remove functions
        setTimeout(() => {
            console.log('✅ Step 3: Testing remove functions...');
            testRemoveAttributeValue();
            testRemoveAttributeRow();
            testRemoveVariantRow();
            
            console.log('🎉 Complete workflow test finished!');
        }, 1000);
    }, 500);
};

// 7. Instructions
console.log('📋 Manual test commands available:');
console.log('   testRemoveAttributeValue()  - Test remove attribute value');
console.log('   testRemoveAttributeRow()    - Test remove attribute row');
console.log('   testRemoveVariantRow()      - Test remove variant row');
console.log('   createTestAttributeRow()    - Create test attribute row');
console.log('   createTestVariantRow()      - Create test variant row');
console.log('   testCompleteWorkflow()      - Test complete workflow');

// 8. Auto-run basic tests
console.log('🔄 Running basic tests...');
testRemoveAttributeValue();
testRemoveAttributeRow();
testRemoveVariantRow();

// 9. Final status
setTimeout(() => {
    console.log('📊 Final Status Summary:');
    console.log('Global Functions:');
    globalFunctions.forEach(funcName => {
        const status = typeof window[funcName] === 'function' ? '✅' : '❌';
        console.log(`   ${status} ${funcName}`);
    });
    
    console.log('KTProductVariantManager:', typeof KTProductVariantManager !== 'undefined' ? '✅' : '❌');
    console.log('DOM Elements:');
    elements.forEach(selector => {
        const status = document.querySelector(selector) ? '✅' : '❌';
        console.log(`   ${status} ${selector}`);
    });
    
    console.log('🎯 Test complete! Use manual commands above for further testing.');
}, 2000);
