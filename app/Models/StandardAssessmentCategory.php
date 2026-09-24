<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardAssessmentCategory extends Model
{
    use HasFactory;

    protected $table = 'standard_assessment_categories';

    protected $fillable = [
        'grade_id',
        'category_name',
        'active',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(StandardAssessmentQuestion::class, 'category_id', 'id');
    }
}

