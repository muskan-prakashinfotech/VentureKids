<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRealqToAssessmentStudentReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE assessment_student_report MODIFY assessment_type ENUM('standard','realq') NOT NULL DEFAULT 'standard'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE assessment_student_report MODIFY assessment_type ENUM('standard') NOT NULL DEFAULT 'standard'");
    }
}
