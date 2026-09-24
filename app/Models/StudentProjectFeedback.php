<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProjectFeedback extends Model
{
    protected $table = 'student_project_feedbacks';

    protected $fillable = ['student_project_id', 'trainer_id', 'public_note', 'private_suggestions', 'smart_score', 'is_publish'];

    protected $casts = [
        'smart_score' => 'integer',
        'is_publish' => 'integer',
    ];

    public function studentProject()
    {
        return $this->belongsTo(StudentProject::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
