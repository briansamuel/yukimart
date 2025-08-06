<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\ReturnOrder;
use App\Models\Payment;
use Exception;

/**
 * Service for generating unique prefixes for various entities
 */
class PrefixGeneratorService
{
    /**
     * Generate unique order code
     */
    public static function generateOrderCode(): string
    {
        return self::generateUniqueCode('DH', Order::class, 'order_code');
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(string $customPrefix = null): string
    {
        $prefix = $customPrefix ?? 'HD';
        return self::generateUniqueCode($prefix, Invoice::class, 'invoice_number');
    }

    /**
     * Generate unique exchange invoice number for return orders
     */
    public static function generateExchangeInvoiceNumber(): string
    {
        return self::generateInvoiceNumber('TH');
    }

    /**
     * Generate unique return order code
     */
    public static function generateReturnOrderCode(): string
    {
        return self::generateUniqueCode('TH', ReturnOrder::class, 'return_order_code');
    }

    /**
     * Generate unique payment code based on reference
     */
    public static function generatePaymentCode(string $referenceType, int $referenceId): string
    {
        switch ($referenceType) {
            case 'invoice':
                return "TTHD{$referenceId}";
            case 'order':
                return "TTDH{$referenceId}";
            case 'return':
                return "TTTH{$referenceId}";
            default:
                return "TT{$referenceId}";
        }
    }

    /**
     * Generate unique code with atomic operation to prevent race conditions
     */
    private static function generateUniqueCode(string $prefix, string $modelClass, string $field): string
    {
        $date = date('Ymd');
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                // Use database lock to prevent race conditions
                $lastRecord = $modelClass::where($field, 'like', $prefix . $date . '%')
                                        ->lockForUpdate()
                                        ->orderBy($field, 'desc')
                                        ->first();

                if ($lastRecord) {
                    $lastNumber = intval(substr($lastRecord->{$field}, -4));
                    $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '0001';
                }

                $code = $prefix . $date . $newNumber;

                // Double-check uniqueness before returning
                $exists = $modelClass::where($field, $code)->exists();
                if (!$exists) {
                    return $code;
                }

                // If exists, increment and try again
                $attempt++;

            } catch (Exception $e) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw new Exception("Unable to generate unique {$prefix} code after {$maxAttempts} attempts");
                }
                // Small delay before retry
                usleep(100000); // 100ms
            }
        }

        throw new Exception("Failed to generate unique {$prefix} code");
    }

    /**
     * Parse code to extract date and sequence
     */
    public static function parseCode(string $code): array
    {
        // Extract prefix (everything before date)
        $matches = [];
        if (preg_match('/^([A-Z_]+)(\d{8})(\d{4})$/', $code, $matches)) {
            return [
                'prefix' => $matches[1],
                'date' => $matches[2],
                'sequence' => $matches[3],
                'formatted_date' => date('d/m/Y', strtotime($matches[2]))
            ];
        }

        return [
            'prefix' => '',
            'date' => '',
            'sequence' => '',
            'formatted_date' => ''
        ];
    }

    /**
     * Get next sequence number for a prefix and date
     */
    public static function getNextSequence(string $prefix, string $modelClass, string $field, string $date = null): int
    {
        $date = $date ?? date('Ymd');
        
        $lastRecord = $modelClass::where($field, 'like', $prefix . $date . '%')
                                ->orderBy($field, 'desc')
                                ->first();

        if ($lastRecord) {
            return intval(substr($lastRecord->{$field}, -4)) + 1;
        }

        return 1;
    }

    /**
     * Validate code format
     */
    public static function validateCodeFormat(string $code, string $expectedPrefix): bool
    {
        $pattern = '/^' . preg_quote($expectedPrefix) . '\d{8}\d{4}$/';
        return preg_match($pattern, $code) === 1;
    }
}
