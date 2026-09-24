<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealqAssessmentSchoolAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realq_assessment_school_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->tinyInteger('realq_assessment_assigned')->default(0);
            $table->tinyInteger('realq_assessment_enabled')->default(0);
            $table->date('realq_assessment_enabled_from')->nullable();
            $table->date('realq_assessment_enabled_to')->nullable();
            $table->unsignedBigInteger('realq_assessment_assigned_board_id')->nullable();
            $table->unsignedBigInteger('realq_assessment_assigned_grade_id')->nullable();
            $table->string('realq_assessment_assigned_parameters_id')->nullable();
            $table->unsignedBigInteger('realq_assessment_assigned_scale_id')->nullable();
            $table->timestamps();

            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('realq_assessment_school_assignments');
    }
}
