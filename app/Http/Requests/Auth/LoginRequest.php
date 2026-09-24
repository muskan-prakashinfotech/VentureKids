<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\School;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'    => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('email');

        // Check if value is email or username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $login,
            'password' => $this->password,
            'status' => 1,
            'suspend' => 2,
        ];
        
        /* START - AUTHENTICATION BY CREDENTIALS */
        $isAuthenticateByCredentials = Auth::attempt($credentials, $this->filled('remember'));
        if (!$isAuthenticateByCredentials) {
            RateLimiter::hit($this->throttleKey());

            if($credentials['suspend'] == 2){
                throw ValidationException::withMessages([
                    'email' => __('auth.suspend'),
                ]);
            }else{
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }
        }
        /* END - AUTHENTICATION BY CREDENTIALS */

        /* START - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */
        $loginUser = Auth::user();

        if ($loginUser->group == 5 && $loginUser->partnership_end_date
            && Carbon::today()->gt(Carbon::parse($loginUser->partnership_end_date)->startOfDay())) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your partnership has ended, please contact Super Admin.',
            ]);
        }

        $isAuthenticatedByDomain = true;
        $tenantId = tenant() ? tenant()->getTenantKey() : null;

        if($loginUser->group == 2) {
            $isAuthenticatedByDomain = School::where([
                'user_id' => Auth::user()->id,
                'official_email_id' => $credentials['email'],
                'tenant_id' => $tenantId
            ])->exists();
        } elseif ($loginUser->group == 4) {
            $isAuthenticatedByDomain = School::where([
                'tenant_id' => $tenantId
            ])->whereHas('students', function ($query) use($loginUser){
                $query->where('user_id', $loginUser->id);
            })->exists();
        } else if(!empty($tenantId)) {
            $isAuthenticatedByDomain = false;
        }

        if (!$isAuthenticatedByDomain) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        /* END - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
