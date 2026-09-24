<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\School\ManageschoolController;
use App\Http\Controllers\School\ProgressreportController;
use App\Http\Controllers\School\NotificationController;
use App\Http\Controllers\School\BatchController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\ClassScheduleController as SchoolClassScheduleController;
use App\Http\Controllers\School\StandardAssessmentController as SchoolStandardAssessmentController;
use App\Http\Controllers\School\RealQAssessmentController as SchoolRealQAssessmentController;
use App\Http\Controllers\Student\StudentAccountController;
use App\Http\Controllers\Student\ContentStudentController;
use App\Http\Controllers\Student\AssignmentController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\StudentProjectController;
use App\Http\Controllers\Student\StudentMarketplaceController;
use App\Http\Controllers\Student\WorkspaceController;
use App\Http\Controllers\Student\ClassScheduleController as StudentClassScheduleController;
use App\Http\Controllers\Student\EventController;
use App\Http\Controllers\Student\TermAndPrivacyPolicyController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\School\ObservationController;
use App\Http\Controllers\Student\PrototypeController;
use App\Http\Controllers\Student\BusinessPlanController;
use App\Http\Controllers\Student\AssessmentController;


/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    /* START - IF AUTH USER FOUND THEN REDIRECT TO RESPECTIVE DASHBOARD | REDIRECT TO LOGIN PAGE */
    Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
        Route::get('/', [FrontendController::class, 'index'])->name('index');
    });
    /* END - IF AUTH USER FOUND THEN REDIRECT TO RESPECTIVE DASHBOARD | REDIRECT TO LOGIN PAGE */

    Route::middleware([])->group(function () {
        // Auth Routes
        require __DIR__ . '/auth.php';
    });

    /* START - SCHOOL ROUTES */
    Route::group(['namespace' => 'School', 'prefix' => 'school', 'as' => 'school.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:2']], function () {

        // School Dashboard
        Route::get('dashboard', [ManageschoolController::class, 'index'])->name('dashboard');

        // School Profile
        Route::get('/profile/edit/', [ManageschoolController::class, 'profileEdit'])->name('profile-edit');
        Route::get('/profile/deletestudents/{id}', [ManageschoolController::class, 'studentDelete'])->name('student-delete-all');
        Route::post('/profile/update/', [ManageschoolController::class, 'updateSchool'])->name('update-school');
        Route::post('/profile/deleteSchoolLogo', [ManageschoolController::class, 'deleteSchoolLogo'])->name('deleteSchoolLogo');
        Route::post('/profile/deleteSchoolCoverLogo', [ManageschoolController::class, 'deleteSchoolCoverLogo'])->name('deleteSchoolCoverLogo');

        // School Privacy
        Route::get('/privacy/police/', [ManageschoolController::class, 'privacyPolice'])->name('privacy-police');
        Route::post('/privacy/police/save/', [ManageschoolController::class, 'savePrivacyPolice'])->name('save-privacy-police');

        // Student Progress Report
        Route::get('/progress/report/', [ProgressreportController::class, 'progressReport'])->name('progress-report');
        Route::post('/student/info/', [ProgressreportController::class, 'studentInfo'])->name('student-info');
        Route::post('getProgressByGrade', [ProgressreportController::class, 'getProgressByGrade'])->name('getProgressByGrade');
        Route::post('generate/leaderboard', [ProgressreportController::class, 'generateLeaderBoard'])->name('generateLeaderBoard');
        Route::post('reward/details', [ProgressreportController::class, 'getRewardPointDetails'])->name('getRewardPointDetails');
        Route::post('/observations', [ProgressreportController::class, 'getStudentObservations'])->name('observations');

        // // Batch List
        Route::get('/batch/list/', [BatchController::class, 'index'])->name('batch-list');
        Route::get('/batch/create', [BatchController::class, 'create'])->name('batch.create');
        Route::post('/batch/store', [BatchController::class, 'store'])->name('batch.store');
        Route::get('/batch/edit/{id}', [BatchController::class, 'edit'])->name('batch.edit');
        Route::post('/batch/update', [BatchController::class, 'update'])->name('batch.update');
        Route::get('/batch/delete/{id}', [BatchController::class, 'destory'])->name('batch.delete');

        // // Student List
        Route::get('/student/list/', [SchoolController::class, 'studentList'])->name('student-list');
        Route::get('/student_list_datatable', [SchoolController::class, 'student_list_datatable'])->name('student_list_datatable');
        Route::get('/student/edit/{id}', [SchoolController::class, 'studentEdit'])->name('student-edit');
        Route::get('/student/create', [SchoolController::class, 'studentAdd'])->name('student-add');
        Route::get('/student/import', [SchoolController::class, 'sudentImport'])->name('student-import');
        Route::post('/student/import/store', [SchoolController::class, 'studentImportStore'])->name('student-import-store');
        Route::post('/student/store', [SchoolController::class, 'studentStore'])->name('student-store');
        Route::get('/student/delete/{id}', [SchoolController::class, 'studentDelete'])->name('student-delete');
        Route::post('/student/update/', [SchoolController::class, 'studentUpdate'])->name('student-update');
        Route::post('/delete-student-profile-avatar', [SchoolController::class, 'deleteStudentProfileAvatar'])->name('delete-student-profile-avatar');
        Route::post('/student-reset-password', [SchoolController::class, 'studentResetPassword'])->name('student-reset-password');

        // Standard Assessment Settings
        Route::get('/standard-assessment', [SchoolStandardAssessmentController::class, 'index'])->name('standard-assessment.index');
        Route::post('/standard-assessment', [SchoolStandardAssessmentController::class, 'update'])->name('standard-assessment.update');
        
        // RealQ Assessment Settings
        Route::get('/realq-assessment', [SchoolRealQAssessmentController::class, 'index'])->name('realq-assessment.index');
        Route::post('/realq-assessment', [SchoolRealQAssessmentController::class, 'update'])->name('realq-assessment.update');
        Route::get('/realq-assessment/report/download', [SchoolRealQAssessmentController::class, 'downloadReport'])->name('realq-assessment.report.download');

        // School Notification
        Route::get('notification/', [NotificationController::class, 'index'])->name('school-notification');

        // Export Observation
        // Route::get('/download/observation/', [ObservationController::class, 'downloadData'])->name('observation.downloadData');

        //School Class Schedule--------------
        Route::get('Class/Schedule/', [SchoolClassScheduleController::class, 'Class_schedule'])->name('Class_schedule');
        Route::get('Class_Schedule/', [SchoolClassScheduleController::class, 'school_classSchedule'])->name('school_classSchedule');
        Route::get('session-report/', [SchoolClassScheduleController::class, 'sessionReport'])->name('session_report');
        Route::get('session-report/download', [SchoolClassScheduleController::class, 'downloadSessionReportPdf'])->name('session_report.download');
    });
    /* END - SCHOOL ROUTES */

    /* START - STUDENT ROUTES */
    Route::group(['namespace' => 'Student', 'prefix' => 'student', 'as' => 'student.', 'middleware' => ['auth', 'can:view_backend', 'check_permission:4']], function () {

        // Dashboard
        Route::get('dashboard', [StudentAccountController::class, 'index'])->name('dashboard');
        Route::get('academic-year-rewards', [StudentAccountController::class, 'getAcademicYearRewards'])->name('academic-year-rewards');
        Route::get('downloadQuizScore/{quizType}/{quizID}', [StudentAccountController::class, 'downloadQuizScore'])->name('downloadQuizScore');
        Route::post('getCompletionRatePercentage', [StudentAccountController::class, 'getCompletionRatePercentage'])->name('getCompletionRatePercentage');

        // Profile
        Route::post('profile/update/', [StudentAccountController::class, 'studentUpdate'])->name('student-update');

        // Assignment
        Route::get('assignment', [AssignmentController::class, 'assignment'])->name('assignment');
        Route::post('save-read-assignment', [AssignmentController::class, 'save_read_assignment'])->name('save_read_assignment');
        Route::post('save-comment-assignment', [AssignmentController::class, 'save_comment_assignment'])->name('save_comment_assignment');
        Route::post('getComment', [AssignmentController::class, 'getComment'])->name('getComment');
        Route::post('addComment', [AssignmentController::class, 'addComment'])->name('addComment');
        Route::post('assignment/storeScormAssignment', [AssignmentController::class, 'storeScormAssignment'])->name('storeScormAssignment');
        Route::get('assignment/{student_communications}', [StudentAssignmentController::class, 'show'])->name('assigment.show');
        Route::post('assignment/{student_communications}', [StudentAssignmentController::class, 'submit'])->name('assigment.submit');
        Route::post('getStudentWorksheets', [AssignmentController::class, 'getStudentWorksheets'])->name('getStudentWorksheets');
        Route::get('assignment/{student_communications}/resubmit', [AssignmentController::class, 'resubmit'])->name('assignment.resubmit');
        
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
        Route::get('project/my-projects', [StudentProjectController::class, 'myProjects'])->name('my-projects');

        // Marketplace
        Route::get('marketplace', [StudentMarketplaceController::class, 'index'])->name('marketplace');
        Route::get('marketplace/products', [StudentMarketplaceController::class, 'products'])->name('marketplace.products');
        Route::post('marketplace/save', [StudentMarketplaceController::class, 'save'])->name('marketplace.save');
        Route::get('marketplace/product/{id}', [StudentMarketplaceController::class, 'edit'])->name('marketplace.edit');
        Route::delete('marketplace/product/{id}', [StudentMarketplaceController::class, 'destroy'])->name('marketplace.destroy');
        Route::patch('marketplace/product/{id}/publish', [StudentMarketplaceController::class, 'publish'])->name('marketplace.publish');
        Route::get('marketplace/image/{id}/download', [StudentMarketplaceController::class, 'downloadImage'])->name('marketplace.image.download');
        
        Route::get('project/create/{id?}', [StudentProjectController::class, 'createProject'])->name('create-project');
        Route::post('project/create/save', [StudentProjectController::class, 'saveProjectStep'])->name('save-project-step');
        Route::get('project/attachment/{id}/download', [StudentProjectController::class, 'downloadAttachment'])->name('download-attachment');
        Route::get('project/{id}/get-ideas', [StudentProjectController::class, 'confirmImprovementIdeas'])->name('confirm-improvement-ideas');
        Route::post('project/{id}/generate-improvement-report', [StudentProjectController::class, 'generateImprovementReport'])->name('generate-improvement-report');
        Route::get('project/{id}/suggestions', [StudentProjectController::class, 'projectSuggestions'])->name('project-suggestions');
        Route::get('project/{id}/confirm-submission', [StudentProjectController::class, 'confirmSubmission'])->name('confirm-submission');
        Route::post('project/{id}/submit', [StudentProjectController::class, 'submitProject'])->name('submit-project');
        Route::get('project/{id}/make-changes', [StudentProjectController::class, 'makeChanges'])->name('make-changes');
        Route::get('project/{id}/teacher-feedback', [StudentProjectController::class, 'viewProjectFeedback'])->name('project-feedback');
        Route::post('project/{id}/publish-sections', [StudentProjectController::class, 'savePublishSections'])->name('save-publish-sections');
        Route::get('project/{id}/download-pdf', [StudentProjectController::class, 'downloadProjectPdf'])->name('download-project-pdf');
        Route::get('project/list/', [StudentProjectController::class, 'index'])->name('list-project');
        Route::get('project/add/', [StudentProjectController::class, 'addProject'])->name('add-project');
        Route::get('project/themes/{grade}', [StudentProjectController::class, 'getThemesByGrade'])->name('project-themes');
        Route::post('project/save/', [StudentProjectController::class, 'saveProject'])->name('save-project');
        Route::get('project/view/{id}', [StudentProjectController::class, 'viewProject'])->name('view-project');
        Route::get('project/edit/{id}', [StudentProjectController::class, 'editProject'])->name('edit-project');
        Route::put('project/update/{id}', [StudentProjectController::class, 'updateProject'])->name('update-project');
        Route::post('project/delete/', [StudentProjectController::class, 'deleteProject'])->name('delete-project');
        Route::post('/delete-project-media', [StudentProjectController::class, 'deleteProjectMedia'])->name('delete-project-media');
        //Route::get('/download-project-file/{id}', [StudentProjectController::class, 'downloadProjectFile'])->name('download-project-file');
        Route::post('/upload/project-video', [StudentProjectController::class, 'uploadProjectVideo'])->name('uploadProjectVideo');
        Route::get('/project/{id}/feedback', [StudentProjectController::class, 'viewFeedback'])->name('view-feedback');
        Route::post('/project/download-file', [StudentProjectController::class, 'downloadProjectFile'])->name('download-project-file');
        Route::get('/project/manually-set-draft/{id}', function($id) {
            $project = \App\Models\Project::find($id);
                if ($project) {
                if ($project->feedback) {
                    $project->feedback->delete();
                } 
                $project->is_publish = 0;
                $project->project_status = 0;
                $project->save();
                return " Project #{$id} , ('{$project->title}') set to draft successfully!";
                }
                return "Project not found!";
        });
        
        // My Workspace
        Route::get('my-workspace', [WorkspaceController::class, 'index_new'])->name('my-workspace');
        // Route::get('workspace/strength/', [WorkspaceController::class, 'index_new'])->name('workspace-strength');
        // Route::get('workspace/goalSetting/', [WorkspaceController::class, 'index'])->name('workspace-goal-setting');
        // Route::get('workspace/resources/', [WorkspaceController::class, 'index'])->name('workspace-resources');
        // Route::get('workspace/prototypes/', [WorkspaceController::class, 'index'])->name('workspace-prototypes');

        //Class Schedule-------------------------
        Route::get('Class/Schedule/', [StudentClassScheduleController::class, 'Class_schedule'])->name('Class_schedule');
        Route::get('Class_Schedule/', [StudentClassScheduleController::class, 'student_classSchedule'])->name('student_classSchedule');
        Route::get('student_day_class_schedule/', [StudentClassScheduleController::class, 'student_day_class_schedule'])->name('student_day_class_schedule');

        // Event
        Route::get('event-list', [EventController::class, 'eventList'])->name('event_list');
        Route::get('/event-list/daily-quizzes', [EventController::class, 'getDailyChallenges'])->name('daily_challenges');
        Route::get('/event-list/industry-challenges', [EventController::class, 'getIndustryChallenges'])->name('industry_challenges');
        Route::get('event-view/{id}', [EventController::class, 'eventView'])->name('event_view');
        Route::post('event-booking-registration', [EventController::class, 'eventBookingRegistration'])->name('event_booking_registration');
        Route::post('event-challenge-response', [EventController::class, 'eventChallengeResponse'])->name('event_challenge_response');

        // Term And Privacy Policy
        Route::get('term-and-privacy-policy', [TermAndPrivacyPolicyController::class, 'term_and_privacy_policy'])->name('term_and_privacy_policy');
        Route::post('save-term-and-privacy-policy', [TermAndPrivacyPolicyController::class, 'saveTermsAndPrivacyPolicy'])->name('savetermsandprivacypolicy');
        Route::post('delete/notification/', [StudentNotificationController::class, 'notification_delete'])->name('notification_delete');

        //Student Notification-------------------------
        Route::get('notification/', [StudentNotificationController::class, 'index'])->name('student-notification');

        //Student Certificate-------------------------
        Route::get('certificate/', [CertificateController::class, 'studentCertificate'])->name('student-certificate');
        Route::get('certificate/request/{gradeId}', [CertificateController::class, 'requestCertificate'])->name('request-certificate');

        Route::get('/change/password', [StudentAccountController::class, 'changePassword'])->name('change-password');
        Route::post('/change/password/request', [StudentAccountController::class, 'changePasswordRequest'])->name('change-password-request');

        Route::get('/profile/photo', [StudentAccountController::class, 'profilePhoto'])->name('profile-photo');
        Route::post('/upload/profile/photo', [StudentAccountController::class, 'uploadProfilePhoto'])->name('upload-profile-photo');
        Route::post('/delete/profile/photo', [StudentAccountController::class, 'deleteProfilePhoto'])->name('delete-profile-photo');

        Route::post('/affirmation-tracking', [StudentAccountController::class, 'audioTracking'])->name('affirmation.tracking');

        // Student Observations (Subscription Activity tab)
        Route::get('/observations', [StudentAccountController::class, 'getStudentObservations'])->name('observations');

        Route::get('/prototype', [PrototypeController::class, 'prototype'])->name('prototype');
        Route::post('/generate-prototype', [PrototypeController::class, 'generatePrototype'])->name('generate-prototype');

        Route::get('/business-plan', [BusinessPlanController::class, 'index'])->name('businessplan.index');
        Route::post('/get-question', [BusinessPlanController::class, 'getQuestion'])->name('businessplan.get');
        Route::post('/save-answer', [BusinessPlanController::class, 'saveAnswer'])->name('businessplan.save');
        Route::get('/businessplan/result', [BusinessPlanController::class, 'showResult'])->name('businessplan.result');
        Route::post('businessplan/generate-pdf-async', [BusinessPlanController::class, 'generateBusinessPlanPDFAsync'])->name('businessplan.generate-pdf-async');
        Route::post('businessplan/download-pdf', [BusinessPlanController::class, 'downloadBusinessPlanPDF'])->name('businessplan.download-pdf');

        Route::get('assessment', [AssessmentController::class, 'index'])->name('assessment');
        Route::get('assessment/baseline-mode', [AssessmentController::class, 'baselineMode'])->name('assessment.mode');
        Route::post('assessment/baseline-mode', [AssessmentController::class, 'saveBaselineMode'])->name('assessment.mode.save');
        Route::get('assessment/subjective-setup', [AssessmentController::class, 'subjectiveSetup'])->name('assessment.subjective.setup');
        Route::get('assessment/subjective/topics', [AssessmentController::class, 'subjectiveTopics'])->name('assessment.subjective.topics');
        Route::post('assessment/subjective/start', [AssessmentController::class, 'startSubjectiveAssessment'])->name('assessment.subjective.start');
        Route::post('assessment/subjective/save-answer', [AssessmentController::class, 'saveSubjectiveAnswer'])->name('assessment.subjective.save-answer');
        Route::get('assessment/mcq-setup', [AssessmentController::class, 'mcqSetup'])->name('assessment.mcq.setup');
        Route::post('assessment/mcq/start', [AssessmentController::class, 'startMcqAssessment'])->name('assessment.mcq.start');
        Route::post('assessment/mcq/save-answer', [AssessmentController::class, 'saveMcqAnswer'])->name('assessment.mcq.save-answer');
        Route::get('assessment/report', [AssessmentController::class, 'report'])->name('assessment.report');
        Route::get('assessment/report/download', [AssessmentController::class, 'downloadReport'])->name('assessment.report.download');
        
        Route::get('assessment/baseline', [AssessmentController::class, 'baseline'])->name('assessment.baseline');
        Route::get('assessment/baseline/question', [AssessmentController::class, 'getBaselineQuestion'])->name('assessment.baseline.question');
        Route::post('assessment/baseline/action', [AssessmentController::class, 'baselineAction'])->name('assessment.baseline.action');
        Route::get('assessment/baseline/report', [AssessmentController::class, 'downloadBaselineReport'])->name('assessment.baseline.report');
    });
    /* END - STUDENT ROUTES */
});

