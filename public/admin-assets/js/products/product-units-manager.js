/**
 * Product Units Manager
 * Handles product units CRUD operations
 */

class ProductUnitsManager {
    constructor() {
        this.units = []; // Array to store units
        this.currentEditingUnitIndex = null;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Open add unit modal
        const addUnitBtn = document.getElementById('add_unit_btn');
        if (addUnitBtn) {
            addUnitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openAddUnitModal();
            });
        }

        // Save unit form
        const saveUnitBtn = document.getElementById('save_unit_btn');
        if (saveUnitBtn) {
            saveUnitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveUnit(false);
            });
        }

        // Save and new unit button
        const saveAndNewUnitBtn = document.getElementById('save_and_new_unit_btn');
        if (saveAndNewUnitBtn) {
            saveAndNewUnitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveUnit(true);
            });
        }

        // Reset form when modal is hidden
        const addUnitModal = document.getElementById('kt_modal_add_unit');
        if (addUnitModal) {
            addUnitModal.addEventListener('hidden.bs.modal', () => {
                this.resetUnitForm();
            });
        }
    }

    openAddUnitModal(unitData = null) {
        const modal = new bootstrap.Modal(document.getElementById('kt_modal_add_unit'));
        
        if (unitData) {
            // Edit mode
            this.populateUnitForm(unitData);
        } else {
            // Create mode
            this.resetUnitForm();
        }
        
        modal.show();
    }

    populateUnitForm(unitData) {
        document.getElementById('unit_id').value = unitData.id || '';
        document.getElementById('unit_name').value = unitData.unit_name || '';
        document.getElementById('unit_sale_price').value = unitData.sale_price || 0;
        document.getElementById('is_direct_sale').checked = unitData.is_direct_sale !== false;
    }

    resetUnitForm() {
        document.getElementById('unit_id').value = '';
        document.getElementById('unit_name').value = '';
        document.getElementById('unit_sale_price').value = 0;
        document.getElementById('is_direct_sale').checked = true;
        this.currentEditingUnitIndex = null;
    }

    saveUnit(saveAndNew = false) {
        const unitId = document.getElementById('unit_id').value;
        const unitName = document.getElementById('unit_name').value.trim();
        const salePrice = parseFloat(document.getElementById('unit_sale_price').value) || 0;
        const isDirectSale = document.getElementById('is_direct_sale').checked;

        // Validation
        if (!unitName) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng nhập tên đơn vị',
            });
            return;
        }

        const unitData = {
            id: unitId || Date.now().toString(),
            unit_name: unitName,
            sale_price: salePrice,
            is_direct_sale: isDirectSale,
            conversion_rate: 1, // Default, will be set later
            is_base_unit: this.units.length === 0, // First unit is base unit
            sort_order: this.units.length,
        };

        if (this.currentEditingUnitIndex !== null) {
            // Update existing unit
            this.units[this.currentEditingUnitIndex] = unitData;
        } else {
            // Add new unit
            this.units.push(unitData);
        }

        this.renderUnitsList();

        if (saveAndNew) {
            this.resetUnitForm();
        } else {
            const modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_add_unit'));
            modal.hide();
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: saveAndNew ? 'Đã thêm đơn vị. Bạn có thể thêm đơn vị mới.' : 'Đã lưu đơn vị',
            timer: 1500,
            showConfirmButton: false
        });
    }

    renderUnitsList() {
        const unitsListContainer = document.getElementById('units_list');
        if (!unitsListContainer) return;

        if (this.units.length === 0) {
            unitsListContainer.innerHTML = '<p class="text-muted fs-7">Chưa có đơn vị nào</p>';
            return;
        }

        let html = '<div class="d-flex flex-column gap-3">';
        
        this.units.forEach((unit, index) => {
            const baseUnitBadge = unit.is_base_unit ? '<span class="badge badge-light-primary ms-2">Đơn vị cơ bản</span>' : '';
            const directSaleBadge = unit.is_direct_sale ? '<span class="badge badge-light-success ms-2">Bán trực tiếp</span>' : '';
            
            html += `
                <div class="d-flex align-items-center justify-content-between p-3 border border-gray-300 rounded">
                    <div>
                        <div class="fw-bold">${unit.unit_name}${baseUnitBadge}${directSaleBadge}</div>
                        <div class="text-muted fs-7">Giá bán: ${this.formatCurrency(unit.sale_price)}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-icon btn-light-primary" onclick="window.productUnitsManager.editUnit(${index})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="window.productUnitsManager.deleteUnit(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        unitsListContainer.innerHTML = html;
    }

    editUnit(index) {
        this.currentEditingUnitIndex = index;
        const unitData = this.units[index];
        this.openAddUnitModal(unitData);
    }

    deleteUnit(index) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa đơn vị này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.units.splice(index, 1);
                this.renderUnitsList();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Đã xóa',
                    text: 'Đơn vị đã được xóa',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    getUnits() {
        return this.units;
    }

    setUnits(units) {
        this.units = units || [];
        this.renderUnitsList();
    }

    clearUnits() {
        this.units = [];
        this.renderUnitsList();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.productUnitsManager = new ProductUnitsManager();
});

