<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProject extends Model
{
    protected $fillable = ['student_id', 'status', 'is_submitted', 'improvement_comments', 'published_sections'];

    protected $casts = [
        'published_sections' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(StudentProjectAnswer::class);
    }

    public function attachments()
    {
        return $this->hasMany(StudentProjectAttachment::class);
    }

    public function feedback()
    {
        return $this->hasOne(StudentProjectFeedback::class)->latestOfMany();
    }

    public function feedbacks()
    {
        return $this->hasMany(StudentProjectFeedback::class);
    }

    protected static function booted()
    {
        static::deleting(function (StudentProject $studentProject) {
            $studentProject->answers()->delete();
            $studentProject->attachments()->delete();
            $studentProject->feedbacks()->delete();
        });
    }
}
