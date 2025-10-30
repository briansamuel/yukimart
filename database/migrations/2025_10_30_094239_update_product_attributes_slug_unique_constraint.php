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
        // Drop old unique constraint on slug
        try {
            DB::statement('ALTER TABLE product_attributes DROP INDEX product_attributes_slug_unique');
        } catch (Exception $e) {
            // Index might not exist
        }

        // Add new unique constraint on tenant_id + slug
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->unique(['tenant_id', 'slug'], 'unique_tenant_attribute_slug');
        });

        // Update product_attribute_values unique constraint
        try {
            DB::statement('ALTER TABLE product_attribute_values DROP INDEX product_attribute_values_attribute_id_slug_unique');
        } catch (Exception $e) {
            // Index might not exist
        }

        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->unique(['tenant_id', 'attribute_id', 'slug'], 'unique_tenant_attribute_value_slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore old unique constraints
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropUnique('unique_tenant_attribute_slug');
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropUnique('unique_tenant_attribute_value_slug');
        });

        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->unique(['attribute_id', 'slug']);
        });
    }
};
