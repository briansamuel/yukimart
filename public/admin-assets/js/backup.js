/**
 * Backup Management JavaScript
 */

class BackupManager {
    constructor() {
        this.init();
        this.loadStats();
    }

    init() {
        this.bindEvents();
        this.initFrequencyToggle();
        this.initTableSelection();
        this.initScheduleButtons();
    }

    bindEvents() {
        // Manual backup form
        const manualForm = document.getElementById('manual-backup-form');
        if (manualForm) {
            manualForm.addEventListener('submit', (e) => this.handleManualBackup(e));
        }

        // Schedule backup form
        const scheduleForm = document.getElementById('schedule-backup-form');
        if (scheduleForm) {
            scheduleForm.addEventListener('submit', (e) => this.handleScheduleBackup(e));
        }

        // Delete backup buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-backup')) {
                this.handleDeleteBackup(e.target.closest('.delete-backup'));
            }
            if (e.target.closest('.restore-backup')) {
                this.handleRestoreBackup(e.target.closest('.restore-backup'));
            }
            if (e.target.closest('.toggle-schedule')) {
                this.handleToggleSchedule(e.target.closest('.toggle-schedule'));
            }
            if (e.target.closest('.delete-schedule')) {
                this.handleDeleteSchedule(e.target.closest('.delete-schedule'));
            }
        });

        // Restore confirmation
        const confirmRestoreBtn = document.getElementById('confirm-restore-btn');
        if (confirmRestoreBtn) {
            confirmRestoreBtn.addEventListener('click', () => this.confirmRestore());
        }
    }

    initFrequencyToggle() {
        const self = this;

        // Simple approach - wait for DOM and bind directly
        setTimeout(function() {
            const frequencySelect = document.getElementById('frequency-select');
            if (frequencySelect) {
                frequencySelect.addEventListener('change', function(e) {
                    self.handleFrequencyChange(e.target.value);
                });

                // Also bind using onchange
                frequencySelect.onchange = function(e) {
                    self.handleFrequencyChange(e.target.value);
                };
            }
        }, 500);
    }

    handleFrequencyChange(selectedFrequency) {
        // Hide all frequency options
        const allOptions = document.querySelectorAll('.frequency-options');
        allOptions.forEach(option => {
            option.style.display = 'none';
        });

        // Show selected frequency options
        if (selectedFrequency) {
            const optionElement = document.getElementById(`${selectedFrequency}-options`);
            if (optionElement) {
                optionElement.style.display = 'flex';
            }
        }
    }

    initScheduleButtons() {
        // Bind toggle schedule buttons
        setTimeout(() => {
            const toggleButtons = document.querySelectorAll('.toggle-schedule');
            toggleButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleToggleSchedule(button);
                });
            });

            // Bind delete schedule buttons
            const deleteButtons = document.querySelectorAll('.delete-schedule');
            deleteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleDeleteSchedule(button);
                });
            });
        }, 1000);
    }

    initTableSelection() {
        // Manual backup table selection
        const backupTypeSelect = document.getElementById('backup-type-select');
        if (backupTypeSelect) {
            backupTypeSelect.addEventListener('change', (e) => {
                const tableSelection = document.getElementById('table-selection');
                if (tableSelection) {
                    tableSelection.style.display = e.target.value === 'selective' ? 'block' : 'none';
                }
            });
        }

        // Schedule backup table selection
        const scheduleBackupType = document.getElementById('schedule-backup-type');
        if (scheduleBackupType) {
            scheduleBackupType.addEventListener('change', (e) => {
                const tableSelection = document.getElementById('schedule-table-selection');
                if (tableSelection) {
                    tableSelection.style.display = e.target.value === 'selective' ? 'block' : 'none';
                }
            });
        }

        // Select all functionality for manual backup
        this.initSelectAll('select-all-tables', '.table-checkbox');
        
        // Select all functionality for schedule backup
        this.initSelectAll('schedule-select-all-tables', '.schedule-table-checkbox');
    }

    initSelectAll(selectAllId, checkboxSelector) {
        const selectAllCheckbox = document.getElementById(selectAllId);
        if (!selectAllCheckbox) return;

        const tableCheckboxes = document.querySelectorAll(checkboxSelector);
        
        selectAllCheckbox.addEventListener('change', function() {
            tableCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        tableCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll(`${checkboxSelector}:checked`).length;
                const totalCount = tableCheckboxes.length;
                
                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            });
        });
    }

    async handleManualBackup(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Get selected tables if selective backup
        if (data.backup_type === 'selective') {
            const selectedTables = Array.from(document.querySelectorAll('.table-checkbox:checked'))
                                       .map(cb => cb.value);
            if (selectedTables.length === 0) {
                this.showAlert('Vui lòng chọn ít nhất một bảng để sao lưu!', 'warning');
                return;
            }
            data.tables = selectedTables;
        }

        try {
            // Show progress modal
            this.showProgressModal();
            
            const response = await fetch('/admin/backup/manual', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Start polling for progress
                this.pollBackupProgress(result.backup.id);
            } else {
                this.hideProgressModal();
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.hideProgressModal();
            this.showAlert('Có lỗi xảy ra khi tạo backup!', 'error');
            console.error('Backup error:', error);
        }
    }

    async handleScheduleBackup(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Get selected tables if selective backup
        if (data.backup_type === 'selective') {
            const selectedTables = Array.from(document.querySelectorAll('.schedule-table-checkbox:checked'))
                                       .map(cb => cb.value);
            if (selectedTables.length === 0) {
                this.showAlert('Vui lòng chọn ít nhất một bảng để sao lưu!', 'warning');
                return;
            }
            data.tables = selectedTables;
        }

        try {
            const response = await fetch('/admin/backup/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Lịch backup đã được tạo thành công!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('Có lỗi xảy ra khi tạo lịch backup!', 'error');
            console.error('Schedule error:', error);
        }
    }

    async handleDeleteBackup(button) {
        const backupId = button.dataset.id;
        
        if (!confirm('Bạn có chắc chắn muốn xóa backup này?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/backup/${backupId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Backup đã được xóa thành công!', 'success');
                button.closest('tr').remove();
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('Có lỗi xảy ra khi xóa backup!', 'error');
            console.error('Delete error:', error);
        }
    }

    handleRestoreBackup(button) {
        const backupId = button.dataset.id;
        const backupName = button.dataset.name;
        
        // Show confirmation modal
        const modal = document.getElementById('restore-confirmation-modal');
        const nameElement = document.getElementById('restore-backup-name');
        const confirmBtn = document.getElementById('confirm-restore-btn');
        
        if (modal && nameElement && confirmBtn) {
            nameElement.textContent = backupName;
            confirmBtn.dataset.backupId = backupId;
            
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    async confirmRestore() {
        const confirmBtn = document.getElementById('confirm-restore-btn');
        const backupId = confirmBtn.dataset.backupId;
        
        if (!backupId) return;

        try {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang khôi phục...';

            const response = await fetch(`/admin/backup/restore/${backupId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Khôi phục dữ liệu thành công!', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('Có lỗi xảy ra khi khôi phục!', 'error');
            console.error('Restore error:', error);
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Khôi phục';
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('restore-confirmation-modal'));
            if (modal) modal.hide();
        }
    }

    async handleToggleSchedule(button) {
        const scheduleId = button.dataset.id;

        try {
            const response = await fetch(`/admin/backup/schedule/${scheduleId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                
                // Update button
                const icon = button.querySelector('i');
                if (result.is_active) {
                    icon.className = 'fas fa-pause';
                    button.dataset.active = '1';
                } else {
                    icon.className = 'fas fa-play';
                    button.dataset.active = '0';
                }
                
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('Có lỗi xảy ra!', 'error');
            console.error('Toggle error:', error);
        }
    }

    async handleDeleteSchedule(button) {
        const scheduleId = button.dataset.id;
        
        if (!confirm('Bạn có chắc chắn muốn xóa lịch backup này?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/backup/schedule/${scheduleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Lịch backup đã được xóa thành công!', 'success');
                button.closest('tr').remove();
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            this.showAlert('Có lỗi xảy ra khi xóa lịch backup!', 'error');
            console.error('Delete schedule error:', error);
        }
    }

    showProgressModal() {
        const modal = document.getElementById('backup-progress-modal');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            });
            bsModal.show();
            
            // Reset progress
            this.updateProgress(0, 'Đang chuẩn bị backup...');
        }
    }

    hideProgressModal() {
        const modal = document.getElementById('backup-progress-modal');
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }

    updateProgress(percentage, message) {
        const progressBar = document.getElementById('backup-progress-bar');
        const progressMessage = document.getElementById('backup-progress-message');
        
        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
            progressBar.setAttribute('aria-valuenow', percentage);
        }
        
        if (progressMessage) {
            progressMessage.textContent = message;
        }
    }

    async pollBackupProgress(backupId) {
        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch(`/admin/backup/progress/${backupId}`);
                const result = await response.json();
                
                this.updateProgress(result.progress, result.message);
                
                if (result.status === 'completed') {
                    clearInterval(pollInterval);
                    this.updateProgress(100, 'Backup hoàn thành thành công!');
                    
                    setTimeout(() => {
                        this.hideProgressModal();
                        this.showAlert('Backup đã được tạo thành công!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    }, 1000);
                } else if (result.status === 'failed') {
                    clearInterval(pollInterval);
                    this.hideProgressModal();
                    this.showAlert(result.message, 'error');
                }
            } catch (error) {
                clearInterval(pollInterval);
                this.hideProgressModal();
                this.showAlert('Có lỗi xảy ra khi kiểm tra tiến trình backup!', 'error');
                console.error('Progress poll error:', error);
            }
        }, 2000); // Poll every 2 seconds
    }

    async loadStats() {
        try {
            const response = await fetch('/admin/backup/stats');
            const stats = await response.json();
            
            // Update statistics cards
            this.updateStatCard('total-backups', stats.total_backups);
            this.updateStatCard('completed-backups', stats.completed_backups);
            this.updateStatCard('active-schedules', stats.active_schedules);
            this.updateStatCard('total-size', this.formatFileSize(stats.total_size));
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    }

    updateStatCard(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    formatFileSize(bytes) {
        if (!bytes) return '0 MB';
        
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
    }

    showAlert(message, type = 'info') {
        // Create alert element
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Find or create alert container
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.className = 'position-fixed top-0 end-0 p-3';
            alertContainer.style.zIndex = '9999';
            document.body.appendChild(alertContainer);
        }

        // Add alert
        alertContainer.insertAdjacentHTML('beforeend', alertHtml);

        // Auto remove after 5 seconds
        setTimeout(() => {
            const alerts = alertContainer.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[0].remove();
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new BackupManager();
});
