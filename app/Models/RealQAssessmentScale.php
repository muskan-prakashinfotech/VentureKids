<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealQAssessmentScale extends Model
{
    use HasFactory;

    protected $table = 'realq_assessment_scale';

    protected $fillable = [
        'name',
    ];
}
