<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_name');
            $table->string('project_type');
            $table->text('project_framework');
            $table->text('project_database');
            $table->text('project_description');
            $table->timestamp('project_due_date');
            $table->text('project_notifications');
            $table->text('project_category');
            $table->string('project_logo')->nullable();
            $table->integer('project_budget');
            $table->enum('project_status', ['in_progress', 'pending', 'completed']);
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
        Schema::dropIfExists('projects');
    }
}
