<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCertificates extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'grade_id',
        'released_by',
        'trainer_id',
        'unique_id',
        'issue_date',
        'download_token',
        'token_expires_at',
        'pdf_path'
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }
}
