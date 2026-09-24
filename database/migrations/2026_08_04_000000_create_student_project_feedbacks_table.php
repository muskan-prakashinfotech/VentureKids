<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_project_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_project_id');
            $table->unsignedBigInteger('trainer_id');
            $table->longText('public_note')->nullable();
            $table->longText('private_suggestions')->nullable();
            $table->boolean('is_publish')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_project_feedbacks');
    }
};
