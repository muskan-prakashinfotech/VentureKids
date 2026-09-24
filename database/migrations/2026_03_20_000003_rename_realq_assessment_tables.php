<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameRealqAssessmentTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('pre_assessment_subjects') && !Schema::hasTable('realq_assessment_subjects')) {
            Schema::rename('pre_assessment_subjects', 'realq_assessment_subjects');
        }

        if (Schema::hasTable('topics') && !Schema::hasTable('realq_assessment_topics')) {
            Schema::rename('topics', 'realq_assessment_topics');
        }

        if (Schema::hasTable('assessment_questions') && !Schema::hasTable('realq_assessment_questions')) {
            Schema::rename('assessment_questions', 'realq_assessment_questions');
        }

        if (Schema::hasTable('assessment_answers') && !Schema::hasTable('realq_assessment_student_answers')) {
            Schema::rename('assessment_answers', 'realq_assessment_student_answers');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('realq_assessment_student_answers') && !Schema::hasTable('assessment_answers')) {
            Schema::rename('realq_assessment_student_answers', 'assessment_answers');
        }

        if (Schema::hasTable('realq_assessment_questions') && !Schema::hasTable('assessment_questions')) {
            Schema::rename('realq_assessment_questions', 'assessment_questions');
        }

        if (Schema::hasTable('realq_assessment_topics') && !Schema::hasTable('topics')) {
            Schema::rename('realq_assessment_topics', 'topics');
        }

        if (Schema::hasTable('realq_assessment_subjects') && !Schema::hasTable('pre_assessment_subjects')) {
            Schema::rename('realq_assessment_subjects', 'pre_assessment_subjects');
        }
    }
}
