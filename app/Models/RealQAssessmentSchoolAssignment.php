<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentSchoolAssignment extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_school_assignments';

    protected $fillable = [
        'school_id',
        'realq_assessment_assigned',
        'realq_assessment_enabled',
        'realq_assessment_enabled_from',
        'realq_assessment_enabled_to',
        'realq_assessment_assigned_board_id',
        'realq_assessment_assigned_grade_id',
        'realq_assessment_assigned_parameters_id',
        'realq_assessment_assigned_scale_id',
    ];
}
