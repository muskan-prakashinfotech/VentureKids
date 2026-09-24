<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCommunications extends Model
{
    use HasFactory;
    protected $table = 'assignments';

    public function assignmentfiles()
    {
        return $this->hasMany(AssingmentFiles::class, 'assignment_id', 'id');
    }

    public function assinmentdetails()
    {
        return $this->hasMany(AssignmentDetails::class, 'assignment_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    public function level()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function session()
    {
        return $this->belongsTo(Studentscontent::class, 'session_id');
    }

    public function getNextDisplayOrderId()
    {
        return ($this->max('display_order_id') + 1);
    }
}
