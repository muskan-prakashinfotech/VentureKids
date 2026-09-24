<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('realq_assessment_student_parameter_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('generate_time_rubric_id')
                ->nullable()
                ->after('rubric_id')
                ->comment('rubric_id as chosen by the AI at first generation, never overwritten by later edits');
        });
    }

    public function down(): void
    {
        Schema::table('realq_assessment_student_parameter_scores', function (Blueprint $table) {
            $table->dropColumn('generate_time_rubric_id');
        });
    }
};
