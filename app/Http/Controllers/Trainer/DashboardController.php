<?php

namespace App\Http\Controllers\Trainer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\grade;
use App\Models\EmailNotification;
use App\Models\Trainer;
use App\Models\Userprofile;
use Illuminate\Support\Str;
use App\Events\Backend\UserCreated;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRoles;
use App\Models\TodoModel;
use App\Models\Content;
use App\Models\Students;
use App\Models\TrainerAllocation;
use App\Models\TrainerAllocationNew;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\EmailInfo;
use App\Models\TrainerEducationBackground;
use App\Models\TrainerPastAchievements;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;

use Illuminate\Support\Facades\Session;
use File;

class DashboardController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:trainer_edit');
        //$this->middleware('role:admin|writer')->only('testmiddleware');

        $this->module_name = 'users';
    }

    public function index(){
        
        // $myDate = date('Y-m-d');

        
        $trainer_id = Session::get('trainer_id');

        $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])->where('trainer_id', $trainer_id)->get();
        
        $batch_ids = $batch_list->pluck('school_batch_id');


        $data['all_todo']=TodoModel::where('trainer_id',Auth::id())->get();
        // $data['content']= Content::orderBy('id','desc')->take(5)->get()->toArray();
        $data['content'] = [];

        // $data['trainer_schedule']=TrainerAllocation::where('trainer_id',$trainer->id)->get()->toArray();
        $data['trainer_schedule'] = [];

        $data['students'] = Students::with(['school' => function($query) {
            $query->select(['id','tenant_id']);
        }])->select(['id','name', 'image','school_id'])->whereIn('school_batch_id', $batch_ids)->get()->toArray();
        
        /*
        $trainerAllocations = TrainerAllocation::where('trainer_id', Session::get('trainer_id'))->get()->toArray();

        $totalHour = 0;
        foreach($trainerAllocations as $allocation){

            $dateFrom = date_create($allocation['created_at']);
            $dateTo = date_create(date('Y-m-d'));

            $countDays = date_diff($dateFrom, $dateTo);
            $days = $countDays->days;

            $week = ceil($days / 7);

            $hour = $week * $allocation['class_duration'];
            $totalHour = $totalHour + $hour;
        }
        */

        return view('trainer.index', [
            'data' => $data,
            'totalHour' => 0    //  $totalHour
        ]);
    }


    public function profile()
    {
        $userId = Session::get('user_id');
        $trainer=Trainer::where('user_id', $userId)->first();

        $data['trainer']=$trainer;
        
        return view('trainer.profile.profile')->with(['datas' => $data, 'countries' => Country::get(['id', 'name'])->sortBy('name')]);
    }

    public function profile_update(Request $request)
    {
        $validated = $request->validate([
            'trainer_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'join_date' => 'required',
            'date_of_birth'=>'required',
            'official_email_id' => 'required',
            'contact_no' => 'required',
            'mode' => 'required',
            'type' => 'required',
            'no_of_hour_per_week' => 'required',
        ]);

        $user= User:: where('email',$request->official_email_id)->first();

        if(!empty($user)){

            if($user['email']==$request->official_email_id && $user['id']==$request->user_id){
                $user= User:: find($request->user_id);
                $user->name = $request->trainer_name;
                $user->email = $request->official_email_id;
                $user->save();
            }else{
                return redirect()->back()->with('email_faild', 'Sorry Email Already Exits.');
            }
        }else{

            $user= User:: find($request->user_id);
            $user->name = $request->trainer_name;
            $user->email = $request->official_email_id;

            $user->save();
        }

        $user_profile= Userprofile:: where('user_id',$request->user_id)->first();
        if(!empty($user_profile)) {
            $user_profile->email = $request->official_email_id;
            $user_profile->name = $request->trainer_name;
            $user_profile->save();
        }    
        //profile update start----------
        $trainer= trainer:: find($request->id);

        $request_image = $request->image;
        if(!empty($request_image)) {
            if ($trainer && !empty($trainer->image)) {
                $destinationPath = public_path('/image/trainer/');
                if (File::exists($destinationPath . $trainer->image)) {
                    File::delete($destinationPath . $trainer->image);
                }
            }
            $img_name = Str::random(10).'.'.$request_image->getClientOriginalExtension();
            $upload_path='image/trainer/';
            $request_image->move($upload_path,$img_name);
            $trainer->image=$img_name;
        }

        $attachment = $request->attachment;
        if(!empty($attachment)) {
            if ($trainer && !empty($trainer->attachment)) {
                $destinationPath = public_path('/image/trainer/attachment/');
                if (File::exists($destinationPath . $trainer->attachment)) {
                    File::delete($destinationPath . $trainer->attachment);
                }
            }
            $attachment_name = Str::random(10).'.'.$attachment->getClientOriginalExtension();
            $upload_path='image/trainer/attachment/';
            $attachment->move($upload_path,$attachment_name);
            $trainer->attachment=$attachment_name;
        }
        
        $cv = $request->cv;
        if(!empty($cv)) {
            if ($trainer && !empty($trainer->cv)) {
                $destinationPath = public_path('/image/trainer/cv/');
                if (File::exists($destinationPath . $trainer->cv)) {
                    File::delete($destinationPath . $trainer->cv);
                }
            }
            $cv_name = Str::random(10).'.'.$cv->getClientOriginalExtension();
            $upload_path='image/trainer/cv/';
            $cv->move($upload_path,$cv_name);
            $trainer->cv=$cv_name;
        }

        $trainer->trainer_name = $request->trainer_name;

        $trainer->address= $request->address;
        $trainer->city= ucfirst($request->city);
        $trainer->join_date = $request->join_date;
        $trainer->date_of_birth= $request->date_of_birth;
        $trainer->official_email_id= $request->official_email_id;
        $trainer->contact_no= $request->contact_no;
        $trainer->mode= $request->mode;
        $trainer->type = $request->type;
        $trainer->no_of_hour_per_week = $request->no_of_hour_per_week;
        $trainer->save();
        
        return redirect()->back()->with('update_success', 'Data Updated successfully.');
    }

    public function deleteTrainerProfileImage(Request $request) { 
        $trainerId = $request->trainerId;
        $trainer_data = Trainer::find($trainerId);
        if ($trainer_data && !empty($trainer_data->image)) {
            $destinationPath = public_path('/image/trainer/');
            if (File::exists($destinationPath . $trainer_data->image)) {
                File::delete($destinationPath . $trainer_data->image);
            }
            $trainer_data->image = null;
            $trainer_data->save();
        }
        return true;
    }

    public function deleteTrainerAttachment(Request $request) { 
        $trainerId = $request->trainerId;
        $trainer_data = Trainer::find($trainerId);
        if ($trainer_data && !empty($trainer_data->attachment)) {
            $destinationPath = public_path('/image/trainer/attachment/');
            if (File::exists($destinationPath . $trainer_data->attachment)) {
                File::delete($destinationPath . $trainer_data->attachment);
            }
            $trainer_data->attachment = null;
            $trainer_data->save();
        }
        return true;
    }

    public function deleteTrainerCV(Request $request) { 
        $trainerId = $request->trainerId;
        $trainer_data = Trainer::find($trainerId);
        if ($trainer_data && !empty($trainer_data->cv)) {
            $destinationPath = public_path('/image/trainer/cv/');
            if (File::exists($destinationPath . $trainer_data->cv)) {
                File::delete($destinationPath . $trainer_data->cv);
            }
            $trainer_data->cv = null;
            $trainer_data->save();
        }
        return true;
    }

}
