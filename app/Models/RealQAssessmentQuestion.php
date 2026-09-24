<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_questions';

    protected $fillable = [
        'topic_id',
        'grade_id',
        'board_id',
        'country_id',
        'subject_id',
        'question_type',
        'moderation_status',
        'reject_reason',
        'question_text',
        'challenge',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'prompt',
        'raw_response',
        'created_by',
    ];
}
