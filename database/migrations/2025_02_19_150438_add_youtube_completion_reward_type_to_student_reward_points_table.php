<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYoutubeCompletionRewardTypeToStudentRewardPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_reward_points', function (Blueprint $table) {
            DB::statement("ALTER TABLE student_reward_points MODIFY COLUMN reward_type ENUM('weekly_challenge', 'scorm_learning_reward', 'project', 'challenge_respond', 'scorm_completion', 'external_reward', 'assignment_submission','daily_challenge', 'youtube_completion')");
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
            
        });
    }
}
