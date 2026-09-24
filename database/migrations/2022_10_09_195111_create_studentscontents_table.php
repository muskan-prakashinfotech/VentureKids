<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentscontentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studentscontents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('ageGroup_id');
            $table->integer('stream_id');
            $table->string('video')->nullable();
            $table->string('worksheet')->nullable();
            $table->text('learning_object')->nullable()->default(null);
            $table->text('outcome_session')->nullable()->default(null);
            $table->text('question_access_knowledge')->nullable()->default(null);
            $table->text('introduce_topic_student')->nullable()->default(null);
            $table->text('related_activity_one')->nullable()->default(null);
            $table->text('related_activity_two')->nullable()->default(null);
            $table->text('vocabulary')->nullable()->default(null);
            $table->text('tips_of_parents')->nullable()->default(null);
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
        Schema::dropIfExists('studentscontents');
    }
}
