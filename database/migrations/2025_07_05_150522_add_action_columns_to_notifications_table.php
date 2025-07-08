<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('action_url')->nullable()->after('expires_at');
            $table->string('action_text', 50)->nullable()->after('action_url');
            $table->string('icon', 50)->nullable()->after('action_text');
            $table->string('color', 20)->nullable()->after('icon');
            $table->boolean('is_dismissible')->default(true)->after('color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['action_url', 'action_text', 'icon', 'color', 'is_dismissible']);
        });
    }
};
