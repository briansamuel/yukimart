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
        Schema::table('invoices', function (Blueprint $table) {
            // Add sold_by column after created_by
            $table->unsignedBigInteger('sold_by')->nullable()->after('created_by');
            
            // Add foreign key constraint
            $table->foreign('sold_by')->references('id')->on('users')->onDelete('set null');
        });

        // Copy data from created_by to sold_by for existing records
        DB::statement('UPDATE invoices SET sold_by = created_by WHERE sold_by IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['sold_by']);
            
            // Drop the column
            $table->dropColumn('sold_by');
        });
    }
};
