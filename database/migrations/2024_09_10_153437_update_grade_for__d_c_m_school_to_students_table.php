<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateGradeForDCMSchoolToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DCM School ID = 61975887
        
        // Grade 6 to be changed to Grade 5
        DB::statement("Update students set student_grade_id = 5 WHERE school_id = 61975887 AND student_grade_id = 6");

        // Grade 7 & 8 to be changed to Grade 6
        DB::statement("Update students set student_grade_id = 6 WHERE school_id = 61975887 AND student_grade_id in (7, 8)");

        // Grade 9 to be changed to Grade 7
        DB::statement("Update students set student_grade_id = 7 WHERE school_id = 61975887 AND student_grade_id = 9");

        // Grade 10 to be changed to Grade 8
        DB::statement("Update students set student_grade_id = 8 WHERE school_id = 61975887 AND student_grade_id = 10");

        // Grade 3 to be changed to Grade 4
        DB::statement("Update students set student_grade_id = 4 WHERE school_id = 61975887 AND student_grade_id = 3");

        // Grade 1 & 2 to be changed to Grade 3
        DB::statement("Update students set student_grade_id = 3 WHERE school_id = 61975887 AND student_grade_id in (1, 2)");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
}
