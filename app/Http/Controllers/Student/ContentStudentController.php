<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Stream;
use App\Models\Students;
use App\Models\Studentscontent;
use Illuminate\Support\Facades\Session;
use App\Models\QuizQuestions;
use App\Models\QuizAttempts;
use App\Models\QuizAnswers;
use Illuminate\Http\Request;
use App\Helpers\QuizHelper;
use App\Helpers\StudentRewardPointsHelper;
use App\Models\WeeklyChallenges;
use App\Models\StudentCommunications;
use DB;
use File;
use Carbon\Carbon;

class ContentStudentController extends Controller
{
    public function index()
    {
        $levels = Grade::where('is_publish', 1)->orderBy('display_order_id')->get();
        $filtered_levels = [];

        $student = auth()->user()->students ?? null;
        $studentGradeIds = [];
        if ($student && !empty($student->grade_id)) {
            $studentGradeIds = array_map('intval', explode(',', $student->grade_id));
        }
        
        $filtered_collection = $levels->filter(function ($item) use (&$filtered_levels) {
            if($item->is_primary == 1) {
                $filtered_levels['primary'][$item->id] = $item->toArray();        
            } else {
                $filtered_levels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();
        return view('student.content.list', compact('filtered_levels','studentGradeIds'));
    }

    public function streamList(Grade $grade)
    {
        if ($grade->is_publish == 0) {
        return redirect()->route('student.contentlist.contentList');
        }
        $student = Students::select('school_id', 'grade_id')->with(['school' => function($query) {
            $query->select(['id','course_end_date']);
        }])->find(Session::get('student_id'));
        $hasAccess = false;
        $streams = Stream::where('agegroup_id', $grade->id)->orderBy('display_order_id')->get();
        if($student && !empty($student->grade_id)) {
            if(in_array($grade->id, explode(',',$student->grade_id))) {
                $hasAccess = true;
                if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                    $hasAccess = false;
                }
            }
        }
        
        if($hasAccess && $streams->count()) {
            foreach($streams as $stream) {
                $stream->hasAccess = false;
                $sessionCount = Studentscontent::select('id')->where('stream_id', $stream->id)->where('is_publish', 1)->get();
                if($sessionCount->count() || !empty($stream->videoUrl) || !empty($stream->scormFile)) {
                    $stream->hasAccess = true;
                }
            }
        }
        
        return view('student.content.streamlist', compact('grade', 'streams', 'hasAccess'));
    }

    public function view($id)
    {
        $streamData = Stream::find($id);
        if($streamData) {
            $hasAccess = false;
            $ageGroup_id = $streamData->agegroup_id;
            $student_id = Session::get('student_id');
            $student = Students::select('school_id', 'grade_id')->with(['school' => function($query) {
                $query->select(['id','course_end_date']);
            }])->find($student_id);
            if($student && !empty($student->grade_id) && in_array($ageGroup_id, explode(',',$student->grade_id))) {
                $hasAccess = true;
                if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                    $hasAccess = false;
                }
            } 
            if(!$hasAccess) {
                return redirect()->route('student.contentlist.contentList');
            }
            $school_id = $student->school_id;
            
            if(!empty($streamData->scormFile)) {
                $scormFileName = $streamData->scormFile;
                $scormFilePath = public_path('/scorm-files/stream/');
                if(File::exists($scormFilePath . $scormFileName)) {
                    $scormFileNameWithoutExtension = pathinfo($scormFileName, PATHINFO_FILENAME);
                    $extractPath = $scormFilePath . $scormFileNameWithoutExtension;
                    if(!File::exists($extractPath)) {
                        $zip = new \ZipArchive();
                        $x = $zip->open($scormFilePath . $scormFileName);
                        if($x === true) {
                            $zip->extractTo($extractPath);
                            $zip->close();
                        } 
                    } 
                    $streamData->scormFile = $scormFileNameWithoutExtension;
                    $scormCompleted = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'scorm_completion', $streamData->id)->count() +StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'video_learning_point', $streamData->id)->count();
                    $streamData->scormCompleted = $scormCompleted;
                } else {
                    $streamData->scormFile = '';
                }
            }
            
            $quizData = [];
            $attemptedQuizIdList = [];
            $sessionData = Studentscontent::select('id','title', 'video', 'video_url', 'worksheet', 'worksheet_name')->where('stream_id', $id)->where('is_publish', 1)->orderBy('display_order_id')->get();

            if(!$sessionData->count() && empty($streamData->videoUrl) && empty($streamData->scormFile)) {
                return redirect()->route('student.contentlist.streamlist', $streamData->agegroup_id);
            }
            
            $youtubeCompletionData = [];

            if($sessionData->count()) {
                $contentIdList = $sessionData->pluck('id')->toArray();
                // preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/", $content['videoUrl'], $matches);
                // if(!empty($matches)) 
                //     $content['video_url'] = $matches[0];
                $quizData = QuizHelper::getAllQuiz($contentIdList, ['content_id']);
                if($quizData) {
                    $quizData = $quizData->unique('content_id')->toArray();
                    $quizData = array_column($quizData, 'content_id');
                    foreach($quizData as $quizId) {
                        $isQuizAttempt = QuizHelper::isQuizAttempt($quizId, $student_id);
                        if($isQuizAttempt->count() && $isQuizAttempt[0]->is_submit) {
                            $attemptedQuizIdList[] = $quizId;
                        }
                    }
                }
                foreach($sessionData as $sData) {
                    //  Exclude all the assignments in the student section for the School of Entrepreneurship if they have been created for All Schools
                    if($school_id == 3) {
                        $assignmentData[$sData->id] = StudentCommunications::select(['id', 'title', 'is_active'])->where('grade_id', $ageGroup_id)->where('stream_id', $id)->where('session_id', $sData->id)->where(function($query) use($school_id) {
                            $query->where('school_id', $school_id);
                        })->get()->toArray();
                    } else {
                        $assignmentData[$sData->id] = StudentCommunications::select(['id', 'title', 'is_active'])->where('grade_id', $ageGroup_id)->where('stream_id', $id)->where('session_id', $sData->id)->where(function($query) use($school_id) {
                            $query->where('school_id', $school_id)->orWhere('school_id', '=', 0);
                        })->get()->toArray();
                    }
                    $youtubeCompleted = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'youtube_completion', $sData->id);
                    $youtubeCompletionData[$sData->id]['youtubeCompleted'] = $youtubeCompleted->count();
                    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $sData->video_url, $match);
                    if(!empty($match)) {
                        $youtubeCompletionData[$sData->id]['youtube_id'] = $match[1];
                    }
                }
            }

            if(!$sessionData->count()) {
                if($school_id == 3) {
                    $assignmentData = StudentCommunications::select(['id', 'title', 'is_active'])->where('grade_id', $ageGroup_id)->where('stream_id', $id)->where(function($query) use($school_id) {
                        $query->where('school_id', $school_id);
                    })->get()->toArray();
                } else {
                    $assignmentData = StudentCommunications::select(['id', 'title', 'is_active'])->where('grade_id', $ageGroup_id)->where('stream_id', $id)->where(function($query) use($school_id) {
                        $query->where('school_id', $school_id)->orWhere('school_id', '=', 0);
                    })->get()->toArray();
                }
                $youtubeCompleted = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'youtube_completion', $id);
                $streamData->youtubeCompleted = $youtubeCompleted->count();
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $streamData->videoUrl, $match);
                if(!empty($match)) {
                    $streamData->youtube_id = $match[1];
                }
            }
            
            return view('student.content.view_content', compact('streamData', 'sessionData', 'quizData', 'attemptedQuizIdList', 'assignmentData', 'youtubeCompletionData'));

        } else {
            return redirect()->route('student.contentlist.contentList');
        }
    }

    public function quiz($type, $quizId)
    {
        if(!in_array($type, ['content', 'weekly', 'daily'])) {
            return redirect()->route('student.dashboard');
        }
        
        $student_id = Session::get('student_id');
        
        if($type == 'content') {

            $student = Students::select('school_id', 'grade_id')->with(['school' => function($query) {
                $query->select(['id','course_end_date']);
            }])->find($student_id);
            
            $content = Studentscontent::find($quizId)->toArray();
            /* START - CHECK STUDENT HAVE ACCESS OF QUIZ FOR THIS CONTENT */
            $hasAccess = false;
            if($student && !empty($student->grade_id) && in_array($content['ageGroup_id'], explode(',',$student->grade_id))) {
                $hasAccess = true;
                if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                    $hasAccess = false;
                }
            } 
            if(!$hasAccess) {
                return redirect()->route('student.contentlist.contentList');
            }
            /* END - CHECK STUDENT HAVE ACCESS OF QUIZ FOR THIS CONTENT */
    
            /* START - CHECK CONTENT IS PUBLISHED */
            if(!$content['is_publish']) {
                return redirect()->route('student.contentlist.contentList');
            }
            /* END - CHECK CONTENT IS PUBLISHED */
            $streams = Stream::find($content['stream_id']);
            $levelName = Grade::find($streams->agegroup_id)->grade;
            $sessionName = $streams->title;
            $content['level'] = $levelName;
            $content['session'] = $sessionName;
            $quizType = 'session';
        } else if(in_array($type, ['weekly', 'daily'])) {
            $dailyQuizEnabled = \App\Models\ModuleSetting::getValue('daily_quiz_enabled', false);
            if(!$dailyQuizEnabled) {
                return redirect()->route('student.event_list');
            }
            $dailyChallengeData = QuizHelper::getDailyChallengeUnlockStatus(Session::get('student_id'));
            $quizzes = $dailyChallengeData['quizzes'];
         
            $challenge_data = $quizzes->where('id', $quizId)->first();
            if(! $challenge_data || !$challenge_data->is_unlocked) {
                return redirect()->route('student.event_list')
                    ->with('error', 'This quiz is not yet unlocked.');
            }

            $content['id'] = $challenge_data->id;
            $content['challenge_name'] = $challenge_data->challenge_name;
            $quizType = 'weekly_challenge';
            if($type == 'daily') {
                $quizType = 'daily_challenge';
            }
        }
        $content['type'] = $type;
        $content['content_type'] = $quizType;
        
        $questions = [];
        $answers = [];
        $totalQuestions = 0;
        $score = 0;
        $isQuizSubmit = 0;
            
        /* START - CHECK STUDENT SUBMITTED QUIZ OR NOT */
        $isQuizAttempt = QuizHelper::isQuizAttempt($quizId, $student_id, $quizType);
        /* END - CHECK STUDENT SUBMITTED QUIZ OR NOT */

        if(!$isQuizAttempt->count()) {

            /* START - GET RANDOM QUESTIONS FOR THIS CONTENT'S QUIZ */
            $questions = QuizQuestions::inRandomOrder()->where('content_id', $quizId)->where('content_type', $quizType)->where('isDisabled', 0)->get();
            /* END - GET RANDOM QUESTIONS FOR THIS CONTENT'S QUIZ */
            $totalQuestions = $questions->count();

            /* START - LOG QUIZ DATA TO Quiz_Attempts TABLE AND Quiz_Answers TABLE  */
            if($totalQuestions) {
                foreach($questions as $que) {
                    $all_question_id[$que->id] = [
                            'student_id' => $student_id, 
                            'quiz_id' => $quizId, 
                            'quiz_type' => $quizType,
                            'question_id' => $que->id, 
                            'is_attempt' => 0, 
                            'created_by' => $student_id, 
                            'created_at' => now()
                        ];    
                }
                QuizAttempts::insert([
                    'student_id' => $student_id,
                    'quiz_id' => $quizId,
                    'quiz_type' => $quizType,
                    'is_submit' => 0,
                    'created_by' => $student_id,
                    'created_at' => now()
                ]);
                QuizAnswers::insert($all_question_id);
            }
            /* END - LOG QUIZ DATA TO Quiz_Attempts TABLE AND Quiz_Answers TABLE  */
            
        } elseif($isQuizAttempt[0]->is_submit) {

            $isQuizSubmit = 1;

            /* START - GET QUIZE SCORE */
            $scoreData = QuizHelper::getQuizScore($quizId, $student_id, $quizType);
            /* END - GET QUIZE SCORE */
            
            $answers = $scoreData['answers'];
            $score = $scoreData['score'];
            $totalQuestions = $scoreData['totalQuestions'];

        } else {

            /* START - GET QUESTIONS */
            $questions = DB::table('quiz_questions')
                ->Join('quiz_answers', 'quiz_questions.id', '=', 'quiz_answers.question_id')
                ->select('quiz_questions.*', 'quiz_answers.answer', 'quiz_answers.is_attempt')
                ->where('quiz_questions.content_type', $quizType)
                ->where('quiz_answers.quiz_id', $quizId)
                ->where('quiz_answers.quiz_type', $quizType)
                ->where('quiz_answers.student_id', $student_id)
                ->orderBy('quiz_answers.id')
                ->get();
            /* END - GET QUESTIONS */
            
            $totalQuestions = $questions->count();
        } 
        
        return view('student.content.quiz_content', compact('questions','answers','isQuizSubmit', 'score', 'totalQuestions', 'content'));
        
    }

    public function submitQuiz(Request $request)
    {
        $quizId = $request->content_id;
        $quiz_type = $request->content_type;
        $answersArr = json_decode($request->answers,true);
        $queIdsArr = json_decode($request->queIds,true);
        $student_id = Session::get('student_id');
        
        // START - UPDATE ENTRY TO QUIZ ATTEMPTS TABLE
        QuizAttempts::where(['student_id' => $student_id, 'quiz_id' => $quizId, 'quiz_type' => $quiz_type])->update([
            'is_submit' => 1, 
            'updated_by' => $student_id
        ]);
        // END - UPDATE ENTRY TO QUIZ ATTEMPTS TABLE

        // START - UPDATE STUDENT ANSWERS
        foreach($queIdsArr as $queId => $ans) {
            $answer = '';
            if(array_key_exists($queId, $answersArr)){
                $answer = $answersArr[$queId];
            }
            
            QuizAnswers::where(['student_id' => $student_id, 'quiz_id' => $quizId, 'quiz_type' => $quiz_type, 'question_id' => $queId])->update([
                'is_attempt' => 1, 
                'updated_by' => $student_id, 
                'answer' => $answer
            ]);
        }
        // END - UPDATE STUDENT ANSWERS

        /* START - Store Weekly Challenger Reward Points */
        if(in_array($quiz_type, ['weekly_challenge', 'daily_challenge'])) {

            /* START - GET QUIZE SCORE */
            $scoreData = QuizHelper::getQuizScore($quizId, $student_id, $quiz_type);
            /* END - GET QUIZE SCORE */

            StudentRewardPointsHelper::storeRewardPoints([
                'student_id' => $student_id,
                'reward_type' => $quiz_type,
                'item_id' => $quizId,
                'reward_points' => $scoreData['score'],
            ]);
        }
        /* END - Store Weekly Challenger Reward Points */
        
        return true;
    }

    public function saveQuiz(Request $request)
    {
        $quizId = $request->content_id;
        $quiz_type = $request->content_type;
        $queId = json_decode($request->queId,true);
        $answer = json_decode($request->answer,true);
        $student_id = Session::get('student_id');
        
        if(!empty($quizId) && !empty($queId)) {

            $updateData = ['is_attempt' => 1, 'updated_by' => $student_id];
            if(!empty($answer)) {
                $updateData['answer'] = $answer;
            }
    
            QuizAnswers::where(['student_id' => $student_id, 'quiz_id' => $quizId, 'quiz_type' => $quiz_type, 'question_id' => $queId])->update($updateData);

            return true;
        }

        return false;
    }

    /* START - Store SCORM Completion Reward Points */
    public function storeScormCompletionPoints(Request $request)
    {
        $stream_id = (int) $request->stream_id;
        if($stream_id) {
            $student_id = Session::get('student_id');
            $scormCompleted = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'video_learning_point', $stream_id);
            if(!$scormCompleted->count()) {
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id' => $student_id,
                    'reward_type' => 'video_learning_point',
                    'item_id' => $stream_id,
                    'reward_points' => 5,
                ]);
            }
        }
    }
    /* END - Store SCORM Completion Reward Points */

     /* START - Calculate and Store SCORM Learning Reward Points */
     public function storeScormLearningPoints(Request $request)
     {
         $stream_id = (int) $request->stream_id;
         $percentage = $request->percentage;
         if($stream_id && isset($percentage)) {
            $student_id = Session::get('student_id');
            $streamData = Stream::select('no_of_questions')->find($stream_id);
            if($streamData) {
                $no_of_questions = $streamData->no_of_questions;
                if(!empty($no_of_questions)) {
                    $learning_reward_points = round(($no_of_questions * $percentage) / 100) * 5;
                    $learning_reward_points = number_format($learning_reward_points);
                    StudentRewardPointsHelper::storeRewardPoints([
                        'student_id' => $student_id,
                        'reward_type' => 'scorm_learning_reward',
                        'item_id' => $stream_id,
                        'reward_points' => $learning_reward_points,
                    ]);
                }
            }
         }
     }
     /* END - Calculate and Store SCORM Learning Reward Points */

     /* START - Store YouTube Completion Reward Points */
    public function storeYoutubeCompletionPoints(Request $request)
    {
        $item_id = (int) $request->item_id;
        $item_type = $request->item_type;
        if($item_id) {
            $student_id = Session::get('student_id');
            $youtubescormCompleted = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'youtube_completion', $item_id);
            if(!$youtubescormCompleted->count()) {
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id' => $student_id,
                    'reward_type' => 'youtube_completion',
                    'item_id' => $item_id,
                    'item_type' => $item_type ?? null,
                    'reward_points' => 2,
                ]);
            }
        }
    }
    /* END - Store YouTube Completion Reward Points */

}
