<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop old unique constraint on name if exists
        try {
            DB::statement('ALTER TABLE product_attributes DROP INDEX product_attributes_name_unique');
        } catch (Exception $e) {
            // Index might not exist
        }

        // Drop old unique constraint if exists
        try {
            DB::statement('ALTER TABLE product_attributes DROP INDEX unique_tenant_attribute_name');
        } catch (Exception $e) {
            // Index might not exist
        }

        // Add new unique constraint on tenant_id + name
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->unique(['tenant_id', 'name'], 'unique_tenant_attribute_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropUnique('unique_tenant_attribute_name');
        });
    }
};
