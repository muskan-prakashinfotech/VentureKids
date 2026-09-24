<?php

namespace App\Providers;

use App\Models\NotificationInfo;
use App\Models\School;
use App\Models\SchoolNotification;
use App\Models\StudentNotification;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\TrainerNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use App\Services\SocialiteProviders\VnayaProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Socialite::extend('vnaya', function($app) {
            $config = $app['config']['services.vnaya'];

            return new VnayaProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                URL::to($config['redirect'])
            );
        });

        View::composer('backend.includes.header', function ($view) {
            $view->with('notificationinfo', NotificationInfo::get());
            $view->with('schoolnotification', NotificationInfo::where(['receiver_status' => 1, 'receiver_id' => Session::get('school_id')])->get());
            $view->with('trainernotification', NotificationInfo::where(['receiver_status'=> 2, 'receiver_id'=> Session::get('trainer_id')])->get());
            $view->with('studentnotification', NotificationInfo::where(['receiver_status'=> 3, 'receiver_id' => Session::get('student_id')])->get());
            if (Auth::check()) {
                switch (Auth::user()->group) {
                    case 2:
                        // school
                        $school = School::where('user_id', Auth::id())->first();
                        if (isset($school)) {
                            $view->with('notifications', SchoolNotification::where('school_id', $school->id)->count());
                        } else {
                            $view->with('notifications', 0);
                        }
                        break;
                    case 3:
                        $trainer = Trainer::where('user_id', Auth::id())->first();
                        if (isset($trainer)) {
                            $view->with('notifications', TrainerNotification::where('trainer_id', $trainer->id)->count());
                        } else {
                            $view->with('notifications', 0);
                        }
                        break;
                    case 4:
                        // student
                        $student = Students::where('user_id', Auth::id())->first();
                        if (isset($student)) {
                            $view->with('notifications', StudentNotification::where('student_id', $student->id)->count());
                        } else {
                            $view->with('notifications', 0);
                        }
                        break;

                    default:
                        $view->with('notifications', 0);
                        break;
                }
            } else {
                $view->with('notifications', 0);
            }
            $app_title = 'VentureKids';
            if(in_array(Session::get('user_group'), [2,4])) {
                $app_title = Session::get('school_name');
            }
            $view->with('app_title', $app_title);
        });

        Paginator::useBootstrap();

        Blade::component('components.backend-breadcrumbs', 'backendBreadcrumbs');
    }
}
