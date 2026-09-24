<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTheme extends Model
{
    use HasFactory;

    protected $fillable = ['student_grade_id', 'project_theme_name','status'];

    public function grade()
    {
        return $this->belongsTo(StudentGrade::class, 'student_grade_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_theme_id');
    }

    
}
