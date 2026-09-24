<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProjectAnswer extends Model
{
    protected $fillable = ['student_project_id', 'project_question_id', 'answer_text'];

    public function studentProject()
    {
        return $this->belongsTo(StudentProject::class);
    }

    public function question()
    {
        return $this->belongsTo(ProjectQuestion::class, 'project_question_id');
    }
}
