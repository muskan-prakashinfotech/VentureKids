<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCentralDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $currentLoginUserCredentials = [];

        if ($request->has('email')) {
            $currentLoginUserCredentials = $request->only('email');
        }

        /* START - LOGIN WITH ORIGINAL DOMAIN - RESTRICT TO TENANT DOMAIN */
        if (in_array(request()->getHost(), config('tenancy.central_domains')) && !empty($currentLoginUserCredentials)) {
            $hasRedirection = $this->userBelongsToCentralDomain($currentLoginUserCredentials);
            if (!empty($hasRedirection)) {
                return redirect()->to($hasRedirection);
            }
        }
        /* END - LOGIN WITH ORIGINAL DOMAIN - RESTRICT TO TENANT DOMAIN */ 

        return $next($request);
    }

    public function userBelongsToCentralDomain($user)
    {
        if (isset($user['email'])) {
            $loginUser = \App\Models\User::with('schools', 'students')->select('id', 'group')->where('email', $user['email'])->first();

            if (isset($loginUser)) {
                /**
                 * USER GROUP
                 * 2 -> SCHOOL
                 */
                if (in_array($loginUser->group, [2]) &&
                    !empty($loginUser->schools->tenant_id)) {

                    $isLoginUserDomainFound = \App\Models\Domain::select('domain')
                        ->where('tenant_id', $loginUser->schools->tenant_id)
                        ->first();

                    // IF LOGIN USER TENANT DOMAIN FOUND THEN REDIRECT TO THAT DOMAIN
                    if (isset($isLoginUserDomainFound)) {
                        $redirectTo = config("app.protocol").$isLoginUserDomainFound->domain.config("tenancy.sub_domain");

                        return $redirectTo;
                    }
                    
                } else if (in_array($loginUser->group, [4]) && //  4 -> STUDENT
                    !empty($loginUser->students) &&
                    !empty($loginUser->students->school)) {

                    $isLoginUserDomainFound = \App\Models\Domain::select('domain')
                        ->where('tenant_id', $loginUser->students->school->tenant_id)
                        ->first();

                    // IF LOGIN USER TENANT DOMAIN FOUND THEN REDIRECT TO THAT DOMAIN
                    if (isset($isLoginUserDomainFound)) {
                        $redirectTo = config("app.protocol").$isLoginUserDomainFound->domain.config("tenancy.sub_domain");

                        return $redirectTo;
                    }

                }
            }
        }

    }
}
