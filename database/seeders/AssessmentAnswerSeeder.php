<?php

namespace Database\Seeders;

use App\Models\AssessmentAnswer;
use App\Models\RealQAssessmentQuestion;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds `realq_assessment_student_answers` — school1.student1's baseline
 * answers to all 5 questions from RealQAssessmentQuestionSeeder (3 mcq,
 * all answered correctly; 2 subjective, with a real written response),
 * dated in the past so a report can be generated from them. Requires
 * RealQAssessmentQuestionSeeder and SchoolSeeder to have already run.
 */
class AssessmentAnswerSeeder extends Seeder
{
    private const SUBJECTIVE_ANSWERS = [
        'Describe one real problem you have noticed at your school or in your neighborhood, and explain who it affects and why it matters.' =>
            'At my school, a lot of students throw away plastic wrapper waste from snacks every day. It affects everyone because it fills up the bins fast and creates litter, and it matters because it is something we could actually reduce if more of us used reusable wraps instead.',
        'Explain why the problem you described is important enough for someone to want to solve it.' =>
            'If nobody fixes it, the school keeps producing avoidable waste every single day, which costs money to clean up and sets a bad example. Solving it would save resources and show that small changes by students can really add up.',
    ];

    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $questions = RealQAssessmentQuestion::where('moderation_status', 'approved')->get();

        if (!$student || $questions->isEmpty()) {
            $this->command?->error('Student school1.student1@venturekids.test or approved questions not found — run SchoolSeeder and RealQAssessmentQuestionSeeder first.');
            return;
        }

        $startedAt = Carbon::now()->subDays(7)->setTime(10, 0);

        foreach ($questions as $index => $question) {
            $answeredAt = $startedAt->copy()->addMinutes($index * 3 + 3);

            $data = [
                'is_submitted' => 1,
                'started_at' => $startedAt,
                'answered_at' => $answeredAt,
            ];

            if ($question->question_type === 'mcq') {
                $data['selected_option'] = $question->correct_option; // answered correctly
            } else {
                $data['answer_text'] = self::SUBJECTIVE_ANSWERS[$question->question_text] ?? 'This is my honest attempt at answering the scenario above.';
            }

            AssessmentAnswer::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'assessment_type' => 'baseline',
                    'assessment_question_id' => $question->id,
                ],
                $data
            );
        }

        $this->command?->info("Seeded {$questions->count()} baseline answers for school1.student1.");
    }
}
