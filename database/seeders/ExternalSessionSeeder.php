<?php

namespace Database\Seeders;

use App\Models\ExternalSession;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the `external_session` table (live/zoom sessions, see
 * Backend\ExternalSessionController) with 2 sessions each in the past,
 * present, and future, so the session list/calendar has realistic coverage
 * across all three states. Requires SchoolSeeder and GradeSeeder to have
 * already run.
 */
class ExternalSessionSeeder extends Seeder
{
    public function run(): void
    {
        $schoolIds = School::orderBy('id')->pluck('id')->all();
        $gradeIds = \App\Models\Grade::orderBy('id')->pluck('id')->all();

        if (empty($schoolIds) || empty($gradeIds)) {
            $this->command?->error('Schools/grades not found — run SchoolSeeder and GradeSeeder first.');
            return;
        }

        $sessions = [
            // --- PAST ---
            [
                'title' => 'Founders Kickoff Webinar',
                'date_time' => Carbon::now()->subDays(14)->setTime(10, 0),
                'agenda' => 'Introduce the VentureKids program and set expectations for the term.',
                'speaker' => 'Aarav Mehta',
            ],
            [
                'title' => 'Mid-Term Progress Review',
                'date_time' => Carbon::now()->subDays(3)->setTime(15, 30),
                'agenda' => 'Review student progress and share feedback with schools.',
                'speaker' => 'Priya Nair',
            ],
            // --- PRESENT (today) ---
            [
                'title' => 'Live Pitch Practice Session',
                'date_time' => Carbon::now()->setTime(11, 0),
                'agenda' => 'Guided pitch practice with live feedback for students.',
                'speaker' => 'Rohan Kapoor',
            ],
            [
                'title' => 'Trainer Sync: Today\'s Curriculum Walkthrough',
                'date_time' => Carbon::now()->addHours(2),
                'agenda' => 'Walk trainers through today\'s session plan and materials.',
                'speaker' => 'Aarav Mehta',
            ],
            // --- FUTURE ---
            [
                'title' => 'Growth Studio Masterclass',
                'date_time' => Carbon::now()->addDays(7)->setTime(14, 0),
                'agenda' => 'A masterclass on branding and pricing for young founders.',
                'speaker' => 'Priya Nair',
            ],
            [
                'title' => 'Impact Summit Finale',
                'date_time' => Carbon::now()->addDays(21)->setTime(16, 0),
                'agenda' => 'End-of-term showcase where students pitch their ventures.',
                'speaker' => 'Rohan Kapoor',
            ],
        ];

        foreach ($sessions as $index => $session) {
            ExternalSession::firstOrCreate(
                ['title' => $session['title']],
                [
                    'created_by' => 1,
                    'session_type' => 1, // online
                    'agenda' => $session['agenda'],
                    'date_time' => $session['date_time'],
                    'recurrence_type' => 'none',
                    'recurrence_interval' => 1,
                    'zoom_link' => 'https://zoom.us/j/' . (8000000000 + $index),
                    'send_zoom_link' => 1,
                    'speaker' => $session['speaker'],
                    'attendees' => implode(',', array_slice($schoolIds, 0, min(3, count($schoolIds)))),
                    'attendee_type' => 'schools',
                    'trainer_ids' => null,
                    'levels' => implode(',', $gradeIds),
                    'batches' => 'all',
                    'notify_recipients' => 'school,trainer,student',
                    'is_cancelled' => 0,
                    'email_job_id' => 0,
                ]
            );
        }

        $this->command?->info('Seeded 2 past, 2 present, and 2 future external sessions.');
    }
}
