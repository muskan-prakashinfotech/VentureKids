<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockPartnerRestrictedSections
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || (int) Auth::user()->group !== 5) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();

        if (isPartnerRestrictedRoute($routeName)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
