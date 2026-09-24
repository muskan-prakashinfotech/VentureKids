<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPlanSubmission extends Model
{
    use HasFactory;

    protected $table = 'business_plans_submission';

    protected $fillable = [
        'business_plan_id',
        'student_id',
        'is_submit',
        'submission_date',
        'file_path',
    ];

    public function businessPlan()
    {
        return $this->belongsTo(BusinessPlan::class, 'business_plan_id');
    }
}
