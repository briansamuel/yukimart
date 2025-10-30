<?php

use Illuminate\Database\Migrations\Migration;
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
        // Update all permissions to have tenant_id = NULL
        // Permissions are global and shared across all tenants
        // Only roles need tenant_id for multi-tenant support
        DB::table('permissions')->update(['tenant_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse - permissions should remain global
    }
};
