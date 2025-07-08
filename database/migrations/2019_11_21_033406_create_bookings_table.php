<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('host_id')->unsigned();
            $table->bigInteger('room_id')->unsigned();
            $table->bigInteger('guest_id')->unsigned();
            $table->timestamp('checkin_date')->nullable();
            $table->timestamp('checkout_date')->nullable();
            $table->integer('night_booking')->default(1);
            $table->integer('room_amount')->default(1);
            $table->string('room_type');
            $table->string('bed_type');
            $table->integer('guest_amount')->default(1);
            $table->string('payment_method')->nullable();
            $table->text('guest_info');
            $table->float('booking_price')->default(0);
            $table->enum('booking_status', ['pending', 'paid', 'draft' ,'trash'])->default('pending');
            $table->bigInteger('created_by_agent')->default(0)->unsigned();
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
        Schema::dropIfExists('bookings');
    }
}
