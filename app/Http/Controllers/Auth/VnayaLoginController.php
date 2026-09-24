<?php

namespace App\Http\Controllers\Auth;

use App\Events\Frontend\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\Students;
use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use App\Services\StudentService;
use App\Models\School;

class VnayaLoginController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService) {
        $this->studentService = $studentService;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    public function redirectTo($errorMessage="")
    {
          abort(404, $errorMessage);
    }

    /**
     * Obtain the user information from Provider (Facebook, Google, GitHub...).
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        try {
            $provider = 'vnaya';

            $socialiteUser = Socialite::driver($provider)->user();

            if(empty($socialiteUser) || empty($socialiteUser->token) || empty($socialiteUser->id)) {
                \Log::error('SSO Login error: No result found from server');
                $this->redirectTo('Failed to authenticate with the provider');
            }

            $authUser = $this->findOrCreateUser($socialiteUser, $provider);

            if(!empty($authUser)) {
                $isLogin = $this->loginUser($authUser);
                if($isLogin) {
                    return redirect('student/content/list');
                }
            }
        } catch (Exception $e) {
            // Handle other generic exceptions
            \Log::error('SSO Login error occurred: ' . $e->getMessage());

            return $this->redirectTo('Failed to authenticate with the provider');
        }

        return $this->redirectTo('Failed to authenticate with the provider');
    }

    /**
     * Return user if exists; create and return if doesn't.
     *
     * @param $githubUser
     *
     * @return User
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        try {
            if(empty(tenant()) || empty(tenant()->tenant_id)) {
                \Log::error('SSO Login error: Invalid tenant');
                return false;
            }

            if ($authUser = UserProvider::where('provider_id', $socialUser->id)->where('provider', $provider)->first()) {
                return User::where('id', $authUser->user_id)->where('group', 4)->first();
            } elseif (User::where('email', $socialUser->email)->first()) {
                \Log::error('SSO Login error: '.$socialUser->email.' Email already exists');
                 return false;
            } else {
                if (!$this->validateUser($socialUser->attributes)) {
                    \Log::error('SSO Login error: Validation failes');
                    return false;
                }

                // check for max student limit
                if(!$this->studentService->checkMaxStudentLimit(tenant())) {
                    \Log::error('SSO Login error: School-'. tenant()->school_name.' Student: '.$socialUser->email.'Max student limit exceed');
                    return false;
                }

                // CREATE NEW STUDENT
                $user = $this->studentService->createStudent(tenant(), $socialUser->attributes);

                if(empty($user)) {
                    \Log::error('SSO Login error: Some error occur while creating student '.$socialUser->email);
                    return false;
                }

                safeEventAction('vnaya login user registered', [
                    'user_id' => $user->id ?? null,
                    'email' => $user->email ?? null,
                ], function () use ($user) {
                    event(new UserRegistered($user));
                });

                UserProvider::create([
                    'user_id'     => $user->id,
                    'provider_id' => $socialUser->id,
                    'avatar'      => $socialUser->avatar,
                    'provider'    => $provider,
                ]);

                return $user;

            }
        } catch (\Exception $e) {
            \Log::error('SSO Login error:'. $e->getMessage());
            return false;
        }
    }

    private function loginUser($loginUser)
    {
        try {
            /* START - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */
            $tenantId = tenant() ? tenant()->getTenantKey() : null;

            if(empty($loginUser) || $loginUser->group != 4 || empty($tenantId)) {
                \Log::error('SSO Login error: Invalid tenant or user group');
                return false;
            }

            // @todo needs to clarity what if user exists but student not exists
            $isAuthenticatedByDomain = School::where([
                'tenant_id' => $tenantId
            ])->whereHas('students', function ($query) use($loginUser){
                $query->where('user_id', $loginUser->id);
            })->exists();

            if(!$isAuthenticatedByDomain) {
                \Log::error('SSO Login error: Invalid tenant or student');
                return false;
            }

            /* END - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */

            $student = Students::with('school')->where('user_id', $loginUser->id)->first();
            $student = (is_object($student)) ? $student->toArray() : $student;

            if (empty($student)) {
                return false;
            }

            Session::put('user_id', $loginUser->id);
            Session::put('email', $loginUser->email);
            Session::put('first_name', $loginUser->first_name);
            Session::put('last_name', $loginUser->last_name);
            Session::put('user_group', $loginUser->group);
            Session::put('student_name', $student['name']);
            Session::put('student_image', $student['image']);
            Session::put('student_id', $student['id']);
            Session::put('country_id', $student['country_id']);
            Session::put('student_school_id', $student['school_id']);

            if (isset($student['school']['tenant_id'])) {
                Session::put('tenant_id', $student['school']['tenant_id']);
            }

            Auth::login($loginUser, true);
            return true;

        } catch (\Exception $e) {
            \Log::error('SSO Login error: '. $e->getMessage());
            return false;
        }
    }

    private function validateUser($user) {
        // @todo add validation for all required fields
        $validator = Validator::make($user, [
            'name'          => 'required',
            'email'         => 'required|email|unique:users,email',
//            'student_grade' => 'required',
//            'date_of_birth' => 'required|date',
//            'gender'        => 'required|in:m,f,o',
//            'school_batch' => 'required',
        ]);

        if ($validator->fails()) {
            \Log::error("SSO Login validation error : ").
            \Log::error($validator->errors()->all());
            return false;
        }

        return true;
    }
}
