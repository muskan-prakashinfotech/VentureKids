<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Events\Backend\UserCreated;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Trainer;
use App\Models\Trainerlavel;
use App\Models\Country;
use App\Mail\CreatedTrainerMail;
use Illuminate\Support\Facades\Log;

class TrainerController extends Controller
{
    use ApiResponse;
    protected $module_name;

    public function __construct()
    {
        $this->module_name = 'users';
    }
    

    public function addTrainer(Request $request)
    {
        $rules = [
            'trainer_name' => 'required|string|max:255',
            'trainer_email' => 'required|email|unique:users,email|max:255',
            'currency' => 'required|in:inr,dollar,sgd',
            'trainer_fee' => 'required|numeric|min:0',
            'contact_no' => 'required|numeric|digits_between:10,15',
            'city' => 'required|string|max:255',
            'country' => 'required|exists:countrys,name',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendResponse(
                implode(',', $validator->messages()->all()),
                401
            );
        }

        $assign_grade = [];

        $level = $request->level;
        if(isset($request->level) && !empty($level)) {
            $level = str_ireplace (' ', '', $level);
            $uniqueCodeList = explode(",", $level);
            $uniqueCodeExist = Trainerlavel::select('id')->whereIn('unique_code', $uniqueCodeList)->get();
            if($uniqueCodeExist->count()) {
                foreach($uniqueCodeExist as $uniqueCode) {
                    $assign_grade[] = $uniqueCode->id;
                }
            }
        } 

        $assign_grade_id_list = '';
        if(count($assign_grade)) {
            $assign_grade_id_list = implode(",", $assign_grade);
        }

        // Get country ID from country name
        $country = Country::where('name', $request->country)->first();
        $country_id = $country->id;

        /* START - CREATE USER FIRST */
        $user = new User();
        $user->name = $request->trainer_name;
        $user->email = $request->trainer_email;
        $user->country_id = $country_id;
        if (isset($request->password) && $request->password != null && $request->password != '') {
            $userPassword = $request->password;
        } else {
            $userPassword = Str::random(10);
        }
        $user->password = Hash::make($userPassword);
        $user->group = 3;
        $user->source = config('app.request_source');
        $user->save();
        /* END - CREATE USER FIRST */

        $module_name = $this->module_name;
        $module_name_singular = Str::singular($module_name);

        $$module_name_singular = $user;

        $roles = Role:: select('name')->where('id', 6)->get()->toArray();
        $permissions = Permission:: select('name')->whereIn('id', [1, 40])->get()->toArray();
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

        $trainerModel = $$module_name_singular;
        safeEventAction('api trainer created', [
            'user_id' => $trainerModel->id ?? null,
            'email' => $trainerModel->email ?? null,
        ], function () use ($trainerModel) {
            event(new UserCreated($trainerModel));
        });

        /* START - CREATE TRAINER */
        $trainer = new Trainer();
        $trainer->user_id = $user->id;
        $trainer->trainer_name = $request->trainer_name;
        $trainer->official_email_id = $request->trainer_email;
        if(!empty($assign_grade_id_list)) {
            $trainer->grade_id = $assign_grade_id_list;
        }
        $trainer->trainer_fee = $request->trainer_fee;
        $trainer->currency = $request->currency;
        $trainer->contact_no = $request->contact_no;
        $trainer->city = $request->city;
        $trainer->country_id = $country_id;
        $trainer->save();
        /* END - CREATE TRAINER */

        clear_cache_manually();

        safeMailAction('api trainer created mail', [
            'trainer_id' => $trainer->id,
            'recipient' => $trainer->official_email_id,
        ], function () use ($trainer, $request, $userPassword) {
            Mail::to($trainer->official_email_id)->bcc(env('MAIL_BCC'))->send(new CreatedTrainerMail($trainer->trainer_name, $request->trainer_email, $userPassword));
            Log::info('Trainer Added from WordPress. Email sent successfully to: ' . $trainer->official_email_id);
        });

        return $this->sendResponse('Trainer Added Successfully!', 200);
    }
}
