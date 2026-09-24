<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPlanQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_value',
        'prompt_text',
        'question_type',
        'is_required',
        'allow_custom_option',
        'placeholder_text',
        'step',
        'display_order',
        'category_id',
        'business_plan_id',
    ];

    public function options()
    {
        return $this->hasMany(BusinessPlanQuestionOption::class, 'business_plan_question_id');
    }

    public function answers()
    {
        return $this->hasMany(BusinessPlanAnswer::class, 'question_id', 'id');
    }
}
