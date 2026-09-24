<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentTopic extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_topics';

    protected $fillable = [
        'grade_id',
        'board_id',
        'country_id',
        'subject_id',
        'topic',
        'moderation_status',
        'prompt',
        'raw_response',
        'created_by',
    ];
}
