<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToStudentscotents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->dropColumn('learning_object');
            $table->dropColumn('outcome_session');
            $table->dropColumn('question_access_knowledge');
            $table->dropColumn('introduce_topic_student');
            $table->dropColumn('related_activity_one');
            $table->dropColumn('related_activity_two');
            $table->dropColumn('vocabulary');
            $table->dropColumn('tips_of_parents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->text('learning_object')->nullable()->default(null);
            $table->text('outcome_session')->nullable()->default(null);
            $table->text('question_access_knowledge')->nullable()->default(null);
            $table->text('introduce_topic_student')->nullable()->default(null);
            $table->text('related_activity_one')->nullable()->default(null);
            $table->text('related_activity_two')->nullable()->default(null);
            $table->text('vocabulary')->nullable()->default(null);
            $table->text('tips_of_parents')->nullable()->default(null);
        });
    }
}
