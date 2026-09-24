<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminForgotPasswordMail;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Helpers\CommonHelper;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $forgot_password_pg = 'auth.forgot-password-new';
        if (tenant() && tenant()->white_label) {
            $forgot_password_pg = 'auth.forgot-password';
        }
        return view($forgot_password_pg);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
        ]);

        /* START - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */
        $isAuthenticatedByDomain = true;
        $tenantId = tenant() ? tenant()->getTenantKey() : null;
        $userData = User::select(['id','group'])->where('email', $request->email)->orWhere('username', $request->email)->first();
        $email = $request->email;
        if($userData) {
            if($userData->group == 2) {
                $isAuthenticatedByDomain = School::where([
                    'official_email_id' => $request->email,
                    'tenant_id' => $tenantId
                ])->exists();
            } elseif ($userData->group == 4) {
                $email = CommonHelper::getRecipientEmailByUserId($userData->id);
                $isAuthenticatedByDomain = School::where([
                    'tenant_id' => $tenantId
                ])->whereHas('students', function ($query) use($userData){
                    $query->where('user_id', $userData->id);
                })->exists();
            } else if(!empty($tenantId)) {
                $isAuthenticatedByDomain = false;
            }  
        } else {
            $isAuthenticatedByDomain = false;
        }

        if (!$isAuthenticatedByDomain) {
            return back()->withErrors(['status' => 'Username or Email not found!']);
        }  
        /* END - AUTHENTICATION BY DOMAIN IF EXISTS TENANT */

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            ['email' => $email]
        );
        if ($userData->group == 1) {
            safeMailAction('admin forgot password mail', [
                'recipient' => env('MAIL_ADMIN'),
                'request_ip' => $request->ip(),
                'email' => $email,
            ], function () use ($request, $email) {
                Mail::to(env('MAIL_ADMIN'))->send(new AdminForgotPasswordMail($request->ip(), $email));
            });
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }
}
