<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentSubject;
use Illuminate\Database\Seeder;

/** Seeds the `realq_assessment_subjects` table. */
class RealQAssessmentSubjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Entrepreneurial Thinking', 'Problem Solving', 'Communication'] as $name) {
            RealQAssessmentSubject::firstOrCreate(['name' => $name]);
        }
    }
}
