/**
 * Test script to verify single attribute row creation
 * Copy and paste this into browser console
 */

console.log('🧪 Testing Single Attribute Row Creation...');

// 1. Check current DOM structure
console.log('1. Checking DOM structure...');
const oldContainer = document.querySelector('#kt_product_variants_container');
const newAttributeContainer = document.querySelector('#attribute_selection_container');
const variantContainer = document.querySelector('#variant_details_container');
const button = document.querySelector('#add_new_attribute_row_btn');

console.log('Old container (should be null):', oldContainer);
console.log('New attribute container:', newAttributeContainer);
console.log('Variant container:', variantContainer);
console.log('Add button:', button);

// 2. Check button listeners
console.log('2. Checking button listeners...');
if (button) {
    console.log('Button has listener attached:', button.hasAttribute('data-listener-attached'));
    console.log('Button onclick:', button.onclick);
    console.log('Button event listeners:', getEventListeners ? getEventListeners(button) : 'getEventListeners not available');
}

// 3. Count current attribute rows
function countAttributeRows() {
    const rows = document.querySelectorAll('#attribute_rows_container .attribute-row');
    console.log(`Current attribute rows: ${rows.length}`);
    return rows.length;
}

// 4. Test single click
window.testSingleClick = function() {
    console.log('🎯 Testing single button click...');
    const initialCount = countAttributeRows();
    console.log(`Initial rows: ${initialCount}`);
    
    if (button) {
        // Simulate click
        button.click();
        
        // Check after short delay
        setTimeout(() => {
            const finalCount = countAttributeRows();
            console.log(`Final rows: ${finalCount}`);
            console.log(`Rows added: ${finalCount - initialCount}`);
            
            if (finalCount - initialCount === 1) {
                console.log('✅ SUCCESS: Only 1 row added');
            } else if (finalCount - initialCount === 2) {
                console.log('❌ FAIL: 2 rows added (duplicate issue)');
            } else {
                console.log(`⚠️ UNEXPECTED: ${finalCount - initialCount} rows added`);
            }
        }, 500);
    } else {
        console.log('❌ Button not found');
    }
};

// 5. Test multiple clicks
window.testMultipleClicks = function() {
    console.log('🎯 Testing multiple button clicks...');
    const initialCount = countAttributeRows();
    console.log(`Initial rows: ${initialCount}`);
    
    if (button) {
        // Click 3 times with delays
        button.click();
        setTimeout(() => {
            button.click();
            setTimeout(() => {
                button.click();
                setTimeout(() => {
                    const finalCount = countAttributeRows();
                    console.log(`Final rows: ${finalCount}`);
                    console.log(`Expected: ${initialCount + 3}, Actual: ${finalCount}`);
                    
                    if (finalCount === initialCount + 3) {
                        console.log('✅ SUCCESS: Correct number of rows added');
                    } else {
                        console.log('❌ FAIL: Incorrect number of rows');
                    }
                }, 200);
            }, 200);
        }, 200);
    }
};

// 6. Check for duplicate listeners
window.checkDuplicateListeners = function() {
    console.log('🔍 Checking for duplicate listeners...');
    
    if (button) {
        const originalClick = button.onclick;
        let clickCount = 0;
        
        // Add test listener to count clicks
        const testListener = function() {
            clickCount++;
            console.log(`Click detected: ${clickCount}`);
        };
        
        button.addEventListener('click', testListener);
        
        // Trigger click
        button.click();
        
        setTimeout(() => {
            console.log(`Total clicks detected: ${clickCount}`);
            if (clickCount === 1) {
                console.log('✅ No duplicate listeners detected');
            } else {
                console.log('❌ Duplicate listeners detected');
            }
            
            // Remove test listener
            button.removeEventListener('click', testListener);
        }, 100);
    }
};

// 7. Manual row creation test
window.createManualRow = function() {
    console.log('🔧 Creating manual attribute row...');
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
                                data-row-index="${rowIndex}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHTML);
        console.log(`✅ Manual row ${rowIndex} created`);
        return rowIndex;
    } else {
        console.log('❌ Container not found');
        return null;
    }
};

// 8. Show containers for testing
window.showContainers = function() {
    console.log('📦 Showing containers...');
    if (newAttributeContainer) {
        newAttributeContainer.style.display = 'block';
        console.log('✅ Attribute container shown');
    }
    if (variantContainer) {
        variantContainer.style.display = 'block';
        console.log('✅ Variant container shown');
    }
};

// 9. Reset test environment
window.resetTest = function() {
    console.log('🔄 Resetting test environment...');
    const rows = document.querySelectorAll('#attribute_rows_container .attribute-row');
    rows.forEach(row => row.remove());
    console.log(`✅ Removed ${rows.length} attribute rows`);
    
    if (variantContainer) {
        variantContainer.style.display = 'none';
    }
    
    countAttributeRows();
};

// 10. Auto-run basic tests
console.log('🔄 Running basic checks...');
countAttributeRows();

if (newAttributeContainer) {
    console.log('✅ New container structure in place');
} else {
    console.log('❌ New container not found');
}

if (!oldContainer) {
    console.log('✅ Old container successfully removed');
} else {
    console.log('❌ Old container still exists');
}

// 11. Instructions
console.log('📋 Available test commands:');
console.log('   testSingleClick()        - Test single button click');
console.log('   testMultipleClicks()     - Test multiple clicks');
console.log('   checkDuplicateListeners() - Check for duplicate listeners');
console.log('   createManualRow()        - Create row manually');
console.log('   showContainers()         - Show all containers');
console.log('   resetTest()              - Reset test environment');
console.log('   countAttributeRows()     - Count current rows');

// 12. Product type test
window.testProductType = function() {
    console.log('🔄 Testing product type change...');
    const productTypeSelect = document.querySelector('#product_type');
    if (productTypeSelect) {
        console.log('Setting product type to variable...');
        productTypeSelect.value = 'variable';
        productTypeSelect.dispatchEvent(new Event('change'));
        
        setTimeout(() => {
            console.log('Attribute container visible:', newAttributeContainer?.offsetParent !== null);
            console.log('Button visible:', button?.offsetParent !== null);
        }, 100);
    } else {
        console.log('❌ Product type select not found');
    }
};

console.log('🎯 Test setup complete! Use commands above to test.');

// Auto-show containers for testing
setTimeout(() => {
    showContainers();
}, 1000);
