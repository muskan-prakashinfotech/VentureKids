<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectDetailsToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('student_grade_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('project_theme_id')->nullable()->after('student_grade_id');

            // New project detail fields
            $table->longText('project_overview')->nullable()->after('description'); 
            $table->longText('project_skills')->nullable()->after('project_overview'); 
            $table->longText('project_challenges')->nullable()->after('project_skills');
            $table->tinyInteger('is_publish')->default(1)->comment('1=publish, 0=draft')->after('project_status');

            // $table->foreign('student_grade_id')->references('id')->on('student_grade')->onDelete('set null');
            // $table->foreign('project_theme_id')->references('id')->on('project_themes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
          $table->dropForeign(['student_grade_id']);
            $table->dropForeign(['project_theme_id']);
            $table->dropColumn([
                'student_grade_id',
                'project_theme_id',
                'project_overview',
                'project_skills',
                'project_challenges',
                'is_publish',
            ]);
        });
    }
}
