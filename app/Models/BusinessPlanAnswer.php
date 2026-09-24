<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPlanAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'question_id', 'selected_option_id', 'selected_option_value', 'custom_text', 'response_text', 'is_correct',
        'currency_id','form_token','business_plan_id'
    ];

     public function question()
    {
         return $this->belongsTo(BusinessPlanQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(BusinessPlanQuestionOption::class, 'selected_option_id');
    }
}
