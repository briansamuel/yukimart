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
        Schema::create('hosts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('host_name');
            $table->string('host_slug');
            $table->text('host_description');
            $table->string('host_thumbnail');
            $table->text('host_policy');
            $table->text('host_convenient');
            $table->string('host_address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('host_status', ['pending', 'publish', 'unpublish'])->default('pending');
            $table->text('host_gallery');
            $table->enum('host_type', ['resort', 'hotel', 'homestay', 'motel', 'villa', 'apartment', 'campsite'])->default('hotel');
            $table->bigInteger('lowest_room_rates')->default(0); // Giá phòng thấp nhất của Hotel
            $table->bigInteger('province_id')->default(0);
            $table->bigInteger('district_id')->default(0);
            $table->bigInteger('ward_id')->default(0);
            $table->string('province_name');
            $table->string('district_name');
            $table->string('ward_name');
            $table->string('language', 10)->default('vi');
            $table->bigInteger('created_by_agent')->default(0)->unsigned();
            $table->bigInteger('updated_by_agent')->default(0)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hosts');
    }
};
