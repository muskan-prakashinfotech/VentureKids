<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_school_report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->enum('assessment_type', ['standard', 'realq']);
            $table->string('report_path');
            $table->timestamps();

            $table->index(['school_id', 'assessment_type'], 'assessment_school_report_school_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_school_report');
    }
};
