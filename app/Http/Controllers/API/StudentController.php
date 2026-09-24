<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\StudentResource;
use App\Mail\CreatedStudentMail;
use App\Models\User;
use App\Models\Students;
use App\Models\EmailInfo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Events\Backend\UserCreated;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\School;
use App\Models\Grade;
use App\Models\Domain;
use App\Models\StudentProject;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;
use App\Models\ProjectSection;

class StudentController extends Controller
{
    use ApiResponse;
    protected $module_name;

    public function __construct()
    {
        $this->module_name = 'users';
    }
    public function studentStore(Request $request)
    {
        $rules = [
            'name'          => 'required',
            'email'         => 'required|email|unique:users,email',
            'mobile'        => 'required',
            // 'address'       => 'required',
            'date_of_birth' => 'required|date',
            'gender'        => 'required|in:Male,Female,Other',
            'parent_name'   => 'required',
            'parent_email'  => 'required',
            'password'      => 'nullable|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendResponse(
                implode(',', $validator->messages()->all()),
                422
            );
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->password = Hash::make($request->password);
        $user->group = 4;
        $user->save();

        $module_name = $this->module_name;
        $module_name_singular = Str::singular($module_name);

        $$module_name_singular = $user;
        $user_id = $user->id;

        $roles = Role::select('name')->where('id', 8)->get()->toArray();
        $permissions = Permission::select('name')->whereIn('id', [1, 42])->get()->toArray();
        $permission = [];
        $role = [];
        foreach ($roles as $getrole) {
            $role[] = $getrole['name'];
        }

        foreach ($permissions as $getper) {
            $permission[] = $getper['name'];
        }

        $module_name_singular = Str::singular('user');

        if (isset($roles)) {
            $$module_name_singular->syncRoles($roles);
        } else {
            $roles = [];
            $$module_name_singular->syncRoles($roles);
        }

        // Sync Permissions
        if (isset($permissions)) {
            $$module_name_singular->syncPermissions($permissions);
        } else {
            $permissions = [];
            $$module_name_singular->syncPermissions($permissions);
        }

        // Username
        $id = $$module_name_singular->id;
        $username = config('app.initial_username') + $id;
        $$module_name_singular->username = $username;
        $$module_name_singular->save();

        $studentModel = $$module_name_singular;
        safeEventAction('api student created', [
            'user_id' => $studentModel->id ?? null,
            'email' => $studentModel->email ?? null,
        ], function () use ($studentModel) {
            event(new UserCreated($studentModel));
        });

        $student = new Students();
        $student->user_id = $user->id;
        $student->school_id = 1;
        $student->country_id = School::find(1)->pluck('country_id')->first() ?? 1;
        $student->name = $request->name;
        $student->parent_name = $request->parent_name;
        $student->parent_email = $request->parent_email;
        $student->address = $request->address;
        $student->save();

        safeMailAction('api student created mail', [
            'recipient' => $request->email,
            'student_email' => $request->email,
        ], function () use ($request) {
            Mail::to($request->email)->send(new CreatedStudentMail($request->email, $request->password));
        });
        safeMailAction('api parent created mail', [
            'recipient' => $request->parent_email,
            'student_email' => $request->email,
        ], function () use ($request) {
            Mail::to($request->parent_email)->send(new CreatedStudentMail($request->email, $request->password));
        });

        $student_email = new EmailInfo;
        $student_email->name = $request->name;
        $student_email->mail_address = $request->email;
        $student_email->mail_description = '';
        $student_email->group = 4;
        $student_email->save();

        return $this->sendResponse('Booking Done Successfully.', 200, new StudentResource($student));
    }

    public function createStudentWithParent(Request $request)
    {
        $rules = [
            'name'          => 'required',
            // 'email'         => 'email|same:parent_email|unique:users,email',
            'parent_name'   => 'required',
            'parent_email'  => 'required|email',
            'mobile'        => 'required',
            // 'address'       => 'required',
            'date_of_birth' => 'required|date|date_format:d-m-Y',
            'gender'        => 'required|in:Male,Female,Other',
            'password'      => 'nullable|min:6|max:10',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendResponse(
                implode(',', $validator->messages()->all()),
                401
            );
        }

        $school_data = School::find(1);

        $baseUserName = preg_replace('/[^A-Za-z0-9._]/', '', trim($request->name));
        $baseUserName = $school_data->domains->domain . '.' . $baseUserName . date('dm', strtotime($request->date_of_birth));

        $userName = $baseUserName;

        $chkDuplicateEmail = User::where('email', $request->parent_email)->orWhere('username', $userName)->first();
        
        if(!empty($chkDuplicateEmail)) {
            return $this->sendResponse('Booking Done Successfully.', 200);
        }
        
        $country_id = $school_data->country_id;

        /* START - CHECK ADDING MAX STUDENT LIMIT */
        $number_of_students_allowed = $school_data->number_of_student;
        $total_students = Students::where('school_id', $school_data->id)->count();

        if(empty($number_of_students_allowed) || !($total_students < $number_of_students_allowed)) {
            return $this->sendResponse([
                'status' => false,
                'message' => "Not allowed to enrol more students",
            ], 401);
        }
        /* END - CHECK ADDING MAX STUDENT LIMIT */

        $assign_grade = [];

        
        $level = $request->level;
        if(isset($request->level) && !empty($level)) {
            $level = str_ireplace (' ', '', $level);
            $uniqueCodeList = explode(",", $level);
            $uniqueCodeExist = Grade::select('id')->whereIn('unique_code', $uniqueCodeList)->get();
            if($uniqueCodeExist->count()) {
                foreach($uniqueCodeExist as $uniqueCode) {
                    $assign_grade[] = $uniqueCode->id;
                }
            }
        } else {
            $uc_exploratory_level = config('global.level_unique_code.exploratory_level');
            $default_level = Grade::select('id')->where('unique_code', $uc_exploratory_level)->first();
            if($default_level) {
                $assign_grade[] = $default_level->id;
            }
        }

        $assign_grade_id_list = '';
        if(count($assign_grade)) {
            $assign_grade_id_list = implode(",", $assign_grade);
        }
        
        
        /* START - CREATE USER FIRST */
        $user = new User();
        $user->name = $request->name;
        // $user->email = $request->parent_email;
        $user->mobile = $request->mobile;
        if(isset($request->date_of_birth) && !empty($request->date_of_birth)) {
            $user->date_of_birth = $request->date_of_birth;
        }
        if(isset($request->gender) && !empty($request->gender)) {
            $user->gender = $request->gender;
        }
        $user->country_id = $country_id;
        if (isset($request->password) && $request->password != null && $request->password != '') {
            $userPassword = $request->password;
        } else {
            $userPassword = Str::random(10);
        }
        $user->password = Hash::make($userPassword);
        $user->group = 4;
        $user->source = config('app.request_source');

        // If username exists, keep modifying until unique
        $counter = 1;
        while (User::where('username', $userName)->exists()) {
            $userName = $baseUserName . '_' . $counter;
            $counter++;
        }
        $user->username = $userName;

        $user->save();
        /* END - CREATE USER FIRST */

        $module_name = $this->module_name;
        $module_name_singular = Str::singular($module_name);

        $$module_name_singular = $user;

        $roles = Role::select('name')->where('id', 8)->get()->toArray();
        $permissions = Permission::select('name')->whereIn('id', [1, 42])->get()->toArray();
        $permission = [];
        $role = [];
        foreach ($roles as $getrole) {
            $role[] = $getrole['name'];
        }

        foreach ($permissions as $getper) {
            $permission[] = $getper['name'];
        }

        $module_name_singular = Str::singular('user');

        if (isset($roles)) {
            $$module_name_singular->syncRoles($roles);
        } else {
            $roles = [];
            $$module_name_singular->syncRoles($roles);
        }

        // Sync Permissions
        if (isset($permissions)) {
            $$module_name_singular->syncPermissions($permissions);
        } else {
            $permissions = [];
            $$module_name_singular->syncPermissions($permissions);
        }

        $studentModel = $$module_name_singular;
        safeEventAction('api student created', [
            'user_id' => $studentModel->id ?? null,
            'email' => $studentModel->email ?? null,
        ], function () use ($studentModel) {
            event(new UserCreated($studentModel));
        });

        /* START - CREATE STUDENT */
        $student = new Students();
        $student->user_id = $user->id;
        $student->school_id = $school_data->id;
        $student->country_id = $country_id;
        $student->name = $request->name;
        $student->parent_name = $request->parent_name;
        $student->parent_email = $request->parent_email;
        if(!empty($assign_grade_id_list)) {
            $student->grade_id = $assign_grade_id_list;
        }
        $student->address = $request->address ?? '';
        $student->save();
        /* END - CREATE STUDENT */

        clear_cache_manually();

        $host = $request->getHost();
        
        /* START - CHECK SCHOOL HAS MULTIPLE DOMAINS */
        $schoolHasMultipleDomain = \App\Models\Domain::select('domain')->where('tenant_id', $school_data->tenant_id)
            ->where('domain', 'not like', '%' . config('tenancy.sub_domain'))
            ->count();
        /* End - CHECK SCHOOL HAS MULTIPLE DOMAINS */

        $domain = null;
        if (isset($school_data->domains->domain) && !empty($school_data->domains->domain)) {
            $domain = $school_data->domains->domain.config('tenancy.sub_domain');
        }
        
        if (!empty($host) && $host != $domain && $schoolHasMultipleDomain) {
            $domain = $host;
        }
        
        safeMailAction('api parent created mail', [
            'recipient' => $request->parent_email,
            'student_email' => $request->email ?? null,
        ], function () use ($request, $userPassword, $domain, $userName) {
            Mail::to($request->parent_email)->bcc(env('MAIL_BCC'))->send(new CreatedStudentMail('', $userPassword, $request->name, 0, $domain, $userName));
        });

        $student_email = new EmailInfo;
        $student_email->name = $request->name;
        // $student_email->mail_address = $request->parent_email;
        $student_email->mail_description = '';
        $student_email->group = 4;
        $student_email->save();

        return $this->sendResponse('Booking Done Successfully.', 200, new StudentResource($student));
    }

    /**
     * Public student project/profile API for the portfolio page.
     * $student_username is students.public_username, which is unique across all students.
     */
    public function portfolio($student_username = null)
    {
        $student_username = trim((string) $student_username);

        if ($student_username === '') {
            return $this->sendResponse('Student username is required.', 422);
        }

        if (!preg_match('/^[A-Za-z0-9-]+$/', $student_username)) {
            return $this->sendResponse('Please provide a valid student username.', 422);
        }

        $student = Students::with(['country:id,name', 'getAssignedGrade:id,name'])
            ->where('public_username', $student_username)
            ->select('id', 'name', 'school_id', 'country_id', 'student_grade_id', 'public_username')
            ->first();

        if (!$student) {
            return $this->sendResponse("Student not found", 404);
        }

        return $this->sendResponse('Student projects fetched successfully.', 200, [
            'student_name'     => $student->name,
            'grade'            => optional($student->getAssignedGrade)->name,
            'country'          => optional($student->country)->name,
            'projects'         => $this->publishedProjects($student->id),
        ]);
    }

    /**
     * List of students for a school: name, display picture and public_username.
     * $school_username is the first label of the school's domain (e.g. "test1" for "test1.kids.test").
     */
    public function studentList($school_username = null)
    {
        $school_username = trim((string) $school_username);

        if ($school_username === '') {
            return $this->sendResponse('School username is required.', 422);
        }

        if (!preg_match('/^[A-Za-z0-9-]+$/', $school_username)) {
            return $this->sendResponse('Please provide a valid school username.', 422);
        }

        $school = $this->resolveSchoolByUsername($school_username);

        if (!$school) {
            return $this->sendResponse("School not found", 404);
        }

        $students = Students::where('school_id', $school->id)
            ->select('id', 'name', 'public_username', 'image')
            ->orderBy('name')
            ->get()
            ->map(fn ($student) => [
                'name'             => $student->name,
                'public_username'  => $student->public_username,
                // 'display_picture'  => $this->studentDisplayPicture($student->image),
            ])
            ->values();

        return $this->sendResponse('Student list fetched successfully.', 200, [
            'students' => $students,
        ]);
    }

    /**
     * Resolve a school by the first label of its domain (e.g. "test1" for "test1.kids.test").
     */
    private function resolveSchoolByUsername($school_username)
    {
        $domain = Domain::select('tenant_id')
            ->whereRaw("SUBSTRING_INDEX(domain, '.', 1) = ?", [$school_username])
            ->first();

        if (!$domain) {
            return null;
        }

        return School::where('tenant_id', $domain->tenant_id)
            ->whereHas('user', function ($query) {
                $query->where('suspend', 2);
            })
            ->first();
    }

    /**
     * students.image is stored inconsistently in legacy data - sometimes with a leading
     * "tenants/" segment, sometimes without - so strip it before re-prefixing to avoid
     * a doubled "tenants/tenants/..." URL.
     */
    private function studentDisplayPicture($image)
    {
        if (empty($image)) {
            return asset('img/default_image.png');
        }

        return asset('tenants/' . ltrim(preg_replace('#^tenants/#', '', $image), '/'));
    }

    /**
     * Published project details for a student: summary fields (id, title, published_at, image)
     * plus the full breakdown of the sections/questions the student chose to publish.
     * Batched (no N+1) regardless of project/section/question count.
     */
    private function publishedProjects($studentId)
    {
        $publishedProjects = StudentProject::where('student_id', $studentId)
            ->where('status', 6)
            ->whereNotNull('published_sections')
            ->get(['id', 'published_sections', 'updated_at'])
            ->filter(fn ($project) => !empty($project->published_sections))
            ->values();

        if ($publishedProjects->isEmpty()) {
            return [];
        }

        $studentProjectIds = $publishedProjects->pluck('id');
        $sectionIds = $publishedProjects->pluck('published_sections')->flatten()->unique()->values();

        $sections = ProjectSection::whereIn('id', $sectionIds)
            ->where('status', 1)
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get()
            ->keyBy('id');

        $titlesByProject = StudentProjectAnswer::whereIn('student_project_answers.student_project_id', $studentProjectIds)
            ->join('project_questions', 'project_questions.id', '=', 'student_project_answers.project_question_id')
            ->where('project_questions.field_text', 'like', '%title%')
            ->get(['student_project_answers.student_project_id', 'student_project_answers.answer_text'])
            ->groupBy('student_project_id')
            ->map(fn ($rows) => optional($rows->first())->answer_text);

        $answersByProject = StudentProjectAnswer::whereIn('student_project_id', $studentProjectIds)
            ->get()
            ->groupBy('student_project_id')
            ->map(fn ($rows) => $rows->pluck('answer_text', 'project_question_id'));

        $attachmentsByProject = StudentProjectAttachment::whereIn('student_project_id', $studentProjectIds)
            ->get()
            ->groupBy(['student_project_id', 'project_question_id']);

        return $publishedProjects->map(function ($project) use ($sections, $titlesByProject, $answersByProject, $attachmentsByProject) {
            $answers = $answersByProject->get($project->id, collect());
            $attachments = $attachmentsByProject->get($project->id, collect());

            $sectionsData = collect($project->published_sections)
                ->map(fn ($sectionId) => $sections->get($sectionId))
                ->filter()
                ->sortBy('display_order')
                ->map(function ($section) use ($answers, $attachments) {
                    return [
                        'title' => $section->section_title,
                        'questions' => $section->questions->map(function ($question) use ($answers, $attachments) {
                            $data = [
                                'text' => $question->field_text,
                                'answer' => $question->field_type !== 'file' ? $answers->get($question->id) : null,
                            ];

                            if ($question->allow_attachments) {
                                $data['attachments'] = ($attachments->get($question->id) ?? collect())
                                    ->map(fn ($attachment) => [
                                        'type' => $attachment->file_type,
                                        'url' => $attachment->url,
                                    ])->values();
                            }

                            return $data;
                        })->values(),
                    ];
                })->values();

            // $image = $sectionsData
            //     ->flatMap(fn ($section) => $section['questions'])
            //     ->flatMap(fn ($question) => $question['attachments'] ?? [])
            //     ->first(fn ($attachment) => $attachment['type'] === 'images');

            return [
                // 'id' => $project->id,
                'title' => $titlesByProject->get($project->id),
                // 'published_at' => optional($project->updated_at)->toDateTimeString(),
                // 'image' => $image['url'] ?? null,
                'sections' => $sectionsData,
            ];
        })->values();
    }
}
