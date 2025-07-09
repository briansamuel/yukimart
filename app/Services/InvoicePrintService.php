<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class InvoicePrintService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('invoice-print');
    }

    /**
     * Get print configuration
     */
    public function getConfig($key = null)
    {
        if ($key) {
            return data_get($this->config, $key);
        }
        
        return $this->config;
    }

    /**
     * Format currency amount
     */
    public function formatCurrency($amount)
    {
        $currency = $this->config['currency'];
        
        $formatted = number_format(
            $amount,
            $currency['decimal_places'],
            $currency['decimal_separator'],
            $currency['thousands_separator']
        );

        if ($currency['position'] === 'before') {
            return $currency['symbol'] . $formatted;
        }
        
        return $formatted . ' ' . $currency['symbol'];
    }

    /**
     * Get company information
     */
    public function getCompanyInfo()
    {
        return $this->config['company'];
    }

    /**
     * Get template configuration
     */
    public function getTemplateConfig($type = 'single')
    {
        return $this->config['template'][$type] ?? $this->config['template']['single'];
    }

    /**
     * Get styling configuration
     */
    public function getStyling()
    {
        return $this->config['styling'];
    }

    /**
     * Get layout configuration
     */
    public function getLayout()
    {
        return $this->config['layout'];
    }

    /**
     * Get field configuration
     */
    public function getFieldConfig($field = null)
    {
        if ($field) {
            return $this->config['fields'][$field] ?? null;
        }
        
        return $this->config['fields'];
    }

    /**
     * Get table configuration
     */
    public function getTableConfig()
    {
        return $this->config['table'];
    }

    /**
     * Get footer configuration
     */
    public function getFooterConfig()
    {
        return $this->config['footer'];
    }

    /**
     * Get print configuration
     */
    public function getPrintConfig()
    {
        return $this->config['print'];
    }

    /**
     * Check if field should be shown
     */
    public function shouldShowField($field)
    {
        $fieldConfig = $this->getFieldConfig($field);
        return $fieldConfig['show'] ?? true;
    }

    /**
     * Get field label
     */
    public function getFieldLabel($field)
    {
        $fieldConfig = $this->getFieldConfig($field);
        return $fieldConfig['label'] ?? ucfirst($field);
    }

    /**
     * Format date according to field configuration
     */
    public function formatDate($date, $field = 'invoice_date')
    {
        $fieldConfig = $this->getFieldConfig($field);
        $format = $fieldConfig['format'] ?? 'd/m/Y';
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass($status, $type = 'status')
    {
        $classes = [
            'status' => [
                'completed' => 'status-completed',
                'processing' => 'status-processing',
                'cancelled' => 'status-cancelled',
                'draft' => 'status-draft',
            ],
            'payment_status' => [
                'paid' => 'status-paid',
                'unpaid' => 'status-unpaid',
                'partial' => 'status-partial',
            ],
        ];

        return $classes[$type][$status] ?? 'status-default';
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status, $type = 'status')
    {
        $labels = [
            'status' => [
                'completed' => 'Hoàn thành',
                'processing' => 'Đang xử lý',
                'cancelled' => 'Đã hủy',
                'draft' => 'Nháp',
            ],
            'payment_status' => [
                'paid' => 'Đã thanh toán',
                'unpaid' => 'Chưa thanh toán',
                'partial' => 'Thanh toán một phần',
            ],
        ];

        return $labels[$type][$status] ?? ucfirst($status);
    }

    /**
     * Prepare invoice data for printing
     */
    public function prepareInvoiceData(Invoice $invoice)
    {
        return [
            'invoice' => $invoice,
            'config' => $this->config,
            'company' => $this->getCompanyInfo(),
            'styling' => $this->getStyling(),
            'layout' => $this->getLayout(),
            'fields' => $this->getFieldConfig(),
            'table' => $this->getTableConfig(),
            'footer' => $this->getFooterConfig(),
            'print' => $this->getPrintConfig(),
            'service' => $this,
        ];
    }

    /**
     * Prepare multiple invoices data for bulk printing
     */
    public function prepareBulkInvoiceData(Collection $invoices)
    {
        return [
            'invoices' => $invoices,
            'config' => $this->config,
            'company' => $this->getCompanyInfo(),
            'styling' => $this->getStyling(),
            'layout' => $this->getLayout(),
            'fields' => $this->getFieldConfig(),
            'table' => $this->getTableConfig(),
            'footer' => $this->getFooterConfig(),
            'print' => $this->getPrintConfig(),
            'service' => $this,
        ];
    }

    /**
     * Generate print styles CSS
     */
    public function generatePrintStyles()
    {
        $styling = $this->getStyling();
        
        return "
            body {
                font-family: {$styling['font_family']};
                font-size: {$styling['font_size']};
                line-height: {$styling['line_height']};
                color: {$styling['primary_color']};
            }
            
            .invoice-title h1 {
                color: {$styling['accent_color']};
            }
            
            .section-title {
                color: {$styling['primary_color']};
            }
            
            .status-paid {
                background-color: {$styling['success_color']}20;
                color: {$styling['success_color']};
            }
            
            .status-unpaid {
                background-color: {$styling['danger_color']}20;
                color: {$styling['danger_color']};
            }
            
            .status-partial {
                background-color: {$styling['warning_color']}20;
                color: {$styling['warning_color']};
            }
        ";
    }

    /**
     * Get table column configuration
     */
    public function getTableColumns()
    {
        $columns = $this->getTableConfig()['columns'];
        
        return collect($columns)->filter(function ($column) {
            return $column['show'] ?? true;
        });
    }

    /**
     * Get column width
     */
    public function getColumnWidth($column)
    {
        $config = $this->getTableConfig()['columns'][$column] ?? [];
        return $config['width'] ?? 'auto';
    }

    /**
     * Get column alignment
     */
    public function getColumnAlign($column)
    {
        $config = $this->getTableConfig()['columns'][$column] ?? [];
        return $config['align'] ?? 'left';
    }

    /**
     * Get column label
     */
    public function getColumnLabel($column)
    {
        $config = $this->getTableConfig()['columns'][$column] ?? [];
        return $config['label'] ?? ucfirst($column);
    }
}
