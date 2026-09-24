<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;
    protected $table = 'student_attendance';
    protected $fillable = ['student_id', 'trainer_id', 'status', 'date'];

    public function trainer()
    {
        return $this->hasMany(Trainer::class, 'trainer_id');
    }
}
