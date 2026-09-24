<?php

namespace Database\Seeders;

use App\Models\Trainerlavel;
use App\Models\Trainerstream;
use Illuminate\Database\Seeder;

/**
 * Seeds the `trainerstreams` table (facilitation session topics within a
 * trainer level) with original VentureKids content, tied to the levels
 * created by TrainerLevelSeeder. Requires TrainerLevelSeeder to have
 * already run.
 */
class TrainerStreamSeeder extends Seeder
{
    public function run(): void
    {
        $streamsByLevel = [
            'Foundations Facilitator' => [
                'Facilitating Curiosity Circles',
                'Classroom Icebreakers for Young Founders',
            ],
            'Ideation Coach' => [
                'Guiding Brainstorm Sessions',
                'Coaching Idea Selection',
            ],
            'Prototype Mentor' => [
                'Hands-On Prototyping Techniques',
                'Feedback Loops & Iteration',
            ],
            'Pitch & Storytelling Coach' => [
                'Building a Pitch Deck',
                'Storytelling Frameworks',
            ],
            'Digital Tools Facilitator' => [
                'Intro to No-Code Tools',
                'Digital Collaboration Basics',
            ],
            'Community Impact Coach' => [
                'Community Needs Assessment',
                'Designing for Social Impact',
            ],
            'Financial Skills Coach' => [
                'Budgeting Basics for Young Founders',
                'Understanding Revenue & Costs',
            ],
            'Leadership & Wellbeing Coach' => [
                'Building Confident Leaders',
                'Wellbeing in Entrepreneurship',
            ],
        ];

        foreach ($streamsByLevel as $levelName => $titles) {
            $level = Trainerlavel::where('grade', $levelName)->first();

            if (!$level) {
                $this->command?->error("Trainer level '{$levelName}' not found — run TrainerLevelSeeder first.");
                continue;
            }

            foreach ($titles as $index => $title) {
                Trainerstream::firstOrCreate(
                    ['title' => $title, 'agegroup_id' => $level->id],
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
