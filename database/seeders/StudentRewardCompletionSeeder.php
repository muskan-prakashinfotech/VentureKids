<?php

namespace Database\Seeders;

use App\Helpers\QuizHelper;
use App\Helpers\StudentRewardPointsHelper;
use App\Models\EventChallenge;
use App\Models\QuizAnswers;
use App\Models\QuizAttempts;
use App\Models\QuizQuestions;
use App\Models\StudentCommunications;
use App\Models\Students;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds one completed entry — dated in the past — for each of the 4 things
 * that award reward points on the student dashboard, for school1.student1:
 *  1. Assignment — BOTH assignment types, since each is graded/rewarded
 *     completely differently:
 *       a. Facilitated ("What is an Idea? Assignment") — student submits a
 *          file (Student\AssignmentController::submit()), then a TRAINER
 *          reviews it and leaves feedback/marks
 *          (Trainer\AssignmentController::review(), reward_type
 *          'assignment_submission', 2 points). Without this trainer-review
 *          step, a facilitated submission earns no points at all — that's
 *          the gap this seeder was missing before.
 *       b. Self-paced (SCORM) — finished at 100%
 *          (Student\AssignmentController::storeScormAssignment, reward_type
 *          'assignment_scorm_point', 5 points).
 *  2. Industry Challenge — a response submitted to an Event
 *     (Student\EventController::eventChallengeResponse, reward_type
 *     'challenge_respond', 1 point).
 *  3. Daily Challenge — a submitted, scored quiz attempt
 *     (Student\ContentStudentController::submitQuiz, reward_type
 *     'daily_challenge', points = quiz score).
 *  4. Content video — a stream's video watched to completion
 *     (Student\ContentStudentController::storeScormCompletionPoints,
 *     reward_type 'video_learning_point', 5 points).
 *
 * Requires SchoolSeeder, AssignmentSeeder, IndustryChallengeSeeder,
 * DailyChallengeSeeder and StudentStreamSeeder to have already run.
 */
class StudentRewardCompletionSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;

        if (!$student) {
            $this->command?->error('Student school1.student1@venturekids.test not found — run SchoolSeeder first.');
            return;
        }

        $pastDate = Carbon::now()->subDays(5)->setTime(15, 0);

        $this->seedFacilitatedAssignmentCompletion($student, $pastDate);
        $this->seedScormAssignmentCompletion($student, $pastDate);
        $this->seedIndustryChallengeCompletion($student, $pastDate);
        $this->seedDailyChallengeCompletion($student, $pastDate);
        $this->seedContentVideoCompletion($student, $pastDate);

        $this->command?->info("Seeded past-dated reward completions for {$student->name}.");
    }

    private function seedFacilitatedAssignmentCompletion(Students $student, Carbon $when): void
    {
        $assignment = StudentCommunications::where('school_id', $student->school_id)
            ->where('category', 'facilitated')
            ->first();

        if (!$assignment) {
            $this->command?->error('No facilitated assignment found for this school — run AssignmentSeeder first.');
            return;
        }

        $tenantId = optional($student->school)->tenant_id;
        $fileName = 'greenbite-idea-assignment-submission.pdf';

        $submission = Submission::firstOrCreate(
            ['student_id' => $student->id, 'assignment_id' => $assignment->id],
            [
                'file' => $tenantId ? $tenantId . '/student/submissions/' . $fileName : null,
                // The trainer's marking/review — Submission has no numeric
                // "marks" column, so written feedback is the real
                // equivalent (see Trainer\AssignmentController::review()).
                'feedback' => 'Great first attempt! You clearly identified a real, relatable problem (snack wrapper waste) and explained who it affects.',
                'comment' => 'Next time, try adding one more detail about how big the impact is (e.g. how much waste per week).',
            ]
        );
        $submission->created_at = $when;
        $submission->updated_at = $when;
        $submission->save();

        // Mirrors Trainer\AssignmentController::review(): reviewing a
        // facilitated submission is what actually awards the points —
        // submitting alone does not.
        if (!StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'assignment_submission', $assignment->id)->count()) {
            $this->storeBackdatedReward($student->id, 'assignment_submission', $assignment->id, 2, $when);
        }
    }

    private function seedScormAssignmentCompletion(Students $student, Carbon $when): void
    {
        $assignment = StudentCommunications::where('school_id', $student->school_id)
            ->where('category', 'self_learning')
            ->first();

        if (!$assignment) {
            $this->command?->error('No self_learning assignment found for this school — run AssignmentSeeder first.');
            return;
        }

        $submission = Submission::firstOrCreate(
            ['student_id' => $student->id, 'assignment_id' => $assignment->id],
            ['scorm_status' => 100]
        );
        $submission->created_at = $when;
        $submission->updated_at = $when;
        $submission->save();

        if (!StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'assignment_scorm_point', $assignment->id)->count()) {
            $this->storeBackdatedReward($student->id, 'assignment_scorm_point', $assignment->id, 5, $when);
        }
    }

    private function seedIndustryChallengeCompletion(Students $student, Carbon $when): void
    {
        $event = \App\Models\Event::orderBy('id')->first();

        if (!$event) {
            $this->command?->error('No event found — run IndustryChallengeSeeder first.');
            return;
        }

        $challenge = EventChallenge::firstOrCreate(
            ['event_id' => $event->id, 'student_id' => $student->id],
            ['description' => 'My venture idea tackles school snack waste with a reusable wrap — GreenBite. I would love to bring this to the challenge!']
        );
        $challenge->created_at = $when;
        $challenge->updated_at = $when;
        $challenge->save();

        if (!StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'challenge_respond', $event->id)->count()) {
            $this->storeBackdatedReward($student->id, 'challenge_respond', $event->id, 1, $when);
        }
    }

    private function seedDailyChallengeCompletion(Students $student, Carbon $when): void
    {
        $challengeId = 1; // "Monday Mindset: Spotting Everyday Problems" — see DailyChallengeSeeder
        $questions = QuizQuestions::where('content_id', $challengeId)->where('content_type', 'daily_challenge')->get();

        if ($questions->isEmpty()) {
            $this->command?->error('No daily challenge questions found — run DailyChallengeSeeder first.');
            return;
        }

        if (QuizHelper::isQuizAttempt($challengeId, $student->id, 'daily_challenge')->isEmpty()) {
            $attempt = QuizAttempts::create([
                'student_id' => $student->id,
                'quiz_id' => $challengeId,
                'quiz_type' => 'daily_challenge',
                'is_submit' => 1,
                'created_by' => $student->id,
                'updated_by' => $student->id,
            ]);
            $attempt->created_at = $when;
            $attempt->updated_at = $when;
            $attempt->save();

            foreach ($questions as $question) {
                $answer = QuizAnswers::create([
                    'student_id' => $student->id,
                    'quiz_id' => $challengeId,
                    'quiz_type' => 'daily_challenge',
                    'question_id' => $question->id,
                    'answer' => $question->correct_option, // answered correctly
                    'is_attempt' => 1,
                    'created_by' => $student->id,
                    'updated_by' => $student->id,
                ]);
                $answer->created_at = $when;
                $answer->updated_at = $when;
                $answer->save();
            }
        }

        if (!StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'daily_challenge', $challengeId)->count()) {
            $score = QuizHelper::getQuizScore($challengeId, $student->id, 'daily_challenge')['score'];
            $this->storeBackdatedReward($student->id, 'daily_challenge', $challengeId, $score, $when);
        }
    }

    private function seedContentVideoCompletion(Students $student, Carbon $when): void
    {
        $stream = \App\Models\Stream::where('agegroup_id', $student->grade_id)->orderBy('id')->first();

        if (!$stream) {
            $this->command?->error('No stream found for this student\'s grade — run StudentStreamSeeder first.');
            return;
        }

        if (!StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'video_learning_point', $stream->id)->count()) {
            $this->storeBackdatedReward($student->id, 'video_learning_point', $stream->id, 5, $when);
        }
    }

    private function storeBackdatedReward(int $studentId, string $rewardType, int $itemId, int $points, Carbon $when)
    {
        StudentRewardPointsHelper::storeRewardPoints([
            'student_id' => $studentId,
            'reward_type' => $rewardType,
            'item_id' => $itemId,
            'reward_points' => $points,
        ]);

        \App\Models\StudentRewardPoints::where('student_id', $studentId)
            ->where('reward_type', $rewardType)
            ->where('item_id', $itemId)
            ->update(['created_at' => $when, 'updated_at' => $when]);
    }
}
