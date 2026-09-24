<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlanAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plan_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_option_id')->nullable();
            $table->text('response_text')->nullable();
            $table->string('selected_option_value')->nullable();
            $table->string('custom_text', 1000)->nullable();
            $table->boolean('is_correct')->nullable();

            $table->foreign('question_id')->references('id')->on('business_plan_questions')->onDelete('cascade');
            $table->foreign('selected_option_id')->references('id')->on('business_plan_question_options')->onDelete('set null');

            $table->index(['student_id', 'question_id']);
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
        Schema::dropIfExists('business_plan_answers');
    }
}
