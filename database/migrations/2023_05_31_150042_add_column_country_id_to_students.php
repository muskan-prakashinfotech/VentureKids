<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Students;

class AddColumnCountryIdToStudents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedInteger('country_id')->nullable()->default(1)->after('school_id')
                ->references('id')->on('countrys')->onDelete('cascade');
        });

        foreach (Students::with('school')->get() as $student) {
            if (!empty($student->school)) {
                $student->country_id = $student->school->country_id;
                $student->update();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
}
