<?php

namespace App\Http\Controllers\trainer;

use App\Http\Controllers\Controller;
use App\Models\AssignmentComment;
use App\Models\AssignmentDetails;
use App\Models\AssingmentFiles;
use App\Models\Grade;
use App\Models\School;
use App\Models\StudentCommunications;
use App\Models\StudentNotification;
use App\Models\Students;
use App\Models\Submission;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use App\Models\TrainerAllocationNew;
use App\Models\Stream;
use App\Models\Studentscontent;
use App\Helpers\StudentRewardPointsHelper;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        $schools = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getSchool' => function($query) {
            $query->select('id','school_name');
        }])->where('trainer_id', $trainer_id)->groupBy('school_id')->get();
        
        $grades = Grade::all();
        $filteredLevels = [];
        $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();        
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();
        
        $trainer_allocation = TrainerAllocationNew::select('id','school_batch_id')->where('trainer_id', $trainer_id)->get();
        $student_id_list = '';
        if($trainer_allocation->count()) {
            $school_batch_id_list = $trainer_allocation->pluck('school_batch_id')->toArray();
            $school_batch_id_list =  implode(",", $school_batch_id_list);
            $student_id_list = Students::select('id')->whereIn('school_batch_id', explode(",", $school_batch_id_list))->pluck('id')->toArray();
            if(!empty($student_id_list)) {
                $student_id_list =  implode(",", $student_id_list);
            }
        }

        $assigments = collect();

        if($schools->count()) {

            $first_school_id = $schools->first()->school_id;

            $assigments = StudentCommunications::
                when(request('school_id') == null, function($query) use ($trainer_id, $first_school_id) {
                    $query->where(function($query) use ($trainer_id, $first_school_id) {
                        $query->where('trainer_id', $trainer_id)->where('school_id', $first_school_id);
                    })->orWhere('school_id', '=', 0);
                })
                ->when(request('school_id'), function ($query) use ($trainer_id, $first_school_id) {
                    $query->where(function($query) use ($trainer_id) {
                        $query->where(function($query) use ($trainer_id) {
                            $query->where('trainer_id', $trainer_id)->where('school_id', request('school_id'));
                        })
                        ->orWhere('school_id', '=', 0);
                    });
                })
                ->when(request('grade_id'), function ($query) {
                    $query->where('grade_id', request('grade_id'));
                })
                ->where('category', '!=', 'self_learning')
                ->with(['school' => function($query) {
                        $query->select('id', 'school_name');
                    }, 'level'  => function($query) {
                        $query->select('id', 'grade');
                    }, 'submissions'  => function($query) use ($student_id_list) {
                        $query->select('id', 'assignment_id', 'feedback', 'comment')->whereIn('student_id',explode(",", $student_id_list));
                    }
                ])
                //  add this to get latest submission created_at per assignment
                ->withMax(['submissions as latest_submission_at' => function ($query) use ($student_id_list) {
                    $query->whereIn('student_id', explode(",", $student_id_list));
                }], 'updated_at')
                ->groupBy('id')
                ->orderByDesc('latest_submission_at')
                ->paginate();
        }

        return view('trainer.assignment.index', compact('assigments', 'schools', 'filteredLevels'));
    }

    public function show(StudentCommunications $student_communications)
    {
        $trainer_id = Session::get('trainer_id');

        $submissions = collect();

        $trainer_allocation = TrainerAllocationNew::select('id','school_batch_id')->where('trainer_id', $trainer_id)->get();
        if($trainer_allocation->count()) {
            $school_batch_id_list = $trainer_allocation->pluck('school_batch_id')->toArray();
            $school_batch_id_list =  implode(",", $school_batch_id_list);
            $student_id_list = Students::select('id')->whereIn('school_batch_id', explode(",", $school_batch_id_list))->pluck('id')->toArray();
            if(!empty($student_id_list)) {
                $student_id_list =  implode(",", $student_id_list);
                $submissions = Submission::where('assignment_id', $student_communications->id)->whereIn('student_id', explode(",", $student_id_list))->with(['student'])->paginate();
            }
        }
        
        return view('trainer.assignment.show', compact('submissions', 'student_communications'));
    }

    public function submission(StudentCommunications $student_communications, Submission $submission)
    {
        $submission->load('student');
        
        if($submission->file) {
            $tenant_id = School::select('tenant_id')->find($submission->student->school_id)->tenant_id;
            if(!empty($tenant_id)) { 
                $submission->file = 'tenants/'. $submission->file;
            }
        }
        
        return view('trainer.assignment.submission', compact('submission', 'student_communications'));
    }

    public function review(StudentCommunications $student_communications, Submission $submission, Request $request)
    {
        $request->validate([
            'feedback' => 'required',
            'comment' => 'required',
        ]);
        // dd($request->all());
        $submission->feedback = $request->feedback;
        $submission->comment = $request->comment;
        // dd($submission);
        $submission->save();

        // Check if reward already exists for this assignment submission, if not then store reward points
        $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($submission->student_id,'assignment_submission',$submission->assignment_id);

        if (!$alreadyRewarded->count()) {
            StudentRewardPointsHelper::storeRewardPoints([
                'student_id'    => $submission->student_id,
                'reward_type'   => 'assignment_submission',
                'item_id'       => $submission->assignment_id,
                'reward_points' => 2,
            ]);
        }

        return back()->with('success', 'Your review saved successfully.');
    }

    public function createAssignment()
    {
        $trainer_id = Session::get('trainer_id');
        $school_list = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getSchool' => function($query) {
            $query->select('id','school_name');
        }])->where('trainer_id', $trainer_id)->groupBy('school_id')->get()->toArray();
        
        $grades = Grade::all();
        $filteredLevels = [];
        $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();        
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        return view('trainer.assignment.create_assignment', [
            'school_list' => $school_list,
            'filteredLevels' => $filteredLevels,
        ]);
    }

    public function store_assignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_title' => 'required',
            'school_id' => 'required',
            'grade_id' => 'required',
            'upload_icon' => 'required|image|mimes:jpeg,jpg,png|max:1024',
        ]);

        $trainer_id = Session::get('trainer_id');

        $assignment = new StudentCommunications();
        $assignment->title = $request->assignment_title;
        $assignment->school_id = $request->school_id;
        $assignment->grade_id = $request->grade_id;
        $assignment->trainer_id = $trainer_id;
        $assignment->comment = $request->comment;

        $upload_icon = $request->file('upload_icon');
        if(!empty($upload_icon)) {
            $iconName = uniqid() . '.' . $upload_icon->getClientOriginalExtension();
            $path = public_path('/image/assignment');
            $upload_icon->move($path, $iconName);
            $assignment->icon_name = $iconName;
        }
        
        $display_order_id = (int) $request->display_order;
        if(empty($display_order_id)) {
            $assignment->display_order_id = $assignment->getNextDisplayOrderId();
        } else {
            $assignment->display_order_id = $display_order_id;
        }

        $assignment->save();

        $multiAssignment = $request->multi_assignment;
        if ($multiAssignment) {
            for ($i = 0; $i < count($multiAssignment); $i++) {
                $assignmentFile = new AssingmentFiles();
                $assignmentFile->assignment_id = $assignment->id;
                $assignmentFile->attachment = $multiAssignment[$i];
                $assignmentFile->save();
            }
        }

        /*
        $students = Students::where('grade_id', $request->grade_id)->where('school_id', $request->school_id)->get();

        $notifications = [];
        foreach ($students as $student) {
            $obj = [
                'student_id'    => $student->id,
                'title'         => 'New Assigment uploaded',
                'description'   => $request->assignment_title . ' by ' . $trainer['trainer_name'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
            $notifications[] = $obj;
        }

        StudentNotification::insert($notifications);
        */

        return redirect()->route('trainer.assigment.index')->with('message', 'Assignment Created Successfully!');
    }

    public function viewAssignment()
    {
        $trainer = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
        $get_schools = TrainerAllocation::where('trainer_id', $trainer['id'])->groupBy('school_id')->get()->toArray();

        $school_ids = [];
        if (!empty($get_schools)) {
            foreach ($get_schools as $school) {
                $school_ids[] = $school['school_id'];
            }
        }
        $assignment = StudentCommunications::whereIn('school_id', $school_ids)->get()->toArray();
        $students = Students::whereIn('school_id', $school_ids)->get()->toArray();

        return view('trainer.assignment.view_assignment', compact('assignment', 'students'));
    }

    public function createComment(Request $request)
    {
        $tariner = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
        $student_id = $request->student_id;
        $message = $request->message;
        $assignment_id = $request->assignment_id;

        $assignment_comment = new AssignmentComment;
        $assignment_comment->assignment_id = $assignment_id;
        $assignment_comment->reciever_id = $student_id;
        $assignment_comment->sender_id = $tariner['id'];
        $assignment_comment->message = $message;
        $assignment_comment->save();

        $comment_id = DB::getPdo()->lastInsertId();
        $comment = AssignmentComment::with('getTrainer')->where('id', $comment_id)->first()->toArray();

        echo json_encode($comment);
    }

    public function getStudentComment(Request $request)
    {
        $student_id = $request->student_id;
        $assignment_id = $request->assignment_id;

        $getComments = DB::select('select * from assignment_comment as ac inner join assignments as a on ac.assignment_id=a.id where ac.assignment_id=' . $assignment_id . ' AND (ac.reciever_id=' . $student_id . ' OR ac.sender_id=' . $student_id . ');');

        $data['getComment'] = json_decode(json_encode($getComments), true);

        $assignmentCommnet = AssignmentDetails::where(['student_id' => $student_id, 'assignment_id' => $assignment_id])->get()->toArray();
        if (!empty($assignmentCommnet)) {
            $data['assignmentCommnet'] = $assignmentCommnet[0];
        } else {
            $data['message'] = 'Not Found!!';
        }

        $data['student'] = Students::where('id', $student_id)->first()->toArray();
        $data['tariner'] = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();

        echo json_encode($data);
    }

    public function completeAssignment(Request $request)
    {
        $student_id = $request->student_id;
        $assignment_id = $request->assignment_id;

        $assignmentDetails = AssignmentDetails::where(['student_id' => $student_id, 'assignment_id' => $assignment_id])->first();
        $assignmentDetails->comment_status = $request->comment;
        $assignmentDetails->save();

        $result = 'success';
        echo json_encode($result);
    }

    public function assignmentView()
    {
        $trainer = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
        $get_schools = TrainerAllocation::where('trainer_id', $trainer['id'])->groupBy('school_id')->get()->toArray();
        if (!empty($get_schools)) {
            foreach ($get_schools as $school) {
                $school_ids[] = $school['school_id'];
            }
        }

        $school_name = School::whereIn('id', $school_ids)->get();

        $trainer = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
        $schools = TrainerAllocation::with('getSchool')->where('trainer_id', $trainer['id'])->get()->toArray();
        $grades = Grade::get()->toArray();
        $grade = Grade::all()->toArray();

        return view('trainer.assignment.view_assignment', compact('school_name', 'grades'))->with('schools', $schools)->with('grades', $grades);
    }

    public function studentLevelList(Request $request)
    {
        $school_id = $request->school_id;

        $grades = Grade::get();
        $option = '<label for="schoolname">Select Level</label><select class="form-control" name="grade_id" id="grade" onchange = "gradeId(this.value);"><option>Select Level</option>';
        foreach ($grades as $val) {
            $option .= '<option value="' . $val->id . '">' . $val->grade . '</option>';
        }
        $option .= '</select>';
        echo json_encode($option);
    }

    public function getStudentList(Request $request)
    {
        $school_id = $request->school_id;
        $grade = $request->grade;
        if (($school_id != '') && ($grade != '')) {
            $data = Students::with([
                'school' => function ($query) {
                    $query->select('id', 'school_name');
                },
                'stdUser', 'assignmentfiles', 'projectfiles', 'getproject',
            ])
            ->where('school_id', $school_id)
            ->where('grade_id', $grade)
            ->get();

            $option = '<label for="schoolname">Select Student</label><select class="form-control" name="student_id" id="student" onchange = "studentAssignment(this.value);" ><option>Select Student</option>';
            foreach ($data as $val) {
                $option .= '<option value="' . $val->id . '">' . $val->name . '</option>';
            }
            $option .= '</select>';
            echo json_encode($option);
        }
    }

    public function studentAssignment(Request $request)
    {
        $student_id = $request->student;
        $finish_assignment = Submission::where('student_id', $student_id)->with(['assignment.trainer'])->get()->toArray();
        $data = [];
        for ($i = 0; $i < count($finish_assignment); $i++) {
            $data[] = $finish_assignment[$i]['assignment_id'];
        }
        $assignment_files = AssingmentFiles::join('assignments', 'assignmentsfiles.assignment_id', '=', 'assignments.id')
               ->wherein('assignment_id', $data)
                ->select('assignmentsfiles.*', 'assignments.title')
                ->get();
        $addclass = '';
        if (!empty($assignment_files)) {
            $addclass .= '<div class="card"><table class="table table-striped table-bordered">
                    <tr>
                      <th>Title</th>
                      <th></th>
                    </tr>';
            foreach ($assignment_files as $files) {
                $addclass .= '<tr>
                      <td><a target="_blank" href="' . url('trainer/view/assignment/complete/' . $student_id . '/' . $files->assignment_id) . '">' . $files->title . '</a></td>
                      <td><span class="btn btn-sm btn-warning"><i class="fas fa-file-pdf"></i></span></td>
                    </tr>
                  ';
            }
            $addclass .= '</table></div>';
        }

        echo json_encode($addclass);
    }

    public function completeStudent(Request $request, $id, $assignId)
    {
        $student_id = $request->student;
        $finish_assignment = Submission::where('student_id', $student_id)->with(['assignment.trainer'])->first();

        $assignment_files = AssingmentFiles::join('assignments', 'assignmentsfiles.assignment_id', '=', 'assignments.id')
               ->where('assignment_id', $assignId)
                ->select('assignmentsfiles.*', 'assignments.title')
                ->get();

        return view('trainer.assignment.view_submit_assignment', compact('assignment_files'));
    }

    public function manualSubmission($assignmentId) {
        $trainer_id = Session::get('trainer_id');
        $assigment_title = StudentCommunications::select('title')->find($assignmentId);
        $assigment_title = $assigment_title->title;
        $school_list = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getSchool' => function($query) {
            $query->select('id','school_name');
        }])->where('trainer_id', $trainer_id)->groupBy('school_id')->get()->toArray();
        
        return view('trainer.assignment.manual_assignment', compact('school_list', 'assignmentId', 'assigment_title'));
    }

    public function manual_student_list_datatable(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])->where('trainer_id', $trainer_id)->get();
        
        $batch_ids = $batch_list->pluck('school_batch_id');
                
        if (($request->school_id != '')) {
            $data = Students::select(['id','user_id'])->with([
                'stdUser' => function($query) {
                    $query->select(['id','name']);
                },
            ]);
            if($request->school_id != ''){
                $data = $data->where('school_id', $request->school_id);
            }
            $data = $data->whereIn('school_batch_id', $batch_ids);
            $submittedList = Submission::select('student_id')->where('assignment_id', $request->assignId)->get();
            if($submittedList->count()) {
                $data = $data->whereNotIn('id', $submittedList);
            }
            $data = $data->get()->toArray();
            if (request()->ajax()) {
                return datatables()->of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $actionbtn = '<input type="checkbox" class="checkBox stud-checkBox" name="studIds[]" value="'.$row['id'].'">';
    
                        return $actionbtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } 

        return null;
    }

    public function markAssignment(Request $request)
    {
        $assignmentId = $request->assignmentId;
        $studIds = $request->studIds;
        if($studIds) {
            foreach($studIds as $sId) {
                Submission::create([
                    'student_id' => $sId,
                    'assignment_id' => $assignmentId,
                    'manual_submission' => '1'
                ]);

                /* START - Store Assignement Submission Reward Points */

                // Check if reward already exists for this assignment submission, if not then store reward points
                $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($sId,'assignment_submission',$assignmentId);
                if (!$alreadyRewarded->count()) {
                    StudentRewardPointsHelper::storeRewardPoints([
                        'student_id' =>  $sId,
                        'reward_type' => 'assignment_submission',
                        'item_id' => $assignmentId,
                        'reward_points' => 2,
                    ]);
                }

                /* END - Store Assignement Submission Reward Points */
            }
        }   
        return redirect(route('trainer.assigment.index'))->with('success', 'Assignment Marked Successfully!');
    }
}
