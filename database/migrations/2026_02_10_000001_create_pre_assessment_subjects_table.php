<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreAssessmentSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pre_assessment_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pre_assessment_subjects');
    }
}
