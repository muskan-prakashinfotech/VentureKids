<?php

namespace App\Http\Controllers\Backend;

use App\Events\Backend\UserCreated;
use App\Http\Controllers\Controller;
use App\Mail\CreatedTrainerMail;
use App\Mail\TrainerChangePasswordMail;
use App\Mail\TrainerLevelUpdatedMail;
use App\Models\EmailInfo;
use App\Models\grade;
use App\Models\NotificationInfo;
use App\Models\Permission;
use App\Models\RequestedCertificate;
use App\Models\Role;
use App\Models\School;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Userprofile;
use App\Models\Trainerlavel;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Image;
use App\Models\Country;
use App\Models\TrainerAllocationNew;
use File;
use Illuminate\Support\Facades\Log;

class TrainerController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:trainer_edit');
        //$this->middleware('role:admin|writer')->only('testmiddleware');

        $this->module_name = 'users';
    }

    private function partnerCanAddTrainer(): bool
    {
        return $this->partnerTrainerQuotaState()['can_add'];
    }

    private function partnerTrainerQuotaState(): array
    {
        if (!isPartnerUser()) {
            return [
                'limit' => null,
                'used' => null,
                'remaining' => null,
                'can_add' => true,
            ];
        }

        $limit = (int) (auth()->user()->allow_add_trainers ?? 0);
        $used = Trainer::where('created_by', auth()->id())->count();

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => max($limit - $used, 0),
            'can_add' => $used < $limit,
        ];
    }

    public function addTrainer()
    {
        $quotaState = $this->partnerTrainerQuotaState();
        if (isPartnerUser() && !$quotaState['can_add']) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'You are not allowed to add trainers.');
        }

        $allGrade = Trainerlavel::all();
        $selectedCountryId = isPartnerUser() ? partnerCountryId() : null;
        if (isPartnerUser() && empty($selectedCountryId)) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'No country has been assigned to your account.');
        }
        $countries = isPartnerUser()
            ? Country::whereKey($selectedCountryId)->get(['id', 'name'])->sortBy('name')
            : Country::get(['id', 'name'])->sortBy('name');
        $partnerCurrency = $this->partnerTrainerCurrency();
        if (isPartnerUser() && empty($partnerCurrency)) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'No currency has been assigned to your account.');
        }

        return view('backend.trainer.add_trainer')->with([
            'countries' => $countries,
            'trainerLevel' => $allGrade,
            'selectedCountryId' => $selectedCountryId,
            'partnerCurrency' => $partnerCurrency,
            'quotaState' => $quotaState,
        ]);
    }

    public function storeTrainer(Request $request)
    {
        $quotaState = $this->partnerTrainerQuotaState();
        if (isPartnerUser() && !$quotaState['can_add']) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'You are not allowed to add trainers.');
        }

        $selectedCountryId = isPartnerUser() ? partnerCountryId() : (int) $request->country;

        if (isPartnerUser() && empty($selectedCountryId)) {
            return redirect()->back()->with('email_faild', 'No country has been assigned to your account.')->withInput();
        }

        if (isPartnerUser()) {
            $partnerCurrency = $this->partnerTrainerCurrency();
            if (empty($partnerCurrency)) {
                return redirect()->back()->with('email_faild', 'No currency has been assigned to your account.')->withInput();
            }
            $request->merge([
                'country' => $selectedCountryId,
                'currency' => $partnerCurrency,
            ]);
        }

        $validated = $request->validate([
            'trainer_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'trainer_fee' => 'nullable|numeric',
            'currency' => 'required',
            'contact_no' => 'required',
            'city' => 'required',
            'country' => 'required',
            'grade_id' => 'required|array|min:1',
            'grade_id.*' => 'exists:trainerlavels,id',
            'join_date' => 'required|date',
        ]);

        $password = Str::random(10);
        
        $user = User:: where('email', $request->email)->first();
        // if (empty($user)) {
            $module_name = $this->module_name;
            $module_name_singular = Str::singular($module_name);

            $data_array = $request->except('_token', 'roles', 'permissions', 'password_confirmation');
            $data_array['name'] = $request->trainer_name;
            $data_array['password'] = Hash::make($password);
            $data_array['group'] = 3;
            $data_array['country_id'] = $selectedCountryId;

            if ($request->confirmed == 1) {
                $data_array = Arr::add($data_array, 'email_verified_at', Carbon::now());
            } else {
                $data_array = Arr::add($data_array, 'email_verified_at', null);
            }

            $$module_name_singular = User::create($data_array);
            $user_id = DB::getPdo()->lastInsertId();

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

            $trainerUser = $$module_name_singular;
            safeEventAction('backend trainer created', [
                'user_id' => $trainerUser->id ?? null,
                'email' => $trainerUser->email ?? null,
            ], function () use ($trainerUser) {
                event(new UserCreated($trainerUser));
            });
        // } else {
        //     return redirect()->back()->with('email_faild', 'Sorry Email Already Exits.');
        // }

        $trainer = new trainer;
        $trainer->user_id = $user_id;
        $trainer->trainer_name = $request->trainer_name;
        $trainer->official_email_id = $request->email;
        $trainer->grade_id = isset($request->grade_id) ? implode(',',$request->grade_id) : null;
        // The database column is non-nullable, so an omitted optional fee is stored as zero.
        $trainer->trainer_fee = $request->input('trainer_fee') ?? 0;
        $trainer->currency = $request->currency;
        $trainer->contact_no = $request->contact_no;
        $trainer->city = $request->city;
        $trainer->join_date = $request->join_date;
        $trainer->country_id = $selectedCountryId;
            $trainer->created_by = Auth::id();
            $trainer->save();

        clear_cache_manually();

        // $trainer = Trainer::with('user')->find($trainer->id)->toArray();
        // echo "<pre>"; print_r($trainer); die();

        // $user = $trainer['user'];
        // $email = $trainer['official_email_id'];
        // $emailSub = 'New trainer created!!';
        // $emailBody = 'Trainer Name: ' . $trainer['trainer_name'] . '<br>';
        // $emailBody .= 'Your Username: ' . $trainer['official_email_id'] . '<br>';
        // $emailBody .= 'Your Password: ' . 'trainer' . '<br>';
        // $emailBody .= "Please login your dashboard by clicking this link <a href='" . url('admin/dashboard') . "'>click here</a> <br>";
        // $emailBody .= 'Thanks <br> venderkids';
        // // die();

        // // die();
        // file_put_contents('../resources/views/mail.blade.php', $emailBody);
        // $data = ['email'=> $email, 'subject'=> $emailSub];

        // Mail::send('mail', $data, function ($message) use ($data) {
        //     $message->to($data['email'], 'venderkids')->subject($data['subject']);
        // });
        safeMailAction('backend trainer created mail', [
            'trainer_id' => $trainer->id,
            'recipient' => $trainer->official_email_id,
        ], function () use ($trainer, $request, $password) {
            Mail::to($trainer->official_email_id)->bcc(env('MAIL_BCC'))->send(new CreatedTrainerMail($trainer->trainer_name, $request->email, $password));
            Log::info('Email sent successfully to: ' . $trainer->official_email_id);
        });

        $newGradeIds = $this->normalizeGradeIds($request->grade_id);
        if (isPartnerUser() && !empty($newGradeIds)) {
            $this->notifySuperAdminTrainerLevelChange(
                $trainer,
                [],
                $newGradeIds,
                'created'
            );
        }

        // if ($trainer) {
        //     $trainer_email = new EmailInfo;
        //     $trainer_email->name = $trainer['trainer_name'];
        //     $trainer_email->mail_address = $email;
        //     $trainer_email->mail_description = $emailBody;
        //     $trainer_email->group = 3;
        //     $trainer_email->save();
        // }

        return redirect()->route('backend.trainerlist.trainerList')->with('success', 'Data Stored successfully.');
    }

    public function trainerList()
    {
        $trainersQuery = Trainer::with('user');

        if (isPartnerUser()) {
            applyCountryScope($trainersQuery, 'country_id');
        }

        $trainers = $trainersQuery->get()->toArray();
        $quotaState = $this->partnerTrainerQuotaState();
        // echo '<pre>'; print_r($trainers); die();

        return view('backend.trainer.trainer_list')->with([
            'trainers' => $trainers,
            'quotaState' => $quotaState,
        ]);
    }

    public function trainerEdit($id)
    {
        $trainerModel = Trainer::findOrFail($id);
        $this->ensurePartnerTrainerAccess($trainerModel);

        $trainer = Trainer::with(['user' => function($query) {
            $query->select('id', 'suspend');
        }])->find($id)->toArray();
        
        $allGrade = Trainerlavel::all();
        $selectedCountryId = isPartnerUser() ? partnerCountryId() : null;
        if (isPartnerUser() && empty($selectedCountryId)) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'No country has been assigned to your account.');
        }
        $countries = isPartnerUser()
            ? Country::whereKey($selectedCountryId)->get(['id', 'name'])->sortBy('name')
            : Country::get(['id', 'name'])->sortBy('name');
        $partnerCurrency = $this->partnerTrainerCurrency();
        if (isPartnerUser() && empty($partnerCurrency)) {
            return redirect()->route('backend.trainerlist.trainerList')->with('email_faild', 'No currency has been assigned to your account.');
        }

        return view('backend.trainer.edit_trainer')->with([
            'trainer'=> $trainer,
            'countries' => $countries,
            'trainerLevel' => $allGrade,
            'selectedCountryId' => $selectedCountryId,
            'partnerCurrency' => $partnerCurrency,
        ]);
    }

    public function updateTrainer(Request $request)
    {
        // dd($request->all());
        // echo "<pre>"; print_r($_POST); die();

        $trainer = Trainer::findOrFail($request->id);
        $this->ensurePartnerTrainerAccess($trainer);

        $selectedCountryId = isPartnerUser() ? partnerCountryId() : (int) $request->country;
        if (isPartnerUser() && empty($selectedCountryId)) {
            return redirect()->back()->with('email_faild', 'No country has been assigned to your account.')->withInput();
        }
        if (isPartnerUser()) {
            $partnerCurrency = $this->partnerTrainerCurrency();
            if (empty($partnerCurrency)) {
                return redirect()->back()->with('email_faild', 'No currency has been assigned to your account.')->withInput();
            }
            $request->merge([
                'country' => $selectedCountryId,
                'currency' => $partnerCurrency,
            ]);
        }

        $validated = $request->validate([
            'trainer_name' => 'required',
            'official_email_id' => 'required',
            'contact_no' => 'required',
            'address' => 'nullable',
            'city' => 'required',
            'country' => 'required',
            'join_date' => 'required|date',
            'trainer_fee' => 'nullable|numeric',
            'currency' => 'required',
            'grade_id' => 'required|array|min:1',
            'grade_id.*' => 'exists:trainerlavels,id',
            //'image' => 'required',
            'mode' => 'required',
            'type' => 'required',
            'status' => 'required',
            'no_of_hour_per_week' => 'required',
        ]);

        //echo $request->user_id;die();

        $oldGradeIds = $this->normalizeGradeIds($trainer->grade_id);

        $p_flag = 0;
        if (!empty($request->new_password) && !empty($request->confirm_password)) {
            if ($request->new_password == $request->confirm_password) {
                $password = $request->new_password;
                $p_flag = 1;
            } else {
                return redirect()->back()->with('confirm_password_faild', "New password and confirm password do not match");
            }
        }

        $user = User:: where('email', $request->official_email_id)->first();
        
        //echo "<pre>"; print_r($user); die();

        $resend_mail = 0;
        
        if (!empty($user)) {
            $user = $user->toArray();
            if ($user['email'] == $request->official_email_id && $user['id'] == $request->user_id) {
                $user = User:: find($request->user_id);
                if($user->email != $request->official_email_id) {
                    $resend_mail = 1;
                    if (!$p_flag) {
                        $password = Str::random(10);
                    }
                }

                if ($p_flag || $resend_mail) {
                    $user->password = Hash::make($password);
                }

                $user->name = $request->trainer_name;
                $user->email = $request->official_email_id;
                $user->country_id = $request->country;
                $user->suspend = $request->status;
                $user->save();
            } else {
                return redirect()->back()->with('email_faild', 'Sorry Email Address Already Exits');
            }
        } else {
            $user = User:: find($request->user_id);
            if($user->email != $request->official_email_id) {
                $resend_mail = 1;
                if (!$p_flag) {
                    $password = Str::random(10);
                }
            }

            if ($p_flag || $resend_mail) {
                $user->password = Hash::make($password);
            }

            $user->name = $request->trainer_name;
            $user->email = $request->official_email_id;
            $user->country_id = $request->country;
            $user->suspend = $request->status;
            $user->save();
        }

        $user_profile = Userprofile:: where('user_id', $request->user_id)->first();
        if(!empty($user_profile)) {
            $user_profile->email = $request->official_email_id;
            $user_profile->name = $request->trainer_name;
            $user_profile->save();
        }
        
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

        $trainer->official_email_id = $request->official_email_id;
        $trainer->grade_id = isset($request->grade_id) ? implode(',',$request->grade_id) : null;
        $trainer->incharge_email = '';

        $trainer->contact_no = $request->contact_no;
        $trainer->address = $request->address;
        $trainer->city = ucfirst($request->city);
        $trainer->country_id = $selectedCountryId;
        $trainer->join_date = $request->join_date;
        $trainer->mode = $request->mode;
        $trainer->type = $request->type;
        $trainer->status = $request->status;
        $trainer->no_of_hour_per_week = $request->no_of_hour_per_week;
        // The database column is non-nullable, so an omitted optional fee is stored as zero.
        $trainer->trainer_fee = $request->input('trainer_fee') ?? 0;
        $trainer->currency = $request->currency;
        $trainer->save();

        $newGradeIds = $this->normalizeGradeIds($request->grade_id);
        if (isPartnerUser() && $this->hasGradeChange($oldGradeIds, $newGradeIds)) {
            $this->notifySuperAdminTrainerLevelChange(
                $trainer,
                $oldGradeIds,
                $newGradeIds,
                'updated'
            );
        }

        // START - RESEND AN EMAIL TO TRAINER
        if($resend_mail) {
            safeMailAction('backend trainer resend onboarding mail', [
                'trainer_id' => $request->id ?? null,
                'recipient' => $request->official_email_id,
            ], function () use ($request, $password) {
                Mail::to($request->official_email_id)->bcc(env('MAIL_BCC'))->send(new CreatedTrainerMail($request->trainer_name, $request->official_email_id, $password));
            });
        } elseif ($p_flag) {
            safeMailAction('backend trainer change password mail', [
                'trainer_id' => $request->id ?? null,
                'recipient' => $request->official_email_id,
            ], function () use ($request, $password) {
                Mail::to($request->official_email_id)->send(new TrainerChangePasswordMail($request->trainer_name,$request->official_email_id,$password));
            });
        }
        // End - RESEND AN EMAIL TO TRAINER
        
        /*
        $getTrainer = Trainer::with('user')->find($request->id)->toArray();
        
        $user = $getTrainer['user'];
        $email = $getTrainer['official_email_id'];
        $emailSub = 'Trainer information updated!! <br>';
        $emailBody = 'Trainer Name: ' . $getTrainer['trainer_name'] . '<br>';
        $emailBody .= 'Your Username: ' . $getTrainer['official_email_id'] . '<br>';
        $emailBody .= 'Your Password: ' . 123456 . '<br>';
        $emailBody .= "Please login your dashboard by clicking this link <a href='" . url('/login') . "'>click here</a> <br>";
        $emailBody .= 'Thanks <br> venderkids';

        file_put_contents('../resources/views/mail.blade.php', $emailBody);
        $data = ['email'=> $email, 'subject'=> $emailSub];

        safeMailAction('backend trainer suspend mail', [
            'trainer_id' => $user->id ?? null,
            'recipient' => $email,
        ], function () use ($data) {
            Mail::send('mail', $data, function ($message) use ($data) {
                $message->to($data['email'], 'venderkids')->subject($data['subject']);
            });
        });
        
        if ($trainer) {
            $trainer_email = new EmailInfo;
            $trainer_email->name = $getTrainer['trainer_name'];
            $trainer_email->mail_address = $email;
            $trainer_email->mail_description = $emailBody;
            $trainer_email->group = 3;
            $trainer_email->save();
        }
        */
        return redirect()->route('backend.trainerlist.trainerList')->with('update_success', 'Data Updated successfully.');
    }

    public function trainerDelete($ids)
    {
        if (isPartnerUser()) {
            abort(403, 'You are not authorized to delete trainers.');
        }

        $all_ids = explode('|', $ids);
        $trainer_id = $all_ids[0];
        $user_id = $all_ids[1];

        DB::table('users')->where('id', $user_id)->delete();

        $data = Trainer :: find($trainer_id);
        if (!is_null($data)) {
            $data->delete();
        }

        TrainerAllocationNew::where('trainer_id', $trainer_id)->delete();

        return redirect()->route('backend.trainerlist.trainerList')->with('update_success', 'Data deleted successfully.');
    }

    //Send Notification Start

    public function trainerNotificationBox()
    {
        $notificationinfo = NotificationInfo::where('creator_id', 3)->get();

        return view('backend.trainer.notification.notification_box', [
            'notificationinfo' => $notificationinfo,
        ]);
    }

    public function trainerCompose()
    {
        $schools = School::get();
        $grades = grade::get();

        return view('backend.trainer.notification.compose', [
            'schools' => $schools,
            'grades' => $grades,
        ]);
    }

    public function trainerNotificationSend(Request $request)
    {
        // echo 11; die();
        $getAllStudents = Students::with('user')->get();
        // dd($getAllStudents);

        foreach ($getAllStudents as $student) {
            if ($student->school_id == $request->school_id && $student->grade_id == $request->grade_id) {
                $notificationinfo = new NotificationInfo();
                $notificationinfo->school_id = $request->school_id;
                $notificationinfo->grade_id = $request->grade_id;
                $notificationinfo->receiver_id = $student->id;
                $notificationinfo->title = $request->title;
                $notificationinfo->description = $request->description;
                $notificationinfo->creator_id = 3;
                $notificationinfo->save();

                // $students = Students::find($student->id);
                // $students->sms_status = 1;
                // $students->save();
            }
        }

        return redirect('admin/trainer/notificationbox')->with('success', 'Notification Send Successfully.');

        // echo "<pre>"; print_r($smsinfo); die();
    }

    //Send Notification End

    public function trainerCheckInfo(Request $request)
    {
        $trainer = Trainer::findOrFail($request->id);
        $this->ensurePartnerTrainerAccess($trainer);

        if ($request->action == 'checked') {
            if ($request->info == 1) {
                $trainer->assessment_done = 1;
                $result = $trainer->save();
            }
            if ($request->info == 2) {
                $trainer->demo_video = 1;
                $result = $trainer->save();
            }
            if ($request->info == 3) {
                $trainer->training_hour = 1;
                $result = $trainer->save();
            }
        } else {
            if ($request->info == 1) {
                $trainer->assessment_done = 0;
                $result = $trainer->save();
            }

            if ($request->info == 2) {
                $trainer->demo_video = 0;
                $result = $trainer->save();
            }

            if ($request->info == 3) {
                $trainer->training_hour = 0;
                $result = $trainer->save();
            }
        }

        echo json_encode($result);
    }

    //Trainer Suspend-------------------------------------
    public function trainerSuspend($id)
    {
        if (isPartnerUser()) {
            abort(403, 'You are not authorized to suspend trainers.');
        }

        $user = User::find($id);
        $user->suspend = 1;
        $user->save();

        $email = $user->email;

        $emailSub = 'Your Trainer Account Suspended!! <br>';
        $emailBody = 'Please contact VentureKids administrator <br>';
        $emailBody .= 'Thanks <br> VentureKids';

        file_put_contents('../resources/views/mail.blade.php', $emailBody);
        $data = ['email'=> $email, 'subject'=> $emailSub];

        safeMailAction('backend trainer unsuspend mail', [
            'trainer_id' => $user->id ?? null,
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
        $suspend_email->group = 3;
        $suspend_email->save();

        return redirect('admin/trainerlist')->with('suspend_success', 'Trainer Account Successfully Suspended!');
    }

    //Trainer UnSuspend-------------------------------------
    public function trainerUnsuspend($id)
    {
        if (isPartnerUser()) {
            abort(403, 'You are not authorized to unsuspend trainers.');
        }

        $user = User::find($id);
        $user->suspend = 2;
        $user->save();

        $email = $user->email;
        $emailSub = 'Your Trainer Account Succussfully Unsuspended!!';
        $emailBody = 'Welcome To VentureKids <br>';
        $emailBody .= 'Thanks <br> VentureKids';

        file_put_contents('../resources/views/mail.blade.php', $emailBody);
        $data = ['email'=> $email, 'subject'=> $emailSub];

        safeMailAction('backend trainer unsuspend mail', [
            'trainer_id' => $user->id ?? null,
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
        $unsuspend_email->group = 3;
        $unsuspend_email->save();

        // echo '<pre>'; print_r($user); die();

        return redirect('admin/trainerlist')->with('suspend_success', 'Trainer Account Successfully Unsuspended!');
    }

    public function allowCertificate(Request $request)
    {
        $reqcertificates = RequestedCertificate::orderBy('file', 'asc')->latest()->with('trainer')->get();

        return view('backend.trainer.certificatelist', compact('reqcertificates'));
    }

    public function uploadCertificate(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'trainer_id' => 'required|exists:trainers,id',
        ]);

        $request_file = $request->file('file');

        $file_name = Str::random(10); //unique nmae generate every time
        $ext = strtolower($request_file->getClientOriginalExtension());
        $file_full_name = 'certificates_' . $file_name . '.' . $ext;

        $upload_path = 'certificates/';

        $request_file->move($upload_path, $file_full_name);

        RequestedCertificate::where('trainer_id', $request->trainer_id)->update(['file' => $file_full_name]);

        return redirect()->back()->with('success', 'Certificate uploaded successfully');
    }

    public function deleteTrainerProfileImage(Request $request) { 
        $trainerId = $request->trainerId;
        $trainer_data = Trainer::find($trainerId);
        $this->ensurePartnerTrainerAccess($trainer_data);
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
        $this->ensurePartnerTrainerAccess($trainer_data);
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
        $this->ensurePartnerTrainerAccess($trainer_data);
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

    public function updateTrainerGrades()
    {
        $levelMap = [
            11 => 33, // thinkpreneur -> thinkpreneur+
            12 => 34, // createpreneur -> createpreneur+
            13 => 35, // launchpreneur -> launchpreneur+
        ];

        $updatedTrainers = [];

        $trainers = Trainer::select('id', 'trainer_name', 'grade_id')->get();
        // $trainers = Trainer::select('id', 'trainer_name', 'grade_id')
        //     ->whereRaw('FIND_IN_SET(?, grade_id)', [11])
        //     ->get();

        foreach ($trainers as $trainer) {
            $existingGrades = array_values(array_filter(array_map('trim', explode(',', (string) $trainer->grade_id))));
            $mergedGrades = $existingGrades;
            $matchedBaseLevels = [];

            foreach ($levelMap as $baseLevelId => $plusLevelId) {
                $baseLevelId = (string) $baseLevelId;
                $plusLevelId = (string) $plusLevelId;

                if (in_array($baseLevelId, $existingGrades, true) && !in_array($plusLevelId, $mergedGrades, true)) {
                    $mergedGrades[] = $plusLevelId;
                    $matchedBaseLevels[] = $baseLevelId;
                }
            }

            $mergedGrades = array_values(array_unique($mergedGrades));

            if ($mergedGrades !== $existingGrades) {
                $trainer->grade_id = implode(',', $mergedGrades);
                $trainer->save();

                $updatedTrainers[] = [
                    'trainer_id' => $trainer->id,
                    'trainer_name' => $trainer->trainer_name,
                    'old_grades' => $existingGrades,
                    'new_grades' => $mergedGrades,
                    'matched_base_levels' => $matchedBaseLevels,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'updated_count' => count($updatedTrainers),
            'updated_trainers' => $updatedTrainers,
            'message' => 'Trainer grade access updated successfully.',
        ]);
    }

    private function ensurePartnerTrainerAccess($trainer): void
    {
        if (!$trainer || !isPartnerUser()) {
            return;
        }

        if ((int) $trainer->country_id !== (int) partnerCountryId()) {
            abort(403, 'You are not authorized to access this trainer.');
        }
    }

    private function partnerTrainerCurrency(): ?string
    {
        if (!isPartnerUser()) {
            return null;
        }

        $currency = DB::table('currencies')
            ->where('id', Auth::user()->currency_id)
            ->value('code');

        $currency = $currency ? strtolower($currency) : null;
        return $currency === 'usd' ? 'dollar' : $currency;
    }

    private function normalizeGradeIds($gradeIds): array
    {
        if (empty($gradeIds)) {
            return [];
        }

        if (is_string($gradeIds)) {
            $gradeIds = explode(',', $gradeIds);
        }

        return array_values(array_unique(array_filter(array_map('trim', (array) $gradeIds), function ($value) {
            return $value !== '';
        })));
    }

    private function hasGradeChange(array $oldGradeIds, array $newGradeIds): bool
    {
        sort($oldGradeIds);
        sort($newGradeIds);

        return $oldGradeIds !== $newGradeIds;
    }

    private function trainerLevelNames(array $gradeIds): string
    {
        if (empty($gradeIds)) {
            return 'Not assigned';
        }

        return Trainerlavel::whereIn('id', $gradeIds)->pluck('grade')->implode(', ');
    }

    private function notifySuperAdminTrainerLevelChange(Trainer $trainer, array $oldGradeIds, array $newGradeIds, string $action = 'updated'): void
    {
        $superAdminEmail = env('MAIL_ADMIN');

        if (empty($superAdminEmail)) {
            return;
        }

        $partner = Auth::user();
        $oldLevels = $this->trainerLevelNames($oldGradeIds);
        $newLevels = $this->trainerLevelNames($newGradeIds);

        if ($action !== 'created' && $oldLevels === $newLevels) {
            return;
        }

        try {
            Mail::to($superAdminEmail)->bcc(env('MAIL_BCC'))->send(new TrainerLevelUpdatedMail(
                $trainer->trainer_name,
                optional($partner)->name ?? 'Partner',
                optional($partner)->email ?? '',
                $oldLevels,
                $newLevels,
                $action
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send trainer level change email: ' . $e->getMessage());
        }
    }
}
