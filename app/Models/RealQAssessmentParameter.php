<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentParameter extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_parameters';

    protected $fillable = [
        'name',
        'description',
    ];
}
