<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentObservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'trainer_id',
        'school_id',
        'grade_id',
        'observation_id',
        'external_session_id',
        'session_date',
        'short_note',
        'image',
    ];

    public function studMindsetData() {
        return $this->hasMany(StudentMindsetData::class, 'student_observation_id');
    }

    public function getStudent() 
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function getTrainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function getLevel()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function getStream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function getSession()
    {
        return $this->belongsTo(Studentscontent::class, 'session_id');
    }
}
