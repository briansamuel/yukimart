<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['branch_id']);
            
            // Drop the column
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Add the column back
            $table->foreignId('branch_id')->nullable()->after('ward')->constrained('branches')->onDelete('set null');
        });
    }
};
