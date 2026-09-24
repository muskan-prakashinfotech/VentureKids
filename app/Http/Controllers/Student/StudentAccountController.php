<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentDetails;
use App\Models\Grade;
use App\Models\Stream;
use App\Models\StudentAttendance;
use App\Models\StudentCommunications;
use App\Models\Studentscontent;
use App\Models\StudentFeedback;
use App\Models\Students;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Image;
use App\Helpers\QuizHelper;
use App\Models\Event;
use App\Models\Project;
use App\Models\EventChallenge;
use App\Models\QuizAttempts;
use App\Models\QuizQuestions;
use App\Models\Submission;
use App\Jobs\StudentChangePasswordRequest;
use App\Models\WeeklyChallenges;
use App\Helpers\StudentRewardPointsHelper;
use App\Helpers\StudentObservationHelper;
use App\Models\SchoolAcademicYear;
use App\Models\StudentObservations;
use App\Models\Observation;
use App\Models\ExternalSession;
use App\Models\StudentRewardPoints;
use App\Models\PlayAffirmation;
use Carbon\Carbon;
use PDF;
use File;
use App\Models\ViewTracking;

class StudentAccountController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        //$this->middleware('permission:student_edit');
        //$this->middleware('role:admin|writer')->only('testmiddleware');

        $this->module_name = 'users';
    }

    protected function getDashboardRewardData($student_id, $school_id, $selectedAcademicYear)
    {
        $reward_points = StudentRewardPointsHelper::getRewardPoints($student_id, $selectedAcademicYear);

        $topFiveStudents = [];
        $allStudents = Students::select('id','user_id', 'image')->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            },
        ])->where('school_id', $school_id)->get();

        if($allStudents->count()) {
            foreach($allStudents as $student) {
                $getStudRewardPoints = StudentRewardPointsHelper::getRewardPoints($student->id, $selectedAcademicYear);
                $student->tot_reward_points = array_sum($getStudRewardPoints);
            }
            $topFiveStudents = $allStudents->sortByDesc('tot_reward_points')->take(5);
            $topFiveStudents = array_values($topFiveStudents->toArray());
        }

        return [$reward_points, $topFiveStudents];
    }

    public function index()
    {
        $userId = Session::get('user_id');
        $student = Students::select('id', 'school_id', 'grade_id', 'created_at', 'country_id')->where('user_id', $userId)->first();
        // Resolve the default/entry grade dynamically (is_primary + lowest
        // display order) instead of a hardcoded unique_code, which broke
        // every time the active database's `grades` catalog didn't contain
        // that exact code (see the matching fix in StudentService::createStudent()).
        $gradeData = Grade::select('id')->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::select('id')->orderBy('display_order_id')->first();
        $student_id = 0;
        $grade_id = 0;
        $currentGradeId = 0;
        $created_at = '';
        $subscribedGradeIds = [];
        $school_id = 0;
        if(!empty($student)) {
            $created_at = $student->created_at;
            $student = $student->toArray();
            $student_id = $student['id'];
            $grade_id = $student['grade_id'];
            $school_id = $student['school_id'];
            $subscribedGradeIds= explode(",", $grade_id);
            if(!empty($grade_id)) {
                if($gradeData && in_array($gradeData->id, $subscribedGradeIds)) {
                    $currentGradeId = $gradeData->id;
                } else {
                    $currentGradeId = $subscribedGradeIds[0];
                }
            }
        }

        $academicYears = collect();
        $academicYearOptions = [];
        $selectedAcademicYear = request()->get('academic_year');
        $defaultAcademicYear = 'past';

        if (!empty($school_id)) {
            $academicYears = SchoolAcademicYear::where('school_id', $school_id)
                ->orderByDesc('start_date')
                ->get();

            $today = Carbon::today();
            $currentAcademicYear = $academicYears->first(function ($academicYear) use ($today) {
                // return Carbon::parse($academicYear->start_date)->lte($today)
                    // && Carbon::parse($academicYear->end_date)->gte($today);
                return Carbon::parse($academicYear->start_date)
                    && Carbon::parse($academicYear->end_date);
            });

            if (!empty($currentAcademicYear)) {
                $defaultAcademicYear = (string) $currentAcademicYear->id;
            }

            if (empty($selectedAcademicYear)) {
                $selectedAcademicYear = $defaultAcademicYear;
            }

            $validAcademicYearIds = $academicYears->pluck('id')->map(function ($id) {
                return (string) $id;
            })->toArray();

            if ($selectedAcademicYear !== 'past' && !in_array((string) $selectedAcademicYear, $validAcademicYearIds)) {
                $selectedAcademicYear = $defaultAcademicYear;
            }

            $academicYearOptions = $academicYears->map(function ($academicYear) use ($today) {
                $startDate = Carbon::parse($academicYear->start_date);
                $endDate = Carbon::parse($academicYear->end_date);
                
                // if ($startDate->gt($today)) {
                //     $status = 'Upcoming';
                // } elseif ($endDate->lt($today)) {
                //     $status = 'Expired';
                // } else {
                //     $status = 'Current';
                // }

                return [
                    'id' => $academicYear->id,
                    // 'label' => $startDate->format('d-m-Y') . ' - ' . $endDate->format('d-m-Y') . ' (' . $status . ')',
                    'label' => 'AY ' . $startDate->format('Y') . ' - ' . $endDate->format('y'),
                ];
            })->values();
        }

        $pageTitle = 'Dashboard';

        $levels = Grade::where('is_publish', 1)->orderBy('display_order_id')->get();
        $filtered_levels = [];
        $filtered_collection = $levels->filter(function ($item) use (&$filtered_levels, $grade_id) {
            if($item->is_primary == 1) {
                $filtered_levels['primary'][$item->id] = $item->toArray();
            // } else if(!empty($grade_id) && in_array($item->id, explode(",", $grade_id))) {
            //     $filtered_levels['add-ons'][$item->id] = $item->toArray();;
            // }
             } else {
                $filtered_levels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        /*
        $getContentIdList = Studentscontent::select('id')->where('ageGroup_id', $currentGradeId)->where('is_publish', 1)->get();
        $quizPercentage = 0;
        if($getContentIdList->count()) {
            $quizIdList = QuizQuestions::select('content_id')->whereIn('content_id', $getContentIdList)->groupBy('content_id')->get();
            if($quizIdList->count()) {
                $quizCount = QuizAttempts::where('student_id', $student_id)->whereIn('quiz_id', $quizIdList)->where('is_submit', 1)->count();
                $quizPercentage = round($quizCount / $quizIdList->count() * 100);
            }

        }
        */

        /*
        $weeklyPercentage = 0;
        if(!empty($created_at)) {
            $start = Carbon::parse($created_at)->startOfDay();
            $end = Carbon::now()->startOfDay();
            $diff = $start->diffInDays($end);
            $weekcount = number_format(ceil(($diff+1) / 7));
            if($weekcount) {
                $weekly_challenges = WeeklyChallenges::select('id')->where('is_active', 1)->take($weekcount)->pluck('id')->toArray();
                if(!empty($weekly_challenges)) {
                    $challenges_id_list = implode("," , $weekly_challenges);
                    $attemptedChallengeCount = QuizAttempts::where('student_id', $student_id)->where('quiz_type', 'weekly_challenge')->where('is_submit', 1)->whereIn('quiz_id', explode(',', $challenges_id_list))->count();
                    $weeklyPercentage = round($attemptedChallengeCount / count($weekly_challenges) * 100);
                }
            }
        }
        */
        
        $weeklyChallengeCount = StudentRewardPointsHelper::getRewardTypes($student_id, ['weekly_challenge','daily_challenge'])->count();   
        
        $school_id = $student['school_id'];
        if (!empty($student)) {
            $assignmentIdList = StudentCommunications::select('id')->where('grade_id', $currentGradeId)->where(function($query) use ($school_id) {
                $query->where('school_id', $school_id)->orWhere('school_id', '=', 0);
            })->get();
        }

        $assignmentPercentage = 0;
        $completionData['assignment_submission']['total'] = 0;
        $completionData['assignment_submission']['completed'] = 0;
        if(isset($assignmentIdList) && $assignmentIdList->count()) {
            $assSubmissionCount = Submission::where('student_id', $student_id)->whereIn('assignment_id', $assignmentIdList)->count();
            $assignmentPercentage = round($assSubmissionCount / $assignmentIdList->count() * 100);
            $completionData['assignment_submission']['total'] = $assignmentIdList->count();
            $completionData['assignment_submission']['completed'] = $assSubmissionCount;
        }

        $modulePercentage = 0;
        $getScromList = Stream::select('id')->where('agegroup_id', $currentGradeId)->whereNotNull('scormFile')->get();
        $completionData['scorm_completion']['total'] = 0;
        $completionData['scorm_completion']['completed'] = 0;
        if($getScromList->count()) {
            $moduleCount = $getScromList->count();
            $scormIdList = $getScromList->pluck('id')->toArray();
            $completedModuleCount = StudentRewardPoints::where('student_id', $student_id) ->where('reward_type', 'video_learning_point')->whereIn('item_id', $scormIdList)->count();
            $modulePercentage = round($completedModuleCount / $moduleCount * 100);
            $completionData['scorm_completion']['total'] = $moduleCount;
            $completionData['scorm_completion']['completed'] = $completedModuleCount;
        }

        /*
        $challengeCount = 0;
        $challengeList = Event::select('id')->where('country_id', $student['country_id'])->get();
        if($challengeList->count()) {
            $totalChallengeCount = $challengeList->count();
            $challengeIdList = $challengeList->pluck('id')->toArray();
            $respondCount = EventChallenge::where('student_id', $student_id)->whereIn('event_id', $challengeIdList)->count();
            $challengeCount = round($respondCount / $totalChallengeCount * 100);
        }
        */
        $industryChallengeCount = 0;
        $challengeList = Event::where('is_publish', 1)->where(function ($query) use ($student) {
        $query->where('visibility_type', 1)->orWhere(function ($q) use ($student) {
                  $q->where('visibility_type', 2) // country-specific
                    ->where('country_id', $student['country_id']);
              });
        })->get(['id']); 
        if($challengeList->count()) {
            $challengeIdList = $challengeList->pluck('id')->toArray();
            $industryChallengeCount = EventChallenge::where('student_id', $student_id)->whereIn('event_id', $challengeIdList)->count();    
        }
        
        $projectCount = Project::where('student_id', $student_id)->where('is_publish',1)->count();
        
        $recommendedChallenges = Event::select('id', 'event_name', 'event_image')->where('is_publish', 1)->whereDate('event_last_date', '>=', date('Y-m-d')) ->where(function ($query) use ($student) {
             $query->where('visibility_type', 1)
              ->orWhere(function ($q) use ($student) {
                 $q->where('visibility_type', 2)
                  ->where('country_id', $student['country_id']);
              });
               })->inRandomOrder()->limit(4)->get();
        
        if($recommendedChallenges->count()) {
            foreach($recommendedChallenges as $challenge) {
                $event_image = $challenge->event_image;
                if(!empty($event_image)) {
                    $extension = pathinfo($event_image, PATHINFO_EXTENSION);
                    $filename = pathinfo($event_image, PATHINFO_FILENAME);
                    $filename = $filename.'-1920x1080.'.$extension;
                    if (file_exists(public_path('image/event/'.$filename))) {
                        $challenge->event_image = 'image/event/'.$filename;
                    }
                }
            }
        }
        
        $latestAffirmation = PlayAffirmation::latest()->first();
       
        $studentPlayCount = ViewTracking::where('item_type', 'audio')->where('student_id', $student_id)->groupBy('student_id')->selectRaw('student_id, SUM(play_count) as total_play_count')->pluck('total_play_count')->first() ?? 0;

        /* START - Get Student All Reward Points */
        [$reward_points, $topFiveStudents] = $this->getDashboardRewardData($student_id, $school_id, $selectedAcademicYear);
        /* END - Get Student All Reward Points */
        
        $viewData = [
            'student' => $student,
            'pageTitle'=> $pageTitle,
            'subscribedLevel' => $currentGradeId,
            'subscribedGradeIds' => $subscribedGradeIds,
            'filteredLevels' => $filtered_levels,
            'weeklyChallengeCount' => $weeklyChallengeCount,
            'assignmentPercentage' => $assignmentPercentage,
            'modulePercentage' => $modulePercentage,
            'industryChallengeCount' => $industryChallengeCount,
            'projectCount' => $projectCount,
            'recommendedChallenges' => $recommendedChallenges,
            'reward_points' => $reward_points,
            'academicYears' => $academicYearOptions,
            'selectedAcademicYear' => $selectedAcademicYear,
            'completionData' => $completionData,
            'topFiveStudents' => $topFiveStudents,
            'latestAffirmation' => $latestAffirmation,
            'studentPlayCount'=>  $studentPlayCount
        ];
        return view('student.index_new', $viewData);
    }

    public function getAcademicYearRewards(Request $request)
    {
        $student_id = Session::get('student_id');
        if (empty($student_id)) {
            return response()->json(['success' => false, 'message' => 'Student not identified.'], 401);
        }

        $student = Students::select('id', 'school_id')->find($student_id);
        if (empty($student) || empty($student->school_id)) {
            return response()->json(['success' => false, 'message' => 'School not found.'], 404);
        }

        $selectedAcademicYear = $request->get('academic_year', 'past');
        [$reward_points, $topFiveStudents] = $this->getDashboardRewardData($student_id, $student->school_id, $selectedAcademicYear);

        $leaderboardHtml = view('student.partials.dashboard_leaderboard', [
            'topFiveStudents' => $topFiveStudents,
        ])->render();

        return response()->json([
            'success' => true,
            'reward_points' => $reward_points,
            'total_points' => array_sum($reward_points),
            'leaderboard_html' => $leaderboardHtml,
        ]);
    }

    public function getStudentObservations(Request $request)
    {
        $userId  = Session::get('user_id');
        $student = Students::select('id')->where('user_id', $userId)->first();

        if (!$student) return response()->json([]);

        $gradeId = (int) $request->get('gradeId');
        if (!$gradeId) {
            return response()->json([]);
        }

        return StudentObservationHelper::getByStudentAndGrade($student->id, $gradeId);
    }

    // public function studentProfile()
    // {
    //     $student = Students::with([
    //         'school',
    //         'stdUser',
    //     ])
    //     ->find(Session::get('student_id'))
    //     ->toArray();

    //     // dd($student);

    //     return view('student.profile.student_edit', [
    //         'student' => $student,
    //     ]);
    // }

    public function studentUpdate(Request $request)
    {
        $user = User::find($request->user_id);

        if (!empty($request->old_password)) {
            if (Hash::check($request->old_password, $user->password)) {
                if ($request->confirm_password == $request->new_password) {
                    $user->password = Hash::make($request->new_password);
                } else {
                    return redirect()->back()->with('confirm_password_faild', "Your new password and confirm password didn't match");
                }
            } else {
                return redirect()->back()->with('old_password_faild', "Your Old password Didn't match");
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->save();

        $request_image = $request->file('profile_image');

        if (!empty($request_image)) {
            // $image_path = public_path('/image/student/');
            $image = Image::make($request_image);
            $directory = 'image/student/';
            $img_name = time() . '.' . $request_image->getClientOriginalExtension();
            $imageUrl = $directory . $img_name;
            $image->save($imageUrl);

            $image_name = $directory . 'thumbnail/' . $img_name;
            $image->resize(null, 200, function ($constraint) {
                $constraint->aspectRatio();
            });

            $image->save($image_name);
        } else {
            $image_name = $request->pre_image;
        }

        $students = Students::find($request->student_id);
        $students->project = $request->project;
        $students->name = $request->name;
        $students->assignment = $request->assignment;
        $students->classes_held = $request->classes_held;
        $students->classes_attended = $request->classes_attended;
        $students->attendance = $request->attendance;
        $students->overal_grade = $request->overal_grade;
        $students->parent_name = $request->parent_name;
        $students->parent_email = $request->parent_email;
        $students->address = $request->address;
        $students->blood_group = $request->blood_group;
        $students->activity_incharge = $request->activity_incharge;
        $students->image = $image_name;
        $students->save();

        return redirect('/student/profile/')->with('message', 'Student successfully Updated!');
    }

    public function downloadQuizScore($type, $quizId)
    {
        if(!in_array($type, ['content', 'weekly'])) {
            return redirect()->route('student.dashboard');
        }

        $studentID = Session::get('student_id');

        $quizResult = [];

        if(is_numeric($quizId)) {

            if($type == 'content') {
                $quizType = 'session';
            } else if($type == 'weekly') {
                $quizType = 'weekly_challenge';
            }

            /* START - GET QUIZE SCORE */
            $scoreData = QuizHelper::getQuizScore($quizId, $studentID, $quizType);
            /* END - GET QUIZE SCORE */

            if($type == 'content') {
                $quizDetail = Studentscontent::find($quizId);
                $streams = Stream::find($quizDetail->stream_id);
                $quizResult[$quizId] = ['level' => Grade::find($streams->agegroup_id)->grade, 'session' => $streams->title, 'title' => $quizDetail->title, 'score' => $scoreData['score']];
            } else if($type == 'weekly') {
                $challenge = WeeklyChallenges::select('challenge_name')->find($quizId);
                $quizResult[$quizId] = ['title' => $challenge->challenge_name, 'score' => $scoreData['score']];
            }
        } else {

            $quizAttempts = QuizHelper::getAllStudentSubmittedQuiz($studentID);

            if($quizAttempts->count()) {

                foreach($quizAttempts as $attempt) {

                    $quizId = $attempt->quiz_id;

                    /* START - GET QUIZE SCORE */
                    $scoreData = QuizHelper::getQuizScore($quizId, $studentID);
                    /* END - GET QUIZE SCORE */

                    $quizDetail = Studentscontent::find($quizId);
                    $streams = Stream::find($quizDetail->stream_id);

                    $quizResult[$quizId] = ['level' => Grade::find($streams->agegroup_id)->grade, 'session' => $streams->title, 'title' => $quizDetail->title, 'score' => $scoreData['score']];
                }
            }
        }

        $pdf = PDF::loadView('student.download_quiz_score', ['student_name' => Session::get('student_name'),'quizDetail' => $quizResult, 'quizType' => $quizType]);

        return $pdf->download('scorecard.pdf');
    }

    public function getCompletionRatePercentage(Request $request) {
        $gradeId = (int)$request->gradeId;
        if($gradeId) {
            $student_id = Session::get('student_id');

            /*
            $getContentIdList = Studentscontent::select('id')->where('ageGroup_id', $gradeId)->where('is_publish', 1)->get();
            $quizPercentage = 0;
            if($getContentIdList->count()) {
                $quizIdList = QuizQuestions::select('content_id')->whereIn('content_id', $getContentIdList)->groupBy('content_id')->get();
                if($quizIdList->count()) {
                    $quizCount = QuizAttempts::where('student_id', $student_id)->whereIn('quiz_id', $quizIdList)->where('is_submit', 1)->count();
                    $quizPercentage = round($quizCount / $quizIdList->count() * 100);
                }
            }
            */

            $studentData = Students::select('school_id')->where('id', $student_id)->get();
            $schoolId = 0;
            if($studentData->count()) {
                $schoolId = $studentData[0]->school_id;
            }

            $assignmentIdList = StudentCommunications::select('id')->where('grade_id', $gradeId)->where(function($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->orWhere('school_id', '=', 0);
            })->get();
            $assignmentPercentage = 0;
            $completionData['assignment_submission']['total'] = 0;
            $completionData['assignment_submission']['completed'] = 0;
            if($assignmentIdList->count()) {
                $assSubmissionCount = Submission::where('student_id', $student_id)->whereIn('assignment_id', $assignmentIdList)->count();
                $assignmentPercentage = round($assSubmissionCount / $assignmentIdList->count() * 100);
                $completionData['assignment_submission']['total'] = $assignmentIdList->count();
                $completionData['assignment_submission']['completed'] = $assSubmissionCount;
            }

            $modulePercentage = 0;
            $getScromList = Stream::select('id')->where('agegroup_id', $gradeId)->whereNotNull('scormFile')->get();
            $completionData['scorm_completion']['total'] = 0;
            $completionData['scorm_completion']['completed'] = 0;
            if($getScromList->count()) {
                $moduleCount = $getScromList->count();
                $scormIdList = $getScromList->pluck('id')->toArray();
                $completedModuleCount = StudentRewardPoints::where('student_id', $student_id)->where('reward_type', 'video_learning_point')->whereIn('item_id', $scormIdList)->count();
                $modulePercentage = round($completedModuleCount / $moduleCount * 100);
                $completionData['scorm_completion']['total'] = $moduleCount;
                $completionData['scorm_completion']['completed'] = $completedModuleCount;
            }
            
            return response()->json(['success' => [
                'assignmentPercentage' => $assignmentPercentage,
                'modulePercentage' => $modulePercentage,
                'completionData' => $completionData,
            ]]);
        }
        return response()->json(['error' => 'Level Not Found!']);
    }

    public function changePassword(Request $request) {
        return view('student.change_password');
    }

    public function changePasswordRequest(Request $request) {
        $host = $request->getHost();
        $student_id = Session::get('student_id');
        $student_data = Students::select('id','user_id','school_id')->with(['school' => function($query){
            $query->select('id', 'school_name', 'official_email_id');
        }, 'user' => function($query) {
            $query->select('id', 'name', 'email');
        }])->find($student_id);

       if($student_data) {
            $student_data = $student_data->toArray();

           // START - SEND AN EMAIL TO SCHOOL FOR CHANGE PASSWORD
           dispatch(new StudentChangePasswordRequest($student_data, $host));
          // END - SEND AN EMAIL TO SCHOOL FOR CHANGE PASSWORD
       }

       return redirect()->route('student.dashboard')->with('change-password-success', 'Your Change Password Request has been sent successfully!');

    }

    public function profilePhoto(Request $request) {
        $student_id = Session::get('student_id');
        
        $student = Students::select('image')->find($student_id);

        return view('student.upload_profile_photo',compact('student'));
    }

    public function uploadProfilePhoto(Request $request) 
    {
        if (!$request->hasFile('profile_photo')) {
            return back()->with('message', 'No file received.');
        }

        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB
        ]);

        $student_id = Session::get('student_id');
        
        $student = Students::find($student_id);
        
        if ($student->school->tenant_id) {
            
            if($student->image) {
                $studentProfileImagePath = \Storage::disk('tenant_uploads')->path($student->image);
                if (File::exists($studentProfileImagePath)) {
                    File::delete($studentProfileImagePath);
                }
            }

            $request_image = $request->profile_photo;

            $file = $request_image->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $profileImgName = $filename. '_' . time() .".". $extension;
            
            $path = $request_image->storeAs($student->school->tenant_id.'/student', $profileImgName, 'tenant_uploads');

            $student->image = $student->school->tenant_id.'/student/' . $profileImgName;

            $student->save();
        }

        return back()->with('message', 'Profile photo uploaded successfully.');
    }

    public function deleteProfilePhoto(Request $request) 
    { 
        $student_id = Session::get('student_id');
        
        $student = Students::find($student_id);
        
        if($student->school->tenant_id && $student->image) {
            $studentProfileImagePath = \Storage::disk('tenant_uploads')->path($student->image);
            if (File::exists($studentProfileImagePath)) {
                File::delete($studentProfileImagePath);
            }
            $student->image = null;

            $student->save();
        }

        return response()->json(['message' => 'Profile photo deleted!']);
    }

    /* To track the audio file completely listened */ 
    public function audioTracking(Request $request)
    {
        if ($request->event === 'ended') {
            $studentId = Session::get('student_id');

            if (!$studentId) {
                return response()->json(['success' => false, 'message' => 'Student not identified'], 401);
            }

            $latestAffirmation = PlayAffirmation::latest()->first();

            if (!$latestAffirmation) {
                return response()->json(['success' => false, 'message' => 'No affirmation found'], 404);
            }

            // Find or create the play record
            $view = ViewTracking::firstOrNew([
                'student_id' => $studentId,
                'item_id' => $latestAffirmation->id
            ]);

            $view->item_type = 'audio'; // Ensure it's set if using in sum()
            $view->play_count = $view->exists ? $view->play_count + 1 : 1;
            $view->save();

            // Return student-specific play count
           $studentPlayCount = ViewTracking::where('item_type', 'audio')->where('student_id', $studentId)->groupBy('student_id')->selectRaw('student_id, SUM(play_count) as total_play_count')->pluck('total_play_count')->first() ?? 0;

            return response()->json([
                'success' => true,
                'message' => 'Play count updated',
                'studentPlayCount' =>$studentPlayCount,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'No action taken']);
    }
}
