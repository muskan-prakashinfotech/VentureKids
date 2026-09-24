<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('school_id')->default(0);
            $table->integer('grade3_student')->default(0);
            $table->integer('grade4_student')->default(0);
            $table->integer('grade5_student')->default(0);
            $table->integer('grade6_student')->default(0);
            $table->integer('grade7_student')->default(0);
            $table->integer('grade8_student')->default(0);
            $table->integer('grade9_student')->default(0);
            $table->integer('grade10_student')->default(0);
            $table->integer('grade11_student')->default(0);
            $table->integer('grade12_student')->default(0);
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
        Schema::dropIfExists('student_grades');
    }
}
