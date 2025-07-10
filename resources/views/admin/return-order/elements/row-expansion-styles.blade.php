<style>
/* Return Order Row Expansion Styles */
.return-order-details-row {
    background-color: #f8f9fa;
    border-left: 4px solid #009ef7;
}

.return-order-details-content {
    padding: 20px;
    background: white;
    border-radius: 8px;
    margin: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.return-order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.return-order-info-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 3px solid #009ef7;
}

.return-order-info-label {
    font-size: 12px;
    font-weight: 600;
    color: #7e8299;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.return-order-info-value {
    font-size: 14px;
    font-weight: 600;
    color: #181c32;
}

.return-order-items-table {
    margin-top: 20px;
}

.return-order-items-table th {
    background-color: #f1f3f6;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    color: #7e8299;
    padding: 12px 15px;
}

.return-order-items-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e4e6ea;
    vertical-align: middle;
}

.return-order-product-info {
    display: flex;
    align-items: center;
}

.return-order-product-image {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    margin-right: 12px;
    object-fit: cover;
}

.return-order-product-details h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #181c32;
}

.return-order-product-details small {
    color: #7e8299;
    font-size: 12px;
}

.return-order-quantity {
    background: #e8f4fd;
    color: #009ef7;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 12px;
}

.return-order-price {
    font-weight: 600;
    color: #181c32;
}

.return-order-total {
    font-weight: 700;
    color: #009ef7;
    font-size: 16px;
}

.return-order-status-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.return-order-status-pending {
    background: #fff5e6;
    color: #f1975a;
}

.return-order-status-approved {
    background: #e8f5e8;
    color: #50cd89;
}

.return-order-status-rejected {
    background: #ffeaea;
    color: #f1416c;
}

.return-order-status-completed {
    background: #e1f0ff;
    color: #009ef7;
}

.return-order-reason-badge {
    background: #f1f3f6;
    color: #7e8299;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.return-order-actions {
    display: flex;
    gap: 8px;
}

.return-order-action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.return-order-action-approve {
    background: #e8f5e8;
    color: #50cd89;
}

.return-order-action-approve:hover {
    background: #50cd89;
    color: white;
}

.return-order-action-reject {
    background: #ffeaea;
    color: #f1416c;
}

.return-order-action-reject:hover {
    background: #f1416c;
    color: white;
}

.return-order-action-complete {
    background: #e1f0ff;
    color: #009ef7;
}

.return-order-action-complete:hover {
    background: #009ef7;
    color: white;
}

.return-order-action-view {
    background: #f1f3f6;
    color: #7e8299;
}

.return-order-action-view:hover {
    background: #7e8299;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .return-order-info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .return-order-details-content {
        margin: 5px;
        padding: 15px;
    }
    
    .return-order-items-table {
        font-size: 12px;
    }
    
    .return-order-items-table th,
    .return-order-items-table td {
        padding: 8px 10px;
    }
    
    .return-order-actions {
        flex-direction: column;
        gap: 5px;
    }
    
    .return-order-action-btn {
        width: 100%;
        text-align: center;
    }
}

/* Animation for row expansion */
.return-order-details-row td {
    animation: fadeInUp 0.3s ease-in-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading state */
.return-order-loading {
    text-align: center;
    padding: 40px;
    color: #7e8299;
}

.return-order-loading i {
    font-size: 24px;
    margin-bottom: 10px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error state */
.return-order-error {
    text-align: center;
    padding: 40px;
    color: #f1416c;
    background: #ffeaea;
    border-radius: 6px;
    margin: 10px;
}

.return-order-error i {
    font-size: 24px;
    margin-bottom: 10px;
}
</style>
