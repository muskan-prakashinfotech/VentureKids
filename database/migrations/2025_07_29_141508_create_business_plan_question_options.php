<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlanQuestionOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plan_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_plan_question_id');
            $table->string('option_text', 500);
            $table->string('option_value', 255);
            $table->boolean('is_correct')->default(false);
            $table->integer('display_order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_input_field')->default(false); 

            $table->foreign('business_plan_question_id')
                ->references('id')
                ->on('business_plan_questions')
                ->onDelete('cascade');

            $table->index('business_plan_question_id');
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
        Schema::dropIfExists('business_plan_question_options');
    }
}
