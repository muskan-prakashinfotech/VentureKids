<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = "projects";

    public function project(){
        return $this->hasMany(Projectfiles::class,'project_id','id');
    }

    public function student(){
        return $this->belongsTo(Students::class, 'student_id', 'id');
    }

    public function projectDetails(){
        return $this->hasMany(ProjectDetails::class,'project_id','id');
    }

    public function projectFiles(){
        return $this->hasMany(Projectfiles::class,'project_id','id');
    }

    public function theme()
    {
        return $this->belongsTo(ProjectTheme::class, 'project_theme_id');
    }

    public function feedback()
    {
        return $this->hasOne(ProjectFeedback::class);
    }
}
