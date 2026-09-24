<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;
    protected $table = "student_grade";
    protected $fillable = ['name'];

    public function themes()
    {
    return $this->hasMany(ProjectTheme::class, 'student_grade_id');
    }
}
