<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectReasonToRealqAssessmentQuestions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('realq_assessment_questions', function (Blueprint $table) {
            $table->text('reject_reason')->nullable()->after('moderation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('realq_assessment_questions', function (Blueprint $table) {
            $table->dropColumn('reject_reason');
        });
    }
}
