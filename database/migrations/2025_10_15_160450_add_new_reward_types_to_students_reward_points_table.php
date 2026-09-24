<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewRewardTypesToStudentsRewardPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_reward_points', function (Blueprint $table) {
            DB::statement("ALTER TABLE student_reward_points CHANGE reward_type reward_type ENUM('weekly_challenge', 'scorm_learning_reward', 'project', 'challenge_respond', 'youtube_completion','scorm_completion', 'external_reward', 'assignment_submission', 'daily_challenge', 'assignment_scorm_point', 'video_learning_point', 'project_ai_score_point', 'pre_assessment_score', 'post_assessment_score') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_reward_points', function (Blueprint $table) {
            DB::statement("ALTER TABLE student_reward_points CHANGE reward_type reward_type ENUM('weekly_challenge', 'scorm_learning_reward', 'project','youtube_completion', 'challenge_respond', 'scorm_completion', 'external_reward', 'assignment_submission', 'daily_challenge') NOT NULL");
        });
    }
}
