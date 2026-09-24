<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectQuestion extends Model
{
    protected $fillable = [
        'project_section_id',
        'field_text',
        'field_type',
        'help_text',
        'is_required',
        'allow_attachments',
        'allowed_types',
        'allowed_multiples',
        'display_order',
        'status',
    ];

    public function section()
    {
        return $this->belongsTo(ProjectSection::class, 'project_section_id');
    }

    public function answers()
    {
        return $this->hasMany(StudentProjectAnswer::class, 'project_question_id');
    }

    public function attachments()
    {
        return $this->hasMany(StudentProjectAttachment::class, 'project_question_id');
    }

    protected static function booted()
    {
        static::deleting(function (ProjectQuestion $question) {
            $question->answers()->delete();
            $question->attachments()->delete();
        });
    }
}
