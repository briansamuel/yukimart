<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropInventoryAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop inventory_alerts table if it exists
        Schema::dropIfExists('inventory_alerts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate inventory_alerts table if needed for rollback
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->unsigned();
            $table->enum('alert_type', [
                'low_stock', 'out_of_stock', 'overstock', 'expired', 'damaged', 'supplier_delay'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('message');
            $table->json('alert_data')->nullable(); // Additional data for the alert
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->bigInteger('resolved_by_user')->unsigned()->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('resolved_by_user')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['product_id', 'alert_type'], 'idx_product_alert_type');
            $table->index(['severity', 'is_resolved'], 'idx_severity_resolved');
            $table->index(['is_read', 'is_resolved'], 'idx_read_resolved');
            $table->index(['created_at'], 'idx_created_at');
        });
    }
}
