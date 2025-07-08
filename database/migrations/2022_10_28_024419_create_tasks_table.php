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
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('task_name');
            $table->string('task_description');
            $table->text('task_content');
            $table->text('task_attachments');
            $table->integer('task_progress');
            $table->text('task_notifications');
            $table->text('task_category');
            $table->text('project_id');
            $table->timestamp('task_due_date');
            $table->enum('task_status', ['in_progress', 'pending', 'completed']);
            $table->string('language', 10);
            $table->bigInteger('created_by_user')->default(0)->unsigned();
            $table->bigInteger('updated_by_user')->default(0)->unsigned();
            $table->softDeletes();
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
        Schema::dropIfExists('tasks');
    }
};
