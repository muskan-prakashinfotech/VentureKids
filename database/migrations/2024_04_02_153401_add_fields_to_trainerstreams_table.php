<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTrainerstreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainerstreams', function (Blueprint $table) {
            $table->string('video')->nullable()->after('agegroup_id');
            $table->string('video_name')->nullable()->after('video');
            $table->string('video_url')->nullable()->after('video_name');
            $table->string('drive_url')->nullable()->after('video_url');
            $table->string('pdf')->nullable()->after('drive_url');
            $table->string('pdf_name')->nullable()->after('pdf');
            $table->string('worksheet')->nullable()->after('pdf_name');
            $table->string('worksheet_name')->nullable()->after('worksheet');
            $table->text('learning_object')->nullable()->after('worksheet_name');
            $table->text('outcome_session')->nullable()->after('learning_object');
            $table->text('question_prior_knowledge')->nullable()->after('outcome_session');
            $table->text('introduce_topic')->nullable()->after('question_prior_knowledge');
            $table->text('related_activity_one')->nullable()->after('introduce_topic');
            $table->text('related_activity_two')->nullable()->after('related_activity_one');
            $table->text('vocabulary')->nullable()->after('related_activity_two');
            $table->text('home_assignments')->nullable()->after('vocabulary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainerstreams', function (Blueprint $table) {
            $table->dropColumn('video');
            $table->dropColumn('video_name');
            $table->dropColumn('video_url');
            $table->dropColumn('drive_url');
            $table->dropColumn('pdf');
            $table->dropColumn('pdf_name');
            $table->dropColumn('worksheet');
            $table->dropColumn('worksheet_name');
            $table->dropColumn('learning_object');
            $table->dropColumn('outcome_session');
            $table->dropColumn('question_prior_knowledge');
            $table->dropColumn('introduce_topic');
            $table->dropColumn('related_activity_one');
            $table->dropColumn('related_activity_two');
            $table->dropColumn('vocabulary');
            $table->dropColumn('home_assignments');
        });
    }
}
