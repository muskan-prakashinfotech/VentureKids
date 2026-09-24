<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolAcademicYear extends Model
{
    protected $table = 'school_academic_years';

    protected $fillable = [
        'school_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
