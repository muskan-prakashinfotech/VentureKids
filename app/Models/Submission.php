<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'assignment_id', 'link', 'file', 'comment', 'feedback', 'manual_submission'];

    public function assignment()
    {
        return $this->belongsTo(StudentCommunications::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
