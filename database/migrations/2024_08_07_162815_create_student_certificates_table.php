<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id')->nullable(false)->references('id')->on('students')->onDelete('cascade');
            $table->unsignedInteger('grade_id')->nullable(false);
            $table->enum('released_by', ['Admin', 'Trainer'])->default('Trainer');
            $table->unsignedInteger('trainer_id')->nullable(false)->references('id')->on('trainers')->onDelete('cascade');
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
        Schema::dropIfExists('student_certificates');
    }
}
