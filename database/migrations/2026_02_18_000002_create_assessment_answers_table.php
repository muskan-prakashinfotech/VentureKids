<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentAnswersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('assessment_type', ['baseline', 'post'])->default('baseline');
            $table->unsignedBigInteger('assessment_question_id');
            $table->longText('answer_text')->nullable();
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'assessment_type', 'assessment_question_id'],
                'assessment_answers_student_type_question_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('assessment_answers');
    }
}
