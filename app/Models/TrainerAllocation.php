<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerAllocation extends Model
{
    use HasFactory;

    protected $table = 'trainer_allocation';
    protected $appends = ['day_name'];
    protected $fillable = ['school_id', 'class_schedule', 'grade', 'day', 'class_start', 'class_end', 'class_date', 'trainer_id', 'class_duration'];

    public function getDayNameAttribute()
    {
        switch ($this->day) {
            case 0:
                return 'sunday';
                break;
            case 1:
                return 'monday';
                break;
            case 2:
                return 'tuesday';
                break;
            case 3:
                return 'wednesday';
                break;
            case 4:
                return 'thursday';
                break;
            case 5:
                return 'friday';
                break;
            case 6:
                return 'saturday';
                break;

            default:
                return null;
                break;
        }
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function level()
    {
        return $this->belongsTo(Grade::class, 'grade');
    }

    public function getSchool()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'allowcated_id');
    }
}
