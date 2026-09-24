<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentTopic;
use App\Models\StudentBoard;
use Illuminate\Database\Seeder;

/**
 * Seeds the `realq_assessment_questions` table — 5 questions (3 mcq + 2
 * subjective), all for the single "Spotting a Problem Worth Solving" topic
 * from RealQAssessmentTopicSeeder, all moderation_status = 'approved' (the
 * only status the real student-facing assessment flow will actually serve
 * — see Student\AssessmentController). Requires RealQAssessmentTopicSeeder
 * to have already run.
 */
class RealQAssessmentQuestionSeeder extends Seeder
{
    private const TOPIC = 'Spotting a Problem Worth Solving';

    private const MCQ_QUESTIONS = [
        [
            'question_text' => 'Which of these is the best example of "spotting a problem worth solving"?',
            'options' => [
                'Noticing that many students throw away reusable water bottles because refill stations are hard to find',
                'Picking a random product to sell because it looks fun',
                'Copying a business idea exactly because a friend has one',
                'Waiting for a teacher to assign an idea to work on',
            ],
            'correct' => 'A',
            'explanation' => 'A good problem is real, specific, and something you have actually observed affecting people.',
        ],
        [
            'question_text' => "Why is it important to observe people's daily routines when looking for a problem to solve?",
            'options' => [
                'It helps you notice problems people face but rarely talk about',
                'It has nothing to do with finding a problem',
                'It only works for adults, not students',
                'It wastes time better spent building something',
            ],
            'correct' => 'A',
            'explanation' => 'Close observation often reveals problems people have simply gotten used to and stopped mentioning.',
        ],
        [
            'question_text' => 'Which of these is NOT a good way to find a problem worth solving?',
            'options' => [
                'Talking to people about their everyday frustrations',
                "Copying someone else's idea without checking if it fits your community",
                'Observing everyday situations closely',
                'Asking "why" when something seems inconvenient',
            ],
            'correct' => 'B',
            'explanation' => 'Copying an idea without checking whether it addresses a real, local problem skips the "spotting" step entirely.',
        ],
    ];

    private const SUBJECTIVE_QUESTIONS = [
        [
            'challenge' => 'Your school cafeteria throws away a lot of food every day because students take more than they eat.',
            'question_text' => 'Describe one real problem you have noticed at your school or in your neighborhood, and explain who it affects and why it matters.',
            'explanation' => 'Look for a specific, real problem with a clear description of who is affected and why it matters to them.',
        ],
        [
            'challenge' => 'You have described a real problem, but a friend asks why anyone should bother solving it.',
            'question_text' => 'Explain why the problem you described is important enough for someone to want to solve it.',
            'explanation' => 'Look for reasoning about impact or consequence — why leaving the problem unsolved actually costs something.',
        ],
    ];

    public function run(): void
    {
        $subject = RealQAssessmentSubject::where('name', 'Entrepreneurial Thinking')->first();
        $board = StudentBoard::where('name', 'CBSE')->first();
        $topic = RealQAssessmentTopic::where('topic', self::TOPIC)->first();

        if (!$subject || !$board || !$topic) {
            $this->command?->error('Subject/board/topic not found — run RealQAssessmentSubjectSeeder, StudentBoardSeeder and RealQAssessmentTopicSeeder first.');
            return;
        }

        $common = [
            'grade_id' => 1,
            'board_id' => $board->id,
            'country_id' => 1,
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'moderation_status' => 'approved',
            'created_by' => 1,
        ];

        foreach (self::MCQ_QUESTIONS as $mcq) {
            RealQAssessmentQuestion::firstOrCreate(
                ['topic_id' => $topic->id, 'question_type' => 'mcq', 'question_text' => $mcq['question_text']],
                $common + [
                    'option_a' => $mcq['options'][0],
                    'option_b' => $mcq['options'][1],
                    'option_c' => $mcq['options'][2],
                    'option_d' => $mcq['options'][3],
                    'correct_option' => $mcq['correct'],
                    'explanation' => $mcq['explanation'],
                    'prompt' => "Generate an MCQ RealQ question for topic: " . self::TOPIC . '.',
                    'raw_response' => 'Question generated and approved for demo/seed purposes.',
                ]
            );
        }

        foreach (self::SUBJECTIVE_QUESTIONS as $subj) {
            RealQAssessmentQuestion::firstOrCreate(
                ['topic_id' => $topic->id, 'question_type' => 'subjective', 'question_text' => $subj['question_text']],
                $common + [
                    'challenge' => $subj['challenge'],
                    'explanation' => $subj['explanation'],
                    'prompt' => "Generate a subjective RealQ scenario question for topic: " . self::TOPIC . '.',
                    'raw_response' => 'Question generated and approved for demo/seed purposes.',
                ]
            );
        }

        $this->command?->info('Seeded 5 RealQ questions (3 mcq + 2 subjective) for topic "' . self::TOPIC . '".');
    }
}
