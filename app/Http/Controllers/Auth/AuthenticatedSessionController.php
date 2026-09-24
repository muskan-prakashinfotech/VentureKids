<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Helpers\LoginHelper;
use App\Enums\UserType;

use App\Models\School;
use App\Models\Students;
use App\Models\Trainer;

use Illuminate\Support\Facades\Mail;
use App\Mail\StudentParentMail;
use Illuminate\Support\Facades\Cookie;


class AuthenticatedSessionController extends Controller
{

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $login_pg = 'auth.login-new';
        if (tenant() && tenant()->white_label) {  
            $login_pg = 'auth.login';
        }
        return view($login_pg);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $request->authenticate();
        
        if(isset($request->remember)) {
            
            // set remember me expire time
            $rememberTokenExpireMinutes = 43200; // 30 days
    
            // first we need to get the "remember me" cookie's key, this key is generate by laravel randomly
            // it looks like: remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d
            $rememberTokenName = Auth::getRecallerName();
    
            // reset that cookie's expire time
            Cookie::queue($rememberTokenName, Cookie::get($rememberTokenName), $rememberTokenExpireMinutes);
        }

        /**
         * Regenerating the session ID is often done in order to prevent malicious users from exploiting a session fixation attack on your application.
         */
        $request->session()->regenerate();

        $redirectTo = request()->redirectTo;

        
        if (Auth::check() && in_array(Auth::user()->group, [4, 5])) {
            $get_user = User::where('email', $request->email)->orWhere('username', $request->email)->first();
        } else {
            $get_user = User::where('email', $request->email)->first();
        }

        $get_user = $get_user?->toArray();
        
        if (!empty($get_user)) {

            if (Hash::check($request->password, $get_user['password'])) {
                //$user_name = $get_user['first_name'].' '.$get_user['last_name'];
                Session::put('user_id', $get_user['id']);
                Session::put('email', $get_user['email']);
                Session::put('first_name', $get_user['first_name']);
                Session::put('last_name', $get_user['last_name']);
                Session::put('user_group', $get_user['group']);

                // echo "<pre>"; print_r($school); die();
                // $get_user['id']
                if($get_user['group'] == 1){

                    Session::put('admin_name', $get_user['name']);
                    Session::put('admin_image', $get_user['avatar']);

                    if ($redirectTo) {
                        return redirect($redirectTo);
                    } else {
                        return redirect('admin/dashboard');
                    }
                }else if($get_user['group'] == 5){

                    Session::put('admin_name', $get_user['name']);
                    Session::put('admin_image', $get_user['avatar']);
                    Session::put('country_id', $get_user['country_id']);

                    if ($redirectTo) {
                        return redirect($redirectTo);
                    } else {
                        return redirect('admin/dashboard');
                    }
                }else if($get_user['group'] == 2){

                    $school = School::where('user_id', $get_user['id'])->first()->toArray();
                    Session::put('school_name', $school['school_name']);
                    Session::put('school_id', $school['id']);
                    Session::put('school_image', $school['school_logo']);
                    Session::put('country_id', $school['country_id']);
                    Session::put('tenant_id', $school['tenant_id']);
                    if ($redirectTo) {
                        return redirect($redirectTo);
                    } else {
                        return redirect('school/dashboard');
                    }

                    // echo "School"; die();
                }else if($get_user['group'] == 3){
                    $trainer = Trainer::where('user_id', $get_user['id'])->first()->toArray();
                    Session::put('trainer_name', $trainer['trainer_name']);
                    Session::put('trainer_image', $trainer['image']);

                    Session::put('user_id', $trainer['user_id']);
                    Session::put('trainer_id', $trainer['id']);
                    Session::put('country_id', $trainer['country_id']);
                    return redirect('trainer/content/list');
                }else{
                    $student = Students::with('school')->where('user_id', $get_user['id'])->first()->toArray();
                    Session::put('student_name', $student['name']);
                    Session::put('student_image', $student['image']);
                    
                    Session::put('student_id', $student['id']);
                    Session::put('country_id', $student['country_id']);
                    Session::put('student_school_id', $student['school_id']);
                    Session::put('school_name', $student['school']['school_name']);
                    if (isset($student['school']['tenant_id'])) {
                        Session::put('tenant_id', $student['school']['tenant_id']);
                    }
                    /*
                    if(!empty($student['parent_email'])) {

                        $studentMailData['name'] = $get_user['name'];
                        $studentMailData['email'] = $get_user['email'];
                        $studentMailData['parent_email'] = $student['parent_email'];
                        
                        Mail::to($student['parent_email'])->send(new StudentParentMail($studentMailData));
                    }
                    */

                    // Prepare login tracking data
                    LoginHelper::track(UserType::STUDENT, $student['id']);
                    
                    return redirect('student/content/list');
                }

            }

            // echo "<pre>"; print_r(Session::); die();


        }


        // if ($redirectTo) {
        //     return redirect($redirectTo);
        // } else {
        //     return redirect('admin/dashboard');
        // }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $userId = Session::get('user_id');

        $user_group = Session::get('user_group');

        $logout_redirect_url = '';
        if(in_array($user_group, [2,4])) {
            $school_id = Session::get('school_id');
            if($user_group == 4) {
                $school_id = Session::get('student_school_id');
            }
            $logout_redirect_url = School::select('logout_redirect_url')->find($school_id)->logout_redirect_url;
        }

        // force remove the remember cookie
        $rememberTokenName = Auth::getRecallerName();
        Cookie::queue(Cookie::forget($rememberTokenName));

        Auth::logout();
        
        $user = User::find($userId);
        $user->remember_token= null;
        $user->save();
            
        $request->session()->invalidate();
        
        $request->session()->regenerateToken();
        
        if(!empty($logout_redirect_url)) {
            return redirect($logout_redirect_url);     
        }

        return redirect('/');
    }

    public function logout()
    {
        $userId = Session::get('user_id');

        // force remove the remember cookie
        $rememberTokenName = Auth::getRecallerName();
        Cookie::queue(Cookie::forget($rememberTokenName));

        Auth::logout();

        $user = User::find($userId);
        $user->remember_token= null;
        $user->save();
        
        Session::flush();

        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return redirect(route('home'))->with('success', 'You are logged out.')->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
