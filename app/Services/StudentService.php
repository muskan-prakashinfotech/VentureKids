<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\SchoolBatch;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\School;
use App\Models\User;
use App\Models\Role;
use App\Models\Students;
use App\Models\Permission;
use App\Events\Backend\UserCreated;
use App\Mail\CreatedStudentMail;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailInfo;
use DB;
use Image;

class StudentService
{
    public function __construct() {
        $this->module_name = 'users';
    }

    public function createStudent(School $school, array $data)
    {
        try {
            $studentEmail = isset($data['email']) ? $data['email'] : null;

            // Check max allowed student
            if(!$this->checkMaxStudentLimit($school)) {
                \Log::error('SSO Login error: School-'. $school->school_name.' Student: '.$studentEmail.'Max student limit exceed');
                return false;
            }

            DB::beginTransaction();

            $image_name = null;
            $image_path = 'image/student/';
            $permission = $role = [];


            /* START - CREATE USER FIRST */
            $user = new User();
            $user->name = isset($data['name']) ? $data['name'] : NULL;
            $user->email = $studentEmail;
            $user->country_id = $school->country_id;
            $user->mobile = isset($data['mobile']) ? $data['mobile'] : NULL;

            if (isset($data['parent_mobile'])) {
                $user->mobile = $data['parent_mobile'];
            }
            if (isset($data['gender'])) {
                $user->gender = $this->mapGender($data['gender']);
            }
            if (isset($data['date_of_birth'])) {
                $user->date_of_birth = $data['date_of_birth'];
            }
            $user->password = Hash::make(Str::random(10));
            $user->group = 4; // GROUP 4 -> STUDENT
            $user->save();
            /* END - CREATE USER FIRST */


            /* START -UPLOAD STUDENT PROFILE IMAGE */
            if (isset($data['profile_image']) && !empty($data['profile_image'])) {
                $request_image = $data['profile_image'];

                $image = Image::make($request_image);
                $image_name = time() . '.' . $request_image->getClientOriginalExtension();
                $imageUrl = $image_path . $image_name;

                if ($school && $school->tenant_id) { // WITH TENANT STUDENT IMAGE STORE TENANT WISE
                    $request_image->storeAs($school->tenant_id.'/student', $image_name, 'tenant_uploads');
                    $image_path = $school->tenant_id.'/student/';
                } else { // WITHOUT TENANT STUDENT IMAGE STORE
                    $image->save($imageUrl);
                }
            }
            /* END -UPLOAD STUDENT PROFILE IMAGE */

            $module_name = $this->module_name;
            $module_name_singular = Str::singular($module_name);
            $$module_name_singular = $user;
            $user_id = $user->id;

            /* START - USER (STUDENT) ROLES & PERMISSIONS */
            $roles = Role::select('name')->where('id', 8)->get()->toArray();
            foreach ($roles as $getrole) {
                $role[] = $getrole['name'];
            }

            $permissions = Permission::select('name')->whereIn('id', [1, 42])->get()->toArray();
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
            /* END - USER (STUDENT) ROLES & PERMISSIONS */


            /* START - SET USER (STUDENT) USERNAME */
            // Username
            $id = $$module_name_singular->id;
            $username = config('app.initial_username') + $id;
            $$module_name_singular->username = $username;
            $$module_name_singular->save();
            /* END - SET USER (STUDENT) USERNAME */


            $studentModel = $$module_name_singular;
            safeEventAction('student service user created', [
                'user_id' => $studentModel->id ?? null,
                'email' => $studentModel->email ?? null,
            ], function () use ($studentModel) {
                event(new UserCreated($studentModel));
            }); // USER CREATE MAIL SEND


            /* START - CREATE STUDENT */
            $student = new Students();
            $student->user_id = $user->id;
            $student->school_id = $school->id;
            $student->country_id = $school->country_id;
            $student->name = (isset($data['name'])) ? $data['name'] : NULL;
            $student->parent_name = (isset($data['parent_name'])) ? $data['parent_name'] : NULL;
            $student->parent_email = (isset($data['parent_email'])) ? $data['parent_email'] : NULL;
            $student->address = (isset($data['address'])) ? $data['address'] : NULL;
            if (isset($data['profile_image']) && !empty($data['profile_image'])) {
                $student->image = $image_path . $image_name;
            }

            // Assign student grade
            $studentGrades = StudentGrade::all()->pluck('name', 'id')->toArray();
            $student_grade_id = '';
            if(isset($data['student_grade']) && in_array($data['student_grade'], $studentGrades)) {
                $student_grade_id = array_search($data['student_grade'], $studentGrades, true);
            }

            // Assign Batch
            $schoolBatches = SchoolBatch::where('school_id', $school->id)->pluck('batch_name','id')->toArray();
            $school_batch_id = '';
            if(isset($data['school_batch']) && in_array($data['school_batch'], $schoolBatches)) {
                $school_batch_id = array_search($data['school_batch'], $schoolBatches, true);
            } else if($schoolBatches && count($schoolBatches) > 0) {
                $school_batch_id = \Arr::first(array_keys($schoolBatches));
            }

            // Assign student grade: the entry-level primary grade (lowest
            // display order), rather than a hardcoded grade name, so this
            // doesn't break whenever the `grades` catalog is reseeded/renamed.
            $default_level = Grade::where('is_primary', 1)->orderBy('display_order_id')->first()
                ?: Grade::orderBy('display_order_id')->first();
            $student->grade_id = $default_level->id ?? null;

            $student->student_grade_id = $student_grade_id ;
            $student->school_batch_id = $school_batch_id;

            $student->save();
            /* END - CREATE STUDENT */


            clear_cache_manually();

            // @todo send mail after discussion

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            \Log::error('SSO Login error: Student'. $studentEmail ." ". $e->getMessage());
            DB::rollBack();
            //throw $e;
            return null;
        }
    }

    public function checkMaxStudentLimit(School $school)
    {
        $number_of_students_allowed = $school->number_of_student;
        $total_students = $school->students->count();
        return empty($number_of_students_allowed) || ($total_students < $number_of_students_allowed);
    }

    public function mapGender($gender) {
        $genderMapping = [
            'm' => 'Male',
            'o' => 'Other',
            'f' => 'Female',
            'female' => 'Female',
            'male' => 'Male',
            'other' => 'Other'
        ];
        return $genderMapping[strtolower($gender)] ?? null;
    }

}
