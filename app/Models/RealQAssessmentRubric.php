<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentRubric extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_rubrics';

    protected $fillable = [
        'scale_id',
        'score',
        'name',
        'description',
    ];

    public function scale()
    {
        return $this->belongsTo(RealQAssessmentScale::class, 'scale_id');
    }
}
