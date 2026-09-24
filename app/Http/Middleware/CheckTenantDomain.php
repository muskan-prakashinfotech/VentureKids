<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class CheckTenantDomain
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

        /* START - LOGIN WITH TENANT DOMAIN */
        if (!empty($currentLoginUserCredentials)) {
            $hasRedirection = $this->userBelongsToTenantDomain($currentLoginUserCredentials);
             if (!empty($hasRedirection)) {
                return redirect()->to($hasRedirection);
            }
        }
        /* END - LOGIN WITH TENANT DOMAIN */   

        return $next($request);
    }

    public function userBelongsToTenantDomain($user)
    {
        $redirectTo = config('app.url');
        
        if (isset($user['email'])) {

            $loginUser = \App\Models\User::with('schools', 'students')
                ->select('id', 'group')
                ->where('email', $user['email'])
                ->first();
            
            if (isset($loginUser) && tenant()) {
                if ($loginUser->id !== tenant()->user_id) {

                    /**
                     * USER GROUP
                     * 2 -> SCHOOL
                     */
                    if (in_array($loginUser->group, [2])) {

                        /* START - CHECK IF CURRENT USER'S SCHOOL HAS TENANT */
                        if (!empty($loginUser->schools) && isset($loginUser->schools->tenant_id)) {

                            $isLoginUserDomainFound = \App\Models\Domain::select('domain')
                                ->where('tenant_id', $loginUser->schools->tenant_id)
                                ->first();

                            // IF LOGIN USER TENANT DOMAIN FOUND THEN REDIRECT TO THAT DOMAIN
                            if (isset($isLoginUserDomainFound)) {
                                $redirectTo = config("app.protocol").$isLoginUserDomainFound->domain.config("tenancy.sub_domain");
                            }
                        }
                        /* END - CHECK IF CURRENT USER'S SCHOOL HAS TENANT */

                    } else if (in_array($loginUser->group, [4])) { // 4 -> STUDENT

                        if (!empty($loginUser->students) && $loginUser->students->school_id != tenant()->id) {
                            if (!empty($loginUser->students->school)) {
        
                                $isLoginUserDomainFound = \App\Models\Domain::select('domain')
                                    ->where('tenant_id', $loginUser->students->school->tenant_id)
                                    ->first();

                                // IF LOGIN USER TENANT DOMAIN FOUND THEN REDIRECT TO THAT DOMAIN
                                if (isset($isLoginUserDomainFound)) {
                                    $redirectTo = config("app.protocol").$isLoginUserDomainFound->domain.config("tenancy.sub_domain");
                                }
                            }
                        } else if (!empty($loginUser->students) && $loginUser->students->school_id == tenant()->id) {
                            $redirectTo = null;
                        }
                    }
                } else {
                    $redirectTo = null;
                }
            }
        }
        
        return $redirectTo;
    }

}
