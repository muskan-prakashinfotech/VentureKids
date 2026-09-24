<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportTextToAssessmentStudentReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assessment_student_report', function (Blueprint $table) {
            $table->unsignedBigInteger('student_grade_id')->nullable()->after('student_id');
            $table->longText('report_text')->nullable()->after('report_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_student_report', function (Blueprint $table) {
            $table->dropColumn('report_text');
            $table->dropColumn('student_grade_id');
        });
    }
}
