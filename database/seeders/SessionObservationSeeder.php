<?php

namespace Database\Seeders;

use App\Models\ExternalSession;
use App\Models\Observation;
use App\Models\StudentObservations;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds one past ExternalSession (a class a trainer conducted) plus one
 * StudentObservations entry logged against it, for
 * school1.student1@venturekids.test. Mirrors the real trainer flow in
 * Trainer\ClassScheduleController::storeStudentObservation(), which
 * requires an existing ExternalSession (student_observations.
 * external_session_id) — trainers log observations against a session that
 * already happened. Requires SchoolSeeder and ObservationSeeder to have
 * already run.
 */
class SessionObservationSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $trainerId = 1; // school1.trainer1
        $observation = Observation::where('name', 'Creativity')->first();

        if (!$student || !$observation) {
            $this->command?->error('Student school1.student1@venturekids.test or the Creativity observation not found — run SchoolSeeder and ObservationSeeder first.');
            return;
        }

        $sessionDate = Carbon::now()->subDays(10);

        $session = ExternalSession::firstOrCreate(
            ['title' => 'GreenBite Prototype Coaching Session'],
            [
                'created_by' => 1,
                'session_type' => 1, // online
                'agenda' => 'A one-on-one coaching session to help students refine their Venture Lab prototypes.',
                'date_time' => $sessionDate->copy()->setTime(11, 0),
                'recurrence_type' => 'none',
                'recurrence_interval' => 1,
                'zoom_link' => 'https://zoom.us/j/8100000001',
                'send_zoom_link' => 1,
                'speaker' => 'Trainer 1-1',
                'attendees' => (string) $student->school_id,
                'attendee_type' => 'schools',
                'trainer_ids' => null,
                'levels' => (string) $student->grade_id,
                'batches' => 'all',
                'notify_recipients' => 'school,trainer,student',
                'is_cancelled' => 0,
                'email_job_id' => 0,
            ]
        );

        StudentObservations::firstOrCreate(
            [
                'student_id' => $student->id,
                'external_session_id' => $session->id,
                'observation_id' => (string) $observation->id,
            ],
            [
                'trainer_id' => $trainerId,
                'school_id' => $student->school_id,
                'grade_id' => (string) $student->grade_id,
                'session_date' => $sessionDate->format('Y-m-d'),
                'short_note' => 'Showed great creativity while refining the GreenBite prototype — tried three different fabric folds before landing on the final design.',
                'image' => 'student_observations/creativity-greenbite.png',
            ]
        );

        $this->command?->info("Seeded 1 past session and 1 observation for {$studentUser->name}.");
    }
}
