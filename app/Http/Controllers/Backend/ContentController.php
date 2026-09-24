<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Content\Contentrequest;
//use Illuminate\Support\Facades\Request;
use App\Models\Content;
use App\Models\Grade;
use App\Models\School;
use App\Models\Stream;
use App\Models\Trainerstream;
use App\Models\StudentNotification;
use App\Models\Students;
use App\Models\Studentscontent;
use App\Models\Trainer;
use App\Models\Trainerlavel;
use App\Models\TrainerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Validation\Validator;
use App\Helpers\QuizHelper;
use App\Models\QuizQuestions;
use App\Models\QuizAttempts;
use App\Models\QuizAnswers;

class ContentController extends Controller
{

    public function addStream(Request $request)
    {
        $request->validate([
            'stream_name' => 'required',
            'level'       => 'required|exists:grades,id',
        ]);
        $user_id = Session::get('user_id');
        $last_name = Session::get('last_name');
        $first_name = Session::get('first_name');
        $user_name = $first_name . ' ' . $last_name;
        $stream_name = $request->stream_name;

        $stream = new Stream();
        $stream->title = $stream_name;
        $stream->agegroup_id = $request->level;
        $stream->display_order_id = $stream->getNextDisplayOrderId($request->level);
        $stream->creator_id = Auth::id();
        $stream->creator = $user_name;
        $stream->save();

        $get_data = Stream::latest()->first()->toArray();

        return response()->json($get_data);
    }

    public function addAgeGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:grades,grade',
            'description' => 'required|unique:grades,description',
        ]);
        Grade::create([
            'grade' => $request->title,
            'description' => $request->description,
        ]);

        return '';
    }

    public function editStream(Request $request) {
        $data['title'] = $request->title;
        Stream::where("id",$request->id)->update($data);
    }

    public function deleteStream(Request $request) {
        $stream = Stream::find($request->stream);
        $contents = Studentscontent::where('stream_id', $stream->id)->orderBy('display_order_id')->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Studentscontent::where('stream_id', $stream->id)->delete();

        $contents = Content::where('stream_id', $stream->id)->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Content::where('stream_id', $stream->id)->delete();
        Stream::where("id",$request->stream)->delete();
    }

    public function destoryStream(Request $request)
    {
        $stream = Stream::find($request->stream);
        $contents = Studentscontent::where('stream_id', $stream->id)->orderBy('display_order_id')->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Studentscontent::where('stream_id', $stream->id)->delete();

        $contents = Content::where('stream_id', $stream->id)->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Content::where('stream_id', $stream->id)->delete();
        $stream->delete();
    }

    //students
    public function index()
    {
        $allGrade = Grade::all();

        return view('backend.content.students.index', compact('allGrade'));
    }
    
    public function stream(Grade $grade)
    {
        $streams = Stream::where('agegroup_id', $grade->id)->orderBy('display_order_id')->get();
        $contents = Studentscontent::whereIn('stream_id', $streams->pluck('id'))->orderBy('display_order_id')->get();

        return view('backend.content.students.streams', compact('grade', 'streams', 'contents'));
    }

    public function changeStudentStream(Request $request) {
        return Stream::where('agegroup_id', $request->agegroupId)->get();
    }

    public function getStreamSession(Request $request) {
        return Studentscontent::where(['ageGroup_id' => $request->agegroupId, 'stream_id' => $request->sessionId, 'is_publish' => 1])->get();
    }

    public function create()
    {
        $AgeGroups = Grade::all();

        return view('backend.content.students.create', compact('AgeGroups'));
    }

    public function store(Contentrequest $request)
    {
        $data = $request->only(
            'stream_id',
            'agegroup_id',
            'title',
            'video_url'
        );

        $worksheet_url = null;
        $worksheet_name = null;
        if ($request->hasFile('worksheets')) {
            $file = $request->file('worksheets');
            $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/content/');
            $file->move($path, $filename);
            $worksheet_url = $filename;
        }

        $video_url = null;
        if ($request->hasFile('video')) {
            $file2 = $request->file('video');
            $video = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/video/content/');
            $file2->move($path, $video);
            $video_url = $video;
        }

        $stream = Stream::with('agegroup')->find($request->stream_id);

        $is_publish = $request->contentPublish;

        $content_id = Studentscontent::create($data + ['display_order_id' => $request->stream_id, 'video' => $video_url, 'worksheet' => $worksheet_url, 'worksheet_name' => $worksheet_name, 'is_publish' => $is_publish])->id;
        
        // Store Quiz Questions START
        $questions = $request->questions;
        if(!empty($questions)) { 
            $options = $request->options;
            if(!empty($options)) $options = array_values($options);
            $answers = $request->answers;
            
            foreach($questions as $queKey => $question) {
                $quizQuestions = new QuizQuestions();
                $quizQuestions->content_id = $content_id;
                $quizQuestions->question = $question;
                $quizQuestions->content_type = 'session';
                foreach($options[$queKey] as $optKey => $option) {
                    $optKey++;
                    $optFld = 'option'.$optKey;
                    $quizQuestions->$optFld = $option;
                }    
                $quizQuestions->correct_option = $answers[$queKey];
                $quizQuestions->created_by = Session::get('user_id');
                $quizQuestions->created_at = now();
                $quizQuestions->save();
            }
        }
        // Store Quiz Questions END
        
        $students = Students::where('grade_id', $stream->agegroup->id)->get();

        $notifications = [];
        foreach ($students as $student) {
            $obj = [
                'student_id' => $student->id,
                'title' => 'New Content Added',
                'description' => $request->title . ' in ' . $stream->title,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $notifications[] = $obj;
        }

        StudentNotification::insert($notifications);

        return redirect()->route('backend.addcontent.streamcontentStudents', $request->agegroup_id)->with('success', 'Data Stored successfully.');
    }

    public function show(Studentscontent $studentscontents)
    {
        /* START - CHECK QUIZ IS PUBLISHED OR NOT */
        $getQuiz = QuizHelper::isQuizPublish($studentscontents->id);
        $isQuizPublish = $getQuiz->count();
        /* END - CHECK QUIZ IS PUBLISHED OR NOT */

        return view('backend.content.students.show', compact('studentscontents', 'isQuizPublish'));
    }

    public function quizResult($id)
    {
        $quizId = $id;
        
        $quizDetail = Studentscontent::find($quizId);
        $streams = Stream::find($quizDetail->stream_id);
        $levelName = Grade::find($streams->agegroup_id)->grade;
        $sessionName = $streams->title;
        $quizDetail->level = $levelName;
        $quizDetail->session = $sessionName;
        
        $schools = School::get();
        
        $quizResult = [];

        $quizAttempts = QuizHelper::getAllSubmittedQuiz($quizId);
        if($quizAttempts->count()) {

            foreach($quizAttempts as $attempt) {

                $quizId = $attempt->quiz_id;
                $studentId = $attempt->student_id;

                /* START - GET QUIZE SCORE */
                $scoreData = QuizHelper::getQuizScore($quizId, $studentId);
                /* END - GET QUIZE SCORE */

                $studDetail = Students::find($studentId);
                
                $studSchoolId = $studDetail->school_id;
                $studFilter = $schools->filter(function ($item) use($studSchoolId) {
                    return $item->id == $studSchoolId;
                })->values();

                $quizResult[$studentId] = ['name' => $studDetail->name, 'score' => $scoreData['score'], 'school' => (!empty($studFilter))? $studFilter[0]->school_name : ''];
                
            }
        }

        return view('backend.content.students.quiz_result', compact('schools', 'quizDetail', 'quizResult', 'quizId'));
    }

    public function quizResultFilter(Request $request) {
        $quizId = $request->quizId;
        $school_id = $request->school_id;
        $school_name = $request->school_name;

        $quizResult = [];

        $studDetail = Students::where('school_id', $school_id)->get();
        if($studDetail->count())
        {
            foreach($studDetail as $studData) {

                $studentId = $studData->id;

                $isQuizAttempt = QuizHelper::isQuizAttempt($quizId, $studentId);

                if($isQuizAttempt->count() && $isQuizAttempt[0]->is_submit) {

                    /* START - GET QUIZE SCORE */
                    $scoreData = QuizHelper::getQuizScore($quizId, $studentId);
                    /* END - GET QUIZE SCORE */

                    $studFilter = $studDetail->filter(function ($item) use($studentId) {
                        return $item->id == $studentId;
                    })->values();

                    $quizResult[$studentId] = ['name' => $studFilter[0]->name, 'score' => $scoreData['score'], 'school' => $school_name];
                }
            } 
        }

        return $quizResult;
    }

    public function edit(Studentscontent $studentscontents)
    {
        $AgeGroups = Grade::all();
        $quizQuestions = QuizQuestions::where('content_id', $studentscontents->id)->get();
       
        $queIds = $quizQuestions->pluck('id');
        $quizAttemptIds = [];
        if(!empty($queIds)) {
            $quizAttemptIds = QuizAnswers::whereIn("question_id", $queIds)->get()->pluck('question_id')->toArray();
        }
        
        return view('backend.content.students.edit', compact('studentscontents', 'AgeGroups', 'quizQuestions', 'quizAttemptIds'));
    }

    public function update(Studentscontent $studentscontents, Contentrequest $request)
    {
        $data = $request->only(
            'stream_id',
            'agegroup_id',
            'title',
            'video_url'
        );

        $worksheet_url = null;
        if ($request->hasFile('worksheets')) {
            if ($studentscontents->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $studentscontents->worksheet);
            }

            $file = $request->file('worksheets');
            $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/content/');
            $file->move($path, $filename);
            $worksheet_url = $filename;
        } else {
            if (!empty($studentscontents->worksheet) && $studentscontents->worksheet != 'no worksheet') {
                $worksheet_url = $studentscontents->worksheet;
                $worksheet_name = $studentscontents->worksheet_name;
            } else {
                $worksheet_url = null;
                $worksheet_name = null;
            }
        }

        $video_url = null;
        if ($request->hasFile('video')) {
            if ($studentscontents->video) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($studentscontents->video);
            }

            $file2 = $request->file('video');
            $video = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/video/content/');
            $file2->move($path, $video);
            $video_url = $video;
        } else { 
            if (!empty($request->pre_video) && $request->pre_video != 'no video') {
                $video_url = $request->pre_video;
            } else {
                $video_url = null;
            }
        }

        $is_publish = $request->contentPublish;
        
        $studentscontents->update($data + ['video' => $video_url, 'worksheet' => $worksheet_url,  'worksheet_name' => $worksheet_name, 'is_publish' => $is_publish]);

        // Update Quiz Questions START
        $questions = $request->questions;
        if(!empty($questions)) {
            $options = $request->options;
            if(!empty($options)) $options = array_values($options);
            $answers = $request->answers;
            $questionIds = $request->questionIds;
            if(!empty($questionIds)) {
                $idsArr = implode(",", $questionIds);
                $idsArr = explode(",", $idsArr);
                $questionRes = QuizQuestions::whereIn('id', $idsArr)->get()->pluck('id')->toArray();
            }
            foreach($questions as $queKey => $question) {
                $quizQuestions['content_id'] = $studentscontents->id;
                $quizQuestions['question'] = $question;
                $quizQuestions['content_type'] = 'session';
                foreach($options[$queKey] as $optKey => $option) {
                    $optKey++;
                    $optFld = 'option'.$optKey;
                    $quizQuestions[$optFld] = $option;
                }    
                $quizQuestions['correct_option'] = $answers[$queKey];
                if(!empty($questionRes) && array_key_exists($queKey, $questionIds) && in_array($questionIds[$queKey], $questionRes)) {
                    $quizQuestions['updated_by'] = Session::get('user_id');
                    QuizQuestions::where('id', $questionIds[$queKey])->update($quizQuestions);
                } else {
                    $quizQuestions['created_by'] = Session::get('user_id');
                    $quizQuestions['created_at'] = now();
                    QuizQuestions::insert($quizQuestions);
                }
            }
        }
        // Update Quiz Questions END

        $stream = Stream::with('agegroup')->find($request->stream_id);
        $students = Students::where('grade_id', $stream->agegroup->id)->get();
        $notifications = [];
        foreach ($students as $student) {
            $obj = [
                'student_id' => $student->id,
                'title' => 'Content updated',
                'description' => $request->title . ' in ' . $stream->title,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $notifications[] = $obj;
        }

        StudentNotification::insert($notifications);
        
        return redirect()->route('backend.addcontent.streamcontentStudents', $studentscontents->ageGroup_id)->with('success', 'Data Updated successfully.');
    }

    public function destory(Studentscontent $studentscontents)
    {
        if ($studentscontents->video) {
            $destinationPath = public_path('/files/content/');
            FacadesFile::delete($studentscontents->video);
        }
        $studentscontents->delete();

        return redirect()->route('backend.addcontent.contentListStudents')->with('success', 'Data Deleted successfully.');
    }

    public function updateOrderStudentstream(Request $request)
    {
        if (!empty($request->studentstreamids)) {
            $studentstreamids = json_decode($request->studentstreamids);
            foreach ($studentstreamids as $index => $studentStreamId) {
                Stream::where("id",$studentStreamId)->update(['display_order_id' => ($index+1)]);
            }
        }
    }

    public function updateOrderStudentcontent(Request $request)
    {
        if (!empty($request->studentcontentids)) {
            $studentcontentids = json_decode($request->studentcontentids);
            foreach ($studentcontentids as $index => $studentContentId) {
                Studentscontent::where("id",$studentContentId)->update(['display_order_id' => ($index+1)]);
            }
        }
    }

    public function deleteWorkSheet(Request $request)
    {
        $contentId = $request->contentId;
        $content = Studentscontent::find($contentId);
        if($content->worksheet) {
            $destinationPath = public_path('/files/content/');
            FacadesFile::delete($destinationPath . $content->worksheet);
            Studentscontent::where("id",$contentId)->update(['worksheet' => null, 'worksheet_name' => null]);
            return true;
        }
        return false;
    }

    public function deleteContentVideo(Request $request)
    {
        $contentId = $request->contentId;
        $content = Studentscontent::find($contentId);
        if($content->video) {
            $destinationPath = public_path('/video/content/');
            FacadesFile::delete($destinationPath . $content->video);
            Studentscontent::where("id",$contentId)->update(['video' => null]);
            return true;
        }
        return false;
    }

    public function deleteQuestion(Request $request)
    {
        $questionId = $request->questionId;
        $question = QuizQuestions::find($questionId);
        $quizId = $question->content_id;
        
        if($question) {

            QuizQuestions::where("id",$questionId)->delete();
            QuizAnswers::where("question_id",$questionId)->delete();
            
            $totalQue = QuizQuestions::where('content_id', $quizId)->get()->toArray();
            if(empty($totalQue))
                QuizAttempts::where("quiz_id",$quizId)->delete();
                Session::flash("success", "Question deleted successfully");

            return true;
        }

        return false;
    }

    public function disableQuestion(Request $request)
    {
        $questionId = $request->questionId;
        if($questionId) {
            QuizQuestions::where("id",$questionId)->update(['isDisabled' => 1]);
            Session::flash("success", "Question disabled successfully");
            return true;
        }
        return false;
    }

    public function enableQuestion(Request $request)
    {
        $questionId = $request->questionId;
        if($questionId) {
            QuizQuestions::where("id",$questionId)->update(['isDisabled' => 0]);
            Session::flash("success", "Question enabled successfully");
            return true;
        }
        return false;
    }
}
