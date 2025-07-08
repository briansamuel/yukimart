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
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('password');
            $table->string('email', 100)->unique();
            $table->string('full_name', 100);
            $table->string('active_code', 100);
            $table->string('group_id', 100);
            $table->string('agent_avatar');
            $table->string('agent_address');
            $table->string('agent_phone', 30);
            $table->timestamp('agent_birthday')->nullable();
            $table->bigInteger('agent_parent');
            $table->enum('status', ['inactive', 'deactive', 'active', 'blocked']);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_visit')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agents');
    }
};
