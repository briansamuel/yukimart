/**
 * OrderCalculator - Shared component for order/invoice calculations
 */
class OrderCalculator {
    constructor(options = {}) {
        this.options = {
            currencySymbol: '₫',
            locale: 'vi-VN',
            ...options
        };
    }

    /**
     * Calculate subtotal from items
     */
    calculateSubtotal(items) {
        if (!Array.isArray(items)) return 0;
        
        return items.reduce((total, item) => {
            const quantity = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.price) || 0;
            return total + (quantity * price);
        }, 0);
    }

    /**
     * Calculate final amount
     */
    calculateFinalAmount(subtotal, discountAmount = 0, otherAmount = 0) {
        const sub = parseFloat(subtotal) || 0;
        const discount = parseFloat(discountAmount) || 0;
        const other = parseFloat(otherAmount) || 0;
        
        return Math.max(0, sub - discount + other);
    }

    /**
     * Calculate discount percentage
     */
    calculateDiscountPercentage(subtotal, discountAmount) {
        const sub = parseFloat(subtotal) || 0;
        const discount = parseFloat(discountAmount) || 0;
        
        if (sub === 0) return 0;
        return (discount / sub) * 100;
    }

    /**
     * Calculate discount amount from percentage
     */
    calculateDiscountFromPercentage(subtotal, discountPercentage) {
        const sub = parseFloat(subtotal) || 0;
        const percent = parseFloat(discountPercentage) || 0;
        
        return (sub * percent) / 100;
    }

    /**
     * Calculate change amount
     */
    calculateChange(finalAmount, paidAmount) {
        const final = parseFloat(finalAmount) || 0;
        const paid = parseFloat(paidAmount) || 0;
        
        return Math.max(0, paid - final);
    }

    /**
     * Calculate remaining amount
     */
    calculateRemaining(finalAmount, paidAmount) {
        const final = parseFloat(finalAmount) || 0;
        const paid = parseFloat(paidAmount) || 0;
        
        return Math.max(0, final - paid);
    }

    /**
     * Format currency
     */
    formatCurrency(amount, showSymbol = true) {
        const num = parseFloat(amount) || 0;
        
        if (showSymbol) {
            return new Intl.NumberFormat(this.options.locale, {
                style: 'currency',
                currency: 'VND'
            }).format(num);
        } else {
            return new Intl.NumberFormat(this.options.locale).format(num);
        }
    }

    /**
     * Parse currency string to number
     */
    parseCurrency(currencyString) {
        if (typeof currencyString === 'number') return currencyString;
        
        // Remove currency symbols and spaces, keep only numbers and decimal points
        const cleaned = String(currencyString).replace(/[^\d.,]/g, '');
        
        // Handle Vietnamese number format (1.000.000,50)
        if (cleaned.includes(',')) {
            const parts = cleaned.split(',');
            if (parts.length === 2) {
                const integerPart = parts[0].replace(/\./g, '');
                const decimalPart = parts[1];
                return parseFloat(`${integerPart}.${decimalPart}`);
            }
        }
        
        // Handle standard format (1000000.50)
        return parseFloat(cleaned.replace(/\./g, '')) || 0;
    }

    /**
     * Validate amount
     */
    validateAmount(amount, min = 0, max = null) {
        const num = this.parseCurrency(amount);
        
        if (isNaN(num)) return false;
        if (num < min) return false;
        if (max !== null && num > max) return false;
        
        return true;
    }

    /**
     * Calculate tax amount
     */
    calculateTax(amount, taxRate) {
        const amt = parseFloat(amount) || 0;
        const rate = parseFloat(taxRate) || 0;
        
        return (amt * rate) / 100;
    }

    /**
     * Calculate amount before tax
     */
    calculateAmountBeforeTax(amountWithTax, taxRate) {
        const amt = parseFloat(amountWithTax) || 0;
        const rate = parseFloat(taxRate) || 0;
        
        if (rate === 0) return amt;
        
        return amt / (1 + (rate / 100));
    }

    /**
     * Round to currency precision
     */
    roundToCurrency(amount, precision = 0) {
        const num = parseFloat(amount) || 0;
        return Math.round(num * Math.pow(10, precision)) / Math.pow(10, precision);
    }

    /**
     * Calculate totals for an order/invoice
     */
    calculateTotals(data) {
        const items = data.items || [];
        const discountAmount = parseFloat(data.discount_amount) || 0;
        const otherAmount = parseFloat(data.other_amount) || 0;
        const taxRate = parseFloat(data.tax_rate) || 0;
        
        // Calculate subtotal
        const subtotal = this.calculateSubtotal(items);
        
        // Calculate tax
        const taxAmount = this.calculateTax(subtotal - discountAmount, taxRate);
        
        // Calculate final amount
        const finalAmount = this.calculateFinalAmount(subtotal, discountAmount, otherAmount + taxAmount);
        
        return {
            subtotal: this.roundToCurrency(subtotal),
            discount_amount: this.roundToCurrency(discountAmount),
            other_amount: this.roundToCurrency(otherAmount),
            tax_amount: this.roundToCurrency(taxAmount),
            final_amount: this.roundToCurrency(finalAmount),
            formatted: {
                subtotal: this.formatCurrency(subtotal),
                discount_amount: this.formatCurrency(discountAmount),
                other_amount: this.formatCurrency(otherAmount),
                tax_amount: this.formatCurrency(taxAmount),
                final_amount: this.formatCurrency(finalAmount)
            }
        };
    }

    /**
     * Update UI with calculated totals
     */
    updateTotalsUI(totals, containerSelector = '.order-totals') {
        const container = $(containerSelector);
        
        container.find('.subtotal-amount').text(totals.formatted.subtotal);
        container.find('.discount-amount').text(totals.formatted.discount_amount);
        container.find('.other-amount').text(totals.formatted.other_amount);
        container.find('.tax-amount').text(totals.formatted.tax_amount);
        container.find('.final-amount').text(totals.formatted.final_amount);
        
        // Update input values if they exist
        container.find('input[name="subtotal"]').val(totals.subtotal);
        container.find('input[name="discount_amount"]').val(totals.discount_amount);
        container.find('input[name="other_amount"]').val(totals.other_amount);
        container.find('input[name="tax_amount"]').val(totals.tax_amount);
        container.find('input[name="final_amount"]').val(totals.final_amount);
    }

    /**
     * Auto-format currency inputs
     */
    setupCurrencyInputs(selector = '.currency-input') {
        $(document).on('input', selector, (e) => {
            const input = $(e.target);
            const value = this.parseCurrency(input.val());
            
            if (!isNaN(value)) {
                // Format and set value
                const formatted = this.formatCurrency(value, false);
                input.val(formatted);
            }
        });

        $(document).on('blur', selector, (e) => {
            const input = $(e.target);
            const value = this.parseCurrency(input.val());
            
            if (!isNaN(value)) {
                input.val(this.formatCurrency(value, false));
            } else {
                input.val('0');
            }
        });
    }

    /**
     * Calculate payment breakdown
     */
    calculatePaymentBreakdown(finalAmount, payments = []) {
        const final = parseFloat(finalAmount) || 0;
        const totalPaid = payments.reduce((sum, payment) => {
            return sum + (parseFloat(payment.amount) || 0);
        }, 0);
        
        const remaining = Math.max(0, final - totalPaid);
        const overpaid = Math.max(0, totalPaid - final);
        
        return {
            final_amount: final,
            total_paid: totalPaid,
            remaining_amount: remaining,
            overpaid_amount: overpaid,
            is_fully_paid: remaining === 0,
            is_overpaid: overpaid > 0,
            formatted: {
                final_amount: this.formatCurrency(final),
                total_paid: this.formatCurrency(totalPaid),
                remaining_amount: this.formatCurrency(remaining),
                overpaid_amount: this.formatCurrency(overpaid)
            }
        };
    }
}
