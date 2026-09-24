<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSchoolReport extends Model
{
    protected $table = 'assessment_school_report';

    protected $fillable = [
        'school_id',
        'assessment_type',
        'report_path',
    ];
}
