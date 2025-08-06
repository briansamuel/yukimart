// Simple test file
console.log('Test file loaded');

class TestReturnTableManager extends BaseTableManager {
    constructor() {
        super();
        console.log('TestReturnTableManager created');
    }

    getSelectAllId() {
        return 'select-all-returns';
    }

    getRowCheckboxes() {
        return document.querySelectorAll('#returns-table-body input[type="checkbox"]');
    }

    getItemName() {
        return 'đơn trả hàng';
    }

    loadData() {
        console.log('Loading return data...');

        // Simple implementation - just show empty state for now
        const tbody = document.querySelector('#returns-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="17" class="text-center py-5">
                        <div class="table-empty-message">Không có dữ liệu đơn trả hàng.</div>
                    </td>
                </tr>
            `;
        }
    }
}

window.TestReturnTableManager = TestReturnTableManager;
console.log('TestReturnTableManager defined');
