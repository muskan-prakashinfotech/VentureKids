<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentParameter;
use Illuminate\Database\Seeder;

/** Seeds the `realq_assessment_parameters` table (the skill dimensions RealQ scores students on). */
class RealQAssessmentParameterSeeder extends Seeder
{
    private const PARAMETERS = [
        'Critical Thinking' => 'The ability to analyze a situation, weigh options, and reason through a problem before acting.',
        'Creativity' => 'The ability to generate original ideas and approach problems in novel ways.',
        'Problem Solving' => 'The ability to identify a real problem and work toward a practical, workable solution.',
        'Decision Making' => 'The ability to make a reasoned choice between options and explain the reasoning behind it.',
    ];

    public function run(): void
    {
        foreach (self::PARAMETERS as $name => $description) {
            RealQAssessmentParameter::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
