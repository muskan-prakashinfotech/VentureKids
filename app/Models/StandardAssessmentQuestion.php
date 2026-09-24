<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardAssessmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'standard_assessment_questions';

    protected $fillable = [
        'category_id',
        'question_text',
        'option_a',
        'option_a_score',
        'option_b',
        'option_b_score',
        'option_c',
        'option_c_score',
        'option_d',
        'option_d_score',
        'correct_option',
        'active',
    ];

    public function category()
    {
        return $this->belongsTo(StandardAssessmentCategory::class, 'category_id', 'id');
    }
}

