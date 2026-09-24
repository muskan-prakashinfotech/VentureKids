<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentSubject extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_subjects';

    protected $fillable = [
        'name',
    ];
}
