<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_student_answers';

    protected $fillable = [
        'student_id',
        'assessment_type',
        'assessment_question_id',
        'answer_text',
        'selected_option',
        'is_submitted',
        'started_at',
        'answered_at',
    ];

    protected $casts = [
        'is_submitted' => 'boolean',
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
    ];
}
