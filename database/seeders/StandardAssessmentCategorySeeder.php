<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\StandardAssessmentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds `standard_assessment_categories` with 2 categories for Grade 1
 * (Curiosity Spark — see GradeSeeder), and enables the Standard Assessment
 * feature for Demo School 1 (schools.standard_assessment_assigned/enabled/
 * enabled_from/enabled_to — see Student\AssessmentController::
 * isSchoolAssessmentAvailable()), active now. Requires GradeSeeder and
 * SchoolSeeder to have already run.
 */
class StandardAssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Communication Skills', 'Problem Solving'] as $name) {
            StandardAssessmentCategory::firstOrCreate(
                ['grade_id' => 1, 'category_name' => $name],
                ['active' => 1]
            );
        }

        $school = School::where('official_email_id', 'school1@venturekids.test')->first();

        if ($school) {
            $school->standard_assessment_assigned = 1;
            $school->standard_assessment_enabled = 1;
            $school->standard_assessment_enabled_from = Carbon::now()->subDays(30)->format('Y-m-d');
            $school->standard_assessment_enabled_to = Carbon::now()->addDays(60)->format('Y-m-d');
            $school->save();
        }

        $this->command?->info('Seeded 2 standard assessment categories and enabled Standard Assessment for Demo School 1.');
    }
}
