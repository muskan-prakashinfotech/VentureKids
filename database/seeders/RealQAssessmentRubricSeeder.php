<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentScale;
use Illuminate\Database\Seeder;

/**
 * Seeds the `realq_assessment_rubrics` table — the 4 score levels for the
 * scale created by RealQAssessmentScaleSeeder. Requires
 * RealQAssessmentScaleSeeder to have already run.
 */
class RealQAssessmentRubricSeeder extends Seeder
{
    private const RUBRICS = [
        ['score' => 1, 'name' => 'Scale 1', 'description' => 'Just starting to show this skill; needs regular guidance and support.'],
        ['score' => 2, 'name' => 'Scale 2', 'description' => 'Shows the skill sometimes, but application is inconsistent.'],
        ['score' => 3, 'name' => 'Scale 3', 'description' => 'Consistently applies the skill in familiar situations.'],
        ['score' => 4, 'name' => 'Scale 4', 'description' => 'Applies the skill confidently, even in new or challenging situations.'],
    ];

    public function run(): void
    {
        $scale = RealQAssessmentScale::where('name', 'RealQ 4-Point Growth Scale')->first();

        if (!$scale) {
            $this->command?->error('RealQ scale not found — run RealQAssessmentScaleSeeder first.');
            return;
        }

        foreach (self::RUBRICS as $rubric) {
            $rubricRow = RealQAssessmentRubric::firstOrCreate(
                ['scale_id' => $scale->id, 'score' => $rubric['score']],
                ['name' => $rubric['name'], 'description' => $rubric['description']]
            );

            // Keep the name in sync on re-runs too (e.g. after renaming
            // Emerging/Developing/Proficient/Advanced to Scale 1..4).
            if ($rubricRow->name !== $rubric['name']) {
                $rubricRow->name = $rubric['name'];
                $rubricRow->save();
            }
        }
    }
}
