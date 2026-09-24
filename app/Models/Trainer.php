<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;

    protected $table = 'trainers';
    //protected $primarykey = "id";
    protected $fillable = ['id', 'user_id', 'created_by', 'trainer_name', 'email', 'trainer_fee', 'currency', 'contact_no', 'address', 'city', 'country_id', 'join_date', 'date_of_birth', 'image', 'mode', 'type', 'no_of_hour_per_week', 'zoom'];

    protected $casts = ['zoom' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'trainer_id');
    }

    // public function getSchool(){
    //     return $this->belongsTo(School::class, 'school_id', 'id');
    // }

    // public function getGrade(){
    //     return $this->belongsTo(Grade::class, 'student_grade_id', 'id');
    // }

     public function country()
    {
       return $this->belongsTo(Country::class, 'country_id');
    }
}
