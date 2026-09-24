<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('realq_assessment_student_parameter_scores')
            ->whereNull('generate_time_rubric_id')
            ->whereNotNull('rubric_id')
            ->update([
                'generate_time_rubric_id' => DB::raw('rubric_id'),
            ]);
    }

    public function down(): void
    {
        // Do not clear historical values if this data-only migration is rolled back.
    }
};
