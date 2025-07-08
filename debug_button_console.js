/**
 * Debug script for Add Attribute Button
 * Copy and paste this into browser console on the Add Product page
 */

console.log('🐛 Starting Add Attribute Button Debug...');

// 1. Check if variant manager exists
console.log('1. Checking KTProductVariantManager...');
if (typeof KTProductVariantManager !== 'undefined') {
    console.log('✅ KTProductVariantManager found');
} else {
    console.log('❌ KTProductVariantManager not found');
}

// 2. Check if button exists
console.log('2. Checking add_new_attribute_row_btn...');
const button = document.querySelector('#add_new_attribute_row_btn');
if (button) {
    console.log('✅ Button found:', button);
    console.log('   - Visible:', button.offsetParent !== null);
    console.log('   - Disabled:', button.disabled);
    console.log('   - Has listener:', button.hasAttribute('data-listener-attached'));
} else {
    console.log('❌ Button not found');
}

// 3. Check if container exists
console.log('3. Checking kt_product_variants_container...');
const container = document.querySelector('#kt_product_variants_container');
if (container) {
    console.log('✅ Container found:', container);
    console.log('   - Display:', container.style.display);
    console.log('   - Visible:', container.offsetParent !== null);
    console.log('   - Classes:', container.className);
} else {
    console.log('❌ Container not found');
}

// 4. Check product type select
console.log('4. Checking product_type select...');
const productTypeSelect = document.querySelector('#product_type');
if (productTypeSelect) {
    console.log('✅ Product type select found:', productTypeSelect);
    console.log('   - Current value:', productTypeSelect.value);
} else {
    console.log('❌ Product type select not found');
}

// 5. Manual functions to test
console.log('5. Creating manual test functions...');

window.debugShowContainer = function() {
    console.log('📦 Manually showing container...');
    if (container) {
        container.style.display = 'block';
        console.log('✅ Container shown');
        
        // Re-check button
        setTimeout(() => {
            const btn = document.querySelector('#add_new_attribute_row_btn');
            console.log('Button after show:', btn);
            console.log('Button visible:', btn && btn.offsetParent !== null);
        }, 100);
    } else {
        console.log('❌ Container not found');
    }
};

window.debugAddAttribute = function() {
    console.log('➕ Manually adding attribute row...');
    const rowsContainer = document.querySelector('#attribute_rows_container');
    if (rowsContainer) {
        const rowIndex = rowsContainer.children.length;
        const rowHTML = `
            <div class="attribute-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                <div class="row">
                    <div class="col-md-5">
                        <select class="form-control attribute-select" data-row-index="${rowIndex}">
                            <option value="">Chọn thuộc tính...</option>
                            <option value="1">Màu sắc</option>
                            <option value="2">Kích thước</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control attribute-values-tagify" 
                               placeholder="Nhập giá trị và enter" 
                               data-row-index="${rowIndex}" disabled>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-light-danger remove-attribute-row" 
                                data-row-index="${rowIndex}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        rowsContainer.insertAdjacentHTML('beforeend', rowHTML);
        console.log('✅ Attribute row added');
    } else {
        console.log('❌ Rows container not found');
    }
};

window.debugAttachListener = function() {
    console.log('🔗 Manually attaching button listener...');
    const btn = document.querySelector('#add_new_attribute_row_btn');
    if (btn) {
        if (!btn.hasAttribute('data-manual-listener')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🎯 Button clicked! (manual listener)');
                debugAddAttribute();
            });
            btn.setAttribute('data-manual-listener', 'true');
            console.log('✅ Manual listener attached');
        } else {
            console.log('⚠️ Manual listener already attached');
        }
    } else {
        console.log('❌ Button not found');
    }
};

window.debugProductType = function(type) {
    console.log(`🔄 Setting product type to: ${type}`);
    if (productTypeSelect) {
        productTypeSelect.value = type;
        productTypeSelect.dispatchEvent(new Event('change'));
        console.log('✅ Product type changed');
    } else {
        console.log('❌ Product type select not found');
    }
};

// 6. Try to use variant manager functions
console.log('6. Testing variant manager functions...');
if (typeof KTProductVariantManager !== 'undefined') {
    try {
        if (KTProductVariantManager.showVariantsContainer) {
            console.log('✅ showVariantsContainer method available');
        }
        if (KTProductVariantManager.addAttributeRow) {
            console.log('✅ addAttributeRow method available');
        }
    } catch (e) {
        console.log('❌ Error accessing variant manager methods:', e);
    }
}

// 7. Instructions
console.log('📋 Manual test commands:');
console.log('   debugShowContainer()     - Show variants container');
console.log('   debugAddAttribute()      - Add attribute row manually');
console.log('   debugAttachListener()    - Attach button listener manually');
console.log('   debugProductType("variable") - Set product type to variable');
console.log('   debugProductType("simple")   - Set product type to simple');

// 8. Auto-fix attempt
console.log('🔧 Attempting auto-fix...');
setTimeout(() => {
    // Show container if product type is variable
    if (productTypeSelect && productTypeSelect.value === 'variable') {
        debugShowContainer();
        setTimeout(() => {
            debugAttachListener();
        }, 200);
    }
    
    // Or just attach listener anyway
    debugAttachListener();
}, 500);

console.log('🎯 Debug complete! Check the functions above or use manual commands.');

// 9. Monitor for changes
console.log('👀 Setting up change monitor...');
if (productTypeSelect) {
    productTypeSelect.addEventListener('change', function() {
        console.log(`🔄 Product type changed to: ${this.value}`);
        if (this.value === 'variable') {
            setTimeout(() => {
                debugShowContainer();
                setTimeout(() => {
                    debugAttachListener();
                }, 200);
            }, 100);
        }
    });
}

// 10. Final status
setTimeout(() => {
    console.log('📊 Final Status:');
    console.log('   Button exists:', !!document.querySelector('#add_new_attribute_row_btn'));
    console.log('   Button visible:', document.querySelector('#add_new_attribute_row_btn')?.offsetParent !== null);
    console.log('   Container exists:', !!document.querySelector('#kt_product_variants_container'));
    console.log('   Container visible:', document.querySelector('#kt_product_variants_container')?.offsetParent !== null);
    console.log('   Product type:', document.querySelector('#product_type')?.value);
}, 1000);
