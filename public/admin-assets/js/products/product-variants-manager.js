/**
 * Product Variants Manager
 * Handles auto-generation and management of product variants
 */

class ProductVariantsManager {
    constructor() {
        this.variants = [];
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Setup price link
        const setupPriceLink = document.getElementById('setup_price_link');
        if (setupPriceLink) {
            setupPriceLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupPriceModal();
            });
        }
    }

    /**
     * Generate variants based on selected attributes
     * Creates all possible combinations of attribute values
     */
    generateVariants() {
        const attributes = window.productAttributesManager ? window.productAttributesManager.getAttributes() : [];
        
        if (attributes.length === 0) {
            this.clearVariantsTable();
            return;
        }

        // Generate all combinations
        const combinations = this.generateCombinations(attributes);
        
        // Create variant objects
        this.variants = combinations.map((combo, index) => {
            // Check if variant already exists (to preserve user input)
            const existingVariant = this.findExistingVariant(combo);
            
            if (existingVariant) {
                return existingVariant;
            }

            // Create new variant
            return {
                id: `variant_${Date.now()}_${index}`,
                attributeValues: combo,
                attributeValuesText: combo.map(c => c.value).join(' - '),
                conversion: '1',
                sku: '',
                barcode: '',
                cost_price: 0,
                sale_price: 0,
                stock: 0,
                points: 0
            };
        });

        this.renderVariantsTable();
    }

    /**
     * Generate all combinations of attribute values
     */
    generateCombinations(attributes) {
        if (attributes.length === 0) return [];
        
        const result = [];
        
        const generate = (current, depth) => {
            if (depth === attributes.length) {
                result.push([...current]);
                return;
            }
            
            const attr = attributes[depth];
            attr.values.forEach(value => {
                current.push({
                    attributeId: attr.attributeId,
                    attributeName: attr.attributeName,
                    valueId: value.id,
                    value: value.value
                });
                generate(current, depth + 1);
                current.pop();
            });
        };
        
        generate([], 0);
        return result;
    }

    /**
     * Find existing variant by attribute values combination
     */
    findExistingVariant(combo) {
        return this.variants.find(variant => {
            if (variant.attributeValues.length !== combo.length) return false;
            
            return combo.every((c, i) => {
                const v = variant.attributeValues[i];
                return v.attributeId === c.attributeId && v.valueId === c.valueId;
            });
        });
    }

    /**
     * Render variants table
     */
    renderVariantsTable() {
        const tbody = document.getElementById('variants_table_body');
        if (!tbody) return;

        if (this.variants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-10">
                        Chọn thuộc tính để tự động tạo hàng cùng loại
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.variants.forEach((variant, index) => {
            html += `
                <tr>
                    <td>
                        <span class="fw-bold">${variant.attributeValuesText}</span>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                               value="${variant.conversion}" 
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'conversion', this.value)" />
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                               value="${variant.sku}" 
                               placeholder="Mã hàng"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'sku', this.value)" />
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                               value="${variant.barcode}" 
                               placeholder="Mã vạch"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'barcode', this.value)" />
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end" 
                               value="${variant.cost_price}" 
                               min="0" step="0.01"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'cost_price', this.value)" />
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end" 
                               value="${variant.sale_price}" 
                               min="0" step="0.01"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'sale_price', this.value)" />
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end" 
                               value="${variant.stock}" 
                               min="0"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'stock', this.value)" />
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end" 
                               value="${variant.points}" 
                               min="0"
                               onchange="window.productVariantsManager.updateVariantField(${index}, 'points', this.value)" />
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Update variant field value
     */
    updateVariantField(index, field, value) {
        if (this.variants[index]) {
            this.variants[index][field] = value;
        }
    }

    /**
     * Clear variants table
     */
    clearVariantsTable() {
        const tbody = document.getElementById('variants_table_body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-10">
                        Chọn thuộc tính để tự động tạo hàng cùng loại
                    </td>
                </tr>
            `;
        }
        this.variants = [];
    }

    /**
     * Open setup price modal
     */
    openSetupPriceModal() {
        const modal = new bootstrap.Modal(document.getElementById('kt_modal_setup_price'));
        modal.show();

        // Handle form submit
        const form = document.getElementById('kt_modal_setup_price_form');
        if (form) {
            form.onsubmit = (e) => {
                e.preventDefault();
                this.applyPriceToAllVariants();
                modal.hide();
            };
        }
    }

    /**
     * Apply price from setup modal to all variants
     */
    applyPriceToAllVariants() {
        const costPrice = parseFloat(document.querySelector('[name="setup_cost_price"]').value) || 0;
        const salePrice = parseFloat(document.querySelector('[name="setup_sale_price"]').value) || 0;

        this.variants.forEach(variant => {
            variant.cost_price = costPrice;
            variant.sale_price = salePrice;
        });

        this.renderVariantsTable();

        Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: 'Đã áp dụng giá cho tất cả biến thể',
            timer: 1500,
            showConfirmButton: false
        });
    }

    /**
     * Get all variants data
     */
    getVariants() {
        return this.variants;
    }

    /**
     * Set variants data
     */
    setVariants(variants) {
        this.variants = variants || [];
        this.renderVariantsTable();
    }

    /**
     * Clear all variants
     */
    clearVariants() {
        this.variants = [];
        this.clearVariantsTable();
    }

    /**
     * Validate variants data
     */
    validateVariants() {
        const errors = [];

        this.variants.forEach((variant, index) => {
            if (!variant.sku) {
                errors.push(`Biến thể "${variant.attributeValuesText}" chưa có mã hàng`);
            }
            if (variant.sale_price <= 0) {
                errors.push(`Biến thể "${variant.attributeValuesText}" chưa có giá bán`);
            }
        });

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.productVariantsManager = new ProductVariantsManager();
});

