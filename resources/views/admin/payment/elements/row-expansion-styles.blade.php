<style>
/* Payment List Styles */
.payment-detail-panel {
    background: #f8f9fa;
    border-left: 4px solid #009ef7;
    margin: 0;
    padding: 0;
}

.payment-detail-panel .card {
    border: none;
    box-shadow: none;
    background: transparent;
}

.payment-detail-panel .nav-tabs {
    border-bottom: 1px solid #e4e6ea;
    background: #fff;
    margin: 0;
    padding: 0 20px;
}

.payment-detail-panel .nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #7e8299;
    font-weight: 600;
    padding: 15px 20px;
    margin-bottom: -1px;
}

.payment-detail-panel .nav-tabs .nav-link.active {
    color: #009ef7;
    border-bottom-color: #009ef7;
    background: transparent;
}

.payment-detail-panel .tab-content {
    padding: 20px;
    background: #fff;
}

.payment-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.payment-info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #e4e6ea;
}

.payment-info-item.primary {
    border-left-color: #009ef7;
}

.payment-info-item.success {
    border-left-color: #50cd89;
}

.payment-info-item.warning {
    border-left-color: #ffc700;
}

.payment-info-item.danger {
    border-left-color: #f1416c;
}

.payment-info-label {
    font-size: 12px;
    font-weight: 600;
    color: #7e8299;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.payment-info-value {
    font-size: 14px;
    font-weight: 600;
    color: #181c32;
}

.payment-actions {
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #e4e6ea;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 768px) {
    .payment-info-grid {
        grid-template-columns: 1fr;
    }
    
    .payment-actions {
        flex-direction: column;
    }
    
    .payment-actions .btn {
        width: 100%;
    }
}

/* Animation */
.payment-detail-panel {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Table row hover effect */
#kt_payments_table tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

/* Status badges */
.badge {
    font-size: 11px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 6px;
}

.badge-warning {
    background-color: #fff8dd;
    color: #f1bc00;
}

.badge-success {
    background-color: #e8fff3;
    color: #50cd89;
}

.badge-danger {
    background-color: #fff5f8;
    color: #f1416c;
}

.badge-primary {
    background-color: #eff8ff;
    color: #009ef7;
}

.badge-info {
    background-color: #f0f9ff;
    color: #7239ea;
}

.badge-light-success {
    background-color: #e8fff3;
    color: #50cd89;
}

.badge-light-primary {
    background-color: #eff8ff;
    color: #009ef7;
}

.badge-light-info {
    background-color: #f0f9ff;
    color: #7239ea;
}

.badge-light-warning {
    background-color: #fff8dd;
    color: #f1bc00;
}

.badge-light-secondary {
    background-color: #f5f8fa;
    color: #7e8299;
}

.badge-secondary {
    background-color: #f5f8fa;
    color: #7e8299;
}
</style>
