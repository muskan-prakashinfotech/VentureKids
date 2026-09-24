<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProjectAttachment extends Model
{
    protected $fillable = ['student_project_id', 'project_question_id', 'file_path', 'file_name', 'file_type'];

    public function studentProject()
    {
        return $this->belongsTo(StudentProject::class);
    }

    public function question()
    {
        return $this->belongsTo(ProjectQuestion::class, 'project_question_id');
    }

    public function getUrlAttribute()
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        if (file_exists(public_path('tenants/' . $this->file_path))) {
            return asset('tenants/' . $this->file_path);
        }

        return asset($this->file_path);
    }

    public function getFullPathAttribute()
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return null;
        }

        if (file_exists(public_path('tenants/' . $this->file_path))) {
            return public_path('tenants/' . $this->file_path);
        }

        return public_path($this->file_path);
    }
}
