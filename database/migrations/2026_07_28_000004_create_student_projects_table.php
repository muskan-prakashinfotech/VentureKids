<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_projects', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->tinyInteger('status')->default(0)->comment('0 or 1 = create draft, 2 = improvement ideas, 3 = improve project, 4 = submit, 5 = teacher feedback, 6 = publish project');
            $table->boolean('is_submitted')->default(0)->comment('0 = not submitted, 1 = submitted to teacher');
            $table->longText('improvement_comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_projects');
    }
};
