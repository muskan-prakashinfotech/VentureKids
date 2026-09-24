<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\WeeklyChallenges;
use App\Models\QuizQuestions;
use App\Models\QuizAttempts;
use App\Models\QuizAnswers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File as FacadesFile;
use File;

class WeeklyChallengeController extends Controller
{
    public function weeklyChallengeList(){
        $challengeList = WeeklyChallenges::all();
        $dailyQuizEnabled = \App\Models\ModuleSetting::getValue('daily_quiz_enabled', true);
        return view('backend.weekly_challenge.weekly_challenge_list', compact('challengeList','dailyQuizEnabled'));
    }

    public function weeklyChallengeAdd() {
        return view('backend.weekly_challenge.weekly_challenge_add');   
    }

    public function weeklyChallengeStore(Request $request) {
        $request->validate([
            'challenge_name' => 'required',
            'challenge_image' => 'required',
        ]);
        $challenge = new WeeklyChallenges;
        $challenge->challenge_name = $request->challenge_name;
        $challenge_image = $request->challenge_image;
        $challenge->challenge_type = 'daily';
        if($challenge_image) {
            $extension = strtolower($challenge_image->getClientOriginalExtension());
            if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                return back()->withErrors(["challenge_image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
            }
            $image_name = Str::random(10); //unique name generate every time
            $ext = strtolower($challenge_image->getClientOriginalExtension());
            $image_full_name = 'image_' . $image_name . '.' . $ext;
            $upload_path = 'image/challenge/';
            $challenge_image->move($upload_path, $image_full_name);
            $challenge->challenge_image = $image_full_name;
        }
        $challenge->save();
        $challenge_id = $challenge->id;
        
        // START - Quiz Questions 
        $questions = $request->questions;
        if(!empty($questions)) { 
            $options = $request->options;
            if(!empty($options)) $options = array_values($options);
            $answers = $request->answers;
            
            foreach($questions as $queKey => $question) {
                $quizQuestions = new QuizQuestions;
                $quizQuestions->content_id = $challenge_id;
                $quizQuestions->content_type = 'daily_challenge';
                $quizQuestions->question = $question;
                foreach($options[$queKey] as $optKey => $option) {
                    $optKey++;
                    $optFld = 'option'.$optKey;
                    $quizQuestions->$optFld = $option;
                } 
                
                if(isset($answers[$queKey]) && is_array($answers[$queKey])) {
                $quizQuestions->correct_option = json_encode($answers[$queKey]);
                } else {
                $quizQuestions->correct_option = json_encode([]); // fallback
                }

                $quizQuestions->created_by = Session::get('user_id');
                $quizQuestions->save();
            }
        }
        // END - Quiz Questions 

        return redirect()->route('backend.weeklyChallengeList')->with('message', 'Daily Quiz Added Successfully!');
    }

    public function weeklyChallengeEdit($challenge_id) {
        $challenge_data = WeeklyChallenges::find($challenge_id);
        if($challenge_data) {
            $quiz_type = 'weekly_challenge';
            if($challenge_data->challenge_type == 'daily') {
                $quiz_type = 'daily_challenge';
            }
            $challenge_data->questions = $challenge_data->questionsByType($quiz_type)->get();
            $queIdList = $challenge_data->questions->pluck('id');
            $attemptedList = [];
            if(!empty($queIdList)) {
                $attemptedList = QuizAnswers::select('question_id')->groupBy('question_id')->whereIn("question_id", $queIdList)->pluck('question_id')->toArray();
            }
            return view('backend.weekly_challenge.weekly_challenge_edit', compact('challenge_data', 'attemptedList'));
        }
        return redirect()->route('backend.weeklyChallengeList')->with('error', 'Challenge Not Found!');   
    }

    public function weeklyChallengeUpdate(Request $request) {
        $request->validate([
            'challenge_name' => 'required',
        ]);
        $challenge_id = $request->challenge_id;
        $challenge = WeeklyChallenges::find($challenge_id);
        $challenge->challenge_name = $request->challenge_name;
        $challenge_image = $request->challenge_image;
        if($challenge_image) {
            $extension = strtolower($challenge_image->getClientOriginalExtension());
            if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                return back()->withErrors(["challenge_image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
            }
            if($challenge->challenge_image) {
                $destinationPath = public_path('image/challenge/' . $challenge->challenge_image);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
            }
            $image_name = Str::random(10);
            $ext = strtolower($challenge_image->getClientOriginalExtension());
            $image_full_name = 'image_' . $image_name . '.' . $ext;
            $upload_path = 'image/challenge/';
            $challenge_image->move($upload_path, $image_full_name);
            $challenge->challenge_image = $image_full_name;
        } else if(empty($challenge->challenge_image)) {
            return back()->withErrors(["challenge_image" => "The quiz image field is required."])->withInput();
        }
        $challenge->save();

        // START - Quiz Questions
        $questions = $request->questions;
        if(!empty($questions)) {

            $options = $request->options;
            if(!empty($options)) $options = array_values($options);
            $answers = $request->answers;
            $questionIds = $request->questionIds;
            foreach($questions as $queKey => $question) {
                if(!empty($questionIds) && array_key_exists($queKey, array_keys($questionIds))) {
                    $quizQuestions = QuizQuestions::find($questionIds[$queKey]);
                    $quizQuestions->updated_by = Session::get('user_id');
                } else {
                    $quizQuestions = new QuizQuestions;
                    $quizQuestions->created_by = Session::get('user_id');
                }
                $quizQuestions->content_id = $challenge_id;
                $quizQuestions->content_type = 'weekly_challenge';
                if($challenge->challenge_type == 'daily') {
                    $quizQuestions->content_type = 'daily_challenge';
                }
                $quizQuestions->question = $question;
                foreach($options[$queKey] as $optKey => $option) {
                    $optKey++;
                    $optFld = 'option'.$optKey;
                    $quizQuestions->$optFld = $option;
                }    
                if(isset($answers[$queKey]) && is_array($answers[$queKey])) {
                $quizQuestions->correct_option = json_encode($answers[$queKey]);
                } else {
                $quizQuestions->correct_option = json_encode([]); // fallback
                }
                $quizQuestions->save();
            }
        }
        // END - Quiz Questions

        return redirect()->route('backend.weeklyChallengeList')->with('message', 'Daily Quiz Updated Successfully!');
    }

    public function deleteWeeklyChallengeImage(Request $request) {
        $challengeId = (int) $request->challengeId;
        if($challengeId) {
            $challengeData = WeeklyChallenges::find($challengeId);
            if($challengeData->challenge_image) {
                $destinationPath = public_path('/image/challenge/');
                FacadesFile::delete($destinationPath . $challengeData->challenge_image);
            }
            $challengeData->challenge_image = null;
            $challengeData->save();
        }
        return true;
    }

    public function weeklyChallengeDelete($id, $type) {
        $challengeId = (int) $id;
        $challenge_type = 'weekly_challenge';
        if($type == 'daily') {
            $challenge_type = 'daily_challenge';
        }
        if($challengeId) {
            $challengeData = WeeklyChallenges::find($challengeId);
            if($challengeData->challenge_image) {
                $destinationPath = public_path('/image/challenge/');
                FacadesFile::delete($destinationPath . $challengeData->challenge_image);
            }
            $question = QuizQuestions::select('content_id')->where('content_id', $challengeId)->get();
            if($question->count()) {
                $quizId = $question[0]->content_id;
                QuizAnswers::where("quiz_id",$quizId)->where("quiz_type",$challenge_type)->delete();
                QuizAttempts::where("quiz_id",$quizId)->where("quiz_type",$challenge_type)->delete();
                QuizQuestions::where("content_id",$challengeId)->where("content_type",$challenge_type)->delete();
            }
            $challengeData->delete();
            return redirect()->back()->with('message', 'Daily Quiz Deleted Successfully!');
        }
        return redirect()->back()->with('error', 'Error Occurred Please try again!');
    }

    public function deleteQuestion(Request $request)
    {
        $questionId = (int) $request->questionId;
        if($questionId) {
            $question = QuizQuestions::select('content_id')->find($questionId);
            $quizId = $question->content_id;
            QuizAnswers::where("question_id",$questionId)->where("quiz_id",$quizId)->delete();
            QuizAttempts::where("quiz_id",$quizId)->delete();
            QuizQuestions::where("id", $questionId)->delete();
        }
        return true;
    }

    public function disableQuestion(Request $request)
    {
        $questionId = (int) $request->questionId;
        if($questionId) {
            QuizQuestions::find($questionId)->update(['isDisabled' => 1]);
        }
        return true;
    }

    public function enableQuestion(Request $request)
    {
        $questionId = (int) $request->questionId;
        if($questionId) {
            QuizQuestions::find($questionId)->update(['isDisabled' => 0]);
        }
        return true;
    }

}
