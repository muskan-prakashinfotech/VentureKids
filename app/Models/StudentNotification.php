<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    protected $fillable = ['student_id', 'title', 'description', 'admin'];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
