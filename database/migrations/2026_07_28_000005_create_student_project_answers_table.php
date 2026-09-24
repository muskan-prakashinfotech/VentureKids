<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_project_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_project_id');
            $table->unsignedBigInteger('project_question_id');
            $table->text('answer_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_project_answers');
    }
};
