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
        Schema::create('guests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('password');
            $table->string('email', 100)->unique();
            $table->string('full_name', 100);
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->string('active_code', 100);
            $table->string('group_id', 100);
            $table->string('guest_avatar');
            $table->string('guest_address');
            $table->string('guest_phone', 30);
            $table->string('guest_birthday');
            $table->enum('status', ['inactive', 'deactive', 'active', 'blocked']);
            $table->string('provider');
            $table->string('provider_id');
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
        Schema::dropIfExists('guests');
    }
};
