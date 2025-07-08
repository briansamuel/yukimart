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
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('host_id')->unsigned();
            $table->string('room_name');
            $table->string('room_description');
            $table->text('room_gallery');
            $table->float('room_area',8,2);
            $table->text('room_convenient');
            $table->string('room_option');
            $table->integer('price_one_night');
            $table->integer('sale_for_room')->default(0);
            $table->tinyInteger('guest_amount');
            $table->tinyInteger('room_amount_empty');
            $table->enum('room_status', ['available_room', 'no_vacancy']);
            $table->string('language')->default('vi');
            $table->bigInteger('created_by_agent')->default(0)->unsigned();
            $table->bigInteger('updated_by_agent')->default(0)->unsigned();
            $table->timestamps();
            // $table->foreign('created_by_agent')->references('id')->on('agents');
            // $table->foreign('updated_by_agent')->references('id')->on('agents');
            // $table->foreign('host_id')->references('id')->on('hosts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};
