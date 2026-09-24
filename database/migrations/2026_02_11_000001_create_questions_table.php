<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->enum('question_type', ['subjective', 'mcq'])->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->longText('challenge')->nullable();
            $table->longText('question_text')->nullable();
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->string('correct_option')->nullable();
            $table->longText('explanation')->nullable();
            $table->longText('prompt')->nullable();
            $table->longText('raw_response')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('assessment_questions');
    }
}
