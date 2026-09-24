<?php

namespace Database\Seeders;

use App\Models\StandardAssessmentCategory;
use App\Models\StandardAssessmentQuestion;
use Illuminate\Database\Seeder;

/**
 * Seeds `standard_assessment_questions` — 3 questions per category from
 * StandardAssessmentCategorySeeder (6 total), each with 4 scored options
 * (1-4, a Likert-style "how well does this describe you" scale) and a
 * correct_option marking the strongest answer. Requires
 * StandardAssessmentCategorySeeder to have already run.
 */
class StandardAssessmentQuestionSeeder extends Seeder
{
    private const QUESTIONS = [
        'Communication Skills' => [
            [
                'question_text' => 'When explaining an idea to a friend, I...',
                'options' => [
                    'a' => ['text' => 'Wait for them to ask questions', 'score' => 2],
                    'b' => ['text' => 'Explain it clearly and check if they understood', 'score' => 4],
                    'c' => ['text' => 'Say as little as possible', 'score' => 1],
                    'd' => ['text' => 'Explain it but never check understanding', 'score' => 2],
                ],
                'correct' => 'B',
            ],
            [
                'question_text' => 'If someone disagrees with my idea, I...',
                'options' => [
                    'a' => ['text' => 'Get upset and stop listening', 'score' => 1],
                    'b' => ['text' => 'Listen to their point and respond calmly', 'score' => 4],
                    'c' => ['text' => 'Ignore them', 'score' => 2],
                    'd' => ['text' => 'Argue loudly until they agree', 'score' => 1],
                ],
                'correct' => 'B',
            ],
            [
                'question_text' => 'When presenting to a group, I...',
                'options' => [
                    'a' => ['text' => 'Speak clearly and make eye contact', 'score' => 4],
                    'b' => ['text' => 'Read directly from notes without looking up', 'score' => 2],
                    'c' => ['text' => 'Mumble and rush through it', 'score' => 1],
                    'd' => ['text' => 'Speak clearly but avoid eye contact', 'score' => 3],
                ],
                'correct' => 'A',
            ],
        ],
        'Problem Solving' => [
            [
                'question_text' => 'When I face a new problem, I...',
                'options' => [
                    'a' => ['text' => 'Try to solve it step by step', 'score' => 4],
                    'b' => ['text' => 'Ask someone else to solve it for me', 'score' => 2],
                    'c' => ['text' => 'Give up quickly', 'score' => 1],
                    'd' => ['text' => 'Try random things without a plan', 'score' => 2],
                ],
                'correct' => 'A',
            ],
            [
                'question_text' => "If my first solution doesn't work, I...",
                'options' => [
                    'a' => ['text' => 'Give up', 'score' => 1],
                    'b' => ['text' => 'Try a different approach', 'score' => 4],
                    'c' => ['text' => 'Keep repeating the same thing', 'score' => 2],
                    'd' => ['text' => 'Blame the problem for being too hard', 'score' => 1],
                ],
                'correct' => 'B',
            ],
            [
                'question_text' => 'Before starting a project, I...',
                'options' => [
                    'a' => ['text' => 'Jump straight in without planning', 'score' => 2],
                    'b' => ['text' => 'Think through the steps first', 'score' => 4],
                    'c' => ['text' => 'Wait for someone to tell me what to do', 'score' => 1],
                    'd' => ['text' => 'Plan only part of it', 'score' => 3],
                ],
                'correct' => 'B',
            ],
        ],
    ];

    public function run(): void
    {
        $total = 0;

        foreach (self::QUESTIONS as $categoryName => $questions) {
            $category = StandardAssessmentCategory::where('category_name', $categoryName)->where('grade_id', 1)->first();

            if (!$category) {
                $this->command?->error("Category '{$categoryName}' not found — run StandardAssessmentCategorySeeder first.");
                continue;
            }

            foreach ($questions as $q) {
                StandardAssessmentQuestion::firstOrCreate(
                    ['category_id' => $category->id, 'question_text' => $q['question_text']],
                    [
                        'option_a' => $q['options']['a']['text'],
                        'option_a_score' => $q['options']['a']['score'],
                        'option_b' => $q['options']['b']['text'],
                        'option_b_score' => $q['options']['b']['score'],
                        'option_c' => $q['options']['c']['text'],
                        'option_c_score' => $q['options']['c']['score'],
                        'option_d' => $q['options']['d']['text'],
                        'option_d_score' => $q['options']['d']['score'],
                        'correct_option' => $q['correct'],
                        'active' => 1,
                    ]
                );
                $total++;
            }
        }

        $this->command?->info("Seeded {$total} standard assessment questions across 2 categories.");
    }
}
