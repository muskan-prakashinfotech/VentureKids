<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\WeeklyChallenges;
use App\Models\QuizQuestions;
use App\Models\QuizAttempts;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuizHelper
{
    public static function isQuizPublish($quizId)
    {
        return QuizQuestions::where('content_id', $quizId)->get();
    }

    public static function isQuizAttempt($quizId, $student_id, $quizType = 'session')
    {
        return QuizAttempts::where('quiz_id', $quizId)->where('student_id', $student_id)->where('quiz_type', $quizType)->get();
    }

    public static function getAllSubmittedQuiz($quizId, $quizType = 'session')
    {
        return QuizAttempts::where('quiz_id', $quizId)->where('quiz_type', $quizType)->where('is_submit', 1)->get();
    }

    public static function getAllStudentSubmittedQuiz($student_id, $quizType = 'session')
    {
        return QuizAttempts::where('student_id', $student_id)->where('quiz_type', $quizType)->where('is_submit', 1)->get();
    }

    public static function getDailyChallengeUnlockStatus($student_id)
    {
        $dailyQuizEnabled = \App\Models\ModuleSetting::getValue('daily_quiz_enabled', false);
        $quizzes = collect();
        $attemptedQuizIdList = [];

        if (! $dailyQuizEnabled) {
            return compact('dailyQuizEnabled', 'quizzes', 'attemptedQuizIdList');
        }

        $today = now()->startOfDay();

        $quizzes = WeeklyChallenges::select('id', 'challenge_name', 'challenge_type', 'challenge_image', 'created_at', 'is_active')
            ->where('is_active', 1)
            ->where('challenge_type', 'daily')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $submittedQuizzes = QuizAttempts::select('quiz_id', 'updated_at', 'created_at')
            ->where('student_id', $student_id)
            ->where('quiz_type', 'daily_challenge')
            ->where('is_submit', 1)
            ->orderBy('updated_at', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->keyBy('quiz_id');

        $attemptedQuizIdList = $submittedQuizzes->keys()->values()->toArray();
        $canUnlockNext = true;
        $previousSubmittedAt = null;

        foreach ($quizzes as $index => $quiz) {
            $submission = $submittedQuizzes->get($quiz->id);
            $submittedAt = $submission
                ? Carbon::parse($submission->updated_at ?? $submission->created_at)->startOfDay()
                : null;

            if ($index === 0) {
                $quiz->is_unlocked = true;
                $quiz->unlock_date = $today->copy();
            } else {
                $quiz->is_unlocked = $canUnlockNext && $previousSubmittedAt && $previousSubmittedAt->lt($today);
                $quiz->unlock_date = $previousSubmittedAt ? $previousSubmittedAt->copy()->addDay()->startOfDay() : null;
            }

            $quiz->is_attempted = (bool) $submission;

            if ($submission) {
                $previousSubmittedAt = $submittedAt;
            } else {
                $canUnlockNext = false;
            }
        }

        return compact('dailyQuizEnabled', 'quizzes', 'attemptedQuizIdList');
    }
        
    public static function getQuizScore($quizId, $student_id, $quizType = 'session')
    {  
        $answers = [];
        $score = 0;

        $questions = DB::table('quiz_questions')
            ->Join('quiz_answers', 'quiz_questions.id', '=', 'quiz_answers.question_id')
            ->select('quiz_questions.*', 'quiz_answers.answer', 'quiz_answers.is_attempt')
            ->where('quiz_questions.content_type', $quizType)
            ->where('quiz_answers.quiz_id', $quizId)
            ->where('quiz_answers.quiz_type', $quizType)
            ->where('quiz_answers.student_id', $student_id)
            ->orderBy('quiz_answers.is_attempt', 'desc')
            ->get();
        
        
        $totalQuestions = $questions->count();
        
        if($totalQuestions) {

            $questions->filter(function ($item, $key) use(&$answers, &$score) {
                 
                $options = [
                    'A' => $item->option1,
                    'B' => $item->option2,
                    'C' => $item->option3,
                    'D' => $item->option4,
                ];

                $answers[$item->id]['question'] = $item->question;
                $answers[$item->id]['optionA'] = $options['A'];
                $answers[$item->id]['optionB'] = $options['B'];
                $answers[$item->id]['optionC'] = $options['C'];
                $answers[$item->id]['optionD'] = $options['D'];
                
                $correctOptiondecoded = json_decode($item->correct_option, true);
                $correctOptiondecoded = is_array($correctOptiondecoded) ? $correctOptiondecoded : explode(',', $item->correct_option);

                $correctOptions = array_filter(array_map(fn($opt) => $options[trim($opt)] ?? null, $correctOptiondecoded));
                
                $answers[$item->id]['correct_option'] = implode(',', $correctOptions);
                $studentAnswer = isset($options[$item->answer]) ? $options[$item->answer] : '';
                $answers[$item->id]['answer'] = $studentAnswer;
                
                if (!empty($studentAnswer) && in_array($studentAnswer, $correctOptions)) {
                    $score++;
                }

            });
        }

        return ['answers' => $answers, 'score' => $score, 'totalQuestions' => $totalQuestions];
    }

    public static function getAllQuiz($contentIdList, $fieldList, $quizType = 'session') {
        return QuizQuestions::select($fieldList)->whereIn('content_id', $contentIdList)->where('content_type', $quizType)->get();
    }

}
