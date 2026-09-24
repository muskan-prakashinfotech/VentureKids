<?php

namespace App\Http\Controllers\Backend;

use App\Events\Backend\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdminRequest;
use App\Http\Requests\StudentRequest;
use App\Mail\CreatedSchoolMail;
use App\Mail\CreatedStudentMail;
use App\Mail\PendingSchoolSubmittedMail;
use App\Mail\PartnerSchoolEditedMail;
use App\Mail\StudentChangePasswordMail;
use App\Models\AdminNotification;
use App\Models\AssessmentStudentReport;
use App\Models\ClassSchedule;
use App\Models\EmailInfo;
use App\Models\EmailNotification;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\PendingSchool;
use App\Models\Role;
use App\Models\School;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentScale;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\SchoolAcademicYear;
use App\Models\SchoolNotification;
use App\Models\StudentCommunications;
use App\Models\Students;
use App\Models\StudentLicense;
use App\Rules\CsvValidator;
use App\Models\User;
use App\Services\StudentProgressService;
use App\Models\Userprofile;
use App\Models\Country;
use App\Traits\CsvFIleupload;
use App\Traits\CsvImportNormalizer;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Image;
use App\Jobs\StudentCreate;
use App\Jobs\InformAdminToAllocateTrainerToSchool;
use App\Jobs\SchoolEdited;
use App\Models\StudentGrade;
use App\Models\SchoolBatch;
use App\Models\TrainerAllocationNew;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\Project;
use App\Models\Event;
use App\Models\EventChallenge;
use App\Models\QuizAttempts;
use App\Models\Stream;
use App\Models\StudentRewardPoints;
use App\Models\WeeklyChallenges;
use Ramsey\Uuid\Uuid;
use App\Rules\DomainValidation;
use Illuminate\Support\Facades\Validator;
use App\Helpers\StudentRewardPointsHelper;
use App\Models\StudentObservations;
use App\Helpers\CommonHelper;
use App\Helpers\StudentObservationHelper;
use App\Services\SchoolOnboardingService;
use Log;
use App\Mail\ImportedStudentsMail;
use App\Models\StudentBoard;
use App\Exports\StudentsExport;
use Maatwebsite\Excel\Facades\Excel;

class SchoolController extends Controller
{
    use CsvFIleupload, CsvImportNormalizer;


    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:school_edit');
        //$this->middleware('role:admin|writer')->only('testmiddleware');
        $this->module_name = 'users';
    }

    public function schoolCreate()
    {
        // $link = route('login');
        // $principal_name = "Kids princlce";
        // $school_name = "school name";
        // $username = "username";
        // $password = "password";
        // return view('emails.created-school-name', compact('link','principal_name','school_name','username', 'password'));
        $grade = Grade::all();
        $countries = Country::get(['id', 'name'])->sortBy('name');
        $selectedCountryId = partnerCountryId();

        if (isPartnerUser() && empty($selectedCountryId)) {
            return redirect()->route('backend.schoollist.schoolList')->with('email_faild', 'No country has been assigned to your account.');
        }

        $partnerCurrencyType = $this->partnerCurrencyType();

        return view('backend.school.add_school', compact('grade', 'countries', 'selectedCountryId', 'partnerCurrencyType'));
    }

    public function schoolStore(Request $req)
    {
        if (isPartnerUser()) {
            $forcedCountryId = partnerCountryId();

            if (empty($forcedCountryId)) {
                return redirect()->back()->with('email_faild', 'No country has been assigned to your account.')->withInput();
            }

            if (!empty($forcedCountryId)) {
                $req->merge(['country' => $forcedCountryId]);
            }

            $partnerCurrencyType = $this->partnerCurrencyType();
            if (empty($partnerCurrencyType)) {
                return redirect()->back()->with('email_faild', 'No currency has been assigned to your account.')->withInput();
            }
            $req->merge([
                'currency_type' => $partnerCurrencyType,
                'logout_redirect_url' => null,
            ]);
        }

        $validatedData = Validator::make($req->all(), [
            'school_name' => 'required',
            'principle_name' => 'required',
            'email' => 'required|email',
            'contact_number' => 'nullable',
            'country' => 'required',
            'city' => 'required',
            'currency_type' => 'required',
            'fee_per_student' => 'nullable',
            'number_of_student' => 'required',
            'course_start_date' => 'required',
            'course_end_date' => 'required',
            'school_domain' => [
                'required',
                new DomainValidation(),
            ],
            'logout_redirect_url' => 'nullable|url',
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        if ($this->schoolDomainExists($req->school_domain)) {
            return redirect()->back()->withErrors([
                'school_domain' => 'This school domain is already in use.',
            ])->withInput();
        }

        if (isPartnerUser()) {
            return $this->storePendingSchool($req);
        }

        try {
            $result = app(SchoolOnboardingService::class)->createSchool($req->all());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('email_faild', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            Log::debug($e);
            return redirect()->back()->with('email_faild', 'Failed to create user and profile.')->withInput();
        }

        return redirect()->route('backend.schoollist.schoolList')->with([
            'message' => 'School created successfully!',
        ]);
    }

    private function storePendingSchool(Request $req)
    {
        if (isPartnerUser()) {
            $forcedCountryId = partnerCountryId();

            if (empty($forcedCountryId)) {
                return redirect()->back()->with('email_faild', 'No country has been assigned to your account.')->withInput();
            }

            if (!empty($forcedCountryId)) {
                $req->merge(['country' => $forcedCountryId]);
            }
        }

        $emailExists = User::where('email', $req->email)->exists();
        $pendingExists = PendingSchool::where('status', 'pending')
            ->where('form_data->email', $req->email)
            ->exists();

        if ($emailExists || $pendingExists) {
            return redirect()->back()->with('email_faild', 'Sorry Email Already Exits.')->withInput();
        }

        $pendingSchool = PendingSchool::create([
            'submitted_by' => auth()->id(),
            'form_data' => $req->only([
                'school_name',
                'principle_name',
                'email',
                'contact_number',
                'country',
                'city',
                'currency_type',
                'fee_per_student',
                'number_of_student',
                'course_start_date',
                'course_end_date',
                'school_domain',
                'batch_name',
            ]),
            'status' => 'pending',
        ]);

        safeMailAction('pending school submitted mail', [
            'pending_school_id' => $pendingSchool->id,
            'recipient' => env('MAIL_ADMIN'),
        ], function () use ($req) {
            Mail::to(env('MAIL_ADMIN'))->bcc(env('MAIL_BCC'))->send(
                new PendingSchoolSubmittedMail(
                    $req->school_name,
                    auth()->user()->name ?? 'Partner',
                    auth()->user()->email ?? '',
                    route('backend.pending-schools.index')
                )
            );
        });
        
        return redirect()->route('backend.schoollist.schoolList')->with([
            'message' => 'School request submitted for approval.',
        ]);
    }

    public function schoolList()
    {
        $school_list = applyCountryScope(
            School::with('user', 'students'),
            'country_id'
        )->get();

        if (isPartnerUser()) {
            $pendingSchools = PendingSchool::where('submitted_by', auth()->id())
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(function (PendingSchool $pendingSchool) {
                    $data = $pendingSchool->form_data ?: [];
                    $row = new \stdClass();
                    $row->is_pending = true;
                    $row->pending_id = $pendingSchool->id;
                    $row->id = null;
                    $row->school_logo = null;
                    $row->school_cover_image = null;
                    $row->tenant_id = null;
                    $row->user_id = null;
                    $row->country_id = $data['country'] ?? null;
                    $row->school_name = $data['school_name'] ?? '';
                    $row->city = $data['city'] ?? '';
                    $row->incharge_name = '';
                    $row->official_email_id = $data['email'] ?? '';
                    $row->contact_number = $data['contact_number'] ?? '';
                    $row->status = 0;
                    $row->students_count = 0;
                    $row->user = null;
                    return $row;
                });

            $school_list = $school_list->map(function ($school) {
                $school->is_pending = false;
                return $school;
            })->concat($pendingSchools);
        }

        return view('backend.school.school_list', compact('school_list'));
    }

    /**
     * Generate deterministic, globally unique public usernames for every student.
     */
    public function generatePublicUsernames()
    {
        $usedUsernames = [];
        $processedCount = 0;
        $updatedCount = 0;

        Students::withoutGlobalScopes()
            ->select(['id', 'name', 'public_username'])
            ->orderBy('id')
            ->chunkById(500, function ($students) use (&$usedUsernames, &$processedCount, &$updatedCount) {
                $updates = [];

                foreach ($students as $student) {
                    $base = Students::basePublicUsername($student->name);
                    $username = $base;
                    $suffix = 1;

                    while (isset($usedUsernames[$username])) {
                        $username = $base . $suffix;
                        $suffix++;
                    }

                    $usedUsernames[$username] = true;
                    $processedCount++;

                    if ($student->public_username !== $username) {
                        $updatedCount++;
                        $updates[] = [
                            'id' => $student->id,
                            'public_username' => $username,
                        ];
                    }
                }

                if (!empty($updates)) {
                    DB::table('students')->upsert($updates, ['id'], ['public_username']);
                }
            });

        return response()->json([
            'success' => true,
            'processed_count' => $processedCount,
            'updated_count' => $updatedCount,
            'message' => 'Globally unique public usernames have been generated for all students.',
        ]);
    }

    public function schoolEdit($id)
    {
        $this->ensurePartnerSchoolAccess($id);
        $school = School::with('batches', 'domains')->find($id);
        $school['school_css'] = '';
        $school['school_logo_path'] = 'image/school/' . $school['school_logo'];
        $school['school_cover_image_path'] = '/image/school/cover_image/' . $school['school_cover_image'];
        $activeAcademicYears = SchoolAcademicYear::where('school_id', $id)
            ->where('is_active', 1)
            ->orderByDesc('end_date')
            ->get();
        $pastAcademicYears = SchoolAcademicYear::where('school_id', $id)
            ->whereDate('end_date', '<', Carbon::today())
            ->orderByDesc('end_date')
            ->get();
        if (!empty($school)) {
            $school = (is_object($school)) ? $school->toArray() : $school;

            if (!empty($school['tenant_id'])) {

                /* START - SCHOOL LOGO PATH */
                $logoPath = \Storage::disk('tenant_uploads')->path($school['school_logo']);
                if (File::exists($logoPath)) {
                    $school['school_logo_path'] = 'tenants/'.$school['school_logo'];
                    $school['school_logo'] = basename($school['school_logo']);
                }
                /* END - SCHOOL LOGO PATH */

                /* START - SCHOOL COVER PATH */
                $schoolCoverPath = \Storage::disk('tenant_uploads')->path($school['school_cover_image']);
                if (File::exists($schoolCoverPath)) {
                    $school['school_cover_image_path'] = 'tenants/'.$school['school_cover_image'];
                    $school['school_cover_image'] = basename($school['school_cover_image']);
                }
                /* END - SCHOOL COVER PATH */

                /* START - SCHOOL Loader */
                $loaderPath = \Storage::disk('tenant_uploads')->path($school['tenant_id'].'/school/loader/school-loader.png');
                if (File::exists($loaderPath)) {
                    $school['school_loader_path'] = 'tenants/'.$school['tenant_id'].'/school/loader/school-loader.png';
                    $school['school_loader'] = 'school-loader.png';
                }
                /* END - SCHOOL Loader */

                /* START - SCHOOL CSS */
                $cssPath = \Storage::disk('tenant_uploads')->path($school['tenant_id'].'/school/css/custom.css');
                if (File::exists($cssPath)) {
                    $school['school_css_path'] = 'tenants/'.$school['tenant_id'].'/school/css/custom.css';
                    $school['school_css'] = 'custom.css';
                }
                /* END - SCHOOL CSS */

                /* IF SCHOOL HAS MULTIPLE DOMAINS */
                $schoolHasMultipleDomain = \App\Models\Domain::select('domain')->where('tenant_id', $school['tenant_id'])
                    ->where('domain', 'not like', '%' . config('tenancy.sub_domain'))
                    ->get();
                /* IF SCHOOL HAS MULTIPLE DOMAINS */
            }
        }

        return view('backend.school.edit_school')->with([
            'school' => $school,
            'grade' => Grade::all(),
            'studentGrades' => StudentGrade::orderBy('name')->get(['id', 'name']),
            'studentBoards' => StudentBoard::orderBy('name')->get(['id', 'name']),
            'realqParameters' => RealQAssessmentParameter::orderBy('name')->get(['id', 'name']),
            'realqScales' => RealQAssessmentScale::orderBy('id')->get(['id', 'name']),
            'realqAssignments' => RealQAssessmentSchoolAssignment::where('school_id', $school['id'] ?? 0)->get(),
            'countries' => Country::get(['id', 'name'])->sortBy('name'),
            'selectedCountryId' => isPartnerUser() ? partnerCountryId() : ($school['country_id'] ?? null),
            'partnerCurrencyType' => $this->partnerCurrencyType(),
            'schoolHasMultipleDomain' => $schoolHasMultipleDomain,
            'activeAcademicYears' => $activeAcademicYears,
            'pastAcademicYears' => $pastAcademicYears,
        ]);

    }

    public function schoolUpdate(Request $req)
    {
        try {
            DB::beginTransaction();

            $validatedData = Validator::make($req->all(), [
                'school_name' => 'required',
                'principle_name' => 'required',
                'official_email_id' => 'required',
                'contact_number' => 'nullable',
                'country' => 'required',
                'city' => 'required',
                'currency_type' => 'required',
                'fee_per_student' => 'nullable',
                'number_of_student'=>'required',
                'course_start_date' => 'required',
                'course_end_date' => 'required',
                'academic_year_start_date' => 'nullable|date|required_with:academic_year_end_date',
                'academic_year_end_date' => 'nullable|date|after_or_equal:academic_year_start_date|required_with:academic_year_start_date',
                'school_domain' => [
                    'nullable',
                    new DomainValidation()
                ],
                'assessment_assignment_standard' => 'nullable|boolean',
                'assessment_assignment_realq' => 'nullable|boolean',
                'logout_redirect_url' => 'nullable|url',
                'school_logo' => 'file|mimes:jpeg,png,jpg|max:5120', // 5MB
                'school_cover_image' => 'file|mimes:jpeg,png,jpg|max:5120', // 5MB
            ]);

            if ($validatedData->fails()) {
                return redirect()->back()->withErrors($validatedData)->withInput();
            }

            if ($req->boolean('assessment_assignment_realq')) {
                $realqValidator = Validator::make($req->all(), [
                    'realq_assessment_enabled_from' => 'required|date',
                    'realq_assessment_enabled_to' => 'required|date|after_or_equal:realq_assessment_enabled_from',
                    'realq_assessment_assigned_board_id' => 'required|integer',
                    'realq_assessment_assigned_scale_id' => 'required|integer',
                    'realq_assessment_assigned_grade_id' => 'required|array|min:1',
                    'realq_assessment_assigned_parameters_id' => 'required|array|min:1',
                ]);

                if ($realqValidator->fails()) {
                    return redirect()->back()->withErrors($realqValidator)->withInput();
                }

                $gradeIds = $req->input('realq_assessment_assigned_grade_id', []);
                $paramsByRow = $req->input('realq_assessment_assigned_parameters_id', []);
                foreach ($gradeIds as $idx => $gradeId) {
                    $paramIds = $paramsByRow[$idx] ?? [];
                    if (empty($gradeId) || empty($paramIds)) {
                        return redirect()->back()->withErrors([
                            'realq_assessment_assigned_parameters_id' => 'Please select at least one parameter for each grade.',
                        ])->withInput();
                    }
                }
            }

            $schhol_id = $req->school_id;

            if (isPartnerUser()) {
                $forcedCountryId = partnerCountryId();
                if (empty($forcedCountryId)) {
                    DB::rollBack();
                    return redirect()->back()->with('email_faild', 'No country has been assigned to your account.')->withInput();
                }
                $req->merge(['country' => $forcedCountryId]);
                $partnerCurrencyType = $this->partnerCurrencyType();
                if (empty($partnerCurrencyType)) {
                    DB::rollBack();
                    return redirect()->back()->with('email_faild', 'No currency has been assigned to your account.')->withInput();
                }
                $req->merge([
                    'currency_type' => $partnerCurrencyType,
                    'logout_redirect_url' => null,
                ]);
            }

            $school_data = School::select(
                'id',
                'school_name',
                'school_logo',
                'school_cover_image',
                'tenant_id',
                'white_label',
                'country_id',
                'course_start_date',
                'course_end_date',
                'standard_assessment_assigned',
                'standard_assessment_enabled_from',
                'standard_assessment_enabled_to',
                'logout_redirect_url'
            )->find($schhol_id);
            if (!$school_data) {
                DB::rollBack();
                abort(404);
            }

            if ($this->schoolDomainExists($req->school_domain, $school_data->tenant_id)) {
                DB::rollBack();
                return redirect()->back()->withErrors([
                    'school_domain' => 'This school domain is already in use.',
                ])->withInput();
            }
            $currentAcademicYear = SchoolAcademicYear::where('school_id', $schhol_id)
                ->orderByDesc('is_active')
                ->orderByDesc('end_date')
                ->first();
            $currentRealqAssignment = RealQAssessmentSchoolAssignment::where('school_id', $schhol_id)
                ->orderByDesc('id')
                ->first();
            $partnerEditChanges = [];
            if (isPartnerUser()) {
                $partnerEditChanges = $this->getPartnerSchoolEditChanges($school_data, $currentAcademicYear, $currentRealqAssignment, $req);
            }

            $email = $req->school_email;
            $user = User::where('email', $req->official_email_id)->first();
            if (!empty($user)) {
                if ($user['email'] == $req->official_email_id && $user['id'] == $req->user_id) {
                    $user = User::find($req->user_id);
                    $user->name = $req->school_name;
                    $user->email = $req->official_email_id;
                    $user->country_id = $req->country;
                    $user->save();
                } else {
                    return redirect()->back()->with('email_faild', 'Sorry School Official Email Already Exits.');
                }
            } else {
                $user = User::find($req->user_id);
                $user->name = $req->school_name;
                $user->email = $req->official_email_id;
                $user->country_id = $req->country;
                $user->save();
            }

            $user_profile = Userprofile::where('user_id', $req->user_id)->first();
            if (!empty($user_profile)) {
                $user_profile->email = $req->official_email_id;
                $user_profile->name = $req->school_name;
                $user->country_id = $req->country;
                $user_profile->save();
            }

            if(!empty($req->incharge_email)) {
                $school_row = School::where('incharge_email', $req->incharge_email)->first();
                if (!empty($school_row)) {
                    if ($school_row['incharge_email'] == $req->incharge_email && $school_row['id'] == $req->school_id) {
                        $data['incharge_email'] = $req->incharge_email;
                    } else {
                        return redirect()->back()->with('email_faild', 'Sorry In-charge Email ID Already Exits.');
                    }
                } else {
                    $data['incharge_email'] = $req->incharge_email;
                }
            }

            $data['school_name'] = $req->school_name;
            $data['principle_name'] = $req->principle_name;
            $data['official_email_id'] = $req->official_email_id;
            $data['contact_number'] = $req->contact_number ?? '';
            $data['incharge_name'] = $req->incharge_name;
            // $data['venturekids_representative'] = $req->partner_name;
            $data['school_address'] = $req->address;
            $data['country_id'] = $req->country;
            $data['city'] = $req->city;
            $data['year_establish'] = $req->year_establish;
            $data['currency_type'] = $req->currency_type;
            $data['fee_per_student'] = $req->fee_per_student ?? 0;
            $data['number_of_student'] = $req->number_of_student;
            $data['course_start_date'] = $req->course_start_date;
            $data['course_end_date'] = $req->course_end_date;

            if (!empty($req->academic_year_start_date) && !empty($req->academic_year_end_date)) {
                $academicYearStartDate = Carbon::parse($req->academic_year_start_date)->toDateString();
                $academicYearEndDate = Carbon::parse($req->academic_year_end_date)->toDateString();
                $academicYearIsActive = Carbon::parse($academicYearEndDate)->gte(Carbon::today());

                if ($academicYearIsActive) {
                    SchoolAcademicYear::where('school_id', $schhol_id)->update([
                        'is_active' => 0,
                    ]);
                }

                SchoolAcademicYear::updateOrCreate([
                    'school_id' => $schhol_id,
                    'start_date' => $academicYearStartDate,
                    'end_date' => $academicYearEndDate,
                ], [
                    'is_active' => $academicYearIsActive ? 1 : 0,
                ]);
            }

            if (!isPartnerUser() && isset($req->white_label)) {

                $data['white_label'] = 1;

                $school_logo = $req->school_logo;
                if ($school_logo) {
                    $logoPath = \Storage::disk('tenant_uploads')->path($school_data->school_logo);
                    if ($school_data && !empty($school_data->school_logo)) {
                        if (File::exists($logoPath)) {
                            File::delete($logoPath);
                        }
                    }
                    $ext = strtolower($school_logo->getClientOriginalExtension());
                    $school_logo_name = 'logo'.".". $ext;
                    $path = $school_logo->storeAs($school_data->tenant_id.'/school', $school_logo_name, 'tenant_uploads');
                    $data['school_logo'] = $school_data->tenant_id.'/school/'. $school_logo_name;
                }
    
                $school_cover = $req->school_cover_image;
                if ($school_cover) {
                    $coverPath = \Storage::disk('tenant_uploads')->path($school_data->school_cover_image);
                    if ($school_data && !empty($school_data->school_cover_image)) {
                        if (File::exists($coverPath)) {
                            File::delete($coverPath);
                        }
                    }
    
                    $ext = strtolower($school_cover->getClientOriginalExtension());
                    $school_cover_name = 'login_cover'.".". $ext;
                    $path = $school_cover->storeAs($school_data->tenant_id.'/school', $school_cover_name, 'tenant_uploads');
                    $data['school_cover_image'] = $school_data->tenant_id.'/school/'. $school_cover_name;
                }
    
                $school_css = $req->school_css;
                if ($school_css) {
                    $existCssFilePath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/css');
    
                    // CHECK IF THE DIRECTORY EXISTS, IF NOT, CREATE IT
                    if (!File::isDirectory($existCssFilePath)) {
                        File::makeDirectory($existCssFilePath, 0755, true, true);
                    }
    
                    // DELETE ANY EXISTING CSS FILES IN THE DIRECTORY
                    $files = File::files($existCssFilePath);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'css') {
                            File::delete($file->getRealPath());
                        }
                    }
    
                    $school_css_name = 'custom.css';
    
                    $path = $school_css->storeAs($school_data->tenant_id.'/school/css', $school_css_name, 'tenant_uploads');
                }
    
                $school_loader = $req->school_loader;
                if ($school_loader) {
                    $existLoaderFilePath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/loader');
    
                    // CHECK IF THE DIRECTORY EXISTS, IF NOT, CREATE IT
                    if (!File::isDirectory($existLoaderFilePath)) {
                        File::makeDirectory($existLoaderFilePath, 0755, true, true);
                    }
    
                    // DELETE ANY EXISTING PNG FILES IN THE DIRECTORY
                    $files = File::files($existLoaderFilePath);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'png') {
                            File::delete($file->getRealPath());
                        }
                    }
    
                    $school_loader_name = 'school-loader.png';
    
                    $path = $school_loader->storeAs($school_data->tenant_id.'/school/loader', $school_loader_name, 'tenant_uploads');
                    
                }
                
                $data['logout_redirect_url'] = $req->logout_redirect_url;

            } else if (!isPartnerUser() && $school_data->white_label != $req->white_label) {
                $data['white_label'] = 0;

                $logoPath = \Storage::disk('tenant_uploads')->path($school_data->school_logo);
                if ($school_data && !empty($school_data->school_logo)) {
                    if (File::exists($logoPath)) {
                        File::delete($logoPath);
                    }
                    $data['school_logo'] = null;
                }
                
                $coverPath = \Storage::disk('tenant_uploads')->path($school_data->school_cover_image);
                if ($school_data && !empty($school_data->school_cover_image)) {
                    if (File::exists($coverPath)) {
                        File::delete($coverPath);
                    }
                    $data['school_cover_image'] = null;
                }

                // DELETE ANY EXISTING CSS FILES IN THE DIRECTORY
                $existCssFilePath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/css');
                if (File::isDirectory($existCssFilePath)) {
                    $files = File::files($existCssFilePath);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'css') {
                            File::delete($file->getRealPath());
                        }
                    }
                }

                // DELETE ANY EXISTING PNG FILES IN THE DIRECTORY
                $existLoaderFilePath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/loader');
                if (File::isDirectory($existLoaderFilePath)) {
                    $files = File::files($existLoaderFilePath);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'png') {
                            File::delete($file->getRealPath());
                        }
                    }
                }

                $data['logout_redirect_url'] = null;

            }

            $selectedLevels = $req->input('default_grade', []);
            if (!empty($selectedLevels)) {
                $data['default_grade'] = implode(',', $selectedLevels);
            } else {
                $data['default_grade'] = null;
            }

            $isStandardAssigned = $req->boolean('assessment_assignment_standard');
            $isRealqAssigned = $req->boolean('assessment_assignment_realq');
            $existingRealqAssignments = RealQAssessmentSchoolAssignment::where('school_id', $schhol_id)->get();
            $existingRealqEnabled = $existingRealqAssignments->where('realq_assessment_enabled', 1)->count() > 0;

            // Standard assessment assignment
            $data['standard_assessment_assigned'] = $isStandardAssigned ? 1 : 0;
            if ($isStandardAssigned) {
                $data['standard_assessment_enabled_from'] = $req->standard_assessment_enabled_from ?: null;
                $data['standard_assessment_enabled_to'] = $req->standard_assessment_enabled_to ?: null;
                $data['standard_assessment_enabled'] =
                    (!empty($data['standard_assessment_enabled_from']) && !empty($data['standard_assessment_enabled_to'])) ? 1 : 0;
            } else {
                $data['standard_assessment_enabled'] = 0;
                $data['standard_assessment_enabled_from'] = null;
                $data['standard_assessment_enabled_to'] = null;
            }

            $data['status'] = 1;

            $tenantId = null;
            if (request()->has('school_domain') && !empty($req->school_domain) && empty($school_data['tenant_id'])) {
                $tenantId = Uuid::uuid4()->toString();
                $data['tenant_id'] = $tenantId;
            } else {
                $tenantId = $school_data['tenant_id'];
            }


            $success = School::where('id', $schhol_id)->update($data);

            if ($success) {
                RealQAssessmentSchoolAssignment::where('school_id', $schhol_id)->delete();
                if ($isRealqAssigned) {
                    $grades = $req->input('realq_assessment_assigned_grade_id', []);
                    $paramsByRow = $req->input('realq_assessment_assigned_parameters_id', []);
                    $assignedBoardId = $req->realq_assessment_assigned_board_id ?: null;
                    $assignedScaleId = $req->realq_assessment_assigned_scale_id ?: null;
                    $enabledFrom = $req->realq_assessment_enabled_from ?: null;
                    $enabledTo = $req->realq_assessment_enabled_to ?: null;
                    $enabledFlag = $existingRealqEnabled ? 1 : 0;

                    foreach ($grades as $index => $gradeId) {
                        $paramIds = $paramsByRow[$index] ?? [];
                        if (empty($gradeId) || empty($paramIds)) {
                            continue;
                        }
                        RealQAssessmentSchoolAssignment::create([
                            'school_id' => $schhol_id,
                            'realq_assessment_assigned' => 1,
                            'realq_assessment_enabled' => $enabledFlag,
                            'realq_assessment_enabled_from' => $enabledFrom,
                            'realq_assessment_enabled_to' => $enabledTo,
                            'realq_assessment_assigned_board_id' => $assignedBoardId,
                            'realq_assessment_assigned_grade_id' => $gradeId,
                            'realq_assessment_assigned_parameters_id' => implode(',', $paramIds),
                            'realq_assessment_assigned_scale_id' => $assignedScaleId,
                        ]);
                    }
                }
            }

            /* START - IF SCHOOL HAS DOMAIN THEN STORE INTO THE DOMAIN MODEL WITH TENANT */
            if ($success && request()->has('school_domain')) {

                $fullDomain = null;
                $domainPrefix = request()->get('school_domain');

                if (!empty($domainPrefix)) { // PREPARE FULL DOMAIN
                    $fullDomain = trim($domainPrefix) . config("tenancy.sub_domain");
                }

                // CHECK IF TENANT HAS ALREADY DOMAIN
                $domainAsTenant = \App\Models\Domain::where('tenant_id', $tenantId)->first();

                if ($domainAsTenant) { // IF TENANT FOUND THEN UPDATE DOMAIN
                    $domainAsTenant->update([
                        'domain' => $fullDomain,
                    ]);
                } else { // CREATE NEW DOMAIN FOR TENANT
                    \App\Models\Domain::create([
                        'tenant_id' => $tenantId,
                        'domain' => $fullDomain,
                    ]);
                }
            }
            /* END - IF SCHOOL HAS DOMAIN THEN STORE INTO THE DOMAIN MODEL WITH TENANT */

            // START - Store School Batches
            $batches = $req->batch_name;
            if(!empty($batches)) {
                $batchIdList = $req->batchIdList;
                foreach($batches as $key => $batch) {
                    if(!empty($batch)) {
                        if(!empty($batchIdList) && array_key_exists($key, array_keys($batchIdList))) {
                            $batchInsert = SchoolBatch::find($batchIdList[$key]);
                        } else {
                            $batchInsert = new SchoolBatch;
                            $batchInsert->school_id = $schhol_id;
                        }
                        $batchInsert->batch_name = $batch;
                        $batchInsert->save();
                    }
                }
            }
            // END - Store School Batches

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('email_faild', 'Failed to update user profile.');
        }

        if ($success && isPartnerUser()) {
            $this->notifySuperAdminOfPartnerSchoolChanges($school_data, $partnerEditChanges);
        }

        if ($success) {
            $notification = [
                'message' => 'School updated successfully!',
                'success' => 'success',
            ];

            return redirect()->route('backend.schoollist.schoolList')->with($notification);
        } else {
            return redirect()->route('backend.schoollist.schoolList')->with(['message' => 'Error occurred. Please try again!', 'success' => 'error']);
        }
    }

    private function getPartnerSchoolEditChanges($schoolData, $currentAcademicYear, $currentRealqAssignment, Request $req): array
    {
        $changes = [];

        $standardAssignedOld = (bool) ($schoolData->standard_assessment_assigned ?? false);
        $standardAssignedNew = $req->boolean('assessment_assignment_standard');
        if ($standardAssignedOld !== $standardAssignedNew) {
            $changes[] = [
                'label' => 'Standard Assessment',
                'old' => $this->formatYesNoValue($standardAssignedOld),
                'new' => $this->formatYesNoValue($standardAssignedNew),
            ];
        }

        $standardFromOld = $this->formatDateValue($schoolData->standard_assessment_enabled_from ?? null);
        $standardFromNew = $this->formatDateValue($req->standard_assessment_enabled_from ?? null);
        if ($standardFromOld !== $standardFromNew) {
            $changes[] = [
                'label' => 'Standard Assessment Start Date',
                'old' => $standardFromOld,
                'new' => $standardFromNew,
            ];
        }

        $standardToOld = $this->formatDateValue($schoolData->standard_assessment_enabled_to ?? null);
        $standardToNew = $this->formatDateValue($req->standard_assessment_enabled_to ?? null);
        if ($standardToOld !== $standardToNew) {
            $changes[] = [
                'label' => 'Standard Assessment End Date',
                'old' => $standardToOld,
                'new' => $standardToNew,
            ];
        }

        $realqAssignedOld = !empty($currentRealqAssignment);
        $realqAssignedNew = $req->boolean('assessment_assignment_realq');
        if ($realqAssignedOld !== $realqAssignedNew) {
            $changes[] = [
                'label' => 'RealQ Assessment',
                'old' => $this->formatYesNoValue($realqAssignedOld),
                'new' => $this->formatYesNoValue($realqAssignedNew),
            ];
        }

        $realqBoardOld = $this->formatLookupValue(optional($currentRealqAssignment)->realq_assessment_assigned_board_id ?? null, StudentBoard::class);
        $realqBoardNew = $this->formatLookupValue($req->realq_assessment_assigned_board_id ?? null, StudentBoard::class);
        if ($realqBoardOld !== $realqBoardNew) {
            $changes[] = [
                'label' => 'RealQ Assessment Block/Section',
                'old' => $realqBoardOld,
                'new' => $realqBoardNew,
            ];
        }

        $academicYearStartDate = $req->academic_year_start_date ?? null;
        $academicYearEndDate = $req->academic_year_end_date ?? null;
        if (!empty($academicYearStartDate) || !empty($academicYearEndDate)) {
            $currentAcademicYearRange = $this->formatAcademicYearRange($currentAcademicYear);
            $newAcademicYearRange = $this->formatAcademicYearRangeFromRequest($academicYearStartDate, $academicYearEndDate);
            if ($currentAcademicYearRange !== $newAcademicYearRange) {
                $changes[] = [
                    'label' => 'Academic Year',
                    'old' => $currentAcademicYearRange,
                    'new' => $newAcademicYearRange,
                ];
            }
        }

        $courseStartOld = $this->formatDateValue($schoolData->course_start_date ?? null);
        $courseStartNew = $this->formatDateValue($req->course_start_date ?? null);
        if ($courseStartOld !== $courseStartNew) {
            $changes[] = [
                'label' => 'Course Start Date',
                'old' => $courseStartOld,
                'new' => $courseStartNew,
            ];
        }

        $courseEndOld = $this->formatDateValue($schoolData->course_end_date ?? null);
        $courseEndNew = $this->formatDateValue($req->course_end_date ?? null);
        if ($courseEndOld !== $courseEndNew) {
            $changes[] = [
                'label' => 'Course Expiration Date',
                'old' => $courseEndOld,
                'new' => $courseEndNew,
            ];
        }

        return $changes;
    }

    private function notifySuperAdminOfPartnerSchoolChanges($schoolData, array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        safeMailAction('partner school edited mail', [
            'school_id' => $schoolData->id ?? null,
            'recipient' => env('MAIL_ADMIN'),
        ], function () use ($schoolData, $changes) {
            Mail::to(env('MAIL_ADMIN'))->send(new PartnerSchoolEditedMail(
                $schoolData->school_name ?? 'School',
                auth()->user()->name ?? 'Partner',
                auth()->user()->email ?? '',
                $changes
            ));
        });
    }

    private function formatDateValue($value): string
    {
        if (empty($value)) {
            return 'Not set';
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function formatYesNoValue($value): string
    {
        return $value ? 'Yes' : 'No';
    }

    private function formatLookupValue($value, string $modelClass): string
    {
        if (empty($value)) {
            return 'Not set';
        }

        try {
            $model = $modelClass::find($value);
            if (!empty($model->name)) {
                return $model->name;
            }

            return (string) $value;
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function formatAcademicYearRange($academicYear): string
    {
        if (empty($academicYear)) {
            return 'Not set';
        }

        return $this->formatDateValue($academicYear->start_date ?? null) . ' to ' . $this->formatDateValue($academicYear->end_date ?? null);
    }

    private function formatAcademicYearRangeFromRequest($startDate, $endDate): string
    {
        if (empty($startDate) && empty($endDate)) {
            return 'Not set';
        }

        return $this->formatDateValue($startDate) . ' to ' . $this->formatDateValue($endDate);
    }

    private function ensurePartnerSchoolAccess($schoolId): void
    {
        if (!isPartnerUser() || empty($schoolId)) {
            return;
        }

        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            abort(403, 'Access denied.');
        }
    }

    private function partnerCurrencyType(): ?string
    {
        if (!isPartnerUser()) {
            return null;
        }

        $currencyCode = DB::table('currencies')
            ->where('id', auth()->user()->currency_id)
            ->value('code');

        return $currencyCode ? strtolower($currencyCode) : null;
    }

    private function schoolDomainExists(?string $domain, ?string $exceptTenantId = null): bool
    {
        if (empty($domain)) {
            return false;
        }

        $fullDomain = trim($domain) . config('tenancy.sub_domain');
        return Domain::where('domain', $fullDomain)
            ->when($exceptTenantId, function ($query) use ($exceptTenantId) {
                $query->where('tenant_id', '!=', $exceptTenantId);
            })
            ->exists();
    }

    private function studentCapacityState(School $school): array
    {
        return partnerSchoolStudentCapacityState($school);
    }

    public function studentImport(School $school)
    {
        $this->ensurePartnerSchoolAccess($school->id);

        $school_id = $school->id;
        $school = School::select('id', 'number_of_student', 'created_type', 'created_by', 'school_name')
            ->with(['students' => function($query) {
                $query->select(['school_id']);
            }])
            ->find($school_id);

        $isPartnerSchool = partnerSchoolIsPartnerRecord($school);
        $capacityState = $this->studentCapacityState($school);
        $number_of_students_allowed = (int) $school->number_of_student;
        $total_students = $capacityState['school_used'];

        if ($capacityState['school_at_capacity'] || $capacityState['partner_at_capacity']) {
            return redirect()->route('backend.studentList.studentList', $school_id)->with('capacity_failed', 'Import exceeds allowed student/license limit');
        }
        $school_batch_list = SchoolBatch::where('school_id', $school_id)->get();
        $student_grade_list = StudentGrade::all()->pluck('name')->toArray();
        return view('backend.school.student.student_import', compact('school', 'number_of_students_allowed', 'total_students', 'school_batch_list', 'student_grade_list', 'isPartnerSchool'));
    }

    public function studentImportStore(Request $req)
    {
        $school_id = $req->school_id;
        $school_data = School::select(
            'id',
            'number_of_student',
            'country_id',
            'tenant_id',
            'school_name',
            'official_email_id',
            'default_grade',
            'created_type',
            'created_by'
        )->with(['students' => function($query) {
            $query->select(['school_id']);
        }, 'domains' => function($query) {
            $query->select(['id','domain','tenant_id']);
        }])->findOrFail($school_id);

        $isPartnerSchool = partnerSchoolIsPartnerRecord($school_data);
        $partnerLicenseLimit = isPartnerUser() ? (int) (auth()->user()->no_of_license_purchased ?? 0) : 0;
        $currentSchoolLicenseCount = $isPartnerSchool
            ? partnerSchoolStudentLicenseCount($school_id)
            : $school_data->students->count();
        $currentPartnerLicenseCount = $isPartnerSchool
            ? partnerSchoolStudentLicenseCountForPartner((int) ($school_data->created_by ?? 0))
            : 0;

        $studentGrade = StudentGrade::all()->pluck('name', 'id')->toArray();
        $school_batch = SchoolBatch::where('school_id', $school_id)->pluck('batch_name','id')->toArray();
        $school_batch_list = implode(",", $school_batch);
        $student_grade_list = implode(",", StudentGrade::all()->pluck('name')->toArray());

        $studentLevelRules = [
            $isPartnerSchool ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($isPartnerSchool) {
                $codes = partnerSchoolParseStudentLevelCodes((string) $value);
                if ($isPartnerSchool && empty($codes)) {
                    $fail('Student Level is required for partner schools.');
                    return;
                }

                if (!empty($codes) && !partnerSchoolValidateStudentLevelCodes($codes)) {
                    $fail('Student Level contains invalid values.');
                }
            }
        ];

        $validatedData = $req->validate([
            'upload_csv'   => ['required', 'mimes:csv,txt,xls,xlsx', new CsvValidator([
                'Student First Name' => 'required',
                'Student Last Name' => 'nullable',
                'User Name' => ['nullable', function ($attribute, $value, $fail) {
                    $val = trim((string) $value);
                    if ($val === '') {
                        return;
                    }
                    if (!preg_match('/^[A-Za-z0-9._]+$/', $val)) {
                        $fail('The '.$attribute.' format is invalid.');
                        return;
                    }
                    if (User::where('username', $val)->exists()) {
                        $fail('The '.$attribute.' has already been taken.');
                    }
                }],
                'Student Email' => ['nullable', function ($attribute, $value, $fail) {
                    $val = trim((string) $value);
                    if ($val === '') {
                        return;
                    }
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $fail('The '.$attribute.' must be a valid email.');
                        return;
                    }
                    if (User::where('email', $val)->exists()) {
                        $fail('The '.$attribute.' has already been taken.');
                    }
                }],
                'Password' => 'nullable|min:6|max:10',
                'Date of Birth' => ['nullable', function ($attribute, $value, $fail) {
                    $val = trim((string) $value);
                    if ($val === '') {
                        return;
                    }
                    if ($this->normalizeCsvDate($value) === null) {
                        $fail('The '.$attribute.' is not a valid date.');
                    }
                }],
                'Gender' => ['required', function ($attribute, $value, $fail) {
                    $val = $this->normalizeCsvGender($value);
                    if ($val === null || !in_array($val, ['Male', 'Female', 'Other'], true)) {
                        $fail('The '.$attribute.' must be Male, Female, or Other.');
                    }
                }],
                'Grade' => ['required', 'in:'.$student_grade_list],
                'Batch' => ['required', 'in:'.$school_batch_list],
                'Student Level' => $studentLevelRules,
                'Parent Full Name' => 'string',
                'Parent Email' => ['nullable', function ($attribute, $value, $fail) {
                    $val = trim((string) $value);
                    if ($val === '') {
                        return;
                    }
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $fail('The '.$attribute.' must be a valid email.');
                    }
                }],
                ])],
            ]);

        if ($req->hasFile('upload_csv')) {
            $csvUpload = $req->file('upload_csv');

            $csvData = $this->getCsvAsArray($csvUpload);
            $csvData = array_map([$this, 'normalizeCsvRow'], $csvData);
            
            if (!count($csvData)) {
                return back()->withErrors(["upload_csv" => "CSV file must have at least one record."]);
            }

            $requiredLicenseUnits = 0;
            foreach ($csvData as $csvd) {
                $levelCodes = partnerSchoolParseStudentLevelCodes($csvd['Student Level'] ?? null);
                if ($isPartnerSchool) {
                    if (empty($levelCodes)) {
                        return back()->with('capacity_failed', 'Import exceeds allowed student/license limit');
                    }
                    $requiredLicenseUnits += count($levelCodes);
                }
            }

            $number_of_students_allowed = (int) $school_data->number_of_student;
            $currentConsumedUnits = $isPartnerSchool ? $currentSchoolLicenseCount : $school_data->students->count();
            $partnerLimitExceeded = $isPartnerSchool
                && (($currentPartnerLicenseCount + $requiredLicenseUnits) > $partnerLicenseLimit);
            $schoolLimitExceeded = $isPartnerSchool
                ? (($currentConsumedUnits + $requiredLicenseUnits) > $number_of_students_allowed)
                : ((count($csvData) + $currentConsumedUnits) > $number_of_students_allowed);

            if ($schoolLimitExceeded || $partnerLimitExceeded) {
                return back()->with('capacity_failed', 'Import exceeds allowed student/license limit');
            }

            if(!$isPartnerSchool && (($currentConsumedUnits + count($csvData)) > $number_of_students_allowed)) {
                return back()->with('capacity_failed', 'You are allowed to add total '.$number_of_students_allowed.' students. You can add '.($number_of_students_allowed - $currentConsumedUnits).' students and you are trying to add '.count($csvData).' students.');
            }

            if (!empty($school_data->tenant_id)) {
                $ext = strtolower($csvUpload->getClientOriginalExtension());
                $fileName = 'student_' . Str::random(10) . '.' . $ext;
                $path = $csvUpload->storeAs($school_data->tenant_id.'/school/studentimport/', $fileName, 'tenant_uploads');
            } else {
                $fileType = $csvUpload->getClientOriginalExtension();
                $fileName = 'student_' . Str::random(10) . '.' . $fileType;
                $csvUpload->move('csv/upload/school/'.$school_id.'/', $fileName);
            }

            if(!empty($school_data->default_grade)) {
                $default_level = Grade::select('id')->whereIn('id', explode(",",  $school_data->default_grade))->get();
            } else {
                $default_level = Grade::select('id')->where('grade', 'THINKpreneur')->get();
            }
            $default_level = $default_level->implode("id", ',');
            
            $importedStudentData = collect();
            $usedUsernames = [];

            foreach ($csvData as $csvd) {
                $password = $csvd['Password'];
                if(empty($password)) {
                    $password = Str::random(10);
                }
                $firstName = $csvd['Student First Name'];
                $lastName = $csvd['Student Last Name'] ?? '';
                $fullName = trim($firstName.' '.($lastName ?? ''));
                $dob = array_key_exists('Date of Birth', $csvd) ? $this->normalizeCsvDate($csvd['Date of Birth']) : null;
                $gender = $this->normalizeCsvGender($csvd['Gender']);

                $userName = preg_replace('/[^A-Za-z0-9._]/', '', $csvd['User Name']);
                if(empty($userName)) {
                    $userName = $firstName;
                    if (!empty($lastName)) {
                        $userName .= $lastName;
                    }
                    $userName = preg_replace('/[^A-Za-z0-9._]/', '', $userName);
                    $userName = $school_data->domains->domain.'.'.$userName.($dob ? date('dm', strtotime($dob)) : '');
                    $userName = $this->makeUniqueUsername($userName, $usedUsernames);
                }
                $user = User::create([
                    'name'              => $fullName,
                    'username'          => $userName,
                    'email'             => array_key_exists('Student Email', $csvd) ? $csvd['Student Email'] : null,
                    'password'          => Hash::make($password),
                    'gender'            => $gender,
                    'country_id'        => $school_data->country_id,
                    'date_of_birth'     => $dob,
                    'group'             => 4,
                ]);

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
                safeEventAction('backend student created', [
                    'user_id' => $studentModel->id ?? null,
                    'email' => $studentModel->email ?? null,
                ], function () use ($studentModel) {
                    event(new UserCreated($studentModel));
                });

                $student_grade_id = '';
                if(in_array($csvd['Grade'], $studentGrade)) {
                    $student_grade_id = array_search($csvd['Grade'], $studentGrade, true);
                }

                $school_batch_id = '';
                if(in_array($csvd['Batch'], $school_batch)) {
                    $school_batch_id = array_search($csvd['Batch'], $school_batch, true);
                }

                $students = new Students();
                $students->user_id = $user->id;
                $students->school_id = $school_id;
                $students->country_id = $school_data->country_id;
                $students->student_grade_id = $student_grade_id;
                $students->school_batch_id = $school_batch_id;
                $students->name = $fullName;
                $students->parent_name = array_key_exists('Parent Full Name', $csvd) ? $csvd['Parent Full Name'] : null;
                $students->parent_email = array_key_exists('Parent Email', $csvd) ? $csvd['Parent Email'] : null;
                $gradeIdList = $default_level;
                $levelCodesRaw = array_key_exists('Student Level', $csvd) ? $csvd['Student Level'] : null;
                $levelIds = [];
                $levelCodes = partnerSchoolParseStudentLevelCodes($levelCodesRaw);
                if (!empty($levelCodes)) {
                    $levelIds = Grade::whereIn('unique_code', $levelCodes)->pluck('id')->toArray();
                    if (!empty($levelIds)) {
                        $gradeIdList = implode(',', $levelIds);
                    }
                }
                $students->grade_id = $gradeIdList;
                $students->optional_details_1 = $csvd['Optional Details 1'] ?? null;
                $students->optional_details_2 = $csvd['Optional Details 2'] ?? null;
                $students->optional_details_3 = $csvd['Optional Details 3'] ?? null;
                $students->save();

                if ($isPartnerSchool) {
                    partnerSchoolRecordStudentLicense($students, $levelIds, auth()->id());
                }

                clear_cache_manually();

                // START - SEND AN EMAIL TO STUDENT
                if (!empty($csvd['Student Email']) || !empty($csvd['Parent Email'])) {
                    $toEmail = CommonHelper::getRecipientEmailByUserId($user->id, false);
                    safeDispatchAction('backend import student create', [
                        'school_id' => $school_id,
                        'recipient' => $toEmail,
                        'student_email' => $csvd['Student Email'] ?? null,
                    ], function () use ($toEmail, $password, $fullName, $school_id, $userName, $csvd) {
                        dispatch(new StudentCreate([
                            'email' => $toEmail,
                            'password' => $password,
                            'name' => $fullName,
                            'school_id' => $school_id,
                            'user_name' => $userName,
                            'student_email' => $csvd['Student Email'] ?? '',
                        ]));
                    });
                }
                // END - SEND AN EMAIL TO STUDENT

                $importedStudentData->push([
                    'student_grade' => $csvd['Grade'],
                    'student_batch' => $csvd['Batch'],
                    'student_first_name' => $csvd['Student First Name'],
                    'student_last_name' => $csvd['Student Last Name'] ?? '',
                    'student_user_name' => $userName,
                    'student_password' => $password,
                ]);

                $student_email = new EmailInfo;
                $student_email->name = $fullName;
                $student_email->mail_address = $csvd['Student Email'] ?? null;
                $student_email->mail_description = '';
                $student_email->group = 4;
                $student_email->save();
            }

            if ($importedStudentData->isNotEmpty()) {
                $schoolData = [
                    'school_name' => $school_data->school_name ?? 'N/A',
                    'school_email'=> $school_data->official_email_id ?? 'N/A',
                ];
                
                $mailRecipients = array_filter(array_unique([
                    $school_data->official_email_id,
                    env('MAIL_ADMIN'),
                ]));

                foreach ($mailRecipients as $recipient) {
                    $recipientName = $recipient === $school_data->official_email_id ? 'School Admin' : 'Admin';
                    safeMailAction('backend imported students summary mail', [
                        'recipient' => $recipient,
                        'school_id' => $school_id,
                    ], function () use ($recipient, $importedStudentData, $schoolData, $recipientName) {
                        Mail::to($recipient)->bcc(env('MAIL_BCC'))->queue(new ImportedStudentsMail($importedStudentData, $schoolData, $recipientName));
                    });
                }
            }
        }

        return redirect()->route('backend.studentList.studentList', $school_id)->with('message', 'Students Imported Successfully!');
    }

    public function schoolDelete($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Partners are not allowed to delete schools.');
        }

        $data = School::find($id);

        if ($data->school_logo != '') {
            $destinationPath = \Storage::disk('tenant_uploads')->path($data->school_logo);
            if ($data->tenant_id && File::exists($destinationPath)) {
                File::delete($destinationPath);
            } else {
                if (File::exists($data->school_logo)) {
                    unlink($data->school_logo);
                }
            }
        }
        if ($data->upload_excel != '') {
            if (File::exists($data->upload_excel)) {
                unlink($data->upload_excel);
            }
        }
        //echo $data->user_id;die();
        //$user = User::where('id', $data->user_id)->first();
        DB::table('users')->where('id', $data->user_id)->delete();

        $success = School::where('id', $id)->delete();

        if ($success) {
            $notification = [
                'message' => 'School Successfully deleted!',
            ];

            return redirect()->back()->with($notification);
        }
    }

    public function viewschool(School $school)
    {
        $school->load(['user', 'students']);

        return view('backend.school.viewschool', compact('school'));
    }

    //Send Notification Start

    public function schoolNotificationBox()
    {
        // echo 11; die();

        return view('backend.school.notification.notification_box');
    }

    public function schoolCompose()
    {
        $schools = School::get();
        $grades = grade::get();

        return view('backend.school.notification.compose', [
            'schools' => $schools,
            'grades' => $grades,
        ]);
    }

    public function schoolNotificationSend(Request $request)
    {
        $request->validate([
            'school_id' => 'required',
            'grade_id' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);

        // ================================================= SELECT SCHOOL ===================================================
            $allschool = School::select('id');
            if($request->school_id != 'all'){
                $allschool = $allschool->where('id',$request->school_id);
            }
            $allschool = $allschool->get();
        // ===================================================================================================================
        // ================================================= SELECT GRADE ====================================================
            $allgrade = Grade::select('id');
            if($request->grade_id != 'all'){
                $allgrade = $allgrade->where('id',$request->grade_id);
            }
            $allgrade = $allgrade->get();
        // ===================================================================================================================
        // ================================================= SELECT STUDENTS =================================================
            $getAllStudents = new Students();
            if($request->school_id == 'all'){
                $getAllStudents = $getAllStudents->whereIn('school_id', $allschool->pluck('id'));
            }else{
                $getAllStudents = $getAllStudents->where('school_id',$request->school_id);
            }
            if($request->grade_id == 'all'){
                $getAllStudents = $getAllStudents->whereIn('grade_id', $allgrade->pluck('id'));
            }else{
                $getAllStudents = $getAllStudents->whereRaw('FIND_IN_SET('.$request->grade_id.',grade_id)');
            }
            $getAllStudents = $getAllStudents->with('user')->get();
            $admin_ntf = [
                'title' => $request->title,
                'description' => $request->description,
            ];
            // 'grade_id' => $grade->id
            if($request->grade_id == 'all'){
                $admin_ntf['grade_id'] = 'All';
            }else{
                $admin_ntf['grade_id'] = $request->grade_id;
            }

            if($request->school_id == 'all'){
                $admin_ntf['school_id'] = 'All';
            }else{
                $admin_ntf['school_id'] = $request->school_id;
            }
            $an = AdminNotification::create($admin_ntf);
        // ===================================================================================================================
        foreach($allschool as $school){
            foreach($allgrade as $grade){

                $sn = SchoolNotification::create([
                    'school_id' => $school->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'admin' => $an->id
                ]);
            }
        }
        foreach ($getAllStudents as $student) {
            $student->notifications()->create(['title' => $request->title, 'description' => $request->description, 'admin' => $an->id]);
        }

        return redirect('admin/school/notificationbox')->with('success', 'Notification Send Successfully.');

        // echo "<pre>"; print_r($smsinfo); die();
    }

    //Send Notification End

    //School Suspend-------------------------------------

    public function schoolSuspend($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Partners are not allowed to suspend schools.');
        }

        $user = User::find($id);
        $user->suspend = 1;
        $user->save();

        $email = $user->email;

        $emailSub = 'Your School Account Suspended!! <br>';
        $emailBody = 'Please contact VentureKids administrator <br>';
        $emailBody .= 'Thanks <br> VentureKids';

        file_put_contents('../resources/views/mail.blade.php', $emailBody);
        $data = ['email'=> $email, 'subject'=> $emailSub];

        safeMailAction('backend school suspend mail', [
            'school_user_id' => $user->id ?? null,
            'recipient' => $email,
        ], function () use ($data) {
            Mail::send('mail', $data, function ($message) use ($data) {
                $message->to($data['email'], 'venturekids')->subject($data['subject']);
            });
        });

        $suspend_email = new EmailInfo;
        $suspend_email->name = $user->name;
        $suspend_email->mail_address = $email;
        $suspend_email->mail_description = $emailBody;
        $suspend_email->group = 2;
        $suspend_email->save();

        return redirect('admin/schoollist')->with('suspend_success', 'School Account Successfully Suspended!');
    }

    //School UnSuspend-------------------------------------
    public function schoolUnsuspend($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Partners are not allowed to unsuspend schools.');
        }

        $user = User::find($id);
        $user->suspend = 2;
        $user->save();

        $email = $user->email;
        $emailSub = 'Your School Account Succussfully Unsuspended!!';
        $emailBody = 'Welcome To VentureKids <br>';
        $emailBody .= 'Thanks <br> VentureKids';
        // die();

        // echo "<pre>"; print_r($trainer); die();

        file_put_contents('../resources/views/mail.blade.php', $emailBody);
        $data = ['email'=> $email, 'subject'=> $emailSub];

        safeMailAction('backend school unsuspend mail', [
            'school_user_id' => $user->id ?? null,
            'recipient' => $email,
        ], function () use ($data) {
            Mail::send('mail', $data, function ($message) use ($data) {
                $message->to($data['email'], 'venturekids')->subject($data['subject']);
            });
        });

        $unsuspend_email = new EmailInfo;
        $unsuspend_email->name = $user->name;
        $unsuspend_email->mail_address = $email;
        $unsuspend_email->mail_description = $emailBody;
        $unsuspend_email->group = 2;
        $unsuspend_email->save();
        // echo '<pre>'; print_r($user); die();

        return redirect('admin/schoollist')->with('suspend_success', 'School Account Unsuspended!');
    }

    public function studentList($id)
    {
        $this->ensurePartnerSchoolAccess($id);

        $school = School::select('id','school_name', 'number_of_student', 'created_type', 'created_by')->with(['students' => function($query) {
            $query->select(['school_id']);
        }])->find($id);

        $grades = Grade::get()->toArray();
        $capacityState = $this->studentCapacityState($school);

        $data['id'] = $id;

        return view('backend.school.student.student_list', compact('grades', 'data', 'school', 'capacityState'));
    }
    public function studentDeleteRequest($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $schools = School::where('id', $id)->get()->toArray();
        //echo '<pre>'; print_r($schools); die();
        $data['id'] = $id;
        return view('backend.school.student.studentdelete_list', compact('data', 'schools'));
    }

    public function student_list_datatable(Request $request)
    {
        $school_id = $request->school_id;
        $this->ensurePartnerSchoolAccess($school_id);

        if ($request->grade_id != '') {
            $data = Students::with(['stdUser'])
            ->where('school_id', $school_id)
            ->whereRaw('FIND_IN_SET('.$request->grade_id.',grade_id)')
            // ->where('grade_id', $request->grade_id)
            ->with(['level'])
            ->get()
            ->toArray();
        } else {
            $data = Students::with(['stdUser'])
             ->where('school_id', $school_id)
             ->with(['level'])
             ->get()
             ->toArray();
        }
        if (request()->ajax()) {
            return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('level.grade',function($row){
                $lavel = Grade::whereIn('id',explode(',',$row['grade_id']))->get();
                $lavelname = [];
                foreach($lavel as $r){
                    $lavelname[] = $r->grade;
                }
                return implode(',',$lavelname);
            })
            ->addColumn('email', function($row){
            return $row['std_user']['email'] ?? '-';
            })
            ->addColumn('action', function ($row) {
                $actionbtn = '<a href="' . route('backend.student-edit', ['school'=>$row['school_id'], 'student' => $row['id']]) . '"class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i></a>';
                if (!isPartnerUser()) {
                    $actionbtn .= '<a href="' . route('backend.student-delete', $row['id']) . '"class="btn btn-block btn-danger btn-sm sdsdsd" id="deleteStudentFromAdmin"><i class="fas fa-trash"></i></a>';
                }

                return $actionbtn;
            })

            ->rawColumns(['action'])
            ->make(true);
        }
    }
    public function student_delete_list_datatable(Request $request)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $school_id = $request->school_id;
        $data = Students::with(['stdUser'])
             ->where('school_id', $school_id)
             ->where('status','1')
             ->with(['level'])
             ->get()
             ->toArray();
        if (request()->ajax()) {
            return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('level.grade',function($row){
                $lavel = Grade::whereIn('id',explode(',',$row['grade_id']))->get();
                $lavelname = [];
                foreach($lavel as $r){
                    $lavelname[] = $r->grade;
                }
                return implode(',',$lavelname);
            })
            ->addColumn('action', function ($row) {
                $actionbtn = '<a href="' . route('backend.student-delete', $row['id']) . '"class="btn btn-block btn-danger btn-sm asasas" id="deleteStudent"><i class="fas fa-trash"></i></a>';
                return $actionbtn;
            })

            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function delete_existing_student(Request $req)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $schhol_id = $req->school_id;
        $all_student = Students::where('school_id', $schhol_id)->get()->toArray();

        if ($all_student) {
            foreach ($all_student as $all_students) {
                $user_id[] = $all_students['user_id'];
            }
            // StudentLicense::where('school_id', $schhol_id)->delete();
            Students::where('school_id', $schhol_id)->delete();
            DB::table('users')->whereIn('id', $user_id)->delete();
            echo 1;
        } else {
            echo 2;
        }
    }

    public function student_delete($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $data = Students::find($id);

        if ($data->image != '') {
            if (File::exists($data->image)) {
                unlink($data->image);
            }
        }
        //echo $data->user_id;die();
        //$user = User::where('id', $data->user_id)->first();
        DB::table('users')->where('id', $data->user_id)->delete();

        // StudentLicense::where('student_id', $id)->delete();

        $success = Students::where('id', $id)->delete();

        if ($success) {
            $notification = [
                'message3' => 'Student Successfully deleted!',
            ];

            return redirect()->back()->with($notification);
        }
    }

    public function studentEdit(School $school, Students $student)
    {
        $this->ensurePartnerSchoolAccess($school->id);

        $student->load([
            'school',
            'stdUser',
        ]);
        $grades = Grade::all();
        $assignment = StudentCommunications::get();
        $country = Country::get()->sortBy('name');
        $student_grade = StudentGrade::all();
        $school_batch_list = SchoolBatch::where('school_id', $school->id)->get();
        return view('backend.school.student.student_edit', [
            'student' => $student,
            'assignment' => $assignment,
            'grades' => $grades,
            'country' => $country,
            'student_grade' => $student_grade,
            'school_batch_list' => $school_batch_list,
            'isPartnerSchool' => partnerSchoolIsPartnerRecord($school),
        ]);
    }

    public function studentUpdate(School $school, Students $student, StudentRequest $request)
    {
        $this->ensurePartnerSchoolAccess($school->id);

        $host = $request->getHost();
        $user = User::find($student->user_id);
        $isPartnerSchool = partnerSchoolIsPartnerRecord($school);
        $existingGradeIds = partnerSchoolStudentLevelIdsForStudent($school->id, $student->id);
        $requestedGradeIds = partnerSchoolNormalizeStudentGradeIds($request->grade_id ?? []);
        $addedGradeIds = [];
        $removedGradeIds = [];

        $p_flag = 0;
        if (isset($request->confirm_password) && isset($request->new_password)) {
            if ($request->confirm_password == $request->new_password) {
                $password = $request->new_password;
                $p_flag = 1;
            } else {
                return redirect()->back()->with('confirm_password_faild', "Your new password and confirm password didn't match");
            }
        }

        $resend_mail = 0;
        if($user->email != $request->email) {
            $resend_mail = 1;
            if(!$p_flag) {
                $password = Str::random(10);
            }
        }

        if($p_flag || $resend_mail) {
            $user->password = Hash::make($password);
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->mobile = $request->parent_mobile;
        $user->gender = $request->gender;
        $user->country_id = $request->country;
        $user->date_of_birth = $request->date_of_birth;

        $user->save();

        $request_image = $request->file('profile_image');

        if (!empty($request_image) && $student->school->tenant_id) {

            if($student->image) {
                $studentProfileImagePath = \Storage::disk('tenant_uploads')->path($student->image);
                if (File::exists($studentProfileImagePath)) {
                    File::delete($studentProfileImagePath);
                }
            }

            $file = $request_image->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            $profileImgName = $filename. '_' . time() .".". $extension;
            $path = $request_image->storeAs($student->school->tenant_id.'/student', $profileImgName, 'tenant_uploads');

            // $image_name = $directory . 'thumbnail/' . $img_name;
            // $image->resize(null, 200, function ($constraint) {
            //     $constraint->aspectRatio();
            // });

            $student->image = $student->school->tenant_id.'/student/' . $profileImgName;
        }

        $student->country_id = $isPartnerSchool && isPartnerUser()
            ? (partnerCountryId() ?? $request->country)
            : $request->country;
        if ($isPartnerSchool) {
            if (isPartnerUser()) {
                $requestedGradeIds = $existingGradeIds;
            } else {
                $addedGradeIds = array_values(array_diff($requestedGradeIds, $existingGradeIds));
                $removedGradeIds = array_values(array_diff($existingGradeIds, $requestedGradeIds));

                if (!empty($addedGradeIds)) {
                    $schoolLimit = (int) ($school->number_of_student ?? 0);
                    $partnerLimit = (int) (User::whereKey($school->created_by)->value('no_of_license_purchased') ?? 0);
                    $currentSchoolLicenseCount = partnerSchoolStudentLicenseCount($school->id);
                    $currentPartnerLicenseCount = partnerSchoolStudentLicenseCountForPartner((int) ($school->created_by ?? 0));

                    if (($currentSchoolLicenseCount + count($addedGradeIds)) > $schoolLimit || ($currentPartnerLicenseCount + count($addedGradeIds)) > $partnerLimit) {
                        return back()->with('capacity_failed', 'Import exceeds allowed student/license limit')->withInput();
                    }
                }
            }
        } else {
            $student->grade_id = ($request->grade_id) ? implode(',', $request->grade_id) : NULL;
        }
        $student->student_grade_id = $request->student_grade_id;
        $student->school_batch_id = $request->school_batch;
        $student->name = $request->name;
        $student->parent_name = $request->parent_name;
        $student->parent_email = $request->parent_email;
        $student->address = $request->address;
        if ($isPartnerSchool) {
            $student->grade_id = !empty($requestedGradeIds) ? implode(',', $requestedGradeIds) : null;
        }

        $student->save();

        if ($isPartnerSchool && !isPartnerUser()) {
            if (!empty($addedGradeIds)) {
                partnerSchoolRecordStudentLicense($student, $addedGradeIds, auth()->id());
            }

            if (!empty($removedGradeIds)) {
                partnerSchoolMarkRemovedStudentLicensesInactive($student, $removedGradeIds);
            }
        }
        
        $toEmail = CommonHelper::getRecipientEmailByUserId($user->id);

        if($resend_mail) {
             // START - RESEND AN EMAIL TO STUDENT
             /*
             dispatch(new StudentCreate([
                'email' => $toEmail,
                'password' => $password,
                'name' => $request->name,
                'school_id' => $school->id,
                'user_name' => $request->username,
            ]));
            */
            // END - RESEND AN EMAIL TO STUDENT
        } else if($p_flag) {
            $domain = "";
            if (isset($school->domains->domain) && !empty($school->domains->domain)) {
                $domain = $school->domains->domain.config("tenancy.sub_domain");
            }

            safeMailAction('backend student change password mail', [
                'recipient' => $toEmail,
                'student_username' => $request->username,
                'school_id' => $school->id,
            ], function () use ($toEmail, $request, $domain) {
                Mail::to($toEmail)->send(new StudentChangePasswordMail($request->username, $request->new_password, $request->name, $domain));
            });
        }

        return redirect()->route('backend.studentList.studentList', $school->id)->with('message', 'Student updated successfully!');
    }

    public function studentAdd(School $school)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $capacityState = $this->studentCapacityState($school);
        if ($capacityState['school_at_capacity'] || $capacityState['partner_at_capacity']) {
            return redirect()->route('backend.studentList.studentList', $school->id)->with('capacity_failed', 'You are not allowed to add more students for this school.');
        }

        $school_id = $school->id;

        $school = School::select('id','number_of_student')->with(['students' => function($query) {
            $query->select(['school_id']);
        }])->find($school_id);

        $number_of_students_allowed = $school->number_of_student;
        $total_students = $school->students->count();

        if($total_students >= $number_of_students_allowed) {
            return redirect()->route('backend.studentList.studentList', $school_id)->with('capacity_failed', 'You are allowed to add total '.$number_of_students_allowed.' students and you already added '.$total_students.' students.');
        }

        $grades = Grade::all();
        $country = Country::get()->sortBy('name');
        $student_grade = StudentGrade::all();
        $school_batch_list = SchoolBatch::where('school_id', $school_id)->get();

        return view('backend.school.student.student_add', compact('school', 'grades','country', 'student_grade', 'school_batch_list'));
    }
    public function studentAddDirect()
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $grades = Grade::all();
        $school = School::where('status',1)->get();
        $country = Country::get()->sortBy('name');
        return view('backend.school.student.new_student', compact('school', 'grades','country'));
    }

    public function studentStore(School $school, StudentRequest $request)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $capacityState = $this->studentCapacityState($school);
        if ($capacityState['school_at_capacity'] || $capacityState['partner_at_capacity']) {
            return redirect()->route('backend.studentList.studentList', $school->id)->with('capacity_failed', 'You are not allowed to add more students for this school.');
        }

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->mobile = $request->parent_mobile;
        $user->gender = $request->gender;
        $user->country_id = $request->country;
        $user->date_of_birth = $request->date_of_birth;

        $password = Str::random(10);
        if (isset($request->password)) {
            if ($request->password_confirmation == $request->password) {
                $password = $request->password;
            } else {
                return redirect()->back()->with('confirm_password_faild', "Your new password and confirm password didn't match");
            }
        }
        $user->password = Hash::make($password);

        $user->group = 4;

        $user->save();

        $request_image = $request->file('profile_image');

        $image_name = $image_path = '';
        if (!empty($request_image)) {
            $image = Image::make($request_image);
            $image_path = 'image/student/';
            $image_name = time() . '.' . $request_image->getClientOriginalExtension();
            $imageUrl = $image_path . $image_name;

            if ($school && $school->tenant_id) {
                $request_image->storeAs($school->tenant_id.'/student', $image_name, 'tenant_uploads');
                $image_path = $school->tenant_id.'/student/';
            } else {
                $image->save($imageUrl);
            }

            // $image_name = $directory . 'thumbnail/' . $img_name;
            // $image->resize(null, 200, function ($constraint) {
            //     $constraint->aspectRatio();
            // });

            // $image->save($image_name);
        }

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

        $studentModel = $$module_name_singular;
        safeEventAction('backend student created', [
            'user_id' => $studentModel->id ?? null,
            'email' => $studentModel->email ?? null,
        ], function () use ($studentModel) {
            event(new UserCreated($studentModel));
        });

        $default_level = Grade::where('grade', 'THINKpreneur')->get();
        
        $student = new Students();
        $student->user_id = $user->id;
        $student->school_id = $school->id;
        $student->country_id = $request->country;
        $student->grade_id = ($request->grade_id) ? implode(',',$request->grade_id) : $default_level[0]->id;
        $student->student_grade_id = $request->student_grade_id;
        $student->school_batch_id = $request->school_batch;
        $student->name = $request->name;
        $student->parent_name = $request->parent_name;
        $student->parent_email = $request->parent_email;
        $student->address = $request->address;
        $student->image = $image_path . $image_name;
        $student->save();

        clear_cache_manually();

        $domain = '';
        if (isset($school->domains->domain) && !empty($school->domains->domain)) {
            $domain = $school->domains->domain.config('tenancy.sub_domain');
        }

        $toEmail = CommonHelper::getRecipientEmailByUserId($user->id);
        safeMailAction('backend created student mail', [
            'recipient' => $toEmail,
            'student_email' => $request->email ?? null,
            'username' => $request->username,
        ], function () use ($toEmail, $request, $password, $domain) {
            Mail::to($toEmail)->bcc(env('MAIL_BCC'))->send(new CreatedStudentMail($request->email ?? '', $password, $request->name, 0, $domain, $request->username));
        });

        $student_email = new EmailInfo;
        $student_email->name = $request->name;
        $student_email->mail_address = $request->email;
        $student_email->mail_description = '';
        $student_email->group = 4;
        $student_email->save();

        return redirect()->route('backend.studentList.studentList', $school->id)->with('message', 'Student created successfully!');
    }

    public function studentNew(StudentRequest $request)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->country_id = $request->country;
        $password = Str::random(10);
        if (isset($request->password)) {
            if ($request->password_confirmation == $request->password) {
                $password = $request->password;
            } else {
                return redirect()->back()->with('confirm_password_faild', "Your new password and confirm password didn't match");
            }
        }
        $user->password = Hash::make($password);
        $user->group = 4;
        $user->save();

        $school = $domain = null;
        if ($request->school_id) {
            $school = School::with('domains')->select('tenant_id')->find($request->school_id);
            if (isset($school->domains->domain) && !empty($school->domains->domain)) {
                $domain = $school->domains->domain.config('tenancy.sub_domain');
            }
        }

        $request_image = $request->file('profile_image');

        $image_name = $directory = '';
        if (!empty($request_image)) {
            $image = Image::make($request_image);
            $directory = 'image/student/';
            $image_name = time() . '.' . $request_image->getClientOriginalExtension();
            $imageUrl = $directory . $image_name;

            if ($school && $school->tenant_id) {
                $request_image->storeAs($school->tenant_id.'/student', $image_name, 'tenant_uploads');
                $directory = $school->tenant_id.'/student/'. $image_name;
            } else {
                $image->save($imageUrl);
            }

            // $image_name = $directory . 'thumbnail/' . $img_name;
            // $image->resize(null, 200, function ($constraint) {
            //     $constraint->aspectRatio();
            // });

            // $image->save($image_name);
        } else {
            $image_name = $request->pre_image;
        }

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

        $studentModel = $$module_name_singular;
        safeEventAction('backend student created', [
            'user_id' => $studentModel->id ?? null,
            'email' => $studentModel->email ?? null,
        ], function () use ($studentModel) {
            event(new UserCreated($studentModel));
        });

        $student = new Students();
        $student->user_id = $user->id;
        $student->school_id = $request->school_id;
        $student->country_id = $request->country;
        $student->name = $request->name;
        $student->parent_name = $request->parent_name;
        $student->parent_email = $request->parent_email;
        $student->address = $request->address;
        $student->image = $directory . $image_name;
        $student->grade_id = implode(',',$request->grade_id);
        $student->save();

        $toEmail = CommonHelper::getRecipientEmailByUserId($user->id);
        safeMailAction('backend created student mail', [
            'recipient' => $toEmail,
            'student_email' => $request->email ?? null,
            'username' => $request->username,
        ], function () use ($toEmail, $request, $password, $domain) {
            Mail::to($toEmail)->bcc(env('MAIL_BCC'))->send(new CreatedStudentMail($request->email ?? '', $password, $request->name, 0, $domain, $request->username));
        });
        
        $student_email = new EmailInfo;
        $student_email->name = $request->name;
        $student_email->mail_address = $request->email;
        $student_email->mail_description = '';
        $student_email->group = 4;
        $student_email->save();

        return redirect()->route('backend.studentList.studentList', $request->school_id)->with('message', 'Student successfully created!');
    }

    public function deleteStudentProfileAvatar(Request $request) {
        $studentId = $request->studentId;
        if(!empty($studentId)) {
            $stud = Students::with('school')->find($studentId);
            if($stud->count()) {
                if ($stud->school->tenant_id) {
                    $destinationPath = \Storage::disk('tenant_uploads')->path($stud->image);
                    if (File::exists($destinationPath)) {
                        File::delete($destinationPath);
                    }
                } else {
                    FacadesFile::delete($stud->image);
                }
                Students::where('id', $studentId)->update(['image' => null]);
                return true;
            }
        }
        return false;
    }

    public function deleteSchoolLogo(Request $request) {
        $schoolId = $request->schoolId;
        $school_data = School::find($schoolId);
        if ($school_data && !empty($school_data->school_logo)) {
            if ($school_data->tenant_id) {
                $destinationPath = \Storage::disk('tenant_uploads')->path($school_data->school_logo);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
            } else {
                $destinationPath = public_path('/image/school/');
                if (File::exists($destinationPath . $school_data->school_logo)) {
                    File::delete($destinationPath . $school_data->school_logo);
                }
            }
            $school_data->school_logo = null;
            $school_data->save();
        }
        return true;
    }

    public function deleteSchoolCoverLogo(Request $request) {
        $schoolId = $request->schoolId;
        $school_data = School::find($schoolId);
        if ($school_data && !empty($school_data->school_cover_image)) {
            if ($school_data->tenant_id) {
                $destinationPath = \Storage::disk('tenant_uploads')->path($school_data->school_cover_image);
                if (File::exists($destinationPath)) {
                    File::delete($destinationPath);
                }
            } else {
                $destinationPath = public_path('/image/school/cover_image/');
                if (File::exists($destinationPath . $school_data->school_cover_image)) {
                    File::delete($destinationPath . $school_data->school_cover_image);
                }
            }
            $school_data->school_cover_image = null;
            $school_data->save();
        }
        return true;
    }

    public function deleteBatch(Request $request) {
        $batchId = $request->batchId;
        $batch = SchoolBatch::find($batchId);
        if($batch) {
            Students::where('school_batch_id',$batchId)->update(['school_batch_id' => null]);
            TrainerAllocationNew::where('school_batch_id',$batchId)->where('school_id', $batch->school_id)->delete();
            $batch->delete();
        }
        return true;
    }

    public function deleteSchoolCss(Request $request) {
        $schoolId = $request->schoolId;
        $school_data = School::find($schoolId);
        if ($school_data && !empty($school_data->tenant_id)) {
            $destinationPath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/css/');
            if (File::exists($destinationPath . 'custom.css')) {
                File::delete($destinationPath . 'custom.css');
            }
        }
        return true;
    }

    public function deleteSchoolLoader(Request $request) {
        $schoolId = $request->schoolId;
        $school_data = School::find($schoolId);
        if ($school_data && !empty($school_data->tenant_id)) {
            $destinationPath = \Storage::disk('tenant_uploads')->path($school_data->tenant_id.'/school/loader/');
            if (File::exists($destinationPath . 'school-loader.png')) {
                File::delete($destinationPath . 'school-loader.png');
            }
        }
        return true;
    }

    protected function getAcademicYearOptionsForSchool($schoolId)
    {
        $academicYears = SchoolAcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get();

        $today = Carbon::today();
        $selectedAcademicYear = 'past';
        $currentAcademicYear = $academicYears->first(function ($academicYear) use ($today) {
            // return Carbon::parse($academicYear->start_date)->lte($today)
            //     && Carbon::parse($academicYear->end_date)->gte($today);
            return Carbon::parse($academicYear->start_date)
                && Carbon::parse($academicYear->end_date);
        });

        if (!empty($currentAcademicYear)) {
            $selectedAcademicYear = (string) $currentAcademicYear->id;
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

        return [$academicYearOptions, $selectedAcademicYear];
    }

    public function viewProgress($schoolId) {
        $levels = Grade::all();   
        $filtered_levels = [];
        $filtered_collection = $levels->filter(function ($item) use (&$filtered_levels) {
            if($item->is_primary == 1) {
                $filtered_levels['primary'][$item->id] = $item->toArray();        
            } else {
                $filtered_levels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        
        $school_data = School::select('id', 'school_name', 'country_id', 'standard_assessment_assigned')->find($schoolId);
        [$academicYearOptions, $selectedAcademicYear] = $this->getAcademicYearOptionsForSchool($schoolId);

        $student_list = Students::select('id')->where('school_id', $schoolId)->get();
        
        $school_data->total_students = $student_list->count();

        $assignmentIdList = StudentCommunications::select('id')->where('school_id', $schoolId)->orWhere('school_id', '=', 0)->get();
        
        $school_data->assignments_uploaded = Submission::whereIn('student_id', $student_list)->whereIn('assignment_id', $assignmentIdList)->count();
        
        $school_data->projects_uploaded = Project::whereIn('student_id', $student_list)->where('is_publish',1)->count();

        $school_data->industry_challenges_responded = 0;
        $challengeList = Event::select('id')->where('is_publish', 1)->where(function ($query) use ($school_data) {
        $query->where('visibility_type', 1) // Global visibility
                ->orWhere(function ($q) use ($school_data) {
                    $q->where('visibility_type', 2) // Country-specific
                        ->where('country_id', $school_data->country_id);
                    });
                })->get();
        if($challengeList->count()) {
            $challengeIdList = $challengeList->pluck('id')->toArray();
            $school_data->industry_challenges_responded = EventChallenge::whereIn('student_id', $student_list)->whereIn('event_id', $challengeIdList)->count();
        }
        
        $uc_thinkpreneur = config('global.level_unique_code.thinkpreneur');
        $gradeData = Grade::select('id')->where('unique_code', $uc_thinkpreneur)->first();
        $currentGradeId = $gradeData->id;
        
        $school_data->session_completed = 0;
        $getScromList = Stream::select('id')->where('agegroup_id', $currentGradeId)->whereNotNull('scormFile')->get();
        if($getScromList->count() && $student_list->count()) {
            $moduleCount = $getScromList->count() * $student_list->count();
            $scormIdList = $getScromList->pluck('id')->toArray();
            $completedModuleCount = StudentRewardPoints::whereIn('student_id', $student_list)->where('reward_type', 'video_learning_point')->whereIn('item_id', $scormIdList)->count();
            $school_data->session_completed = round(($completedModuleCount / $moduleCount) * 100, 2);
        }

        $school_data->weekly_challenges_submitted = StudentRewardPointsHelper::getRewardCountByType($student_list, ['weekly_challenge','daily_challenge']);

        $school_data->baseline_assessment_submitted = 0;
        $school_data->post_assessment_submitted = 0;
        $school_data->student_reflections_submitted = 0;
        if($school_data->standard_assessment_assigned) {
            $studentIds = $student_list->pluck('id')->toArray();
            if (!empty($studentIds)) {
            $school_data->baseline_assessment_submitted = AssessmentStudentReport::whereIn('student_id', $studentIds)
                ->where('assessment_type', 'standard')
                ->count();
            }
        }
        

        return view('backend.progres_report.list_progress', [
            'grades' => $filtered_levels,
            'school_data' => $school_data,
            'currentGradeId' => $currentGradeId,
            'academicYears' => $academicYearOptions,
            'selectedAcademicYear' => $selectedAcademicYear,
        ]);
    }

    public function getProgressByGrade(Request $request)
    {
        $grade_id = $request->grade_id;
        $school_id = $request->school_id;
        $data['students'] = [];
        $students = Students::select('id','user_id', 'image')->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->where('school_id', $school_id)->whereRaw("find_in_set($grade_id,grade_id)")->get();
        if($students->count()) {
            $data['students'] = $students->toArray();
        }
        $data['grade_name'] = Grade::select('grade')->find($grade_id);
        $data['session_completed'] = 0;
        $getScromList = Stream::select('id')->where('agegroup_id', $grade_id)->whereNotNull('scormFile')->get();
        $student_list = Students::select('id')->where('school_id', $school_id)->get();
        if($getScromList->count() && $student_list->count()) {
            $moduleCount = $getScromList->count() * $student_list->count();
            $scormIdList = $getScromList->pluck('id')->toArray();
            $completedModuleCount = StudentRewardPoints::whereIn('student_id', $student_list)->where('reward_type', 'video_learning_point')->whereIn('item_id', $scormIdList)->count();
            $data['session_completed'] = round(($completedModuleCount / $moduleCount) * 100, 2);
        }
        echo json_encode($data);
    }

    public function generateLeaderBoard(Request $request) {
        $school_id = $request->school_id;
        $academicYearFilter = $request->get('academic_year');
        $gradeId = (int) $request->get('grade_id');
        $month = $request->get('month');

        $query = Students::select('id', 'user_id', 'image')->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            },
        ])->where('school_id', $school_id);

        if (!empty($gradeId)) {
            $query->whereRaw('find_in_set(?, grade_id)', [$gradeId]);
        }

        $students = $query->get();

        if ($students->count()) {
            foreach ($students as $student) {
                $reward_points = StudentRewardPointsHelper::getRewardPoints($student->id, $academicYearFilter, $month);
                $student->tot_reward_points = array_sum($reward_points);
            }
            $students = $students->sortByDesc('tot_reward_points');
        }

        echo json_encode(array_values($students->toArray()));
    }

    public function studentInfo(Request $request)
    {
        $data = StudentProgressService::getData(
            (int) $request->studentId,
            (int) $request->gradeId,
            $request->get('academic_year')
        );

        echo json_encode($data);
    }

    public function getRewardPointDetails(Request $request) {
        $stud_id  = (int) $request->stud_id;
        $academicYearFilter = $request->get('academic_year');
        $month = $request->get('month');
        if($stud_id) {
            return json_encode(StudentRewardPointsHelper::getRewardPoints($stud_id, $academicYearFilter, $month));
        }
        return false;
    }

    public function updateGrades($schoolId)
    {
        $createpreneurId = 4;
        $createpreneurPlusId = 23;

        $newGrades = [$createpreneurId, $createpreneurPlusId];

        $result = [];

        // Fetch all students for this school
        $students = Students::select('id', 'grade_id', 'name')
            ->where('school_id', $schoolId)
            ->get();
        
        foreach ($students as $student) {
            // Get existing grades
            $oldGrades = array_filter(explode(',', $student->grade_id));

            // Merge with new grades (and remove duplicates)
            $mergedGrades = array_unique(array_merge($oldGrades, $newGrades));

            // Update student record
            $student->grade_id = implode(',', $mergedGrades);
            $student->save();

            $result[] = [
                'student_name' => $student->name,
                'old_grades'   => $oldGrades,      // stays original
                'new_grades'   => $mergedGrades,   // updated
            ];
       
        }
        
        return view('backend.school.grade_update_summary', compact('result'));
    }

    public function updateUsernameScript($school_id)
    {
        $students = Students::select('id','user_id', 'school_id')->where('school_id', $school_id)->with(['stdUser'=>function($query) {
            $query->select('id', 'username', 'name', 'date_of_birth');
        }])->get();

        $school = School::select('id', 'school_name', 'tenant_id')->with(['domains' => function($query) {
                $query->select(['id','domain','tenant_id']);
            }])->where('id', $school_id)->first();
        
        foreach ($students as $student) {
            $user = User::find($student->user_id);
            $userName = preg_replace('/[^A-Za-z0-9._]/', '', $student->stdUser->name);
            $userName = $school->domains->domain.'.'.$userName.date('dm', strtotime($student->stdUser->date_of_birth));
            // check if exists for any other user 
            $exists = User::where('username', $userName) ->where('id', '!=', $student->user_id) ->exists(); 
            
            // if exists → append user_id 
            if ($exists) { 
                $userName = "{$userName}{$student->user_id}"; 
            }

            $user->username = $userName;
            $user->save();
        }

        echo "Username update script executed successfully.";
    }

    public function deleteOrphanUserScript($school_id, $action = 'view')
    {
       $school = School::select('id', 'user_id', 'school_name')->findOrFail($school_id);

        // Orphan student users: no students row + not the school admin user.
        $orphanUsers = DB::table('users')
            ->leftJoin('students', 'students.user_id', '=', 'users.id')
            ->whereNull('students.id')
            // ->whereNull('users.deleted_at')
            // ->where('users.id', '!=', $school->user_id)
            ->where('users.group', 4)
            ->select('users.id', 'users.name', 'users.username', 'users.email', 'users.group')
            ->orderBy('users.id')
            ->get();

        if ($action === 'delete') {
            $userIds = $orphanUsers->pluck('id')->all();
            if (!empty($userIds)) {
                User::whereIn('id', $userIds)->delete();
            }

            echo 'Deleted orphan users: ' . count($userIds);
            return;
        }

        // Default action: view
        echo '<table border="1" cellpadding="6" cellspacing="0">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Group</th></tr></thead><tbody>';
        foreach ($orphanUsers as $u) {
            echo '<tr>'
                . '<td>' . htmlspecialchars((string) $u->id, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) $u->name, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) $u->username, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) $u->email, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) $u->group, ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>';
        }
        echo '</tbody></table>';
    }

        public function exportStudents($schoolId)
    {
        $school = School::select('school_name')->find($schoolId);

        $schoolName = (string) Str::of($school?->school_name ?? ('school-' . $schoolId))
            ->trim()
            ->replaceMatches('/[^A-Za-z0-9]+/', '-')
            ->trim('-');

        $filename = $schoolName . '-student-data-' . now()->format('dmY') . '.xlsx';

        return Excel::download(new StudentsExport($schoolId), $filename);
    }

    public function getStudentObservations(Request $request)
    {
        return StudentObservationHelper::getByStudentAndGrade(
            (int) $request->studentId,
            (int) $request->gradeId
        );
    }
}

