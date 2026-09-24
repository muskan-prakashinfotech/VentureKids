<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLearningOutcomeAndSkillFocusToTrainerSessionReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainer_session_reports', function (Blueprint $table) {
            $table->text('learning_outcome')->nullable()->after('highlights_feedback');
            $table->text('skill_focus')->nullable()->after('learning_outcome');
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
            $table->dropColumn(['learning_outcome', 'skill_focus']);
        });
    }
}
