<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealQStudentParameterScore extends Model
{
    protected $table = 'realq_assessment_student_parameter_scores';

    protected $fillable = [
        'student_id',
        'school_id',
        'student_grade_id',
        'assessment_report_id',
        'parameter_id',
        'rubric_id',
        'generate_time_rubric_id',
    ];
}
