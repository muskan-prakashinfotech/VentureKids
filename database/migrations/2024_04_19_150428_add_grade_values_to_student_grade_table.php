<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\StudentGrade;
use Carbon\Carbon;

class AddGradeValuesToStudentGradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['name' => 'Grade1'],
            ['name' => 'Grade2'],
            ['name' => 'Grade3'],
            ['name' => 'Grade4'],
            ['name' => 'Grade5'],
            ['name' => 'Grade6'],
            ['name' => 'Grade7'],
            ['name' => 'Grade8']
        ];

        $timestamp = Carbon::now();

        foreach ($data as &$record) {
            $record['created_at'] = $timestamp;
            $record['updated_at'] = $timestamp;
        }
        
        StudentGrade::insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_grade', function (Blueprint $table) {
            //
        });
    }
}
