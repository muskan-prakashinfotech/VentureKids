<?php

use App\Http\Controllers\RouteActionController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\ContentStudentController;
use App\Http\Controllers\Trainer\AssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\TrainerLavelController;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware([
    //'check_central_domain'
])->group(function() {
    // Autho Routes
    require __DIR__ . '/auth.php';
});

// Language Switch
Route::get('language/{language}', 'LanguageController@switch')->name('language.switch');

Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::get('/', 'FrontendController@index')->name('index');
});

/*
*
* Backend Routes
* These routes need view-backend permission
* --------------------------------------------------------------------
*/

// =====================  Admin Section =================
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'as' => 'backend.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:1', 'block_partner_restricted_sections']], function () {
    /*
     * Backend Dashboard
     * Namespaces indicate folder structure.
     */
    // Admin Routes is start here

    Route::get('/', 'BackendController@index')->name('home');
    Route::get('dashboard', 'BackendController@index')->name('dashboard');
    Route::match(['get', 'post'], 'support/find-by-username-or-email', function (Request $request) {
        $keyword = trim((string) ($request->input('keyword') ?? $request->input('search') ?? ''));

        if ($keyword === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please pass keyword or search.',
            ], 422);
        }

        $lowerKeyword = strtolower($keyword);

        $users = DB::table('users')
            ->select('id', 'name', 'username', 'email', 'group', 'status', 'suspend')
            ->where(function ($query) use ($keyword, $lowerKeyword) {
                $query->where('username', $keyword)
                    ->orWhereRaw('LOWER(email) = ?', [$lowerKeyword]);
            })
            ->get();

        $trainers = DB::table('trainers')
            ->leftJoin('users', 'users.id', '=', 'trainers.user_id')
            ->select(
                'trainers.id',
                'trainers.user_id',
                'trainers.trainer_name',
                'trainers.official_email_id',
                'trainers.status as trainer_status',
                'trainers.contact_no',
                'users.username as linked_username',
                'users.email as linked_user_email',
                'users.status as user_status',
                'users.suspend as user_suspend'
            )
            ->where(function ($query) use ($keyword, $lowerKeyword) {
                $query->whereRaw('LOWER(trainers.official_email_id) = ?', [$lowerKeyword])
                    ->orWhereRaw('LOWER(trainers.incharge_email) = ?', [$lowerKeyword])
                    ->orWhere('users.username', $keyword)
                    ->orWhereRaw('LOWER(users.email) = ?', [$lowerKeyword]);
            })
            ->get();

        $schools = DB::table('schools')
            ->leftJoin('users', 'users.id', '=', 'schools.user_id')
            ->select(
                'schools.id',
                'schools.user_id',
                'schools.school_name',
                'schools.official_email_id',
                'schools.incharge_email',
                'schools.status as school_status',
                'schools.contact_number',
                'schools.tenant_id',
                'users.username as linked_username',
                'users.email as linked_user_email',
                'users.status as user_status',
                'users.suspend as user_suspend'
            )
            ->where(function ($query) use ($keyword, $lowerKeyword) {
                $query->whereRaw('LOWER(schools.official_email_id) = ?', [$lowerKeyword])
                    ->orWhereRaw('LOWER(schools.incharge_email) = ?', [$lowerKeyword])
                    ->orWhere('users.username', $keyword)
                    ->orWhereRaw('LOWER(users.email) = ?', [$lowerKeyword]);
            })
            ->get();

        $students = DB::table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('schools', 'schools.id', '=', 'students.school_id')
            ->select(
                'students.id',
                'students.user_id',
                'students.school_id',
                'students.name',
                'students.parent_email',
                'students.grade_id',
                'students.status as student_status',
                'users.username as linked_username',
                'users.email as linked_user_email',
                'users.status as user_status',
                'users.suspend as user_suspend',
                'schools.school_name'
            )
            ->where(function ($query) use ($keyword, $lowerKeyword) {
                $query->where('users.username', $keyword)
                    ->orWhereRaw('LOWER(users.email) = ?', [$lowerKeyword])
                    ->orWhereRaw('LOWER(students.parent_email) = ?', [$lowerKeyword]);
            })
            ->get();

        return response()->json([
            'success' => true,
            'keyword' => $keyword,
            'counts' => [
                'users' => $users->count(),
                'trainers' => $trainers->count(),
                'schools' => $schools->count(),
                'students' => $students->count(),
            ],
            'data' => [
                'users' => $users,
                'trainers' => $trainers,
                'schools' => $schools,
                'students' => $students,
            ],
        ]);
    })->name('support.find_by_username_or_email');

    /* To Reset Password of School Students*/
    Route::get('reset-dcm-students-password/{schoolId}', function ($schoolId, Request $request) {
        try {
            $password = $request->query('password', 'Kids@DCM1');

            $school = DB::table('schools')
                ->where('id', $schoolId)
                ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found for the provided school id.',
                    'school_id' => (int) $schoolId,
                ], 404);
            }
            
            $userIds = DB::table('students')
                ->where('school_id', $school->id)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            if ($userIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found for this school.',
                    'school_id'   => $school->id,
                    'school_name' => $school->school_name,
                ], 404);
            }
            
            $updated = DB::table('users')
                ->whereIn('id', $userIds)
                ->where('group', 4)
                ->update([
                    'password' => Hash::make($password),
                ]);

            return response()->json([
                'success'                  => true,
                'message'                  => 'Passwords reset successfully.',
                'school_id'                => $school->id,
                'school_name'              => $school->school_name,
                'total_students_in_school' => $userIds->count(),
                'total_passwords_updated'  => $updated,
                'password_used'            => $password,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    })->name('reset.dcm.students.password');

    Route::get('/clear-cache', function() {
        Artisan::call('optimize:clear');
        return 'Application cache has been cleared';
    });

    Route::get('/storage-link ', function() {
        Artisan::call('storage:link');
        return 'Symbolic link created successfully';
    });

    Route::get('/student-grade/add-grades-11-12', function() {
        $grades = ['Grade11', 'Grade12'];
        $created = [];

        foreach ($grades as $gradeName) {
            $grade = \App\Models\StudentGrade::where('name', $gradeName)->first();

            if (!$grade) {
                $grade = new \App\Models\StudentGrade();
                $grade->name = $gradeName;
                $grade->save();
            }

            $created[] = $grade->name;
        }

        return response()->json([
            'success' => true,
            'grades' => $created,
            'message' => 'Grade 11 and Grade 12 have been added to the student_grade table if they were missing.'
        ]);
    });

    // Remove Thinkpreneur access from Grade 1 & Grade 2 students for a school
    /*Route::get('/dbels-school/{schoolId}/remove-thinkpreneur-grade-1-2', function($schoolId) {
        $school = \App\Models\School::find($schoolId);
        $studentGradeIds = \App\Models\StudentGrade::whereIn('name', ['Grade1', 'Grade2'])->pluck('id');
        $thinkpreneurId = '3';

        if (!$school || $studentGradeIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve school or student grades (Grade1/Grade2).'
            ], 422);
        }

        $students = \App\Models\Students::where('school_id', $school->id)
            ->whereIn('student_grade_id', $studentGradeIds)
            ->get();

        $updated = [];

        foreach ($students as $student) {
            $gradeIds = array_filter(array_map('trim', explode(',', (string) $student->grade_id)));

            if (in_array($thinkpreneurId, $gradeIds, true)) {
                $gradeIds = array_values(array_diff($gradeIds, [$thinkpreneurId]));
                $student->grade_id = implode(',', $gradeIds);
                $student->save();
                $updated[] = ['id' => $student->id, 'name' => $student->name, 'grade_id' => $student->grade_id];
            }
        }

        return response()->json([
            'success' => true,
            'updated_students' => $updated,
            'message' => 'Thinkpreneur access removed for Grade1 & Grade2 students of the school.'
        ]);
    });*/

    // Add Createpreneur and Createpreneur+ access for Grade 3 students for a school
    /*Route::get('/dbels-school/{schoolId}/add-createpreneur-grade-3', function($schoolId) {
        $school = \App\Models\School::find($schoolId);
        $studentGradeId = \App\Models\StudentGrade::where('name', 'Grade3')->value('id');
        $courseIds = ['4', '23'];

        if (!$school || !$studentGradeId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve school or student grade (Grade3).'
            ], 422);
        }

        $students = \App\Models\Students::where('school_id', $school->id)
            ->where('student_grade_id', $studentGradeId)
            ->get();

        $updated = [];

        foreach ($students as $student) {
            $gradeIds = array_filter(array_map('trim', explode(',', (string) $student->grade_id)));
            $gradeIds = array_values(array_unique(array_merge($gradeIds, $courseIds)));
            $student->grade_id = implode(',', $gradeIds);
            $student->save();
            $updated[] = ['id' => $student->id, 'name' => $student->name, 'grade_id' => $student->grade_id];
        }

        return response()->json([
            'success' => true,
            'updated_students' => $updated,
            'message' => 'Createpreneur & Createpreneur+ access added for Grade3 students of the school.'
        ]);
    });*/

    Route::get('/admin/get-school-by-email', function() {
        $user = \App\Models\User::whereIn('email', ['sevenset7@gmail.com', 'akalismiles777@gmail.com', 'akhruvitsu@gmail.com'])->get();

        echo '<pre>';
        print_r($user->toArray());

        $school = \App\Models\School::whereIn('official_email_id', ['sevenset7@gmail.com', 'akalismiles777@gmail.com', 'akhruvitsu@gmail.com'])->get();
        print_r($school->toArray());
        die();
    });

    // Manual delete project
    Route::get('/student/manually-delete-project/{projectId}', function($projectId) {
        try {
            $project = \App\Models\Project::where('id', $projectId)->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or you do not have permission to delete it.'
                ]);
            }

            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully along with all associated files.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project: ' . $e->getMessage()
            ]);
        }
    });
    /**
     * Get student gender statistics
     * Usage: /student-gender-stats
     */
    Route::get('/student-gender-statistics', function() {
        
        $maleCount = \App\Models\User::where('group', 4)
            ->where('gender', 'Male')
            ->count();
        
        $femaleCount = \App\Models\User::where('group', 4)
            ->where('gender', 'Female')
            ->count();
        
        $otherCount = \App\Models\User::where('group', 4)
            ->where('gender', 'Other')
            ->count();

        $nullCount = \App\Models\User::where('group', 4)
            ->whereNull('gender')   
            ->orWhere('gender', '')     
            ->count();
        
        $totalStudents = \App\Models\User::where('group', 4)->count();
        
        return response()->json([
            'male' => $maleCount,
            'female' => $femaleCount,
            'other' => $otherCount,
            'null' => $nullCount,
            'total' => $totalStudents
        ]);
        
    })->name('student.gender.stats.simple');

    Route::get('/admin/clear-external-session-email-jobs', function() {
        $patterns = [
            '%QueueExternalSessionEmails%',
            '%QueueExternalSessionReminderEmails%',
            '%QueueSessionCancelledEmails%',
            '%SendExternalSessionEmails%',
            '%SendExternalSessionReminderEmails%',
            '%SendSessionCancelledEmail%',
        ];

        $query = DB::table('jobs');
        foreach ($patterns as $pattern) {
            $query->orWhere('payload', 'like', $pattern);
        }
        $deleted = $query->delete();

        $failedQuery = DB::table('failed_jobs');
        foreach ($patterns as $pattern) {
            $failedQuery->orWhere('payload', 'like', $pattern);
        }
        $deletedFailed = $failedQuery->delete();

        return "Done. {$deleted} pending job(s) and {$deletedFailed} failed job(s) removed.";
    });

    // DB action route and clear route
    /*
    Route::get('clear', [RouteActionController::class, 'clear']);
    Route::get('migration', [RouteActionController::class, 'migration']);
    Route::get('migration/refresh', [RouteActionController::class, 'migrationRefresh']);
    Route::get('migration/rollback', [RouteActionController::class, 'migrationRollback']);
    Route::get('seeder', [RouteActionController::class, 'seedRun']);
    Route::get('seeder/{class}', [RouteActionController::class, 'runSeedWithClass']);
    Route::get('artisancall/{action}', [RouteActionController::class, 'artisanCall']);
    */

    // School Onboarding
    Route::get('schoolcreate', ['as' => 'schoolcreate.schoolCreate', 'uses' => 'SchoolController@schoolCreate']);
    Route::post('schoolstore', ['as' => 'schoolstore.schoolStore', 'uses' => 'SchoolController@schoolStore']);
    Route::get('schoollist', ['as' => 'schoollist.schoolList', 'uses' => 'SchoolController@schoolList']);
    Route::get('pending-school-approvals', ['as' => 'pending-schools.index', 'uses' => 'PendingSchoolController@index', 'middleware' => ['super_admin_only']]);
    Route::post('pending-school-approvals/{pendingSchool}/approve', ['as' => 'pending-schools.approve', 'uses' => 'PendingSchoolController@approve', 'middleware' => ['super_admin_only']]);
    Route::post('pending-school-approvals/{pendingSchool}/reject', ['as' => 'pending-schools.reject', 'uses' => 'PendingSchoolController@reject', 'middleware' => ['super_admin_only']]);
    Route::get('schooledit/{id}', ['as' => 'schooledit.schoolEdit', 'uses' => 'SchoolController@schoolEdit']);
    Route::post('schoolupdate', ['as' => 'schoolupdate.schoolUpdate', 'uses' => 'SchoolController@schoolUpdate']);
    Route::post('deleteSchoolLogo', ['as' => 'school.deleteSchoolLogo', 'uses' => 'SchoolController@deleteSchoolLogo']);
    Route::post('deleteSchoolCoverLogo', ['as' => 'school.deleteSchoolCoverLogo', 'uses' => 'SchoolController@deleteSchoolCoverLogo']);
    Route::post('deleteSchoolCss', ['as' => 'school.deleteSchoolCss', 'uses' => 'SchoolController@deleteSchoolCss']);
    Route::post('deleteSchoolLoader', ['as' => 'school.deleteSchoolLoader', 'uses' => 'SchoolController@deleteSchoolLoader']);
    Route::post('delete/batch', ['as' => 'school.deleteBatch', 'uses' => 'SchoolController@deleteBatch']);
    Route::get('schooldelete/{id}', ['as' => 'schooldelete.schoolDelete', 'uses' => 'SchoolController@schoolDelete']);
    Route::get('viewschool/{school}', ['as' => 'viewschool.viewschool', 'uses' => 'SchoolController@viewschool']);
    Route::get('studentList/{id}', ['as' => 'studentList.studentList', 'uses' => 'SchoolController@studentList']);
    Route::get('/student/export/{id}', ['uses' => 'SchoolController@exportStudents', 'as' => 'student-export']);
    Route::get('studentDeleteRequestList/{id}', ['as' => 'studentDeleteRequest.studentDeleteRequest', 'uses' => 'SchoolController@studentDeleteRequest']);
    Route::get('student-add/{school}', ['as' => 'student-add', 'uses' => 'SchoolController@studentAdd']);
    Route::get('/school/{school}/update-grades', ['as' =>'school.update-grades', 'uses' => 'SchoolController@updateGrades']);
    Route::get('/trainer/update-grades', ['as' =>'trainer.update-grades', 'uses' => 'TrainerController@updateTrainerGrades']);
    Route::get('student-import/{school}', ['as' => 'student-import', 'uses' => 'SchoolController@studentImport']);
    Route::get('add-student', ['as' => 'add-student', 'uses' => 'SchoolController@studentAddDirect']);
    Route::post('student-store/{school}', ['as' => 'student-store', 'uses' => 'SchoolController@studentStore']);
    Route::post('import-store', ['as' => 'import-store', 'uses' => 'SchoolController@studentImportStore']);
    Route::post('student-new', ['as' => 'student-new', 'uses' => 'SchoolController@studentNew']);
    Route::get('/student_list_datatable', ['uses' => 'SchoolController@student_list_datatable', 'as' => 'student_list_datatable']);
    Route::get('/student_delete_list_datatable', ['uses' => 'SchoolController@student_delete_list_datatable', 'as' => 'student_delete_list_datatable']);
    Route::get('/student-delete/{id}', ['uses' => 'SchoolController@student_delete', 'as' => 'student-delete']);
    Route::get('/student-edit/{school}/{student}', ['uses' => 'SchoolController@studentEdit', 'as' => 'student-edit']);
    Route::post('/student-update/{school}/{student}', ['uses' => 'SchoolController@studentUpdate', 'as' => 'student-update']);
    Route::get('/delete_existing_student', ['uses' => 'SchoolController@delete_existing_student', 'as' => 'delete_existing_student']);
    Route::post('/delete-student-profile-avatar', ['uses' => 'SchoolController@deleteStudentProfileAvatar', 'as' => 'delete-student-profile-avatar']);
    Route::get('students/generate-public-usernames', ['as' => 'students.generate-public-usernames', 'uses' => 'SchoolController@generatePublicUsernames']);
    Route::post('deleteWorkSheet', ['as' => 'content.deleteWorkSheet', 'uses' => 'ContentController@deleteWorkSheet']);
    Route::post('deleteContentVideo', ['as' => 'content.deleteContentVideo', 'uses' => 'ContentController@deleteContentVideo']);
    Route::get('loginhistory',['as'=>'student.loginhistory','uses'=>'LoginTrackingController@index']);
    Route::get('/login_history_datatable', ['uses' => 'LoginTrackingController@login_history_datatable', 'as' => 'login_history_datatable']);
    Route::post('/student_all_login_history', ['uses' => 'LoginTrackingController@getStudentLoginHistory', 'as' => 'student_all_login_history']);
    Route::get('/export-all-login-history/{studentId}', ['as' => 'export-all-login-history', 'uses' => 'LoginTrackingController@exportStudentLoginHistory']);
    Route::get('/export-login-history',['as'=>'export-login-history','uses'=>'LoginTrackingController@exportLoginHistoryOfAllStudent']);

    Route::get('play-affirmation',['as'=>'student.playaffirmation','uses'=>'PlayAffirmationController@index']);
    Route::post('play-affirmation/store', ['as' => 'student.playaffirmation.store','uses' => 'PlayAffirmationController@store']);
      Route::post('deleteAffirmationFile', ['as' => 'deleteAffirmationFile', 'uses' => 'PlayAffirmationController@deleteAffirmationFile']);

    Route::post('deleteQuestion', ['as' => 'content.deleteQuestion', 'uses' => 'ContentController@deleteQuestion']);
    Route::post('disableQuestion', ['as' => 'content.disableQuestion', 'uses' => 'ContentController@disableQuestion']);
    Route::post('enableQuestion', ['as' => 'content.enableQuestion', 'uses' => 'ContentController@enableQuestion']);

    Route::post('deleteTrainerContentVideo', ['as' => 'content.deleteTrainerContentVideo', 'uses' => 'TrainerContentController@deleteTrainerContentVideo']);
    Route::post('deleteTrainerSessionImage', ['as' => 'content.deleteTrainerSessionImage', 'uses' => 'TrainerContentController@deleteTrainerSessionImage']);
    Route::post('deleteTrainerSessionPdf', ['as' => 'content.deleteTrainerSessionPdf', 'uses' => 'TrainerContentController@deleteTrainerSessionPdf']);
    Route::post('deleteTrainerWorksheet', ['as' => 'content.deleteTrainerWorksheet', 'uses' => 'TrainerContentController@deleteTrainerWorksheet']);
    Route::post('deleteTrainerSessionPresentation', ['as' => 'content.deleteTrainerSessionPresentation', 'uses' => 'TrainerContentController@deleteTrainerSessionPresentation']);

    //Generate AI Tool
    Route::get('ai-Tool/create', ['as' => 'aiToolcreate.aiToolCreate', 'uses' => 'GenerateAIToolController@aiToolCreate']);
    Route::post('ai-Tool/store', ['as' => 'aiToolstore.aiToolStore', 'uses' => 'GenerateAIToolController@aiToolStore']);
    Route::get('/ai-Tools/list', ['uses' => 'GenerateAIToolController@getAiTool', 'as' => 'getAiTool']);
    Route::get('ai-Tool/list', ['as' => 'aiToollist.aiToolList', 'uses' => 'GenerateAIToolController@aiToolList']);
    Route::delete('/ai-Tools/{id}', ['as' => 'aiTooldelete.aiToolDelete', 'uses' => 'GenerateAIToolController@aiToolDelete']);
    Route::get('/ai-Tools/{id}/edit', ['as' => 'aiTooledit.aiToolEdit', 'uses' => 'GenerateAIToolController@aiToolEdit']);
    Route::put('/ai-Tools-update/{id}', ['as' => 'aiToolupdate.aiToolUpdate', 'uses' => 'GenerateAIToolController@aiToolUpdate']);
    Route::post('/ai-Tools/delete-image', ['uses' => 'GenerateAIToolController@deleteAiToolImage', 'as' => 'deleteAiToolImage']);

    // Project Management
    Route::get('project-management', ['as' => 'projectmanagement.index', 'uses' => 'ProjectManagementDashboardController@index']);

    // Project Section
    Route::get('project-section/create', ['as' => 'projectSectioncreate.projectSectionCreate', 'uses' => 'ProjectSectionController@projectSectionCreate']);
    Route::post('project-section/store', ['as' => 'projectSectionstore.projectSectionStore', 'uses' => 'ProjectSectionController@projectSectionStore']);
    Route::get('project-sections/list', ['uses' => 'ProjectSectionController@getProjectSection', 'as' => 'getProjectSection']);
    Route::get('project-section/list', ['as' => 'projectSectionlist.projectSectionList', 'uses' => 'ProjectSectionController@projectSectionList']);
    Route::delete('/project-section/{id}', ['as' => 'projectSectiondelete.projectSectionDelete', 'uses' => 'ProjectSectionController@projectSectionDelete']);
    Route::get('/project-section/{id}/edit', ['as' => 'projectSectionedit.projectSectionEdit', 'uses' => 'ProjectSectionController@projectSectionEdit']);
    Route::put('/project-section-update/{id}', ['as' => 'projectSectionupdate.projectSectionUpdate', 'uses' => 'ProjectSectionController@projectSectionUpdate']);

    // Project Question
    Route::get('project-section/{sectionId}/questions', ['as' => 'projectQuestionlist.projectQuestionList', 'uses' => 'ProjectQuestionController@projectQuestionList']);
    Route::get('project-section/{sectionId}/questions/data', ['as' => 'getProjectQuestion', 'uses' => 'ProjectQuestionController@getProjectQuestion']);
    Route::get('project-section/{sectionId}/question/create', ['as' => 'projectQuestioncreate.projectQuestionCreate', 'uses' => 'ProjectQuestionController@projectQuestionCreate']);
    Route::post('project-section/{sectionId}/question/store', ['as' => 'projectQuestionstore.projectQuestionStore', 'uses' => 'ProjectQuestionController@projectQuestionStore']);
    Route::post('project-section/{sectionId}/question/store-attachment', ['as' => 'projectQuestionAttachmentstore.projectQuestionAttachmentStore', 'uses' => 'ProjectQuestionController@projectQuestionAttachmentStore']);
    Route::put('/project-question/{id}/toggle-attachments', ['as' => 'projectQuestionToggleattachments.projectQuestionToggleAttachments', 'uses' => 'ProjectQuestionController@toggleAllowAttachments']);
    Route::delete('/project-question/{id}', ['as' => 'projectQuestiondelete.projectQuestionDelete', 'uses' => 'ProjectQuestionController@projectQuestionDelete']);
    Route::get('/project-question/{id}/edit', ['as' => 'projectQuestionedit.projectQuestionEdit', 'uses' => 'ProjectQuestionController@projectQuestionEdit']);
    Route::put('/project-question-update/{id}', ['as' => 'projectQuestionupdate.projectQuestionUpdate', 'uses' => 'ProjectQuestionController@projectQuestionUpdate']);

    //Project Theme
    Route::get('project-theme/create', ['as' => 'projectThemecreate.projectThemeCreate', 'uses' => 'ProjectThemeController@projectThemeCreate']);
    Route::post('project-theme/store', ['as' => 'projectThemestore.projectThemeStore', 'uses' => 'ProjectThemeController@projectThemeStore']);
    Route::get('project-themes/list', ['uses' => 'ProjectThemeController@getProjectTheme', 'as' => 'getProjectTheme']);
    Route::get('project-theme/list', ['as' => 'projectThemelist.projectThemeList', 'uses' => 'ProjectThemeController@projectThemeList']);
    Route::delete('/project-theme/{id}', ['as' => 'projectThemedelete.projectThemeDelete', 'uses' => 'ProjectThemeController@projectThemeDelete']);
    Route::get('/project-theme/{id}/edit', ['as' => 'projectThemeedit.projectThemeEdit', 'uses' => 'ProjectThemeController@projectThemeEdit']);
    Route::put('/project-theme-update/{id}', ['as' => 'projectThemeupdate.projectThemeUpdate', 'uses' => 'ProjectThemeController@projectThemeUpdate']);

    // RealQ Assessment
    Route::get('realq-assessment', ['as' => 'realqassessment.index', 'uses' => 'RealQAssessmentDashboardController@index']);
    Route::get('realq-assessment/grades', ['as' => 'realqassessment.grades.index', 'uses' => 'StudentGradeController@index']);
    Route::get('realq-assessment/grades/create', ['as' => 'realqassessment.grades.create', 'uses' => 'StudentGradeController@create']);
    Route::post('realq-assessment/grades/store', ['as' => 'realqassessment.grades.store', 'uses' => 'StudentGradeController@store']);
    Route::get('realq-assessment/grades/edit/{id}', ['as' => 'realqassessment.grades.edit', 'uses' => 'StudentGradeController@edit']);
    Route::post('realq-assessment/grades/update', ['as' => 'realqassessment.grades.update', 'uses' => 'StudentGradeController@update']);
    Route::delete('realq-assessment/grades/delete/{id}', ['as' => 'realqassessment.grades.delete', 'uses' => 'StudentGradeController@destroy']);

    Route::get('realq-assessment/subjects', ['as' => 'realqassessment.subjects.index', 'uses' => 'RealQAssessmentSubjectController@index']);
    Route::get('realq-assessment/subjects/create', ['as' => 'realqassessment.subjects.create', 'uses' => 'RealQAssessmentSubjectController@create']);
    Route::post('realq-assessment/subjects/store', ['as' => 'realqassessment.subjects.store', 'uses' => 'RealQAssessmentSubjectController@store']);
    Route::get('realq-assessment/subjects/edit/{id}', ['as' => 'realqassessment.subjects.edit', 'uses' => 'RealQAssessmentSubjectController@edit']);
    Route::post('realq-assessment/subjects/update', ['as' => 'realqassessment.subjects.update', 'uses' => 'RealQAssessmentSubjectController@update']);
    Route::delete('realq-assessment/subjects/delete/{id}', ['as' => 'realqassessment.subjects.delete', 'uses' => 'RealQAssessmentSubjectController@destroy']);

    Route::get('realq-assessment/parameters', ['as' => 'realqassessment.parameters.index', 'uses' => 'RealQAssessmentParameterController@index']);
    Route::get('realq-assessment/parameters/create', ['as' => 'realqassessment.parameters.create', 'uses' => 'RealQAssessmentParameterController@create']);
    Route::post('realq-assessment/parameters/store', ['as' => 'realqassessment.parameters.store', 'uses' => 'RealQAssessmentParameterController@store']);
    Route::get('realq-assessment/parameters/edit/{id}', ['as' => 'realqassessment.parameters.edit', 'uses' => 'RealQAssessmentParameterController@edit']);
    Route::post('realq-assessment/parameters/update', ['as' => 'realqassessment.parameters.update', 'uses' => 'RealQAssessmentParameterController@update']);
    Route::delete('realq-assessment/parameters/delete/{id}', ['as' => 'realqassessment.parameters.delete', 'uses' => 'RealQAssessmentParameterController@destroy']);

    Route::get('realq-assessment/scales', ['as' => 'realqassessment.scales.index', 'uses' => 'RealQAssessmentScaleController@index']);
    Route::get('realq-assessment/scales/create', ['as' => 'realqassessment.scales.create', 'uses' => 'RealQAssessmentScaleController@create']);
    Route::post('realq-assessment/scales/store', ['as' => 'realqassessment.scales.store', 'uses' => 'RealQAssessmentScaleController@store']);
    Route::get('realq-assessment/scales/edit/{id}', ['as' => 'realqassessment.scales.edit', 'uses' => 'RealQAssessmentScaleController@edit']);
    Route::post('realq-assessment/scales/update', ['as' => 'realqassessment.scales.update', 'uses' => 'RealQAssessmentScaleController@update']);
    Route::delete('realq-assessment/scales/delete/{id}', ['as' => 'realqassessment.scales.delete', 'uses' => 'RealQAssessmentScaleController@destroy']);

    Route::get('realq-assessment/rubrics', ['as' => 'realqassessment.rubrics.index', 'uses' => 'RealQAssessmentRubricController@index']);
    Route::get('realq-assessment/rubrics/create', ['as' => 'realqassessment.rubrics.create', 'uses' => 'RealQAssessmentRubricController@create']);
    Route::post('realq-assessment/rubrics/store', ['as' => 'realqassessment.rubrics.store', 'uses' => 'RealQAssessmentRubricController@store']);
    Route::get('realq-assessment/rubrics/edit/{id}', ['as' => 'realqassessment.rubrics.edit', 'uses' => 'RealQAssessmentRubricController@edit']);
    Route::post('realq-assessment/rubrics/update', ['as' => 'realqassessment.rubrics.update', 'uses' => 'RealQAssessmentRubricController@update']);
    Route::delete('realq-assessment/rubrics/delete/{id}', ['as' => 'realqassessment.rubrics.delete', 'uses' => 'RealQAssessmentRubricController@destroy']);

    Route::get('realq-assessment/boards', ['as' => 'realqassessment.boards.index', 'uses' => 'StudentBoardController@index']);
    Route::get('realq-assessment/boards/create', ['as' => 'realqassessment.boards.create', 'uses' => 'StudentBoardController@create']);
    Route::post('realq-assessment/boards/store', ['as' => 'realqassessment.boards.store', 'uses' => 'StudentBoardController@store']);
    Route::get('realq-assessment/boards/edit/{id}', ['as' => 'realqassessment.boards.edit', 'uses' => 'StudentBoardController@edit']);
    Route::post('realq-assessment/boards/update', ['as' => 'realqassessment.boards.update', 'uses' => 'StudentBoardController@update']);
    Route::delete('realq-assessment/boards/delete/{id}', ['as' => 'realqassessment.boards.delete', 'uses' => 'StudentBoardController@destroy']);

    Route::get('realq-assessment/topics', ['as' => 'realqassessment.topics.index', 'uses' => 'RealQAssessmentTopicController@index']);
    Route::post('realq-assessment/topics/generate', ['as' => 'realqassessment.topics.generate', 'uses' => 'RealQAssessmentTopicController@generate']);
    Route::post('realq-assessment/topics/store', ['as' => 'realqassessment.topics.store', 'uses' => 'RealQAssessmentTopicController@storeGenerated']);
    Route::get('realq-assessment/topics/list', ['as' => 'realqassessment.topics.list', 'uses' => 'RealQAssessmentTopicController@list']);
    Route::delete('realq-assessment/topics/delete/{id}', ['as' => 'realqassessment.topics.delete', 'uses' => 'RealQAssessmentTopicController@destroy']);
    Route::get('realq-assessment/topics/{topicId}/questions', ['as' => 'realqassessment.questions.page', 'uses' => 'RealQAssessmentQuestionController@generatePage']);
    Route::post('realq-assessment/topics/{topicId}/questions/generate', ['as' => 'realqassessment.questions.generate', 'uses' => 'RealQAssessmentQuestionController@generate']);
    Route::post('realq-assessment/topics/{topicId}/questions/store', ['as' => 'realqassessment.questions.store', 'uses' => 'RealQAssessmentQuestionController@store']);
    Route::get('realq-assessment/question-bank', ['as' => 'realqassessment.questions.bank', 'uses' => 'RealQAssessmentQuestionController@questionBank']);
    Route::get('realq-assessment/question-bank/data', ['as' => 'realqassessment.questions.bank.data', 'uses' => 'RealQAssessmentQuestionController@questionBankData']);
    Route::get('realq-assessment/question-bank/{id}', ['as' => 'realqassessment.questions.bank.show', 'uses' => 'RealQAssessmentQuestionController@questionBankShow']);
    Route::post('realq-assessment/question-bank/{id}/archive', ['as' => 'realqassessment.questions.bank.archive', 'uses' => 'RealQAssessmentQuestionController@archive']);

    Route::get('realq-assessment/reports', ['as' => 'realqassessment.reports.index', 'uses' => 'RealQAssessmentReportController@index']);
    Route::post('realq-assessment/reports/{schoolId}/generate', ['as' => 'realqassessment.reports.generate', 'uses' => 'RealQAssessmentReportController@generate']);
    Route::get('realq-assessment/reports/download/{reportId}', ['as' => 'realqassessment.reports.download', 'uses' => 'RealQAssessmentReportController@download']);
    Route::get('realq-assessment/reports/preview/{schoolId}', ['as' => 'realqassessment.reports.preview', 'uses' => 'RealQAssessmentReportController@preview']);

    // Student report review workflow routes
    Route::get('realq-assessment/student-reports', ['as' => 'realqassessment.student-reports.index', 'uses' => 'RealQAssessmentStudentReportController@index']);
    Route::get('realq-assessment/student-reports/datatable', ['as' => 'realqassessment.student-reports.datatable', 'uses' => 'RealQAssessmentStudentReportController@datatable']);
    Route::get('realq-assessment/student-reports/students-by-school/{schoolId}', ['as' => 'realqassessment.student-reports.students-by-school', 'uses' => 'RealQAssessmentStudentReportController@studentsBySchool']);
    Route::post('realq-assessment/student-reports/{id}/generate', ['as' => 'realqassessment.student-reports.generate', 'uses' => 'RealQAssessmentStudentReportController@generate']);
    Route::get('realq-assessment/student-reports/{id}/edit', ['as' => 'realqassessment.student-reports.edit', 'uses' => 'RealQAssessmentStudentReportController@edit']);
    Route::put('realq-assessment/student-reports/{id}', ['as' => 'realqassessment.student-reports.update', 'uses' => 'RealQAssessmentStudentReportController@update']);
    Route::post('realq-assessment/student-reports/{id}/approve', ['as' => 'realqassessment.student-reports.approve', 'uses' => 'RealQAssessmentStudentReportController@approve']);

    // AI report accuracy dashboard
    Route::get('realq-assessment/accuracy-dashboard', ['as' => 'realqassessment.accuracy-dashboard.index', 'uses' => 'RealQAccuracyDashboardController@index']);
    Route::get('realq-assessment/accuracy-dashboard/data', ['as' => 'realqassessment.accuracy-dashboard.data', 'uses' => 'RealQAccuracyDashboardController@data']);

    // Standard Assessment - Categories
    Route::get('standard-assessment/categories/list', ['as' => 'standard_assessment.categories.list', 'uses' => 'StandardAssessmentCategoryController@categoryList']);
    Route::get('standard-assessment/categories/data', ['as' => 'standard_assessment.categories.data', 'uses' => 'StandardAssessmentCategoryController@getCategories']);
    Route::get('standard-assessment/categories/create', ['as' => 'standard_assessment.categories.create', 'uses' => 'StandardAssessmentCategoryController@categoryCreate']);
    Route::post('standard-assessment/categories/store', ['as' => 'standard_assessment.categories.store', 'uses' => 'StandardAssessmentCategoryController@categoryStore']);
    Route::get('standard-assessment/categories/{id}/edit', ['as' => 'standard_assessment.categories.edit', 'uses' => 'StandardAssessmentCategoryController@categoryEdit']);
    Route::put('standard-assessment/categories/{id}', ['as' => 'standard_assessment.categories.update', 'uses' => 'StandardAssessmentCategoryController@categoryUpdate']);
    Route::delete('standard-assessment/categories/{id}', ['as' => 'standard_assessment.categories.delete', 'uses' => 'StandardAssessmentCategoryController@categoryDelete']);

    // Standard Assessment - Questionnaire
    Route::get('standard-assessment/questions/list', ['as' => 'standard_assessment.questions.list', 'uses' => 'StandardAssessmentQuestionController@questionList']);
    Route::get('standard-assessment/questions/data', ['as' => 'standard_assessment.questions.data', 'uses' => 'StandardAssessmentQuestionController@getQuestions']);
    Route::get('standard-assessment/questions/create', ['as' => 'standard_assessment.questions.create', 'uses' => 'StandardAssessmentQuestionController@questionCreate']);
    Route::post('standard-assessment/questions/store', ['as' => 'standard_assessment.questions.store', 'uses' => 'StandardAssessmentQuestionController@questionStore']);
    Route::get('standard-assessment/questions/{id}', ['as' => 'standard_assessment.questions.show', 'uses' => 'StandardAssessmentQuestionController@questionShow']);
    Route::get('standard-assessment/questions/{id}/edit', ['as' => 'standard_assessment.questions.edit', 'uses' => 'StandardAssessmentQuestionController@questionEdit']);
    Route::put('standard-assessment/questions/{id}', ['as' => 'standard_assessment.questions.update', 'uses' => 'StandardAssessmentQuestionController@questionUpdate']);
    Route::get('standard-assessment/questions/{que_id}/set-option-c-weightage', ['as' => 'standard_assessment.questions.set_option_c_weightage', 'uses' => 'StandardAssessmentQuestionController@setOptionCWeightage']);
    Route::delete('standard-assessment/questions/{id}', ['as' => 'standard_assessment.questions.delete', 'uses' => 'StandardAssessmentQuestionController@questionDelete']);

    // Standard Assessment - Export
    Route::get('standard-assessment/export', ['as' => 'standard_assessment.export.index', 'uses' => 'StandardAssessmentExportController@index']);
    Route::get('standard-assessment/export/download', ['as' => 'standard_assessment.export.download', 'uses' => 'StandardAssessmentExportController@export']);
    Route::get('standard-assessment/attempts', ['as' => 'standard_assessment.attempts.list', 'uses' => 'StandardAssessmentAttemptController@index']);
    Route::get('standard-assessment/attempts/data', ['as' => 'standard_assessment.attempts.data', 'uses' => 'StandardAssessmentAttemptController@data']);
    Route::post('standard-assessment/attempts/reset', ['as' => 'standard_assessment.attempts.reset', 'uses' => 'StandardAssessmentAttemptController@reset']);
    Route::get('standard-assessment/attempts/export', ['as' => 'standard_assessment.attempts.export', 'uses' => 'StandardAssessmentAttemptController@export']);
    Route::get('standard-assessment/attempts/assign-reward-points', ['as' => 'standard_assessment.attempts.assign_reward_points', 'uses' => 'StandardAssessmentAttemptController@assignRewardPoints']);

    // School Progress Report
    Route::get('view/progress/{school}', ['as' => 'viewProgress', 'uses' => 'SchoolController@viewProgress']);
    Route::post('getProgressByGrade', ['uses' => 'SchoolController@getProgressByGrade', 'as' => 'getProgressByGrade']);
    Route::post('generate/leaderboard', ['uses' => 'SchoolController@generateLeaderBoard', 'as' => 'generateLeaderBoard']);
    Route::post('/student/info/', ['uses' => 'SchoolController@studentInfo', 'as' => 'student-info']);
    Route::post('reward/details', ['uses' => 'SchoolController@getRewardPointDetails', 'as' => 'getRewardPointDetails']);
    Route::post('progress/student-observations', ['uses' => 'SchoolController@getStudentObservations', 'as' => 'getStudentObservations']);
    
    // School notification
    Route::get('school/notificationbox/', ['as' => 'school-notificationbox', 'uses' => 'SchoolController@schoolNotificationBox']);
    Route::get('school/compose/', ['as' => 'school-compose', 'uses' => 'SchoolController@schoolCompose']);
    Route::post('school/notification/send/', ['as' => 'school-notification-send', 'uses' => 'SchoolController@schoolNotificationSend']);

    Route::get('school/suspend/{id}', ['as' => 'school-suspend', 'uses' => 'SchoolController@schoolSuspend']);
    Route::get('school/unsuspend/{id}', ['as' => 'school-unsuspend', 'uses' => 'SchoolController@schoolUnsuspend']);

    //event section---------------------
    Route::get('createevent', ['as' => 'createevent.createevent', 'uses' => 'EventController@createevent']);
    Route::post('eventstore', ['as' => 'eventstore.eventstore', 'uses' => 'EventController@eventstore']);
    Route::get('eventlist', ['as' => 'eventlist.eventlist', 'uses' => 'EventController@eventlist']);
    Route::get('viewevent/{id}', ['as' => 'viewevent.viewevent', 'uses' => 'EventController@viewevent']);
    Route::get('editevent/{id}', ['as' => 'editevent.editevent', 'uses' => 'EventController@editevent']);
    Route::post('eventupdate', ['as' => 'eventupdate.eventupdate', 'uses' => 'EventController@eventupdate']);
    Route::get('eventdelete/{id}', ['as' => 'eventdelete.eventdelete', 'uses' => 'EventController@eventdelete']);
    Route::get('viewchallenge/{id}', ['as' => 'viewchallenge.viewchallenge', 'uses' => 'EventController@viewchallenge']);
    Route::get('student-challenge/attachment-download/{eventChallengeId}', ['as' => 'attachmentdownload.attachmentdownload', 'uses' => 'EventController@attachmentdownload']);
    Route::post('deleteHighlight', ['as' => 'deleteHighlight', 'uses' => 'EventController@deleteHighlight']);
    Route::post('deletePoster', ['as' => 'deletePoster', 'uses' => 'EventController@deletePoster']);

    Route::get('daily/challenge/list', ['as' => 'weeklyChallengeList', 'uses' => 'WeeklyChallengeController@weeklyChallengeList']);
    Route::get('daily/challenge/add', ['as' => 'weeklyChallengeAdd', 'uses' => 'WeeklyChallengeController@weeklyChallengeAdd']);
    Route::post('weeklychallengestore', ['as' => 'weeklyChallengeStore', 'uses' => 'WeeklyChallengeController@weeklyChallengeStore']);
    Route::get('weekly/challenge/edit/{id}', ['as' => 'weeklyChallengeEdit', 'uses' => 'WeeklyChallengeController@weeklyChallengeEdit']);
    Route::post('weeklychallengeupdate', ['as' => 'weeklyChallengeUpdate', 'uses' => 'WeeklyChallengeController@weeklyChallengeUpdate']);
    Route::post('deleteweeklychallengeimage', ['as' => 'deleteWeeklyChallengeImage', 'uses' => 'WeeklyChallengeController@deleteWeeklyChallengeImage']);
    Route::get('weekly/challenge/delete/{id}/{type}', ['as' => 'weeklyChallengeDelete', 'uses' => 'WeeklyChallengeController@weeklyChallengeDelete']);
    Route::post('weekly/delete/question', ['as' => 'weekly.deleteQuestion', 'uses' => 'WeeklyChallengeController@deleteQuestion']);
    Route::post('weekly/disable/question', ['as' => 'weekly.disableQuestion', 'uses' => 'WeeklyChallengeController@disableQuestion']);
    Route::post('weekly/enable/question', ['as' => 'weekly.enableQuestion', 'uses' => 'WeeklyChallengeController@enableQuestion']);

    Route::post('/settings/daily-quiz/status', ['as' => 'settings.updateDailyQuizStatus', 'uses' => 'ModuleSettingController@updateDailyQuizSatus']);

    Route::get('create/how-it-works', ['as' => 'createvideo.createVideo', 'uses' => 'HowItWorksController@createVideo']);
    Route::post('store/how-it-works/', ['as' => 'storevideo.storeVideo', 'uses' => 'HowItWorksController@storeVideo']);
   
    // Trainer Onboarding
    Route::get('addtrainer', ['as' => 'addtrainer.addTrainer', 'uses' => 'TrainerController@addTrainer']);
    Route::post('storetrainer', ['as' => 'storetrainer.storeTrainer', 'uses' => 'TrainerController@storeTrainer']);
    Route::get('trainerlist', ['as' => 'trainerlist.trainerList', 'uses' => 'TrainerController@trainerList']);
    Route::get('traineredit/{id}', ['as' => 'traineredit.trainerEdit', 'uses' => 'TrainerController@trainerEdit']);
    Route::post('traineredit', ['as' => 'updatetrainer.updateTrainer', 'uses' => 'TrainerController@updateTrainer']);
    Route::get('trainerdelete/{id}', ['as' => 'trainerdelete.trainerDelete', 'uses' => 'TrainerController@trainerDelete']);
    Route::post('deleteprofileimage', ['as' => 'deleteTrainerProfileImage', 'uses' => 'TrainerController@deleteTrainerProfileImage']);
    Route::post('deleteattachment', ['as' => 'deleteTrainerAttachment', 'uses' => 'TrainerController@deleteTrainerAttachment']);
    Route::post('deletecv', ['as' => 'deleteTrainerCV', 'uses' => 'TrainerController@deleteTrainerCV']);

    // Trainer notification
    Route::get('trainer/notificationbox/', ['as' => 'trainer-notificationbox', 'uses' => 'TrainerController@trainerNotificationBox']);
    Route::get('trainer/compose/', ['as' => 'trainer-compose', 'uses' => 'TrainerController@trainerCompose']);
    Route::post('trainer/notification/send/', ['as' => 'trainer-notification-send', 'uses' => 'TrainerController@trainerNotificationSend']);

    Route::post('trainer/checkinfo/', ['as' => 'trainer-checkinfo', 'uses' => 'TrainerController@trainerCheckInfo']);
    Route::get('trainer/suspend/{id}', ['as' => 'trainer-suspend', 'uses' => 'TrainerController@trainerSuspend']);
    Route::get('trainer/unsuspend/{id}', ['as' => 'trainer-unsuspend', 'uses' => 'TrainerController@trainerUnsuspend']);

    // Route::get('trainer/request', ['as' => 'trainer-request', 'uses' => 'TrainerController@allowCertificate']);
    // Route::post('trainer/upload', ['as' => 'trainer-request-upload', 'uses' => 'TrainerController@uploadCertificate']);
    // Route::get('student/request', ['as' => 'student-request', 'uses' => 'StudentCommunication@allowCertificate']);
    // Route::post('student/upload', ['as' => 'student-request-upload', 'uses' => 'StudentCommunication@uploadCertificate']);

    //Trainer Allocation------------------
    Route::get('trainerallocation', ['as' => 'trainerallocation.trainerallocation', 'uses' => 'TrainerAllocationController@trainerallocation']);
    Route::get('/trainer_allocation_list_datatable', ['uses' => 'TrainerAllocationController@trainer_allocation_list_datatable', 'as' => 'trainer_allocation_list_datatable']);
    Route::get('/allocate/trainer', ['uses' => 'TrainerAllocationController@allocateTrainer', 'as' => 'trainer_allocation.create']);
    Route::post('/allocate/trainer/save', ['uses' => 'TrainerAllocationController@storeAllocateTrainer', 'as' => 'trainer_allocation.store']);
    Route::get('/allocate/trainer/edit/{id}', ['uses' => 'TrainerAllocationController@editAllocateTrainer', 'as' => 'trainer_allocation.edit']);
    Route::post('/allocate/trainer/edit', ['uses' => 'TrainerAllocationController@updateAllocateTrainer', 'as' => 'trainer_allocation.update']);
    Route::get('/delete/trainer/allocation/{id}', ['as' => 'trainer_allocation.delete', 'uses' => 'TrainerAllocationController@deleteTrainerAllocation']);
    Route::post('getschoolsbytrainer', ['as' => 'getSchoolsByTrainer', 'uses' => 'TrainerAllocationController@getSchoolsByTrainer']);
    Route::post('getschoolbatch', ['as' => 'getAllSchoolBatch', 'uses' => 'TrainerAllocationController@getAllSchoolBatch']);
    Route::get('alltainer', ['as' => 'alltainer.alltainer', 'uses' => 'TrainerAllocationController@alltainer']);
    Route::get('trainer_schedule_show', ['as' => 'trainer_schedule_show.trainer_schedule_show', 'uses' => 'TrainerAllocationController@trainer_schedule_show']);
    Route::get('trainer_class_schedule', ['as' => 'trainer_class_schedule.trainer_class_schedule', 'uses' => 'TrainerAllocationController@trainer_class_schedule']);
    Route::post('assigntrainer', ['as' => 'assigntrainer.assigntrainer', 'uses' => 'TrainerAllocationController@assigntrainer']);
    Route::post('assigntrainer_delete', ['as' => 'assigntrainer_delete', 'uses' => 'TrainerAllocationController@assigntrainer_delete']);
    Route::get('event_insert', ['as' => 'event_insert.event_insert', 'uses' => 'TrainerAllocationController@event_insert']);
    Route::get('trainerallocation/school', ['as' => 'trainerallocation.dayschoolclassschedule', 'uses' => 'TrainerAllocationController@daySchoolClassSchedule']);
    Route::post('trainerallocation/classschedule', ['as' => 'classschedule', 'uses' => 'TrainerAllocationController@classSchedule']);

    // Student Communication
    Route::get('/assignment/create/', ['uses' => 'StudentCommunication@createAssignment', 'as' => 'create-assignment']);
    Route::get('/assignmentlist/', ['uses' => 'StudentCommunication@assignmentList', 'as' => 'assignmentlist']);
    Route::post('/assignment/save/', ['uses' => 'StudentCommunication@saveAssignment', 'as' => 'save-assignment']);
    Route::post('/multi/assignment/', ['uses' => 'StudentCommunication@multiAssignment', 'as' => 'multi-assignment']);
    Route::get('/assignment/edit/{id}', ['uses' => 'StudentCommunication@editAssignment', 'as' => 'edit-assignment']);
    Route::post('/assignment/update/', ['uses' => 'StudentCommunication@updateAssignment', 'as' => 'update-assignment']);
    Route::get('/assignment/delete/{id}', ['uses' => 'StudentCommunication@deleteAssignment', 'as' => 'delete-assignment']);
    Route::post('/assignment/delete/assignmentFile', ['uses' => 'StudentCommunication@deleteAssignmentFile', 'as' => 'delete-assignment-file']);
    Route::post('/school/trainers/', ['uses' => 'StudentCommunication@getSchoolTrainers', 'as' => 'getSchoolTrainers']);
    Route::post('/assignment/delete/icon', ['uses' => 'StudentCommunication@deleteAssignmentIcon', 'as' => 'delete-assignment-icon']);
    Route::post('/assignment/deleteScormFile', ['as' => 'assignment.deleteAssignmentScormFile', 'uses' => 'StudentCommunication@deleteAssignmentScormFile']);
    Route::post('assignment/uploadSCORMFile', ['as' => 'assignment.uploadAssignmentSCORMFile', 'uses' => 'StudentCommunication@uploadAssignmentSCORMFile']);

    // Trainer Content
    Route::get('contentdelete/{id}', ['as' => 'contentdelete.contentDelete', 'uses' => 'TrainerContentController@contentDelete']);
    Route::post('contentupdate', ['as' => 'updatecontent.updateContent', 'uses' => 'TrainerContentController@updateContent']);
    Route::get('contentedit/{id}', ['as' => 'contentedit.contentEdit', 'uses' => 'TrainerContentController@contentEdit']);
    Route::get('contentview/{id}', ['as' => 'contentview.contentView', 'uses' => 'TrainerContentController@contentView']);
    Route::get('contentlist/{grade}/grade', ['as' => 'contentlist.streamlist', 'uses' => 'TrainerContentController@streamList']);
    Route::get('contentlist', ['as' => 'contentlist.contentList', 'uses' => 'TrainerContentController@contentList']);
    Route::post('storecontent', ['as' => 'storecontent.storeContent', 'uses' => 'TrainerContentController@storeContent']);
    Route::get('addcontent', ['as' => 'addcontent.addContent', 'uses' => 'TrainerContentController@addContent']);
    Route::post('addtrainerstream', ['as' => 'addtrainerttream.addTrainerStream', 'uses' => 'TrainerContentController@addTrainerStream']);
    Route::post('edittrainerstream', ['as' => 'editTrainerStream.editTrainerStream', 'uses' => 'TrainerContentController@editTrainerStream']);
    Route::post('deleteTrainerStream', ['as' => 'deleteTrainerStream.deleteTrainerStream', 'uses' => 'TrainerContentController@deleteTrainerStream']);
    Route::post('destorytrainerstream', ['as' => 'destorytrainerstream.destoryTrainerStream', 'uses' => 'TrainerContentController@destoryTrainerStream']);
    Route::post('changetrainerstream', ['as' => 'changetrainerstream.changeTrainerStream', 'uses' => 'TrainerContentController@changeTrainerStream']);
    Route::post('updateordertrainerstream', ['as' => 'updateTrainerstream.updateOrderTrainerstream', 'uses' => 'TrainerContentController@updateOrderTrainerstream']);
    Route::post('updateordertrainercontent', ['as' => 'updateTrainercontent.updateOrderTrainercontent', 'uses' => 'TrainerContentController@updateOrderTrainercontent']);
    Route::post('updateorderstudentstream', ['as' => 'updateStudentstream.updateOrderStudentstream', 'uses' => 'ContentController@updateOrderStudentstream']);
    Route::post('updateorderstudentcontent', ['as' => 'updateStudentcontent.updateOrderStudentcontent', 'uses' => 'ContentController@updateOrderStudentcontent']);
    Route::get('contentlist/students', ['as' => 'addcontent.contentListStudents', 'uses' => 'ContentController@index']);
    Route::get('contentlist/students/{grade}/grade', ['as' => 'addcontent.streamcontentStudents', 'uses' => 'ContentController@stream']);
    Route::get('contentlist/students/addcontent', ['as' => 'addcontent.addContentStudents', 'uses' => 'ContentController@create']);
    Route::post('contentlist/students/addcontent', ['as' => 'addcontent.addContentStudents1', 'uses' => 'ContentController@store']);
    Route::get('contentlist/students/{studentscontents}', ['as' => 'addcontent.viewContentStudents', 'uses' => 'ContentController@show']);
    Route::get('contentlist/students/{studentscontents}/edit', ['as' => 'addcontent.getEditContentStudents', 'uses' => 'ContentController@edit']);
    Route::patch('contentlist/students/{studentscontents}/edit', ['as' => 'addcontent.editContentStudents', 'uses' => 'ContentController@update']);
    Route::get('contentlist/students/{studentscontents}/delete', ['as' => 'addcontent.deleteContentStudents', 'uses' => 'ContentController@destory']);
    Route::post('editstream', ['as' => 'editstream.editstream', 'uses' => 'ContentController@editStream']);
    Route::post('deleteStream', ['as' => 'deleteStream.deleteStream', 'uses' => 'ContentController@deleteStream']);
    Route::post('changestudentstream', ['as' => 'changestudentstream.changeStudentStream', 'uses' => 'ContentController@changeStudentStream']);
    Route::post('getStreamSession', ['as' => 'getStreamSession.getStreamSession', 'uses' => 'ContentController@getStreamSession']);

    Route::get('quizResult/{id}', ['as' => 'quizResult', 'uses' => 'ContentController@quizResult']);
    Route::post('quizResultFilter', ['as' => 'quizResultFilter', 'uses' => 'ContentController@quizResultFilter']);

    Route::post('addstream', ['as' => 'addstream.addStream', 'uses' => 'ContentController@addStream']);
    Route::post('destorystream', ['as' => 'addstream.destoryStream', 'uses' => 'ContentController@destoryStream']);

    Route::post('content/upload-file', ['as' => 'trainerContent.uploadFile', 'uses' => 'TrainerContentController@uploadFile']);

    Route::post('addagegroup', ['as' => 'addagegroup.addAgeGroup', 'uses' => 'ContentController@addAgeGroup']);

    // Other setting of Admin
    Route::get('resources', ['as' => 'resources.resources', 'uses' => 'AdminsettingController@resources']);
    Route::post('get_resource_value', ['as' => 'get_resource_value', 'uses' => 'AdminsettingController@getResourceValue']);
    Route::post('save_resource_value', ['as' => 'save_resource_value', 'uses' => 'AdminsettingController@saveResourceValue']);

    // Route::get("teacher", ['as' => "teacher", 'uses' => "AdminsettingController@teacher"]);
    Route::get('trainerterms', ['as' => 'trainerterms.trainerTerms', 'uses' => 'AdminsettingController@trainerTerms']);
    Route::get('studentterms', ['as' => 'studentterms.studentTerms', 'uses' => 'AdminsettingController@studentTerms']);
    Route::get('schoolterms', ['as' => 'schoolterms.schoolTerms', 'uses' => 'AdminsettingController@schoolTerms']);

    // Route::post("updatetrainerguide", ['as' => "updatetrainerguide.updateTrainerguide", 'uses' => "AdminsettingController@updateTrainerguide"]);
    Route::post('updateresources', ['as' => 'updateresources.updateResources', 'uses' => 'AdminsettingController@updateResources']);

    Route::get('level', ['as' => 'level.index', 'uses' => 'LevelController@index']);
    Route::get('level/create', ['as' => 'level.create', 'uses' => 'LevelController@create']);
    Route::post('level', ['as' => 'level.store', 'uses' => 'LevelController@store']);
    Route::get('level/{grade}/delete', ['as' => 'level.delete', 'uses' => 'LevelController@destory']);
    Route::get('level/{grade}/edit', ['as' => 'level.edit', 'uses' => 'LevelController@edit']);
    Route::patch('level/{grade}/update', ['as' => 'level.update', 'uses' => 'LevelController@update']);
    Route::post('level/imagedelete', ['as' => 'level.gradeimagedelete', 'uses' => 'LevelController@imageDelete']);

    Route::get('stream', ['as' => 'stream.index', 'uses' => 'StreamController@index']);
    Route::get('stream/create', ['as' => 'stream.create', 'uses' => 'StreamController@create']);
    Route::post('stream', ['as' => 'stream.store', 'uses' => 'StreamController@store']);
    Route::get('stream/{level}/edit', ['as' => 'stream.edit', 'uses' => 'StreamController@edit']);
    Route::patch('stream/{level}/update', ['as' => 'stream.update', 'uses' => 'StreamController@update']);
    Route::get('stream/{level}/delete', ['as' => 'stream.delete', 'uses' => 'StreamController@destory']);
    Route::post('deleteScormFile', ['as' => 'deleteScormFile', 'uses' => 'StreamController@deleteScormFile']);
    Route::post('stream/uploadSCORMFile', ['as' => 'stream.uploadSCORMFile', 'uses' => 'StreamController@uploadSCORMFile']);

    Route::get('trainerstream', ['as' => 'trainerstream.index', 'uses' => 'TrainerStreamController@index']);
    Route::get('trainerstream/create', ['as' => 'trainerstream.create', 'uses' => 'TrainerStreamController@create']);
    Route::post('trainerstream/store', ['as' => 'trainerstream.store', 'uses' => 'TrainerStreamController@store']);
    Route::get('trainerstream/{level}/edit', ['as' => 'trainerstream.edit', 'uses' => 'TrainerStreamController@edit']);
    Route::patch('trainerstream/{level}/update', ['as' => 'trainerstream.update', 'uses' => 'TrainerStreamController@update']);
    Route::get('trainerstream/{level}/delete', ['as' => 'trainerstream.delete', 'uses' => 'TrainerStreamController@destory']);
    Route::post('deleteTrainerStreamVideo', ['as' => 'trainerstream.deleteTrainerStreamVideo', 'uses' => 'TrainerStreamController@deleteStreamVideo']);
    Route::post('deleteTrainerStreamImage', ['as' => 'trainerstream.deleteTrainerStreamImage', 'uses' => 'TrainerStreamController@deleteStreamImage']);
    Route::post('deleteTrainerStreamPdf', ['as' => 'trainerstream.deleteTrainerStreamPdf', 'uses' => 'TrainerStreamController@deleteStreamPdf']);
    Route::post('deleteTrainerStreamWorksheet', ['as' => 'trainerstream.deleteTrainerStreamWorksheet', 'uses' => 'TrainerStreamController@deleteStreamWorksheet']);

    Route::get('observations/list', ['as' => 'observation_list', 'uses' => 'ObservationController@index']);
    Route::get('observation/create', ['as' => 'observation.create', 'uses' => 'ObservationController@create']);
    Route::post('observation/store', ['as' => 'observation.store', 'uses' => 'ObservationController@store']);
    Route::get('observation/edit/{id}', ['as' => 'observation.edit', 'uses' => 'ObservationController@edit']);
    Route::post('observation/update', ['as' => 'observation.update', 'uses' => 'ObservationController@update']);
    Route::get('observation/delete/{id}', ['as' => 'observation.delete', 'uses' => 'ObservationController@destory']);
    Route::get('observations/all', ['as' => 'observations.all', 'uses' => 'ObservationController@viewAllObservations']);
    Route::get('observations/all/download', ['as' => 'observations.all.download', 'uses' => 'ObservationController@downloadObservationsPdf']);
    Route::get('observations/all/download-student-reports', ['as' => 'observations.all.download_student_reports', 'uses' => 'ObservationController@downloadStudentObservationReports']);
    Route::get('observations/session/{sessionId}', ['as' => 'observations.session.detail', 'uses' => 'ObservationController@viewSessionObservations']);

    Route::get('mindset/list', ['as' => 'mindset_list', 'uses' => 'MindsetController@index']);
    Route::get('mindset/create', ['as' => 'mindset.create', 'uses' => 'MindsetController@create']);
    Route::post('mindset/store', ['as' => 'mindset.store', 'uses' => 'MindsetController@store']);
    Route::get('mindset/edit/{id}', ['as' => 'mindset.edit', 'uses' => 'MindsetController@edit']);
    Route::post('mindset/update', ['as' => 'mindset.update', 'uses' => 'MindsetController@update']);
    Route::get('mindset/delete/{id}', ['as' => 'mindset.delete', 'uses' => 'MindsetController@destory']);

    // Route::get('export/observation', ['as' => 'observation.exportData', 'uses' => 'ObservationController@exportData']);
    // Route::post('download/observation', ['as' => 'observation.downloadData', 'uses' => 'ObservationController@downloadData']);

    // Route::resource('trainerlevel', TrainerLavelController::class);
    // Route::resource('trainerlevel', ['as' => 'trainerlevel', 'uses' => LevelController::class]);
    Route::get('trainerlevel', ['as' => 'trainerlevel.index', 'uses' => 'TrainerLavelController@index']);
    Route::get('trainerlevel/create', ['as' => 'trainerlevel.create', 'uses' => 'TrainerLavelController@create']);
    Route::post('trainerlevel', ['as' => 'trainerlevel.store', 'uses' => 'TrainerLavelController@store']);
    Route::get('trainerlevel/{trainerlevel}/delete', ['as' => 'trainerlevel.delete', 'uses' => 'TrainerLavelController@destroy']);
    Route::get('trainerlevel/{trainerlevel}/edit', ['as' => 'trainerlevel.edit', 'uses' => 'TrainerLavelController@edit']);
    Route::patch('trainerlevel/{trainerlevel}/update', ['as' => 'trainerlevel.update', 'uses' => 'TrainerLavelController@update']);
    Route::post('trainerlevel/imagedelete', ['as' => 'trainerlevel.gradeimagedelete', 'uses' => 'TrainerLavelController@imageDelete']);

    //External Session Routes
    Route::get('external_session/list', ['as' => 'external_session.list', 'uses' => 'ExternalSessionController@index']);
    Route::get('external-session/batches-by-schools', ['as' => 'external_session.batches_by_schools', 'uses' => 'ExternalSessionController@getBatchesBySchools']);
    Route::get('external-session/trainers-by-school', ['as' => 'external_session.trainers_by_school', 'uses' => 'ExternalSessionController@getTrainersBySchool']);
    Route::get('external_session/create', ['as' => 'external_session.create', 'uses' => 'ExternalSessionController@create']);
    Route::get('external_session/duplicate/{external_session}', ['as' => 'external_session.duplicate', 'uses' => 'ExternalSessionController@duplicate']);
    Route::post('external_session/store', ['as' => 'external_session.store', 'uses' => 'ExternalSessionController@store']);
    Route::get('external_session/edit/{external_session}', ['as' => 'external_session.edit', 'uses' => 'ExternalSessionController@edit']);
    Route::put('external_session/update/{external_session}', ['as' => 'external_session.update', 'uses' => 'ExternalSessionController@update']);
    Route::delete('/external_session/{id}', ['as' => 'external_session.delete', 'uses' => 'ExternalSessionController@destroy']);
    Route::get('/external_session/{id}/cancel', ['as' => 'external_session.cancel', 'uses' => 'ExternalSessionController@cancel']);
    Route::get('external_session/calendar', ['as' => 'external_session.calendar', 'uses' => 'ExternalSessionController@calendar']);
    Route::get('external_session/calendar-data', ['as' => 'external_session.calendar_data', 'uses' => 'ExternalSessionController@calendarData']);
    Route::post('external_session/delete-occurrence', ['as' => 'external_session.deleteOccurrence', 'uses' => 'ExternalSessionController@deleteOccurrence']);
    Route::get('/update_username_script/{school_id}', ['as' => 'update_username_script', 'uses' => 'SchoolController@updateUsernameScript']);
    Route::get('/delete_orphan_user_script/{school_id}/{action?}', ['as' => 'delete_orphan_user_script', 'uses' => 'SchoolController@deleteOrphanUserScript']);

    // Missing certificates dashboard
    Route::get('certificates/missing', ['as' => 'certificates.missing', 'uses' => 'CertificateAuditController@missingCertificates']);
    Route::post('certificates/{id}/regenerate', ['as' => 'certificates.regenerate', 'uses' => 'CertificateAuditController@regenerateCertificate']);

    /*
     *
     *  Settings Routes
     *
     * ---------------------------------------------------------------------
     */
    Route::group(['middleware' => ['permission:edit_settings']], function () {
        $module_name = 'settings';
        $controller_name = 'SettingController';
        Route::get("$module_name", "$controller_name@index")->name("$module_name");
        Route::post("$module_name", "$controller_name@store")->name("$module_name.store");
    });

    /*
    *
    *  Notification Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'notifications';
    $controller_name = 'NotificationsController';
    Route::get("$module_name", ['as' => "$module_name.index", 'uses' => "$controller_name@index"]);
    Route::get("$module_name/markAllAsRead", ['as' => "$module_name.markAllAsRead", 'uses' => "$controller_name@markAllAsRead"]);
    Route::delete("$module_name/deleteAll", ['as' => "$module_name.deleteAll", 'uses' => "$controller_name@deleteAll"]);
    Route::get("$module_name/{id}", ['as' => "$module_name.show", 'uses' => "$controller_name@show"]);

    /*
    *
    *  Backup Routes
    *
    * ---------------------------------------------------------------------
    */

    $module_name = 'backups';
    $controller_name = 'BackupController';
    Route::get("$module_name", ['as' => "$module_name.index", 'uses' => "$controller_name@index"]);
    Route::get("$module_name/create", ['as' => "$module_name.create", 'uses' => "$controller_name@create"]);
    Route::get("$module_name/download/{file_name}", ['as' => "$module_name.download", 'uses' => "$controller_name@download"]);
    Route::get("$module_name/delete/{file_name}", ['as' => "$module_name.delete", 'uses' => "$controller_name@delete"]);

    /*
    *
    *  Roles Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'roles';
    $controller_name = 'RolesController';
    Route::resource("$module_name", "$controller_name");

    /*
    *
    *  Users Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'users';
    $controller_name = 'UserController';
    Route::get("$module_name/profile/{id}", ['as' => "$module_name.profile", 'uses' => "$controller_name@profile"]);
    Route::get("$module_name/profile/{id}/edit", ['as' => "$module_name.profileEdit", 'uses' => "$controller_name@profileEdit"]);
    Route::patch("$module_name/profile/{id}/edit", ['as' => "$module_name.profileUpdate", 'uses' => "$controller_name@profileUpdate"]);
    Route::get("$module_name/emailConfirmationResend/{id}", ['as' => "$module_name.emailConfirmationResend", 'uses' => "$controller_name@emailConfirmationResend"]);
    Route::delete("$module_name/userProviderDestroy", ['as' => "$module_name.userProviderDestroy", 'uses' => "$controller_name@userProviderDestroy"]);
    Route::get("$module_name/profile/changeProfilePassword/{id}", ['as' => "$module_name.changeProfilePassword", 'uses' => "$controller_name@changeProfilePassword"]);
    Route::patch("$module_name/profile/changeProfilePassword/{id}", ['as' => "$module_name.changeProfilePasswordUpdate", 'uses' => "$controller_name@changeProfilePasswordUpdate"]);
    Route::get("$module_name/changePassword/{id}", ['as' => "$module_name.changePassword", 'uses' => "$controller_name@changePassword"]);
    Route::patch("$module_name/changePassword/{id}", ['as' => "$module_name.changePasswordUpdate", 'uses' => "$controller_name@changePasswordUpdate"]);
    Route::get("$module_name/trashed", ['as' => "$module_name.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/trashed/{id}", ['as' => "$module_name.restore", 'uses' => "$controller_name@restore"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::get("$module_name/index_list", ['as' => "$module_name.index_list", 'uses' => "$controller_name@index_list"]);
    Route::resource("$module_name", "$controller_name");
    Route::patch("$module_name/{id}/block", ['as' => "$module_name.block", 'uses' => "$controller_name@block", 'middleware' => ['permission:block_users']]);
    Route::patch("$module_name/{id}/unblock", ['as' => "$module_name.unblock", 'uses' => "$controller_name@unblock", 'middleware' => ['permission:block_users']]);

    /*
    *
    *  Partner Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'partners';
    $controller_name = 'PartnerController';
    Route::get("$module_name/check-unique", ['as' => "$module_name.checkUnique", 'uses' => "$controller_name@checkUnique"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::resource("$module_name", "$controller_name");
});

// =====================  School Section =================
Route::group(['namespace' => 'School', 'prefix' => 'school', 'as' => 'school.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:2']], function () {
    /*
     * Backend Dashboard
     * Namespaces indicate folder structure.
     */
    // School Dashboard
    Route::get('dashboard', 'ManageschoolController@index')->name('dashboard');

    // School Profile
    Route::get('/profile/edit/', ['uses' => 'ManageschoolController@profileEdit', 'as' => 'profile-edit']);
    Route::get('/profile/deletestudents/{id}', ['uses' => 'ManageschoolController@studentDelete', 'as' => 'student-delete-all']);
    Route::post('/profile/update/', ['uses' => 'ManageschoolController@updateSchool', 'as' => 'update-school']);
    Route::post('/profile/deleteSchoolLogo', ['as' => 'deleteSchoolLogo', 'uses' => 'ManageschoolController@deleteSchoolLogo']);
    Route::post('/profile/deleteSchoolCoverLogo', ['as' => 'deleteSchoolCoverLogo', 'uses' => 'ManageschoolController@deleteSchoolCoverLogo']);
    // School Event
    Route::get('/event/list/', ['uses' => 'ManageschoolController@eventList', 'as' => 'event-list']);
    Route::get('/event/view/{id}', ['uses' => 'ManageschoolController@eventView', 'as' => 'event-view']);

    // School Privacy
    Route::get('/privacy/police/', ['uses' => 'ManageschoolController@privacyPolice', 'as' => 'privacy-police']);
    Route::post('/privacy/police/save/', ['uses' => 'ManageschoolController@savePrivacyPolice', 'as' => 'save-privacy-police']);

    // Student Progress Report
    Route::get('/progress/report/', ['uses' => 'ProgressreportController@progressReport', 'as' => 'progress-report']);
    Route::post('/student/info/', ['uses' => 'ProgressreportController@studentInfo', 'as' => 'student-info']);
    Route::post('getProgressByGrade', ['uses' => 'ProgressreportController@getProgressByGrade', 'as' => 'getProgressByGrade']);
    Route::post('generate/leaderboard', ['uses' => 'ProgressreportController@generateLeaderBoard', 'as' => 'generateLeaderBoard']);
    Route::post('reward/details', ['uses' => 'ProgressreportController@getRewardPointDetails', 'as' => 'getRewardPointDetails']);

    // Batch List
    Route::get('/batch/list/', ['uses' => 'BatchController@index', 'as' => 'batch-list']);
    Route::get('/batch/create', ['as' => 'batch.create', 'uses' => 'BatchController@create']);
    Route::post('/batch/store', ['as' => 'batch.store', 'uses' => 'BatchController@store']);
    Route::get('/batch/edit/{id}', ['as' => 'batch.edit', 'uses' => 'BatchController@edit']);
    Route::post('/batch/update', ['as' => 'batch.update', 'uses' => 'BatchController@update']);
    Route::get('/batch/delete/{id}', ['as' => 'batch.delete', 'uses' => 'BatchController@destory']);

    // Student List
    Route::get('/student/list/', ['uses' => 'SchoolController@studentList', 'as' => 'student-list']);
    Route::get('/student_list_datatable', ['uses' => 'SchoolController@student_list_datatable', 'as' => 'student_list_datatable']);
    Route::get('/student/edit/{id}', ['uses' => 'SchoolController@studentEdit', 'as' => 'student-edit']);
    Route::get('/student/create', ['uses' => 'SchoolController@studentAdd', 'as' => 'student-add']);
    Route::get('/student/import', ['uses' => 'SchoolController@sudentImport', 'as' => 'student-import']);
    Route::post('/student/import/store', ['as' => 'student-import-store', 'uses' => 'SchoolController@studentImportStore']);
    Route::post('/student/store', ['uses' => 'SchoolController@studentStore', 'as' => 'student-store']);
    Route::get('/student/delete/{id}', ['uses' => 'SchoolController@studentDelete', 'as' => 'student-delete']);
    Route::post('/student/update/', ['uses' => 'SchoolController@studentUpdate', 'as' => 'student-update']);
    Route::post('/delete-student-profile-avatar', ['uses' => 'SchoolController@deleteStudentProfileAvatar', 'as' => 'delete-student-profile-avatar']);
    Route::post('/student-reset-password', ['uses' => 'SchoolController@studentResetPassword', 'as' => 'student-reset-password']);

    // Standard Assessment Settings
    Route::get('/standard-assessment', ['uses' => 'StandardAssessmentController@index', 'as' => 'standard-assessment.index']);
    Route::post('/standard-assessment', ['uses' => 'StandardAssessmentController@update', 'as' => 'standard-assessment.update']);
    
    //School Class Schedule--------------
    Route::get('Class/Schedule/', ['as' => 'Class_schedule', 'uses' => 'ClassScheduleController@Class_schedule']);
    Route::get('Class_Schedule/', ['as' => 'school_classSchedule', 'uses' => 'ClassScheduleController@school_classSchedule']);

    //School Notification-------------------------
    Route::get('notification/', ['as' => 'school-notification', 'uses' => 'NotificationController@index']);

    Route::get('/download/observation/', ['as' => 'observation.downloadData', 'uses' => 'ObservationController@downloadData']);
});

// =====================  Trainer Section =================
Route::group(['namespace' => 'Trainer', 'prefix' => 'trainer', 'as' => 'trainer.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:3']], function () {
    // Dashboard
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    // Content
    Route::get('content/list', ['as' => 'content/list.contentList', 'uses' => 'ContentController@content_list']);
    Route::get('content/view/{id}', ['as' => 'contentview.contentView', 'uses' => 'ContentController@contentView']);
    Route::get('content/{content}/finish', ['as' => 'contentview.finish', 'uses' => 'ContentController@finishVideo']);
    Route::get('content/{grade}/grade', ['as' => 'contentlist.streamlist', 'uses' => 'ContentController@streamList']);
    Route::get('content/pdf-view/{contentId}', ['as' => 'pdfView.pdfView', 'uses' => 'ContentController@pdfView']);
    Route::get('content/images-download/{contentId}', ['as' => 'imagesdownload.imagesdownload', 'uses' => 'ContentController@contentImagesDownload']);

    //Event----------------------
    Route::get('event/list', ['as' => 'event/list.eventList', 'uses' => 'EventController@event_list']);
    Route::get('event/view/{id}', ['as' => 'event/view.eventView', 'uses' => 'EventController@eventView']);

    //Trainer Class Schedule---------------
    Route::get('Class/Schedule/', ['as' => 'Class_schedule', 'uses' => 'ClassScheduleController@Class_schedule']);
    Route::get('Class_Schedule/', ['as' => 'trainer_classSchedule', 'uses' => 'ClassScheduleController@trainer_classSchedule']);
    Route::get('trainer_day_classSchedule', ['as' => 'trainer_day_class_schedule', 'uses' => 'ClassScheduleController@trainer_day_class_schedule']);
    Route::get('session-report/{sessionId}/{schoolId}/{sessionDate}', ['as' => 'session_report.get', 'uses' => 'ClassScheduleController@getSessionReport']);
    Route::post('session-report/store', ['as' => 'session_report.store', 'uses' => 'ClassScheduleController@storeSessionReport']);
    Route::delete('session-report/photo/{photoId}', ['as'=> 'session_report.photo.delete', 'uses'=> 'ClassScheduleController@deleteReportPhoto']);
    Route::get('session-students/{sessionId}/{schoolId}/{sessionDate}',['as'=>'session.students', 'uses'=>'ClassScheduleController@getSessionStudents']);
    Route::get('observations', ['as' => 'observations.list', 'uses' => 'ClassScheduleController@getObservations']);
    Route::post('student-observation/store', ['as' => 'student_observation.store', 'uses' => 'ClassScheduleController@storeStudentObservation']);
    Route::get('student-observations/{sessionId}/{schoolId}/{studentId}/{sessionDate}', ['as' => 'student_observations.get', 'uses' => 'ClassScheduleController@getStudentObservations']);
    Route::post('rephrase-anecdote', ['as' => 'rephrase_anecdote', 'uses' => 'ClassScheduleController@rephraseAnecdote']);

    //Students------------------------------
    Route::get('student/list', ['as' => 'student_list', 'uses' => 'StudentController@student_list']);
    Route::get('student/leaderboard', ['as' => 'student_leaderboard', 'uses' => 'StudentController@leaderboard']);
    Route::get('student_list', ['as' => 'student_list_datatable', 'uses' => 'StudentController@student_list_datatable']);
    Route::get('student_view/{id}', ['as' => 'student_view', 'uses' => 'StudentController@student_view']);
    Route::get('observation-report/{studentId}', ['as' => 'observation_report', 'uses' => 'StudentController@generateObservationReport']);
    Route::post('observation-reports', ['as' => 'observation_reports', 'uses' => 'StudentController@generateObservationReports']);
    Route::post('student_feedback', ['as' => 'student_feedback', 'uses' => 'StudentController@student_feedback']);
    Route::post('student_feedback_submit', ['as' => 'student_feedback_submit', 'uses' => 'StudentController@student_feedback_submit']);
    Route::post('project_approve', ['as' => 'project_approve', 'uses' => 'StudentController@project_approve']);
    Route::post('approve_project_submit', ['as' => 'approve_project_submit', 'uses' => 'StudentController@approve_project_submit']);
    Route::post('reject_project_submit', ['as' => 'reject_project_submit', 'uses' => 'StudentController@reject_project_submit']);
    Route::post('student_by_level', ['as' => 'student_by_level', 'uses' => 'StudentController@studentByLevel']);
    Route::post('student/progress/info', ['as' => 'studentProgressInfo', 'uses' => 'StudentController@studentProgressInfo']);
    Route::post('student/progress/by-grade', ['as' => 'getProgressByGrade', 'uses' => 'StudentController@getProgressByGrade']);
    Route::post('student/progress/leaderboard', ['as' => 'generateLeaderBoard', 'uses' => 'StudentController@generateLeaderBoard']);
    Route::post('student/progress/reward-details', ['as' => 'getRewardPointDetails', 'uses' => 'StudentController@getRewardPointDetails']);
    Route::post('student/progress/observations', ['as' => 'getStudentObservations', 'uses' => 'StudentController@getStudentObservations']);

    // Project
    Route::get('projects', ['as' => 'list-project', 'uses' => 'ProjectController@index']);
    Route::get('project/submission/{id}', ['as' => 'view-submission', 'uses' => 'ProjectController@viewSubmission']);
    Route::post('project/submission/generate-feedback/{id}', ['as' => 'generate-submission-feedback', 'uses' => 'ProjectController@generateSubmissionFeedback']);
    Route::get('project/submission/feedback/{feedback}/edit', ['as' => 'edit-submission-feedback', 'uses' => 'ProjectController@editSubmissionFeedback']);
    Route::put('project/submission/feedback/{feedback}', ['as' => 'update-submission-feedback', 'uses' => 'ProjectController@updateSubmissionFeedback']);
    Route::get('project/submission/feedback/{feedback}', ['as' => 'view-submission-feedback', 'uses' => 'ProjectController@viewSubmissionFeedback']);
    Route::post('project/download-selected', ['as' => 'download-selected-projects', 'uses' => 'ProjectController@downloadSelectedProjects']);
    Route::get('project/view/{id}', ['as' => 'view-project', 'uses' => 'ProjectController@viewProject']);
    Route::post('project/change/status', ['as' => 'change-status', 'uses' => 'ProjectController@changeStatus']);
    Route::post('project/generate-feedback/{project}', ['as'=>'generate-feedback', 'uses'=> 'ProjectController@generateFeedback']);
    Route::get('project/feedback/{feedback}/edit', ['as' => 'edit-feedback', 'uses'=> 'ProjectController@editFeedback']);
    Route::put('project/feedback/{feedback}', ['as'=>'update-feedback','uses'=>'ProjectController@updateFeedback']);
    Route::get('project/feedback/{feedback}', ['as' => 'view-feedback','uses'=> 'ProjectController@viewFeedback']);

    // Attendance
    Route::get('student_attendence', ['as' => 'student_attendence', 'uses' => 'StudentController@student_attendence']);
    Route::post('student_attendence', ['as' => 'student_attendence', 'uses' => 'StudentController@student_attendence']);
    Route::post('getStudent', ['as' => 'getStudent', 'uses' => 'StudentController@getStudent']);
    Route::post('saveAttendance', ['as' => 'saveAttendance', 'uses' => 'StudentController@saveAttendance']);

    //Assignment-----------------------------
    Route::get('create/assignment', ['as' => 'createAssignment', 'uses' => 'AssignmentController@createAssignment']);
    Route::post('store/assignment', ['as' => 'store-assignment', 'uses' => 'AssignmentController@store_assignment']);
    Route::get('view/assignment', ['as' => 'viewAssignment', 'uses' => 'AssignmentController@viewAssignment']);
    Route::get('view/assignment', ['as' => 'viewAssignment', 'uses' => 'AssignmentController@assignmentView']);
    Route::get('view/assignment/complete/{id}/{assignId}', [AssignmentController::class, 'completeStudent'])->name('assigment.view_submit_assignment');
    // Route::get('view/assignment/complete/{id}', [AssignmentController::class, 'completeStudent'])->name('assigment.view_submit_assignment');;
    Route::get('assignment', [AssignmentController::class, 'index'])->name('assigment.index');
    Route::get('assignment/{student_communications}', [AssignmentController::class, 'show'])->name('assigment.show');
    Route::get('assignment/{student_communications}/{submission}', [AssignmentController::class, 'submission'])->name('assigment.submission')->scopeBindings();
    Route::post('assignment/{student_communications}/{submission}', [AssignmentController::class, 'review'])->name('assigment.review')->scopeBindings();
    Route::get('manual/assignment/{assignId}', [AssignmentController::class, 'manualSubmission'])->name('manualSubmission');
    Route::get('manual/student/list', ['as' => 'manual_student_list_datatable', 'uses' => 'AssignmentController@manual_student_list_datatable']);
    Route::post('mark/assignment', ['as' => 'mark.assignment', 'uses' => 'AssignmentController@markAssignment']);

    // Comment
    Route::post('createComment', ['as' => 'createComment', 'uses' => 'AssignmentController@createComment']);
    Route::post('getStudentComment', ['as' => 'getStudentComment', 'uses' => 'AssignmentController@getStudentComment']);
    Route::post('getLevelList', ['as' => 'getLevelList', 'uses' => 'AssignmentController@studentLevelList']);
    Route::post('getStudentList', ['as' => 'getStudentList', 'uses' => 'AssignmentController@getStudentList']);
    Route::post('studentAssignment', ['as' => 'studentAssignment', 'uses' => 'AssignmentController@studentAssignment']);
    Route::post('update/assignment/complete', ['as' => 'update_complete_assignment', 'uses' => 'AssignmentController@completeAssignment']);

    //Todo--------------------------------
    Route::get('todo/index', ['as' => 'todo_index', 'uses' => 'TodoController@todo_index']);
    Route::post('todo/insert', ['as' => 'todo_insert', 'uses' => 'TodoController@todo_insert']);
    Route::get('todo/delete/{id}', ['as' => 'todo_delete', 'uses' => 'TodoController@todo_delete']);
    Route::get('todo/edit', ['as' => 'todo_edit', 'uses' => 'TodoController@todo_edit']);
    Route::post('todo/update', ['as' => 'todo_update', 'uses' => 'TodoController@todo_update']);
    Route::post('todo/check', ['as' => 'todo_check', 'uses' => 'TodoController@todo_check']);

    //Profile------------------------------------
    Route::get('profile', ['as' => 'profile', 'uses' => 'DashboardController@profile']);
    Route::post('profile/update', ['as' => 'profile_update', 'uses' => 'DashboardController@profile_update']);
    Route::post('delete/profile/image', ['as' => 'deleteTrainerProfileImage', 'uses' => 'DashboardController@deleteTrainerProfileImage']);
    Route::post('delete/profile/attachment', ['as' => 'deleteTrainerAttachment', 'uses' => 'DashboardController@deleteTrainerAttachment']);
    Route::post('delete/profile/cv', ['as' => 'deleteTrainerCV', 'uses' => 'DashboardController@deleteTrainerCV']);

    // Terms & Privacy Policy
    Route::get('terms-and-privacy-policy', ['as' => 'termsandprivacypolicy', 'uses' => 'TermsAndPrivacyPolicyController@termsAndPrivacyPolicy']);
    Route::post('save-terms-and-privacy-policy', ['as' => 'savetermsandprivacypolicy', 'uses' => 'TermsAndPrivacyPolicyController@saveTermsAndPrivacyPolicy']);

    //Traienr Notification-------------------------
    Route::get('notification/', ['as' => 'trainer-notification', 'uses' => 'NotificationController@index']);

    //Trainer Certificate-------------------------
    Route::get('certificate', ['as' => 'trainer-certificate', 'uses' => 'CertificateController@trainerCertificate']);
    Route::get('certificate/request', ['as' => 'request-certificate', 'uses' => 'CertificateController@requestCertificate']);
    Route::get('certificates/management', ['as' => 'certificates.management', 'uses' => 'StudentController@certificateManagement']);
    Route::get('certificates/get-students', ['as' => 'certificates.getStudents', 'uses' => 'StudentController@getCertificateStudents']);
    Route::post('certificates/bulk-release', ['as' => 'certificates.bulkRelease', 'uses' => 'StudentController@bulkReleaseCertificates']);
    Route::get('test-certificate', ['as' => 'test.certificate', 'uses' => 'StudentController@testCertificateDesign']);

    Route::post('trainer/getStream', ['as' => 'getStream', 'uses' => 'StudentController@getStream']);
    Route::post('trainer/getSession', ['as' => 'getSession', 'uses' => 'StudentController@getSession']);
    Route::post('observation/store', ['as' => 'observation.store', 'uses' => 'StudentController@observationStore']);
    Route::post('observation/delete/image', ['as' => 'observation.deleteSessionImage', 'uses' => 'StudentController@deleteSessionImage']);
    Route::post('observation/delete', ['as' => 'deleteObservation', 'uses' => 'StudentController@observationDelete']);
    Route::get('certificates/bulk-download/{token}', ['as' => 'certificates.bulkDownload', 'uses' => 'StudentController@bulkDownloadCertificates']);

    Route::get('download/observation/{id}', ['as' => 'observation.downloadData', 'uses' => 'ObservationController@downloadData']);
});

// =====================  Student Section =================
Route::group(['namespace' => 'Student', 'prefix' => 'student', 'as' => 'student.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:4']], function () {
    // Dashboard
    Route::get('dashboard', 'StudentAccountController@index')->name('dashboard');
    Route::get('academic-year-rewards', 'StudentAccountController@getAcademicYearRewards')->name('academic-year-rewards');
    Route::get('downloadQuizScore/{quizType}/{quizID}', ['as' => 'downloadQuizScore', 'uses' => 'StudentAccountController@downloadQuizScore']);
    Route::post('getCompletionRatePercentage', ['as' => 'getCompletionRatePercentage', 'uses' => 'StudentAccountController@getCompletionRatePercentage']);

    // Profile
    // Route::get('profile', ['as' => 'student-profile', 'uses' => 'StudentAccountController@studentProfile']);
    Route::post('profile/update/', ['as' => 'student-update', 'uses' => 'StudentAccountController@studentUpdate']);

    // Assignment
    Route::get('assignment', 'AssignmentController@assignment')->name('assignment');
    Route::post('save-read-assignment', 'AssignmentController@save_read_assignment')->name('save_read_assignment');
    Route::post('save-comment-assignment', 'AssignmentController@save_comment_assignment')->name('save_comment_assignment');
    Route::post('getComment', 'AssignmentController@getComment')->name('getComment');
    Route::post('addComment', 'AssignmentController@addComment')->name('addComment');
    Route::get('assignment/{student_communications}', [StudentAssignmentController::class, 'show'])->name('assigment.show');
    Route::post('assignment/{student_communications}', [StudentAssignmentController::class, 'submit'])->name('assigment.submit');
    Route::post('getStudentWorksheets', ['as' => 'getStudentWorksheets', 'uses' => 'AssignmentController@getStudentWorksheets']);

    Route::get('content/list', [ContentStudentController::class, 'index'])->name('contentlist.contentList');
    Route::get('content/view/{id}', [ContentStudentController::class, 'view'])->name('contentview.contentView');
    Route::get('quiz/{type}/{id}', [ContentStudentController::class, 'quiz'])->name('quiz');
    Route::post('content/submitquiz', [ContentStudentController::class, 'submitQuiz'])->name('submitquiz.submitQuiz');
    Route::post('content/savequiz', [ContentStudentController::class, 'saveQuiz'])->name('savequiz.saveQuiz');
    Route::get('content/{grade}/grade', [ContentStudentController::class, 'streamList'])->name('contentlist.streamlist');
    Route::post('content/scormcompletionpoints', [ContentStudentController::class, 'storeScormCompletionPoints'])->name('storeScormCompletionPoints');
    Route::post('content/scormlearningpoints', [ContentStudentController::class, 'storeScormLearningPoints'])->name('storeScormLearningPoints');
    Route::post('content/youtubecompletionpoints', [ContentStudentController::class, 'storeYoutubeCompletionPoints'])->name('storeYoutubeCompletionPoints');

    // Project
    Route::get('project/list/', ['as' => 'list-project', 'uses' => 'StudentProjectController@index']);
    Route::get('project/add/', ['as' => 'add-project', 'uses' => 'StudentProjectController@addProject']);
    Route::post('project/save/', ['as' => 'save-project', 'uses' => 'StudentProjectController@saveProject']);
    Route::get('project/view/{id}', ['as' => 'view-project', 'uses' => 'StudentProjectController@viewProject']);
    Route::get('project/edit/{id}', ['as' => 'edit-project', 'uses' => 'StudentProjectController@editProject']);
    Route::post('project/update/', ['as' => 'update-project', 'uses' => 'StudentProjectController@updateProject']);
    Route::post('project/delete/', ['as' => 'delete-project', 'uses' => 'StudentProjectController@deleteProject']);
    Route::post('project/delete/projectFile/', ['as' => 'delete-project-file', 'uses' => 'StudentProjectController@deleteProjectFile']);
    Route::post('project/delete/projectDetails/', ['as' => 'delete-project-details', 'uses' => 'StudentProjectController@deleteProjectDetails']);

    // My Workspace
    Route::get('workspace/strength/', ['as' => 'workspace-strength', 'uses' => 'WorkspaceController@index']);
    Route::get('workspace/goalSetting/', ['as' => 'workspace-goal-setting', 'uses' => 'WorkspaceController@index']);
    Route::get('workspace/resources/', ['as' => 'workspace-resources', 'uses' => 'WorkspaceController@index']);
    Route::get('workspace/prototypes/', ['as' => 'workspace-prototypes', 'uses' => 'WorkspaceController@index']);

    //Class Schedule-------------------------
    Route::get('Class/Schedule/', ['as' => 'Class_schedule', 'uses' => 'ClassScheduleController@Class_schedule']);
    Route::get('Class_Schedule/', ['as' => 'student_classSchedule', 'uses' => 'ClassScheduleController@student_classSchedule']);
    Route::get('student_day_class_schedule/', ['as' => 'student_day_class_schedule', 'uses' => 'ClassScheduleController@student_day_class_schedule']);

    // Event
    Route::get('event-list', 'EventController@eventList')->name('event_list');
    Route::get('event-view/{id}', 'EventController@eventView')->name('event_view');
    Route::post('event-booking-registration', 'EventController@eventBookingRegistration')->name('event_booking_registration');
    Route::post('event-challenge-response', 'EventController@eventChallengeResponse')->name('event_challenge_response');

    // Term And Privacy Policy
    Route::get('term-and-privacy-policy', 'TermAndPrivacyPolicyController@term_and_privacy_policy')->name('term_and_privacy_policy');
    Route::post('save-term-and-privacy-policy', 'TermAndPrivacyPolicyController@saveTermsAndPrivacyPolicy')->name('savetermsandprivacypolicy');
    Route::post('delete/notification/', ['as' => 'notification_delete', 'uses' => 'NotificationController@notification_delete']);

    //Student Notification-------------------------
    Route::get('notification/', ['as' => 'student-notification', 'uses' => 'NotificationController@index']);

    //Student Certificate-------------------------
    Route::get('certificate/', ['as' => 'student-certificate', 'uses' => 'CertificateController@studentCertificate']);
    Route::get('certificate/request/{gradeId}', ['as' => 'request-certificate', 'uses' => 'CertificateController@requestCertificate']);

    Route::get('/change/password', ['as' => 'change-password', 'uses' => 'StudentAccountController@changePassword']);
    Route::post('/change/password/request', ['as' => 'change-password-request', 'uses' => 'StudentAccountController@changePasswordRequest']);

    Route::get('/profile/photo', ['as' => 'profile-photo', 'uses' => 'StudentAccountController@profilePhoto']);
    Route::post('/upload/profile/photo', ['as' => 'upload-profile-photo', 'uses' => 'StudentAccountController@uploadProfilePhoto']);
    Route::post('/delete/profile/photo', ['as' => 'delete-profile-photo', 'uses' => 'StudentAccountController@deleteProfilePhoto']);

    Route::get('/prototype', ['as' => 'prototype', 'uses' => 'PrototypeController@prototype']);
    Route::post('/generate-prototype', ['as' => 'generate-prototype', 'uses' => 'PrototypeController@generatePrototype']);

    Route::get('/business-plan', [ 'as'   => 'businessplan.index','uses' => 'BusinessPlanController@index']);
    Route::post('/get-question', ['as'   => 'businessplan.get','uses' => 'BusinessPlanController@getQuestion']);
    Route::post('/save-answer', ['as'   => 'businessplan.save','uses' => 'BusinessPlanController@saveAnswer']);
    Route::get('/businessplan/result', ['as'   => 'businessplan.result','uses' => 'BusinessPlanController@showResult']);
    Route::post('businessplan/generate-pdf-async', ['as'   => 'businessplan.generate-pdf-async','uses' => 'BusinessPlanController@generateBusinessPlanPDFAsync']);
    Route::post('businessplan/download-pdf', ['as'   => 'businessplan.download-pdf','uses' => 'BusinessPlanController@downloadBusinessPlanPDF']);

    Route::get('assessment', ['as' => 'assessment', 'uses' => 'AssessmentController@index']);
    Route::get('assessment/baseline', ['as' => 'assessment.baseline', 'uses' => 'AssessmentController@baseline']);
    Route::get('assessment/baseline/question', ['as' => 'assessment.baseline.question', 'uses' => 'AssessmentController@getBaselineQuestion']);
    Route::post('assessment/baseline/action', ['as' => 'assessment.baseline.action', 'uses' => 'AssessmentController@baselineAction']);
    Route::get('assessment/baseline/report', ['as' => 'assessment.baseline.report', 'uses' => 'AssessmentController@downloadBaselineReport']);
});

// Certificate download routes (outside auth groups for public access)
Route::get('trainer/certificate/download/{token}', ['as' => 'certificate.download', 'uses' => 'Trainer\StudentController@downloadCertificate']);
Route::get('student/certificate/download/{token}', ['as' => 'student.certificate.download', 'uses' => 'Trainer\StudentController@downloadCertificate']);
