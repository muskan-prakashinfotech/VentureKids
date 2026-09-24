<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardAssessmentStudentAnswer extends Model
{
    use HasFactory;

    protected $table = 'standard_assessment_student_answers';

    protected $fillable = [
        'student_id',
        'question_id',
        'selected_option',
        'is_submitted',
        'started_at',
        'answered_at',
    ];
}

