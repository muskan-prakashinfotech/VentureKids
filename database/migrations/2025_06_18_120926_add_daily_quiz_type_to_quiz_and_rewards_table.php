<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDailyQuizTypeToQuizAndRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN content_type ENUM('session','weekly_challenge','daily_challenge')");

        DB::statement("ALTER TABLE quiz_attempts MODIFY COLUMN quiz_type ENUM('session','weekly_challenge','daily_challenge')");

        DB::statement("ALTER TABLE quiz_answers MODIFY COLUMN quiz_type ENUM('session','weekly_challenge','daily_challenge')");

        DB::statement("ALTER TABLE student_reward_points MODIFY COLUMN reward_type ENUM('weekly_challenge', 'scorm_learning_reward', 'project', 'challenge_respond', 'scorm_completion', 'external_reward', 'assignment_submission','daily_challenge')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
