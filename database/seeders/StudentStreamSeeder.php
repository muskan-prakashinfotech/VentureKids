<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Stream;
use Illuminate\Database\Seeder;

/**
 * Seeds the `streams` table (session topics within a student grade/level)
 * with original VentureKids content, tied to the grades created by
 * GradeSeeder. Requires GradeSeeder to have already run.
 */
class StudentStreamSeeder extends Seeder
{
    public function run(): void
    {
        $streamsByGrade = [
            'Curiosity Spark' => [
                'What is an Idea?',
                'Spotting Everyday Problems',
            ],
            'Idea Forge' => [
                'Brainstorming Like a Pro',
                'From Idea to Concept',
            ],
            'Venture Lab' => [
                'Build Your First Prototype',
                'Testing with Real Users',
            ],
            'Growth Studio' => [
                'Telling Your Brand Story',
                'Pricing for Growth',
            ],
            'Impact Summit' => [
                'Pitch Like a Founder',
                'Measuring Real-World Impact',
            ],
        ];

        foreach ($streamsByGrade as $gradeName => $titles) {
            $grade = Grade::where('grade', $gradeName)->first();

            if (!$grade) {
                $this->command?->error("Grade '{$gradeName}' not found — run GradeSeeder first.");
                continue;
            }

            foreach ($titles as $index => $title) {
                Stream::firstOrCreate(
                    ['title' => $title, 'agegroup_id' => $grade->id],
                    [
                        'creator_id' => 1,
                        'creator' => 'Super Admin',
                        'display_order_id' => $index + 1,
                    ]
                );
            }
        }
    }
}
