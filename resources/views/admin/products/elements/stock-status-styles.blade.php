<!--begin::Stock Status Styles-->
<style>
/* Stock Status Badge Styles */
.stock-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border-radius: 0.475rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
}

.stock-status-badge i {
    margin-right: 0.375rem;
    font-size: 0.875rem;
}

/* In Stock - Success */
.stock-status-in-stock {
    background-color: rgba(80, 205, 137, 0.1);
    color: #50cd89;
    border: 1px solid rgba(80, 205, 137, 0.25);
}

.stock-status-in-stock i {
    color: #50cd89;
}

/* Low Stock - Warning */
.stock-status-low-stock {
    background-color: rgba(255, 199, 0, 0.1);
    color: #ffc700;
    border: 1px solid rgba(255, 199, 0, 0.25);
}

.stock-status-low-stock i {
    color: #ffc700;
}

/* Out of Stock - Danger */
.stock-status-out-of-stock {
    background-color: rgba(241, 65, 108, 0.1);
    color: #f1416c;
    border: 1px solid rgba(241, 65, 108, 0.25);
}

.stock-status-out-of-stock i {
    color: #f1416c;
}

/* Stock quantity number styling */
.stock-quantity-number {
    font-weight: 700;
    font-size: 0.875rem;
}

/* Hover effects */
.stock-status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stock-status-badge {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
    }
    
    .stock-status-badge i {
        margin-right: 0.25rem;
        font-size: 0.75rem;
    }
}

/* DataTable column specific styling */
.dataTables_wrapper .stock-quantity-column {
    text-align: center;
    vertical-align: middle;
}

/* Filter dropdown styling */
.stock-status-filter .select2-container--default .select2-selection--single {
    border: 1px solid #e4e6ef;
    border-radius: 0.475rem;
    height: calc(1.5em + 1.3rem + 2px);
}

.stock-status-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #5e6278;
    line-height: calc(1.5em + 1.3rem);
    padding-left: 0.75rem;
    padding-right: 2rem;
}

/* Custom scrollbar for dropdown */
.select2-results__options::-webkit-scrollbar {
    width: 6px;
}

.select2-results__options::-webkit-scrollbar-track {
    background: #f1f3f6;
    border-radius: 3px;
}

.select2-results__options::-webkit-scrollbar-thumb {
    background: #c4c4c4;
    border-radius: 3px;
}

.select2-results__options::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Loading state */
.stock-status-loading {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.25);
}

.stock-status-loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Tooltip styling for stock status */
.stock-status-tooltip {
    position: relative;
    cursor: help;
}

.stock-status-tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #2b2b2b;
    color: white;
    padding: 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 0.25rem;
}

.stock-status-tooltip:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #2b2b2b;
    z-index: 1000;
}

/* Print styles */
@media print {
    .stock-status-badge {
        background-color: transparent !important;
        border: 1px solid #000 !important;
        color: #000 !important;
    }
    
    .stock-status-badge i {
        display: none;
    }
}
</style>
<!--end::Stock Status Styles-->
