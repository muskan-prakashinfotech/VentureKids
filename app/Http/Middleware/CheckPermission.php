<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $validate)
    {
        $segment = $request->segment(1);
        $expectedGroup = null;

        switch ($segment) {
            case 'admin':
                $expectedGroup = 1;
                break;
            case 'school':
                $expectedGroup = 2;
                break;
            case 'trainer':
                $expectedGroup = 3;
                break;
            case 'student':
                $expectedGroup = 4;
                break;
            default:
                return $next($request);
        }

        if (!Auth::check()) {
            abort(401);
        }

        $userGroup = (int) Auth::user()->group;

        if ((int) $validate === $expectedGroup && (
            $userGroup === $expectedGroup ||
            ($expectedGroup === 1 && $userGroup === 5)
        )) {
            return $next($request);
        }

        /*
        $currentRole = $this->getRoleName($userGroup);
        $allowedPrefix = $this->getAllowedPrefix($expectedGroup);

        $message = sprintf(
            'Access denied. You are logged in as %s and can only access /%s/* routes.',
            $currentRole,
            $allowedPrefix
        );
        */
        
        $message = 'Access denied.';
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        return redirect($this->getRoleHomeUrl($userGroup))
            ->with('error', $message);
    }

    private function getRoleName(int $group): string
    {
        return match ($group) {
            1 => 'Super Admin',
            2 => 'School',
            3 => 'Trainer',
            4 => 'Student',
            5 => 'Partner',
            default => 'Unknown Role',
        };
    }

    private function getAllowedPrefix(?int $group): string
    {
        return match ($group) {
            1 => 'admin',
            2 => 'school',
            3 => 'trainer',
            4 => 'student',
            default => 'unknown',
        };
    }

    private function getRoleHomeUrl(int $group): string
    {
        return match ($group) {
            1, 5 => url('admin/dashboard'),
            2 => url('school/dashboard'),
            3 => url('trainer/content/list'),
            4 => url('student/content/list'),
            default => route('login'),
        };
    }
}
