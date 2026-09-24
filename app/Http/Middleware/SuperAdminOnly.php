<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && (int) Auth::user()->group === 1) {
            return $next($request);
        }

        abort(403, 'Access denied.');
    }
}
