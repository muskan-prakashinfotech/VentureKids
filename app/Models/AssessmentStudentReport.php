<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Students;

class AssessmentStudentReport extends Model
{
    use HasFactory;

    protected $table = 'assessment_student_report';

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    protected $fillable = [
        'student_id',
        'student_grade_id',
        'assessment_type',
        'status',
        'report_path',
        'report_text',
        'report_data',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'report_data' => 'array',
        'approved_at' => 'datetime',
    ];
}

