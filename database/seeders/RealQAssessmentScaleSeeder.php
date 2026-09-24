<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentScale;
use Illuminate\Database\Seeder;

/** Seeds the `realq_assessment_scale` table (the rating scale RealQAssessmentRubricSeeder attaches levels to). */
class RealQAssessmentScaleSeeder extends Seeder
{
    public function run(): void
    {
        RealQAssessmentScale::firstOrCreate(['name' => 'RealQ 4-Point Growth Scale']);
    }
}
