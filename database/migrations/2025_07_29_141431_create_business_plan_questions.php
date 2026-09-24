<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlanQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plan_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question_value', 500);
            $table->text('prompt_text')->nullable();
            $table->enum('question_type', ['text', 'radio', 'checkbox', 'textarea', 'custom_text']);
            $table->boolean('is_required')->default(false);
            $table->boolean('allow_custom_option')->default(false);
            $table->string('placeholder_text', 255)->nullable();
            $table->integer('step')->default(1);
            $table->integer('display_order')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
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
        Schema::dropIfExists('business_plan_questions');
    }
}
