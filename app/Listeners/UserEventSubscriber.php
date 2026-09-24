<?php

namespace App\Listeners;

use App\Models\Userprofile;
use Carbon\Carbon;
use Log;

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin($event)
    {
        try {
            $user = $event->user;
            $user_profile = $user->userprofile;
            
            if($user_profile){
                /*
                * Updating user profile data after successful login
                */
                $user_profile->last_login = Carbon::now();
                $user_profile->last_ip = request()->getClientIp();
                $user_profile->login_count = $user_profile->login_count + 1;
                $user_profile->save();
            }else{
                $user_profile = new Userprofile;
                $user_profile->last_login = Carbon::now();
                $user_profile->last_ip = request()->getClientIp();
                $user_profile->login_count = $user_profile->login_count + 1;
                $user_profile->save();
            }
        } catch (\Exception $e) {
            Log::error($e);
        }

        Log::debug('Login Success: ' . $user->name . ', IP:' . request()->getClientIp());
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout($event)
    {
        $user = $event->user;

        Log::debug('Logout Success. ' . $user->name);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'App\Listeners\UserEventSubscriber@handleUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'App\Listeners\UserEventSubscriber@handleUserLogout'
        );
    }
}
