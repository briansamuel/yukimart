<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update reference_type from full class names to lowercase strings
        DB::table('payments')
            ->where('reference_type', 'App\\Models\\Invoice')
            ->update(['reference_type' => 'invoice']);
            
        DB::table('payments')
            ->where('reference_type', 'App\\Models\\ReturnOrder')
            ->update(['reference_type' => 'return_order']);
            
        DB::table('payments')
            ->where('reference_type', 'App\\Models\\Order')
            ->update(['reference_type' => 'order']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to full class names
        DB::table('payments')
            ->where('reference_type', 'invoice')
            ->update(['reference_type' => 'App\\Models\\Invoice']);
            
        DB::table('payments')
            ->where('reference_type', 'return_order')
            ->update(['reference_type' => 'App\\Models\\ReturnOrder']);
            
        DB::table('payments')
            ->where('reference_type', 'order')
            ->update(['reference_type' => 'App\\Models\\Order']);
    }
};
