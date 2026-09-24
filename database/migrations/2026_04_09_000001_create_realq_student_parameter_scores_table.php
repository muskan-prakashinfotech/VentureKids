<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realq_assessment_student_parameter_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('student_grade_id')->nullable();
            $table->unsignedBigInteger('assessment_report_id');
            $table->unsignedBigInteger('parameter_id')->nullable();
            $table->unsignedBigInteger('rubric_id')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'assessment_report_id'], 'realq_asp_student_report_idx');
            $table->index(['school_id', 'student_grade_id'], 'realq_asp_school_grade_idx');
            $table->index(['parameter_id', 'rubric_id'], 'realq_asp_param_rubric_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realq_assessment_student_parameter_scores');
    }
};
