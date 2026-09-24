<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('trainer_id');
            $table->unsignedBigInteger('student_id');
            
            // Knowledge Assessment (1-4)
            $table->tinyInteger('knowledge_score')->nullable();
            $table->text('knowledge_feedback')->nullable();
            
            // Skills Assessment (1-4)
            $table->tinyInteger('skills_score')->nullable();
            $table->text('skills_feedback')->nullable();
            
            // Mindset Assessment (CROPCEOAGE - 1-4 each)
            $table->tinyInteger('curious_score')->nullable();
            $table->tinyInteger('resilient_score')->nullable();
            $table->tinyInteger('open_score')->nullable();
            $table->tinyInteger('positive_score')->nullable();
            $table->tinyInteger('creative_score')->nullable();
            $table->tinyInteger('empathetic_score')->nullable();
            $table->tinyInteger('observant_score')->nullable();
            $table->tinyInteger('abundance_score')->nullable();
            $table->tinyInteger('growth_score')->nullable();
            $table->tinyInteger('entrepreneurial_score')->nullable();
            $table->text('mindset_feedback')->nullable();
            $table->json('spider_graph_data')->nullable();
        
            $table->tinyInteger('is_publish')->default(1)->comment('1=publish, 0=draft');
            $table->text('teacher_notes')->nullable();
            
            $table->timestamps();
            $table->unique('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_feedback');
    }
}
