<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectRewardsToStudentRewardPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('INSERT INTO `student_reward_points` (`student_id`, `reward_type`, `item_id`, `reward_points`, `created_at`, `updated_at`) SELECT `student_id`, "project", `id`, 5, NOW(),NOW() FROM projects');
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
