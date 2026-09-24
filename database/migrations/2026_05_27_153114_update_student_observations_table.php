<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStudentObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->after('id');
            $table->string('observation_id')->nullable()->after('school_id');
            $table->unsignedBigInteger('external_session_id')->nullable()->after('observation_id');
            $table->string('short_note')->nullable()->after('external_session_id');
            $table->string('image')->nullable()->after('short_note');
            $table->string('grade_id')->nullable()->change();

            $table->dropColumn(['stream_id','session_id','session_image','remarkable_note','skill_id','achievement_points']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->unsignedInteger('stream_id')->nullable(false);
            $table->unsignedInteger('session_id')->nullable(false);
            $table->string('session_image')->nullable();
            $table->text('remarkable_note')->nullable();
            $table->string('skill_id')->nullable();
            $table->string('achievement_points')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable()->change();

            $table->dropColumn(['school_id','observation_id','external_session_id','short_note','image']);
        });
    }
}
