<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id')->nullable(false)->references('id')->on('students')->onDelete('cascade');
            $table->unsignedInteger('trainer_id')->nullable(false)->references('id')->on('trainers')->onDelete('cascade');
            $table->unsignedInteger('grade_id')->nullable(false)->references('id')->on('grades')->onDelete('cascade');
            $table->unsignedInteger('stream_id')->nullable(false)->references('id')->on('streams')->onDelete('cascade');
            $table->unsignedInteger('session_id')->nullable(false)->references('id')->on('studentscontents')->onDelete('cascade');
            $table->string('session_image')->nullable();
            $table->text('remarkable_note')->nullable();
            $table->string('skill_id')->nullable();
            $table->string('achievement_points')->nullable();
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
        Schema::dropIfExists('student_observations');
    }
}
