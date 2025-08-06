/**
 * Global Date Utilities for YukiMart Admin
 * Provides consistent date formatting across all modules
 */

window.DateUtils = (function() {
    'use strict';

    /**
     * Format date string to Vietnamese locale date
     * @param {string} dateString - Date string in various formats
     * @returns {string} Formatted date string or 'N/A' if invalid
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            // Try to parse as ISO string first (created_at_raw)
            let date = new Date(dateString);
            
            // If invalid, try to parse Vietnamese format (dd/mm/yyyy)
            if (isNaN(date.getTime())) {
                // Convert dd/mm/yyyy hh:mm to yyyy-mm-dd hh:mm
                const parts = dateString.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})\s*(\d{1,2}:\d{2})?/);
                if (parts) {
                    const [, day, month, year, time] = parts;
                    const isoString = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}${time ? 'T' + time + ':00' : 'T00:00:00'}`;
                    date = new Date(isoString);
                }
            }
            
            if (isNaN(date.getTime())) {
                console.warn('DateUtils: Invalid date string:', dateString);
                return 'N/A';
            }
            
            return date.toLocaleDateString('vi-VN');
        } catch (error) {
            console.error('DateUtils: Error formatting date:', dateString, error);
            return 'N/A';
        }
    }

    /**
     * Format time string to Vietnamese locale time
     * @param {string} dateString - Date string in various formats
     * @returns {string} Formatted time string or 'N/A' if invalid
     */
    function formatTime(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            // Try to parse as ISO string first (created_at_raw)
            let date = new Date(dateString);
            
            // If invalid, try to parse Vietnamese format (dd/mm/yyyy)
            if (isNaN(date.getTime())) {
                // Convert dd/mm/yyyy hh:mm to yyyy-mm-dd hh:mm
                const parts = dateString.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})\s*(\d{1,2}:\d{2})?/);
                if (parts) {
                    const [, day, month, year, time] = parts;
                    const isoString = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}${time ? 'T' + time + ':00' : 'T00:00:00'}`;
                    date = new Date(isoString);
                }
            }
            
            if (isNaN(date.getTime())) {
                console.warn('DateUtils: Invalid time string:', dateString);
                return 'N/A';
            }
            
            return date.toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('DateUtils: Error formatting time:', dateString, error);
            return 'N/A';
        }
    }

    /**
     * Format date and time together
     * @param {string} dateString - Date string in various formats
     * @returns {object} Object with date and time properties
     */
    function formatDateTime(dateString) {
        return {
            date: formatDate(dateString),
            time: formatTime(dateString),
            full: `${formatDate(dateString)} ${formatTime(dateString)}`
        };
    }

    /**
     * Format date for form inputs (YYYY-MM-DD)
     * @param {string} dateString - Date string in various formats
     * @returns {string} Date in YYYY-MM-DD format or empty string if invalid
     */
    function formatForInput(dateString) {
        if (!dateString) return '';
        
        try {
            let date = new Date(dateString);
            
            // If invalid, try to parse Vietnamese format
            if (isNaN(date.getTime())) {
                const parts = dateString.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
                if (parts) {
                    const [, day, month, year] = parts;
                    date = new Date(`${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`);
                }
            }
            
            if (isNaN(date.getTime())) {
                return '';
            }
            
            return date.toISOString().split('T')[0];
        } catch (error) {
            console.error('DateUtils: Error formatting for input:', dateString, error);
            return '';
        }
    }

    /**
     * Get relative time (time ago)
     * @param {string} dateString - Date string in various formats
     * @returns {string} Relative time string
     */
    function getTimeAgo(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            let date = new Date(dateString);
            
            if (isNaN(date.getTime())) {
                const parts = dateString.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})\s*(\d{1,2}:\d{2})?/);
                if (parts) {
                    const [, day, month, year, time] = parts;
                    const isoString = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}${time ? 'T' + time + ':00' : 'T00:00:00'}`;
                    date = new Date(isoString);
                }
            }
            
            if (isNaN(date.getTime())) {
                return 'N/A';
            }
            
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'vừa xong';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} phút trước`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} giờ trước`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} ngày trước`;
            if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} tháng trước`;
            return `${Math.floor(diffInSeconds / 31536000)} năm trước`;
        } catch (error) {
            console.error('DateUtils: Error getting time ago:', dateString, error);
            return 'N/A';
        }
    }

    /**
     * Validate if a date string is valid
     * @param {string} dateString - Date string to validate
     * @returns {boolean} True if valid, false otherwise
     */
    function isValidDate(dateString) {
        if (!dateString) return false;
        
        try {
            let date = new Date(dateString);
            
            if (isNaN(date.getTime())) {
                const parts = dateString.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
                if (parts) {
                    const [, day, month, year] = parts;
                    date = new Date(`${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`);
                }
            }
            
            return !isNaN(date.getTime());
        } catch (error) {
            return false;
        }
    }

    // Public API
    return {
        formatDate: formatDate,
        formatTime: formatTime,
        formatDateTime: formatDateTime,
        formatForInput: formatForInput,
        getTimeAgo: getTimeAgo,
        isValidDate: isValidDate
    };
})();

// Also make functions available globally for backward compatibility
window.formatDate = window.DateUtils.formatDate;
window.formatTime = window.DateUtils.formatTime;

console.log('DateUtils loaded successfully');
