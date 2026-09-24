<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionDateToTrainerReportsAndObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainer_session_reports', function (Blueprint $table) {
            $table->date('session_date')->nullable()->after('school_id');
        });

        Schema::table('student_observations', function (Blueprint $table) {
            $table->date('session_date')->nullable()->after('external_session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainer_session_reports', function (Blueprint $table) {
            $table->dropColumn('session_date');
        });

        Schema::table('student_observations', function (Blueprint $table) {
            $table->dropColumn('session_date');
        });
    }
}
