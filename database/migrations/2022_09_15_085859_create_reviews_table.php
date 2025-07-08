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
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('host_id')->unsigned();
            $table->string('review_title');
            $table->text('review_content');
            $table->double('rating_review');
            $table->text('review_image')->nullable();
            $table->enum('review_status', ['publish', 'block', 'pending'])->default('pending');
            $table->string('language')->default('vi');
            $table->string('name_guest')->nullable();
            $table->bigInteger('created_by_guest')->default(0)->unsigned();
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
        Schema::dropIfExists('reviews');
    }
};
