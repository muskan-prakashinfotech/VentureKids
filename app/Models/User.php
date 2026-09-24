<?php

namespace App\Models;

use App\Models\Presenters\UserPresenter;
use App\Models\Traits\HasHashedMediaTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use HasHashedMediaTrait;
    use UserPresenter;
    use HasApiTokens;

    protected $guarded = [
        'id',
        'updated_at',
        '_token',
        '_method',
        'password_confirmation',
    ];

    protected $dates = [
        'deleted_at',
        'date_of_birth',
        'email_verified_at',
        'partnership_start_date',
        'partnership_end_date',
    ];

    protected $fillable = ['id', 'name', 'first_name', 'last_name', 'username', 'email', 'mobile', 'gender', 'country_id' , 'date_of_birth', 'password', 'avatar', 'status', 'group', 'allow_add_trainers', 'no_of_license_purchased', 'partnership_start_date', 'partnership_end_date', 'currency_id', 'partnership_reminder_job_ids'];

    protected $casts = [
        'allow_add_trainers' => 'integer',
        'no_of_license_purchased' => 'integer',
        'partnership_reminder_job_ids' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function providers()
    {
        return $this->hasMany('App\Models\UserProvider');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Userprofile');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userprofile()
    {
        return $this->hasOne('App\Models\Userprofile');
    }

    /**
     * Get the list of users related to the current User.
     *
     * @return [array] roels
     */
    public function getRolesListAttribute()
    {
        return array_map('intval', $this->roles->pluck('id')->toArray());
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return env('SLACK_NOTIFICATION_WEBHOOK');
    }

    public function schools()
    {
        return $this->hasOne(\App\Models\School::class, 'user_id');
    }

    public function students()
    {
        return $this->hasOne(\App\Models\Students::class, 'user_id');
    }

    public function trainer()
    {
        return $this->hasOne(\App\Models\Trainer::class, 'user_id');
    }
}
