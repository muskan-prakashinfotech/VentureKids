<?php

namespace App\Http\Controllers\School;

use App\Events\Backend\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\StudentRequest;
use App\Mail\CreatedStudentMail;
use App\Mail\StudentChangePasswordMail;
use App\Models\EmailInfo;
use App\Models\Grade;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentCommunications;
use App\Models\StudentLicense;
use App\Models\Students;
use App\Models\User;
use App\Models\StudentGrade;
use App\Models\SchoolBatch;
use App\Rules\CsvValidator;
use App\Traits\CsvFIleupload;
use App\Jobs\StudentCreate;
use App\Jobs\StudentDeleteRequest;
use DB;
use File;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Image;
use App\Traits\CsvImportNormalizer;
use App\Helpers\CommonHelper;
use App\Mail\ImportedStudentsMail;
class SchoolController extends Controller
{
    use CsvFIleupload, CsvImportNormalizer;

    public function __construct()
    {
        $this->middleware('permission:school_edit');
        $this->module_name = 'users';
    }

    private function schoolStudentCapacityState(School $school): array
    {
        return partnerSchoolStudentCapacityState($school);
    }

    public function studentList()
    {
        $school_id = Session::get('school_id');

        $school = School::select('id','number_of_student','created_type','created_by')->with(['students' => function($query) {
            $query->select(['school_id']);
        }])->find($school_id);

        $capacityState = $this->schoolStudentCapacityState($school);
        $number_of_students_allowed = $school->number_of_student;
        $total_students = $capacityState['school_used'];

        $grades = Grade::all();

        return view('school.student.student_list', compact('grades', 'number_of_students_allowed', 'total_students', 'school', 'capacityState'));
    }

    public function student_list_datatable(Request $request)
    {
        $userId = Session::get('user_id');
        // echo $userId;die();
        $school = School::where('user_id', $userId)->first();
        if($school) {
            if ($request->grade_id != '') {
                $data = Students::with([
                    'stdUser' => function ($query) {
                        $query->select('id', 'name');
                    },
                ])
                ->where('school_id', $school->id)
                ->whereRaw('FIND_IN_SET('.$request->grade_id.',grade_id)')
                ->get();
            } else {
                $data = Students::with([
                    'stdUser' => function ($query) {
                        $query->select('id', 'name');
                    },
                ])
                ->where('school_id', $school->id)
                ->get();
            }
            if($data->count()) {
                $data = $data->toArray();
            }
            if (request()->ajax()) {
                return datatables()->of($data)
                    ->addIndexColumn()
                    ->addColumn('grade_id',function($row){
                        $lavel = Grade::whereIn('id',explode(',',$row['grade_id']))->get();
                        $lavelname = [];
                        foreach($lavel as $r){
                            $lavelname[] = $r->grade;
                        }
                        return implode(',',$lavelname);
                    })
                    ->addColumn('action', function ($row) {
                        $actionbtn = '<div class="ActionBtns"><a href="' . route('school.student-edit', $row['id']) . '"class="btn btn-block btn-info btn-sm"><i class="fas fa-edit"></i></a>';
                        if($row['status'] == '0'){
                            $actionbtn .= '<a href="' . route('school.student-delete', $row['id']) . '" class="btn btn-block btn-danger btn-sm" id="deleteStudent"><i class="fas fa-trash"></i></a>';
                        }else{
                            $actionbtn .= '<span class="btn btn-block btn-danger btn-sm">Requested</span>';
                        }
                        $actionbtn .='</div>';
                        return $actionbtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }

    public function studentEdit($id)
    {
        $student = Students::with([
            'school',
            'stdUser',
        ])->find($id);

        $grades = Grade::all();
        $student_grade = StudentGrade::all();

        $school_batch_list = [];
        if (!empty($student)) {
            $student = (is_object($student)) ? $student->toArray() : $student;
            $school_batch_list = SchoolBatch::where('school_id', $student['school_id'])->get();

            $student['image_path'] = 'image/student/'. $student['image'];
            if ($student['school']['tenant_id']) {

                /* START - STUDENT PROFILE PATH */
                $logoPath = \Storage::disk('tenant_uploads')->path($student['image']);
                if (File::exists($logoPath)) {
                    $student['image_path'] = 'tenants/'.$student['image'];
                    $student['image'] = basename($student['image']);
                }
                /* END - STUDENT PROFILE PATH */
            }

        } else {
            abort(404);
        }

        return view('school.student.student_edit', [
            'student' => $student,
            'grades' => $grades,
            'student_grade' => $student_grade,
            'school_batch_list' => $school_batch_list
        ]);
    }

    public function studentDelete($id)
    {
        $studentData = Students::select(['id', 'user_id', 'school_id'])->with(['school' => function($query) {
            $query->select(['id', 'school_name']);
        }, 'stdUser' => function($query) {
            $query->select(['id', 'name', 'email']);
        }])->find($id)->toArray();

        if(!empty($studentData)) {
            $student = Students::find($id);
            $student->status = '1';
            $student->save();
            
            // START - SEND AN EMAIL TO ADMIN
            safeDispatchAction('school student delete request', [
              'student_id' => $student->id,
              'school_id' => $student->school_id,
            ], function () use ($studentData) {
                dispatch(new StudentDeleteRequest([
                  'school_name' =>  $studentData['school']['school_name'],
                  'student_name' =>  $studentData['std_user']['name'],
                  'student_email' =>  $studentData['std_user']['email'],
                ]));
            });
            // END - SEND AN EMAIL TO ADMIN
            
            $notification = [
                'message1' => 'Student delete request send successfully.',
            ];
        } else {
            $notification = [
                'message1' => 'Student not found!',
            ];
        }

        return redirect()->back()->with($notification);
    }

    public function studentUpdate(StudentRequest $request)
    {
        $user = User::find($request->user_id);

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
            $user->password = FacadesHash::make($password);
        }

        $school_id = Session::get('school_id');

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;

        $user->save();

        $request_image = $request->file('profile_image');

        $students = Students::with('school')->find($request->student_id);

        if (!empty($request_image) && $students->school->tenant_id) {

            if($students->image) {
                $studentProfileImagePath = \Storage::disk('tenant_uploads')->path($students->image);
                if (File::exists($studentProfileImagePath)) {
                    File::delete($studentProfileImagePath);
                }
            }

            $file = $request_image->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            $profileImgName = $filename. '_' . time() .".". $extension;
            $path = $request_image->storeAs($students->school->tenant_id.'/student', $profileImgName, 'tenant_uploads');

            // $image_name = $directory . 'thumbnail/' . $img_name;
            // $image->resize(null, 200, function ($constraint) {
            //     $constraint->aspectRatio();
            // });

            $students->image = $students->school->tenant_id.'/student/'. $profileImgName;
        }

        $students->name = $request->name;
        $students->student_grade_id = $request->student_grade_id;
        $students->school_batch_id = $request->school_batch;
        $students->address = $request->address;
        $students->parent_name = $request->parent_name;
        $students->parent_email = $request->parent_email;

        $students->save();

        $toEmail = CommonHelper::getRecipientEmailByUserId($user->id);

        if($resend_mail) {      // RESEND ON BOARDING EMAIL TO STUDENT
            /*
            dispatch(new StudentCreate([
               'email' => $toEmail,
               'password' => $password,
               'name' => $request->name,
               'school_id' => $school_id,
               'user_name' => $request->username,
           ]));
           */
        } else if($p_flag) {    // SEND CHANGE PASSWORD EMAIL TO STUDENT
            $host = $request->getHost();
            $domain = "";
            if (!empty($school_id)) {
                $school = \App\Models\School::with('domains')->find($school_id);
                if (isset($school->domains->domain) && !empty($school->domains->domain)) {
                    $domain = $school->domains->domain.config("tenancy.sub_domain");
                }
            }

            if (!empty($host) && $host != $domain) {
                $domain = $host;
            }

            safeMailAction('school student change password mail', [
                'recipient' => $toEmail,
                'student_username' => $request->username,
                'school_id' => $school_id,
            ], function () use ($toEmail, $request, $password, $domain) {
                Mail::to($toEmail)->send(new StudentChangePasswordMail($request->username, $password, $request->name, $domain));
            });
        }

        return redirect('/school/student/list')->with('message', 'Student updated successfully!');
    }

    public function studentAdd(School $school)
    {
        $school_id = Session::get('school_id');

        $school = School::select('id','number_of_student','created_type','created_by')->with(['students' => function($query) {
            $query->select(['school_id']);
        }])->find($school_id);

        if (partnerSchoolIsPartnerRecord($school)) {
            return redirect()->route('school.student-list')->with('email_faild', 'You are not allowed to add students for partner-created schools.');
        }

        $number_of_students_allowed = $school->number_of_student;
        $total_students = $school->students->count();

        if($total_students >= $number_of_students_allowed) {
            return redirect()->route('school.student-list')->with('capacity_failed', 'You are allowed to add total '.$number_of_students_allowed.' students and you already added '.$total_students.' students.');
        }

        $grades = Grade::all();
        $student_grade = StudentGrade::all();
        $school_batch_list = SchoolBatch::where('school_id', $school_id)->get();

        return view('school.student.student_add', compact('school', 'grades', 'student_grade', 'school_batch_list'));
    }

    public function studentStore(StudentRequest $request)
    {
        try {
            DB::beginTransaction();

            $host = $request->getHost();

            $school = School::where('user_id', Auth::id())->first();

            if (partnerSchoolIsPartnerRecord($school)) {
                return redirect()->route('school.student-list')->with('email_faild', 'You are not allowed to add students for partner-created schools.');
            }

            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->gender = $request->gender;
            $user->country_id = $school->country_id;
            $user->date_of_birth = $request->date_of_birth;

            $password = Str::random(10);
            $user->password = FacadesHash::make($password);

            $user->group = 4;

            $user->save();

            $request_image = $request->file('profile_image');
            $image_path = '';
            if (!empty($request_image) && tenant() && tenant()->tenant_id) {

                $file = $request_image->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                $image_name = $filename. '_' . time() .".". $extension;
                $path = $request_image->storeAs(tenant()->tenant_id.'/student', $image_name, 'tenant_uploads');
                $image_path = tenant()->tenant_id.'/student/'. $image_name;

            }

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
            safeEventAction('school student created', [
                'user_id' => $studentModel->id ?? null,
                'email' => $studentModel->email ?? null,
            ], function () use ($studentModel) {
                event(new UserCreated($studentModel));
            });

            $student = new Students();
            $student->user_id = $user->id;
            $student->school_id = $school->id;
            $student->country_id = $school->country_id;
            $student->student_grade_id = $request->student_grade_id;
            $student->school_batch_id = $request->school_batch;
            $student->name = $request->name;
            $student->parent_name = $request->parent_name;
            $student->parent_email = $request->parent_email;
            $student->address = $request->address;
            $student->image = $image_path;

            $default_level = Grade::where('grade', 'THINKpreneur')->get();
            $student->grade_id = $default_level[0]->id;

            $student->save();

            clear_cache_manually();

            $domain = null;
            if (isset($school->domains->domain) && !empty($school->domains->domain)) {
                $domain = $school->domains->domain.config('tenancy.sub_domain');
            }

            if (!empty($host) && $host != $domain) {
                $domain = $host;
            }

            $toEmail = CommonHelper::getRecipientEmailByUserId($user->id);
            safeMailAction('school created student mail', [
                'recipient' => $toEmail,
                'student_email' => $request->email ?? null,
                'username' => $request->username,
            ], function () use ($toEmail, $request, $password, $domain) {
                Mail::to($toEmail)->bcc(env('MAIL_BCC'))->send(new CreatedStudentMail($request->email ?? '', $password, $request->name, 0, $domain, $request->username));
            });

            $student_email = new EmailInfo();
            $student_email->name = $request->name;
            $student_email->mail_address = $request->email;
            $student_email->mail_description = '';
            $student_email->group = 4;
            $student_email->save();

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', 'Failed to create student.');
        }

        return redirect()->route('school.student-list')->with('message', 'Student created successfully!');
    }

    public function sudentImport(Request $request)
    {
        $school_id = Session::get('school_id');

        $school = School::select('id','number_of_student','created_type','created_by')->with(['students' => function($query) {
            $query->select(['school_id']);
        }])->find($school_id);

        $capacityState = $this->schoolStudentCapacityState($school);
        $number_of_students_allowed = $school->number_of_student;
        $total_students = $capacityState['school_used'];

        if($capacityState['school_at_capacity'] || $capacityState['partner_at_capacity']) {
            return redirect()->route('school.student-list')->with('capacity_failed', 'Import exceeds allowed student/license limit');
        }

        $school_batch_list = SchoolBatch::where('school_id', $school_id)->get();
        $student_grade_list = StudentGrade::all()->pluck('name')->toArray();
        return view('school.student.student_import', compact('number_of_students_allowed', 'total_students', 'school_batch_list', 'student_grade_list', 'school', 'capacityState'));
    }

    public function studentImportStore(Request $req)
    {
        $host = $req->getHost();
        $school_id = Session::get('school_id');
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
        $partnerLicenseLimit = $isPartnerSchool
            ? (int) (User::whereKey((int) ($school_data->created_by ?? 0))->value('no_of_license_purchased') ?? 0)
            : 0;
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
                safeEventAction('school imported student created', [
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
                    safeDispatchAction('school import student create', [
                        'school_id' => $school_id,
                        'recipient' => $toEmail,
                        'student_email' => $csvd['Student Email'] ?? null,
                    ], function () use ($toEmail, $password, $fullName, $school_id, $userName, $csvd, $host) {
                        dispatch(new StudentCreate([
                            'email' => $toEmail,
                            'password' => $password,
                            'name' => $fullName,
                            'school_id' => $school_id,
                            'user_name' => $userName,
                            'student_email' => $csvd['Student Email'] ?? '',
                        ], $host));
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
                $student_email->name = $csvd['Student First Name'].' '.($csvd['Student Last Name'] ?? '');
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
                
                safeMailAction('school imported students summary mail', [
                    'recipient' => $school_data->official_email_id,
                    'school_id' => $school_id,
                ], function () use ($school_data, $importedStudentData, $schoolData) {
                    Mail::to($school_data->official_email_id)->bcc(env('MAIL_BCC'))->queue(new ImportedStudentsMail($importedStudentData, $schoolData));
                });
            }
        }

        return redirect()->route('school.student-list')->with('message', 'Student imported successfully!');
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
                    $destinationPath = public_path('/image/school/');
                    if (File::exists($destinationPath . $school_data->school_logo)) {
                        File::delete($destinationPath . $school_data->school_logo);
                    }
                    FacadesFile::delete($stud->image);
                }

                Students::where('id', $studentId)->update(['image' => null]);
                return true;
            }
        }
        return false;
    }

    public function studentResetPassword(Request $request) {
        $host = $request->getHost();
        $studentId = (int)$request->studentId;
        $sent = false;
        if($studentId) {
            $student = Students::with('stdUser')->find($studentId);
            if($student) {
                $email = $student->stdUser->email;
                if($email) {
                    $password = Str::random(10);
                    User::find($student->user_id)->update(['password' => FacadesHash::make($password)]);
                    $domain = "";
                    if (!empty($student->school_id)) {
                        $school = \App\Models\School::with('domains')->find($student->school_id);
                        if (isset($school->domains->domain) && !empty($school->domains->domain)) {
                            $domain = $school->domains->domain.config("tenancy.sub_domain");
                        }
                    }

                    if (!empty($host) && $host != $domain) {
                        $domain = $host;
                    }

                    safeMailAction('school student change password mail', [
                        'recipient' => $email,
                        'student_id' => $student->id,
                    ], function () use ($email, $student, $password, $domain) {
                        Mail::to($email)->send(new StudentChangePasswordMail($student->stdUser->username, $password, $student->stdUser->name, $domain));
                    });
                    $sent = true;
                }
            }
        }
        return $sent;
    }
}
