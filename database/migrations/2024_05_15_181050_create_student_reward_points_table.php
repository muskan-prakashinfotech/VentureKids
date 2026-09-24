<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentRewardPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_reward_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id')->nullable()->references('id')->on('students')->onDelete('cascade');
            $table->enum('reward_type', ['weekly_challenge', 'scorm_learning_reward', 'project', 'challenge_respond', 'scorm_completion', 'external_reward'])->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('reward_points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_reward_points');
    }
}
