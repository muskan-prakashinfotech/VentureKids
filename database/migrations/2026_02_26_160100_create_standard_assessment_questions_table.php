<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardAssessmentQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->text('question_text');
            $table->text('option_a');
            $table->unsignedTinyInteger('option_a_score')->default(1);
            $table->text('option_b');
            $table->unsignedTinyInteger('option_b_score')->default(1);
            $table->text('option_c');
            $table->unsignedTinyInteger('option_c_score')->default(1);
            $table->text('option_d');
            $table->unsignedTinyInteger('option_d_score')->default(1);
            $table->char('correct_option', 1)->nullable();
            $table->tinyInteger('active')->default(1);
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
        Schema::dropIfExists('standard_assessment_questions');
    }
}

