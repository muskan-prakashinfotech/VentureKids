<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestedStudentCertificate extends Model
{
    protected $fillable = ['student_id', 'file'];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }
}
