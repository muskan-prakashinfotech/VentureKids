<?php

namespace Database\Seeders;

use App\Models\Observation;
use Illuminate\Database\Seeder;

/**
 * Seeds the `observations` table (trainer-logged student observations) with
 * original VentureKids content across both supported categories: 'skill'
 * and 'mindset'. 10 total (5 each), comfortably over the requested minimum
 * of 8 combined.
 */
class ObservationSeeder extends Seeder
{
    public function run(): void
    {
        $observations = [
            ['name' => 'Creativity', 'category' => 'skill', 'icon' => 'creativity.png'],
            ['name' => 'Collaboration', 'category' => 'skill', 'icon' => 'collaboration.png'],
            ['name' => 'Initiative', 'category' => 'skill', 'icon' => 'initiative.png'],
            ['name' => 'Communication', 'category' => 'skill', 'icon' => 'communication.png'],
            ['name' => 'Leadership', 'category' => 'skill', 'icon' => 'leadership.png'],
            ['name' => 'Empathy', 'category' => 'mindset', 'icon' => 'empathy.png'],
            ['name' => 'Problem Solving', 'category' => 'mindset', 'icon' => 'problem-solving.png'],
            ['name' => 'Active Listening', 'category' => 'mindset', 'icon' => 'active-listening.png'],
            ['name' => 'Perseverance', 'category' => 'mindset', 'icon' => 'perseverance.png'],
            ['name' => 'Responsibility', 'category' => 'mindset', 'icon' => 'responsibility.png'],
        ];

        foreach ($observations as $observation) {
            Observation::firstOrCreate(
                ['name' => $observation['name']],
                $observation
            );
        }
    }
}
