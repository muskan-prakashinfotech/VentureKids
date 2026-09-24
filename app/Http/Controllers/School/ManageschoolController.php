<?php

namespace App\Http\Controllers\School;

use App\Events\Backend\UserCreated;
use App\Http\Controllers\Controller;
use App\Models\AdminOthers;
use App\Models\ClassSchedule;
use App\Models\EmailInfo;
use App\Models\Event;
use App\Models\Grade;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\Students;
use App\Models\User;
use App\Models\Userprofile;
use App\Rules\CsvValidator;
use App\Traits\CsvFIleupload;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Jobs\StudentCreate;
use App\Jobs\InformAdminToAllocateTrainerToSchool;
use App\Jobs\SchoolEdited;
use App\Models\Country;

class ManageschoolController extends Controller
{
    use CsvFIleupload;

    public function __construct()
    {
        $this->middleware('permission:school_edit');
        $this->module_name = 'users';
    }

    public function index()
    {
        $userId = Session::get('user_id');
        $school = School::with('students')
        ->where('user_id', $userId)
        ->get()
        ->first();

        return view('school.index', [
            'school' => $school,
        ]);
    }

    public function profileEdit()
    {
        $userId = Session::get('user_id');
        $schoolId = School::where('user_id', $userId)->first();
        $school = School::with('ClassSchedule')->find($schoolId['id']);
        $school = (is_object($school)) ? $school->toArray() : $school;

        $school['school_css'] = '';
        $school['school_logo_path'] = 'image/school/' . $school['school_logo'];
        $school['school_cover_image_path'] = '/image/school/cover_image/' . $school['school_cover_image'];
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

                /* START - SCHOOL CSS */
                $cssPath = \Storage::disk('tenant_uploads')->path($school['tenant_id'].'/school/css/custom.css');
                if (File::exists($cssPath)) {
                    $school['school_css_path'] = 'tenants/'.$school['tenant_id'].'/school/css/custom.css';
                    $school['school_css'] = 'custom.css';
                }
                /* END - SCHOOL CSS */
            }
        }

        $grade = Grade::all();
        //echo '<pre>'; print_r($school); die();
        return view('school.profile.edit_profile', [
            'school' => $school,
            'grade'=>$grade,
            'countries' => Country::get(['id', 'name'])->sortBy('name')
        ]);
    }

    public function updateSchool(Request $req)
    {
        $schhol_id = $req->school_id;

        $validatedData = $req->validate([
            'incharge_name' => 'required',
            'incharge_email' => 'required',
        ]);

        $school_data = School::select('school_name', 'incharge_name', 'incharge_email', 'venturekids_representative', 'school_logo', 'school_cover_image', 'tenant_id')->find($schhol_id);

        $data['incharge_name'] = $req->incharge_name;

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

        // $data['venturekids_representative'] = $req->partner_name;

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

        $data['status'] = 1;

        $success = School::where('id', $schhol_id)->update($data);

        if ($success) {
            if ($school_data) {
                $updatedData = [];
                if($school_data->incharge_name != $req->incharge_name) {
                    $updatedData[] = 'Activity In-charge First Name';
                }
                if($school_data->incharge_email != $req->incharge_email) {
                    $updatedData[] = 'Activity In-charge Email ID';
                }
                // if($school_data->venturekids_representative != $req->partner_name) {
                //     $updatedData[] = 'venderkids Representative Full Name';
                // }
                if($req->school_logo) {
                    $updatedData[] = 'School Logo';
                }
                if($req->school_cover_image) {
                    $updatedData[] = 'School Cover Picture';
                }

                if(!empty($updatedData)){
                    // START - SEND AN EMAIL TO ADMIN REGARDING SCHOOL UPDATION
                    safeDispatchAction('school profile edited notification', [
                        'school_id' => $school_data->id ?? null,
                        'updated_fields' => $updatedData,
                    ], function () use ($school_data, $updatedData) {
                        dispatch(new SchoolEdited($school_data->school_name, $updatedData));
                    });
                    // END - SEND AN EMAIL TO SCHOOL REGARDING SCHOOL UPDATION
                }
            }
            return redirect('/school/profile/edit')->with('message', 'School updated successfully!');
        } else {
            return redirect('/school/profile/edit')->with('message', 'Error occurred. Please try again!');
        }
    }

    public function eventList()
    {
        return view('school.event.event_list', [
            'event' => Event::where('country_id', Session::get('country_id'))->get(),
        ]);
    }

    public function eventView($id)
    {
        $event = Event::find($id);

        return view('school.event.view_event', [
            'event' => $event,
        ]);
    }

    public function privacyPolice()
    {
        $user_id = Session::get('user_id');
        $user = User::where('id', $user_id)->first()->toArray();

        $terms = AdminOthers::where('setting_name', 'school')->first()->toArray();

        // dd($user);
        // dd($terms);

        return view('school.terms.privacy_police', [
            'terms' => $terms,
            'user' => $user,
        ]);
    }

    public function savePrivacyPolice(Request $request)
    {
        // echo 11; die();
        $validated = $request->validate([
            'termandcondition' => 'required',
        ]);

        $user_id = Session::get('user_id');
        $termsandcondition = $request->termandcondition;

        $user = User::where('id', $user_id)->first();
        $user->termandcondition = $termsandcondition;
        $user->save();

        return redirect('school/privacy/police/')->with('success', 'Terms and condition accepted.');
    }

    public function studentDelete($id){
        // dd($id);
        // $id = Auth::id();
        if(isset($id)){
            $student = Students::where('school_id',$id)->get();
            foreach($student as $row){
                $row->status = '1';
                $row->save();
            }
            $notification = [
                'message1' => 'Student delete request send successfully.',
            ];
            return redirect()->back()->with($notification);
        }else{
            $notification = [
                'message1' => 'Please Login first.',
            ];
            return redirect()->back()->with($notification);
        }
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
}
