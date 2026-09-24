<?php

namespace Database\Seeders;

use App\Models\ModuleSetting;
use App\Models\QuizQuestions;
use App\Models\WeeklyChallenges;
use Illuminate\Database\Seeder;

/**
 * Seeds the "Daily Challenges" feature (student.event.getDailyChallenges,
 * QuizHelper::getDailyChallengeUnlockStatus): rows in `weekly_challenges`
 * with challenge_type = 'daily', each with 2 quiz questions in
 * `quiz_questions` (content_type = 'daily_challenge'). Also enables the
 * `daily_quiz_enabled` module setting the feature is gated behind — without
 * it the Daily Challenges panel stays empty regardless of data.
 */
class DailyChallengeSeeder extends Seeder
{
    private const CHALLENGES = [
        [
            'name' => 'Monday Mindset: Spotting Everyday Problems',
            'image' => 'daily-monday-mindset.png',
            'questions' => [
                [
                    'question' => 'What is the first step to finding a great business idea?',
                    'options' => ['Spotting a real problem people face', 'Copying a famous brand', 'Picking a random product', 'Waiting for an idea to appear'],
                    'correct' => 'A',
                ],
                [
                    'question' => 'Which of these is an example of "spotting a problem"?',
                    'options' => ['Noticing classmates waste plastic wrappers', 'Watching TV all day', 'Choosing a favorite color', 'Memorizing facts'],
                    'correct' => 'A',
                ],
            ],
        ],
        [
            'name' => 'Tuesday Trivia: Turning Ideas into Action',
            'image' => 'daily-tuesday-trivia.png',
            'questions' => [
                [
                    'question' => 'After you have an idea, what should you do next?',
                    'options' => ['Forget about it', 'Make a simple plan to test it', 'Keep it a secret forever', 'Wait for someone else to build it'],
                    'correct' => 'B',
                ],
                [
                    'question' => 'What is a prototype?',
                    'options' => ['A finished product ready to sell', 'A early, simple version of your idea to test', 'A type of advertisement', 'A business license'],
                    'correct' => 'B',
                ],
            ],
        ],
        [
            'name' => 'Wednesday Wisdom: Know Your Customer',
            'image' => 'daily-wednesday-wisdom.png',
            'questions' => [
                [
                    'question' => 'Why is it important to know who your customer is?',
                    'options' => ['So you can build something they actually need', 'It is not important', 'So you can copy competitors exactly', 'To make the product more expensive'],
                    'correct' => 'A',
                ],
                [
                    'question' => 'What is a good way to learn what customers want?',
                    'options' => ['Guessing', 'Asking them questions and listening', 'Ignoring feedback', 'Assuming everyone wants the same thing'],
                    'correct' => 'B',
                ],
            ],
        ],
        [
            'name' => 'Thursday Throwdown: Pitch Perfect',
            'image' => 'daily-thursday-throwdown.png',
            'questions' => [
                [
                    'question' => 'What should a good pitch clearly explain?',
                    'options' => ['The problem and how your idea solves it', 'Only your favorite hobbies', 'Nothing in particular', 'Every possible detail at once'],
                    'correct' => 'A',
                ],
                [
                    'question' => 'Why is practicing your pitch out loud helpful?',
                    'options' => ['It helps you sound clear and confident', 'It wastes time', 'It makes the idea worse', 'It is not helpful at all'],
                    'correct' => 'A',
                ],
            ],
        ],
        [
            'name' => 'Friday Finale: Reflect & Grow',
            'image' => 'daily-friday-finale.png',
            'questions' => [
                [
                    'question' => 'Why is it useful to reflect on what you learned each week?',
                    'options' => ['To help you improve next time', 'It has no real use', 'To forget your mistakes', 'To avoid trying new things'],
                    'correct' => 'A',
                ],
                [
                    'question' => 'What is a healthy way to respond to feedback?',
                    'options' => ['Ignore it completely', 'Get upset and give up', 'Listen and use it to improve', 'Argue that you are always right'],
                    'correct' => 'C',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        ModuleSetting::updateOrCreate(['key' => 'daily_quiz_enabled'], ['value' => 1]);

        foreach (self::CHALLENGES as $item) {
            $challenge = WeeklyChallenges::firstOrCreate(
                ['challenge_name' => $item['name'], 'challenge_type' => 'daily'],
                ['challenge_image' => $item['image'], 'is_active' => 1]
            );

            foreach ($item['questions'] as $q) {
                QuizQuestions::firstOrCreate(
                    ['content_id' => $challenge->id, 'content_type' => 'daily_challenge', 'question' => $q['question']],
                    [
                        'option1' => $q['options'][0],
                        'option2' => $q['options'][1],
                        'option3' => $q['options'][2],
                        'option4' => $q['options'][3],
                        'correct_option' => $q['correct'],
                        'isDisabled' => 0,
                        'created_by' => 1,
                    ]
                );
            }
        }

        $this->command?->info('Seeded ' . count(self::CHALLENGES) . ' daily challenges with quiz questions, and enabled daily_quiz_enabled.');
    }
}
