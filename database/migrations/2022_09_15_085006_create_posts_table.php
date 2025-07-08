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
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('post_title');
            $table->string('post_slug');
            $table->text('post_description');
            $table->longText('post_content');
            $table->string('post_seo_title')->nullable();
            $table->string('post_seo_keyword')->nullable();
            $table->string('post_seo_description')->nullable();
            $table->string('post_thumbnail')->nullable();
            $table->string('post_author', 100);
            $table->bigInteger('post_parent')->default(0);
            $table->enum('post_status', ['trash', 'pending', 'draft', 'publish']);
            $table->string('post_type', 20);
            $table->tinyInteger('post_feature')->default(0);
            $table->string('language', 10)->default('vi');
            $table->bigInteger('created_by_user')->default(0)->unsigned();
            $table->bigInteger('updated_by_user')->default(0)->unsigned();
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
        Schema::dropIfExists('posts');
    }
};
