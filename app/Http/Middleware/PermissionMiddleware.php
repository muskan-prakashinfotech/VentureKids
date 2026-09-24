<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middlewares\PermissionMiddleware as SpatiePermissionMiddleware;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Sub Admins (group 5) should behave like Super Admins for permission checks
     * and remain restricted only by country-scoped query filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (Auth::check() && in_array((int) Auth::user()->group, [1, 5], true)) {
            return $next($request);
        }

        return app(SpatiePermissionMiddleware::class)->handle($request, $next, ...$permissions);
    }
}
