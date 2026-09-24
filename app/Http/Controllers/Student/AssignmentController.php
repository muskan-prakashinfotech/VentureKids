<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentComment;
use App\Models\AssignmentDetails;
use App\Models\StudentCommunications;
use App\Models\Students;
use App\Models\Submission;
use App\Models\AssingmentFiles;
use App\Models\TrainerAllocation;
use App\Models\Grade;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Helpers\StudentRewardPointsHelper;
use Illuminate\Support\Facades\File;
use App\Models\Stream;

class AssignmentController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        //$this->middleware('permission:student_edit');
        //$this->middleware('role:admin|writer')->only('testmiddleware');

        $this->module_name = 'users';
    }

    public function assignment()
    {
        $student_id = Session::get('student_id');
        $category = 'self_learning'; // default category

        $student = Students::select('school_id', 'grade_id')->with(['school' => function($query) {
            $query->select(['id','course_end_date']);
        }])->find($student_id);
        
        $school_id = 0;
        $grade_id = 0;
        $currentGradeId = 0;
        $uc_thinkpreneur = config('global.level_unique_code.thinkpreneur');
        $gradeData = Grade::select('id')->where('unique_code', $uc_thinkpreneur)->first();
        if($student) {
            $school_id = $student->school_id;
            $grade_id = $student->grade_id;
            if(!empty($grade_id)) {
                $currentGradeId = explode(",", $grade_id);
                if(in_array($gradeData->id, $currentGradeId)) {
                    $currentGradeId = $gradeData->id;
                } else {
                    $currentGradeId = $currentGradeId[0];
                }
            }
        }

        $allowedGradeIds = !empty($student->grade_id) ? explode(',', $student->grade_id) : [];
        $selectedGradeId = (int) request('gradeId', $currentGradeId);
        if (!in_array($selectedGradeId, $allowedGradeIds)) {
            $selectedGradeId = $currentGradeId;
        }

        $selectedCategory = request('category', $category);
        if (!in_array($selectedCategory, ['facilitated', 'self_learning'], true)) {
            $selectedCategory = $category;
        }

        $selectedStreamId = request('streamId');
        $selectedStreamId = !empty($selectedStreamId) ? (int) $selectedStreamId : null;

        /* START - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */
        $hasAccess = false;
        if($student && !empty($student->grade_id) && in_array($currentGradeId, explode(',',$student->grade_id))) {
            $hasAccess = true;
            if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                $hasAccess = false;
            }
        } 
        /* END - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */
         
        // Get streams for the current grade
        $streams = [];
        if($selectedGradeId) {
            $streams = Stream::select(['id','title'])->where('agegroup_id', $selectedGradeId)->get();
        }

        if($hasAccess) {
            $pastWorksheets = Submission::query()->with('assignment')->whereHas('assignment', function($query) use ($selectedGradeId, $selectedCategory, $selectedStreamId) {
                $query->where('grade_id', $selectedGradeId)
                    ->where('category', $selectedCategory)
                    ->where('is_active', '!=', 2);
                if (!empty($selectedStreamId)) {
                    $query->where('stream_id', $selectedStreamId);
                }
            })->where('student_id', $student_id)->orderBy('assignment_id', 'ASC')->get();

            $pastWorksheetsIDList = [];
            if ($pastWorksheets->count()) {
                $pastWorksheetsIDList = $pastWorksheets->pluck('assignment_id')->toArray();
            }

            //  Exclude all the assignments in the student section for the School of Entrepreneurship if they have been created for All Schools
            if($school_id == 3) {
                $worksheets = StudentCommunications::select('id','title','icon_name', 'is_active')->where('grade_id', $selectedGradeId)->where('is_active', '!=', 2)->where(function($query) use($school_id) {
                    $query->where('school_id', $school_id);
                })->whereNotIn('id', $pastWorksheetsIDList)->where('category', $selectedCategory)->when(!empty($selectedStreamId), function($query) use ($selectedStreamId) {
                    $query->where('stream_id', $selectedStreamId);
                })->orderByRaw("display_order_id ASC, id ASC")->get();
            } else {
                $worksheets = StudentCommunications::select('id','title','icon_name', 'is_active')->where('grade_id', $selectedGradeId)->where('is_active', '!=', 2)->where(function($query) use($school_id) {
                    $query->where('school_id', $school_id)->orWhere('school_id', '=', 0);
                })->whereNotIn('id', $pastWorksheetsIDList)->where('category', $selectedCategory)->when(!empty($selectedStreamId), function($query) use ($selectedStreamId) {
                    $query->where('stream_id', $selectedStreamId);
                })->orderByRaw("display_order_id ASC, id ASC")->get();
            }
        } else {
            $worksheets = $pastWorksheets = collect();
        }

        
        $levels = Grade::where('is_publish', 1)->orderBy('display_order_id','asc')->get(); 
        $filteredLevels = [];
        $studentGradeIds = !empty($student->grade_id) ? explode(',', $student->grade_id) : [];
        $filtered_collection = $levels->filter(function ($item) use (&$filteredLevels,$studentGradeIds) {
             $levelData = $item->toArray();
             $levelData['has_access'] = in_array($item->id, $studentGradeIds);
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $levelData;        
            } else {
                $filteredLevels['add-ons'][$item->id] = $levelData;
            }
        })->values();
        
        return view('student.assignment.assignment_view', compact('worksheets', 'pastWorksheets', 'filteredLevels', 'currentGradeId', 'selectedGradeId', 'streams'));
    }

    public function save_read_assignment(Request $request)
    {
        $read = $request->read;
        $student_id = $request->student_id;
        $assignment_id = $request->assignment_id;

        $check_assignment = AssignmentDetails::where('student_id', $student_id)->where('assignment_id', $assignment_id)->first();
        if (!empty($check_assignment)) {
            $check_assignment->read_status = $read;
            $check_assignment->save();
        } else {
            $ass_detail = new AssignmentDetails;
            $ass_detail->student_id = $student_id;
            $ass_detail->assignment_id = $assignment_id;
            $ass_detail->read_status = $read;
            $ass_detail->save();
        }
        echo 1;
    }

    public function save_comment_assignment(Request $request)
    {
        $comment = $request->comment;
        $student_id = $request->student_id;
        $assignment_id = $request->assignment_id;

        $check_assignment = AssignmentDetails::where('student_id', $student_id)->where('assignment_id', $assignment_id)->first();
        if (!empty($check_assignment)) {
            $check_assignment->comment_status = $comment;
            $check_assignment->save();
        } else {
            $ass_detail = new AssignmentDetails;
            $ass_detail->student_id = $student_id;
            $ass_detail->assignment_id = $assignment_id;
            $ass_detail->comment_status = $comment;
            $ass_detail->save();
        }

        echo 1;
    }

    public function getComment(Request $request)
    {
        $student = Students::where('user_id', Session::get('user_id'))->first();
        if(!empty($student)) {
            $student = $student->toArray();
        }
        $data['student'] = $student;
        $trainer = TrainerAllocation::with('trainer')->where('school_id', $data['student']['school_id'])->first();
        if(!empty($trainer)) {
            $trainer = $trainer->toArray();
            $data['trainer'] = $trainer['trainer'];
        } else {
            $data['trainer'] = '';
        }
        $assignment_id = $request->assignment_id;

        $data['getMessage'] = AssignmentComment::with(['getAssignment'])->where('assignment_id', $assignment_id)->where('reciever_id', $data['student']['id'])->orWhere('sender_id', $data['student']['id'])->get()->toArray();

        echo json_encode($data);
    }

    public function addComment(Request $request)
    {
        $assignment_id = $request->assignment_id;
        $trainer_id = $request->trainer_id;
        $student_id = $request->student_id;
        $comment = $request->comment;

        $assignment_comment = new AssignmentComment;
        $assignment_comment->assignment_id = $assignment_id;
        $assignment_comment->reciever_id = $trainer_id;
        $assignment_comment->sender_id = $student_id;
        $assignment_comment->message = $comment;
        $assignment_comment->save();

        $comment_id = DB::getPdo()->lastInsertId();
        $comment = AssignmentComment::with('student')->where('id', $comment_id)->first()->toArray();

        echo json_encode($comment);
    }

    public function show(StudentCommunications $student_communications)
    {
        $student_id = Session::get('student_id');
        $gradeId = $student_communications->grade_id;
        $school_id = Session::get('student_school_id');

        $student = Students::select('id', 'school_id', 'grade_id')->with(['school' => function($query) {
            $query->select(['id','course_end_date']);
        }])->find($student_id);

        /* START - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */
        $hasAccess = false;
        if($student && !empty($student->grade_id) && in_array($gradeId, explode(',',$student->grade_id))) {
            $hasAccess = true;
            if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                $hasAccess = false;
            }
            if(!in_array($student_communications->school_id, [0, $school_id])) {    
                $hasAccess = false;
            }
        } 
        /* END - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */

        if(!$hasAccess || (int) $student_communications->is_active !== 1) {
            return redirect()->route('student.assignment', request()->query())
                ->with('error', 'This assignment is not available.');
        }

        $submission = Submission::where(['student_id' => $student->id, 'assignment_id' => $student_communications->id])->first();
        $assignmentsfiles = AssingmentFiles::where(['assignment_id' => $student_communications->id])->first();
        $assignmentids = Submission::select('assignment_id')->where('student_id', $student['id'])->get();
        $complete = $assignmentids->pluck('assignment_id')->toArray();
        $assignments = StudentCommunications::with(['assignmentfiles', 'assinmentdetails', 'trainer'])->where('grade_id', $student['grade_id'])->where('school_id', $student['school_id'])->whereNotIn('id', $complete)->get();
        $scormPath = null;
        $category  = $student_communications->category ?? 'facilitated';
         
        if ($category === 'self_learning' && !empty($student_communications->scorm_file)) {
            $scormFileName = $student_communications->scorm_file;
            $scormFilePath = public_path('/scorm-files/assignment/');

            if (File::exists($scormFilePath . $scormFileName)) {
                $scormFileNameWithoutExtension = pathinfo($scormFileName, PATHINFO_FILENAME);
                $extractPath = $scormFilePath . $scormFileNameWithoutExtension;

                if (!File::exists($extractPath)) {
                    $zip = new \ZipArchive();
                    $x   = $zip->open($scormFilePath . $scormFileName);
                    if ($x === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();
                    }
                }
                $student_communications->scorm_file=$scormFileNameWithoutExtension;
            } else {
                $student_communications->scorm_file = '';
            }
        }

        return view('student.assignment.show', compact('student_communications', 'submission', 'assignments','assignmentsfiles',  'category'));
    }

    public function submit(StudentCommunications $student_communications, Request $request)
    {
        $request->validate([
            // 'link' => 'required_without:file|url',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $student_id = Session::get('student_id');
        $assignment_id = $student_communications->id;

        $alreadySubmitted = Submission::where(['assignment_id' => $assignment_id, 'student_id' => $student_id])->first();
        
        $file = $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = 'files/submissions/';
            $image_name = uniqid(Str::random(12)) . '.' . $request->file->getClientOriginalExtension();

            if (tenant() && tenant()->tenant_id) {
                $request->file->storeAs(tenant()->tenant_id.'/student/submissions' , $image_name, 'tenant_uploads');
                $filePath = tenant()->tenant_id.'/student/submissions/';
            } else {
                $request->file->move('files/submissions', $image_name);
            }

            $file = $image_name;
        }

        if ( $alreadySubmitted) {
            // delete old file if exists
            if ( $alreadySubmitted->file && file_exists(public_path( $alreadySubmitted->file))) {
                unlink(public_path( $alreadySubmitted->file));
            }

            // update record
            $alreadySubmitted->update([
                'link' => $request->link,
                'file' => $filePath . $file,
                'feedback' => null,   // reset feedback
                'comment'  => null,
            ]);

         } else {
    
            $student_communications->submissions()->create([
                'student_id' => $student_id,
                'link' => $request->link,
                'file' => $filePath . $file,
            ]);
    
        }
        
        return redirect(route('student.assignment'))->with(['success' => 'Thank you for your submission.', 'confetti_visible' => 1]);
        // return redirect()->back()->with('success', 'Thank you for your submission.');
    }

    public function getStudentWorksheets(Request $request) {
        $gradeId = (int)$request->gradeId;
        $streamId = (int)$request->streamId ?? null;
        $category = $request->category ?? 'facilitated';
        $student_id = Session::get('student_id');
        
        $student = Students::select('school_id', 'grade_id')->with(['school' => function($query) {
            $query->select(['id','course_end_date']);
        }])->find($student_id);
        
        $school_id = 0;
        if($student) {
            $school_id = $student->school_id;
        }

        if($gradeId) {

             /* START - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */
            $hasAccess = false;
            if($student && !empty($student->grade_id) && in_array($gradeId, explode(',',$student->grade_id))) {
                $hasAccess = true;
                if(!empty($student->school->course_end_date) && $student->school->course_end_date < date('Y-m-d')) {
                    $hasAccess = false;
                }
            } 
            /* END - CHECK STUDENT HAVE ACCESS OF LEVEL OR NOT */

            if($hasAccess) {
                $pastWorksheets = Submission::query() ->with('assignment')->whereHas('assignment', function($query) use ($gradeId,$category,$streamId) {
                    $query->where('grade_id', $gradeId)
                    ->where('category', $category)
                    ->where('is_active', '!=', 2);
                    if (!empty($streamId)) {
                        $query->where('stream_id', $streamId);
                    }
                })->where('student_id', $student_id)->orderBy('assignment_id','ASC')->get();
                
                $pastWorksheetsIDList = [];
                if($pastWorksheets->count()) {
                    $pastWorksheetsIDList = $pastWorksheets->pluck('assignment_id')->toArray();
                }

                //  Exclude all the assignments in the student section for the School of Entrepreneurship if they have been created for All Schools
                if($school_id == 3) {
                    $worksheets = StudentCommunications::select('id','title', 'icon_name', 'is_active')->where('grade_id', $gradeId)->where('is_active', '!=', 2)->where(function($query) use($school_id) {
                        $query->where('school_id', $school_id);
                            })->where(function($query) use($streamId) {
                        if (!empty($streamId)) {
                            $query->where('stream_id', $streamId);
                        }
                    })->whereNotIn('id', $pastWorksheetsIDList)->where('category', $category)->orderByRaw("display_order_id ASC, id ASC")->get();
                } else {
                    $worksheets = StudentCommunications::select('id','title', 'icon_name', 'is_active')->where('grade_id', $gradeId)->where('is_active', '!=', 2)->where(function($query) use($school_id) {
                        $query->where('school_id', $school_id)->orWhere('school_id', '=', 0);
                            })->where(function($query) use($streamId) {
                        if (!empty($streamId)) {
                            $query->where('stream_id', $streamId);
                        }
                    })->whereNotIn('id', $pastWorksheetsIDList)->where('category', $category)->orderByRaw("display_order_id ASC, id ASC")->get();
                }
                $streams = Stream::select(['id','title'])->where('agegroup_id', $gradeId)->get();
            } else {
                $worksheets = $pastWorksheets = collect();
                $streams = collect();
            }
            
            return response()->json(['success' => [
                'pastWorksheets' => $pastWorksheets,
                'worksheets' => $worksheets,
                'streams' => $streams
            ]]); 
        }
        return response()->json(['error' => 'Level Not Found!']); 
    }

    public function resubmit(StudentCommunications $student_communications)
    {
        $student_id = Session::get('student_id');

        $submission = Submission::where([
            'assignment_id' => $student_communications->id,
            'student_id'    => $student_id
        ])->first();

        $assignmentsfiles = AssingmentFiles::where('assignment_id', $student_communications->id)->first();
    
        $category = $student_communications->category; 

        return view('student.assignment.show', compact('student_communications', 'submission', 'assignmentsfiles', 'category'))
        ->with('resubmitMode', true);
    }

    public function storeScormAssignment(Request $request)
    {
        $studentId = Session::get('student_id');
        $scorm_score = (int) $request->scorm_score;

        $assignment_id = (int) $request->assignment_id;
        $assignment = StudentCommunications::find($assignment_id);

        if (!$assignment) {
            return response()->json(['error' => 'Assignment not found'], 404);
        }

        $submission = Submission::create([
            'student_id' => $studentId,
            'assignment_id' => $assignment_id,
        ]);

        if ($scorm_score === 100) {
        $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($studentId,'assignment_scorm_point',$assignment_id);

            if (!$alreadyRewarded->count()) {
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id' => $studentId,
                    'reward_type' => 'assignment_scorm_point',
                    'item_id' => $assignment_id,
                    'reward_points' => 5,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your submission.', 
            'submission_id' => $submission->id
        ]);

    }


}
