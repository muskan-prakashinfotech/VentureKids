<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the "Industry Challenges" feature (Student\EventController::
 * getIndustryChallenges()): rows in `events`, shown to students split into
 * current vs past based on `event_last_date`. `visibility_type` 1 = global
 * (shown to everyone), 2 = country-specific (shown only to students in
 * `country_id`).
 *
 * event_image stores just a base filename; the controller only picks it up
 * if a "<name>-1920x1080.<ext>" variant exists under public/image/event/,
 * so that variant is what's actually seeded on disk for each event.
 */
class IndustryChallengeSeeder extends Seeder
{
    private const EVENTS = [
        [
            'name' => 'VentureKids Innovation Summit 2026',
            'image' => 'vk-innovation-summit.jpg',
            'description' => 'A full-day summit where young founders across VentureKids schools showcase their ventures, attend workshops, and meet real entrepreneurs.',
            'date' => '+30 days',
            'last_date' => '+35 days',
            'fee' => 'Free',
            'visibility_type' => 1, // global
            'country_id' => null,
            'reward_amount' => 500,
            'reward_description' => 'Top 3 ventures receive a VentureKids seed grant and mentorship session.',
        ],
        [
            'name' => 'Young Founders Pitch Challenge',
            'image' => 'vk-pitch-challenge.jpg',
            'description' => 'Submit a 2-minute pitch video of your venture idea for a chance to be featured and win prizes.',
            'date' => '+14 days',
            'last_date' => '+21 days',
            'fee' => 'Free',
            'visibility_type' => 1, // global
            'country_id' => null,
            'reward_amount' => 250,
            'reward_description' => 'Winner receives a feature on the VentureKids showcase page.',
        ],
        [
            'name' => 'Eco Ventures Regional Challenge',
            'image' => 'vk-eco-ventures.jpg',
            'description' => 'A regional challenge for students to design a small eco-friendly product or service and present it to local judges.',
            'date' => '+45 days',
            'last_date' => '+50 days',
            'fee' => 'Free',
            'visibility_type' => 2, // country-specific
            'country_id' => 1,
            'reward_amount' => 300,
            'reward_description' => 'Winning team receives eco-friendly starter kits for their venture.',
        ],
        [
            'name' => 'Founders Bootcamp Weekend',
            'image' => 'vk-founders-bootcamp.jpg',
            'description' => 'An intensive weekend bootcamp covering idea validation, prototyping, and pitching, wrapped up with a mini demo day.',
            'date' => '-20 days',
            'last_date' => '-15 days',
            'fee' => 'Free',
            'visibility_type' => 1, // global
            'country_id' => null,
            'reward_amount' => null,
            'reward_description' => null,
        ],
    ];

    public function run(): void
    {
        $currencyId = DB::table('currencies')->orderBy('id')->value('code') ?? 'USD';

        foreach (self::EVENTS as $item) {
            if (Event::where('event_name', $item['name'])->exists()) {
                continue; // already seeded, keep this seeder safe to re-run
            }

            $event = new Event();
            $event->event_name = $item['name'];
            $event->event_image = $item['image'];
            $event->event_date = now()->modify($item['date'])->format('Y-m-d');
            $event->event_last_date = now()->modify($item['last_date'])->format('Y-m-d');
            $event->event_fee = $item['fee'];
            $event->currency = $currencyId;
            $event->event_description = $item['description'];
            $event->country_id = $item['country_id'] ?? 1;
            $event->visibility_type = $item['visibility_type'];
            $event->is_publish = 1;
            $event->reward_amount = $item['reward_amount'];
            $event->reward_description = $item['reward_description'];
            $event->save();
        }

        $this->command?->info('Seeded ' . count(self::EVENTS) . ' industry challenge events.');
    }
}
