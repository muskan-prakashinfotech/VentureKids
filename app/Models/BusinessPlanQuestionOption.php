<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPlanQuestionOption extends Model
{
    use HasFactory;


    protected $fillable = [
        'business_plan_question_id',
        'option_text',
        'option_value',
        'show_input_field',
        'is_active',
        'is_correct',
        'business_plan_id'
    ];

    public function question()
    {
        return $this->belongsTo(BusinessPlanQuestion::class);
    }
}
