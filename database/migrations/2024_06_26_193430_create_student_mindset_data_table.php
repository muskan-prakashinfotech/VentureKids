<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentMindsetDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_mindset_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_observation_id')->nullable(false)->references('id')->on('student_observations')->onDelete('cascade');
            $table->unsignedInteger('mindset_id')->nullable(false)->references('id')->on('student_mindset')->onDelete('cascade');
            $table->string('value')->nullable(false);
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
        Schema::dropIfExists('student_mindset_data');
    }
}
