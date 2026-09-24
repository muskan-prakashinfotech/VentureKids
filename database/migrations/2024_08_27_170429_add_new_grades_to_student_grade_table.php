<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewGradesToStudentGradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['name' => 'Grade9', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade10', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('student_grade')->insert($data);
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
