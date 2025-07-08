<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('page_title');
            $table->string('page_slug');
            $table->text('page_description');
            $table->longText('page_content');
            $table->string('page_seo_title')->nullable();
            $table->string('page_seo_keyword')->nullable();
            $table->string('page_seo_description')->nullable();

            $table->string('page_author', 100);
            $table->bigInteger('page_parent')->default(0);
            $table->string('page_template')->nullable();
            $table->enum('page_status', ['trash', 'pending', 'draft', 'publish']);
            $table->string('page_type', 20);
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
        Schema::dropIfExists('pages');
    }
}
