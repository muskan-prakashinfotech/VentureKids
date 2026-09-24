<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLicense extends Model
{
    use HasFactory;

    protected $table = 'student_license';

    protected $fillable = [
        'school_id',
        'student_id',
        'level_id',
        'license_key',
        'status',
        'created_by',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
