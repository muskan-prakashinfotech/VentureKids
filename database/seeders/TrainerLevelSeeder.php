<?php

namespace Database\Seeders;

use App\Models\Trainerlavel;
use Illuminate\Database\Seeder;

/**
 * Seeds the `trainerlavels` table (the levels/tracks a trainer is qualified to
 * facilitate) with a VentureKids-original naming set. Deliberately distinct
 * from kids_new's trainer levels (Thinkpreneur, Createpreneur, Launchpreneur,
 * Design Thinking & Innovation, Mastering Makerspaces, Skillpreneur, ...) so
 * this data isn't a copy of the reference database.
 */
class TrainerLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['grade' => 'Foundations Facilitator', 'display_order_id' => 1, 'unique_code' => 'VK-TF1'],
            ['grade' => 'Ideation Coach', 'display_order_id' => 2, 'unique_code' => 'VK-TF2'],
            ['grade' => 'Prototype Mentor', 'display_order_id' => 3, 'unique_code' => 'VK-TF3'],
            ['grade' => 'Pitch & Storytelling Coach', 'display_order_id' => 4, 'unique_code' => 'VK-TF4'],
            ['grade' => 'Digital Tools Facilitator', 'display_order_id' => 5, 'unique_code' => 'VK-TF5'],
            ['grade' => 'Community Impact Coach', 'display_order_id' => 6, 'unique_code' => 'VK-TF6'],
            ['grade' => 'Financial Skills Coach', 'display_order_id' => 7, 'unique_code' => 'VK-TF7'],
            ['grade' => 'Leadership & Wellbeing Coach', 'display_order_id' => 8, 'unique_code' => 'VK-TF8'],
        ];

        foreach ($levels as $level) {
            $level['image'] = 'default.jpg';

            $trainerLevel = Trainerlavel::firstOrCreate(['grade' => $level['grade']], $level);

            // TrainerGradeObserver::creating() overwrites unique_code with
            // 'L'.substr(sha1(time()), 0, 5) on insert, which collides across
            // rows created within the same second. Re-apply our intended code
            // with a plain save (fires 'updating', not 'creating', so the
            // observer doesn't clobber it again).
            if ($trainerLevel->unique_code !== $level['unique_code']) {
                $trainerLevel->unique_code = $level['unique_code'];
                $trainerLevel->save();
            }
        }
    }
}
