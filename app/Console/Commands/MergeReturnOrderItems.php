<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use Illuminate\Support\Facades\DB;

class MergeReturnOrderItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'return-orders:merge-items {--return-order-id= : Specific return order ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate return order items with same product_id and unit_price';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $returnOrderId = $this->option('return-order-id');
        
        if ($returnOrderId) {
            $returnOrders = ReturnOrder::where('id', $returnOrderId)->get();
            if ($returnOrders->isEmpty()) {
                $this->error("Return order with ID {$returnOrderId} not found.");
                return 1;
            }
        } else {
            $returnOrders = ReturnOrder::with('returnOrderItems')->get();
        }

        $this->info('Starting to merge duplicate return order items...');
        
        $totalMerged = 0;
        $totalReturnOrders = $returnOrders->count();
        
        $progressBar = $this->output->createProgressBar($totalReturnOrders);
        $progressBar->start();

        foreach ($returnOrders as $returnOrder) {
            $merged = $this->mergeReturnOrderItems($returnOrder);
            $totalMerged += $merged;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
        $this->info("Completed! Merged {$totalMerged} duplicate items across {$totalReturnOrders} return orders.");
        
        return 0;
    }

    /**
     * Merge duplicate items for a specific return order.
     */
    private function mergeReturnOrderItems(ReturnOrder $returnOrder): int
    {
        $items = $returnOrder->returnOrderItems;
        
        if ($items->count() <= 1) {
            return 0; // No duplicates possible
        }

        // Group items by product_id and unit_price
        $groups = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->unit_price;
        });

        $mergedCount = 0;

        DB::transaction(function () use ($groups, &$mergedCount) {
            foreach ($groups as $groupKey => $groupItems) {
                if ($groupItems->count() <= 1) {
                    continue; // No duplicates in this group
                }

                // Keep the first item and merge others into it
                $primaryItem = $groupItems->first();
                $itemsToMerge = $groupItems->slice(1);

                $totalQuantity = $groupItems->sum('quantity_returned');
                $mergedNotes = $groupItems->pluck('notes')->filter()->unique()->implode('; ');

                // Update primary item with merged data
                $primaryItem->update([
                    'quantity_returned' => $totalQuantity,
                    'line_total' => $totalQuantity * $primaryItem->unit_price,
                    'notes' => $mergedNotes ?: null,
                    'sort_order' => 0, // Reset sort order
                ]);

                // Delete duplicate items
                foreach ($itemsToMerge as $item) {
                    $item->delete();
                    $mergedCount++;
                }
            }
        });

        // Recalculate return order totals
        if ($mergedCount > 0) {
            $returnOrder->calculateTotals();
        }

        return $mergedCount;
    }
}
